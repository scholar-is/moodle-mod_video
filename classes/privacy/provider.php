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
 * Privacy API implementation.
 *
 * @package    mod_video
 * @copyright  2022 Joseph Conradt <joeconradt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_video\privacy;

use coding_exception;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;
use dml_exception;

/**
 * Privacy API implementation.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Get metadata.
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table('video_session', [
            'userid' => 'privacy:metadata:video_session:userid',
            'watchtime' => 'privacy:metadata:video_session:watchtime',
            'lasttime' => 'privacy:metadata:video_session:lasttime',
            'maxtime' => 'privacy:metadata:video_session:maxtime',
            'watchpercent' => 'privacy:metadata:video_session:watchpercent',
            'timecreated' => 'privacy:metadata:video_session:timecreated',
            'timemodified' => 'privacy:metadata:video_session:timemodified',
        ], 'privacy:metadata:video_session');

        return $collection;
    }

    /**
     * Get contexts for user.
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT c.id
            FROM {context} c
            JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            JOIN {video_session} vs ON vs.cmid = cm.id
            WHERE vs.userid = :userid";

        $params = [
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export user data.
     * @param approved_contextlist $contextlist
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
        $contextids = $contextlist->get_contextids();

        foreach ($contextids as $contextid) {
            $context = \context::instance_by_id($contextid);

            $records = $DB->get_records('video_session', [
                'userid' => $userid,
                'cmid' => $context->instanceid,
            ]);

            foreach ($records as $record) {
                $subpath = ['video_session', $record->id];
                writer::with_context($context)->export_data(
                    $subpath,
                    (object)[
                        'watchtime' => gmdate("H:i:s", $record->watchtime),
                        'lasttime' => gmdate("H:i:s", $record->lasttime),
                        'maxtime' => gmdate("H:i:s", $record->maxtime),
                        'watchpercent' => number_format($record->watchpercent * 100, 2) . '%',
                        'timecreated' => userdate($record->timecreated, '', $user->timezone),
                        'timemodified' => userdate($record->timemodified, '', $user->timezone),
                    ]
                );
            }
        }
    }

    /**
     * Delete all data.
     * @param \context $context
     * @return void
     * @throws dml_exception
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $DB->delete_records('video_session', ['cmid' => $context->instanceid]);
    }

    /**
     * Delete data for user.
     * @param approved_contextlist $contextlist
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        $contextids = $contextlist->get_contextids();

        foreach ($contextids as $contextid) {
            $context = \context::instance_by_id($contextid);

            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }

            $DB->delete_records('video_session', ['userid' => $userid, 'cmid' => $context->instanceid]);
        }
    }
}
