<?php

namespace mod_video\tab;

use core_component;
use mod_video\component\tabs;
use cm_info;
use moodle_exception;

class tab_manager {
    /**
     * @var cm_info
     */
    protected cm_info $cm;

    public function __construct(cm_info $cm) {
        if ($cm->modname !== 'video') {
            throw new moodle_exception('');
        }
        $this->cm = $cm;
    }

    /**
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
