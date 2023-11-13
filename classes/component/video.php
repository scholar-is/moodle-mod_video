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
 * Video component.
 *
 * @package    mod_video
 * @copyright  2022 Scholaris <joe@scholar.is>
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
 * Video component.
 *
 * @package    mod_video
 * @copyright  2022 Scholaris <joe@scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class video extends base_component {
    /**
     * Video instance.
     * @var cm_info
     */
    private cm_info $cm;

    /**
     * Video instance.
     * @var stdClass
     */
    private stdClass $video;

    /**
     * Constructor.
     * @param cm_info $cm
     * @throws moodle_exception
     */
    public function __construct(cm_info $cm) {
        global $DB;
        $this->cm = $cm;
        $this->video = $DB->get_record('video', ['id' => $cm->instance], '*', MUST_EXIST);
        $tabmanager = new tab_manager(cm_info::create($cm));
        $tabs = $tabmanager->build_tabs_component();

        $this->add_childcomponent('tabs', $tabs);
    }

    /**
     * Get video URL from source.
     * Move to video_source?
     * @throws coding_exception
     */
    public function get_url(): ?string {
        switch ($this->video->type) {
            case 'external':
                return $this->video->externalurl;
            case 'internal':
                $fs = get_file_storage();
                $cm = get_coursemodule_from_instance('video', $this->video->id);
                $context = \context_module::instance($cm->id);
                $files = $fs->get_area_files($context->id, 'mod_video', 'videofiles', $this->video->id, 'id', false);
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

    /**
     * Get plyr control values.
     * @return array
     */
    public function get_controls(): array {
        $instancecontrols = json_decode($this->video->controls, true);

        if (!is_array($instancecontrols)) {
            return array_keys(array_filter(video_get_controls_default_values()));
        }

        // Filter the array to only include controls that are enabled (truthy values).
        $filteredcontrols = array_filter($instancecontrols);

        // Get the keys (control names) of the filtered array.
        return array_keys($filteredcontrols);
    }

    /**
     * Get extra options for JS.
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_extra_options(): array {
        global $USER;
        $aggregatevalues = video_session::get_aggregate_values($this->cm->id, $USER->id);
        return [
            'preventForwardSeeking' => !!$this->video->preventforwardseeking,
            'sessionAggregates' => $aggregatevalues,
        ];
    }

    /**
     * Export data for mustache.
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_data(): array {
        return [
            'video' => $this->video,
            'cm' => $this->cm,
            'cmjson' => json_encode($this->cm),
            'videojson' => json_encode($this->video),
            'options' => json_encode(array_merge([
                'debug' => !!$this->video->debug,
                'autoplay' => !!$this->video->autoplay,
                'fullscreen' => ['enabled' => !!$this->video->fullscreenenabled],
                'disableContextMenu' => !!$this->video->disablecontextmenu,
                'hideControls' => !!$this->video->hidecontrols,
                'controls' => $this->get_controls(),
            ], $this->get_extra_options())),
            'supportsprovider' => in_array($this->video->type, ['youtube', 'vimeo']),
            'supportshtml5' => in_array($this->video->type, ['internal', 'external']),
            'url' => $this->get_url(),
        ];
    }
}
