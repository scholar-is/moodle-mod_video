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
 * Url module admin settings and defaults
 *
 * @package    mod_video
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../../config.php');

use core\output\notification;

$code = required_param('code', PARAM_TEXT);
$state = required_param('state', PARAM_TEXT);

if ($state !== get_user_preferences('vimeo_auth_state')) {
    die('Invalid state parameter.');
}

$tokenurl = 'https://api.vimeo.com/oauth/access_token';
$clientid = get_config('videosource_vimeo', 'clientid');
$clientsecret = get_config('videosource_vimeo', 'clientsecret');
$callback = (new moodle_url('/mod/video/source/vimeo/callback.php'))->out(false);

$ch = curl_init($tokenurl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $callback,
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . base64_encode($clientid . ':' . $clientsecret),
    'Content-Type: application/json',
]);
$response = curl_exec($ch);
curl_close($ch);

if ($response === false) {
    die('Vimeo error');
}

$responsedata = json_decode($response, true);

if (isset($responsedata['error_description']) && $responsedata['error_description']) {
    throw new Exception($responsedata['error_description']);
}

if (empty($responsedata['access_token'])) {
    die('Invalid access token response.');
}

set_config('accesstoken', $responsedata['access_token'], 'videosource_vimeo');

redirect(
    new moodle_url('/admin/settings.php?section=videosource_vimeo'),
    'Successfully authorized with Vimeo',
    null,
    notification::NOTIFY_SUCCESS,
);
