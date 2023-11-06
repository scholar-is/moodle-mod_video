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
 * Base tab.
 *
 * @package    mod_video
 * @copyright  2023 Scholaris <joe@scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_video\tab;

use cm_info;
use dml_exception;
use mod_video\component\base_component;
use moodle_exception;
use stdClass;

/**
 * Base tab.
 *
 * @package    mod_video
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base_tab extends base_component {

    /**
     * Video course module.
     * @var cm_info
     */
    protected cm_info $cm;

    /**
     * Video activity instance.
     * @var stdClass
     */
    protected stdClass $instance;

    /**
     * Constructor.
     * @param cm_info $cm
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function __construct(cm_info $cm) {
        global $DB;
        if ($cm->modname !== 'video') {
            throw new moodle_exception('');
        }
        $this->cm = $cm;
        $this->instance = $DB->get_record('video', ['id' => $this->cm->instance], '*', MUST_EXIST);
    }

    /**
     * Get unique name for tab.
     * @return string
     */
    abstract public function get_name(): string;

    /**
     * Get human-readable title for tab.
     * @return string
     */
    abstract public function get_title(): string;

    /**
     * Check if tab should be displayed or not.
     * @return bool
     */
    abstract public function show_tab(): bool;

    /**
     * Get sequence for ordering tabs.
     * @return int
     */
    public function get_order_sequence(): int {
        return 100;
    }
}
