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
 * @package    videoreport_usersessions
 * @copyright  2023 Scholaris <joe@scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videoreport_usersessions;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/tablelib.php');

use cm_info;
use coding_exception;
use dml_exception;
use stdClass;
use table_sql;

/**
 * Session report table.
 */
class session_report_table extends table_sql {
    /**
     * @var cm_info
     */
    private cm_info $cm;

    /**
     * @var stdClass
     */
    private stdClass $user;

    /**
     * Constructor.
     * @param cm_info $cm
     * @param stdClass $user
     * @param string $uniqueid
     * @throws coding_exception
     * @throws dml_exception
     */
    public function __construct(cm_info $cm, stdClass $user, string $uniqueid) {
        global $DB;
        parent::__construct($uniqueid);

        $this->cm = $cm;
        $this->user = $user;

        $columns = ['watchtime', 'watchpercent', 'firstaccess', 'lastaccess'];
        $headers = [
            get_string('totalwatchtime', 'video'),
            get_string('watchpercentage', 'video'),
            get_string('firstaccess', 'video'),
            get_string('lastaccess', 'video'),
        ];

        $this->define_columns($columns);
        $this->define_headers($headers);

        $sqlfields = "vs.id,
                      vs.watchtime,
                      CONCAT(ROUND(vs.watchpercent * 100, 2), '%') AS watchpercent,
                      vs.timecreated AS firstaccess,
                      vs.timemodified AS lastaccess";

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

        $this->pagesize(15, $DB->count_records_sql($this->countsql, $this->countparams));
    }

    /**
     * Format watchtime.
     * @param stdClass $values
     * @return string
     */
    public function col_watchtime($values): string {
        $hours = floor($values->watchtime / 3600);
        $minutes = floor(($values->watchtime % 3600) / 60);
        $seconds = $values->watchtime % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Format firstaccess.
     * @param stdClass $values
     * @return string
     */
    public function col_firstaccess($values): string {
        return userdate($values->firstaccess, "", \core_date::get_user_timezone($this->user));
    }

    /**
     * Format lastaccess.
     * @param stdClass $values
     * @return string
     */
    public function col_lastaccess($values): string {
        if ($values->lastaccess) {
            return userdate($values->lastaccess, "", \core_date::get_user_timezone($this->user));
        }
        return '';
    }
}
