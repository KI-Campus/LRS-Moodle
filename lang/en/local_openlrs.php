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

$string['pluginname'] = 'openLRS Plugin';

$string['externalpath'] = 'External openLRS Path';
$string['externalpath_desc'] = 'The URL of the external openLRS to which data will be sent. Make sure to include the trailing slash.';

$string['secretkey'] = 'Secret Key';
$string['secretkey_desc'] = 'The secret key used for secure communications.';
$string['consumerid'] = 'Consumer ID';
$string['consumerid_desc'] = 'The consumer ID used for identifying the consumer.';

$string['openlrs:view'] = 'View OpenLRS content';