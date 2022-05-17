=== Hestia Nginx Cache ===
Contributors: jakobbouchard
Tags: cache, caching, wp-cache, flush, purge, hestia, hestiacp, nginx
Requires at least: 4.8
Tested up to: 6.0
Requires PHP: 5.4
Stable tag: 2.0.0
License: GPL v3
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Purge Nginx cache automatically after making website changes. Uses the *new* HestiaCP API, released in 1.6.0.

== Description ==

This plugin automatically purges the Nginx cache after you make a website change such as updating a post or changing your theme.

The automatic purge functionality works for websites using HestiaCP.

== Installation ==

You install this plugin just like any other WordPress plugin.

== Configuration ==

To configure the plugin, go in Settings > Hestia Nginx Cache and fill in the fields!

== Frequently Asked Questions ==

= How do I generate an API key =

Log in as your user (*not* the super admin), then go into your user settings. Click on the "Access Key" button, and create a new access key with the `purge-nginx-cache` permission. *Make sure to save the secret key, as you will only see it once.*

= Why does the plugin clear the cache multiple times? =

This should have been fixed with 1.1.0, please update if you haven't done so.

= Can I manually purge the cache using the plugin? =

Yes, you can. Once the plugin is installed and activated, you will see a "Purge Hestia Nginx Cache" button in the admin bar.

= I am experiencing issues with the plugin. What do I do? =

You can try to resolve the problem by purging the cache, deactivating the plugin, or disabling Nginx caching in Hestia.

If you are using Cloudflare and get an error when purging the cache, enter the hostname of the Hestia install in the settings, not the site URL.

== Changelog ==

= 2.0.0 =
* Add support for the new Hestia API, released in 1.6.0.
* Add settings link in the plugins list.
* Remove jQuery usage in the admin JS.
* Remove support for the legacy API, as it was quite unsecure for many reasons.

= 1.2.2 =
* Fix automatic purging.

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
