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
 * Akamai Media Services Authorization Token stream protection upgrade.
 *
 * @package   filter_akamaitoken
 * @author    Ruslan Kabalin <ruslan.kabalin@gmail.com>
 * @copyright 2020 Ecole hôtelière de Lausanne {@link https://www.ehl.edu/}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade routine.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_filter_akamaitoken_upgrade($oldversion) {
    global $CFG;

    if ($oldversion < 2020092700) {
        $configdata = [];
        if (!empty(get_config('filter_akamaitoken', 'ondemandkey')) && !empty(get_config('filter_akamaitoken', 'ondemanddomain'))) {
            $configdata[] = [get_config('filter_akamaitoken', 'ondemandkey') => get_config('filter_akamaitoken', 'ondemanddomain')];
        }
        if (!empty(get_config('filter_akamaitoken', 'livekey')) && !empty(get_config('filter_akamaitoken', 'livedomain'))) {
            $configdata[] = [get_config('filter_akamaitoken', 'livekey') => get_config('filter_akamaitoken', 'livedomain')];
        }
        set_config('streams', json_encode($configdata), 'filter_akamaitoken');

        // Remove deprecated configuration.
        unset_config('ondemandkey', 'filter_akamaitoken');
        unset_config('ondemanddomain', 'filter_akamaitoken');
        unset_config('livekey', 'filter_akamaitoken');
        unset_config('livedomain', 'filter_akamaitoken');

        upgrade_plugin_savepoint(true, 2020092700, 'filter', 'akamaitoken');
    }

    return true;
}
