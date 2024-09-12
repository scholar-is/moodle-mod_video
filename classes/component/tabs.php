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
 * Tabs component.
 *
 * @package    mod_video
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_video\component;

use cm_info;
use coding_exception;
use comment_exception;
use mod_video\tab\base_tab;
use moodle_exception;
use renderable;
use renderer_base;
use templatable;

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->dirroot/comment/lib.php");

/**
 * Tabs component.
 *
 * @package    mod_video
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tabs extends base_component {
    /**
     * Get data for template specific to this component.
     * @return array
     */
    protected function get_data(): array {
        return [];
    }

    /**
     * Get data for template.
     * @param renderer_base $output
     * @return array
     * @throws coding_exception
     */
    public function export_for_template(renderer_base $output): array {
        $data = parent::export_for_template($output);
        $data['tabs'] = [];

        // Filter out only the tab components.
        $tabs = array_filter($this->get_childcomponents(), function ($component) {
            return $component instanceof base_tab;
        });

        // Sort the tabs based on the get_order_sequence value.
        usort($tabs, function ($a, $b) {
            return $a->get_order_sequence() <=> $b->get_order_sequence();
        });

        $first = true;
        /** @var base_tab $tab */
        foreach ($tabs as $tab) {
            $data['tabs'][] = [
                'title' => $tab->get_title(),
                'name' => $tab->get_name(),
                'content' => $data[$tab->get_name()],
                'active' => $first,
            ];

            $first = false;
        }

        return $data;
    }
}
