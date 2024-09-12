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
 * @package    videosource_vimeo
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videosource_vimeo\videosource;

use dml_exception;
use lang_string;
use mod_video\video_source;
use mod_video_mod_form;
use moodle_exception;
use moodle_url;
use MoodleQuickForm;
use stdClass;

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->dirroot/mod/video/source/vimeo/vendor/autoload.php");

/**
 * Vimeo video source.
 * @package    videosource_vimeo
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vimeo extends video_source {

    private $lib;

    public function __construct() {
        $this->lib = new \Vimeo\Vimeo(
            get_config('videosource_vimeo', 'clientid'),
            get_config('videosource_vimeo', 'clientsecret'),
            get_config('videosource_vimeo', 'accesstoken'),
        );
    }

    /**
     * Get source type.
     * @return string
     */
    public function get_type(): string {
        return 'vimeo';
    }

    /**
     * Get source name.
     * @return string
     */
    public function get_name(): string {
        return new lang_string('pluginname', 'media_vimeo');
    }

    /**
     * Get source icon.
     * @return string
     */
    public function get_icon(): string {
        return 'vimeo';
    }

    /**
     * Check if video source is fully configured.
     * @return bool
     */
    public function has_api(): bool {
        return true;
    }

    public function add_form_elements(mod_video_mod_form $form, MoodleQuickForm $mform, $current): void {
        global $PAGE;

        $PAGE->requires->js_call_amd('mod_video/mod_form', 'init', [
            'uniqueid' => 'modform_vimeo',
            'videoSourceType' => 'vimeo',
            'inputId' => 'id_vimeoid',
            'debug' => $current && $current->debug === "1",
        ]);

        $group = [];
        $group[] = $mform->createElement('text', 'vimeoid', get_string('vimeovideoid', 'videosource_vimeo'));
        if ((new vimeo())->is_configured()) {
            $group[] = $mform->createElement('button', 'searchvideos_vimeo', get_string('searchvideos', 'video'));
        } else if (is_siteadmin()) {
            $group[] = $mform->createElement(
                'static',
                'connectvimeo',
                '',
                \html_writer::link(
                    new moodle_url('/admin/settings.php?section=videosource_vimeo'),
                    get_string('connectvimeo', 'videosource_vimeo')
                )
            );
        }
        $mform->addGroup($group, 'videoidgroup', get_string('vimeovideoid', 'videosource_vimeo'), null, false);
        $mform->addHelpButton('videoidgroup', 'vimeovideoid', 'videosource_vimeo');
        $mform->setType('vimeoid', PARAM_INT);
        $mform->setType('videoidgroup', PARAM_RAW);
        $mform->hideIf('videoidgroup', 'type', 'noeq', $this->get_type());

        parent::add_form_elements($form, $mform, $current);
    }

    public function data_preprocessing(&$defaultvalues): void {
        if ($defaultvalues['videoid']) {
            $defaultvalues['vimeoid'] = $defaultvalues['videoid'];
        }
    }

    public function data_postprocessing(stdClass $data): void {
        if ($data->vimeoid) {
            $data->videoid = $data->vimeoid;
        }
    }

    /**
     * @throws moodle_exception
     */
    public function get_authorization_url(): string {
        $scope = 'public private';
        $redirecturi = new moodle_url('/mod/video/source/vimeo/callback.php');
        if (!get_user_preferences('vimeo_auth_state')) {
            $state = random_string(15);
            set_user_preference('vimeo_auth_state', $state);
        }
        return $this->lib->buildAuthorizationEndpoint(
            $redirecturi->out(false),
            $scope,
            get_user_preferences('vimeo_auth_state')
        );
    }

    /**
     * Check if the Vimeo API is configured in Moodle.
     * @return bool
     * @throws dml_exception
     */
    public function is_configured(): bool {
        return get_config('videosource_vimeo', 'clientid') &&
            get_config('videosource_vimeo', 'clientsecret') &&
            get_config('videosource_vimeo', 'accesstoken');
    }

    public function query(string $query): array {
        $result = $this->lib->request('/me/videos', [
            'query' => $query,
        ]);

        $results = [
            'videos' => [],
            'total' => $result['body']['total'],
        ];
        foreach ($result['body']['data'] as $video) {
            $thumbnail = isset($video['pictures']['sizes'][2]) ? $video['pictures']['sizes'][2]['link'] : '';
            $results['videos'][] = [
                'videoid' => explode('/', $video['uri'])[2],
                'title' => $video['name'],
                'description' => $video['description'],
                'thumbnail' => $thumbnail,
            ];
        }

        return $results;
    }
}