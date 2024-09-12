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
 * Vimeo's integration help table.
 *
 * @package    videosource_vimeo
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videosource_vimeo\component;

use dml_exception;
use mod_video\component\base_component;
use moodle_exception;
use videosource_vimeo\videosource\vimeo;

/**
 * Vimeo's integration help table.
 *
 * @package    videosource_vimeo
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vimeo_help_table extends base_component {

    /**
     * Vimeo video source.
     * @var vimeo
     */
    private vimeo $source;

    /**
     * Constructor.
     * @param vimeo $source
     */
    public function __construct(vimeo $source) {
        $this->source = $source;
    }

    /**
     * Export data for mustache.
     * @return array
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function get_data(): array {
        global $CFG;
        return [
            'configured' => $this->source->is_configured(),
            'callbackurl' => "$CFG->wwwroot/mod/video/source/vimeo/callback.php",
            'authorizationurl' => $this->source->get_authorization_url(),
            'hasclientinfo' => get_config('videosource_vimeo', 'clientid') &&
                get_config('videosource_vimeo', 'clientsecret'),
        ];
    }
}
