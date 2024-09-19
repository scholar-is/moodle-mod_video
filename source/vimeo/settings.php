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
 * Vimeo settings.
 *
 * @package    videosource_vimeo
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use videosource_vimeo\admin_setting_authorize;
use videosource_vimeo\component\vimeo_help_table;
use videosource_vimeo\videosource\vimeo;

defined('MOODLE_INTERNAL') || die();

$renderer = $PAGE->get_renderer('video');
$settings = new admin_settingpage(
    'videosource_vimeo',
    get_string('connectvimeo', 'videosource_vimeo'),
);

$settings->add(new admin_setting_heading(
    'vimeoinstructions',
    get_string('gettingstarted', 'videosource_vimeo'),
    $renderer->render(new vimeo_help_table(new vimeo())))
);

$settings->add(new admin_setting_heading(
    'vimeoconfiguration',
    get_string('configuration', 'videosource_vimeo'),
    get_string('configurationinstructions', 'videosource_vimeo')
));

$settings->add(new admin_setting_configtext(
    'videosource_vimeo/clientid',
    get_string('clientid', 'videosource_vimeo'),
    get_string('clientid_desc', 'videosource_vimeo'),
    '',
    PARAM_TEXT
));

$settings->add(new admin_setting_configtext(
    'videosource_vimeo/clientsecret',
    get_string('clientsecret', 'videosource_vimeo'),
    get_string('clientsecret_desc', 'videosource_vimeo'),
    '',
    PARAM_TEXT
));

$settings->add(new admin_setting_authorize(
    'videosource_vimeo/authorize',
    get_string('authorize', 'videosource_vimeo'),
    get_string('authorizewithvimeo', 'videosource_vimeo'),
));

$ADMIN->add('modvideofolder', $settings);
