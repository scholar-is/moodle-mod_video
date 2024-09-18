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

use coding_exception;
use DateMalformedStringException;
use DateTime;
use dml_exception;
use Exception;
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

    /**
     * Vimeo PHP SDK.
     * @var \Vimeo\Vimeo
     */
    private $lib;

    /**
     * Constructor.
     * @throws dml_exception
     */
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
     * Does this source have an API for querying videos?
     * @return bool
     */
    public function has_api(): bool {
        return true;
    }

    /**
     * Add form elements for this video source.
     * @param mod_video_mod_form $form
     * @param MoodleQuickForm $mform
     * @param stdClass $current
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function add_form_elements(mod_video_mod_form $form, MoodleQuickForm $mform, stdClass $current): void {
        global $PAGE;

        $PAGE->requires->js_call_amd('mod_video/mod_form', 'init', [
            'uniqueid' => 'modform_vimeo',
            'videoSourceType' => 'vimeo',
            'inputId' => 'id_vimeoid',
            'debug' => $current && isset($current->debug) && $current->debug === "1",
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

    /**
     * Data preprocessing.
     * @param array $defaultvalues
     * @return void
     */
    public function data_preprocessing(&$defaultvalues): void {
        if ($defaultvalues['videoid']) {
            $defaultvalues['vimeoid'] = $defaultvalues['videoid'];
        }
    }

    /**
     * Data postprocessing.
     * @param stdClass $data
     * @return void
     */
    public function data_postprocessing(stdClass $data): void {
        if ($data->vimeoid) {
            $data->videoid = $data->vimeoid;
        }
    }

    /**
     * Get authorization URL.
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

    /**
     * Search for videos.
     * @param string $query
     * @return array
     */
    public function query(string $query): array {
        $result = $this->lib->request('/me/videos', [
            'query' => $query,
            'per_page' => 10,
        ]);

        $results = [
            'videos' => [],
            'total' => $result['body']['total'],
        ];
        foreach ($result['body']['data'] as $video) {
            $thumbnail = isset($video['pictures']['sizes'][2]) ? $video['pictures']['sizes'][2]['link'] : '';
            try {
                $datecreated = $this->time_elapsed_string($video['created_time']);
            } catch (Exception $e) {
                $datecreated = '';
            }

            $results['videos'][] = [
                'videoid' => explode('/', $video['uri'])[2],
                'title' => $video['name'],
                'description' => $video['description'],
                'thumbnail' => $thumbnail,
                'datecreated' => $datecreated,
            ];
        }

        return $results;
    }

    /**
     * Get human-readable "time ago" string.
     * @param string $datetime
     * @param bool $full
     * @return string
     * @throws DateMalformedStringException
     */
    private function time_elapsed_string(string $datetime, bool $full = false): string {
        $now = new DateTime();
        $giventime = new DateTime($datetime);
        $diff = $now->diff($giventime);

        // Determine if the time is in the past or future.
        $suffix = ($giventime > $now) ? 'from now' : 'ago';

        // If the time is in the future, invert the difference.
        $diff->invert = false;

        $units = [
            'year'   => $diff->y,
            'month'  => $diff->m,
            'week'   => floor($diff->d / 7),
            'day'    => $diff->d % 7,
            'hour'   => $diff->h,
            'minute' => $diff->i,
            'second' => $diff->s,
        ];

        $parts = [];
        foreach ($units as $unit => $value) {
            if ($value > 0) {
                $parts[] = $value . ' ' . $unit . ($value > 1 ? 's' : '');
                if (!$full) {
                    break; // Use only the largest unit.
                }
            }
        }

        return $parts ? implode(', ', $parts) . ' ' . $suffix : 'just now';
    }
}
