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
 * Display list of videos for a course.
 * @package    mod_video
 * @copyright  2023 Joseph Conradt <joeconradt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT);
$PAGE->set_url('/mod/video/index.php', ['id' => $id]);

if (!$course = $DB->get_record('course', ['id' => $id])) {
    throw new \moodle_exception('invalidcourseid');
}

require_login($course, true);
$PAGE->set_pagelayout('incourse');
$context = context_course::instance($course->id);

$event = \mod_video\event\course_module_instance_list_viewed::create(['context' => $context]);
$event->add_record_snapshot('course', $course);
$event->trigger();

$PAGE->navbar->add(get_string('modulenameplural', 'video'), 'index.php?id=$course->id');
$PAGE->set_title(get_string('modulenameplural', 'video'));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'video'));

if (!$videos = get_all_instances_in_course('video', $course)) {
    notice(get_string('thereareno', 'moodle', get_string('modulenameplural', 'video')), $PAGE->url);
    die;
}

$usesections = course_format_uses_sections($course->format);

$timenow = time();
$strname = get_string('name');
$table = new html_table();

if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_' . $course->format);
    $table->head = [$strsectionname, $strname];
} else {
    $table->head = [$strname];
}

foreach ($videos as $video) {
    $linkcss = null;
    if (!$video->visible) {
        $linkcss = ['class' => 'dimmed'];
    }
    $link = html_writer::link(new moodle_url('/mod/video/view.php', ['id' => $video->coursemodule]), $video->name, $linkcss);

    if ($usesections) {
        $table->data[] = [get_section_name($course, $video->section), $link];
    } else {
        $table->data[] = [$link];
    }
}

echo html_writer::table($table);

echo $OUTPUT->footer();
