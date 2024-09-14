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
 * @package    videosource_youtube
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videosource_youtube\videosource;

use coding_exception;
use lang_string;
use mod_video\video_source;
use mod_video_mod_form;
use MoodleQuickForm;
use stdClass;

/**
 * Youtube video source.
 * @package    videosource_youtube
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class youtube extends video_source {
    /**
     * Get source type.
     * @return string
     */
    public function get_type(): string {
        return 'youtube';
    }

    /**
     * Get source name.
     * @return string
     */
    public function get_name(): string {
        return new lang_string('pluginname', 'media_youtube'); // Already translated.
    }

    /**
     * Get source icon.
     * @return string
     */
    public function get_icon(): string {
        return 'youtube-play';
    }

    /**
     * Add form elements for this video source.
     * @param mod_video_mod_form $form
     * @param MoodleQuickForm $mform
     * @param $current
     * @return void
     * @throws coding_exception
     */
    public function add_form_elements(mod_video_mod_form $form, MoodleQuickForm $mform, $current): void {
        $mform->addElement('text', 'youtubeid', get_string('youtubeid', 'videosource_youtube'));
        $mform->addHelpButton('youtubeid', 'youtubeid', 'videosource_youtube');
        $mform->setType('youtubeid', PARAM_TEXT);
        $mform->hideIf('youtubeid', 'type', 'noeq', $this->get_type());

        parent::add_form_elements($form, $mform, $current);
    }

    /**
     * Data preprocessing.
     * @param $defaultvalues
     * @return void
     */
    public function data_preprocessing(&$defaultvalues): void {
        if ($defaultvalues['videoid']) {
            $defaultvalues['youtubeid'] = $defaultvalues['videoid'];
        }
    }

    /**
     * Data postprocessing.
     * @param stdClass $data
     * @return void
     */
    public function data_postprocessing(stdClass $data): void {
        if ($data->youtubeid) {
            $data->videoid = $data->youtubeid;
        }
    }
}
