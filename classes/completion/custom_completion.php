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

namespace mod_video\completion;

use coding_exception;
use core_completion\activity_custom_completion;
use dml_exception;
use moodle_exception;

/**
 * Activity custom completion subclass for the video activity.
 *
 * Class for defining mod_video's custom completion rules and fetching the completion statuses
 * of the custom completion rules for a given video instance and a user.
 *
 * @package    mod_video
 * @copyright  2022 Joseph Conradt <joeconradt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_completion extends activity_custom_completion {
    /**
     * Fetches the completion state for a given completion rule.
     *
     * @param string $rule The completion rule.
     * @return int The completion state.
     * @throws dml_exception
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);

        $userid = $this->userid;
        $cm = $this->cm;

        if (!$video = $DB->get_record('video', ['id' => $this->cm->instance])) {
            throw new moodle_exception('Unable to find video with ID: ' . $this->cm->instance);
        }

        $rulecompleted = false;

        switch ($rule) {
            case 'completiononplay':
                $rulecompleted = $DB->record_exists('video_session', ['userid' => $userid, 'cmid' => $cm->id]);
                break;
            case 'completiononpercent':
                $maxwatchpercent = $DB->get_field_sql(
                    'SELECT MAX(watchpercent) FROM {video_session} WHERE userid = ? AND cmid = ?',
                    [$userid, $cm->id]
                );
                $rulecompleted = $maxwatchpercent >= ($video->completionpercent / 100);
                break;
            case 'completiononviewtime':
                $watchtime = $DB->get_field_sql(
                    'SELECT SUM(watchtime) FROM {video_session} WHERE userid = ? AND cmid = ?',
                    [$userid, $cm->id]
                );
                $rulecompleted = $watchtime >= $video->completionviewtime;
                break;
        }

        return $rulecompleted ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
    }

    /**
     * Fetch the list of custom completion rules that this module defines.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return [
            'completiononplay',
            'completiononpercent',
            'completiononviewtime',
        ];
    }

    /**
     * Returns an associative array of the descriptions of custom completion rules.
     *
     * @return array
     * @throws coding_exception
     */
    public function get_custom_rule_descriptions(): array {
        $completiononplay = $this->cm->customdata['customcompletionrules']['completiononplay'] ?? 0;
        $completiononpercent = $this->cm->customdata['customcompletionrules']['completiononpercent'] ?? 0;
        $completiononviewtime = $this->cm->customdata['customcompletionrules']['completiononviewtime'] ?? 0;

        return [
            'completiononplay' => get_string('completiondetail:completiononplay', 'video', $completiononplay),
            'completiononpercent' => get_string('completiondetail:completiononpercent', 'video', $completiononpercent),
            'completiononviewtime' => get_string('completiondetail:completiononviewtime', 'video', $completiononviewtime),
        ];
    }

    /**
     * Returns an array of all completion rules, in the order they should be displayed to users.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return [
            'completionview',
            'completiononplay',
            'completiononpercent',
            'completiononviewtime',
        ];
    }
}
