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

namespace mod_video\output;

use core\output\activity_header as base_activity_header;
use moodle_page;
use stdClass;

/**
 * Override activity header component.
 *
 * @package    mod_video
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_header extends base_activity_header {

    /**
     * Constructor for activity_header.
     * @param moodle_page $page
     * @param stdClass $user
     */
    public function __construct(\moodle_page $page, stdClass $user) {
        parent::__construct($page, $user);

        if ($page->activityrecord && $page->activityrecord->descriptioninsummary) {
            $this->description = '';
        }
    }
}
