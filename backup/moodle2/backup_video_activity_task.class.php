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
 * This file contains the backup activity for the video module
 *
 * @package    mod_video
 * @copyright  2022 Scholaris <joe@scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/video/backup/moodle2/backup_video_stepslib.php');

/**
 * Video backup task that provides all the settings and steps to perform one complete backup of the activity
 *
 * @package    mod_video
 * @copyright  2022 Scholaris <joe@scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_video_activity_task extends backup_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        $this->add_step(new backup_video_activity_structure_step('video_structure', 'mod_video.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     * @param string $content
     * @return string
     */
    public static function encode_content_links($content): string {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        $search = "/(".$base."\/mod\/video\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@VIDEOINDEX*$2@$', $content);

        $search = "/(".$base."\/mod\/video\/view.php\?id\=)([0-9]+)/";
        return preg_replace($search, '$@VIDEOVIEWBYID*$2@$', $content);
    }

}

