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
	public const NAME = 'hestia-nginx-cache';

	private $plugin = null;

	public function __construct()
	{
		$this->plugin = Hestia_Nginx_Cache::get_instance();

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

	public function register_settings()
	{
		register_setting(
			self::NAME,
			self::NAME,
			array('sanitize_callback' => array($this, 'hestia_nginx_options_validate'))
		);

		add_settings_section('api_settings', 'API Settings', array($this, 'hestia_nginx_section_text'), self::NAME);

		add_settings_field('hestia_nginx_setting_host', 'Host', array($this, 'hestia_nginx_setting_host'), self::NAME, 'api_settings');
		add_settings_field('hestia_nginx_setting_port', 'Port', array($this, 'hestia_nginx_setting_port'), self::NAME, 'api_settings');
		add_settings_field('hestia_nginx_setting_api_key', 'API Key', array($this, 'hestia_nginx_setting_api_key'), self::NAME, 'api_settings');
		add_settings_field('hestia_nginx_setting_user', 'Hestia User', array($this, 'hestia_nginx_setting_user'), self::NAME, 'api_settings');
	}

	public function hestia_nginx_options_validate($input)
	{
		$options = get_option(self::NAME);

		$input['port'] = trim($input['port']);
		$input['api_key'] = trim($input['api_key']);

		if (!preg_match('/^\d{1,5}$/i', $input['port'])) {
			$input['port'] = '';
		}

		if ($input['api_key'] == 'you_really_thought_id_leave_it') {
			$input['api_key'] = $options['api_key'];
		} elseif (!preg_match('/^[a-z0-9-_]{32}$/i', $input['api_key'])) {
			$input['api_key'] = '';
		}

		return $input;
	}

	public function hestia_nginx_section_text()
	{
		echo '<p>Here you can set all the options for the API.</p>';
	}

	public function hestia_nginx_setting_host()
	{
		$options = get_option(self::NAME);
		echo "<input id='hestia_nginx_setting_host' name='" . self::NAME . "[host]' type='text' value='" . esc_attr($options['host']) . "' required />";
	}

	public function hestia_nginx_setting_port()
	{
		$options = get_option(self::NAME);
		echo "<input id='hestia_nginx_setting_port' name='" . self::NAME . "[port]' type='text' value='" . esc_attr($options['port']) . "' required />";
	}

	public function hestia_nginx_setting_api_key()
	{
		$options = get_option(self::NAME);
		$api_key = $options['api_key'] ? 'you_really_thought_id_leave_it' : '';
		echo "<input id='hestia_nginx_setting_api_key' name='" . self::NAME . "[api_key]' type='password' value='" . $api_key . "' required />";
	}

	public function hestia_nginx_setting_user()
	{
		$options = get_option(self::NAME);
		echo "<input id='hestia_nginx_setting_user' name='" . self::NAME . "[user]' type='text' value='" . esc_attr($options['user']) . "' required />";
	}

	public function add_settings_page()
	{
		add_options_page(
			__('Hestia Nginx Cache', 'hestia-nginx-cache'),
			__('Hestia Nginx Cache', 'hestia-nginx-cache'),
			'manage_options',
			self::NAME,
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
			<h1><?php esc_html_e('Hestia Nginx Cache', 'hestia-nginx-cache'); ?></h1>

			<form method="post" action="options.php">
				<?php settings_fields(self::NAME);
				do_settings_sections(self::NAME);
				submit_button(); ?>
			</form>
		</div>
<?php
	}

	public function add_scripts()
	{
		wp_register_script(self::NAME, plugins_url('assets/js/admin.js', dirname(__FILE__)));
		wp_enqueue_script(self::NAME);
	}

	public function add_styles()
	{
		wp_register_style(self::NAME, plugins_url('assets/css/admin.css', dirname(__FILE__)));
		wp_enqueue_style(self::NAME);
	}

	public function add_purge_button($wp_admin_bar)
	{
		$wp_admin_bar->add_node(array(
			'id'    => 'hestia-nginx-cache-manual-purge',
			'title' => __('Purge Hestia Nginx Cache', 'hestia-nginx-cache'),
			'href'  => 'javascript:;',
			'meta'  => array('title' => __('Purge Hestia Nginx Cache', 'hestia-nginx-cache'))
		));

		add_action('admin_footer', array($this, 'embed_wp_nonce'));
		add_action('admin_notices', array($this, 'embed_admin_notices'));
	}

	public function embed_wp_nonce()
	{
		echo '<span id="' . self::NAME . '-purge-wp-nonce' . '" class="hidden">'
			. wp_create_nonce(self::NAME . '-purge-wp-nonce')
			. '</span>';
	}

	public function embed_admin_notices()
	{
		echo '<div id="' . self::NAME . '-admin-notices"></div>';
	}

	public function purge()
	{
		$result = $this->plugin->purge(true);

		if (wp_verify_nonce($_POST['wp_nonce'], self::NAME . '-purge-wp-nonce') && !is_wp_error($result) && $result !== false) {
			echo json_encode(array(
				'success' => true,
				'message' => __('The Hestia Nginx Cache was purged successfully.', 'hestia-nginx-cache')
			));
		} else {
			echo json_encode(array(
				'success' => false,
				'message' => __('The Hestia Nginx Cache could not be purged!', 'hestia-nginx-cache'),
				'error'   => $result->get_error_message()
			));
		}

		exit();
	}
}
