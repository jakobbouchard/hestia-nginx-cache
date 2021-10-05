=== Hestia Nginx Cache ===
Contributors: jakobbouchard
Tags: cache, caching, wp-cache, flush, purge, hestia, hestiacp, nginx
Requires at least: 4.8
Tested up to: 5.8.1
Requires PHP: 5.4
Stable tag: 1.1.0
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

I might fix it at some point in the future by building a purge queue instead and trying to remove duplicate events, or by adding a rate limit.

= Can I enable/disable caching using the plugin? =

Not as of right now, but I might consider adding it in the future.

= Can I manually purge the cache using the plugin? =

Yes, you can. Once the plugin is installed and activated, you will see a "Purge Hestia Nginx Cache" button in the admin bar.

= I am experiencing issues with the plugin. What do I do? =

You can try to resolve the problem by purging the cache, deactivating the plugin, or disabling Nginx caching in Hestia.

If you are using Cloudflare and get an error when purging the cache, enter the hostname of the Hestia install in the settings, not the site URL.

= Your code sucks! =

That's not a question, but okay! I don't have much experience with programming WordPress plugins, so the code might be shit at some places. I might refactor the plugin at some point, but for now it works.

== Changelog ==

= 1.1.0 =
* Changed the way purges are done, so that they are done only once per post instead of 10+ times.
* Hide password in frontend.

= 1.0.0 =
* Initial release.
