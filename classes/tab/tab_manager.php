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

namespace mod_video\tab;

use core_component;
use mod_video\component\tabs;
use cm_info;
use moodle_exception;

/**
 * Tab manager.
 *
 * @package    mod_video
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tab_manager {
    /**
     * Video course module.
     * @var cm_info
     */
    protected cm_info $cm;

    /**
     * Constructor.
     * @param cm_info $cm
     * @throws moodle_exception
     */
    public function __construct(cm_info $cm) {
        if ($cm->modname !== 'video') {
            throw new moodle_exception('');
        }
        $this->cm = $cm;
    }

    /**
     * Get all tabs.
     * @return base_tab[]
     */
    private function get_all_tabs(): array {
        $tabs = [];

        $tabclasses = core_component::get_component_classes_in_namespace(null, 'videotab');
        foreach ($tabclasses as $class => $path) {
            if (is_subclass_of($class, base_tab::class)) {
                $tabs[] = new $class($this->cm);
            }
        }

        return $tabs;
    }

    /**
     * Get `tabs` component with all tabs as child components.
     * @return tabs
     */
    public function build_tabs_component(): tabs {
        $tabs = new tabs();
        foreach ($this->get_all_tabs() as $tab) {
            if (!$tab->show_tab()) {
                continue;
            }
            $tabs->add_childcomponent($tab->get_name(), $tab);
        }
        return $tabs;
    }
}
