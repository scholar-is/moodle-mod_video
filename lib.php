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
 * @package    mod_video
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once("$CFG->dirroot/lib/formslib.php");

/**
 * List of features supported in Video module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return string|int|bool|null True if module supports feature, false if not, null if doesn't know
 */
function video_supports(string $feature): string|int|bool|null {
    switch ($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_OTHER;
        case FEATURE_GROUPINGS:
        case FEATURE_GRADE_HAS_GRADE:
        case FEATURE_GRADE_OUTCOMES:
        case FEATURE_GROUPS:
            return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_COMPLETION_HAS_RULES:
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_CONTENT;

        default:
            return null;
    }
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data mixed the data submitted from the reset course.
 * @return array status array
 */
function video_reset_userdata(mixed $data): array {
    // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
    // See MDL-9367.

    return [];
}

/**
 * Add video instance.
 * @param stdClass $data
 * @param mod_video_mod_form|null $mform
 * @return int new video instance id
 * @throws dml_exception
 * @throws coding_exception
 */
function video_add_instance(stdClass $data, mod_video_mod_form $mform = null): int {
    global $DB;

    $data->controls = json_encode($data->controls);
    $data->id = $DB->insert_record('video', $data);

    $context = context_module::instance($data->coursemodule);
    if (!empty($data->videofile)) {
        file_save_draft_area_files(
            $data->videofile,
            $context->id,
            'mod_video',
            'videofiles',
            $data->id,
            ['subdirs' => 0, 'maxbytes' => -1, 'maxfiles' => 1]
        );
    }

    return $data->id;
}

/**
 * Update video instance.
 * @param stdClass $data
 * @param mod_video_mod_form $mform
 * @return bool true
 * @throws coding_exception
 * @throws dml_exception
 */
function video_update_instance(stdClass $data, mod_video_mod_form $mform): bool {
    global $DB;

    $data->controls = json_encode($data->controls);
    $data->timemodified = time();
    $data->id = $data->instance;

    $context = context_module::instance($data->coursemodule);
    if (!empty($data->videofile)) {
        file_save_draft_area_files(
            $data->videofile,
            $context->id,
            'mod_video',
            'videofiles',
            $data->id,
            ['subdirs' => 0, 'maxbytes' => -1, 'maxfiles' => 1]
        );
    }

    return $DB->update_record('video', $data);
}

/**
 * Delete video instance.
 * @param int $id
 * @return bool true
 */
function video_delete_instance($id) {
    global $DB;

    if (!$video = $DB->get_record('video', ['id' => $id])) {
        return false;
    }

    $cm = get_coursemodule_from_instance('video', $video->id);

    $DB->delete_records('video_session', ['cmid' => $cm->id]);

    if (!$DB->delete_records('video', ['id' => $video->id])) {
        return false;
    }

    return true;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param stdClass $coursemodule
 * @return cached_cm_info Info to customise main video display
 */
function video_get_coursemodule_info($coursemodule): cached_cm_info|bool {
    global $DB;

    if (!$video = $DB->get_record('video', ['id' => $coursemodule->instance])) {
        return false;
    }

    $info = new cached_cm_info();
    $info->name = $video->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $info->content = format_module_intro('video', $video, $coursemodule->id, false);
    }

    // Populate the custom completion rules as key => value pairs, but only if the completion mode is 'automatic'.
    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $info->customdata['customcompletionrules']['completiononplay'] = $video->completiononplay;
        $info->customdata['customcompletionrules']['completiononpercent'] = $video->completiononpercent;
        $info->customdata['customcompletionrules']['completiononviewtime'] = $video->completiononviewtime;
    }

    return $info;
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function video_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $modulepagetype = ['mod-video-*' => get_string('video-mod-page-x', 'video')];
    return $modulepagetype;
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $video      page object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 */
function video_view($video, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = [
        'context' => $context,
        'objectid' => $video->id,
    ];

    $event = \mod_video\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('video', $video);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

/**
 * Check if the module has any update that affects the current user since a given time.
 *
 * @param  cm_info $cm course module data
 * @param  int $from the time to check updates from
 * @param  array $filter  if we need to check only specific updates
 * @return stdClass an object with the different type of areas indicating if they were updated or not
 */
function video_check_updates_since(cm_info $cm, $from, $filter = []) {
    $updates = course_check_module_updates_since($cm, $from, [''], $filter);
    return $updates;
}

/**
 * Serves the resource files.
 *
 * @package  mod_video
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function video_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);

    if ($filearea == 'videofiles') {
        $relativepath = implode('/', $args);

        $fullpath = "/$context->id/mod_video/$filearea/$relativepath";

        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }

        send_stored_file($file, null, 0, $forcedownload, $options);
    }
}

/**
 * Obtains the automatic completion state for this video based on any conditions
 * in video settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function video_get_completion_state($course, $cm, $userid, $type) {
    global $CFG, $DB;

    // Get forum details
    $forum = $DB->get_record('forum', ['id' => $cm->instance], '*', MUST_EXIST);

    // If completion option is enabled, evaluate it and return true/false
    if ($forum->completionposts) {
        return $forum->completionposts <= $DB->get_field_sql(
            "
                         SELECT 
                             COUNT(1) 
                         FROM 
                             {forum_posts} fp 
                             INNER JOIN {forum_discussions} fd ON fp.discussion=fd.id
                         WHERE
                     fp.userid=:userid AND fd.forum=:forumid",
            ['userid' => $userid, 'forumid' => $forum->id]
        );
    } else {
        // Completion option is not enabled so just return $type
        return $type;
    }
}
