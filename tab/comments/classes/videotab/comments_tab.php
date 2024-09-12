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
 * Displays video comments in a tab.
 *
 * @package    videotab_comments
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotab_comments\videotab;

use coding_exception;
use comment_exception;
use dml_exception;
use mod_video\tab\base_tab;
use moodle_exception;
use cm_info;

/**
 * Displays video comments in a tab.
 *
 * @package    videotab_comments
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class comments_tab extends base_tab {

    /**
     * Constructor.
     * @param cm_info $cm
     * @throws moodle_exception
     */
    public function __construct(cm_info $cm) {
        parent::__construct($cm);
        global $PAGE;

        $PAGE->requires->strings_for_js([
            'commentscount',
            'addcomment',
        ], 'moodle');
    }

    /**
     * Get data for template.
     * @throws moodle_exception
     * @throws comment_exception
     */
    protected function get_data(): array {
        return [
            'commentshtml' => $this->get_comments()->output(true),
        ];
    }

    /**
     * Build comment object.
     * @throws moodle_exception
     * @throws comment_exception
     */
    public function get_comments(): \comment {
        return new \comment((object)[
            'component' => 'mod_video',
            'context'   => $this->cm->context,
            'course'    => $this->cm->get_course(),
            'cm'        => $this->cm,
            'area'      => 'video_comments',
            'showcount' => false,
            'notoggle'  => true,
            'autostart' => true,
        ]);
    }

    /**
     * Get unique name for tab.
     * @return string
     */
    public function get_name(): string {
        return 'comments';
    }

    /**
     * Get human-readable title for tab.
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_title(): string {
        global $DB;
        $count = $DB->count_records('comments', ['contextid' => $this->cm->context->id]);
        return get_string('commentswithcount', 'videotab_comments', $count);
    }

    /**
     * Check if the tab should be displayed or not.
     * @return bool
     */
    public function show_tab(): bool {
        return $this->instance->comments == "1";
    }
}
