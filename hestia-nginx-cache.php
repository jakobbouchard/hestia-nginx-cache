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
 * Version:           2.0.0
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

	private static $instance = null;

	private $purge = false;

	private $events = array(
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

		if (is_admin()) {
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

		$options = get_option(self::NAME);
		if (!$options || !isset($options['api_key']) || $options['api_key'] == '' || !isset($options['api_secret']) || $options['api_secret'] == '') {
			return false;
		}

		// Server credentials
		$hostname = $options['host'];
		$port = $options['port'];
		$api_key = $options['api_key'];
		$api_secret = $options['api_secret'];

		// Info to purge
		$username = $options['user'];
		$domain = parse_url(get_site_url(), PHP_URL_HOST);

		// Prepare POST query
		$body = array(
			'hash' => $api_key . ':' . $api_secret,
			'returncode' => 'yes',
			'cmd' => 'v-purge-nginx-cache',
			'arg1' => $username,
			'arg2' => $domain,
		);

		$args = array(
			'method'    => 'POST',
			'blocking'  => true,
			'headers'   => array(
				'Content-type: application/x-www-form-urlencoded'
			),
			'body'      => $body,
		);

		return wp_remote_post('https://' . $hostname . ':' . $port . '/api/', $args);
	}
}

Hestia_Nginx_Cache::get_instance();
