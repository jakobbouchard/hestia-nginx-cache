<?php

/**
 * Hestia Nginx Cache
 *
 * @package           Hestia_Nginx_Cache
 * @author            Jakob Bouchard
 * @license           GPL-3.0+
 *
 * @wordpress-plugin
 * Plugin Name:       Hestia Nginx Cache
 * Description:       Hestia Nginx Cache Integration for WordPress. Auto-purges the Nginx cache when needed.
 * Plugin URI:				https://github.com/jakobbouchard/hestia-nginx-cache
 * Version:           2.2.3
 * Requires at least: 4.8
 * Requires PHP:      5.4
 * Author:            Jakob Bouchard
 * Author URI:        https://jakobbouchard.dev
 * Text Domain:       hestia-nginx-cache
 * License:           GPL v3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 */

if (!defined('ABSPATH')) {
	exit();
}

class Hestia_Nginx_Cache
{
	public const NAME = 'hestia-nginx-cache';
	public const VERSION = '2.2.3';

	private static $instance = null;
	public static $plugin_basename = null;
	public static $is_configured = false;

	public $admin = null;
	public $site_health = null;

	private $purge = false;

	private $events = array(
		'publish_post',
		'edit_post',
		'save_post',
		'post_updated',
		'deleted_post',
		'trashed_post',
		'wp_trash_post',
		'add_attachment',
		'edit_attachment',
		'attachment_updated',
		'publish_phone',
		'clean_post_cache',
		'pingback_post',
		'comment_post',
		'edit_comment',
		'delete_comment',
		'wp_insert_comment',
		'wp_set_comment_status',
		'trackback_post',
		'transition_post_status',
		'transition_comment_status',
		'wp_update_nav_menu',
		'switch_theme',
		'permalink_structure_changed',
	);

	private function __construct()
	{
		$this::$plugin_basename = plugin_basename(__FILE__);
		$options = get_option(self::NAME);
		if ($options && isset($options['access_key']) && $options['access_key'] != '' && isset($options['secret_key']) && $options['secret_key'] != '') {
			$this::$is_configured = true;
		}
		add_action('init', array($this, 'init'));
		add_action('shutdown', array($this, 'purge'));
	}

	public static function get_instance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function init()
	{
		load_plugin_textdomain(self::NAME, false, self::NAME . '/languages');

		// Register site_health
		if (is_admin()) {
			require_once __DIR__ . '/includes/site_health.php';
			$this->site_health = new Hestia_Nginx_Cache_Site_Health();
		}

		// Do not allow logged in users / subscribers to clear cache
		if (current_user_can('edit_posts')) {
			require_once __DIR__ . '/includes/admin.php';
			$this->admin = new Hestia_Nginx_Cache_Admin();
		}

		foreach ($this->events as $event) {
			add_action($event, array($this, 'consolidate_purge'));
		}
	}

	public function consolidate_purge()
	{
		$this->purge = true;
	}

	public function purge($force = false)
	{
		if ($this->purge !== true && !$force) {
			return false;
		}
		if (!$this::$is_configured) {
			return false;
		}

		$options = get_option(self::NAME);

		// Server credentials
		$hostname = $options['host'];
		$port = $options['port'];
		$access_key = $options['access_key'];
		$secret_key = $options['secret_key'];

		// Info to purge
		$username = $options['user'];
		$domain = $options['domain'];

		// Prepare POST query
		$body = array(
			'hash' => "$access_key:$secret_key",
			'returncode' => 'yes',
			'cmd' => 'v-purge-nginx-cache',
			'arg1' => $username,
			'arg2' => $domain,
		);

		return wp_remote_post("https://$hostname:$port/api/", array('sslverify' => false, 'timeout' => 60, 'body' => $body));
	}
}

Hestia_Nginx_Cache::get_instance();
