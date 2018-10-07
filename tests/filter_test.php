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
 * @author    Ruslan Kabalin <ruslan.kabalin@gmail.com>
 * @copyright 2018 Ecole hôtelière de Lausanne {@link https://www.ehl.edu/}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/filter/akamaitoken/filter.php');


class filter_akamaitoken_testcase extends advanced_testcase {

    protected function setUp() {
        // Configure plugin.
        set_config('ondemandkey', 'a0a0a0a0a0a0a0a0', 'filter_akamaitoken');
        set_config('ondemanddomain', 'example-vh.akamaihd.net', 'filter_akamaitoken');
        set_config('livekey', 'a0a0a0a0a0a0a0a0', 'filter_akamaitoken');
        set_config('livedomain', 'example-lh.akamaihd.net', 'filter_akamaitoken');

        $this->resetAfterTest();
    }

    function test_filter_akamaitoken_not_matching() {
        $filterplugin = new filter_akamaitoken(null, array());
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
            $filtered = $filterplugin->filter($validurl);
            // The same link, no changes expected.
            $this->assertEquals($validurl, $filtered);
        }

        // Generate video tag with all URLs listed as sources.
        foreach ($urls as $url) {
            $sources[] = html_writer::empty_tag('source', array('src' => $url));
        }
        $attributes = array('controls' => 'true');
        $sources = implode("\n", $sources);
        $videotag = html_writer::tag('video', $sources, $attributes);
        $filtered = $filterplugin->filter($videotag);
        $this->assertEquals($videotag, $filtered);
    }

    function test_filter_akamaitoken_valid_a_tag() {
        $filterplugin = new filter_akamaitoken(null, array());
        $urls = array(
            'http://example-vh.akamaihd.net/i/movies/example2a_,300000,500000,800000,1000000,_event1.mp4.csmil/master.m3u8',
            'http://example-lh.akamaihd.net/i/movies/event1@49207.mp4.csmil/master.m3u8',
        );
        foreach ($urls as $url) {
            $validurl = html_writer::link($url, 'Watch this one');
            $filter = $filterplugin->filter($validurl);
            // Changes expected.
            $this->assertNotEquals($validurl, $filter);
            $this->assertContains('hdnts', $filter);
            $this->assertNotContains('ip%3D', $url);
        }
    }

    function test_filter_akamaitoken_valid_video_tag() {
        $filterplugin = new filter_akamaitoken(null, array());
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
        $filtered = $filterplugin->filter($videotag);
        $this->assertNotEquals($videotag, $filtered);

        // More deatiled look at sources.
        if (preg_match_all('/<source\b[^>]*\bsrc="(.*?)"/im', $filtered, $matches)) {
            foreach ($matches[1] as $url) {
                $this->assertContains('hdnts', $url);
                $this->assertNotContains('ip%3D', $url);
            }
        }
    }

    function test_filter_akamaitoken_valid_video_tag_restrict_ip() {
        set_config('restrictip', true, 'filter_akamaitoken');
        $filterplugin = new filter_akamaitoken(null, array());
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
        $filtered = $filterplugin->filter($videotag);
        $this->assertNotEquals($videotag, $filtered);

        // More deatiled look at sources.
        if (preg_match_all('/<source\b[^>]*\bsrc="(.*?)"/im', $filtered, $matches)) {
            foreach ($matches[1] as $url) {
                $this->assertContains('hdnts=', $url);
                $this->assertContains('ip%3D', $url);
            }
        }
    }

}
