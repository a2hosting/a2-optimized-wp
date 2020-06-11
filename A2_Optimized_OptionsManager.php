<?php

/*
	Author: Benjamin Cool, Andrew Jones
	Author URI: https://www.a2hosting.com/
	License: GPLv2 or Later
*/

// Prevent direct access to this file
if ( ! defined( 'WPINC' ) ) {
	die;
}

if (is_admin()) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	class A2_Plugin_Installer_Skin extends Plugin_Installer_Skin {
		public function error($error) {
		}
	}
}

if (file_exists('/opt/a2-optimized/wordpress/Optimizations.php')) {
	require_once '/opt/a2-optimized/wordpress/Optimizations.php';
}
require_once 'A2_Optimized_Optimizations.php';

class A2_Optimized_OptionsManager {
	public $plugin_dir;
	private $optimizations;
	private $advanced_optimizations;
	private $advanced_optimization_status;
	private $optimization_count;
	private $advanced_optimization_count;
	private $plugin_list;
	private $install_status;
	private $salts_array;
	private $new_salts;

	public function __construct() {
	}

	/**
	 * Get the current version of w3tc
	 *
	 */
	private function get_current_w3tc_version() {
		$version = get_transient('a2_w3tc_current_version');
		if (!$version) {
			$response = wp_remote_get( 'https://wp-plugins.a2hosting.com/wp-json/wp/v2/update_notice?notice_plugin=3&per_page=1' );
			if ( is_array( $response ) ) {
				$body = json_decode($response['body']); // use the content
				foreach ($body as $item) {
					$version = $item->title->rendered;
					set_transient('a2_w3tc_current_version', $version, 3600 * 12);
				}
			} else {
				$version = null;
				set_transient('a2_w3tc_current_version', $version, 3600);
			}
		}

		return $version;
	}

	/**
	 * Get the array of w3tc plugin default settings
	 *
	 * @return array
	 */
	public function get_w3tc_defaults() {
		return array(
			'pgcache.check.domain' => true,
			'pgcache.prime.post.enabled' => true,
			'pgcache.reject.logged' => true,
			'pgcache.reject.request_head' => true,
			'pgcache.purge.front_page' => true,
			'pgcache.purge.home' => true,
			'pgcache.purge.post' => true,
			'pgcache.purge.comments' => true,
			'pgcache.purge.author' => true,
			'pgcache.purge.terms' => true,
			'pgcache.purge.archive.daily' => true,
			'pgcache.purge.archive.monthly' => true,
			'pgcache.purge.archive.yearly' => true,
			'pgcache.purge.feed.blog' => true,
			'pgcache.purge.feed.comments' => true,
			'pgcache.purge.feed.author' => true,
			'pgcache.purge.feed.terms' => true,
			'pgcache.cache.feed' => true,
			'pgcache.debug' => false,
			'pgcache.purge.postpages_limit' => 0,//purge all pages that list posts
			'pgcache.purge.feed.types' => array(
				0 => 'rdf',
				1 => 'rss',
				2 => 'rss2',
				3 => 'atom'
			),
			'pgcache.compatibility' => true,
			'minify.debug' => false,
			'dbcache.debug' => false,
			'objectcache.debug' => false,

			'mobile.enabled' => true,

			'minify.auto' => false,
			'minify.html.engine' => 'html',
			'minify.html.inline.css' => true,
			'minify.html.inline.js' => true,

			'minify.js.engine' => 'js',
			'minify.css.engine' => 'css',

			'minify.js.header.embed_type' => 'nb-js',
			'minify.js.body.embed_type' => 'nb-js',
			'minify.js.footer.embed_type' => 'nb-js',

			'minify.lifetime' => 14400,
			'minify.file.gc' => 144000,

			'dbcache.lifetime' => 3600,
			'dbcache.file.gc' => 7200,

			'objectcache.lifetime' => 3600,
			'objectcache.file.gc' => 7200,

			'browsercache.cssjs.last_modified' => true,
			'browsercache.cssjs.expires' => true,
			'browsercache.cssjs.lifetime' => 31536000,
			'browsercache.cssjs.nocookies' => false,
			'browsercache.cssjs.cache.control' => true,
			'browsercache.cssjs.cache.policy' => 'cache_maxage',
			'browsercache.cssjs.etag' => true,
			'browsercache.cssjs.w3tc' => true,
			'browsercache.cssjs.replace' => true,
			'browsercache.html.last_modified' => true,
			'browsercache.html.expires' => true,
			'browsercache.html.lifetime' => 30,
			'browsercache.html.cache.control' => true,
			'browsercache.html.cache.policy' => 'cache_maxage',
			'browsercache.html.etag' => true,
			'browsercache.html.w3tc' => true,
			'browsercache.html.replace' => true,
			'browsercache.other.last_modified' => true,
			'browsercache.other.expires' => true,
			'browsercache.other.lifetime' => 31536000,
			'browsercache.other.nocookies' => false,
			'browsercache.other.cache.control' => true,
			'browsercache.other.cache.policy' => 'cache_maxage',
			'browsercache.other.etag' => true,
			'browsercache.other.w3tc' => true,
			'browsercache.other.replace' => true,

			'config.check' => true,

			'varnish.enabled' => false
		);
	}

	/**
	 * Enable w3tc cache plugin
	 *
	 */
	public function enable_w3_total_cache() {
		$file = 'a2-w3-total-cache/a2-w3-total-cache.php';
		$slug = 'a2-w3-total-cache';
		$this->install_plugin($slug);
		$this->activate_plugin($file);
		$this->hit_the_w3tc_page();
	}

	/**
	 * Get all plugins for this theme
	 *
	 */
	public function get_plugins() {
		if (isset($this->plugin_list)) {
			return $this->plugin_list;
		} else {
			return get_plugins();
		}
	}

	/**
	 * Enable browser cache for w3tc
	 *
	 * @param string $slug The plugin path
	 * @para boolean $activate - Activate plugin after installl
	 *
	 */
	public function install_plugin($slug, $activate = false) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		$api = plugins_api('plugin_information', array('slug' => $slug));
		$response = true;

		$found = false;

		if ($slug == 'a2-w3-total-cache') {
			$file = 'a2-w3-total-cache/a2-w3-total-cache.php';
		}

		$plugins = $this->get_plugins();

		foreach ($plugins as $file => $plugin) {
			if ($plugin['Name'] == $api->name) {
				$found = true;
			}
		}

		if (!$found) {
			ob_start();
			$upgrader = new Plugin_Upgrader(new A2_Plugin_Installer_Skin(compact('title', 'url', 'nonce', 'plugin', 'api')));

			if ($slug == 'a2-w3-total-cache') {
				$api->download_link = 'https://wp-plugins.a2hosting.com/wp-content/uploads/rkv-repo/a2-w3-total-cache.zip';
			}

			$response = $upgrader->install($api->download_link);
			ob_end_clean();
			$this->plugin_list = get_plugins();
		}

		if ($activate) {
			$plugins = $this->get_plugins();
			foreach ($plugins as $file => $plugin) {
				if ($plugin['Name'] == $api->name) {
					$this->activate_plugin($file);
				}
			}
		}

		$this->clear_w3_total_cache();

