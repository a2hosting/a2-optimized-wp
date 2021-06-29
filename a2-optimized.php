<?php
/*
	Plugin Name: A2 Optimized WP
	Plugin URI: https://wordpress.org/plugins/a2-optimized/
	Version: 2.1.3.2
	Author: A2 Hosting
	Author URI: https://www.a2hosting.com/
	Description: A2 Optimized - WordPress Optimization Plugin
	Text Domain: a2-optimized
	License: GPLv3
*/

// Prevent direct access to this file
if ( ! defined( 'WPINC' ) ) {
	die;
}

//////////////////////////////////
// Run initialization
/////////////////////////////////

require_once 'A2_Optimized_Plugin.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once 'A2_Optimized_Server_Info.php';
require_once 'A2_Optimized_Cache.php';
require_once 'A2_Optimized_CacheEngine.php';
require_once 'A2_Optimized_CacheDisk.php';

//constants
define( 'A2OPT_VERSION', '2.1' );
define( 'A2OPT_MIN_PHP', '5.6' );
define( 'A2OPT_MIN_WP', '5.1' );
define( 'A2OPT_FILE', __FILE__ );
define( 'A2OPT_BASE', plugin_basename( __FILE__ ) );
define( 'A2OPT_DIR', __DIR__ );

class A2_Optimized {
	public function __construct() {
		if (version_compare(PHP_VERSION, A2OPT_MIN_PHP) < 0) {
			add_action('admin_notices', array(&$this,'A2_Optimized_noticePhpVersionWrong'));

			return;
		}

		$GLOBALS['A2_Plugin_Dir'] = dirname(__FILE__);

		$a2Plugin = new A2_Optimized_Plugin();

		// Install the plugin
		if (!$a2Plugin->isInstalled()) {
			$a2Plugin->install();
		} else {
			// Perform any version-upgrade activities prior to activation (e.g. database changes)
			$a2Plugin->upgrade();
		}

		// Add callbacks to hooks
		$a2Plugin->addActionsAndFilters();

		// Register the Plugin Activation Hook
		register_activation_hook(__FILE__, array(&$a2Plugin, 'activate'));

		// Register the Plugin Deactivation Hook
		register_deactivation_hook(__FILE__, array(&$a2Plugin, 'deactivate'));
	}

	public function A2_Optimized_noticePhpVersionWrong() {
		echo '<div class="notice notice-warning fade is-dismissible">' .
			__('Error: plugin "A2 Optimized" requires a newer version of PHP to be running.', 'a2-optimized') .
			'<br/>' . __('Minimal version of PHP required: ', 'a2-optimized') . '<strong>' . A2OPT_MIN_PHP . '</strong>' .
			'<br/>' . __('Your site is running PHP version: ', 'a2-optimized') . '<strong>' . phpversion() . '</strong>' .
			'<br />' . __(' To learn how to change the version of php running on your site') . ' <a target="_blank" href="http://www.a2hosting.com/kb/cpanel/cpanel-software-and-services/php-version">' . __('read this Knowledge Base Article') . '</a>.' .
			'</div>';
	}

	// add plugin upgrade notification
	public static function showUpgradeNotification($currentPluginMetadata) {
		// Notice Transient
		$upgrade_notices = get_transient('a2_opt_ug_notes');
		if (!$upgrade_notices) {
			$response = wp_remote_get( 'https://wp-plugins.a2hosting.com/wp-json/wp/v2/update_notice?notice_plugin=2' );
			if ( is_array( $response ) ) {
				$upgrade_notices = array();
				$body = json_decode($response['body']); // use the content
				foreach ($body as $item) {
					$upgrade_notices[$item->title->rendered] = 'Version ' . $item->title->rendered . ': ' . strip_tags($item->content->rendered);
				}
				set_transient('a2_opt_ug_notes', $upgrade_notices, 3600 * 12);
			} else {
				return;
			}
		}

		foreach ($upgrade_notices as $ver => $notice) {
			if (version_compare($currentPluginMetadata['Version'], $ver) < 0) {
				echo '</div><p style="background-color: #d54e21; padding: 10px; color: #f9f9f9; margin-top: 10px" class="update-message notice inline notice-warning notice-alt"><strong>Important Upgrade Notice:</strong><br />';
				echo esc_html($notice), '</p><div>';
				break;
			}
		}
	}

	// Remove WooCommerce AJAX calls from homepage if user has selected
	public static function dequeue_woocommerce_cart_fragments() {
		if (is_front_page() && get_option('a2_wc_cart_fragments')) {
			wp_dequeue_script('wc-cart-fragments');
		}
	}
}

if (get_option('a2_cache_enabled') == 1) {
	add_action( 'plugins_loaded', array( 'A2_Optimized_Cache', 'init' ) );
}
register_deactivation_hook( __FILE__, array( 'A2_Optimized_Cache', 'on_deactivation' ) );
register_uninstall_hook( __FILE__, array( 'A2_Optimized_Cache', 'on_uninstall' ) );

$a2opt_class = new A2_Optimized();
add_action('in_plugin_update_message-a2-optimized-wp/a2-optimized.php', array( 'A2_Optimized','showUpgradeNotification'), 10, 2);
add_action( 'wp_enqueue_scripts', array('A2_Optimized', 'dequeue_woocommerce_cart_fragments'), 11, 2);

// load WP-CLI command
if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( 'WP_CLI' ) ) {
	require_once A2OPT_DIR . '/A2_Optimized_CLI.php';
}
