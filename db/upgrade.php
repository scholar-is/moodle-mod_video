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
 * Upgrade scripts.
 *
 * @package    mod_video
 * @copyright  2023 Scholaris <joe@scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Run upgrade scripts.
 * @param int $oldversion
 * @return true
 * @throws ddl_table_missing_exception
 * @throws ddl_exception
 * @throws moodle_exception
 */
function xmldb_video_upgrade(int $oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023102101) {

        // Define field comments to be added to video.
        $table = new xmldb_table('video');
        $field = new xmldb_field('comments', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'resume');

        // Conditionally launch add field comments.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Video savepoint reached.
        upgrade_mod_savepoint(true, 2023102101, 'video');
    }

    if ($oldversion < 2023110101) {

        // Define field comments to be added to video.
        $table = new xmldb_table('video');
        $field = new xmldb_field('descriptioninsummary', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'comments');

        // Conditionally launch add field comments.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Video savepoint reached.
        upgrade_mod_savepoint(true, 2023110101, 'video');
    }

    return true;
}
