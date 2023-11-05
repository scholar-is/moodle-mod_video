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
 * Web service tests.
 *
 * @package    mod_video
 * @copyright  2022 Scholaris <joe@scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_video;

use mod_video\external\external;
use mod_video\persistent\video_session;
use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * API tests.
 * @group mod_video
 * @runTestsInSeparateProcesses
 */
class external_test extends \externallib_advanced_testcase {
    /**
     * @var stdClass
     */
    private $user;

    protected function setUp(): void {
        $this->resetAfterTest();
        $this->user = $this->getDataGenerator()->create_user();
        $this->setUser($this->user);
    }

    /**
     * Test video sessions.
     * @covers \mod_video\external\external::create_session
     * @covers \mod_video\external\external::record_session_updates
     * @covers \mod_video\persistent\video_session::get_aggregate_values
     * @return void
     * @throws \coding_exception
     * @throws \core_external\restricted_context_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     * @throws \restricted_context_exception
     * @throws exception\module_not_found
     */
    public function test_session() {
        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($this->user->id, $course->id);
        $video = $this->getDataGenerator()->create_module('video', ['course' => $course->id]);

        $response = external::create_session($video->cmid);

        $this->assertEquals($this->user->id, $response['session']->userid);

        external::record_session_updates($response['session']->id, 10, 10, 0.25);
        external::record_session_updates($response['session']->id, 10, 20, 0.5);
        external::record_session_updates($response['session']->id, 10, 30, 0.75);
        external::record_session_updates($response['session']->id, 10, 10, 0.25);

        $session = video_session::get_record(['id' => $response['session']->id]);

        $this->assertEquals(40, $session->get('watchtime'));
        $this->assertEquals(10, $session->get('lasttime'));
        $this->assertEquals(30, $session->get('maxtime'));
        $this->assertEquals(0.75, $session->get('watchpercent'));

        $response = external::create_session($video->cmid);
        external::record_session_updates($response['session']->id, 20, 35, 0.875);
        external::record_session_updates($response['session']->id, 10, 15, 0.375);

        $aggregates = video_session::get_aggregate_values($video->cmid, $this->user->id);

        $this->assertEquals(70, $aggregates->totalwatchtime);
        $this->assertEquals(15, $aggregates->lasttime);
        $this->assertEquals(35, $aggregates->maxtime);
        $this->assertEquals(0.88, $aggregates->maxwatchpercent);
    }
}
