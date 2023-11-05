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
 * @package    videoreport_videosessions
 * @copyright  2023 Scholaris <joe@scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videoreport_videosessions;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/tablelib.php');

use cm_info;
use coding_exception;
use core_user\fields;
use dml_exception;
use moodle_exception;
use moodle_url;
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
     * Constructor.
     * @param cm_info $cm
     * @param string $uniqueid
     * @throws coding_exception
     * @throws dml_exception
     */
    public function __construct(cm_info $cm, $uniqueid) {
        global $DB;
        parent::__construct($uniqueid);

        $this->cm = $cm;

        $columns = ['userid', 'views', 'watchtime', 'watchpercent', 'firstaccess', 'lastaccess', 'actions'];
        $headers = [
            get_string('user'),
            get_string('views'),
            get_string('totalwatchtime', 'video'),
            get_string('watchpercentage', 'video'),
            get_string('firstaccess', 'video'),
            get_string('lastaccess', 'video'),
            get_String('actions', 'video'),
        ];

        $this->define_columns($columns);
        $this->define_headers($headers);

        $userfields = ltrim(fields::for_name()->get_sql('u')->selects . fields::for_userpic()->get_sql('u')->selects, ', ');

        $sqlfields = "u.id AS userid, $userfields,
                      COUNT(u.id) as views,
                      COUNT(u.id) as count,
                      SUM(vs.watchtime) as watchtime,
                      CONCAT(ROUND(MAX(vs.watchpercent) * 100, 2), '%') AS watchpercent,
                      MIN(vs.timecreated) AS firstaccess,
                      MAX(vs.timemodified) AS lastaccess";

        $sqlfrom = "{video_session} vs
                    JOIN {user} u ON u.id = vs.userid";

        $this->set_sql($sqlfields, $sqlfrom, 'vs.cmid = :cmid GROUP BY u.id', ['cmid' => $this->cm->id]);
        $this->set_count_sql("SELECT COUNT(DISTINCT vs.userid) FROM {video_session} vs
                                  WHERE vs.cmid = :cmid", ['cmid' => $this->cm->id]);

        $this->pagesize(15, $DB->count_records_sql($this->countsql, $this->countparams));
    }

    /**
     * Get user for this row.
     * @param stdClass $values
     * @return stdClass
     */
    private function get_user($values): stdClass {
        $user = new stdClass();
        $user->id = $values->userid;

        $namefields = fields::for_name()->get_sql('u')->selects;
        $picturefields = fields::for_userpic()->get_sql('u')->selects;

        // Extracting individual fields from the SQL string.
        $fields = explode(',', $namefields . $picturefields);
        foreach ($fields as $field) {
            if (empty($field)) {
                continue;
            }
            // Remove table alias and trim.
            $field = trim(str_replace('u.', '', $field));
            $user->{$field} = $values->{$field};
        }
        return $user;
    }

    /**
     * Format views.
     * @param stdClass $values
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function col_views($values): string {
        if ($this->is_downloading()) {
            return $values->views;
        }
        return \html_writer::link(new moodle_url('/mod/video/report/usersessions/index.php', [
            'cmid' => $this->cm->id,
            'userid' => $values->userid,
        ]), get_string($values->views == 1 ? 'numviews' : 'numviews_plural', 'video', ['views' => $values->views]));
    }

    /**
     * Display user's name and picture.
     * @param stdClass $values
     * @return string
     */
    public function col_userid($values): string {
        global $OUTPUT;
        return $OUTPUT->user_picture($this->get_user($values), ['courseid' => $this->cm->course, 'includefullname' => true]);
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
        return userdate($values->firstaccess, "", \core_date::get_user_timezone($this->get_user($values)));
    }

    /**
     * Format lastaccess.
     * @param stdClass $values
     * @return string
     */
    public function col_lastaccess($values): string {
        if ($values->lastaccess) {
            return userdate($values->lastaccess, "", \core_date::get_user_timezone($this->get_user($values)));
        }
        return '';
    }

    /**
     * Render actions (eventually).
     * @param stdClass $values
     * @return void
     */
    public function col_actions($values) {

    }
}
