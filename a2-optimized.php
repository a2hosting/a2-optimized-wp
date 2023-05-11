<?php
/**
 * Main Plugin File
 *
 * @link              http://example.com
 * @since             3.0.0
 * @package           A2_Optimized
 *
 * @wordpress-plugin
 * Plugin Name: A2 Optimized WP
 * Plugin URI: https://wordpress.org/plugins/a2-optimized/
 * Version: 3.0.6.3.2
 * Author: A2 Hosting
 * Author URI: https://www.a2hosting.com/
 * Description: Automatically optimizes performance and security. Works together with LiteSpeed Cache.
 * Text Domain: a2-optimized
 * License: GPLv3
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'A2OPT_VERSION', '3.0' );
define( 'A2OPT_FULL_VERSION', '3.0.6.3.2' );
define( 'A2OPT_MIN_PHP', '5.6' );
define( 'A2OPT_MIN_WP', '5.1' );
define( 'A2OPT_FILE', __FILE__ );
define( 'A2OPT_BASE', plugin_basename( __FILE__ ) );
define( 'A2OPT_DIR', __DIR__ );

/**
 * Creates/Maintains the object of Requirements Checker Class
 *
 * @return \A2_Optimized\Includes\Requirements_Checker
 * @since 3.0.0
 */
function plugin_requirements_checker() {
	static $requirements_checker = null;

	if ( null === $requirements_checker ) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-requirements-checker.php';
		$requirements_conf = apply_filters( 'a2_optimized_minimum_requirements', include_once( plugin_dir_path( __FILE__ ) . 'requirements-config.php' ) );
		$requirements_checker = new A2_Optimized\Includes\Requirements_Checker( $requirements_conf );
	}

	return $requirements_checker;
}

/**
 * Begins execution of the plugin.
 *
 * @since    3.0.0
 */
function run_a2_optimized() {
	// If Plugins Requirements are not met.
	if ( ! plugin_requirements_checker()->requirements_met() ) {
		add_action( 'admin_notices', [ plugin_requirements_checker(), 'show_requirements_errors' ] );

		// Deactivate plugin immediately if requirements are not met.
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		deactivate_plugins( plugin_basename( __FILE__ ) );

		return;
	}

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and frontend-facing site hooks.
	 */
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-a2-optimized.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    3.0.0
	 */
	$router_class_name = apply_filters( 'a2_optimized_router_class_name', '\A2_Optimized\Core\Router' );
	$routes = apply_filters( 'a2_optimized_routes_file', plugin_dir_path( __FILE__ ) . 'routes.php' );
	$GLOBALS['a2_optimized'] = new A2_Optimized( $router_class_name, $routes );

	register_activation_hook( __FILE__, [ new A2_Optimized\App\Activator(), 'activate' ] );
	register_deactivation_hook( __FILE__, [ new A2_Optimized\App\Deactivator(), 'deactivate' ] );
	
	new A2_Optimized_Maintenance;

	if (get_option('a2_cache_enabled') == 1) {
		if(in_array('litespeed-cache/litespeed-cache.php', apply_filters('active_plugins', get_option('active_plugins')))){ 
			A2_Optimized_Cache_Disk::clean();
			update_option('a2_cache_enabled', 0);
		} else {
			add_action('plugins_loaded', [ 'A2_Optimized_Cache', 'init' ]);
		}
	}
    if(is_admin()){
		new A2_Optimized_SiteHealth;
		if (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT) {
			add_action('admin_menu', ['A2_Optimized_Optimizations', 'addLockedEditor'], 100, 100);
		}
	}
	if (get_option('A2_Optimized_Plugin_recaptcha', 0) == 1 && !is_admin()) {
		add_action('woocommerce_login_form', ['A2_Optimized_Optimizations', 'login_captcha']);
		add_action('login_form', ['A2_Optimized_Optimizations', 'login_captcha']);
		add_filter('authenticate', ['A2_Optimized_Optimizations', 'captcha_authenticate'], 1, 3);
		add_action('comment_form_after_fields', ['A2_Optimized_Optimizations', 'comment_captcha']);
		add_filter('preprocess_comment', ['A2_Optimized_Optimizations', 'captcha_comment_authenticate'], 1, 3);
	}

	if(file_exists('/opt/a2-optimized/wordpress/class.A2_Optimized_Private_Optimizations_v3.php')){
		require_once('/opt/a2-optimized/wordpress/class.A2_Optimized_Private_Optimizations_v3.php');
		new A2_Optimized_Private_Optimizations(); 
	}

	new A2_Optimized_SiteData;
	$optimizations = new A2_Optimized_Optimizations;

	$optimizations->maybe_display_litespeed_notice();

	if ($optimizations->is_xmlrpc_request() && get_option('a2_block_xmlrpc')) {
		$optimizations->block_xmlrpc_request();
		add_filter('xmlrpc_methods', ['A2_Optimized_Optimizations', 'remove_xmlrpc_methods']);
	}

}

run_a2_optimized();
