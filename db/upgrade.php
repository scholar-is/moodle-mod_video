<?php
// This file keeps track of upgrades to
// the survey module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installation to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the methods of database_manager class
//
// Please do not forget to use upgrade_set_timeout()
// before any action that may take longer time to finish.

defined('MOODLE_INTERNAL') || die();

function xmldb_video_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2022060600) {

        // Define field youtubeurl to be added to video.
        $table = new xmldb_table('video');
        $field = new xmldb_field('youtubeurl', XMLDB_TYPE_CHAR, '1000', null, null, null, null, 'type');

        // Conditionally launch add field youtubeurl.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field vimeourl to be added to video.
        $table = new xmldb_table('video');
        $field = new xmldb_field('vimeourl', XMLDB_TYPE_CHAR, '1000', null, null, null, null, 'youtubeurl');

        // Conditionally launch add field vimeourl.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field externalurl to be added to video.
        $table = new xmldb_table('video');
        $field = new xmldb_field('externalurl', XMLDB_TYPE_CHAR, '1000', null, null, null, null, 'vimeourl');

        // Conditionally launch add field externalurl.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field url to be dropped from video.
        $table = new xmldb_table('video');
        $field = new xmldb_field('url');

        // Conditionally launch drop field url.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Video savepoint reached.
        upgrade_mod_savepoint(true, 2022060600, 'video');
    }

    if ($oldversion < 2022061200) {

        // Define field youtubeid to be added to video.
        $table = new xmldb_table('video');
        $field = new xmldb_field('youtubeid', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'youtubeurl');

        // Conditionally launch add field youtubeid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field vimeoid to be added to video.
        $table = new xmldb_table('video');
        $field = new xmldb_field('vimeoid', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'vimeourl');

        // Conditionally launch add field vimeoid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Video savepoint reached.
        upgrade_mod_savepoint(true, 2022061200, 'video');
    }

    if ($oldversion < 2022061201) {

        // Define field videoid to be added to video.
        $table = new xmldb_table('video');
        $field = new xmldb_field('videoid', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'type');

        // Conditionally launch add field videoid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Video savepoint reached.
        upgrade_mod_savepoint(true, 2022061201, 'video');
    }

    return true;
}
