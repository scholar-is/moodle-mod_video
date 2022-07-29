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
 * Base component.
 *
 * @package    mod_video
 * @copyright  2022 Joseph Conradt <joeconradt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_video\component;

use mod_video\persistent\video_session;
use renderable;
use renderer_base;
use templatable;

defined('MOODLE_INTERNAL') || die();

/**
 * @package    mod_video
 * @copyright  2022 Joseph Conradt <joeconradt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class video implements templatable, renderable {

    private $instance;

    public function __construct($instance) {
        $this->instance = $instance;
    }

    public function get_url(): ?string {
        switch ($this->instance->type) {
            case 'external':
                return $this->instance->externalurl;
            case 'internal':
                $fs = get_file_storage();
                $cm = get_coursemodule_from_instance('video', $this->instance->id);
                $context = \context_module::instance($cm->id);
                $files = $fs->get_area_files($context->id, 'mod_video', 'videofiles', $this->instance->id, 'id', false);
                $file = array_values($files)[0];

                return \moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                )->out();
        }

        return null;
    }

    public function get_controls(): array {
        $controls = [];
        foreach (json_decode($this->instance->controls, true) as $name => $value) {
            if (!$value) {
                continue;
            }
            $controls[] = $name;
        }
        return $controls;
    }

    public function get_cm(): object {
        return get_coursemodule_from_instance('video', $this->instance->id, 0, false, MUST_EXIST);
    }

    public function get_extra_options(): array {
        global $USER;
        $aggregatevalues = video_session::get_aggregate_values($this->get_cm()->id, $USER->id);
        return [
            'preventForwardSeeking' => $this->instance->preventforwardseeking,
            'sessionAggregates' => $aggregatevalues
        ];
    }

    public function export_for_template(renderer_base $output) {
        $cm = $this->get_cm();
        return [
            'video' => $this->instance,
            'cm' => $cm,
            'cmjson' => json_encode($cm),
            'videojson' => json_encode($this->instance),
            'options' => json_encode(array_merge([
                'debug' => !!$this->instance->debug,
                'autoplay' => !!$this->instance->autoplay,
                'fullscreen' => ['enabled' => !!$this->instance->fullscreenenabled],
                'disableContextMenu' => !!$this->instance->disablecontextmenu,
                'hideControls' => !!$this->instance->hidecontrols,
                'controls' => $this->get_controls()
            ], $this->get_extra_options())),
            'supportsprovider' => in_array($this->instance->type, ['youtube', 'vimeo']),
            'supportshtml5' => in_array($this->instance->type, ['internal', 'external']),
            'url' => $this->get_url()
        ];
    }
}