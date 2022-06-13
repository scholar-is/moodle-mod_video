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
 * @copyright  2022 Joseph Conradt <joeconradt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_video;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_video instance list viewed event class.
 *
 * @package    mod_video
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class video_source {

    /**
     * @return string
     */
    public abstract function get_type(): string;

    public abstract function get_name(): string;

    public abstract function get_icon(): string;

    public function get_radio_label(): string {
        global $OUTPUT;
        return $OUTPUT->render_from_template('mod_video/video_type_label', [
            'label' => $this->get_name(),
            'icon' => $this->get_icon()
        ]);
    }

    /**
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

