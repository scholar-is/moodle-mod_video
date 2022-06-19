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
 * @copyright  2022 Joseph Conradt <joeconradt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_video\external;

use external_api;
use external_function_parameters;
use external_single_structure;
use mod_video\persistent\video_session;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

/**
 * Webservice functions.
 *
 * @package    mod_video
 * @copyright  2022 Joseph Conradt <joeconradt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

    /**
     * Returns description of create_session() parameters.
     *
     * @return external_function_parameters
     */
    public static function create_session_parameters() {
        return new external_function_parameters([
            'cmid' => new \external_value(PARAM_INT, 'Video course module ID')
        ]);
    }

    /**
     * Create new video session for user.
     *
     * @param $cmid
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \module_not_found
     * @throws \restricted_context_exception
     */
    public static function create_session($cmid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::create_session_parameters(), [
            'cmid' => $cmid
        ]);
        $context = \context_module::instance($params['cmid']);
        self::validate_context($context);

        if (!get_coursemodule_from_id('video', $params['cmid'])) {
            throw new \module_not_found($params['cmid']);
        }

        $now = new \DateTime('now', \core_date::get_user_timezone_object(99));

        $newsession = (object)[
            'cmid' => $params['cmid'],
            'userid' => $USER->id,
            'watchtime' => 0,
            'lasttime' => 0,
            'maxtime' => 0,
            'watchpercent' => 0,
            'timecreated' => $now->getTimestamp(),
        ];

        $newsession->id = $DB->insert_record('video_session', $newsession);

        return [
            'session' => $newsession
        ];
    }

    /**
     * Returns description of create_session() result value.
     *
     * @return external_single_structure
     */
    public static function create_session_returns() {
        return new external_single_structure([
            'session' => video_session::get_external_description()
        ]);
    }

    /**
     * Returns description of record_session_updates() parameters.
     *
     * @return external_function_parameters
     */
    public static function record_session_updates_parameters() {
        return new external_function_parameters([
            'sessionid' => new \external_value(PARAM_INT, 'Session to record updates to', VALUE_DEFAULT),
            'timeelapsed' => new \external_value(PARAM_INT, 'Increment watch time by seconds', VALUE_DEFAULT),
            'currenttime' => new \external_value(PARAM_INT, 'Current video time', VALUE_DEFAULT),
            'currentpercent' => new \external_value(PARAM_FLOAT, 'Current video watch percentage', VALUE_DEFAULT)
        ]);
    }

    public static function record_session_updates($sessionid, $timeelapsed, $currenttime, $currentpercent) {
        global $DB;

        $params = self::validate_parameters(self::record_session_updates_parameters(), [
            'sessionid' => $sessionid,
            'timeelapsed' => $timeelapsed,
            'currenttime' => $currenttime,
            'currentpercent' => $currentpercent
        ]);

        if (!$session = $DB->get_record('video_session', ['id' => $params['sessionid']])) {
            throw new \session_not_found();
        }

        $context = \context_module::instance($session->cmid);
        self::validate_context($context);

        if (!is_null($params['timeelapsed'])) {
            $session->watchtime += intval($params['timeelapsed']);
        }

        if (!is_null($params['currenttime'])) {
            $session->lasttime = $params['currenttime'];
            if ($params['currenttime'] > $session->maxtime) {
                $session->maxtime = $params['currenttime'];
            }
        }

        if (!is_null($params['currentpercent']) && $params['currentpercent'] > $session->watchpercent) {
            $session->watchpercent = $params['currentpercent'];
        }

        $DB->update_record('video_session', $session);

        return [
            'session' => $session
        ];
    }

    /**
     * Returns description of record_session_updates() result value.
     *
     * @return external_single_structure
     */
    public static function record_session_updates_returns() {
        return new external_single_structure([
            'session' => video_session::get_external_description()
        ]);
    }
}