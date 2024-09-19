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
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_video\video_source;
use videosource_vimeo\videosource\vimeo;

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/repository/lib.php');

/**
 * Video instance create/edit form.
 */
class mod_video_mod_form extends moodleform_mod {

    /**
     * Define form fields.
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function definition(): void {
        global $CFG, $PAGE;

        $PAGE->requires->js_call_amd('mod_video/mod_form', 'init', [
            'uniqueid' => 'modform_youtube',
            'videoSourceType' => 'youtube',
            'debug' => $this->current && isset($this->current->debug) && $this->current->debug === "1",
        ]);

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('hidden', 'videoid');
        $mform->setType('videoid', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name'), ['size' => '48']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        $mform->addElement('advcheckbox', 'descriptioninsummary', 'Display description in video summary');
        $mform->setType('descriptioninsummary', PARAM_BOOL);
        $mform->setDefault('descriptioninsummary', true);
        $mform->addHelpButton('descriptioninsummary', 'descriptioninsummary', 'video');
        $mform->setAdvanced('descriptioninsummary');

        $mform->addElement('header', 'videodetails', get_string('videodetails', 'video'));
        $mform->setExpanded('videodetails');

        $radioarray = [];
        foreach (video_source::get_video_sources() as $source) {
            $radioarray[] = $mform->createElement('radio', 'type', '', $source->get_radio_label(), $source->get_type());
        }
        $mform->addGroup($radioarray, 'radioar', 'Type', [' '], false);
        $mform->setDefault('type', 'vimeo');

        foreach (video_source::get_video_sources() as $source) {
            $source->add_form_elements($this, $mform, $this->current);
        }

        $mform->addElement('url', 'externalurl', get_string('externalurl', 'url'), ['size' => '60'], ['usefilepicker' => true]);
        $mform->setType('externalurl', PARAM_RAW_TRIMMED);
        $mform->hideIf('externalurl', 'type', 'noeq', 'external');

        $mform->addElement(
            'filemanager',
            'videofile',
            get_string('videofile', 'video'),
            null,
            ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['video']]
        );
        $mform->hideIf('videofile', 'type', 'noeq', 'internal');

        $mform->addElement('header', 'embedoptions', get_string('embedoptions', 'video'));

        $mform->addElement('advcheckbox', 'autoplay', get_string('autoplay', 'video'));
        $mform->setType('autoplay', PARAM_BOOL);
        $mform->addHelpButton('autoplay', 'autoplay', 'video');

        $mform->addElement('advcheckbox', 'resume', get_string('resume', 'video'));
        $mform->setType('resume', PARAM_BOOL);
        $mform->setDefault('resume', true);
        $mform->addHelpButton('resume', 'resume', 'video');

        $mform->addElement('advcheckbox', 'comments', get_string('allowcomments', 'video'));
        $mform->setType('comments', PARAM_BOOL);
        $mform->addHelpButton('comments', 'allowcomments', 'video');

        $mform->addElement('advcheckbox', 'preventforwardseeking', get_string('preventforwardseeking', 'video'));
        $mform->setType('preventforwardseeking', PARAM_BOOL);
        $mform->addHelpButton('preventforwardseeking', 'preventforwardseeking', 'video');

        $mform->addElement('advcheckbox', 'hidecontrols', get_string('hidecontrols', 'video'));
        $mform->setType('hidecontrols', PARAM_BOOL);

        $mform->addElement('advcheckbox', 'fullscreenenabled', get_string('fullscreenenabled', 'video'));
        $mform->setType('fullscreenenabled', PARAM_BOOL);

        $mform->addElement('advcheckbox', 'loopvideo', get_string('loop', 'video'));
        $mform->setType('loop', PARAM_BOOL);

        $controloptions = [];
        foreach (video_get_controls_default_values() as $controloptionname => $defaultvalue) {
            $controloptions[] = $mform->createElement(
                'advcheckbox',
                $controloptionname,
                get_string("control_$controloptionname", 'video')
            );
        }
        $mform->addGroup($controloptions, 'controls', get_string('showcontrols', 'video'), '<br>');
        $mform->addHelpButton('controls', 'showcontrols', 'video');
        foreach (video_get_controls_default_values() as $controloptionname => $defaultvalue) {
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

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    /**
     * Add custom completion rules.
     *
     * @return array Array of string IDs of added items, empty array if none
     * @throws coding_exception
     */
    public function add_completion_rules(): array {
        $mform =& $this->_form;

        $mform->addElement('checkbox', 'completiononplay', get_string('completiononplay', 'video'));
        $mform->setType('completiononplay', PARAM_BOOL);

        $group = [
            $mform->createElement('checkbox', 'completiononpercent', '', get_string('completiononpercent', 'video')),
            $mform->createElement('text', 'completionpercent', '', ['size' => 3]),
        ];
        $mform->setType('completionpercent', PARAM_INT);
        $mform->addGroup($group, 'completiononpercentgroup', '', null, false);
        $mform->disabledIf('completionpercent', 'completiononpercent');

        $group = [
            $mform->createElement('checkbox', 'completiononviewtime', '', get_string('completiononviewtime', 'video')),
            $mform->createElement('duration', 'completionviewtime'),
        ];
        $mform->setType('completionviewtime', PARAM_INT);
        $mform->addGroup($group, 'completionviewtimegroup', '', null, false);
        $mform->disabledIf('completionviewtime[number]', 'completiononviewtime');
        $mform->disabledIf('completionviewtime[timeunit]', 'completiononviewtime');

        return ['completiononplay', 'completiononpercentgroup', 'completionviewtimegroup'];
    }

