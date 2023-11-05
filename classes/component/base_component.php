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

namespace mod_video\component;

use cm_info;
use moodle_exception;
use renderable;
use renderer_base;
use templatable;

/**
 * Base tab.
 *
 * @package    mod_video
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base_component implements renderable, templatable {
    /**
     * @var base_component[]
     */
    private $childcomponents = [];

    public function add_childcomponent(string $name, base_component $childcomponent) {
        $this->childcomponents[$name] = $childcomponent;
    }

    /**
     * @return base_component[]
     */
    public function get_childcomponents(): array {
        return $this->childcomponents;
    }

    abstract protected function get_data();

    /**
     * @throws \coding_exception
     */
    public function export_for_template(renderer_base $output) {
        $data = $this->get_data();

        foreach ($this->childcomponents as $name => $component) {
            $data[$name] = $output->render($component);
        }

        return $data;
    }
}
