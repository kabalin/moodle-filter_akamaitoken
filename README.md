![Moodle Plugin CI](https://github.com/kabalin/moodle-filter_akamaitoken/workflows/Moodle%20Plugin%20CI/badge.svg)

moodle-filter_akamaitoken
=========================

This Moodle filter plugin enables viewing SMP protected HLS media stream
delivered by Akamai Adaptive Media Delivery service. It is generating and adding one-time
access Edge Authorization token to HLS stream URL, so that it is validated
by Edge server to authenticate user session and permit playback using the
media player plugin of your choice.

Plugin supports HLS streams provisioned using [Akamai Media Services on
Demand](https://learn.akamai.com/en-us/products/media_delivery/media_services_on_demand.html)
and [Akamai Media Services
Live](https://learn.akamai.com/en-us/products/media_delivery/media_services_live.html)
(i.e. via supported [Segmented Media Delivery Modes](https://learn.akamai.com/en-us/webhelp/adaptive-media-delivery/adaptive-media-delivery-implementation-guide/GUID-FA61EC80-6682-46D7-8E3D-9BCDBB90A5C5.html#GUID-FA61EC80-6682-46D7-8E3D-9BCDBB90A5C5))

For more details on Akamai Segmented Media Protection, please refer to [documentation](https://learn.akamai.com/en-us/webhelp/adaptive-media-delivery/adaptive-media-delivery-implementation-guide/GUID-C720FEF7-2FDE-469C-A7C4-6BD255729DD7.html).

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

Domain settings in streams configuration is used to determine which URL should
be treated for token Authorization and encryption key is used to generate
access token for this URL. You can configure any number of encryption key - domain
pairs. The common use case is to have one pair for media streams provisioned
by Akamai Media Services on Demand and one for Akamai Media Services Live.

### URL Syntax

Example of client-side URL Syntax for Akamai Media Services on Demand:

`http://example-vh.akamaihd.net/i/movies/example2a_,300000,500000,800000,1000000,_event1.mp4.csmil/master.m3u8`

The part of URL used for token generation (ACL) is:

`/i/movies/example2a_,300000,500000,800000,1000000,_event1.mp4.csmil*`

The final URL passed to player will be:

`http://example-vh.akamaihd.net/i/movies/example2a_,300000,500000,800000,1000000,_event1.mp4.csmil/master.m3u8?ndnts=<token>`

Example of client-side URL Syntax for Akamai Media Services Live:

`http://example-lh.akamaihd.net/i/event_1@49207/master.m3u8`.

The part of URL used for token generation (ACL) is:

`/i/event_1@49207*`

The final URL passed to player will be:

`http://example-lh.akamaihd.net/i/event_1@49207/master.m3u8?ndnts=<token>`

Credits
-------

* [Ecole hôtelière de Lausanne](https://www.ehl.edu/)
* David Owen, a founder of [Digotel Sàrl](https://www.digotel.com), provided expert advice on implemening this feature.
