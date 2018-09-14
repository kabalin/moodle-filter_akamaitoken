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
 * Strings for component 'filter_akamaitoken', language 'en'
 *
 * @package   filter_akamaitoken
 * @author    Ruslan Kabalin <ruslan.kabalin@gmail.com>
 * @copyright 2018 Ecole hôtelière de Lausanne {@link https://www.ehl.edu/}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['restrictip'] = 'Restrict token to IP address';
$string['restrictipdesc'] = 'If enabled, token will be restricted to client IP address.';
$string['filtername'] = 'Akamai Media Services Authorization Token stream protection';
$string['liveconfig'] = 'Akamai Media Services Live stream';
$string['livedomain'] = 'Live streaming domain';
$string['livedomaindesc'] = 'Akamai Media Services Live streaming domain from which HLS media is served, e.g. example-lh.akamaihd.net. Leave empty if there is no Live Service';
$string['livekey'] = 'Live service key';
$string['livekeydesc'] = 'Encryption key required to produce EdgeAuth token for Live stream. It must be hexadecimal digit string of even-length.';
$string['ondemandconfig'] = 'Akamai Media Services On Demand stream';
$string['ondemanddomain'] = 'On Demand streaming domain';
$string['ondemanddomaindesc'] = 'Akamai Media Services on Demand streaming domain from which HLS media is served, e.g. example-vh.akamaihd.net. Leave empty if there is no On Demand Service';
$string['ondemandkey'] = 'On Demand service key';
$string['ondemandkeydesc'] = 'Encryption key required to produce EdgeAuth token for On Demand stream. It must be hexadecimal digit string of even-length.';
$string['privacy:metadata'] = 'The Akamai Media Services Authorization Token stream protection plugin does not store any personal data.';
$string['window'] = 'Validity time window';
$string['windowdesc'] = 'The number of seconds the token will be valid for.';