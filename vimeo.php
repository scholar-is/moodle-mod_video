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


require_once(__DIR__ . '/../../config.php');
require_once("{$CFG->libdir}/adminlib.php");

admin_externalpage_setup('customreports', null, [], new moodle_url('/mod/video/authorize.php'));

$PAGE->set_context(context_system::instance());

$renderer = $PAGE->get_renderer('video');

echo $OUTPUT->header();

echo $renderer->render(new \videosource_vimeo\component\vimeo_setup_wizard());

echo $OUTPUT->footer();
