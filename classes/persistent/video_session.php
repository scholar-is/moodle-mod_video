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
 * Video session.
 *
 * @package    mod_video
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_video\persistent;

use core_competency\persistent;
use dml_exception;

/**
 * Video session.
 *
 * @package    mod_video
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class video_session extends persistent {
    /** Table name */
    const TABLE = 'video_session';

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties(): array {
        return [
            'cmid' => [
                'type' => PARAM_INT,
            ],
            'userid' => [
                'type' => PARAM_INT,
            ],
            'watchtime' => [
                'type' => PARAM_INT,
            ],
            'lasttime' => [
                'type' => PARAM_INT,
            ],
            'maxtime' => [
                'type' => PARAM_INT,
            ],
            'watchpercent' => [
                'type' => PARAM_FLOAT,
            ],
        ];
    }

    /**
     * Aggregate session data for a user.
     * @param int $cmid
     * @param int $userid
     * @return false|mixed
     * @throws dml_exception
     */
    public static function get_aggregate_values(int $cmid, int $userid) {
        global $DB;

        $aggregates = $DB->get_record_sql('
            SELECT
                SUM(vs.watchtime) as totalwatchtime,
                MAX(vs.maxtime) as maxtime,
                MAX(vs.watchpercent) as maxwatchpercent
            FROM {video_session} vs
            WHERE vs.userid = ? AND vs.cmid = ?
        ', [$userid, $cmid]);

        $lasttimerecord = $DB->get_record_sql('
            SELECT lasttime FROM {video_session} WHERE userid = ? AND cmid = ? ORDER BY id DESC LIMIT 1
        ', [$userid, $cmid]);

        $aggregates->lasttime = $lasttimerecord ? $lasttimerecord->lasttime : 0;

        return $aggregates;
    }

    /**
     * Get shape of API response.
     * @return \external_single_structure
     */
    public static function get_external_description(): \external_single_structure {
        return new \external_single_structure([
            'id' => new \external_value(PARAM_INT),
            'cmid' => new \external_value(PARAM_INT),
            'userid' => new \external_value(PARAM_INT),
            'watchtime' => new \external_value(PARAM_INT),
            'lasttime' => new \external_value(PARAM_INT),
            'maxtime' => new \external_value(PARAM_INT),
            'watchpercent' => new \external_value(PARAM_FLOAT),
            'timecreated' => new \external_value(PARAM_INT),
        ]);
    }
}
