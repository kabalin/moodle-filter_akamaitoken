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
 * Unit test for the filter_akamaitoken
 *
 * @package   filter_akamaitoken
 * @category  test
 * @author    Ruslan Kabalin <ruslan.kabalin@gmail.com>
 * @copyright 2018 Ecole hôtelière de Lausanne {@link https://www.ehl.edu/}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_akamaitoken;

use html_writer;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/filter/akamaitoken/filter.php');

/**
 * Testcase class.
 *
 * @package   filter_akamaitoken
 * @category  test
 * @covers    \filter_akamaitoken
 * @author    Ruslan Kabalin <ruslan.kabalin@gmail.com>
 * @copyright 2018 Ecole hôtelière de Lausanne {@link https://www.ehl.edu/}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_test extends \advanced_testcase {

    protected function setUp(): void {
        // Configure plugin.
        $configdata = [
            ['a0a0a0a0a0a0a0a0' => 'example-vh.akamaihd.net'],
            ['a1a1a1a1a1a1a1a1' => 'example-lh.akamaihd.net'],
        ];
        set_config('streams', json_encode($configdata), 'filter_akamaitoken');

        // Disable mediaplugin, so it won't interfere.
        filter_set_global_state('mediaplugin', TEXTFILTER_DISABLED);
        filter_set_global_state('akamaitoken', TEXTFILTER_ON);

        $this->resetAfterTest();
    }

    public function test_filter_akamaitoken_not_matching() {
        // On demand URL.
        $urls = array(
            'http://example-invalid.akamaihd.net/i/movies/example2a_,300000,500000,800000,1000000,_event1.mp4.csmil/master.m3u8',
            'http://example-lh.akamaihd.net/i/event_1@49207/notmaster.m3u8',
            'http://example-vh.akamaihd.net/i/event_1@49207',
            'http://moodle.org/testfile/test.mp4',
        );

        // Check each URL.
        foreach ($urls as $url) {
            $validurl = html_writer::link($url, 'Watch this one');
            $filtered = format_text($validurl, FORMAT_HTML);
            // The same link, no changes expected.
            $this->assertEquals($validurl, $filtered);
        }

        // Generate video tag with all URLs listed as sources.
        foreach ($urls as $url) {
            $sources[] = html_writer::empty_tag('source', array('src' => $url));
        }
        $sources = implode('', $sources);
        $videotag = html_writer::tag('video', $sources);
        $filtered = format_text($videotag, FORMAT_HTML);
        $this->assertEquals($videotag, $filtered);
    }

    public function test_filter_akamaitoken_valid_a_tag() {
        $urls = array(
            'http://example-vh.akamaihd.net/i/movies/example2a_,300000,500000,800000,1000000,_event1.mp4.csmil/master.m3u8',
            'http://example-lh.akamaihd.net/i/movies/event1@49207.mp4.csmil/master.m3u8',
        );
        foreach ($urls as $url) {
            $validurl = html_writer::link($url, 'Watch this one');
            $filtered = format_text($validurl, FORMAT_HTML);
            // Changes expected.
            $this->assertNotEquals($validurl, $filtered);
            $this->assertStringContainsString('hdnts', $filtered);
            $this->assertStringNotContainsString('ip%3D', $url);
        }
    }

    public function test_filter_akamaitoken_valid_video_tag() {
        $urls = array(
            'http://example-vh.akamaihd.net/i/movies/example2a_,300000,500000,800000,1000000,_event1.mp4.csmil/master.m3u8',
            'http://example-lh.akamaihd.net/i/movies/event1@49207.mp4.csmil/master.m3u8',
        );
        // Generate video tag with all URLs listed as sources.
        foreach ($urls as $url) {
            $sources[] = html_writer::empty_tag('source', array('src' => $url));
        }
        $attributes = array('controls' => 'true');
        $sources = implode("\n", $sources);
        $videotag = html_writer::tag('video', $sources, $attributes);
        $filtered = format_text($videotag, FORMAT_HTML);
        $this->assertNotEquals($videotag, $filtered);

        // More deatiled look at sources.
        if (preg_match_all('/<source\b[^>]*\bsrc="(.*?)"/im', $filtered, $matches)) {
            foreach ($matches[1] as $url) {
                $this->assertStringContainsString('hdnts', $url);
                $this->assertStringNotContainsString('ip%3D', $url);
            }
        }
    }

    public function test_filter_akamaitoken_valid_video_tag_restrict_ip() {
        set_config('restrictip', true, 'filter_akamaitoken');
        $urls = array(
            'http://example-vh.akamaihd.net/i/movies/example2a_,300000,500000,800000,1000000,_event1.mp4.csmil/master.m3u8',
            'http://example-lh.akamaihd.net/i/movies/event1@49207.mp4.csmil/master.m3u8',
        );
        // Generate video tag with all URLs listed as sources.
        foreach ($urls as $url) {
            $sources[] = html_writer::empty_tag('source', array('src' => $url));
        }
        $attributes = array('controls' => 'true');
        $sources = implode("\n", $sources);
        $videotag = html_writer::tag('video', $sources, $attributes);
        $filtered = format_text($videotag, FORMAT_HTML);
        $this->assertNotEquals($videotag, $filtered);

        // More deatiled look at sources.
        if (preg_match_all('/<source\b[^>]*\bsrc="(.*?)"/im', $filtered, $matches)) {
            foreach ($matches[1] as $url) {
                $this->assertStringContainsString('hdnts=', $url);
                $this->assertStringContainsString('ip%3D', $url);
            }
        }
    }

}
