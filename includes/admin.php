<?php

/**
 * Hestia Nginx Cache
 *
 * @package           Hestia_Nginx_Cache
 * @author            Jakob Bouchard
 * @license           GPL-3.0+
 */

class Hestia_Nginx_Cache_Admin
{
	private $plugin = null;

	public function __construct()
	{
		$this->plugin = Hestia_Nginx_Cache::get_instance();

		// Add settings link to plugin actions.
		add_filter('plugin_action_links_' . $this->plugin::$plugin_basename, array($this, 'settings_link'));

		// Add settings page.
		add_action('admin_init', array($this, 'register_settings'));
		add_action('admin_menu', array($this, 'add_settings_page'));

		// Add menu button
		add_action('admin_print_styles', array($this, 'add_styles'));
		add_action('admin_enqueue_scripts', array($this, 'add_scripts'));
		add_action('admin_bar_menu', array($this, 'add_purge_button'), PHP_INT_MAX);

		// Handle purge requests.
		add_action('wp_ajax_hestia_nginx_cache_manual_purge', array($this, 'purge'));
	}

	function settings_link($links)
	{
		$url = esc_url(add_query_arg(
			'page',
			$this->plugin::NAME,
			get_admin_url() . 'admin.php'
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

		add_settings_section('api_settings', 'API Settings', array($this, 'hestia_nginx_section_text'), $this->plugin::NAME);

		add_settings_field('hestia_nginx_setting_host', 'Server Hostname', array($this, 'setting_host'), $this->plugin::NAME, 'api_settings');
		add_settings_field('hestia_nginx_setting_port', 'Server Port', array($this, 'setting_port'), $this->plugin::NAME, 'api_settings');
		add_settings_field('hestia_nginx_setting_access_key', 'Access Key', array($this, 'setting_access_key'), $this->plugin::NAME, 'api_settings');
		add_settings_field('hestia_nginx_setting_secret_key', 'Secret Key', array($this, 'setting_secret_key'), $this->plugin::NAME, 'api_settings');
		add_settings_field('hestia_nginx_setting_user', 'Hestia Username', array($this, 'setting_user'), $this->plugin::NAME, 'api_settings');
	}

	public function validate_options($input)
	{
		$options = get_option($this->plugin::NAME);

		$input['port'] = trim($input['port']);
		$input['secret_key'] = trim($input['secret_key']);

		if (!preg_match('/^\d{1,5}$/i', $input['port'])) {
			$input['port'] = '';
		}

		if ($input['secret_key'] == '#secret_key_PLACEHOLDER#') {
			$input['secret_key'] = $options['secret_key'];
		}

		return $input;
	}

	public function hestia_nginx_section_text()
	{
		echo '<p>Here you can set all the options for the API. Please refer to the <a href="https://docs.hestiacp.com/admin_docs/api/rest_api.html" rel="noopener noreferrer" target="_blank">Hestia Docs</a> for information on how to create an API key.</p>';
	}

	public function setting_host($test)
	{
		$options = get_option($this->plugin::NAME);
		echo '<input id="hestia_nginx_setting_host" name="' . $this->plugin::NAME . '[host]" type="text" value="' . esc_attr($options['host']) . '" required />';
	}

	public function setting_port()
	{
		$options = get_option($this->plugin::NAME);
		echo '<input id="hestia_nginx_setting_port" name="' . $this->plugin::NAME . '[port]" type="text" value="' . esc_attr($options['port']) . '" required />';
	}

	public function setting_access_key()
	{
		$options = get_option($this->plugin::NAME);
		echo '<input id="hestia_nginx_setting_access_key" name="' . $this->plugin::NAME . '[access_key]" type="text" value="' . esc_attr($options['access_key']) . '" required />';
	}

	public function setting_secret_key()
	{
		$options = get_option($this->plugin::NAME);
		$secret_key = $options['secret_key'] ? '#secret_key_PLACEHOLDER#' : '';
		echo '<input id="hestia_nginx_setting_secret_key" name="' . $this->plugin::NAME . '[secret_key]" type="password" value="' . $secret_key . '" required />';
	}

	public function setting_user()
	{
		$options = get_option($this->plugin::NAME);
		echo '<input id="hestia_nginx_setting_user" name="' . $this->plugin::NAME . '[user]" type="text" value="' . esc_attr($options['user']) . '" required />';
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
		$wp_admin_bar->add_node(array(
			'id'    => 'hestia-nginx-cache-manual-purge',
			'title' => __('Purge Hestia Nginx Cache', $this->plugin::NAME),
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
		$exit_code = null;
		if ($result) {
			$exit_code = wp_remote_retrieve_header($result, 'Hestia-Exit-Code');
		}

		if (!$result || is_wp_error($result) || $exit_code != 0) {
			wp_send_json_error(array(
				'message'   => __('The Hestia Nginx Cache could not be purged!', $this->plugin::NAME),
				'exit_code' => $exit_code,
				'error'     => is_wp_error($result) ? $result->get_error_message() : 'Some options are missing.'
			));
		} elseif (wp_verify_nonce($_POST['wp_nonce'], $this->plugin::NAME . '-purge-wp-nonce')) {
			wp_send_json_success(array(
				'message' => __('The Hestia Nginx Cache was purged successfully.', $this->plugin::NAME)
			));
		}
	}
}
