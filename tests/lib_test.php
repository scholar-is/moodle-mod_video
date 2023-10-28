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
 * Lib tests.
 *
 * @package    mod_video
 * @copyright  2022 Joseph Conradt <joeconradt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_video;

use mod_video\external\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/video/lib.php');

/**
 * @group mod_video
 */
class lib_test extends \advanced_testcase {
    /**
     * Test deleting a video instance.
     * @covers \video_delete_instance
     */
    public function test_video_delete_instance() {
        global $SITE, $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->enrol_user($student1->id, $course->id);
        $this->getDataGenerator()->enrol_user($student2->id, $course->id);

        $video1 = $this->getDataGenerator()->create_module('video', ['course' => $course->id]);
        $video2 = $this->getDataGenerator()->create_module('video', ['course' => $course->id]);

        $this->setUser($student1);
        $response = external::create_session($video1->cmid);
        external::record_session_updates($response['session']->id, 10, 10, 0.25);
        external::record_session_updates($response['session']->id, 10, 20, 0.5);
        $response = external::create_session($video2->cmid);
        external::record_session_updates($response['session']->id, 10, 10, 0.25);
        external::record_session_updates($response['session']->id, 10, 20, 0.5);

        $this->setUser($student2);
        $response = external::create_session($video1->cmid);
        external::record_session_updates($response['session']->id, 10, 10, 0.25);
        external::record_session_updates($response['session']->id, 10, 20, 0.5);
        $response = external::create_session($video2->cmid);
        external::record_session_updates($response['session']->id, 10, 10, 0.25);
        external::record_session_updates($response['session']->id, 10, 20, 0.5);

        $this->assertCount(4, $DB->get_records('video_session'));

        video_delete_instance($video1->id);

        $this->assertCount(0, $DB->get_records('video_session', ['cmid' => $video1->cmid]));
        $this->assertEquals(0, $DB->get_record('video', ['id' => $video1->id]), 'Ensure video instance was deleted.');
    }
}
