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
 * @copyright  2022 Scholaris <joe@scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videosource_vimeo\videosource;

use dml_exception;
use lang_string;
use mod_video\video_source;
use moodle_exception;
use moodle_url;

/**
 * Vimeo video source.
 * @package    videosource_vimeo
 * @copyright  2022 Scholaris <joe@scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vimeo extends video_source {
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
        return new lang_string('pluginname', 'media_vimeo'); // Already translated.
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

    /**
     * @throws moodle_exception
     * @throws dml_exception
     */
    public function get_authorization_url(): \moodle_url {
        $clientid = get_config('videosource_vimeo', 'clientid');
        $redirecturi = new moodle_url('/mod/video/source/vimeo/callback.php');

        $scope = 'public private';
        $state = random_string(15);

        $authurl = 'https://api.vimeo.com/oauth/authorize';
        $params = [
            'response_type' => 'code',
            'client_id' => $clientid,
            'redirect_uri' => $redirecturi->out(false),
            'scope' => $scope,
            'state' => $state, // CSRF protection.
        ];

        set_user_preference('vimeo_auth_state', $state);

        return new moodle_url($authurl, $params);
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
}
