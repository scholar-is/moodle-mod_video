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
 * Webservice functions.
 *
 * @package    mod_video
 * @copyright  2022 Scholaris <joe@scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_video_create_session' => [
        'classname'    => 'mod_video\external\external',
        'methodname'   => 'create_session',
        'classpath'    => '',
        'description'  => 'Create video session.',
        'type'         => 'write',
        'capabilities' => 'mod/video:view',
        'ajax'         => true,
    ],
    'mod_video_record_session_updates' => [
        'classname'    => 'mod_video\external\external',
        'methodname'   => 'record_session_updates',
        'classpath'    => '',
        'description'  => 'Record session updates.',
        'type'         => 'write',
        'capabilities' => 'mod/video:view',
        'ajax'         => true,
    ],
];

