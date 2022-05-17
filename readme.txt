=== Hestia Nginx Cache ===
Contributors: jakobbouchard
Tags: cache, caching, wp-cache, flush, purge, hestia, hestiacp, nginx
Requires at least: 4.8
Tested up to: 6.0
Requires PHP: 5.4
Stable tag: 1.2.1
License: GPL v3
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Purge Nginx cache automatically after making website changes. Uses the HestiaCP API.

== Description ==

This plugin automatically purges the Nginx cache after you make a website change such as updating a post or changing your theme.

The automatic purge functionality works for websites using HestiaCP.

== Installation ==

You install this plugin just like any other WordPress plugin.

== Configuration ==

To configure the plugin, go in Settings > Hestia Nginx Cache and fill in the fields!

== Frequently Asked Questions ==

= How do I generate an API key =

Run `v-generate-api-key` on your machine. Do note that API keys have *FULL* admin access.

= Why does the plugin clear the cache multiple times? =

This should have been fixed with 1.1.0, please update if you haven't done so.

= Can I manually purge the cache using the plugin? =

Yes, you can. Once the plugin is installed and activated, you will see a "Purge Hestia Nginx Cache" button in the admin bar.

= I am experiencing issues with the plugin. What do I do? =

You can try to resolve the problem by purging the cache, deactivating the plugin, or disabling Nginx caching in Hestia.

If you are using Cloudflare and get an error when purging the cache, enter the hostname of the Hestia install in the settings, not the site URL.

== Changelog ==

= 1.2.1 =
* Fix error notice not appearing when the cache failed to clear.

= 1.2.0 =
* Fix the admin bar button not working.
* Fix the notice not appearing.

= 1.1.0 =
* Changed the way purges are done, so that they are done only once per post instead of 10+ times.
* Hide password in frontend.

= 1.0.0 =
* Initial release.
