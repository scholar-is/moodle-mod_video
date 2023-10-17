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

namespace videoreport_usersessions;

global $CFG;

require_once($CFG->libdir . '/tablelib.php');

use cm_info;
use coding_exception;
use core_user\fields;
use stdClass;
use table_sql;

class session_report_table extends table_sql {
    /**
     * @var cm_info
     */
    private $cm;

    private $user;

    /**
     * @throws coding_exception
     */
    public function __construct(cm_info $cm, stdClass $user, $uniqueid) {
        parent::__construct($uniqueid);

        $this->cm = $cm;
        $this->user = $user;

        $columns = ['watch_time', 'watch_percentage', 'first_access', 'last_access'];
        $headers = [
            get_string('totalwatchtime', 'video'),
            get_string('watchpercentage', 'video'),
            get_string('firstaccess', 'video'),
            get_string('lastaccess', 'video'),
        ];

        $this->define_columns($columns);
        $this->define_headers($headers);

        $sqlfields = "vs.id,
                      CONCAT(
                        FLOOR(vs.watchtime / 3600),
                        ':',
                        FLOOR((vs.watchtime % 3600) / 60),
                        ':',
                        vs.watchtime % 60
                      ) AS watch_time,
                      CONCAT(ROUND(vs.watchpercent * 100, 2), '%') AS watch_percentage,
                      vs.timecreated AS first_access,
                      vs.timemodified AS last_access";

        $sqlfrom = "{video_session} vs";

        $this->set_sql(
            $sqlfields,
            $sqlfrom,
            'vs.cmid = :cmid AND vs.userid = :userid',
            [
                'cmid' => $this->cm->id,
                'userid' => $this->user->id,
            ]
        );
        $this->set_count_sql(
            "SELECT COUNT(1) FROM {video_session} vs
                 WHERE vs.cmid = :cmid AND vs.userid = :userid",
            [
                'cmid' => $this->cm->id,
                'userid' => $this->user->id,
            ]
        );
    }

    public function col_first_access($values) {
        return userdate($values->first_access, "", \core_date::get_user_timezone($this->user));
    }

    public function col_last_access($values) {
        if ($values->last_access) {
            return userdate($values->last_access, "", \core_date::get_user_timezone($this->user));
        }
        return '';
    }
}
