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

$functions = array(
    'local_openlrs_handle_data' => array(
        'classname'   => 'local_openlrs_external',
        'methodname'  => 'handle_data',
        'classpath'   => 'local/openlrs/externallib.php',
        'description' => 'Handle data',
        'type'        => 'write',
        'ajax'        => true,
    ),
);

$services = array(
    'openLRS Plugin service' => array(
        'functions' => array ('local_openlrs_handle_data'),
        'restrictedusers' => 0,
        'enabled'=>1,
    ),
);
