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
 * Video plugin renderer.
 *
 * @package    mod_video
 * @copyright  2022 Scholaris <joe@scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_video\output;

use mod_video\component\base_component;
use plugin_renderer_base;

/**
 * Implement render methods as needed. For now, using as rendering shortcut {@see plugin_renderer_base::render()}.
 *
 * @package    mod_video
 * @copyright  2022 Scholaris <joe@scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
//    /**
//     * Renders the provided widget and returns the HTML to display it.
//     *
//     * @param renderable $widget instance with renderable interface
//     * @return string
//     */
//    public function render(\renderable $widget) {
//        if ($widget instanceof base_component) {
//            return $this->render_component($widget);
//        }
//
//        return parent::render($widget);
//    }
//
//    public function render_component(base_component $component) {
//        $classparts = explode('\\', get_class($widget));
//        // Strip namespaces.
//        $classname = array_pop($classparts);
//
//        $rendermethod = "render_{$classname}";
//        if (method_exists($this, $rendermethod)) {
//            // Call the render_[widget_name] function.
//            // Note: This has a higher priority than the named_templatable to allow the theme to override the template.
//            return $this->$rendermethod($widget);
//        }
//
//        if ($widget instanceof named_templatable) {
//            // This is a named templatable.
//            // Fetch the template name from the get_template_name function instead.
//            // Note: This has higher priority than the guessed template name.
//            return $this->render_from_template(
//                $widget->get_template_name($this),
//                $widget->export_for_template($this)
//            );
//        }
//
//        if ($widget instanceof templatable) {
//            // Guess the templat ename based on the class name.
//            // Note: There's no benefit to moving this aboved the named_templatable and this approach is more costly.
//            $component = array_shift($classparts);
//            if (!$component) {
//                $component = 'core';
//            }
//            $template = $component . '/' . $classname;
//            $context = $widget->export_for_template($this->output);
//            return $this->render_from_template($template, $context);
//        }
//    }
}
