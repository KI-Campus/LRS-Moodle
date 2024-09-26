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
 * This plugin provides access to Moodle data in form of analytics and reports in real time.
 *
 *
 * @package    local_openlrs
 * @copyright  2024 Ahmed Mujtaba Chang <mujtabachang@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once($CFG->libdir . '/filelib.php'); 


class local_openlrs_external extends external_api {

    public static function handle_data_parameters() {
        return new external_function_parameters(
            array('data' => new external_value(PARAM_RAW, 'The JSON data'))
        );
    }

    public static function handle_data($data) {
        self::validate_parameters(self::handle_data_parameters(),
                array('data' => $data));

        // Process your data here
        $decoded = json_decode($data);
        if (is_null($decoded)) {
            throw new invalid_parameter_exception('Invalid JSON');
        }

        // Check if data has context_id ($decoded->.metadata.session.context_id)
        if (!isset($decoded->metadata->session->context_id)) {
            throw new invalid_parameter_exception('Context ID is missing');
        }

        // Get settings
        $externalpath = get_config('local_openlrs', 'externalpath');
        $secretkey = get_config('local_openlrs', 'secretkey');
        $consumerid = get_config('local_openlrs', 'consumerid');

        // Append consumer ID to data as data.metadata.session.custom_consumer
        $decoded->metadata->session->custom_consumer = $consumerid;

        // Get Course Title from context_id
        $courseid = $decoded->metadata->session->context_id;

         // Sanitize $courseid
        $courseid = intval($courseid); // Convert to integer for basic validation
  
        if ($courseid <= 0) {
            throw new invalid_parameter_exception('Invalid course ID');
        }

        $course = get_course($courseid);

        if ($course) {
            $course_title = $course->fullname; // Full name of the course
        } else {
            $course_title = $courseid;
        }

        // Append the course title to data as data.metadata.session.course_title
        $decoded->metadata->session->context_title = $course_title;

        // Append the date in JavaScript format to data as data.metadata.createdAt
        $decoded->metadata->createdAt = date('c');

     
        // Encode data back to JSON string with JSON_UNESCAPED_SLASHES and JSON_UNESCAPED_UNICODE
        $data = json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Calculate the HMAC SHA1 signature
        $signature = hash_hmac('sha1', $data, $secretkey);

        // Send data to openLRS website
        $curl = new curl;

        $header = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data),
            'Accept: application/json',
            'X-Signature: ' . $signature
        );

        $curl->setHeader($header);
        
        $response = $curl->post($externalpath . "lrs", $data);
       
        return array('status' => 'success', 'response' => $response);
    }

    public static function handle_data_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, 'The status of data handling'),
                'response' => new external_value(PARAM_RAW, 'Response from the openLRS website')
            )
        );
    }
}
