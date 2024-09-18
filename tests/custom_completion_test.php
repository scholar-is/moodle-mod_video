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
 * Custom completion tests.
 *
 * @package    mod_video
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace mod_video;

use advanced_testcase;
use cm_info;
use coding_exception;
use dml_exception;
use mod_video\completion\custom_completion;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/mod/forum/tests/generator/lib.php');
require_once($CFG->dirroot . '/mod/forum/tests/generator_trait.php');

/**
 * Custom completion tests.
 *
 * @package    mod_video
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class custom_completion_test extends advanced_testcase {
    use \mod_forum_tests_generator_trait;

    /**
     * Data provider for get_state().
     *
     * @return array[]
     */
    public static function get_state_provider(): array {
        return [
            'Undefined rule' => [
                'somenonexistentrule', COMPLETION_TRACKING_NONE, null, coding_exception::class,
            ],
            'Completion on play rule not available'  => [
                'completiononplay', COMPLETION_TRACKING_NONE, null, moodle_exception::class,
                [
                    'completiononplay' => 1,
                ],
            ],
            'Completion on play rule available, user has not played video' => [
                'completiononplay', COMPLETION_TRACKING_AUTOMATIC, COMPLETION_INCOMPLETE, null,
                [
                    'completiononplay' => 1,
                ],
            ],
            'Rule available, user has played video' => [
                'completiononplay', COMPLETION_TRACKING_AUTOMATIC, COMPLETION_COMPLETE, null,
                [
                    'completiononplay' => 1,
                ],
                [[
                    'watchtime' => 0,
                ], ],
            ],
        ];
    }

    /**
     * Test for get_state().
     *
     * @covers \mod_video\completion\custom_completion
     * @dataProvider get_state_provider
     * @param string $rule The custom completion rule.
     * @param int $available Whether this rule is available.
     * @param int|null $status Expected status.
     * @param string|null $exception Expected exception.
     * @param array $extraparams
     * @param array $sessions
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function test_get_state(
        string $rule,
        int $available,
        ?int $status,
        ?string $exception,
        array $extraparams = [],
        array $sessions = []
    ): void {
        if (!is_null($exception)) {
            $this->expectException($exception);
        }

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => COMPLETION_ENABLED]);
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        $videogenerator = $this->getDataGenerator()->get_plugin_generator('mod_video');

        $params = array_merge([
            'course' => $course->id,
            'completion' => $available,
        ], $extraparams);
        $video = $this->getDataGenerator()->create_module('video', $params);

        $cm = get_coursemodule_from_instance('video', $video->id);

        if ($sessions) {
            foreach ($sessions as $session) {
                $videogenerator->create_video_session((int)$student->id, (int)$cm->id, $session);
            }
        }

        // Make sure we're using a cm_info object.
        $cm = cm_info::create($cm);

        $customcompletion = new custom_completion($cm, (int)$student->id);
        $this->assertEquals($status, $customcompletion->get_state($rule));
    }

    /**
     * Test for get_defined_custom_rules().
     * @covers \mod_video\completion\custom_completion::get_defined_custom_rules
     */
    public function test_get_defined_custom_rules(): void {
        $rules = custom_completion::get_defined_custom_rules();
        $this->assertCount(3, $rules);
        $this->assertEquals('completiononplay', reset($rules));
    }

    /**
     * Test for get_defined_custom_rule_descriptions().
     * @covers \mod_video\completion\custom_completion::get_custom_rule_descriptions
     */
    public function test_get_custom_rule_descriptions(): void {
        // Get defined custom rules.
        $rules = custom_completion::get_defined_custom_rules();

        // Build a mock cm_info instance.
        $mockcminfo = $this->getMockBuilder(cm_info::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get'])
            ->getMock();
        // Instantiate a custom_completion object using the mocked cm_info.
        $customcompletion = new custom_completion($mockcminfo, 1);

        // Get custom rule descriptions.
        $ruledescriptions = $customcompletion->get_custom_rule_descriptions();

        // Confirm that defined rules and rule descriptions are consistent with each other.
        $this->assertEquals(count($rules), count($ruledescriptions));
        foreach ($rules as $rule) {
            $this->assertArrayHasKey($rule, $ruledescriptions);
        }
    }

    /**
     * Test for is_defined().
     * @covers \mod_video\completion\custom_completion::is_defined
     */
    public function test_is_defined(): void {
        // Build a mock cm_info instance.
        $mockcminfo = $this->getMockBuilder(cm_info::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customcompletion = new custom_completion($mockcminfo, 1);

        // Rule is defined.
        $this->assertTrue($customcompletion->is_defined('completiononplay'));

        // Undefined rule.
        $this->assertFalse($customcompletion->is_defined('somerandomrule'));
    }

    /**
     * Data provider for test_get_available_custom_rules().
     *
     * @return array[]
     */
    public static function get_available_custom_rules_provider(): array {
        return [
            'Completion on play available' => [
                COMPLETION_ENABLED, ['completiononplay'],
            ],
            'Completion on play not available' => [
                COMPLETION_DISABLED, [],
            ],
            'Completion on percent available' => [
                COMPLETION_ENABLED, ['completiononpercent'],
            ],
            'Completion on percent not available' => [
                COMPLETION_DISABLED, [],
            ],
            'Completion on viewtime available' => [
                COMPLETION_ENABLED, ['completiononviewtime'],
            ],
            'Completion on viewtime not available' => [
                COMPLETION_DISABLED, [],
            ],
        ];
    }

    /**
     * Test for get_available_custom_rules().
     *
     * @covers \mod_video\completion\custom_completion::get_available_custom_rules
     * @dataProvider get_available_custom_rules_provider
     * @param int $status
     * @param array $expected
     */
    public function test_get_available_custom_rules(int $status, array $expected): void {
        $customdataval = [
            'customcompletionrules' => [],
        ];
        if ($status == COMPLETION_ENABLED) {
            $rule = $expected[0];
            $customdataval = [
                'customcompletionrules' => [$rule => $status],
            ];
        }

        // Build a mock cm_info instance.
        $mockcminfo = $this->getMockBuilder(cm_info::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get'])
            ->getMock();

        // Mock the return of magic getter for the customdata attribute.
        $mockcminfo->expects($this->any())
            ->method('__get')
            ->with('customdata')
            ->willReturn($customdataval);

        $customcompletion = new custom_completion($mockcminfo, 1);
        $this->assertEquals($expected, $customcompletion->get_available_custom_rules());
    }
}
