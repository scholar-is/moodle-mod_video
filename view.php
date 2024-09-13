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
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_video\component\video;
use videotab_overview\component\comments;

require('../../config.php');

global $DB, $CFG, $PAGE, $OUTPUT;

require_once($CFG->dirroot . '/mod/video/lib.php');
require_once($CFG->libdir . '/completionlib.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID.
$v = optional_param('v', 0, PARAM_INT);  // Video instance ID.

if ($v) {
    if (!$video = $DB->get_record('video', ['id' => $v])) {
        throw new moodle_exception('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('video', $video->id, $video->course, false, MUST_EXIST);
} else {
    if (!$cm = get_coursemodule_from_id('video', $id)) {
        throw new moodle_exception('invalidcoursemodule');
    }
    $video = $DB->get_record('video', ['id' => $cm->instance], '*', MUST_EXIST);
}

$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/video:view', $context);

// Completion and trigger events.
video_view($video, $course, $cm, $context);

$PAGE->set_url('/mod/video/view.php', ['id' => $cm->id]);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title($course->shortname . ': ' . $video->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($video);

$renderer = $PAGE->get_renderer('mod_video');

echo $OUTPUT->header();

echo $renderer->render(new video(cm_info::create($cm)));

echo $OUTPUT->footer();
