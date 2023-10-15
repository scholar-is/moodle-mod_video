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
 * mod_video data generator
 *
 * @package    mod_video
 * @copyright  2022 Joseph Conradt <joeconradt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_video\persistent\video_session;

defined('MOODLE_INTERNAL') || die();


/**
 * Video module data generator class.
 *
 * @package    mod_video
 * @copyright  2022 Joseph Conradt <joeconradt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_video_generator extends testing_module_generator {

    public function create_instance($record = null, array $options = null) {
        $record = (object)(array)$record;

        $defaultsettings = array(
            'name' => 'Testing video',
            'type' => 'external',
            'youtubeurl' => '',
            'youtubeid' => '',
            'vimeourl' => '',
            'vimeoid' => '',
            'externalurl' => '',
            'intro' => '',
            'introformat' => FORMAT_PLAIN,
            'timemodified' => 0,
            'videoid' => '',
            'debug' => 0,
            'controls' => 0,
            'autoplay' => 0,
            'disablecontextmenu' => 0,
            'hidecontrols' => 0,
            'fullscreenenabled' => 0,
            'loopvideo' => 0,
        );

        foreach ($defaultsettings as $name => $value) {
            if (!isset($record->{$name})) {
                $record->{$name} = $value;
            }
        }

        return parent::create_instance($record, (array)$options);
    }

    public function create_video_session(int $userid, int $cmid, array $params = []) {
        $session = new video_session(0, (object)array_merge([
            'userid' => $userid,
            'cmid' => $cmid,
            'lasttime' => 0,
            'maxtime' => 0,
            'watchpercent' => 0
        ], $params));

        $session->create();

        return $session;
    }
}
