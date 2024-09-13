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
 * Video source.
 *
 * @package    videosource_external
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videosource_external\videosource;

use lang_string;
use mod_video\video_source;

/**
 * External video source.
 * @package    videosource_external
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends video_source {
    /**
     * Get source type.
     * @return string
     */
    public function get_type(): string {
        return 'external';
    }

    /**
     * Get source name.
     * @return string
     */
    public function get_name(): string {
        return new lang_string('externalurl', 'video');
    }

    /**
     * Get source icon.
     * @return string
     */
    public function get_icon(): string {
        return 'link';
    }
}
