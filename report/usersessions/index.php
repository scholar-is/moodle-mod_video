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
 * Video module view.
 *
 * @package    mod_video
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use videoreport_usersessions\session_report_table;

require('../../../../config.php');

global $CFG, $PAGE, $OUTPUT, $DB;

$download = optional_param('download', '', PARAM_ALPHA);
$cmid = required_param('cmid', PARAM_INT);
$userid = required_param('userid', PARAM_INT);

$cm = get_coursemodule_from_id('video', $cmid, 0, false, MUST_EXIST);
$video = $DB->get_record('video', ['id' => $cm->instance], '*', MUST_EXIST);

$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/video:view', $context);

$PAGE->set_url('/mod/video/report/usersessions/index.php', [
    'cmid' => $cmid,
    'userid' => $userid,
]);
$PAGE->set_pagelayout('report');
$PAGE->set_context($context);
$PAGE->activityheader->disable();

require_capability('mod/video:viewreports', $context);

$table = new session_report_table(cm_info::create($cm), $user, 'session_report');
$table->define_baseurl($PAGE->url);
$table->is_downloading($download, 'video_session_report', 'Video Session Report');

ob_start();
$table->out(15, false);
$reporthtml = ob_get_clean();

if (!$table->is_downloading()) {
    $PAGE->set_title(get_string('reportname', 'videoreport_videosessions'));
    $PAGE->set_heading(get_string('reportname', 'videoreport_videosessions'));
    $PAGE->navbar->add(
        get_string('reportname', 'videoreport_videosessions'),
        new moodle_url('/mod/video/report/videosessions/index.php', [
            'cmid' => $cmid,
        ]),
    );
    $PAGE->navbar->add(
        get_string('reportname', 'videoreport_usersessions'),
        $PAGE->url,
    );
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('videoreport_usersessions/index', [
        'backtoreporturl' => (new moodle_url('/mod/video/report/videosessions/index.php', [
            'cmid' => $cm->id,
        ]))->out(false),
        'userpichtml' => $OUTPUT->user_picture($user, ['courseid' => $course->id, 'includefullname' => true, 'size' => 50]),
        'reporthtml' => $reporthtml,
    ]);
} else {
    echo $reporthtml;
}

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}
