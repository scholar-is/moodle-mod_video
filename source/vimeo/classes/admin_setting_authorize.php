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

namespace videosource_vimeo;

use dml_exception;
use moodle_exception;
use stdClass;
use videosource_vimeo\videosource\vimeo;

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/adminlib.php");

/**
 * Custom setting to display an authorize button.
 * @package    videosource_vimeo
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_authorize extends \admin_setting {

    /**
     * Constructor.
     * @param string $name
     * @param string $visiblename
     * @param string $description
     */
    public function __construct($name, $visiblename, $description) {
        parent::__construct($name, $visiblename, $description, '');
    }

    /**
     * Render button.
     * @param string $data
     * @param string $query
     * @return string
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function output_html($data, $query = ''): string {
        global $OUTPUT;

        $vimeo = new vimeo();
        $content = $OUTPUT->render_from_template('videosource_vimeo/authorize_button', [
            'configured' => $vimeo->is_configured(),
            'authorizationurl' => $vimeo->get_authorization_url(),
            'hasclientinfo' => get_config('videosource_vimeo', 'clientid') &&
                get_config('videosource_vimeo', 'clientsecret'),
        ]);

        return format_admin_setting(
            $this,
            $this->visiblename,
            $content,
            $this->description,
            true,
            '',
            $this->defaultsetting,
            $query
        );
    }

    /**
     * Not required.
     * @return void
     */
    public function get_setting() {
    }

    /**
     * Not required.
     * @param string $data
     * @return string
     */
    public function write_setting($data): string {
        return $data;
    }
}
