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
 * Define all the restore steps that will be used by the restore_video_activity_task
 *
 * @package    mod_video
 * @copyright  2022 Joseph Conradt <joeconradt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete videoment structure for restore, with file and id annotations
 *
 * @package    mod_video
 * @copyright  2022 Joseph Conradt <joeconradt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_video_activity_structure_step extends restore_activity_structure_step {

    /**
     * Define the structure of the restore workflow.
     *
     * @return restore_path_element $structure
     * @throws base_step_exception
     */
    protected function define_structure(): restore_path_element {
        $paths = [];
        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $paths[] = new restore_path_element('video', '/activity/video');
        if ($userinfo) {
            $paths[] = new restore_path_element('video_session',
                                                   '/activity/video/videosessions/videosession');
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process a video restore.
     *
     * @param object $data The data in object form
     * @return void
     * @throws dml_exception
     * @throws base_step_exception
     */
    protected function process_video(object $data): void {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $newitemid = $DB->insert_record('video', $data);

        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process a user_flags restore
     * @param object $data The data in object form
     * @return void
     * @throws dml_exception
     */
    protected function process_video_videosession(object $data): void {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->cmid = $this->get_mappingid('course_module', $data->cmid);

        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('video_session', $data);
    }

    /**
     * Once the database tables have been fully restored, restore the files
     * @return void
     */
    protected function after_execute(): void {
        $this->add_related_files('mod_video', 'intro', null);
    }
}
