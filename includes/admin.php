<?php

/**
 * Hestia Nginx Cache
 *
 * @package           Hestia_Nginx_Cache
 * @author            Jakob Bouchard
 * @license           GPL-3.0+
 */

if (!defined('ABSPATH')) {
	exit();
}

class Hestia_Nginx_Cache_Admin
{
	private $plugin = null;

	public function __construct()
	{
		$this->plugin = Hestia_Nginx_Cache::get_instance();

		$options = get_option($this->plugin::NAME);

		// Add settings link to plugin actions.
		add_filter('plugin_action_links_' . $this->plugin::$plugin_basename, array($this, 'settings_link'));

		// Add settings page.
		add_action('admin_init', array($this, 'register_settings'));
		add_action('admin_menu', array($this, 'add_settings_page'));

		// Add menu button
		add_action('admin_print_styles', array($this, 'add_styles'));
		add_action('admin_enqueue_scripts', array($this, 'add_scripts'));
		if ($this->plugin::$is_configured && $options['show_adminbar_button']) {
			add_action('admin_bar_menu', array($this, 'add_purge_button'), PHP_INT_MAX);
		}

		// Handle purge requests.
		add_action('wp_ajax_hestia_nginx_cache_manual_purge', array($this, 'purge'));
	}

	function settings_link($links)
	{
		$url = esc_url(add_query_arg(
			'page',
			$this->plugin::NAME,
			get_admin_url() . 'options-general.php'
		));

		array_push($links, "<a href='$url'>" . __('Settings', $this->plugin::NAME) . '</a>');
		return $links;
	}

	public function register_settings()
	{
		register_setting(
			$this->plugin::NAME,
			$this->plugin::NAME,
			array('sanitize_callback' => array($this, 'validate_options'))
		);

		add_settings_section('api_settings', 'API Settings', array($this, 'api_settings_text'), $this->plugin::NAME);
		add_settings_field('api_setting_host', 'Server hostname', array($this, 'api_setting_host'), $this->plugin::NAME, 'api_settings');
		add_settings_field('api_setting_port', 'Server port', array($this, 'api_setting_port'), $this->plugin::NAME, 'api_settings');
		add_settings_field('api_setting_access_key', 'Access key', array($this, 'api_setting_access_key'), $this->plugin::NAME, 'api_settings');
		add_settings_field('api_setting_secret_key', 'Secret key', array($this, 'api_setting_secret_key'), $this->plugin::NAME, 'api_settings');
		add_settings_field('api_setting_user', 'Hestia username', array($this, 'api_setting_user'), $this->plugin::NAME, 'api_settings');
		add_settings_field('api_setting_domain', 'Domain to purge', array($this, 'api_setting_domain'), $this->plugin::NAME, 'api_settings');

		add_settings_section('plugin_settings', 'Plugin Settings', array($this, 'plugin_settings_text'), $this->plugin::NAME);
		add_settings_field('plugin_setting_show_purge_button', 'Show button in the admin bar', array($this, 'plugin_setting_show_purge_button'), $this->plugin::NAME, 'plugin_settings');
		add_settings_field('plugin_setting_purge_button_text', 'Admin bar button text', array($this, 'plugin_setting_purge_button_text'), $this->plugin::NAME, 'plugin_settings');
	}

	public function validate_options($input)
	{
		$options = get_option($this->plugin::NAME);

		$input['port'] = trim($input['port']);
		$input['secret_key'] = trim($input['secret_key']);
		$input['domain'] = parse_url($input['domain'], PHP_URL_HOST) ?: $input['domain'];

		if (!preg_match('/^\d{1,5}$/i', $input['port'])) {
			$input['port'] = '';
		}

		if ($input['secret_key'] == '#secret_key_PLACEHOLDER#') {
			$input['secret_key'] = $options['secret_key'];
		}

		return $input;
	}

	public function api_settings_text()
	{
		echo '<p>Here you can set all the options for the API. Please refer to the plugin\'s installation guide for information on how to generate an API key.</p>';
	}

	public function api_setting_host()
	{
		$options = get_option($this->plugin::NAME);
		$host = $this->plugin::$is_configured ? esc_attr($options["host"]) : "";
		echo '<input id="api_setting_host" name="' . $this->plugin::NAME . '[host]" type="text" value="' . $host . '" required />';
	}

	public function api_setting_port()
	{
		$options = get_option($this->plugin::NAME);
		$port = $this->plugin::$is_configured ? esc_attr($options["port"]) : "";
		echo '<input id="api_setting_port" name="' . $this->plugin::NAME . '[port]" type="text" value="' . $port . '" required />';
	}

	public function api_setting_access_key()
	{
		$options = get_option($this->plugin::NAME);
		$access_key = $this->plugin::$is_configured ? esc_attr($options["access_key"]) : "";
		echo '<input id="api_setting_access_key" name="' . $this->plugin::NAME . '[access_key]" type="text" value="' . $access_key . '" required />';
	}

	public function api_setting_secret_key()
	{
		$options = get_option($this->plugin::NAME);
		$secret_key = $this->plugin::$is_configured ? $options["secret_key"] : "";
		$secret_key = $secret_key != "" ? '#secret_key_PLACEHOLDER#' : '';
		echo '<input id="api_setting_secret_key" name="' . $this->plugin::NAME . '[secret_key]" type="password" value="' . $secret_key . '" required />';
	}

