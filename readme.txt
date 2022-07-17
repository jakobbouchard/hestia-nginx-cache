=== Hestia Nginx Cache ===
Contributors: jakobbouchard, jaapmarcus
Tags: cache, caching, wp-cache, flush, purge, hestia, hestiacp, nginx
Requires at least: 4.8
Tested up to: 6.0
Requires PHP: 5.4
Stable tag: 2.1.4
License: GPL v3
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Purge Nginx cache automatically after making website changes. Uses the *new* HestiaCP API, released in 1.6.0.

== Description ==

This plugin automatically purges the Nginx cache after you make a website change such as updating a post or changing your theme.

You also have the ability to manually purge the cache using a button in the WordPress admin bar.

== Installation ==

Manage your Hestia cache in a better way by using these steps to install the plugin.

= Installing and activating the plugin =

1. Install Hestia Nginx Cache automatically or by uploading the ZIP file.
2. Activate the plugin through the *Plugins* menu in WordPress.

= Getting API access =

If you are not the server administrator, you can skip this step.

1. Login as the system administrator account and navigate to the server configuration page.
2. Enable API access for all users in the Security > System section.
3. In the "Allowed IP addresses for API" box, enter both `127.0.0.1` and your server's public IP.

= Generating an Access key =

1. Log in as your regular user in the Hestia Control Panel and navigate to your user settings.
2. Click on the "Access Key" button, and create a new access key with the `purge-nginx-cache` permission. *Make sure to save the Secret key, as you will only see it once.*

If you do not see the "Access Key" button, it means that the feature is disabled on the server. If you are the administrator, refer to the previous section to enable it. If not, contact your server administrator so that they can enable the API access.

= Configuring the plugin =

1. Navigate to the plugin's settings in the Settings submenu in the sidebar.
2. Enter the Access key and Secret key from the previous section in the appropriate boxes.
3. For the hostname, use the HestiaCP same hostname you use to connect to the control panel. Same thing for the port.
4. For the username, enter the username that is owner of the domain name you are trying to clear.

== Frequently Asked Questions ==

= How do I generate an Access key? =

Refer to the Installation tab for information on how to configure the plugin.

= Can I manually purge the cache using the plugin? =

Yes, you can. Once the plugin is installed and activated, you will see a "Purge Nginx Cache" button in the admin bar.

= I am experiencing issues with the plugin. What do I do? =

You can try to resolve the problem by purging the cache, deactivating the plugin, or disabling Nginx caching in Hestia.

If you are using Cloudflare and get an error when purging the cache, enter the hostname of the Hestia install in the settings, not the site URL.

If your issues persist, do not hesitate to contact me via email!

== Changelog ==

= 2.1.4 =
* Fix domain field in admin
* Increase response timeout, in case the server is slow
* Remove SSL verification, in case your HestiaCP serves a self-signed certificate

= 2.1.3 =
* Fixed PHP 8.1 compatibility

= 2.1.2 =
* Fixed require at least "Can edit posts" permission to reset cache

= 2.1.0 =
* Add a setting to disable the admin bar button.
* Add a setting to change the admin bar button's text.
* Add a purge cache button in the settings page.
* Add a section to site health with the plugin's info.

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
