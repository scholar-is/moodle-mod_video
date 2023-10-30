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
 * Comments component.
 *
 * @package    mod_video
 * @copyright  2023 Joseph Conradt <joeconradt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_video\component;

use cm_info;
use comment_exception;
use moodle_exception;
use renderable;
use renderer_base;
use templatable;

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->dirroot/comment/lib.php");

/**
 * Comments component.
 *
 * @package    mod_video
 * @copyright  2023 Joseph Conradt <joeconradt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class comments implements renderable, templatable {
    /**
     * Course module for comments.
     */
    private cm_info $cm;

    /**
     * Constructor.
     * @param cm_info $cm
     */
    public function __construct(cm_info $cm) {
        global $PAGE;
        $this->cm = $cm;

        $PAGE->requires->strings_for_js([
            'commentscount',
            'addcomment',
        ], 'moodle');
    }

    /**
     * Build comment object.
     * @throws moodle_exception
     * @throws comment_exception
     */
    public function get_comments(): \comment {
        return new \comment((object)[
            'component' => 'mod_video',
            'context'   => $this->cm->context,
            'course'    => $this->cm->get_course(),
            'cm'        => $this->cm,
            'area'      => 'video_comments',
            'showcount' => true,
        ]);
    }

    /**
     * Data for template.
     * @throws moodle_exception
     * @throws comment_exception
     */
    public function export_for_template(renderer_base $output): array {
        return [
            'commentshtml' => $this->get_comments()->output(true),
        ];
    }
}
