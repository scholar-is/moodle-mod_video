<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Session report table.
 *
 * @package    mod_video
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_video\table;

global $CFG;

require_once($CFG->libdir . '/tablelib.php');
use core_user\fields;
use stdClass;
use table_sql;

class session_report_table extends table_sql {
    public function __construct(int $cmid, $uniqueid) {
        parent::__construct($uniqueid);

        $columns = ['userid', 'watch_time', 'watch_percentage', 'first_access', 'last_access'];
        $headers = ['User Name', 'Watch Time', 'Watch Percentage', 'First Access', 'Last Access'];

        $this->define_columns($columns);
        $this->define_headers($headers);

        // Get all user name fields for the SQL SELECT statement.
        $userfields = ltrim(fields::for_name()->get_sql('u')->selects, ', ');

        $sqlfields = "u.id AS userid, $userfields,
                      CONCAT(FLOOR(vs.watchtime / 3600), ':', FLOOR((vs.watchtime % 3600) / 60), ':', vs.watchtime % 60) AS watch_time,
                      CONCAT(ROUND(vs.watchpercent * 100, 2), '%') AS watch_percentage,
                      FROM_UNIXTIME(vs.timecreated) AS first_access,
                      FROM_UNIXTIME(vs.timemodified) AS last_access";

        $sqlfrom = "{video_session} vs
                    JOIN {user} u ON u.id = vs.userid";

        $this->set_sql($sqlfields, $sqlfrom, 'vs.cmid = :cmid', ['cmid' => $cmid]);
        $this->set_count_sql("SELECT COUNT(1) FROM {video_session} vs
                                  JOIN {user} u ON u.id = vs.userid
                                  WHERE vs.cmid = :cmid", ['cmid' => $cmid]);
    }

    public function col_userid($values) {
        global $CFG;

        $user = new stdClass();
        $user->id = $values->userid;

        $namefields = fields::for_name()->get_sql('u')->selects;

        // Extracting individual fields from the SQL string.
        $fields = explode(',', $namefields);
        foreach ($fields as $field) {
            if (empty($field)) {
                continue;
            }
            // Remove table alias and trim.
            $field = trim(str_replace('u.', '', $field));
            $user->{$field} = $values->{$field};
        }

        return '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $user->id . '">' . fullname($user) . '</a>';
    }
}
