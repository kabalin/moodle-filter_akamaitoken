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
 * Akamai Media Services Authorization Token stream protection filter
 *
 * This filter adds Edge Autorization token to HLS media links located in
 * configured Akamai Media Services domain.
 *
 * @package   filter_akamaitoken
 * @author    Ruslan Kabalin <ruslan.kabalin@gmail.com>
 * @copyright 2018 Ecole hôtelière de Lausanne {@link https://www.ehl.edu/}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/filter/mediaplugin/filter.php');
require_once($CFG->dirroot.'/filter/akamaitoken/lib/akamai-token.php');

/**
 * Akamai Media Services Authorization Token stream protection filter class.
 *
 * @package   filter_akamaitoken
 * @author    Ruslan Kabalin <ruslan.kabalin@gmail.com>
 * @copyright 2018 Ecole hôtelière de Lausanne {@link https://www.ehl.edu/}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_akamaitoken extends filter_mediaplugin {

    /**
     * This is a wrapper for {@link filter_mediaplugin::embed_alternatives()}
     * that adds Edge Autorization token to HLS media URL in configured Akamai
     * Media Services domain.
     *
     * @param array $urls Array of moodle_url to media files
     * @param string $name Optional user-readable name to display in download link
     * @param int $width Width in pixels (optional)
     * @param int $height Height in pixels (optional)
     * @param array $options Array of key/value pairs
     * @return string HTML content of embed
     */
    protected function embed_alternatives($urls, $name, $width, $height, $options) {
        $mediamanager = core_media_manager::instance();
        foreach ($urls as $key => $url) {
            // Check if we deal with Akamai Media Services HLS stream.
            if ($url->get_host() === get_config('filter_akamaitoken', 'ondemanddomain') && $mediamanager->get_filename($url) === 'master.m3u8') {
                // Prepare path for tokenization (remove trailing /master.m3u8).
                $parts = explode('/', $url->get_path());
                array_pop($parts);
                $path  = implode('/', $parts);

                // Configure EdgeAuth token.
                $edgeconfig = new Akamai_EdgeAuth_Config();
                $edgeconfig->set_window(get_config('filter_akamaitoken', 'window'));
                $edgeconfig->set_acl($path . '*');
                $edgeconfig->set_key(get_config('filter_akamaitoken', 'key'));
                $edgeconfig->set_ip(getremoteaddr());

                // Generate EdgeAuth token.
                $generator = new Akamai_EdgeAuth_Generate();
                $token = $generator->generate_token($edgeconfig);

                // Add token parameter to URL.
                $url->param('hdnts', $token);
                $urls[$key] = $url;
            }
        }

        return parent::embed_alternatives($urls, $name, $width, $height, $options);
    }
}
