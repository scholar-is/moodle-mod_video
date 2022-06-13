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
 * Video configuration form.
 *
 * @package    mod_video
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_video\video_source;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->dirroot . '/repository/lib.php');

class mod_video_mod_form extends moodleform_mod {
    function definition() {
        global $CFG, $DB, $OUTPUT;

        $mform = $this->_form;

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        $mform->addElement('header', 'videodetails', get_string('videodetails', 'video'));
        $mform->setExpanded('videodetails');

        $radioarray = [];
        foreach (video_source::get_video_sources() as $source) {
            $radioarray[] = $mform->createElement('radio', 'type', '', $source->get_radio_label(), $source->get_type());
        }
        $mform->addGroup($radioarray, 'radioar', 'Type', [' '], false);
        $mform->setDefault('type', 'vimeo');

        //-------------------------------------------------------
        $mform->addElement('text', 'videoid', 'Video ID');
        $mform->setType('videoid', PARAM_TEXT);
        $mform->hideIf('videoid', 'type', 'in', ['internal', 'external']);
        //-------------------------------------------------------

        //-------------------------------------------------------
        $mform->addElement('url', 'externalurl', get_string('externalurl', 'url'), array('size'=>'60'), array('usefilepicker'=>true));
        $mform->setType('externalurl', PARAM_RAW_TRIMMED);
        $mform->hideIf('externalurl', 'type', 'noeq', 'external');

        $mform->addElement('filemanager', 'videofile', get_string('videofile', 'video'), null,
            ['subdirs' => 0, 'maxfiles' => 1,
                'accepted_types' => ['video']]);
        $mform->hideIf('videofile', 'type', 'noeq', 'internal');

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons();
    }

    public function data_preprocessing(&$defaultvalues) {
        if ($this->current->instance) {
            // Editing existing instance - copy existing files into draft area.
            $draftitemid = file_get_submitted_draft_itemid('videofile');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_video', 'videofiles', $this->current->id, ['subdirs'=>0, 'maxbytes' => -1, 'maxfiles' => 1]);
            $defaultvalues['videofile'] = $draftitemid;
        }
    }
}