		return $response;
	}

	public function activate_plugin($file) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		activate_plugin($file);
		$this->clear_w3_total_cache();
	}

	public function clear_w3_total_cache() {
		if (is_plugin_active('a2-w3-total-cache/a2-w3-total-cache.php')) {
			//TODO:  add clear cache
		}
	}

	/**
	 * Curl call the w3tc page
	 *
	 */
	public function hit_the_w3tc_page() {
		$disregarded_cookies = array(
			'PHPSESSID',
			);

		$cookie = '';
		foreach ($_COOKIE as $name => $val) {
			if (!in_array($name, $disregarded_cookies)) {
				$cookie .= "{$name}={$val};";
			}
		}
		rtrim($cookie, ';');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, get_admin_url() . 'admin.php?page=w3tc_general&nonce=' . wp_create_nonce('w3tc'));
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6');
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		curl_setopt($ch, CURLOPT_REFERER, get_admin_url());
		$result = curl_exec($ch);
		curl_close($ch);
	}

	public function refresh_w3tc() {
		$this->hit_the_w3tc_page();
	}

	/**
	 * Get configs for w3tc
	 *
	 *@return array|false
	 */
	public function get_w3tc_config() {
		if (class_exists('W3_ConfigData')) {
			$config_writer = new W3_ConfigWriter(0, false);

			return W3_ConfigData::get_array_from_file($config_writer->get_config_filename());
		} else {
			return false;
		}
	}

	/**
	 * Enable plguin cache for w3tc
	 *
	 */
	public function enable_w3tc_cache() {
		$permalink_structure = get_option('permalink_structure');
		$vars = array();
		if ($permalink_structure == '') {
			$vars['pgcache.engine'] = 'file';
		} else {
			$vars['pgcache.engine'] = 'file_generic';
		}
		$vars['dbcache.engine'] = 'file';
		$vars['objectcache.engine'] = 'file';

		$vars['objectcache.enabled'] = true;
		$vars['dbcache.enabled'] = true;
		$vars['pgcache.enabled'] = true;
		$vars['pgcache.compatibility'] = true;
		$vars['pgcache.cache.ssl'] = true;
		$vars['browsercache.enabled'] = true;

		$this->update_w3tc($vars);
	}

	/**
	 * Enable page cache for w3tc
	 *
	 */
	public function enable_w3tc_page_cache() {
		$permalink_structure = get_option('permalink_structure');
		$vars = array();
		if ($permalink_structure == '') {
			$vars['pgcache.engine'] = 'file';
		} else {
			$vars['pgcache.engine'] = 'file_generic';
		}

		$vars['pgcache.enabled'] = true;
		$vars['pgcache.cache.ssl'] = true;
		$vars['pgcache.compatibility'] = true;

		$this->update_w3tc($vars);
	}

	/**
	 * Enable database cache for w3tc
	 *
	 */
	public function enable_w3tc_db_cache() {
		$vars = array();

		$vars['dbcache.engine'] = 'file';
		$vars['dbcache.enabled'] = true;

		$this->update_w3tc($vars);
	}

	/**
	 * Enable plugin object cache for w3tc
	 *
	 */
	public function enable_w3tc_object_cache() {
		$vars = array();

		$vars['objectcache.engine'] = 'file';
		$vars['objectcache.enabled'] = true;

		$this->update_w3tc($vars);
	}

	/**
	 * Enable browser cache for w3tc
	 *
	 */
	public function enable_w3tc_browser_cache() {
		$vars = array();

		$vars['browsercache.enabled'] = true;

		$this->update_w3tc($vars);
	}

	/**
	 *  Enable gzip for w3tc
	 *
	 */
	public function enable_w3tc_gzip() {
		$vars = array();

		$vars['browsercache.other.compression'] = true;
		$vars['browsercache.html.compression'] = true;
		$vars['browsercache.cssjs.compression'] = true;

		$this->update_w3tc($vars);
	}

	/**
	*  Disable gzip for w3tc
	*
	*/
	public function disable_w3tc_gzip() {
		$vars = array();

		$vars['browsercache.other.compression'] = false;
		$vars['browsercache.html.compression'] = false;
		$vars['browsercache.cssjs.compression'] = false;

		$this->update_w3tc($vars);
	}
	
	/**
	*  Enable WooCommerce Cart Fragment Dequeuing
	*
	*/
	public function enable_woo_cart_fragments() {
		update_option('a2_wc_cart_fragments', 1);
		update_option('woocommerce_cart_redirect_after_add', 'yes'); // Recommended WooCommerce setting when disabling cart fragments
	}
	
	/**
	*  Disable WooCommerce Cart Fragment Dequeuing
	*
	*/
	public function disable_woo_cart_fragments() {
		delete_option('a2_wc_cart_fragments');
		delete_option('woocommerce_cart_redirect_after_add');
	}
	
	/**
	*  Enable Blocking of XML-RPC Requests
	*
	*/
	public function enable_xmlrpc_requests() {
		update_option('a2_block_xmlrpc', 1);
	}
	
	/**
	*  Disable Blocking of XML-RPC Requests
	*
	*/
	public function disable_xmlrpc_requests() {
		delete_option('a2_block_xmlrpc');
	}

	/**
	*  Regenerate wp-config.php salts
	*
	*/
	public function regenerate_wpconfig_salts() {
		$this->salts_array = array(
			"define('AUTH_KEY',",
			'SECURE_AUTH_KEY',
			'LOGGED_IN_KEY',
			'NONCE_KEY',
			"define('AUTH_SALT',",
			'SECURE_AUTH_SALT',
			'LOGGED_IN_SALT',
			'NONCE_SALT',
		);

		$returned_salts = file_get_contents('https://api.wordpress.org/secret-key/1.1/salt/');
		$this->new_salts = explode("\n", $returned_salts);

		update_option('a2_updated_regenerate-salts', date('F jS, Y'));

		return $this->writeSalts($this->salts_array, $this->new_salts);
	}

	public function regenerate_wpconfig_desc() {
		$output = '<p>Generate new salt values for wp-config.php<br /><strong>This will log out all users including yourself</strong><br />Last regenerated:</p>';
		
		return $output;
	}

	private function writeSalts($salts_array, $new_salts) {
		$config_file = $this->config_file_path();

		$tmp_config_file = ABSPATH . 'wp-config-tmp.php';

		foreach ($salts_array as $salt_key => $salt_value) {
			$readin_config = fopen($config_file, 'r');
			$writing_config = fopen($tmp_config_file, 'w');

			$replaced = false;
			while (!feof($readin_config)) {
				$line = fgets($readin_config);
				if (stristr($line, $salt_value)) {
					$line = $new_salts[$salt_key] . "\n";
					$replaced = true;
				}
				fputs($writing_config, $line);
			}

			fclose($readin_config);
			fclose($writing_config);

			if ($replaced) {
				rename($tmp_config_file, $config_file);
			} else {
				unlink($tmp_config_file);
			}
		}
	}
	
	private function config_file_path() {
		$salts_file_name = 'wp-config';
		$config_file = ABSPATH . $salts_file_name . '.php';
		$config_file_up = ABSPATH . '../' . $salts_file_name . '.php';

		if (file_exists($config_file) && is_writable($config_file)) {
			return $config_file;
		} elseif (file_exists($config_file_up) && is_writable($config_file_up) && !file_exists(dirname(ABSPATH) . '/wp-settings.php')) {
			return $config_file_up;
		}

		return false;
	}

	/**
	 * Update w3tc plugin
	 * @param array $vars Variables to set in config with the config writer
	 *
	 */
	public function update_w3tc($vars) {
		$vars = array_merge($this->get_w3tc_defaults(), $vars);

		/* Make sure we're running a compatible version of W3 Total Cache */
		if ($this->is_valid_w3tc_installed() && class_exists('W3_ConfigData')) {
			$config_writer = new W3_ConfigWriter(0, false);
			foreach ($vars as $name => $val) {
				$config_writer->set($name, $val);
			}
			$config_writer->set('common.instance_id', mt_rand());
			$config_writer->save();
			$this->refresh_w3tc();
		}
	}

	/**
	 * Disable plugin cache for w3tc
	 *
	 */
	public function disable_w3tc_cache() {
		$this->update_w3tc(array(
			'pgcache.enabled' => false,
			'dbcache.enabled' => false,
			'objectcache.enabled' => false,
			'browsercache.enabled' => false,
		));
	}

	/**
	 * Disable page cache for w3tc
	 *
	 */
	public function disable_w3tc_page_cache() {
		$vars = array();
		$vars['pgcache.enabled'] = false;
		$this->update_w3tc($vars);
	}

	/**
	 * Disable database cache for w3tc
	 *
	 */
	public function disable_w3tc_db_cache() {
		$vars = array();
		$vars['dbcache.enabled'] = false;
		$this->update_w3tc($vars);
	}

	/**
	 * Disable object cache for w3tc
	 *
	 */
	public function disable_w3tc_object_cache() {
		$vars = array();
		$vars['objectcache.enabled'] = false;
		$this->update_w3tc($vars);
	}

	/**
	 * Disable browser cache for w3tc
	 *
	 */
	public function disable_w3tc_browser_cache() {
		$vars = array();
		$vars['browsercache.enabled'] = false;
		$this->update_w3tc($vars);
	}

	/**
	 * Disable html minification
	 *
	 */
	public function disable_html_minify() {
		$this->update_w3tc(array(
			'minify.html.enable' => false,
			'minify.html.enabled' => false,
			'minify.auto' => false
		));
	}

	/**
	 * Enable html minification
	 *
	 */
	public function enable_html_minify() {
		$this->update_w3tc(array(
			'minify.html.enable' => true,
			'minify.enabled' => true,
			'minify.auto' => false,
			'minify.engine' => 'file'
		));
	}

	public function curl_save_w3tc($cookie, $url) {
		$post = 'w3tc_save_options=Save all settings&_wpnonce=' . wp_create_nonce('w3tc') . '&_wp_http_referer=%2Fwp-admin%2Fadmin.php%3Fpage%3Dw3tc_general%26&w3tc_note%3Dconfig_save';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, get_admin_url() . $url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		curl_setopt($ch, CURLOPT_REFERER, get_admin_url() . $url);
		//curl_setopt($ch, CURLOPT_NOBODY, TRUE); // remove body
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$head = curl_exec($ch);
		curl_close($ch);
	}

	public function get_optimizations() {
		return $this->optimizations;
	}

	/**
	 * Creates HTML for the Administration page to set options for this plugin.
	 * Override this method to create a customized page.
	 * @return void
	 */
	public function settingsPage() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access A2 Optimized.', 'a2-optimized'));
		}

		$thisclass = $this;

		//$thisdir = rtrim(__DIR__, '/');

		wp_enqueue_style('bootstrap', plugins_url('/assets/bootstrap/css/bootstrap.css', __FILE__), '', $thisclass->getVersion());
		wp_enqueue_style('bootstrap-theme', plugins_url('/assets/bootstrap/css/bootstrap-theme.css', __FILE__), '', $thisclass->getVersion());
		wp_enqueue_script('bootstrap-theme', plugins_url('/assets/bootstrap/js/bootstrap.js', __FILE__), array('jquery'), $thisclass->getVersion());

		$image_dir = plugins_url('/assets/images', __FILE__);

		$ini_error_reporting = ini_get('error_reporting');
		//ini_set('error_reporting',0);

		if (isset($_GET['a2-page'])) {
			if (isset($_GET['step'])) {
				$step = $_GET['step'];
			} else {
				$step = 1;
			}

			// Show wizard here...
			if ($_GET['a2-page'] == 'newuser_wizard') {
				$this->newuser_wizard_html($step);
			}
			if ($_GET['a2-page'] == 'upgrade_wizard') {
				$this->upgrade_wizard_html($step);
			}
			if ($_GET['a2-page'] == 'w3tcfixed_confirm') {
				update_option( 'a2opt_w3tcfixed_confirm', true);
				$this->settings_page_html();
			}
			if ($_GET['a2-page'] == 'recaptcha_settings') {
				$this->recaptcha_settings_html();
			}
			if ($_GET['a2-page'] == 'recaptcha_settings_save') {
				$this->recaptcha_settings_save();
				$this->settings_page_html();
			}
			if ($_GET['a2-page'] == 'enable-rwl') {
				if ($_GET['enable'] == '1') {
					if (!isset($this->plugin_list['easy-hide-login/wp-hide-login.php'])) {
						$this->install_plugin('easy-hide-login');
					}
					$this->activate_plugin('easy-hide-login/wp-hide-login.php');

					if (get_option('a2_login_page') === false) {
						if (get_option('wpseh_l01gnhdlwp') === false) {
							$length = 4;
							$rwl_page = $this->getRandomString($length);
							update_option('a2_login_page', $rwl_page);
							update_option('wpseh_l01gnhdlwp', $rwl_page);
						} else {
							update_option('a2_login_page', get_option('wpseh_l01gnhdlwp'));
						}
					} else {
						update_option('wpseh_l01gnhdlwp', get_option('a2_login_page'));
					}
					delete_option('rwl_redirect');
				}

				update_option('a2_managed_changelogin', 1);
				$this->settings_page_html();
			}
		} else {
			$this->settings_page_html();
		}

		ini_set('error_reporting', $ini_error_reporting);
	}

	/**
	 * Settings page for A2Optimized
	 *
	 */
	private function settings_page_html() {
		$thisclass = $this;
		$w3tc = $this->get_w3tc_config();
		$opts = new A2_Optimized_Optimizations($thisclass);
		$optimization_count = 0;
		$this->get_plugin_status();
		$this->optimization_status = '';
		$image_dir = plugins_url('/assets/images', __FILE__);

		do_action('a2_notices');

		$optionMetaData = $this->getOptionMetaData();

		$optimization_status = '';

		foreach ($this->advanced_optimizations as $shortname => &$item) {
			$this->advanced_optimization_status .= $this->get_optimization_status($item, $opts->server_info);
			if ($item['configured']) {
				$this->advanced_optimization_count++;
			}
		}

		$this->optimization_alert = '';

		if (is_plugin_active('w3-total-cache/w3-total-cache.php')) {
			$this->optimization_alert = "<div class='alert alert-info'>";
			$this->optimization_alert .= '<p>We noticed you have W3 Total Cache already installed. We are not able to fully support this version of W3 Total Cache with A2 Optimized. To get the best options for optimizing your WordPress site, we will help you disable this W3 Total Cache plugin version and install an A2 Hosting supported version of W3 Total Cache in its place.</p>';
			$this->optimization_alert .= "<p><a href='" . admin_url('admin.php?a2-page=upgrade_wizard&page=A2_Optimized_Plugin_admin') . "' class='btn btn-success'>Disable W3 Total Cache</a></p>";
			$this->optimization_alert .= '</div>';
		} elseif (is_plugin_active('w3-total-cache-fixed/w3-total-cache-fixed.php')) {
			$w3tc_fixed_info = get_plugin_data('w3-total-cache-fixed/w3-total-cache-fixed.php');
			if (version_compare($w3tc_fixed_info['Version'], '0.9.5.0') >= 0) {
				$this->optimization_alert = "<div class='alert alert-info'>";
				$this->optimization_alert .= '<p>We noticed you have W3 Total Cache already installed. We are not able to fully support this version of W3 Total Cache with A2 Optimized. To get the best options for optimizing your WordPress site, we will help you disable this W3 Total Cache plugin version and install an A2 Hosting supported version of W3 Total Cache in its place.</p>';
				$this->optimization_alert .= "<p><a href='" . admin_url('admin.php?a2-page=upgrade_wizard&page=A2_Optimized_Plugin_admin') . "' class='btn btn-success'>Disable W3 Total Cache Fixed</a></p>";
				$this->optimization_alert .= '</div>';
			} elseif (get_option('a2opt_w3tcfixed_confirm') === false) {
				$this->optimization_alert = "<div class='alert alert-info'>";
				$this->optimization_alert .= '<p>Please note that you have W3 Total Cache Fixed v0.9.4.x plugin installed. We cannot guarantee our optimizations will be fully supported with this version. To ensure the best compatibility for your WordPress site, please disable the W3 Total Cache Fixed plugin by clicking the button below and install our supported W3 Total Cache plugin, which is based on W3 Total Cache Fixed.</p>';
				$this->optimization_alert .= "<p><a href='" . admin_url('admin.php?a2-page=upgrade_wizard&page=A2_Optimized_Plugin_admin') . "' class='btn btn-success'>Disable W3 Total Cache Fixed</a></p>";
				$this->optimization_alert .= '<p>If you would like to keep your currently installed W3 Total Cache Fixed plugin, you may click the button below to dismiss this dialog and enable the optimization options below.</p>';
				$this->optimization_alert .= "<p><a href='" . admin_url('admin.php?a2-page=w3tcfixed_confirm&page=A2_Optimized_Plugin_admin') . "' class='btn btn-warning'>I accept the risks</a></p>";
				$this->optimization_alert .= '</div>';
			}
		} elseif (
			$this->is_plugin_installed('a2-w3-total-cache/a2-w3-total-cache.php') === false
				|| is_plugin_active('a2-w3-total-cache/a2-w3-total-cache.php') === false
		) {
			$this->optimization_alert = "<div class='alert alert-info'>";
			$this->optimization_alert .= '<p>Thank you for installing A2 Optimized for WordPress. Some features below require an additional plugin. We will walk you through the process of installing our supported version of W3 Total Cache that will enable the rest of the options below.</p>';
			$this->optimization_alert .= "<p><a href='" . admin_url('admin.php?a2-page=newuser_wizard&page=A2_Optimized_Plugin_admin') . "' class='btn btn-success'>Begin Installation</a></p>";
			$this->optimization_alert .= '</div>';
		}

		$this->optimization_count = 0;

		foreach ($this->optimizations as $shortname => &$item) {
			$this->optimization_status .= $this->get_optimization_status($item, $opts->server_info);
			if ($item['configured']) {
				$this->optimization_count++;
			}
		}

		if ($this->optimization_count == count($this->optimizations)) {
			$optimization_alert = '<div class="alert alert-success">Your site has been fully optimized!</div>';
		} elseif (!$this->optimizations['page_cache']['configured']) {
			$optimization_alert = '<div class="alert alert-danger">Your site is NOT optimized!</div>';
		} elseif ($this->optimization_count > 5) {
			$optimization_alert = '<div class="alert alert-success">Your site has been partially optimized!</div>';
		} elseif ($this->optimization_count > 2) {
			$optimization_alert = '<div class="alert alert-danger">Your site is barely optimized!</div>';
		} else {
			$optimization_alert = '<div class="alert alert-danger">Your site is NOT optimized!</div>';
		}

		$optimization_number = count($this->optimizations);

		$optimization_circle = '';
		if ($optimization_number > 0) {
			$optimization_circle = <<<HTML
<span class="badge badge-success">{$this->optimization_count}/{$optimization_number}</span>
HTML;
		}

		$kb_search_box = $this->kb_searchbox_html();

		list($warnings, $num_warnings) = $this->warnings();

		$advanced_circle = '';

		$save_alert = '';

		if (isset($_GET['save_settings']) && $_GET['save_settings']) {
			$save_alert = '<div class="alert alert-success">Settings Saved</div>';
		}

		$warning_circle = '';
		if ($num_warnings > 0) {
			$warning_circle = <<<HTML
<span class="badge badge-warning">{$num_warnings}</span>
HTML;
		}

		$settingsGroup = get_class($this) . '-settings-group';
		$description = $this->get_plugin_description();

		if ($this->is_a2()) {
			$feedback = <<<HTML
        <div  style="margin:10px 0;" class="alert alert-success">
            We want to hear from you! Please share your thoughts and feedback in our <a href="https://my.a2hosting.com/?m=a2_suggestionbox" target="_blank">Suggestion Box!</a>
        </div>
HTML;
		} else {
			$feedback = <<<HTML
        <div  style="margin:10px 0;" class="alert alert-success">
            We want to hear from you! Please share your thoughts and feedback in our wordpress.org <a href="https://wordpress.org/support/plugin/a2-optimized-wp/" target="_blank">support forum!</a>
        </div>
HTML;
		}

		echo <<<HTML


<section id="a2opt-content-general">
	<div  class="wrap">
		<div>
			<div>
				<div>
					<div style="float:left;clear:both">
						<img src="{$image_dir}/a2optimized.png"  style="margin-top:20px" />
					</div>
					<div style="float:right;">
						{$kb_search_box}
					</div>
				</div>
				<div style="clear:both;"></div>
			</div>
			<div >

                <div style="margin:20px 0;">
    				{$optimization_alert}
    				{$save_alert}
				</div>
			</div>
		</div>


		<ul class="nav nav-tabs" roll="tablist">
		  <li role="tab" aria-controls="optimization-status" id="li-optimization-status" ><a onclick='document.location.hash="#optimization-status-tab"' href="#optimization-status" data-toggle="tab">Optimization Status {$optimization_circle}</a></li>
		  <li role="tab" aria-controls="optimization-warnings" id="li-optimization-warnings" ><a onclick='document.location.hash="#optimization-warnings-tab"' href="#optimization-warnings" data-toggle="tab">Warnings {$warning_circle}</a></li>
		  <li role="tab" aria-controls="optimization-advanced" id="li-optimization-advanced" ><a onclick='document.location.hash="#optimization-advanced-tab"' href="#optimization-advanced" data-toggle="tab">Advanced Optimizations {$advanced_circle}</a></li>
		  <li role="tab" aria-controls="optimization-about" id="li-optimization-about" ><a onclick='document.location.hash="#optimization-about-tab"' href="#optimization-about" data-toggle="tab">About A2 Optimized</a></li>
		</ul>




		<div class="tab-content">
			<div role="tabpanel" aria-labelledby="li-optimization-status" id="optimization-status" class="tab-pane">
				<h3>Optimization Status</h3>
				{$this->optimization_alert}
				<div >
					{$this->optimization_status}
				</div>
			</div>
			<div role="tabpanel" aria-labelledby="li-optimization-warnings" id="optimization-warnings" class="tab-pane">
				<h3>Warnings</h3>
				{$warnings}
			</div>

			<div role="tabpanel" aria-labelledby="li-optimization-advanced" id="optimization-advanced" class="tab-pane">
				<h3>Advanced Optimizations</h3>
					{$this->advanced_optimization_status}
			</div>

            <div role="tabpanel" aria-labelledby="li-optimization-about" id="optimization-about" class="tab-pane">
				<div style="margin:20px 0;">
				    <h3>About A2 Optimized</h3>
                    <p>A2 Optimized was developed by A2 Hosting to make it faster and easier to configure the caching of all aspects of a WordPress site.</p>
                    <p>This free plugin comes with many of the popular Optimizations that come with WordPress hosted at A2 Hosting.</p>
                    <p>To get the full advantage of A2 Optimized, host your site at <a href='https://www.a2hosting.com/wordpress-hosting?utm_source=A2%20Optimized&utm_medium=Referral&utm_campaign=A2%20Optimized' target='_blank'>A2 Hosting</a></p>

				</div>
				<div style="margin:20px 0;">
					<h3>Additional Plugins Installed on A2 Hosting</h3>
					<p><strong>A2 Total Cache</strong><br />
					A community supported fork of W3 Total Cache</p>
					<p><strong>Easy Hide Login</strong><br />
					Changes the location of the WordPress login page</p>
					<p><strong>EWWW Image Optimizer</strong><br />
					Compress and optimize images on upload</p>
				</div>
				<div style="margin:20px 0;">
				    <h3>Free Optimizations</h3>
				    <dt>Page Caching with W3 Total Cache</dt>
                    <dd>
                        <ul>
                            <li>Page Caching stores full copies of pages on the disk so that PHP code and database queries can be skipped by the web server.</li>
                        </ul>
                    </dd>
                    <dt>DB Caching with W3 Total Cache</dt>
                    <dd>
                        <ul>
                            <li>Database cache stores copies of common database queries on disk or in memory to speed up page rendering.</li>
                        </ul>
                    </dd>
                    <dt>Object Caching with W3 Total Cache</dt>
                    <dd>
                        <ul>
                            <li>Object Caching stores commonly used elements such as menus, widgets and forms on disk or in memory to speed up page rendering.</li>
                        </ul>
                    </dd>

                    <dt>Browser Caching with W3 Total Cache</dt>
                    <dd>
                        <ul>
                            <li>Add Rules to the web server to tell the visitor's browser to store a copy of static files to reduce the load time for pages requested after the first page is loaded.</li>
                        </ul>
                    </dd>



                    <dt>Minify HTML Pages</dt>
                    <dd>
                        <ul>
                            <li style="list-style-position: inside">Auto Configure W3 Total Cache to remove excess white space and comments from HTML pages to compress their size.</li>
                            <li>Smaller html pages download faster.</li>
                        </ul>
                    </dd>
                    <dt>Minify CSS Files</dt>
                    <dd>
                        <ul>
                            <li>Auto Configure W3 Total Cache to condense CSS files.</li>
                            <li>Combines multiple css files into a single download.</li>
                            <li>Can provide significant speed imporvements for page loads.</li>
                        </ul>
                    </dd>
                    <dt>Minify JS Files</dt>
                    <dd>
                        <ul>
                            <li>Auto Configure W3 Total Cache to condense JavaScript files into non human-readable compressed files.</li>
                            <li>Combines multiple js files into a single download.</li>
                            <li>Can provide significant speed improvements for page loads.</li>
                        </ul>
                    </dd>
                    <dt>Gzip Compression Enabled</dt>
                    <dd>
                        <ul>
                            <li>Turns on gzip compression using W3 Total Cache.</li>
                            <li>Ensures that files are compressed before sending them to the visitor's browser.</li>
                            <li>Can provide significant speed improvements for page loads.</li>
                            <li>Reduces bandwidth required to serve web pages.</li>
                        </ul>
                    </dd>
                    <dt>Deny Direct Access to Configuration Files and Comment Form</dt>
                    <dd>
                        <ul>
                            <li>Enables WordPress hardening rules in .htaccess to prevent browser access to certain files.</li>
                            <li>Prevents bots from submitting to comment forms.</li>
                            <li>Turn this off if you use systems that post to the comment form without visiting the page.</li>
                        </ul>
                    </dd>
                    <dt>Lock Editing of Plugins and Themes from the WP Admin</dt>
                    <dd>
                        <ul>
                            <li>Turns off the file editor in the wp-admin.</li>
                            <li>Prevents plugins and themes from being tampered with from the wp-admin.</li>
                        </ul>
                    </dd>
				</div>
				<div style="margin:20px 0;">
				    <h3>A2 Hosting Exclusive Optimizations</h3>
				    <p>
				        These one-click optimizations are only available while hosted at A2 Hosting.
                    </p>
				    <dt>Login URL Change</dt>
                    <dd>
                        <ul>
                            <li>Move the login page from the default wp-login.php to a random URL.</li>
                            <li>Prevents bots from automatically brute-force attacking wp-login.php</li>
                        </ul>
                    </dd>
                    <dt>reCAPTCHA on comments and login</dt>
                    <dd>
                        <ul>
                            <li>Provides google reCAPTCHA on both the Login form and comments.</li>
                            <li>Prevents bots from automatically brute-force attacking wp-login.php</li>
                            <li>Prevents bots from automatically spamming comments.</li>
                        </ul>
                    </dd>
                    <dt>Compress Images on Upload</dt>
                    <dd>
                        <ul>
                            <li>Enables and configures EWWW Image Optimizer.</li>
                            <li>Compresses images that are uploaded to save bandwidth.</li>
                            <li>Improves page load times: especially on sites with many images.</li>
                        </ul>
                    </dd>
                    <dt>Turbo Web Hosting</dt>
                    <dd>
                        <ul>
                            <li>Take advantage of A2 Hosting's Turbo Web Hosting platform.</li>
                            <li>Faster serving of static files.</li>
                            <li>Pre-compiled .htaccess files on the web server for imporved performance.</li>
                            <li>PHP OpCode cache enabled by default</li>
                            <li>Custom PHP engine that is faster than Fast-CGI and FPM</li>
                        </ul>
                    </dd>
                    <dt>Memcached Database and Object Cache</dt>
                    <dd>
                        <ul>
                            <li>Database and Object cache in memory instead of on disk.</li>
                            <li>More secure and faster Memcached using Unix socket files.</li>
                            <li>Significant improvement in page load times, especially on pages that can not use full page cache such as wp-admin</li>
                        </ul>
                    </dd>
                </div>
			</div>
		</div>

		$feedback

	</div>

	<div style="clear:both;padding:10px;"></div>
</section>


	<script>
			if(document.location.hash != ""){
				switch(document.location.hash.replace("#","")){
					case 'optimization-status-tab':
						document.getElementById("li-optimization-status").setAttribute("class","active");
						document.getElementById("optimization-status").setAttribute("class","tab-pane active");
						break;
					case 'optimization-warnings-tab':
						document.getElementById("li-optimization-warnings").setAttribute("class","active");
						document.getElementById("optimization-warnings").setAttribute("class","tab-pane active");
						break;
					case "optimization-plugins-tab":
						document.getElementById("li-optimization-plugins").setAttribute("class","active");
						document.getElementById("optimization-plugins").setAttribute("class","tab-pane active");
						break;
					case "optimization-advanced-tab":
						document.getElementById("li-optimization-advanced").setAttribute("class","active");
						document.getElementById("optimization-advanced").setAttribute("class","tab-pane active");
						break;
					case "optimization-about-tab":
						document.getElementById("li-optimization-about").setAttribute("class","active");
						document.getElementById("optimization-about").setAttribute("class","tab-pane active");
						break;
					default:
						document.getElementById("li-optimization-status").setAttribute("class","active");
						document.getElementById("optimization-status").setAttribute("class","tab-pane active");
				}
			}
			else{
				document.getElementById("li-optimization-status").setAttribute("class","active");
				document.getElementById("optimization-status").setAttribute("class","tab-pane active");
			}
		</script>

HTML;
	}

	/**
	 * Wizard to install the W3TC plugin
	 * @param integer $setup_step The step to begin install process
	 *
	 */
	private function newuser_wizard_html($setup_step = 1) {
		$image_dir = plugins_url('/assets/images', __FILE__);
		$kb_search_box = $this->kb_searchbox_html();

		if ($setup_step == 1) {
			echo <<<HTML
<section id="a2opt-content-general">
	<div  class="wrap">
		<div>
			<div>
				<div>
					<div style="float:left;clear:both">
						<img src="{$image_dir}/a2optimized.png"  style="margin-top:20px" />
					</div>
					<div style="float:right;">
						{$kb_search_box}
					</div>
				</div>
				<div style="clear:both;"></div>
			</div>
		</div>
		<div class="tab-content">
			<h3>Downloading A2 W3 Total Cache plugin</h3>
			<p class='loading-spinner'><img src='{$image_dir}/spinner.gif' style='height: auto; width: 50px;' /></p>
HTML;

			if ($this->is_plugin_installed('a2-w3-total-cache/a2-w3-total-cache.php')) {
				$plugin_install_output = "<p>W3 Total Cache has now been successfully downloaded. Next we will activate the plugin.</p><p><a href='" . admin_url('admin.php?a2-page=newuser_wizard&page=A2_Optimized_Plugin_admin&step=2') . "' class='btn btn-success'>Activate</a></p>";
			} else {
				$plugin_install = $this->install_plugin('a2-w3-total-cache');
				if ($plugin_install) {
					$plugin_install_output = "<p>W3 Total Cache has now been successfully downloaded. Next we will activate the plugin.</p><p><a href='" . admin_url('admin.php?a2-page=newuser_wizard&page=A2_Optimized_Plugin_admin&step=2') . "' class='btn btn-success'>Activate</a></p>";
				} else {
					$plugin_install_output = "<p class='text-danger'>We couldn’t install the new plugin to your site. This is usually caused by permission issues or low disk space. You may need to contact your web host for more information.</p><p>You may also download the zip archive of the plugin below and attempt to install it manually.</p><p><a href='https://wp-plugins.a2hosting.com/wp-content/uploads/rkv-repo/a2-w3-total-cache.zip' class='btn btn-info' target='_blank'>Download ZIP</a>";
				}
			}
			echo <<<HTML
			<div>
				{$plugin_install_output}
			</div>
		</div>
		$feedback

	</div>

	<div style="clear:both;padding:10px;"></div>
</section>
<style>
.loading-spinner { display: none; }
</style>
HTML;
		}

		if ($setup_step == 2) {
			$this->activate_plugin('a2-w3-total-cache/a2-w3-total-cache.php');
			$admin_url = admin_url('admin.php?page=A2_Optimized_Plugin_admin');

			echo <<<HTML
<section id="a2opt-content-general">
	<div  class="wrap">
		<div>
			<div>
				<div>
					<div style="float:left;clear:both">
						<img src="{$image_dir}/a2optimized.png"  style="margin-top:20px" />
					</div>
					<div style="float:right;">
						{$kb_search_box}
					</div>
				</div>
				<div style="clear:both;"></div>
			</div>
		</div>
		<div class="tab-content">
			<h3>Congratulations!</h3>
			<div>
				<p>W3 Total Cache is now installed. Let’s get started with the configuration.</p>
				<p><a href='{$admin_url}' class='btn btn-success'>Start Configuration</a></p>
			</div>
		</div>
		$feedback

	</div>

	<div style="clear:both;padding:10px;"></div>
</section>

HTML;
		}
	}

	/**
	 * Wizard to upgrade the W3TC plugin installation
	 *
	 * @param integer $setup_step The step to begin install process
	 *
	 */
	private function upgrade_wizard_html($setup_step = 1) {
		$image_dir = plugins_url('/assets/images', __FILE__);
		$kb_search_box = $this->kb_searchbox_html();
		$admin_url = admin_url('admin.php?a2-page=newuser_wizard&page=A2_Optimized_Plugin_admin&step=1');

		if ($setup_step == 1) {
			echo <<<HTML
<section id="a2opt-content-general">
	<div  class="wrap">
		<div>
			<div>
				<div>
					<div style="float:left;clear:both">
						<img src="{$image_dir}/a2optimized.png"  style="margin-top:20px" />
					</div>
					<div style="float:right;">
						{$kb_search_box}
					</div>
				</div>
				<div style="clear:both;"></div>
			</div>
		</div>
		<div class="tab-content">
			<h3>Disabling incompatible W3 Total Cache plugin</h3>
			<p class='loading-spinner'><img src='{$image_dir}/spinner.gif' style='height: auto; width: 50px;' /></p>
HTML;
			$this->deactivate_plugin('w3-total-cache/w3-total-cache.php');
			$this->deactivate_plugin('w3-total-cache-fixed/w3-total-cache-fixed.php');
			$plugin_install_output = "<p>W3 Total Cache has been disabled. We will now download a supported version of W3 Total Cache to your site.</p><p><a href='" . $admin_url . "' class='btn btn-success'>Install supported W3 Total Cache</a></p>";

			echo <<<HTML
			<div>
				{$plugin_install_output}
			</div>
		</div>
		$feedback

	</div>

	<div style="clear:both;padding:10px;"></div>
</section>
<style>
.loading-spinner { display: none; }
</style>
HTML;
		}
	}

	/**
	 * reCaptcha Settings Page
	 *
	 */
	private function recaptcha_settings_html() {
		$image_dir = plugins_url('/assets/images', __FILE__);
		$kb_search_box = $this->kb_searchbox_html();
		$admin_url = admin_url('admin.php?a2-page=recaptcha_settings_save&page=A2_Optimized_Plugin_admin&save_settings=1');

		$a2_recaptcha_usecustom = get_option('a2_recaptcha_usecustom');
		$a2_recaptcha_sitekey = esc_textarea(get_option('a2_recaptcha_sitekey'));
		$a2_recaptcha_secretkey = esc_textarea(get_option('a2_recaptcha_secretkey'));
		$a2_recaptcha_theme = get_option('a2_recaptcha_theme');

		$dark_selected = '';
		if ($a2_recaptcha_theme == 'dark') {
			$dark_selected = 'selected';
		}
		$custom_selected = '';
		if ($a2_recaptcha_usecustom) {
			$custom_selected = 'checked';
		}

		echo <<<HTML
<section id="a2opt-content-general">
	<div  class="wrap">
		<div>
			<div>
				<div>
					<div style="float:left;clear:both">
						<img src="{$image_dir}/a2optimized.png"  style="margin-top:20px" />
					</div>
					<div style="float:right;">
						{$kb_search_box}
					</div>
				</div>
				<div style="clear:both;"></div>
			</div>
		</div>
		<div class="tab-content">
			<h3>reCaptcha Settings</h3>
			<div>
				<form action="{$admin_url}" method="POST">
					<div class="form-group">
					    <label>
							<input type="checkbox" name="a2_recaptcha_usecustom" value="1" {$custom_selected}>
					       Use my settings below for reCaptcha
					    </label>
				    </div>
					<div class="form-group">
					    <label for="a2_recaptcha_sitekey">Site Key</label>
					    <input type="text" class="form-control" id="a2_recaptcha_sitekey" name="a2_recaptcha_sitekey" value="{$a2_recaptcha_sitekey}" placeholder="Site Key">
					</div>
					<div class="form-group">
					    <label for="a2_recaptcha_secretkey">Secret Key</label>
					    <input type="text" class="form-control" id="a2_recaptcha_secretkey" name="a2_recaptcha_secretkey"  value="{$a2_recaptcha_secretkey}" placeholder="Secret Key">
					</div>
					<div class="form-group">
					    <label for="exampleInputEmail1">Theme</label>
						<select class="form-control" id="a2_recaptcha_theme" name="a2_recaptcha_theme">
						  <option value="light" >Light</option>
						  <option value="dark" {$dark_selected}>Dark</option>
						</select>
					</div>
					<button type="submit" class="btn btn-success">Save Settings</button>
				</form>
			</div>
		</div>

	</div>

	<div style="clear:both;padding:10px;"></div>
</section>
HTML;
	}

	/**
	 * Save reCaptcha Settings
	 *
	 */
	private function recaptcha_settings_save() {
		update_option('a2_recaptcha_usecustom', sanitize_text_field($_POST['a2_recaptcha_usecustom']));
		update_option('a2_recaptcha_sitekey', sanitize_text_field($_POST['a2_recaptcha_sitekey']));
		update_option('a2_recaptcha_secretkey', sanitize_text_field($_POST['a2_recaptcha_secretkey']));
		update_option('a2_recaptcha_theme', sanitize_text_field($_POST['a2_recaptcha_theme']));
	}

	/**
	 * Knowledge Base Searchbox HMTL
	 *
	 */
	private function kb_searchbox_html() {
		return <<<HTML
<div class='big-search' style="margin-top:34px" >
	<div class='kb-search' >
		<form method="post" action="https://www.a2hosting.com/" target="_blank"  >
			<div class='hiddenFields'>
				<input type="hidden" name="ACT" value="47" />
				<input type="hidden" name="params" value="eyJyZXF1aXJlZCI6ImtleXdvcmRzIn0">
			</div>
			<input type="text" id="kb-search-request" name="keywords" placeholder="Search The A2 Knowledge Base">
			<button class='btn btn-success' type='submit'>Search</button>
		</form>
	</div>
</div>
HTML;
	}

	/**
	 * Get the status of the plugin
	 *
	 */
	public function get_plugin_status() {
		$thisclass = $this;

		$opts = new A2_Optimized_Optimizations($thisclass);
		$this->advanced_optimizations = $opts->get_advanced();
		$this->optimizations = $opts->get_optimizations();
		$this->plugin_list = get_plugins();

		if (isset($_GET['activate'])) {
			foreach ($this->plugin_list as $file => $plugin) {
				if ($_GET['activate'] == $plugin['Name']) {
					$this->activate_plugin($file);
				}
			}
		}

		if (isset($_GET['hide_login_url'])) {
			$this->addOption('hide_login_url', true);
		}

		if (isset($_GET['deactivate'])) {
			foreach ($this->plugin_list as $file => $plugin) {
				if ($_GET['deactivate'] == $plugin['Name']) {
					$this->deactivate_plugin($file);
				}
			}
		}

		if (isset($_GET['delete'])) {
			foreach ($this->plugin_list as $file => $plugin) {
				if ($_GET['delete'] == $plugin['Name']) {
					$this->uninstall_plugin($file);
				}
			}
		}

		if (isset($_GET['disable_optimization'])) {
			$hash = '';

			if (isset($this->optimizations[$_GET['disable_optimization']])) {
				$this->optimizations[$_GET['disable_optimization']]['disable']($_GET['disable_optimization']);
			}

			if (isset($this->advanced_optimizations[$_GET['disable_optimization']])) {
				$this->advanced_optimizations[$_GET['disable_optimization']]['disable']($_GET['disable_optimization']);
				$hash = '#optimization-advanced-tab';
			}

			echo <<<JAVASCRIPT
<script type="text/javascript">
	window.location = 'admin.php?page=A2_Optimized_Plugin_admin{$hash}';
</script>
JAVASCRIPT;
			exit();
		}

		if (isset($_GET['enable_optimization'])) {
			$hash = '';
			if (isset($this->optimizations[$_GET['enable_optimization']])) {
				$this->optimizations[$_GET['enable_optimization']]['enable']($_GET['enable_optimization']);
			}

			if (isset($this->advanced_optimizations[$_GET['enable_optimization']])) {
				$this->advanced_optimizations[$_GET['enable_optimization']]['enable']($_GET['enable_optimization']);
				$hash = '#optimization-advanced-tab';
			}

			echo <<<JAVASCRIPT
<script type="text/javascript">
	window.location = 'admin.php?page=A2_Optimized_Plugin_admin{$hash}';
</script>
JAVASCRIPT;
			exit();
		}

		ini_set('disable_functions', '');

		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$plugins_url = plugins_url();
		$plugins_url = explode('/', $plugins_url);
		array_shift($plugins_url);
		array_shift($plugins_url);
		array_shift($plugins_url);
		$this->plugin_dir = ABSPATH . implode('/', $plugins_url);

		$this->plugins_url = plugins_url();

		validate_active_plugins();

		$this->set_install_status('plugins', $this->plugin_list);
	}

	/**
	 * A wrapper function delegating to WP add_option() but it prefixes the input $optionName
	 * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
	 * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
	 * @param  $value mixed the new value
	 * @return null from delegated call to delete_option()
	 */
	public function addOption($optionName, $value) {
		$prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
		return add_option($prefixedOptionName, $value);
	}

	/**
	 * Get the prefixed version input $name suitable for storing in WP options
	 * Idempotent: if $optionName is already prefixed, it is not prefixed again, it is returned without change
	 * @param  $name string option name to prefix. Defined in settings.php and set as keys of $this->optionMetaData
	 * @return string
	 */
	public function prefix($name) {
		$optionNamePrefix = $this->getOptionNamePrefix();
		if (strpos($name, $optionNamePrefix) === 0) { // 0 but not false
			return $name; // already prefixed
		}

		return $optionNamePrefix . $name;
	}

	public function getOptionNamePrefix() {
		return get_class($this) . '_';
	}

	/**
	 * Deactivate the plugin
	 * @param string $file The name of the plugin to deactivate
	 *
	 */
	public function deactivate_plugin($file) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		if (is_plugin_active($file)) {
			deactivate_plugins($file);
			$this->clear_w3_total_cache();
		}
	}

	/**
	 * Uninstall the plugin
	 * @param string $file The name of the plugin to uninstall
	 * @param boolean $delete Delete plugin files after uninstall
	 *
	 */
	public function uninstall_plugin($file, $delete = true) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$this->deactivate_plugin($file);
		uninstall_plugin($file);
		if ($delete) {
			delete_plugins(array($file));
		}
		unset($this->plugin_list[$file]);
		$this->clear_w3_total_cache();
	}

	/**
	 * Set the install status for the plugin
	 * @param string $name The name of the plugin
	 * @param string $value The status of the plugin
	 *
	 */
	public function set_install_status($name, $value) {
		if (!isset($this->install_status)) {
			$this->install_status = new StdClass;
		}
		$this->install_status->{$name} = $value;
	}

	/**
	 * Define your options meta data here as an array, where each element in the array
	 * @return array of key=>display-name and/or key=>array(display-name, choice1, choice2, ...)
	 * key: an option name for the key (this name will be given a prefix when stored in
	 * the database to ensure it does not conflict with other plugin options)
	 * value: can be one of two things:
	 *   (1) string display name for displaying the name of the option to the user on a web page
	 *   (2) array where the first element is a display name (as above) and the rest of
	 *       the elements are choices of values that the user can select
	 * e.g.
	 * array(
	 *   'item' => 'Item:',             // key => display-name
	 *   'rating' => array(             // key => array ( display-name, choice1, choice2, ...)
	 *       'CanDoOperationX' => array('Can do Operation X', 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber'),
	 *       'Rating:', 'Excellent', 'Good', 'Fair', 'Poor')
	 */
	public function getOptionMetaData() {
		return array();
	}

	private function curl($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$content = curl_exec($ch);
		curl_close($ch);

		return $content;
	}

	/*public function get_litespeed(){
	  return get_option('a2_optimized_litespeed');
	}*/

	/*public function set_litespeed($litespeed = true){
	  update_option('a2_optimized_litespeed',$litespeed);
	}*/

	public function get_optimization_status(&$item, $server_info) {
		if ($item != null) {
			if ($this->is_a2_managed() && isset($item['hide_managed']) && $item['hide_managed'] === true) {
				return;
			}

			$settings_slug = $this->getSettingsSlug();

			if (isset($item['is_configured'])) {
				$item['is_configured']($item);
			}
			$active_color = 'danger';
			$active_text = 'Not Activated';
			$glyph = 'exclamation-sign';
			$links = array();

			$active_class = '';
			if (
				isset($item['plugin']) && $item['plugin'] == 'W3 Total Cache'
				&& (
					$this->is_plugin_installed('a2-w3-total-cache/a2-w3-total-cache.php') === false
					|| is_plugin_active('a2-w3-total-cache/a2-w3-total-cache.php') === false
				)
			) {
				$active_class = 'inactive';
			}

			if ($item['configured']) {
				$active_color = 'success';
				$active_text = 'Configured';
				$glyph = 'ok';

				if (isset($item['disable'])) {
					if (isset($item['remove_link']) && $item['remove_link'] == true && ($server_info->cf || $server_info->gzip || $server_info->br)) {
						// skip adding "disable" link if 'remove_link' key is set and site is behind cloudflare
						// used for Gzip options
					} else {
						$links[] = array("?page=$settings_slug&amp;disable_optimization={$item['slug']}", 'Disable', '_self');
					}
				}
				if (isset($item['settings'])) {
					$links[] = array("{$item['settings']}", 'Configure', '_self');
				}

				if (isset($item['configured_links'])) {
					foreach ($item['configured_links'] as $name => $link) {
						if (gettype($link) == 'array') {
							$links[] = array($link[0], $name, $link[1]);
						} else {
							$links[] = array($link, $name, '_self');
						}
					}
				}
			} elseif (isset($item['partially_configured']) && $item['partially_configured']) {
				$active_color = 'warning';
				$active_text = "Partially Configured. {$item['partially_configured_message']}";
				$glyph = 'warning-sign';

				if (isset($item['disable'])) {
					$links[] = array("?page=$settings_slug&amp;disable_optimization={$item['slug']}", 'Disable', '_self');
				}
				if (isset($item['settings'])) {
					$links[] = array("{$item['settings']}", 'Configure', '_self');
				}

				if (isset($item['partially_configured_links'])) {
					foreach ($item['partially_configured_links'] as $name => $link) {
						if (gettype($link) == 'array') {
							$links[] = array($link[0], $name, $link[1]);
						} else {
							$links[] = array($link, $name, '_self');
						}
					}
				}
			} elseif (isset($item['optional']) && $item['optional']) {
				$active_color = 'warning';
				$active_text = 'Optional';
				$glyph = 'warning-sign';
				if (isset($item['enable']) && $active_class == '') {
					$action_text = 'Enable';
					if (isset($item['update'])) {
						$action_text = 'Update Now';
					}
					$links[] = array("?page=$settings_slug&amp;enable_optimization={$item['slug']}", $action_text, '_self');
				}

				if (isset($item['not_configured_links'])) {
					foreach ($item['not_configured_links'] as $name => $link) {
						if (gettype($link) == 'array') {
							$links[] = array($link[0], $name, $link[1]);
						} else {
							$links[] = array($link, $name, '_self');
						}
					}
				}
			} else {
				if (isset($item['enable']) && $active_class == '') {
					$links[] = array("?page=$settings_slug&amp;enable_optimization={$item['slug']}", 'Enable', '_self');
				}

				if (isset($item['not_configured_links'])) {
					foreach ($item['not_configured_links'] as $name => $link) {
						if (gettype($link) == 'array') {
							$links[] = array($link[0], $name, $link[1]);
						} else {
							$links[] = array($link, $name, '_self');
						}
					}
				}
			}
			if (isset($item['kb'])) {
				$links[] = array($item['kb'], 'Learn More', '_blank');
			}
			$link_html = '';
			foreach ($links as $i => $link) {
				if (isset($link[0]) && isset($link[1]) && isset($link[2])) {
					$link_html .= <<<HTML
	 <a href="{$link[0]}" target="{$link[2]}">{$link[1]}</a> |
HTML;
				}
			}

			$premium = '';
			if (isset($item['premium'])) {
				$premium = '<div style="float:right;padding-right:10px"><a href="https://www.a2hosting.com/wordpress-hosting?utm_source=A2%20Optimized&utm_medium=Referral&utm_campaign=A2%20Optimized" target="_blank" class="a2-exclusive"></a></div>';
			}

			$description = $item['description'];
			if (isset($item['last_updated']) && $item['last_updated']) {
				$description .= 'Last Updated: ';
				if (get_option('a2_updated_' . $item['slug'])) {
					$description .= get_option('a2_updated_' . $item['slug']);
				} else {
					$description .= 'Never';
				}
			}

			$link_html = rtrim($link_html, '|');

			return <<<HTML
<div class="optimization-item {$active_class} {$item['slug']}">
	<div class="optimization-item-one" >
		<span class="glyphicon glyphicon-{$glyph}"></span>
	</div>
	<div class="optimization-item-two">
		<b>{$item['name']}</b><br>
		<span class="{$active_color}">{$active_text}</span>
	</div>
	{$premium}
	<div class="optimization-item-three">
		<p>{$description}</p>
	</div>
	<div class="optimization-item-four">
		{$link_html}
	</div>
</div>
HTML;
		}

		return true;
	}

	/**
	 * Display the warnings for the plugin
	 *
	 * @return array $warnings
	 *
	 */
	private function warnings() {
		$num_warnings = 0;

		$opts = new A2_Optimized_Optimizations($this);
		$warnings = $opts->get_warnings();

		$warning_html = '';

		foreach ($warnings as $type => $warning_set) {
			switch ($type) {
				case 'Bad WP Options':
					foreach ($warning_set as $option_name => $warning) {
						$warn = false;
						$value = get_option($option_name);
						switch ($warning['type']) {
							case 'numeric':
								switch ($warning['threshold_type']) {
									case '>':
										if ($value > $warning['threshold']) {
											$warning_html .= $this->warning_display($warning);
											$num_warnings++;
										}
										break;
									case '<':
										if ($value < $warning['threshold']) {
											$warning_html .= $this->warning_display($warning);
											$num_warnings++;
										}
										break;
									case '=':
										if ($value == $warning['threshold']) {
											$warning_html .= $this->warning_display($warning);
											$num_warnings++;
										}
										break;
								}
								break;
							case 'text':
								switch ($warning['threshold_type']) {
									case '=':
										if ($value == $warning['threshold']) {
											$warning_html .= $this->warning_display($warning);
											$num_warnings++;
										}
										break;
									case '!=':
										if ($value != $warning['threshold']) {
											$warning_html .= $this->warning_display($warning);
											$num_warnings++;
										}
										break;
								}
								break;
							case 'array_count':
								switch ($warning['threshold_type']) {
									case '>':
										if (is_array($value) && count($value) > $warning['threshold']) {
											$warning_html .= $this->warning_display($warning);
											$num_warnings++;
										}
										break;
								}
								break;
						}
					}
					break;
				case 'Advanced Warnings':
					foreach ($warning_set as $name => $warning) {
						if ($warning['is_warning']()) {
							$warning_html .= $this->warning_display($warning);
							$num_warnings++;
						}
					}
					break;
				case 'Bad Plugins':
					foreach ($warning_set as $plugin_folder => $warning) {
						$warn = false;
					}
			}
		}

		$warn = false;
		$plugins = $this->get_plugins();
		foreach ($plugins as $file => $plugin) {
			if (!is_plugin_active($file)) {
				$plugin['file'] = $file;
				$warning_html .= $this->plugin_not_active_warning($plugin);
				$num_warnings++;
			}
		}

		return array($warning_html, $num_warnings);
	}

	/**
	 * Warning display for plugin
	 * @param string $warning The warning for installing or updating plugin
	 *
	 * @return markup HTML the formatted HTML to display plugin warning on web page
	 *
	 */
	private function warning_display($warning) {
		return <<<HTML
<div class="optimization-item">
	<div style="float:left;width:44px;font-size:36px">
		<span class="glyphicon glyphicon-exclamation-sign"></span>
	</div>
	<div style="float:left;">
		<b>{$warning['title']}</b><br>
	</div>
	<div style="clear:both;">
		<p>{$warning['description']}</p>
	</div>
	<div>
		<a href="{$warning['config_url']}" >Configure</a>
	</div>
</div>
HTML;
	}

	/*
	public function plugin_list(){
		//Name,PluginURI,Version,Description,Author,AuthorURI,TextDomain,DomainPath,Network,Title,AuthorName

		$string = "";
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$plugins = $this->get_plugins();
		foreach($plugins as $filename=>$plugin){
			$name = $plugin['Name'];
			$title = $plugin['Title'];
			$checked = "";
			if(is_plugin_active($filename)){
				$checked = "checked='checked'";
			}
			ob_start();
			$dump = ob_get_contents();
			ob_end_clean();
			$string .=<<<HTML
			<div class="wrap">
				<span style="font-size:16pt"><input type="checkbox" $checked> $title</span> <a href="">delete</a>
				{$dump}
			</div>
HTML;

		}
		return $string;
	}*/

	/**
	 * Set the install status for the plugin
	 * @param string $plugin The name of the plugin
	 *
	 * @return markup HTML the formatted HTML to display plugin status on web page
	 *
	 */
	private function plugin_not_active_warning($plugin) {
		$manage = 'plugins.php?plugin_status=inactive';

		return <<<HTML
<div class="optimization-item">
	<div style="float:left;width:44px;font-size:36px">
		<span class="glyphicon glyphicon-exclamation-sign"></span>
	</div>
	<div style="float:left;">
		<b>Inactive Plugin: {$plugin['Name']}</b><br>
	</div>
	<div style="clear:both;">
		<p>Deactivated plugins should be deleted. Deactivating a plugin does not remove the plugin and its files from your website.  Plugins with security flaws may still affect your site even when not active.</p>
		<p>{$plugin['Description']}</p>
	</div>
	<div>
		<a href="{$manage}" >Manage deactivated plugins</a>
	</div>
</div>
HTML;
	}

	/**
	 * Get the advanced optimizations for A2Optimized
	 *
	 * @return array @advanved_optimizations An array of optimization options
	 */
	public function get_advanced_optimizations() {
		return $this->advanced_optimizations;
	}

	/**
	 * Set the lockdown status for the plugin
	 * @param boolean $lockdown Lockdown enabled or disabled
	 *
	 */
	public function set_lockdown($lockdown = true) {
		update_option('a2_optimized_lockdown', $lockdown);
	}

	public function set_nomods($lockdown = true) {
		update_option('a2_optimized_nomods', $lockdown);
	}

	/**
	 * Set the install status for the plugin
	 * @param boolean $deny Deny direct access option
	 *
	 */
	public function set_deny_direct($deny = true) {
		update_option('a2_optimized_deny_direct', $deny);
	}

	/**
	 * Write the config options
	 *
	 */
	public function write_wp_config() {
		$lockdown = $this->get_lockdown();

		$nomods = $this->get_nomods();

		touch(ABSPATH . 'wp-config.php');
		copy(ABSPATH . 'wp-config.php', ABSPATH . 'wp-config.bak-a2.php');

		$a2_config = '';
		if ($lockdown) {
			$a2_config = <<<PHP

// BEGIN A2 CONFIG
define('DISALLOW_FILE_EDIT', true);
// END A2 CONFIG
PHP;
		}

		if ($nomods) {
			$a2_config .= <<<PHP

define('DISALLOW_FILE_MODS', true);

PHP;
		}

		$wpconfig = file_get_contents(ABSPATH . 'wp-config.php');
		$pattern = "/[\r\n]*[\/][\/] BEGIN A2 CONFIG.*[\/][\/] END A2 CONFIG[\r\n]*/msU";
		$wpconfig = preg_replace($pattern, '', $wpconfig);

		$wpconfig = str_replace('<?php', "<?php{$a2_config}", $wpconfig);

		//Write the rules to .htaccess
		$fh = fopen(ABSPATH . 'wp-config.php', 'w+');
		fwrite($fh, $wpconfig);
		fclose($fh);
	}

	/**
	 * Check the theme lockdown option
	 *
	 */
	public function get_lockdown() {
		return get_option('a2_optimized_lockdown');
	}

	/**
	 * Check if the theme has a no modication flag
	 *
	 */
	public function get_nomods() {
		return get_option('a2_optimized_nomods');
	}

	/**
	 * Write WP changes to the .htaccess file
	 *
	 */
	public function write_htaccess() {
		//make sure .htaccess exists
		touch(ABSPATH . '.htaccess');
		touch(ABSPATH . '404.shtml');
		touch(ABSPATH . '403.shtml');

		//make sure it is writable by owner and readable by everybody
		chmod(ABSPATH . '.htaccess', 0644);

		$home_path = explode('/', str_replace(array('http://', 'https://'), '', home_url()), 2);

		if (!isset($home_path[1]) || $home_path[1] == '') {
			$home_path = '/';
		} else {
			$home_path = "/{$home_path[1]}/";
		}

		$a2hardening = '';

		if ($this->get_deny_direct()) {
			//Append the new rules to .htaccess

			//get the path to the WordPress install - nvm
			//$rewrite_base = "/".trim(explode('/',str_replace(array('https://','http://'),'',site_url()),2)[1],"/")."/";

			$a2hardening = <<<APACHE

# BEGIN WordPress Hardening
<FilesMatch "^.*(error_log|wp-config\.php|php.ini|\.[hH][tT][aApP].*)$">
Order deny,allow
Deny from all
</FilesMatch>
<IfModule mod_rewrite.c>
    RewriteBase {$home_path}
    RewriteRule ^wp-admin/includes/ - [F,L]
    RewriteRule !^wp-includes/ - [S=3]
    RewriteRule ^wp-includes/[^/]+\.php$ - [F,L]
    RewriteRule ^wp-includes/js/tinymce/langs/.+\.php - [F,L]
    RewriteRule ^wp-includes/theme-compat/ - [F,L]
    RewriteRule ^wp-config\.php - [F,L]
    RewriteRule ^php\.ini - [F,L]
    RewriteRule \.htaccess - [F,L]
    RewriteCond %{REQUEST_METHOD} POST
    RewriteCond %{REQUEST_URI} .wp-comments-post.php*
    RewriteCond %{HTTP_REFERER} !.*{$_SERVER['HTTP_HOST']}.* [OR]
    RewriteCond %{HTTP_USER_AGENT} ^$
    RewriteRule (.*) - [F,L]
</IfModule>
# END WordPress Hardening
APACHE;
		}

		$litespeed = '';

		$htaccess = file_get_contents(ABSPATH . '.htaccess');

		$pattern = "/[\r\n]*# BEGIN WordPress Hardening.*# END WordPress Hardening[\r\n]*/msiU";
		$htaccess = preg_replace($pattern, '', $htaccess);

		$htaccess = <<<HTACCESS
$litespeed
$a2hardening
$htaccess
HTACCESS;

		//Write the rules to .htaccess
		$fp = fopen(ABSPATH . '.htaccess', 'c');

		if (flock($fp, LOCK_EX)) {
			ftruncate($fp, 0);	  // truncate file
			fwrite($fp, $htaccess);
			fflush($fp);			// flush output before releasing the lock
			flock($fp, LOCK_UN);	// release the lock
		} else {
			//no file lock :(
		}
	}

	public function get_deny_direct() {
		return get_option('a2_optimized_deny_direct');
	}

	/**
	 * A wrapper function delegating to WP delete_option() but it prefixes the input $optionName
	 * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
	 * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
	 * @return bool from delegated call to delete_option()
	 */
	public function deleteOption($optionName) {
		$prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
		return delete_option($prefixedOptionName);
	}

	/**
	 * A wrapper function delegating to WP add_option() but it prefixes the input $optionName
	 * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
	 * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
	 * @param  $value mixed the new value
	 * @return null from delegated call to delete_option()
	 */
	public function updateOption($optionName, $value) {
		$prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
		return update_option($prefixedOptionName, $value);
	}

	/**
	 * Checks if a particular user has a role.
	 * Returns true if a match was found.
	 *
	 * @param string $role Role name.
	 * @param int $user_id (Optional) The ID of a user. Defaults to the current user.
	 * @return bool
	 */
	public function checkUserRole($role, $user_id = null) {
		if (is_numeric($user_id)) {
			$user = get_userdata($user_id);
		} else {
			$user = wp_get_current_user();
		}

		return empty($user) ? false : in_array($role, (array)$user->roles);
	}

	/**
	 * A wrapper function delegating to WP get_option() but it prefixes the input $optionName
	 * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
	 * @param $optionName string defined in settings.php and set as keys of $this->optionMetaData
	 * @param $default string default value to return if the option is not set
	 * @return string the value from delegated call to get_option(), or optional default value
	 * if option is not set.
	 */
	public function getOption($optionName, $default = null) {
		$prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
		$retVal = get_option($prefixedOptionName);
		if (!$retVal && $default) {
			$retVal = $default;
		}

		return $retVal;
	}

	/**
	 * @param $roleName string a standard WP role name like 'Administrator'
	 * @return bool
	 */
	public function isUserRoleEqualOrBetterThan($roleName) {
		if ('Anyone' == $roleName) {
			return true;
		}
		$capability = $this->roleToCapability($roleName);

		return $this->checkUserCapability($capability);
	}

	/**
	 * Given a WP role name, return a WP capability which only that role and roles above it have
	 * http://codex.wordpress.org/Roles_and_Capabilities
	 * @param  $roleName
	 * @return string a WP capability or '' if unknown input role
	 */
	protected function roleToCapability($roleName) {
		switch ($roleName) {
			case 'Super Admin':
				return 'manage_options';
			case 'Administrator':
				return 'manage_options';
			case 'Editor':
				return 'publish_pages';
			case 'Author':
				return 'publish_posts';
			case 'Contributor':
				return 'edit_posts';
			case 'Subscriber':
				return 'read';
			case 'Anyone':
				return 'read';
		}

		return '';
	}

	/**
	 * Checks if a particular user has a given capability without calling current_user_can.
	 * Returns true if a match was found.
	 *
	 * @param string $capability Capability name.
	 * @param int $user_id (Optional) The ID of a user. Defaults to the current user.
	 * @return bool
	 */
	public function checkUserCapability($capability, $user_id = null) {
		if (!is_numeric($user_id)) {
			$user = wp_get_current_user();
		} else {
			$user = get_userdata($user_id);
		}

		if (is_object($user)) {
			$capabilities = (array)$user->allcaps;

			if (isset($capabilities[$capability])) {
				return $capabilities[$capability];
			}
		}

		return false;
	}

	/**
	 * Display plugin name, status and description
	 * @param array $plugin The plugin attributes
	 * @return markup HTML  The plugin information in HTML format
	 */
	private function plugin_display($plugin) {
		$links['Delete'] = admin_url() . 'admin.php?page=' . $this->getSettingsSlug() . "&delete={$plugin['Name']}";

		$glyph = 'warning-sign';
		if (!$plugin['active']) {
			if ($plugin['optional']) {
				$glyph = 'warning-sign';
			} else {
				$glyph = 'exclamation-sign';
			}
			$links['Activate'] = admin_url() . 'admin.php?page=' . $this->getSettingsSlug() . "&activate={$plugin['Name']}";
		} else {
			$glyph = 'ok';
			$links['Deactivate'] = admin_url() . 'admin.php?page=' . $this->getSettingsSlug() . "&deactivate={$plugin['Name']}";
			if (isset($plugin['config_url'])) {
				$links['Configure'] = $plugin['config_url'];
			}
		}

		$link_html = '';
		foreach ($links as $name => $href) {
			$link_html .= <<<HTML
<a href="{$href}">$name</a> |
HTML;
		}

		$link_html = trim($link_html, ' |');

		return <<<HTML
<div class="optimization-item">
	<div style="float:left;width:44px;font-size:36px">
		<span class="glyphicon glyphicon-{$glyph}"></span>
	</div>
	<div style="float:left;">
		<b>{$plugin['Name']}</b><br>
	</div>
	<div style="clear:both;">
		<p>{$plugin['Description']}</p>
	</div>
	<div>
		{$link_html}
	</div>
</div>
HTML;
	}

	/**
	 * Check Check for the correct a2_optimized directory
	 * @return boolean true|false
	 */
	protected function is_a2() {
		if ( is_dir('/opt/a2-optimized') ) {
			return true;
		}

		return false;
	}

	/**
	 * Check to see if this is a ManagedWP install
	 *
	 * return bool
	 */
	protected function is_a2_managed() {
		return file_exists('/opt/a2-optimized/wordpress/a2managed');
	}

	/**
	 * Check for installed plugin
	 * @param string $slug The plugin that we check for installation
	 * @return boolean true|false
	 */
	private function is_plugin_installed($slug) {
		$plugins = get_plugins();
		if (array_key_exists($slug, $plugins)) {
			return true;
		}

		return false;
	}

	/**
	 * Check for a valid and active w3tc plugin
	 * @return boolean true|false
	 */
	private function is_valid_w3tc_installed() {
		/* W3 Total Cache Offical is not valid */
		if (is_plugin_active('w3-total-cache/w3-total-cache.php')) {
			return false;
		}

		/* W3 Total Cache Fixed < 0.9.5.x is ok */
		if (is_plugin_active('w3-total-cache-fixed/w3-total-cache-fixed.php')) {
			$w3tc_fixed_info = get_plugin_data('w3-total-cache-fixed/w3-total-cache-fixed.php');
			if (version_compare($w3tc_fixed_info['Version'], '0.9.5.0') >= 0) {
				return false;
			} else {
				return true;
			}
		}

		/* A2 Fixed W3TC is ok */
		if (is_plugin_active('a2-w3-total-cache/a2-w3-total-cache.php')) {
			return true;
		}

		return false;
	}

	public function getVersion() {
		return $this->getPluginHeaderValue('Version');
	}

	public function getPluginHeaderValue($key) {
		// Read the string from the comment header of the main plugin file
		$data = file_get_contents($this->getPluginDir() . DIRECTORY_SEPARATOR . $this->getMainPluginFileName());
		$match = array();
		preg_match('/' . $key . ':\s*(\S+)/', $data, $match);
		if (count($match) >= 1) {
			return $match[1];
		}

		return null;
	}

	protected function getPluginDir() {
		return dirname(__FILE__);
	}

	/**
	 * Generates a random string of lower case letters, used for Rename WP Login URL
	 *
	 * @param int $length The length of the random string
	 * @return string $output The random string
	 */
	public function getRandomString($length = 4) {
		$output = '';
		$valid_chars = 'abcdefghijklmnopqrstuvwxyz';
		// count the number of chars in the valid chars string so we know how many choices we have
		$num_valid_chars = strlen($valid_chars);
		// repeat the steps until we've created a string of the right length
		for ($i = 0; $i < $length; $i++) {
			// pick a random number from 1 up to the number of valid chars
			$random_pick = mt_rand(1, $num_valid_chars);
			// take the random character out of the string of valid chars
			// subtract 1 from $random_pick because strings are indexed starting at 0, and we started picking at 1
			$random_char = $valid_chars[$random_pick - 1];
			// add the randomly-chosen char onto the end of our string so far
			$output .= $random_char;
		}

		return $output;
	}

	/**
	 * Get the description for the plugin
	 * @return string $description The description of the plugin
	 */
	public function get_plugin_description() {
		$description = <<<HTML

HTML;

		return $description;
	}
}
