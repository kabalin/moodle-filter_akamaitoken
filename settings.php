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

// New category.
$ADMIN->add('filtersettings', new admin_category('filterakamaitoken', new lang_string('filtername', 'filter_akamaitoken'), $filter->is_enabled() === false));

// Move plugin settings in the new category.
$settings->visiblename = new lang_string('filtersettings', 'filter_akamaitoken');
$ADMIN->add('filterakamaitoken', $settings);

// Add custom page for editing streaming domains.
$streams = new admin_externalpage('filterakamaitokenstreams',
        new lang_string('streamssettings', 'filter_akamaitoken'),
        new moodle_url('/filter/akamaitoken/streams.php'));
$ADMIN->add('filterakamaitoken', $streams);

// Add general settings.
if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configduration('filter_akamaitoken/window',
            get_string('window', 'filter_akamaitoken'),
            get_string('windowdesc', 'filter_akamaitoken'),
            300, PARAM_INT));

    $settings->add(new admin_setting_configcheckbox('filter_akamaitoken/restrictip',
            get_string('restrictip', 'filter_akamaitoken'),
            get_string('restrictipdesc', 'filter_akamaitoken'),
            false, PARAM_BOOL));
}

// Tell core we already added the settings structure.
$settings = null;