    /**
     * Check if custom rules are enabled.
     * @param array $data
     * @return bool
     */
    public function completion_rule_enabled($data): bool {
        return (!empty($data['completiononplay'])) ||
            (!empty($data['completionpercent'])) ||
            (!empty($data['completionviewtime']));
    }

    /**
     * Form submission validation.
     * @param array $data
     * @param array $files
     * @return array
     * @throws coding_exception
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        $controlnames = array_keys(video_get_controls_default_values());

        foreach ($data['controls'] as $name => $value) {
            if (!in_array($name, $controlnames)) {
                $errors['controls'] = get_string('invalidcontrol', 'video');
            }
        }

        return $errors;
    }

    /**
     * Transform values before they are set in the form.
     * @param array $defaultvalues
     * @return void
     */
    public function data_preprocessing(&$defaultvalues): void {
        parent::data_preprocessing($defaultvalues);

        if ($this->current->instance) {
            // Editing existing instance - copy existing files into draft area.
            $draftitemid = file_get_submitted_draft_itemid('videofile');
            file_prepare_draft_area(
                $draftitemid,
                $this->context->id,
                'mod_video',
                'videofiles',
                $this->current->id,
                ['subdirs' => 0, 'maxbytes' => -1, 'maxfiles' => 1]
            );
            $defaultvalues['videofile'] = $draftitemid;

            if ($this->current->controls) {
                foreach (json_decode($this->current->controls, true) as $name => $value) {
                    $defaultvalues["controls[$name]"] = $value;
                }
            }
        }

        $defaultvalues['completiononplay'] = !empty($defaultvalues['completiononplay']) ? 1 : 0;
        $defaultvalues['completiononpercent'] = !empty($defaultvalues['completionpercent']) ? 1 : 0;
        $defaultvalues['completiononviewtime'] = !empty($defaultvalues['completionviewtime']) ? 1 : 0;

        if (isset($defaultvalues['type'])) {
            $source = video_source::get_video_source_by_type($defaultvalues['type']);
            $source->data_preprocessing($defaultvalues);
        }
    }

    /**
     * Allows module to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     * Only available on moodleform_mod.
     * @param stdClass $data the form data to be modified.
     * @return void
     */
    public function data_postprocessing($data): void {
        parent::data_postprocessing($data);
        // Turn off completion settings if the checkboxes aren't ticked.
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && $data->completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completiononplay) || !$autocompletion) {
                $data->completiononplay = 0;
            }
            if (empty($data->completiononpercent) || !$autocompletion) {
                $data->completiononpercent = 0;
            }
            if (empty($data->completiononviewtime) || !$autocompletion) {
                $data->completiononviewtime = 0;
            }
        }

        $source = video_source::get_video_source_by_type($data->type);
        $source->data_postprocessing($data);
    }
}
