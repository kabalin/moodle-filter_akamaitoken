<?php
// This file is part of the mod_appointment plugin for Moodle - http://moodle.org/
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
 * Akamai Media Services Authorization Token stream protection streams settings form
 *
 * @package   filter_akamaitoken
 * @author    Ruslan Kabalin <ruslan.kabalin@gmail.com>
 * @copyright 2020 Ecole hôtelière de Lausanne {@link https://www.ehl.edu/}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_akamaitoken\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Class filter_akamaitoken\form\streams
 *
 * @package   filter_akamaitoken
 * @author    Ruslan Kabalin <ruslan.kabalin@gmail.com>
 * @copyright 2020 Ecole hôtelière de Lausanne {@link https://www.ehl.edu/}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class streams extends \moodleform {

    /**
     * Form definition
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('html', \html_writer::div(get_string('streamsinstruction', 'filter_akamaitoken'), 'py-4'));
        // Streams repeated elements.
        $repeatarray = [];
        $repeatarray[] = $mform->createElement('html', \html_writer::start_div('border rounded p-4 mb-3 br-1 bg-gray020'));
        $repeatarray[] = $mform->createElement('text', 'key', get_string('servicekey', 'filter_akamaitoken'), ['size' => '55']);
        $repeatarray[] = $mform->createElement('text', 'domain',
            get_string('servicedomain', 'filter_akamaitoken'), ['size' => '55']);
        $repeatarray[] = $mform->createElement('html', \html_writer::end_div());

        $repeatoptions = [
            'key' => ['type' => PARAM_ALPHANUM, 'rule' => 'alphanumeric', 'helpbutton' => ['servicekey', 'filter_akamaitoken']],
            'domain' => ['type' => PARAM_RAW, 'helpbutton' => ['servicedomain', 'filter_akamaitoken']],
        ];

        $repeats = (count($this->_customdata['streamsconfig'])) ?: 1;

        $this->repeat_elements($repeatarray, $repeats, $repeatoptions, 'service_repeats', 'addservice',
            1, get_string('addservice', 'filter_akamaitoken'), true);
        $this->add_action_buttons();
    }

    /**
     * Form validation
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (!empty($data['service_repeats'])) {
            for ($i = 0; $i < $data['service_repeats']; $i++) {
                // When both key and domain are empty, this implies service record deletion.
                // So we check "empty" in pair with other setting. Setting rule to "required"
                // won't help here, as it will make impossible to delete record.
                if (!empty($data['domain'][$i]) && $data['domain'][$i] !== clean_param($data['domain'][$i], PARAM_HOST)) {
                    // Not FQDN host supplied (we use same check as Akamai library).
                    $errors["domain[$i]"] = get_string('errordomain', 'filter_akamaitoken');
                }
                if (!empty($data['key'][$i])
                        && !(preg_match('/^[a-fA-F0-9]+$/', $data['key'][$i]) && (strlen($data['key'][$i]) % 2) == 0)) {
                    // Not valid HEX supplied .
                    $errors["key[$i]"] = get_string('errorkey', 'filter_akamaitoken');
                }
                if (empty($data['key'][$i]) && !empty($data['domain'][$i])) {
                    $errors["key[$i]"] = get_string('errorkey', 'filter_akamaitoken');
                    continue;
                }
                if (empty($data['domain'][$i]) && !empty($data['key'][$i])) {
                    $errors["domain[$i]"] = get_string('errordomain', 'filter_akamaitoken');
                    continue;
                }
            }
        }

        return $errors;
    }

    /**
     * Process data from submitted form.
     *
     * @param \stdClass $data
     */
    public function process(\stdClass $data) {
        $data = (array) $data;
        $configdata = [];
        if (!empty($data['service_repeats'])) {
            for ($i = 0; $i < $data['service_repeats']; $i++) {
                if (empty($data['key'][$i]) && empty($data['domain'][$i])) {
                    // One to delete. Skip.
                    continue;
                }
                $configdata[] = [$data['key'][$i] => $data['domain'][$i]];
            }
        }
        set_config('streams', json_encode($configdata), 'filter_akamaitoken');
    }
}
