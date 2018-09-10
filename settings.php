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
 * Akamai Media Services Authorization Token stream protection filter settings
 *
 * @package   filter_akamaitoken
 * @author    Ruslan Kabalin <ruslan.kabalin@gmail.com>
 * @copyright 2018 Ecole hôtelière de Lausanne {@link https://www.ehl.edu/}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('filter_akamaitoken/key',
            get_string('key', 'filter_akamaitoken'),
            get_string('keydesc', 'filter_akamaitoken'),
            ''));

    $settings->add(new admin_setting_configduration('filter_akamaitoken/window',
            get_string('window', 'filter_akamaitoken'),
            get_string('windowdesc', 'filter_akamaitoken'),
            300, PARAM_INT));

    $settings->add(new admin_setting_configtext('filter_akamaitoken/ondemanddomain',
            get_string('ondemanddomain', 'filter_akamaitoken'),
            get_string('ondemanddomaindesc', 'filter_akamaitoken'),
            '', PARAM_HOST));
    }