	public function api_setting_user()
	{
		$options = get_option($this->plugin::NAME);
		$user = $this->plugin::$is_configured ? esc_attr($options["user"]) : "";
		echo '<input id="api_setting_user" name="' . $this->plugin::NAME . '[user]" type="text" value="' . $user . '" required />';
	}

	public function api_setting_domain()
	{
		$options = get_option($this->plugin::NAME);
		$domain = $this->plugin::$is_configured ? esc_attr($options["domain"]) : "";
		$domain = $domain != "" ? $domain : parse_url(get_site_url(), PHP_URL_HOST);
		echo '<input id="api_setting_domain" name="' . $this->plugin::NAME . '[domain]" type="text" value="' . $domain . '" required />';
	}

	public function plugin_settings_text()
	{
		echo '<p>Some miscellaneous settings for the plugin.</p>';
	}

	public function plugin_setting_show_purge_button()
	{
		$options = get_option($this->plugin::NAME);
		$show_adminbar_button = $this->plugin::$is_configured ? $options["show_adminbar_button"] : false;
		echo '<input id="plugin_setting_show_purge_button" name="' . $this->plugin::NAME . '[show_adminbar_button]" type="checkbox" value="1" ' . checked(1, $show_adminbar_button, false) . ' />';
	}

	public function plugin_setting_purge_button_text()
	{
		$options = get_option($this->plugin::NAME);
		$adminbar_button_text = $this->plugin::$is_configured ? esc_attr($options["adminbar_button_text"]) : "";
		echo '<input id="plugin_setting_purge_button_text" placeholder="Empty for default" name="' . $this->plugin::NAME . '[adminbar_button_text]" type="text" value="' . $adminbar_button_text . '" />';
	}

	public function add_settings_page()
	{
		add_options_page(
			__('Hestia Nginx Cache', $this->plugin::NAME),
			__('Hestia Nginx Cache', $this->plugin::NAME),
			'manage_options',
			$this->plugin::NAME,
			array($this, 'render_settings_page')
		);
	}

	public function render_settings_page()
	{
		if (!current_user_can('manage_options')) {
			return;
		}
?>
		<div class="wrap">
			<h1><?php esc_html_e('Hestia Nginx Cache', $this->plugin::NAME); ?></h1>

			<form method="post" action="options.php">
				<?php
				settings_fields($this->plugin::NAME);
				do_settings_sections($this->plugin::NAME);
				submit_button();
				submit_button('Purge cache', 'delete', 'purge_cache', false);
				?>
			</form>
		</div>
<?php
	}

	public function add_scripts()
	{
		wp_register_script($this->plugin::NAME, plugins_url('assets/js/admin.js', dirname(__FILE__)));
		wp_enqueue_script($this->plugin::NAME);
	}

	public function add_styles()
	{
		wp_register_style($this->plugin::NAME, plugins_url('assets/css/admin.css', dirname(__FILE__)));
		wp_enqueue_style($this->plugin::NAME);
	}

	public function add_purge_button($wp_admin_bar)
	{
		$options = get_option($this->plugin::NAME);

		$wp_admin_bar->add_node(array(
			'id'    => 'hestia-nginx-cache-manual-purge',
			'title' => $this->plugin::$is_configured && $options['adminbar_button_text'] ? $options['adminbar_button_text'] : __('Purge Nginx Cache', $this->plugin::NAME),
		));

		add_action('admin_footer', array($this, 'embed_wp_nonce'));
		add_action('admin_notices', array($this, 'embed_admin_notices'));
	}

	public function embed_wp_nonce()
	{
		echo '<span id="' . $this->plugin::NAME . '-purge-wp-nonce' . '" class="hidden">'
			. wp_create_nonce($this->plugin::NAME . '-purge-wp-nonce')
			. '</span>';
	}

	public function embed_admin_notices()
	{
		echo '<div id="' . $this->plugin::NAME . '-admin-notices"></div>';
	}

	public function purge()
	{
		$result = $this->plugin->purge(true);
		if ($result) {
			$exit_code = wp_remote_retrieve_header($result, 'Hestia-Exit-Code');
		}

		if (!$result || is_wp_error($result) || $exit_code != 0) {
			$args = array('message'   => __('The Hestia Nginx Cache could not be purged!', $this->plugin::NAME));
			if (is_wp_error($result)) {
				$args['error'] = $result->get_error_message();
			} elseif ($result === false) {
				$args['error'] = 'Some options are missing.';
			} elseif (isset($exit_code) && $exit_code != 0) {
				$args['error'] = "Hestia exit code: $exit_code";
			} else {
				$args['error'] = 'Unknown error';
			}
			wp_send_json_error($args);
		} elseif (wp_verify_nonce($_POST['wp_nonce'], $this->plugin::NAME . '-purge-wp-nonce')) {
			wp_send_json_success(array(
				'message' => __('The Hestia Nginx Cache was purged successfully.', $this->plugin::NAME)
			));
		}
	}
}
