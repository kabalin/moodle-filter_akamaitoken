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
 * Akamai Media Services Authorization Token stream protection streams settings
 *
 * @package   filter_akamaitoken
 * @author    Ruslan Kabalin <ruslan.kabalin@gmail.com>
 * @copyright 2018 Ecole hôtelière de Lausanne {@link https://www.ehl.edu/}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('filterakamaitokenstreams');
$PAGE->set_context(context_system::instance());

$streamsconfig = [];
if (!empty(get_config('filter_akamaitoken', 'streams'))) {
    $streamsconfig = json_decode(get_config('filter_akamaitoken', 'streams'), true);
}
$mform = new filter_akamaitoken\form\streams(null, ['streamsconfig' => $streamsconfig]);

if ($mform->is_cancelled()) {
    // Reload, so we have original values populated.
    redirect($PAGE->url);
}

if ($data = $mform->get_data()) {
    // Saving configuration.
    $mform->process($data);
    redirect($PAGE->url);
} else {
    // Restoring settings.
    $data = [];
    foreach ($streamsconfig as $stream) {
        $key = key($stream);
        $data['key'][] = $key;
        $data['domain'][] = $stream[$key];
    }
    $mform->set_data($data);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('streamssettings', 'filter_akamaitoken'));
echo $mform->render();
echo $OUTPUT->footer();
