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
 * @package    mod_video
 * @copyright  2022 Scholaris <joe@scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_video;

use moodle_exception;

/**
 * Video source.
 *
 * @package    mod_video
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class video_source {
    /**
     * Get video source type.
     * @return string
     */
    abstract public function get_type(): string;

    /**
     * Get source name.
     * @return string
     */
    abstract public function get_name(): string;

    /**
     * Get source icon.
     * @return string
     */
    abstract public function get_icon(): string;

    /**
     * Render radio label.
     * @throws moodle_exception
     */
    public function get_radio_label(): string {
        global $OUTPUT;
        return $OUTPUT->render_from_template('mod_video/video_type_label', [
            'label' => $this->get_name(),
            'icon' => $this->get_icon(),
        ]);
    }

    /**
     * Get list of all video sources.
     * @return video_source[]
     */
    public static function get_video_sources(): array {
        $sources = [];
        foreach (array_keys(\core_component::get_component_classes_in_namespace('mod_video', 'video_sources')) as $class) {
            $sources[] = new $class();
        }
        return $sources;
    }
}
