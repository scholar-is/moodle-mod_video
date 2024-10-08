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
 * Webservice functions.
 *
 * @package    mod_video
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_video\external;

use coding_exception;
use completion_info;
use context_module;
use core\invalid_persistent_exception;
use DateTime;
use dml_exception;
use external_api;
use external_function_parameters;
use external_single_structure;
use invalid_parameter_exception;
use mod_video\exception\module_not_found;
use mod_video\exception\session_not_found;
use mod_video\persistent\video_session;
use mod_video\video_source;
use moodle_exception;
use restricted_context_exception;
use videosource_vimeo\videosource\vimeo;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once("$CFG->libdir/externallib.php");

/**
 * Webservice functions.
 *
 * @package    mod_video
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {
    /**
     * Returns description of create_session() parameters.
     *
     * @return external_function_parameters
     */
    public static function create_session_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new \external_value(PARAM_INT, 'Video course module ID'),
        ]);
    }

    /**
     * Create new video session for user.
     * @param int $cmid
     * @return array
     * @throws invalid_persistent_exception
     * @throws \core_external\restricted_context_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws module_not_found
     * @throws moodle_exception
     */
    public static function create_session($cmid): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::create_session_parameters(), [
            'cmid' => $cmid,
        ]);
        $context = context_module::instance($params['cmid']);
        self::validate_context($context);

        if (!get_coursemodule_from_id('video', $params['cmid'])) {
            throw new module_not_found($params['cmid']);
        }

        $cm = get_coursemodule_from_id('video', $params['cmid'], 0, false, MUST_EXIST);
        $video = $DB->get_record('video', ['id' => $cm->instance], '*', MUST_EXIST);
        $course = get_course($cm->course);

        $now = new DateTime('now', \core_date::get_user_timezone_object(99));

        $newsession = (new video_session(0, (object)[
            'cmid' => $params['cmid'],
            'userid' => $USER->id,
            'watchtime' => 0,
            'lasttime' => 0,
            'maxtime' => 0,
            'watchpercent' => 0,
            'timecreated' => $now->getTimestamp(),
        ]))->create();

        // Update completion status.
        $completion = new completion_info($course);
        if ($completion->is_enabled($cm) && ($video->completiononplay)) {
            $completion->update_state($cm, COMPLETION_COMPLETE);
        }

        return [
            'session' => $newsession->to_record(),
        ];
    }

    /**
     * Returns description of create_session() result value.
     *
     * @return external_single_structure
     */
    public static function create_session_returns(): external_single_structure {
        return new external_single_structure([
            'session' => video_session::get_external_description(),
        ]);
    }

    /**
     * Returns description of record_session_updates() parameters.
     *
     * @return external_function_parameters
     */
    public static function record_session_updates_parameters(): external_function_parameters {
        return new external_function_parameters([
            'sessionid' => new \external_value(PARAM_INT, 'Session to record updates to', VALUE_DEFAULT),
            'timeelapsed' => new \external_value(PARAM_INT, 'Increment watch time by seconds', VALUE_DEFAULT),
            'currenttime' => new \external_value(PARAM_INT, 'Current video time', VALUE_DEFAULT),
            'currentpercent' => new \external_value(PARAM_FLOAT, 'Current video watch percentage', VALUE_DEFAULT),
        ]);
    }

    /**
     * Record session updates.
     * @param int $sessionid
     * @param int $timeelapsed
     * @param int $currenttime
     * @param float $currentpercent
     * @return array
     * @throws invalid_persistent_exception
     * @throws \core_external\restricted_context_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws session_not_found
     */
    public static function record_session_updates($sessionid, $timeelapsed, $currenttime, $currentpercent): array {
        global $DB;

        $params = self::validate_parameters(self::record_session_updates_parameters(), [
            'sessionid' => $sessionid,
            'timeelapsed' => $timeelapsed,
            'currenttime' => $currenttime,
            'currentpercent' => $currentpercent,
        ]);

        if (!$session = video_session::get_record(['id' => $params['sessionid']])) {
            throw new session_not_found();
        }

        $context = context_module::instance($session->get('cmid'));
        self::validate_context($context);

        $cm = get_coursemodule_from_id('video', $session->get('cmid'), 0, false, MUST_EXIST);
        $video = $DB->get_record('video', ['id' => $cm->instance], '*', MUST_EXIST);
        $course = get_course($cm->course);

        if (!is_null($params['timeelapsed'])) {
            $session->set('watchtime', intval($session->get('watchtime')) + intval($params['timeelapsed']));
        }

        if (!is_null($params['currenttime'])) {
            $session->set('lasttime', $params['currenttime']);
            if ($params['currenttime'] > $session->get('maxtime')) {
                $session->set('maxtime', $params['currenttime']);
            }
        }

        if (!is_null($params['currentpercent']) && $params['currentpercent'] > $session->get('watchpercent')) {
            $session->set('watchpercent', round($params['currentpercent'], 2));
        }

        $session->update();

        // Update completion status.
        $completion = new completion_info($course);
        if ($completion->is_enabled($cm) && ($video->completiononplay)) {
            $completion->update_state($cm, COMPLETION_COMPLETE);
        }

        return [
            'session' => $session->to_record(),
        ];
    }

    /**
     * Returns description of record_session_updates() result value.
     *
     * @return external_single_structure
     */
    public static function record_session_updates_returns(): external_single_structure {
        return new external_single_structure([
            'session' => video_session::get_external_description(),
        ]);
    }

    /**
     * Returns description of record_session_updates() parameters.
     *
     * @return external_function_parameters
     */
    public static function query_videos_parameters(): external_function_parameters {
        return new external_function_parameters([
            'query' => new \external_value(PARAM_TEXT, 'Video search query.'),
            'videosourcetype' => new \external_value(PARAM_TEXT, 'Search this video source'),
        ]);
    }

    /**
     * Record session updates.
     * @param string $query
     * @param string $videosourcetype
     * @return array
     * @throws \core_external\restricted_context_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function query_videos($query, $videosourcetype): array {
        $params = self::validate_parameters(self::query_videos_parameters(), [
            'query' => $query,
            'videosourcetype' => $videosourcetype,
        ]);

        $context = \context_system::instance();
        self::validate_context($context);

        $videosource = null;
        foreach (video_source::get_video_sources() as $vs) {
            if ($vs->get_type() == $params['videosourcetype']) {
                $videosource = $vs;
            }
        }

        if (!$videosource) {
            throw new moodle_exception('cannot find video source ' . $params['videosourcetype']);
        }

        if (!$videosource->has_api()) {
            throw new moodle_exception('Video source does not support querying videos.');
        }

        return ['results' => $videosource->query($params['query'])];
    }

    /**
     * Returns description of record_session_updates() result value.
     *
     * @return external_single_structure
     */
    public static function query_videos_returns(): external_single_structure {
        return new external_single_structure([
            'results' => new external_single_structure([
                'videos' => new \external_multiple_structure(new external_single_structure([
                    'videoid' => new \external_value(PARAM_TEXT),
                    'title' => new \external_value(PARAM_TEXT),
                    'thumbnail' => new \external_value(PARAM_URL),
                    'description' => new \external_value(PARAM_TEXT),
                    'datecreated' => new \external_value(PARAM_TEXT),
                ])),
                'total' => new \external_value(PARAM_INT),
            ]),
        ]);
    }
}
