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
 * @package    videotab_overview
 * @copyright  2023 Scholaris <joe@scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotab_overview\videotab;

use mod_video\tab\base_tab;

/**
 * Base tab.
 *
 * @package    videotab_overview
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class overview_tab extends base_tab {
    protected function get_data() {
        $activitydescription = format_module_intro('video', $this->instance, $this->cm->id);
        return [
            'activitydescription' => $activitydescription,
        ];
    }

    public function get_name(): string {
        return 'overview';
    }

    public function get_title(): string {
        return get_string('overview', 'videotab_overview');
    }

    public function show_tab(): bool {
        return $this->instance->descriptioninsummary == '1';
    }

    public function get_order_sequence(): int {
        return 0; // Overview should always be first.
    }
}
