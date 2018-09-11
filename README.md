moodle-filter_akamaitoken
=========================

This Moodle filter plugin enables viewing SMP protected HLS media stream
delivered by Akamai Media Services. It is generating and adding one-time
access Edge Authorization token to HLS stream URL, so that it is validated
by Edge server to authenticate user session and permit playback using the
media player plugin of your choice.

Currently plugin supports adding tokens to HLS streams provisioned by
Akamai Media Services on Demand and Akamai Media Services Life.

For more details on Akamai Segmented Media Protection, please refer to [documentation](https://learn.akamai.com/en-us/webhelp/adaptive-media-delivery/adaptive-media-delivery-implementation-guide/GUID-2EFAD1C1-B5B8-4F66-A4CC-10428654CDF7.html).

Installation
------------

Plugin files need to be placed in `./filter/akamaitoken` directory in
Moodle, then you will need to go through installation process as normal by
loggining in as site admin.

Make sure that filter plugin is configured, enabled and located above
"Multimedia plugins" filter in the "Manage Filters" administration page, so
that it has a higher priority to process media files first.

How it works
------------

Plugin is extending
[`filter_mediaplugin`](https://github.com/moodle/moodle/tree/master/filter/mediaplugin)
filter plugin by adding a feature to identify URLs that belongs to Akamai
Media Services and extending them with Edge Authorization tokens prior to
passing to `core_media_manager` instance for embedding a player.

Domain settings in plugin configuration is used to determine which URL is
suitable for token Authorization.

### URL Syntax

Example of client-side URL Syntax for Akamai Media Services on Demand:

`http://example-vh.akamaihd.net/i/movies/example2a_,300000,500000,800000,1000000,_event1.mp4.csmil/master.m3u8`

The part of URL used for token generation (ACL) is:

`/i/movies/example2a_,300000,500000,800000,1000000,_event1.mp4.csmil*`

The final URL passed to player will be:

`http://example-vh.akamaihd.net/i/movies/example2a_,300000,500000,800000,1000000,_event1.mp4.csmil/master.m3u8?ndnts=<token>`

Example of client-side URL Syntax for Akamai Media Services Life:

`http://example-lh.akamaihd.net/i/event_1@49207/master.m3u8`.

The part of URL used for token generation (ACL) is:

`/i/event_1@49207*`

The final URL passed to player will be:

`http://example-lh.akamaihd.net/i/event_1@49207/master.m3u8?ndnts=<token>`

Credits
-------

[Ecole hôtelière de Lausanne](https://www.ehl.edu/)
