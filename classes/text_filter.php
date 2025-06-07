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

namespace filter_akamaitoken;

use core_media_manager;

require_once($CFG->dirroot.'/filter/akamaitoken/lib/akamai-token.php');

/**
 * Akamai Media Services Authorization Token stream protection filter.
 *
 * This filter enables viewing SMP protected HLS media stream delivered by
 * Akamai Media Services. It is generating and adding one-time access
 * Edge Authorization token to HLS stream URL, so that it is validated
 * by Edge server to authenticate user session and permit playback using the
 * media player plugin of your choice.
 *
 * @package   filter_akamaitoken
 * @author    Ruslan Kabalin <ruslan.kabalin@gmail.com>
 * @copyright 2018 Ecole hôtelière de Lausanne {@link https://www.ehl.edu/}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class text_filter extends \filter_mediaplugin\text_filter {
    /** @var array List of configured streaming services in host => key format. */
    private $streamsconfig = [];

    /**
     * Setup page with filter requirements and other prepare stuff.
     *
     * @param moodle_page $page The page we are going to add requirements to.
     * @param context $context The context which contents are going to be filtered.
     */
    public function setup($page, $context) {
        if (!empty(get_config('filter_akamaitoken', 'streams'))) {
            $streamsconfig = json_decode(get_config('filter_akamaitoken', 'streams'), true);
            foreach ($streamsconfig as $stream) {
                $key = key($stream);
                $host = $stream[$key];
                $this->streamsconfig[$host] = $key;
            }
        }
    }

    /**
     * This is a wrapper for {@see filter_mediaplugin::embed_alternatives}
     * that adds Edge Authorization token to HLS media URL provisioned by
     * Akamai Media Services.
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

        $tokenisedurls = array();
        foreach ($urls as $url) {
            if ($mediamanager->get_filename($url) !== 'master.m3u8') {
                // Not Akamai HLS stream.
                continue;
            }

            // Check if we deal with Akamai Media Services HLS stream of particular type.
            if (in_array($url->get_host(), array_keys($this->streamsconfig), true)) {
                // Prepare path for tokenization (remove trailing /master.m3u8).
                $parts = explode('/', $url->get_path());
                array_pop($parts);
                $path  = implode('/', $parts);

                // Add token parameter to URL.
                $key = $this->streamsconfig[$url->get_host()];
                $token = $this->generate_token($key, $path);

                // Populate tokenised URLs array in a form of 'original URL string' => 'tokenised URL string'.
                $origurl = $url->out();
                // Add token.
                $url->param('hdnts', $token);
                $tokenisedurls[$origurl] = $url->out();
            }
        }

        if (count($tokenisedurls)) {
            // We have added tokens to some URLs. We need to modify original ones.
            // Nothing complicated here regexp-wise, as we simply replace all "strings" we modified.
            return str_replace(array_keys($tokenisedurls), array_values($tokenisedurls),
                    $options[core_media_manager::OPTION_ORIGINAL_TEXT]);
        }
        // No changes made. Output original html.
        return $options[core_media_manager::OPTION_ORIGINAL_TEXT];
    }

    /**
     * EdgeAuth token generator.
     *
     * @param string $key encryption key
     * @param string $path path for using as ACL.
     * @return string EdgeAuth token.
     */
    private function generate_token($key, $path) {
        // Configure EdgeAuth token.
        $edgeconfig = new \Akamai_EdgeAuth_Config();
        $edgeconfig->set_window(get_config('filter_akamaitoken', 'window'));
        $edgeconfig->set_acl($path . '*');
        $edgeconfig->set_key($key);

        // Encode IP in token, if required. In some network configurations
        // this may cause an issue.
        if (get_config('filter_akamaitoken', 'restrictip')) {
            $edgeconfig->set_ip(getremoteaddr());
        }

        // Generate EdgeAuth token.
        $generator = new \Akamai_EdgeAuth_Generate();
        $token = $generator->generate_token($edgeconfig);
        return $token;
    }
}
