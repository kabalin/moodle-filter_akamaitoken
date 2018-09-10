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
     * Performs filtering.
     *
     * @param string $text some HTML content to process.
     * @param array $options options passed to the filters
     * @return string tokenized URL.
     */
    public function filter($text, array $options = array()) {
        global $CFG, $PAGE;

        if (!is_string($text) or empty($text)) {
            // non string data can not be filtered anyway
            return $text;
        }

        if (stripos($text, '</a>') === false && stripos($text, '</video>') === false && stripos($text, '</audio>') === false) {
            // Performance shortcut - if there are no </a>, </video> or </audio> tags, nothing can match.
            return $text;
        }

        // Looking for tags.
        $matches = preg_split('/(<[^>]*>)/i', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        if (!$matches) {
            return $text;
        }

        // Regex to find Akamai Media Services domain in an <a> tag.
        $domain = get_config('filter_akamaitoken', 'ondemanddomain');
        $re = '~<a\s[^>]*href="([^"]*(?:' . preg_quote($domain) . ')[^"]*)"[^>]*>([^>]*)</a>~is';

        $newtext = '';
        $validtag = '';
        $tagname = '';
        $sizeofmatches = count($matches);

        // We iterate through the given string to find valid <a> tags
        // and build them so that the callback function can check it for
        // embedded content. Then we rebuild the string.
        foreach ($matches as $idx => $tag) {
            if (preg_match('|</'.$tagname.'>|', $tag) && !empty($validtag)) {
                $validtag .= $tag;

                // Given we now have a valid <a> tag to process it's time for
                // ReDoS protection. Stop processing if a word is too large.
                if (strlen($validtag) < 4096) {
                    if ($tagname === 'a') {
                        $processed = preg_replace_callback($re, array($this, 'callback'), $validtag);
                    } else {
                        // For audio and video tags we just process them without precheck for embeddable markers.
                        $processed = $this->process_media_tag($validtag);
                    }
                }
                // Rebuilding the string with our new processed text.
                $newtext .= !empty($processed) ? $processed : $validtag;
                // Wipe it so we can catch any more instances to filter.
                $validtag = '';
                $processed = '';
            } else if (preg_match('/<(a|video|audio)\s[^>]*/', $tag, $tagmatches) && $sizeofmatches > 1 &&
                    (empty($validtag) || $tagname === strtolower($tagmatches[1]))) {
                // Looking for a starting tag. Ignore tags embedded into each other.
                $validtag = $tag;
                $tagname = strtolower($tagmatches[1]);
            } else {
                // If we have a validtag add to that to process later,
                // else add straight onto our newtext string.
                if (!empty($validtag)) {
                    $validtag .= $tag;
                } else {
                    $newtext .= $tag;
                }
            }
        }

        // Return the same string except processed by the above.
        return $newtext;
    }

    /**
     * Replace link with embedded content, if supported.
     *
     * @param array $matches
     * @return string
     */
    private function callback(array $matches) {
        global $CFG, $PAGE;

        // Check if we ignore it.
        if (preg_match('/class="[^"]*nomediaplugin/i', $matches[0])) {
            return $matches[0];
        }

        // Get name.
        $name = trim($matches[2]);
        if (empty($name) or strpos($name, 'http') === 0) {
            $name = ''; // Use default name.
        }

        // Split provided URL into alternatives.
        $mediamanager = core_media_manager::instance();
        $urls = $mediamanager->split_alternatives($matches[1], $width, $height);

        $options = [core_media_manager::OPTION_ORIGINAL_TEXT => $matches[0]];
        return $this->embed_alternatives($urls, $name, $width, $height, $options);
    }

    /**
     * Renders media files (audio or video) using suitable embedded player.
     *
     * Wrapper for {@link core_media_manager::embed_alternatives()}
     *
     * @param array $urls Array of moodle_url to media files
     * @param string $name Optional user-readable name to display in download link
     * @param int $width Width in pixels (optional)
     * @param int $height Height in pixels (optional)
     * @param array $options Array of key/value pairs
     * @return string HTML content of embed
     */
    protected function embed_alternatives($urls, $name, $width, $height, $options) {
        var_dump($urls);
        return parent::embed_alternatives($urls, $name, $width, $height, $options);
    }
}
