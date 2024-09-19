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
 * Define all the backup steps that will be used by the backup_video_activity_task
 *
 * @package    mod_video
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete choice structure for backup, with file and id annotations
 *
 * @package    mod_video
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_video_activity_structure_step extends backup_activity_structure_step {
    /**
     * Define the structure for the video activity
     * @return backup_nested_element
     * @throws base_step_exception
     * @throws base_element_struct_exception
     */
    protected function define_structure(): backup_nested_element {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $video = new backup_nested_element('video', ['id'], [
            'id',
            'course',
            'name',
            'type',
            'externalurl',
            'intro',
            'introformat',
            'timemodified',
            'videoid',
            'debug',
            'controls',
            'autoplay',
            'disablecontextmenu',
            'hidecontrols',
            'fullscreenenabled',
            'loopvideo',
            'preventforwardseeking',
            'completiononplay',
            'completiononpercent',
            'completionpercent',
            'completiononviewtime',
            'completionviewtime',
            'resume',
            'comments',
            'descriptioninsummary',
        ]);

        $videosessions = new backup_nested_element('videosessions');

        $videosession = new backup_nested_element('video_session', ['id'], [
            'id',
            'cmid',
            'userid',
            'watchtime',
            'lasttime',
            'maxtime',
            'watchpercent',
            'timecreated',
            'usermodified',
            'timemodified',
        ]);

        // Build the tree.
        $video->add_child($videosessions);
        $videosessions->add_child($videosession);

        // Define sources.
        $video->set_source_table('video', ['id' => backup::VAR_ACTIVITYID]);

        if ($userinfo) {
            $videosession->set_source_table('video_session', ['cmid' => backup::VAR_MODID]);
        }

        // Define id annotations.
        $videosession->annotate_ids('user', 'userid');

        // Define file annotations.
        // These file areas don't have an itemid.
        $video->annotate_files('mod_video', 'intro', null);

        // Return the root element (video), wrapped into standard activity structure.
        return $this->prepare_activity_structure($video);
    }
}
