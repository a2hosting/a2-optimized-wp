<?php
/**
 * A2 Optimized Cache base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class A2_Optimized_Cache {
	/**
	 * initialize plugin
	 *
	 */

	public static function init() {
		new self();
	}

	/**
	 * settings from database
	 *
	 */

	public static $options;

	/**
	 * fire page cache cleared hook
	 *
	 * @var     boolean
	 */

	public static $fire_page_cache_cleared_hook = true;

	/**
	 * constructor
	 *
	 */

	public function __construct() {
		// init hooks
		add_action( 'init', [ 'A2_Optimized_Cache_Engine', 'start' ] );
		add_action( 'init', [ __CLASS__, 'process_clear_cache_request' ] );
		add_action( 'init', [ __CLASS__, 'register_textdomain' ] );

		// public clear cache hooks
		add_action( 'a2opt_cache_clear_complete_cache', [ __CLASS__, 'clear_complete_cache' ] );
		add_action( 'a2opt_cache_clear_site_cache', [ __CLASS__, 'clear_site_cache' ] );
		add_action( 'a2opt_cache_clear_site_cache_by_blog_id', [ __CLASS__, 'clear_site_cache_by_blog_id' ] );
		add_action( 'a2opt_cache_clear_page_cache_by_post_id', [ __CLASS__, 'clear_page_cache_by_post_id' ] );
		add_action( 'a2opt_cache_clear_page_cache_by_url', [ __CLASS__, 'clear_page_cache_by_url' ] );

		// system clear cache hooks
		add_action( '_core_updated_successfully', [ __CLASS__, 'clear_complete_cache' ] );
		add_action( 'upgrader_process_complete', [ __CLASS__, 'on_upgrade' ], 10, 2 );
		add_action( 'switch_theme', [ __CLASS__, 'clear_complete_cache' ] );
		add_action( 'permalink_structure_changed', [ __CLASS__, 'clear_site_cache' ] );
		add_action( 'activated_plugin', [ __CLASS__, 'on_plugin_activation_deactivation' ], 10, 2 );
		add_action( 'deactivated_plugin', [ __CLASS__, 'on_plugin_activation_deactivation' ], 10, 2 );
		add_action( 'save_post', [ __CLASS__, 'on_save_post' ] );
		add_action( 'post_updated', [ __CLASS__, 'on_post_updated' ], 10, 3 );
		add_action( 'wp_trash_post', [ __CLASS__, 'on_trash_post' ] );
		add_action( 'transition_post_status', [ __CLASS__, 'on_transition_post_status' ], 10, 3 );
		add_action( 'comment_post', [ __CLASS__, 'on_comment_post' ], 99, 2 );
		add_action( 'edit_comment', [ __CLASS__, 'on_edit_comment' ], 10, 2 );
		add_action( 'transition_comment_status', [ __CLASS__, 'on_transition_comment_status' ], 10, 3 );

		// third party clear cache hooks
		add_action( 'autoptimize_action_cachepurged', [ __CLASS__, 'clear_complete_cache' ] );
		add_action( 'woocommerce_product_set_stock', [ __CLASS__, 'on_woocommerce_stock_update' ] );
		add_action( 'woocommerce_variation_set_stock', [ __CLASS__, 'on_woocommerce_stock_update' ] );
		add_action( 'woocommerce_product_set_stock_status', [ __CLASS__, 'on_woocommerce_stock_update' ] );
		add_action( 'woocommerce_variation_set_stock_status', [ __CLASS__, 'on_woocommerce_stock_update' ] );

		// multisite hooks
		add_action( 'wp_initialize_site', [ __CLASS__, 'install_later' ] );
		add_action( 'wp_uninitialize_site', [ __CLASS__, 'uninstall_later' ] );

		// settings hooks
		add_action( 'permalink_structure_changed', [ __CLASS__, 'update_backend' ] );
		add_action( 'add_option_a2opt_cache', [ __CLASS__, 'on_update_backend' ], 10, 2 );
		add_action( 'update_option_a2opt_cache', [ __CLASS__, 'on_update_backend' ], 10, 2 );

		// admin bar hook
		add_action( 'admin_bar_menu', [ __CLASS__, 'add_admin_bar_items' ], 90 );

		// admin interface hooks
		if ( is_admin() ) {
			// settings
			add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
			// notices
			add_action( 'admin_notices', [ __CLASS__, 'requirements_check' ] );
			add_action( 'admin_notices', [ __CLASS__, 'cache_cleared_notice' ] );
			add_action( 'network_admin_notices', [ __CLASS__, 'cache_cleared_notice' ] );
		}
	}

	/**
	 * activation hook
	 *
	 * @param   boolean  $network_wide  network activated
	 */

	public static function on_activation($network_wide) {
		// add backend requirements, triggering the settings file(s) to be created
		self::each_site( $network_wide, 'self::update_backend' );

		// configure system files
		A2_Optimized_Cache_Disk::setup();
	}

	/**
	 * upgrade hook
	 *
	 * @param   WP_Upgrader  $obj   upgrade instance
	 * @param   array        $data  update data
	 */

	public static function on_upgrade($obj, $data) {
		// if setting enabled clear site cache on any plugin update
		if ( A2_Optimized_Cache_Engine::$settings['clear_site_cache_on_changed_plugin'] ) {
			self::clear_site_cache();
		}

		// check if A2 Optimized has been updated
		if ( $data['action'] === 'update' && $data['type'] === 'plugin' && array_key_exists( 'plugins', $data ) ) {
			foreach ( (array) $data['plugins'] as $plugin_file ) {
				if ( $plugin_file === A2OPT_BASE ) {
					self::on_a2opt_update();
				}
			}
		}
	}

	/**
	 * Cache update actions
	 *
	 */

	public static function on_a2opt_update() {
		if (get_option('a2_cache_enabled') == 1) {
			// clean system files
			self::each_site( is_multisite(), 'A2_Optimized_Cache_Disk::clean' );

			// configure system files
			A2_Optimized_Cache_Disk::setup();

			// clear complete cache
			self::clear_complete_cache();
		}
	}

	/**
	 * deactivation hook
	 *
	 * @param   boolean  $network_wide  network deactivated
	 */

	public static function on_deactivation($network_wide) {
		// clean system files
		self::each_site( $network_wide, 'A2_Optimized_Cache_Disk::clean' );

		// clear site(s) cache
		self::each_site( $network_wide, 'self::clear_site_cache' );
	}

	/**
	 * uninstall hook
	 *
	 */

	public static function on_uninstall() {
		// uninstall backend requirements
		self::each_site( is_multisite(), 'self::uninstall_backend' );
	}

	/**
	 * install on new site in multisite network
	 *
	 * @param   WP_Site  $new_site  new site instance
	 */

	public static function install_later($new_site) {
		// check if network activated
		if ( ! is_plugin_active_for_network( A2OPT_BASE ) ) {
			return;
		}

		// switch to new site
		switch_to_blog( (int) $new_site->blog_id );

		// add backend requirements, triggering the settings file to be created
		self::update_backend();

		// restore current blog from before new site
		restore_current_blog();
	}

	/**
	 * add or update backend requirements
	 *
	 * @return  $new_option_value  new or current database option value
	 */

	public static function update_backend() {
		// delete user(s) meta key from deleted publishing action (1.5.0)
		delete_metadata( 'user', 0, '_clear_post_cache_on_update', '', true );

		// maybe rename old database option (1.5.0)
		$old_option_value = get_option( 'a2opt-cache' );
		if ( $old_option_value !== false ) {
			delete_option( 'a2opt-cache' );
			add_option( 'a2opt-cache', $old_option_value );
		}

		// get defined settings, fall back to empty array if not found
		$old_option_value = get_option( 'a2opt-cache', [] );

		// update default system settings
		$old_option_value = wp_parse_args( self::get_default_settings( 'system' ), $old_option_value );

		// merge defined settings into default settings
		$new_option_value = wp_parse_args( $old_option_value, self::get_default_settings() );

		// add or update database option
		update_option( 'a2opt-cache', $new_option_value );

		// create settings file if action has not been registered for hook yet, like when in activation hook
		if ( has_action( 'update_option_a2opt_cache', [ __CLASS__, 'on_update_backend' ] ) === false ) {
			A2_Optimized_Cache_Disk::create_settings_file( $new_option_value );
		}

		return $new_option_value;
	}

	/**
	 * add or update database option hook
	 *
	 * @param   mixed  $option            old database option value or name of the option to add
	 * @param   mixed  $new_option_value  new database option value
	 */

	public static function on_update_backend($option, $new_option_value) {
		A2_Optimized_Cache_Disk::create_settings_file( $new_option_value );
	}

	/**
	 * uninstall on deleted site in multisite network
	 *
	 * @param   WP_Site  $old_site  old site instance
	 */

	public static function uninstall_later($old_site) {
		$delete_cache_size_transient = false;

		// clean system files
		A2_Optimized_Cache_Disk::clean();

		// clear site cache of deleted site
		self::clear_site_cache_by_blog_id( (int) $old_site->blog_id, $delete_cache_size_transient );
	}

	/**
	 * uninstall backend requirements
	 *
	 */

	private static function uninstall_backend() {
		// delete database option
		delete_option( 'a2opt-cache' );
	}

	/**
	 * enter each site
	 *
	 * @param   boolean  $network          whether or not each site in network
	 * @param   string   $callback         callback function
	 * @param   array    $callback_params  callback function parameters
	 * @return  array    $callback_return  returned value(s) from callback function
	 */

	private static function each_site($network, $callback, $callback_params = []) {
		$callback_return = [];

		if ( $network ) {
			$blog_ids = self::get_blog_ids();
			// switch to each site in network
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				$callback_return[ $blog_id ] = call_user_func_array( $callback, $callback_params );

				restore_current_blog();
			}
		} else {
			$callback_return[] = (int) call_user_func_array( $callback, $callback_params );
		}

		return $callback_return;
	}

	/**
	 * plugin activation and deactivation hooks
	 *
	 */

	public static function on_plugin_activation_deactivation() {
		// if setting enabled clear site cache on any plugin activation or deactivation
		if ( A2_Optimized_Cache_Engine::$settings['clear_site_cache_on_changed_plugin'] ) {
			self::clear_site_cache();
		}
	}

	/**
	 * get settings from database
	 *
	 * @return  array  $settings  current settings from database
	 */

	public static function get_settings() {
		// get database option value
		$settings = get_option( 'a2opt-cache' );

		// if database option does not exist or settings are outdated
		if ( $settings === false || isset( $settings['version'] ) && $settings['version'] !== A2OPT_VERSION ) {
			$settings = self::update_backend();
		}

		return $settings;
	}

	/**
	 * get blog IDs
	 *
	 * @return  array  $blog_ids  blog IDs
	 */

	private static function get_blog_ids() {
		$blog_ids = [ 1 ];

		if ( is_multisite() ) {
			global $wpdb;
			$blog_ids = array_map( 'absint', $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" ) );
		}

		return $blog_ids;
	}

	/**
	 * get blog path
	 *
	 * @return  string  $blog_path  blog path from site address URL, empty otherwise
	 */

	public static function get_blog_path() {
		$site_url_path = parse_url( home_url(), PHP_URL_PATH );
		$site_url_path = rtrim( $site_url_path, '/' );
		$site_url_path_pieces = explode( '/', $site_url_path );

		// get last piece in case installation is in a subdirectory
		$blog_path = ( ! empty( end( $site_url_path_pieces ) ) ) ? '/' . end( $site_url_path_pieces ) . '/' : '';

		return $blog_path;
	}

	/**
	 * get blog paths
	 *
	 * @return  array  $blog_paths  blog paths
	 */

	public static function get_blog_paths() {
		$blog_paths = [ '/' ];

		if ( is_multisite() ) {
			global $wpdb;
			$blog_paths = $wpdb->get_col( "SELECT path FROM $wpdb->blogs" );
		}

		return $blog_paths;
	}

	/**
	 * get permalink structure
	 *
	 * @return  string  permalink structure
	 */

	private static function get_permalink_structure() {
		// get permalink structure
		$permalink_structure = get_option( 'permalink_structure' );

		// permalink structure is custom and has a trailing slash
		if ( $permalink_structure && preg_match( '/\/$/', $permalink_structure ) ) {
			return 'has_trailing_slash';
		}

		// permalink structure is custom and does not have a trailing slash
		if ( $permalink_structure && ! preg_match( '/\/$/', $permalink_structure ) ) {
			return 'no_trailing_slash';
		}

		// permalink structure is not custom
		if ( empty( $permalink_structure ) ) {
			return 'plain';
		}
	}

	/**
	 * get cache size
	 *
	 * @return  integer  $cache_size  cache size in bytes
	 */

	public static function get_cache_size() {
		$cache_size = get_transient( self::get_cache_size_transient_name() );

		if ( ! $cache_size ) {
			$cache_size = A2_Optimized_Cache_Disk::cache_size();
			set_transient( self::get_cache_size_transient_name(), $cache_size, MINUTE_IN_SECONDS * 15 );
		}

		return $cache_size;
	}

	/**
	 * get the cache size transient name
	 *
	 * @return  string  $transient_name  transient name
	 */

	private static function get_cache_size_transient_name() {
		$transient_name = 'a2opt_cache_cache_size';

		return $transient_name;
	}

	/**
	 * get the cache cleared transient name used for the clear notice
	 *
	 * @return  string  $transient_name  transient name
	 */

	private static function get_cache_cleared_transient_name() {
		$transient_name = 'a2opt_cache_cache_cleared_' . get_current_user_id();

		return $transient_name;
	}

	/**
	 * get default settings
	 *
	 * @param   string  $settings_type                              default `system` settings
	 * @return  array   $system_default_settings|$default_settings  only default system settings or all default settings
	 */

	private static function get_default_settings($settings_type = null) {
		$system_default_settings = [
			'version' => (string) A2OPT_VERSION,
			'permalink_structure' => (string) self::get_permalink_structure(),
		];

		if ( $settings_type === 'system' ) {
			return $system_default_settings;
		}

		$user_default_settings = [
			'cache_expires' => 0,
			'cache_expiry_time' => 0,
			'clear_site_cache_on_saved_post' => 1,
			'clear_site_cache_on_saved_comment' => 1,
			'clear_site_cache_on_changed_plugin' => 1,
			'compress_cache' => 0,
			'convert_image_urls_to_webp' => 0,
			'excluded_post_ids' => '',
			'excluded_page_paths' => '',
			'excluded_query_strings' => '',
			'excluded_cookies' => '',
			'minify_html' => 1,
			'minify_inline_css_js' => 0,
		];

		// merge default settings
		$default_settings = wp_parse_args( $user_default_settings, $system_default_settings );

		return $default_settings;
	}

	/**
	 * add admin bar items
	 *
	 * @param   object  menu properties
	 */

	public static function add_admin_bar_items($wp_admin_bar) {
		// check user role
		if ( ! self::user_can_clear_cache() ) {
			return;
		}

		// set clear cache button title
		$title = ( is_multisite() && is_network_admin() ) ? esc_html__( 'Clear Network Cache', 'a2-optimized-wp' ) : esc_html__( 'Clear Site Cache', 'a2-optimized-wp' );

		// add "Clear Network Cache" or "Clear Site Cache" button in admin bar
		$wp_admin_bar->add_menu(
			[
				'id' => 'a2opt_cache_clear_cache',
				'href' => wp_nonce_url( add_query_arg( [
								'_cache' => 'a2-optimized-wp',
								'_action' => 'clear',
							] ), 'a2opt_cache_clear_cache_nonce' ),
				'parent' => 'top-secondary',
				'title' => '<span class="ab-item">' . $title . '</span>',
				'meta' => [ 'title' => $title ],
			]
		);

		// add "Clear Page Cache" button in admin bar
		if ( ! is_admin() ) {
			$wp_admin_bar->add_menu(
				[
					'id' => 'a2opt_cache_clear_page_cache',
					'href' => wp_nonce_url( add_query_arg( [
									'_cache' => 'a2-optimized-wp',
									'_action' => 'clearurl',
								] ), 'a2opt_cache_clear_cache_nonce' ),
					'parent' => 'top-secondary',
					'title' => '<span class="ab-item">' . esc_html__( 'Clear Page Cache', 'a2-optimized-wp' ) . '</span>',
					'meta' => [ 'title' => esc_html__( 'Clear Page Cache', 'a2-optimized-wp' ) ],
				]
			);
		}
	}

	/**
	 * check if user can clear cache
	 *
	 * @return  boolean  true if user can clear cache, false otherwise
	 */

	private static function user_can_clear_cache() {
		if ( apply_filters( 'a2opt_cache_user_can_clear_cache', current_user_can( 'manage_options' ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * process clear cache request
	 *
	 */

	public static function process_clear_cache_request() {
		// check if clear cache request
		if ( empty( $_GET['_cache'] ) || empty( $_GET['_action'] ) || $_GET['_cache'] !== 'a2-optimized-wp' || ( $_GET['_action'] !== 'clear' && $_GET['_action'] !== 'clearurl' ) ) {
			return;
		}

		// validate nonce
		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'a2opt_cache_clear_cache_nonce' ) ) {
			return;
		}

		// check user role
		if ( ! self::user_can_clear_cache() ) {
			return;
		}

		// clear page cache
		if ( $_GET['_action'] === 'clearurl' ) {
			// set clear URL without query string
			$clear_url = parse_url( home_url(), PHP_URL_SCHEME ) . '://' . parse_url( home_url(), PHP_URL_HOST ) . preg_replace( '/\?.*/', '', $_SERVER['REQUEST_URI'] );
			self::clear_page_cache_by_url( $clear_url );
		// clear site(s) cache
		} elseif ( $_GET['_action'] === 'clear' ) {
			self::each_site( ( is_multisite() && is_network_admin() ), 'self::clear_site_cache' );
		}

		// redirect to same page
		wp_safe_redirect( wp_get_referer() );

		// set transient for clear notice
		if ( is_admin() ) {
			set_transient( self::get_cache_cleared_transient_name(), 1 );
		}

		// clear cache request completed
		exit;
	}

	/**
	 * admin notice after cache has been cleared
	 *
	 */

	public static function cache_cleared_notice() {
		// check user role
		if ( ! self::user_can_clear_cache() ) {
			return;
		}

		if ( get_transient( self::get_cache_cleared_transient_name() ) ) {
			echo sprintf(
				'<div class="notice notice-success is-dismissible"><p><strong>%s</strong></p></div>',
				( is_multisite() && is_network_admin() ) ? esc_html__( 'Network cache cleared.', 'a2-optimized-wp' ) : esc_html__( 'Site cache cleared.', 'a2-optimized-wp' )
			);

			delete_transient( self::get_cache_cleared_transient_name() );
		}
	}

	/**
	 * save post hook
	 *
	 * @param   integer  $post_id  post ID
	 */

	public static function on_save_post($post_id) {
		// if any published post type is created or updated
		if ( get_post_status( $post_id ) === 'publish' ) {
			self::clear_cache_on_post_save( $post_id );
		}
	}

	/**
	 * post updated hook
	 *
	 * @param   integer  $post_id      post ID
	 * @param   WP_Post  $post_after   post instance following the update
	 * @param   WP_Post  $post_before  post instance before the update
	 */

	public static function on_post_updated($post_id, $post_after, $post_before) {
		// if setting disabled and any published post type author changes
		if ( $post_before->post_author !== $post_after->post_author ) {
			if ( ! A2_Optimized_Cache_Engine::$settings['clear_site_cache_on_saved_post'] ) {
				// clear before the update author archives
				self::clear_author_archives_cache_by_user_id( $post_before->post_author );
			}
		}
	}

	/**
	 * trash post hook
	 *
	 * @param   integer  $post_id  post ID
	 */

	public static function on_trash_post($post_id) {
		// if any published post type is trashed
		if ( get_post_status( $post_id ) === 'publish' ) {
			$trashed = true;
			self::clear_cache_on_post_save( $post_id, $trashed );
		}
	}

	/**
	 * transition post status hook
	 *
	 * @param   string   $new_status  new post status
	 * @param   string   $old_status  old post status
	 * @param   WP_Post  $post        post instance
	 */

	public static function on_transition_post_status($new_status, $old_status, $post) {
		// if any published post type status has changed
		if ( $old_status === 'publish' && in_array( $new_status, [ 'future', 'draft', 'pending', 'private' ] ) ) {
			self::clear_cache_on_post_save( $post->ID );
		}
	}

	/**
	 * comment post hook
	 *
	 * @param   integer         $comment_id        comment ID
	 * @param   integer|string  $comment_approved  comment approval status
	 */

	public static function on_comment_post($comment_id, $comment_approved) {
		// if new approved comment is posted
		if ( $comment_approved === 1 ) {
			// if setting enabled clear site cache
			if ( A2_Optimized_Cache_Engine::$settings['clear_site_cache_on_saved_comment'] ) {
				self::clear_site_cache();
			// clear page cache otherwise
			} else {
				self::clear_page_cache_by_post_id( get_comment( $comment_id )->comment_post_ID );
			}
		}
	}

	/**
	 * edit comment hook
	 *
	 * @param   integer  $comment_id    comment ID
	 * @param   array    $comment_data  comment data
	 */

	public static function on_edit_comment($comment_id, $comment_data) {
		$comment_approved = (int) $comment_data['comment_approved'];

		// if approved comment is edited
		if ( $comment_approved === 1 ) {
			// if setting enabled clear site cache
			if ( A2_Optimized_Cache_Engine::$settings['clear_site_cache_on_saved_comment'] ) {
				self::clear_site_cache();
			// clear page cache otherwise
			} else {
				self::clear_page_cache_by_post_id( get_comment( $comment_id )->comment_post_ID );
			}
		}
	}

	/**
	 * transition comment status hook
	 *
	 * @param   integer|string  $new_status  new comment status
	 * @param   integer|string  $old_status  old comment status
	 * @param   WP_Comment      $comment     comment instance
	 */

	public static function on_transition_comment_status($new_status, $old_status, $comment) {
		// if comment status has changed from or to approved
		if ( $old_status === 'approved' || $new_status === 'approved' ) {
			// if setting enabled clear site cache
			if ( A2_Optimized_Cache_Engine::$settings['clear_site_cache_on_saved_comment'] ) {
				self::clear_site_cache();
			// clear page cache otherwise
			} else {
				self::clear_page_cache_by_post_id( $comment->comment_post_ID );
			}
		}
	}

	/**
	 * WooCommerce stock hooks
	 *
	 * @param   integer|WC_Product  $product  product ID or product instance
	 */

	public static function on_woocommerce_stock_update($product) {
		// get product ID
		if ( is_int( $product ) ) {
			$product_id = $product;
		} else {
			$product_id = $product->get_id();
		}

		self::clear_cache_on_post_save( $product_id );
	}

	/**
	 * clear complete cache
	 *
	 */

	public static function clear_complete_cache() {
		// clear site(s) cache
		self::each_site( is_multisite(), 'self::clear_site_cache' );

		// delete cache size transient(s)
		self::each_site( is_multisite(), 'delete_transient', [ self::get_cache_size_transient_name() ] );
	}

	/**
	 * clear site cache
	 *
	 */

	public static function clear_site_cache() {
		self::clear_site_cache_by_blog_id( get_current_blog_id() );
	}

	/**
	 * clear cached pages that might have changed from any new or updated post
	 *
	 * @param   WP_Post  $post  post instance
	 */

	public static function clear_associated_cache($post) {
		// clear post type archives
		self::clear_post_type_archives_cache( $post->post_type );

		// clear taxonomies archives
		self::clear_taxonomies_archives_cache_by_post_id( $post->ID );

		if ( $post->post_type === 'post' ) {
			// clear author archives
			self::clear_author_archives_cache_by_user_id( $post->post_author );
			// clear date archives
			self::clear_date_archives_cache_by_post_id( $post->ID );
		}
	}

	/**
	 * clear post type archives page cache
	 *
	 * @param   string  $post_type  post type
	 */

	public static function clear_post_type_archives_cache($post_type) {
		// get post type archives URL
		$post_type_archives_url = get_post_type_archive_link( $post_type );

		if ( ! empty( $post_type_archives_url ) ) {
			// clear post type archives page and its pagination page(s) cache
			self::clear_page_cache_by_url( $post_type_archives_url, 'pagination' );
		}
	}

	/**
	 * clear taxonomies archives pages cache by post ID
	 *
	 * @param   integer  $post_id  post ID
	 */

	public static function clear_taxonomies_archives_cache_by_post_id($post_id) {
		// get taxonomies
		$taxonomies = get_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {
			if ( wp_count_terms( $taxonomy ) > 0 ) {
				// get terms attached to post
				$term_ids = wp_get_post_terms( $post_id, $taxonomy, [ 'fields' => 'ids' ] );
				foreach ( $term_ids as $term_id ) {
					$term_archives_url = get_term_link( (int) $term_id, $taxonomy );
					// validate URL and ensure it does not have a query string
					if ( filter_var( $term_archives_url, FILTER_VALIDATE_URL ) && ! filter_var( $term_archives_url, FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED ) ) {
						// clear taxonomy archives page and its pagination page(s) cache
						self::clear_page_cache_by_url( $term_archives_url, 'pagination' );
					}
				}
			}
		}
	}

	/**
	 * clear author archives page cache by user ID
	 *
	 * @param   integer  $user_id  user ID of the author
	 */

	public static function clear_author_archives_cache_by_user_id($user_id) {
		// get author archives URL
		$author_username = get_the_author_meta( 'user_login', $user_id );
		$author_base = $GLOBALS['wp_rewrite']->author_base;
		$author_archives_url = home_url( '/' ) . $author_base . '/' . $author_username;

		// clear author archives page and its pagination page(s) cache
		self::clear_page_cache_by_url( $author_archives_url, 'pagination' );
	}

	/**
	 * clear date archives pages cache
	 *
	 * @param   integer  $post_id  post ID
	 */

	public static function clear_date_archives_cache_by_post_id($post_id) {
		// get post dates
		$post_date_day = get_the_date( 'd', $post_id );
		$post_date_month = get_the_date( 'm', $post_id );
		$post_date_year = get_the_date( 'Y', $post_id );

		// get post dates archives URLs
		$date_archives_day_url = get_day_link( $post_date_year, $post_date_month, $post_date_day );
		$date_archives_month_url = get_month_link( $post_date_year, $post_date_month );
		$date_archives_year_url = get_year_link( $post_date_year );

		// clear date archives pages and their pagination pages cache
		self::clear_page_cache_by_url( $date_archives_day_url, 'pagination' );
		self::clear_page_cache_by_url( $date_archives_month_url, 'pagination' );
		self::clear_page_cache_by_url( $date_archives_year_url, 'pagination' );
	}

	/**
	 * clear page cache by post ID
	 *
	 * @param   integer|string  $post_id     post ID
	 * @param   string          $clear_type  clear the `pagination` cache or all `subpages` cache instead of only the `page` cache
	 */

	public static function clear_page_cache_by_post_id($post_id, $clear_type = 'page') {
		// validate integer
		if ( ! is_int( $post_id ) ) {
			// if string try to convert to integer
			$post_id = (int) $post_id;
			// conversion failed
			if ( ! $post_id ) {
				return;
			}
		}

		// clear page cache
		self::clear_page_cache_by_url( get_permalink( $post_id ), $clear_type );
	}

	/**
	 * clear page cache by URL
	 *
	 * @param   string  $clear_url   full URL to potentially cached page
	 * @param   string  $clear_type  clear the `pagination` cache or all `subpages` cache instead of only the `page` cache
	 */

	public static function clear_page_cache_by_url($clear_url, $clear_type = 'page') {
		A2_Optimized_Cache_Disk::clear_cache( $clear_url, $clear_type );
	}

	/**
	 * clear site cache by blog ID
	 *
	 * @param   integer|string  $blog_id                      blog ID
	 * @param   boolean         $delete_cache_size_transient  whether or not the cache size transient should be deleted
	 */

	public static function clear_site_cache_by_blog_id($blog_id, $delete_cache_size_transient = true) {
		// validate integer
		if ( ! is_int( $blog_id ) ) {
			// if string try to convert to integer
			$blog_id = (int) $blog_id;
			// conversion failed
			if ( ! $blog_id ) {
				return;
			}
		}

		// check if blog ID exists
		if ( ! in_array( $blog_id, self::get_blog_ids() ) ) {
			return;
		}

		// ensure site cache being cleared is current blog
		if ( is_multisite() ) {
			switch_to_blog( $blog_id );
		}

		// disable page cache cleared hook
		self::$fire_page_cache_cleared_hook = false;

		// get site URL
		$site_url = home_url();

		// get site objects
		$site_objects = A2_Optimized_Cache_Disk::get_site_objects( $site_url );

		// clear all first level pages and subpages cache
		foreach ( $site_objects as $site_object ) {
			self::clear_page_cache_by_url( trailingslashit( $site_url ) . $site_object, 'subpages' );
		}

		// clear home page cache
		self::clear_page_cache_by_url( $site_url );

		// delete cache size transient
		if ( $delete_cache_size_transient ) {
			delete_transient( self::get_cache_size_transient_name() );
		}

		// restore current blog from before site cache being cleared
		if ( is_multisite() ) {
			restore_current_blog();
		}
	}

	/**
	 * clear cache when any post type is created or updated
	 *
	 * @param   integer  $post_id  post ID
	 * @param   boolean  $trashed  whether this is an existing post being trashed
	 */

	public static function clear_cache_on_post_save($post_id, $trashed = false) {
		// get post data
		$post = get_post( $post_id );

		// if setting enabled clear site cache
		if ( A2_Optimized_Cache_Engine::$settings['clear_site_cache_on_saved_post'] ) {
			self::clear_site_cache();
		// clear page and/or associated cache otherwise
		} else {
			// if updated or trashed clear page cache
			if ( strtotime( $post->post_modified_gmt ) > strtotime( $post->post_date_gmt ) || $trashed ) {
				self::clear_page_cache_by_post_id( $post_id );
			}

			// clear associated cache
			self::clear_associated_cache( $post );
		}
	}

	/**
	 * check plugin requirements
	 *
	 */

	public static function requirements_check() {
		//confirm
		// check user role
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// check advanced-cache.php drop-in exists
		if ( ! file_exists( WP_CONTENT_DIR . '/advanced-cache.php' ) && !is_plugin_active('litespeed-cache/litespeed-cache.php') && get_option('a2_cache_enabled') == 1) {
			echo sprintf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				sprintf(
					// translators: 1. A2 Optimized 2. advanced-cache.php 3. wp-content/plugins/a2-optimized-wp 4. wp-content
					esc_html__( '%1$s requires the %2$s drop-in. Please disable and then re-enable the "Page Caching" setting in A2 Optimized to automatically copy this file or manually copy it from the %3$s directory to the %4$s directory.', 'a2-optimized-wp' ),
					'<strong>A2 Optimized</strong>',
					'<code>advanced-cache.php</code>',
					'<code>wp-content/plugins/a2-optimized-wp</code>',
					'<code>wp-content</code>'
				)
			);
		}

		// check advanced-cache.php drop-in is ours
		if (file_exists( WP_CONTENT_DIR . '/advanced-cache.php' ) && !is_plugin_active('litespeed-cache/litespeed-cache.php') && get_option('a2_cache_enabled') == 1) {
			$existing_hash = md5(WP_CONTENT_DIR . '/advanced-cache.php');
			$plugin_hash = md5(A2OPT_DIR . '/advanced-cache.php');
			if($plugin_hash != $existing_hash){
				@unlink( WP_CONTENT_DIR . '/advanced-cache.php' );
				A2_Optimized_Cache_Disk::setup();
			}
		}

		// check permalink structure
		if ( A2_Optimized_Cache_Engine::$settings['permalink_structure'] === 'plain' && current_user_can( 'manage_options' ) ) {
			echo sprintf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				sprintf(
					// translators: 1. A2 Optimized 2. Permalink Settings
					esc_html__( '%1$s requires a custom permalink structure. Please enable a custom structure in the %2$s.', 'a2-optimized-wp' ),
					'<strong>A2 Optimized</strong>',
					sprintf(
						'<a href="%s">%s</a>',
						admin_url( 'options-permalink.php' ),
						esc_html__( 'Permalink Settings', 'a2-optimized-wp' )
					)
				)
			);
		}

		// check file permissions
		if ( file_exists( dirname( A2_Optimized_Cache_Disk::$cache_dir ) ) && ! is_writable( dirname( A2_Optimized_Cache_Disk::$cache_dir ) ) ) {
			echo sprintf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				sprintf(
					// translators: 1. A2 Optimized 2. 755 3. wp-content/cache 4. file permissions
					esc_html__( '%1$s requires write permissions %2$s in the %3$s directory. Please change the %4$s.', 'a2-optimized-wp' ),
					'<strong>A2 Optimized</strong>',
					'<code>755</code>',
					'<code>wp-content/cache</code>',
					sprintf(
						'<a href="%s" target="_blank" rel="nofollow noopener">%s</a>',
						'https://wordpress.org/support/article/changing-file-permissions/',
						esc_html__( 'file permissions', 'a2-optimized-wp' )
					)
				)
			);
		}

		// check Autoptimize HTML optimization
		if ( defined( 'AUTOPTIMIZE_PLUGIN_DIR' ) && A2_Optimized_Cache_Engine::$settings['minify_html'] && get_option( 'autoptimize_html', '' ) !== '' ) {
			echo sprintf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				sprintf(
					// translators: 1. Autoptimize 2. A2 Optimized Settings
					esc_html__( '%1$s HTML optimization is enabled. Please disable HTML minification in the %2$s.', 'a2-optimized-wp' ),
					'<strong>Autoptimize</strong>',
					sprintf(
						'<a href="%s">%s</a>',
						admin_url( 'admin.php?page=a2-optimized-wp' ),
						esc_html__( 'A2 Optimized Settings', 'a2-optimized-wp' )
					)
				)
			);
		}
	}

	/**
	 * register textdomain
	 *
	 */

	public static function register_textdomain() {
		// load translated strings
		load_plugin_textdomain( 'a2-optimized-wp', false, 'a2-optimized-wp/lang' );
	}

	/**
	 * register settings
	 *
	 */

	public static function register_settings() {
		register_setting( 'a2opt-cache', 'a2opt-cache', [ __CLASS__, 'validate_settings' ] );
		register_setting( 'a2opt-cache', 'a2_optimized_objectcache_type' );
		register_setting( 'a2opt-cache', 'a2_optimized_memcached_server', [ __CLASS__, 'validate_object_cache' ]);
		register_setting( 'a2opt-cache', 'a2_optimized_redis_server', [ __CLASS__, 'validate_object_cache' ]);
	}

	/**
	 * validate regex
	 *
	 * @param   string  $regex            string containing regex
	 * @return  string  $validated_regex  string containing regex or empty string if input is invalid
	 */

	public static function validate_regex($regex) {
		if ( ! empty( $regex ) ) {
			if ( ! preg_match( '/^\/.*\/$/', $regex ) ) {
				$regex = '/' . $regex . '/';
			}

			if ( @preg_match( $regex, null ) === false ) {
				return '';
			}

			$validated_regex = sanitize_text_field( $regex );

			return $validated_regex;
		}

		return '';
	}

	/**
	 * validate settings
	 *
	 * @param   array  $settings            user defined settings
	 * @return  array  $validated_settings  validated settings
	 */

	public static function validate_settings($settings) {
		// validate array
		if ( ! is_array( $settings ) ) {
			return;
		}

		$validated_settings = [
			'cache_expires' => (int) ( ! empty( $settings['cache_expires'] ) ),
			'cache_expiry_time' => (int) @$settings['cache_expiry_time'],
			'clear_site_cache_on_saved_post' => (int) ( ! empty( $settings['clear_site_cache_on_saved_post'] ) ),
			'clear_site_cache_on_saved_comment' => (int) ( ! empty( $settings['clear_site_cache_on_saved_comment'] ) ),
			'clear_site_cache_on_changed_plugin' => (int) ( ! empty( $settings['clear_site_cache_on_changed_plugin'] ) ),
			'compress_cache' => (int) ( ! empty( $settings['compress_cache'] ) ),
			'convert_image_urls_to_webp' => (int) ( ! empty( $settings['convert_image_urls_to_webp'] ) ),
			'excluded_post_ids' => (string) sanitize_text_field( @$settings['excluded_post_ids'] ),
			'excluded_page_paths' => (string) self::validate_regex( @$settings['excluded_page_paths'] ),
			'excluded_query_strings' => (string) self::validate_regex( @$settings['excluded_query_strings'] ),
			'excluded_cookies' => (string) self::validate_regex( @$settings['excluded_cookies'] ),
			'minify_html' => (int) ( ! empty( $settings['minify_html'] ) ),
			'minify_inline_css_js' => (int) ( ! empty( $settings['minify_inline_css_js'] ) ),
		];
		// add default system settings
		$validated_settings = wp_parse_args( $validated_settings, self::get_default_settings( 'system' ) );

		// check if site cache should be cleared
		if ( ! empty( $settings['clear_site_cache_on_saved_settings'] ) ) {
			self::clear_site_cache();
			set_transient( self::get_cache_cleared_transient_name(), 1 );
		}

		// check if database optimizations should be executed
		if ( ! empty( $settings['apply_db_optimizations_on_saved_settings'] ) ) {
			$a2_db_optimizations = new A2_Optimized_DBOptimizations;
			$a2_db_optimizations->execute_optimizations();
		}

		return $validated_settings;
	}

	/**
	 * validate memcached settings
	 *
	 * @param   string  $settings            user defined settings
	 * @return  string  $validated_settings  validated settings
	 */

	public static function validate_object_cache($server_address) {
		if (!$server_address || strtolower($server_address) === 'false') {
			return;
		}

		$object_cache_type = '';
		if (get_option('a2_optimized_objectcache_type')) {
			$object_cache_type = get_option('a2_optimized_objectcache_type');
		}

		$address_valid = false;

		switch ($object_cache_type) {
			case 'memcached':
				if (class_exists('Memcached')) {
					$memcached = new Memcached;
					if ( 'unix://' == substr( $server_address, 0, 7 ) ) {
						$node = str_replace('unix://', '', $server_address);
						$port = 0;
					} else {
						list( $node, $port ) = explode( ':', $server_address );
						if ( ! $port ) {
							$port = ini_get( 'memcache.default_port' );
						}
						$port = intval( $port );
						if ( ! $port ) {
							$port = 11211;
						}
					}
					$instances[] = [ $node, $port, 1 ];

					$memcached->addServers($instances);
					$memcached_available = $memcached->getStats();
					if ($memcached_available) {
						update_option('litespeed.conf.object-kind', 0);
						update_option('litespeed.conf.object-host', $server_address);
						update_option('litespeed.conf.object-port', 0);
						
						delete_option('a2_optimized_memcached_invalid');
						
						$address_valid = true;
					} else {
						update_option('a2_optimized_memcached_invalid', 'Unable to connect to Memcached Server');
					}
				} else {
					update_option('a2_optimized_memcached_invalid', 'Missing Memcached extension');
				}

				break;
			case 'redis':

				if (class_exists('Redis') && file_exists($server_address)) {
					$conn = new Redis() ;
					$conn->connect( $server_address, 0 ) ;
					$conn->select( 0 ); // default db

					$redis_available = $conn->ping();

					if ($redis_available) {
						update_option('litespeed.conf.object-kind', 1);
						update_option('litespeed.conf.object-host', $server_address);
						update_option('litespeed.conf.object-port', 0);

						delete_option('a2_optimized_memcached_invalid');
						
						$address_valid = true;
					} else {
						update_option('a2_optimized_memcached_invalid', 'Unable to connect to Redis Server');
					}
				} else {
					update_option('a2_optimized_memcached_invalid', 'Missing Redis extension or invalid socket path');
				}

				break;
		}

		if($address_valid){
			return $server_address;
		} else {
			return false;
		}
	}
}
