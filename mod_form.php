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
    private $controloptions = [
        'play-large' => 1,
        'restart' => 0,
        'rewind' => 0,
        'play' => 1,
        'fast-forward' => 0,
        'progress' => 1,
        'current-time' => 1,
        'duration' => 0,
        'mute' => 1,
        'volume' => 1,
        'captions' => 1,
        'settings' => 1,
        'pip' => 1,
        'airplay' => 1,
        'download' => 0,
        'fullscreen' => 1,
    ];

    public function definition() {
        global $CFG, $DB, $OUTPUT;

        $mform = $this->_form;

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), ['size' => '48']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        //-------------------------------------------------------
        $mform->addElement('header', 'videodetails', get_string('videodetails', 'video'));
        $mform->setExpanded('videodetails');

        $radioarray = [];
        foreach (video_source::get_video_sources() as $source) {
            $radioarray[] = $mform->createElement('radio', 'type', '', $source->get_radio_label(), $source->get_type());
        }
        $mform->addGroup($radioarray, 'radioar', 'Type', [' '], false);
        $mform->setDefault('type', 'vimeo');

        $mform->addElement('text', 'videoid', 'Video ID');
        $mform->setType('videoid', PARAM_TEXT);
        $mform->hideIf('videoid', 'type', 'in', ['internal', 'external']);

        $mform->addElement('url', 'externalurl', get_string('externalurl', 'url'), ['size' => '60'], ['usefilepicker' => true]);
        $mform->setType('externalurl', PARAM_RAW_TRIMMED);
        $mform->hideIf('externalurl', 'type', 'noeq', 'external');

        $mform->addElement('filemanager', 'videofile', get_string('videofile', 'video'), null,
            ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['video']]);
        $mform->hideIf('videofile', 'type', 'noeq', 'internal');

        //-------------------------------------------------------
        $mform->addElement('header', 'embedoptions', get_string('embedoptions', 'video'));

        $mform->addElement('advcheckbox', 'autoplay', get_string('autoplay', 'video'));
        $mform->setType('autoplay', PARAM_BOOL);

        $mform->addElement('advcheckbox', 'preventforwardseeking', get_string('preventforwardseeking', 'video'));
        $mform->setType('preventforwardseeking', PARAM_BOOL);
        $mform->addHelpButton('preventforwardseeking', 'preventforwardseeking', 'video');

        $mform->addElement('advcheckbox', 'hidecontrols', get_string('hidecontrols', 'video'));
        $mform->setType('hidecontrols', PARAM_BOOL);

        $mform->addElement('advcheckbox', 'fullscreen', get_string('fullscreenenabled', 'video'));
        $mform->setType('fullscreen', PARAM_BOOL);

        $mform->addElement('advcheckbox', 'loopvideo', get_string('loop', 'video'));
        $mform->setType('loop', PARAM_BOOL);

        $controloptions = [];
        foreach ($this->controloptions as $controloptionname => $defaultvalue) {
            $controloptions[] = $mform->createElement('advcheckbox', $controloptionname,
                get_string("control_$controloptionname", 'video'));
        }
        $mform->addGroup($controloptions, 'controls', get_string('showcontrols', 'video'), '<br>');
        $mform->addHelpButton('controls', 'showcontrols', 'video');
        foreach ($this->controloptions as $controloptionname => $defaultvalue) {
            $mform->setDefault("controls[$controloptionname]", $defaultvalue);
        }

        $mform->addElement('advcheckbox', 'debug', get_string('enabledebug', 'video'));
        $mform->setType('debug', PARAM_BOOL);
        $mform->setDefault('debug', false);
        $mform->addHelpButton('debug', 'enabledebug', 'video');
        $mform->setAdvanced('debug');

        $mform->addElement('advcheckbox', 'disablecontextmenu', get_string('disablecontextmenu', 'video'));
        $mform->setType('disablecontextmenu', PARAM_BOOL);
        $mform->setAdvanced('disablecontextmenu');

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        foreach ($data['controls'] as $name => $value) {
            if (!isset($this->controloptions[$name])) {
                $errors['controls'] = get_string('invalidcontrol', 'video');
            }
        }

        return $errors;
    }

    public function data_preprocessing(&$defaultvalues) {
        if ($this->current->instance) {
            // Editing existing instance - copy existing files into draft area.
            $draftitemid = file_get_submitted_draft_itemid('videofile');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_video', 'videofiles', $this->current->id, ['subdirs'=>0, 'maxbytes' => -1, 'maxfiles' => 1]);
            $defaultvalues['videofile'] = $draftitemid;

            if ($this->current->controls) {
                foreach (json_decode($this->current->controls, true) as $name => $value) {
                    $defaultvalues["controls[$name]"] = $value;
                }
            }
        }
    }
}

