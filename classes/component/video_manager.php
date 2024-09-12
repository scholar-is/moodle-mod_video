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
 * Video manager component.
 *
 * @package    mod_video
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_video\component;

use cm_info;
use coding_exception;
use dml_exception;
use mod_video\persistent\video_session;
use mod_video\tab\tab_manager;
use moodle_exception;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Video manager component.
 *
 * @package    mod_video
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class video_manager extends base_component {

    /**
     * Constructor.
     * @param cm_info $cm
     * @throws moodle_exception
     */
    public function __construct() {
    }

    /**
     * Export data for mustache.
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_data(): array {
        return [
            'uniqueid' => 'foo'
        ];
    }
}
