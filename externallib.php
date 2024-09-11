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

    // Functions to handle xAPI data
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

    // Functions to get is Teacher or is Admin
    public static function is_teacher_or_admin_parameters() {
        return new external_function_parameters(
            array('courseid' => new external_value(PARAM_RAW, 'The course ID'))
        );
    }

    public static function is_teacher_or_admin($courseid) {
        self::validate_parameters(self::is_teacher_or_admin_parameters(),
                array('courseid' => $courseid));

        // Sanitize $courseid
        $courseid = intval($courseid); // Convert to integer for basic validation

        // Get the course context
        $context = context_course::instance($courseid);

        // Check if the user is a teacher or admin in the course
        if (has_capability('moodle/course:update', $context) || is_siteadmin()) {
            return true;
        } else {
            return false;
        }
    }

    public static function is_teacher_or_admin_returns() {
        return new external_value(PARAM_BOOL, 'Is teacher or admin');
    }
    
    // Functions to generate a magic login token
    public static function generate_magic_login_token_parameters() {
        return new external_function_parameters(
            array('courseid' => new external_value(PARAM_RAW, 'The course ID'))
        );
    }

    public static function generate_magic_login_token($courseid) {
        self::validate_parameters(self::generate_magic_login_token_parameters(),
                array('courseid' => $courseid));

        // Sanitize $courseid
        $courseid = intval($courseid); // Convert to integer for basic validation

        // Get the course context
        $context = context_course::instance($courseid);

        // Check if the user is a teacher or admin in the course
        if (has_capability('moodle/course:update', $context) || is_siteadmin()) {

             // Get settings
            $externalpath = get_config('local_openlrs', 'externalpath');
            $secretkey = get_config('local_openlrs', 'secretkey');
            $consumerid = get_config('local_openlrs', 'consumerid');

            if (empty($externalpath) || empty($secretkey) || empty($consumerid)) {
                throw new moodle_exception('Missing required OpenLRS configuration');
            }

            if (empty($courseid)) {
                throw new moodle_exception('Invalid course ID');
            }

            $message = [
                'courseId' => (string)$courseid,
                'consumerId' => $consumerid,
            ];

            if (empty($message['courseId']) || empty($message['consumerId'])) {
                throw new moodle_exception('Invalid course or consumer data');
            }

            $signature = hash_hmac('sha1', json_encode($message), $secretkey);

            $externalpathtempusercreate = $externalpath . 'lrs/create_temp_user';

            $ch = curl_init($externalpathtempusercreate );
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'X-Signature: ' . $signature
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Initialize token
            $token = false;

            if ($http_code == 200) {
                $data = json_decode($response, true);
                if (isset($data['success']) && $data['success'] && isset($data['user']) ) {
                    $token = [
                        'user' => $data['user'],
                        'lrsUrl' => $externalpath
                    ];
                }
            }

            if ($http_code != 200 || !$token) {
                throw new moodle_exception('Failed to create temp user: ' . $response);
            }

            return json_encode($token);
        } else {
            return false;
        }
    }

    public static function generate_magic_login_token_returns() {
        return new external_value(PARAM_RAW, 'Magic login token details in JSON format');
    }
}