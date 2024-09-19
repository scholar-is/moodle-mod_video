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
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_video;

use mod_video_mod_form;
use moodle_exception;
use MoodleQuickForm;
use stdClass;

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
     * Add form elements for this video source.
     * @param mod_video_mod_form $form
     * @param MoodleQuickForm $mform
     * @param stdClass $current
     * @return void
     */
    public function add_form_elements(mod_video_mod_form $form, MoodleQuickForm $mform, stdClass $current): void {
    }

    /**
     * Preprocess data.
     * @param array $defaultvalues
     * @return void
     */
    public function data_preprocessing(array &$defaultvalues): void {
    }

    /**
     * Postprocess data.
     * @param stdClass $data
     * @return void
     */
    public function data_postprocessing(stdClass $data): void {

    }

    /**
     * Get list of all video sources.
     * @return video_source[]
     */
    public static function get_video_sources(): array {
        $sources = [];
        foreach (array_keys(\core_component::get_component_classes_in_namespace(null, 'videosource')) as $class) {
            if (is_subclass_of($class, self::class)) {
                $sources[] = new $class();
            }
        }
        return $sources;
    }

    /**
     * Get video source by type.
     * @param string $type
     * @return video_source
     */
    public static function get_video_source_by_type(string $type): video_source {
        $found = null;
        foreach (self::get_video_sources() as $source) {
            if ($source->get_type() === $type) {
                $found = $source;
            }
        }
        return $found;
    }

    /**
     * Does this source have an API for querying videos?
     * @return bool
     */
    public function has_api(): bool {
        return false;
    }

    /**
     * Check if video source is fully configured.
     * @return bool
     */
    public function is_configured(): bool {
        return true;
    }

    /**
     * Query videos from this video source.
     * @param string $query
     * @return array
     */
    public function query(string $query): array {
        return [];
    }
}
