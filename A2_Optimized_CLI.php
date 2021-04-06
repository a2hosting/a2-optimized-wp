<?php
/**
 * Interact with A2 Optimized
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class A2_Optimized_CLI {
	/**
	 * Clear the page cache.
	 *
	 * ## OPTIONS
	 *
	 * [--ids=<id>]
	 * : Clear the cache for given post ID(s). Separate multiple IDs with commas.
	 *
	 * [--urls=<url>]
	 * : Clear the cache for the given URL(s). Separate multiple URLs with commas.
	 *
	 * [--sites=<site>]
	 * : Clear the cache for the given blog ID(s). Separate multiple blog IDs with commas.
	 *
	 * ## EXAMPLES
	 *
	 *    # Clear all pages cache.
	 *    $ wp a2-optimized clear
	 *    Success: Site cache cleared.
	 *
	 *    # Clear the page cache for post IDs 1, 2, and 3.
	 *    $ wp a2-optimized clear --ids=1,2,3
	 *    Success: Pages cache cleared.
	 *
	 *    # Clear the page cache for a particular URL.
	 *    $ wp a2-optimized clear --urls=https://www.example.com/about-us/
	 *    Success: Page cache cleared.
	 *
	 *    # Clear all pages cache for sites with blog IDs 1, 2, and 3.
	 *    $ wp a2-optimized clear --sites=1,2,3
	 *    Success: Sites cache cleared.
	 *
	 * @alias clear
	 */

	public function clear($args, $assoc_args) {
		$assoc_args = wp_parse_args(
			$assoc_args,
			array(
				'ids' => '',
				'urls' => '',
				'sites' => '',
			)
		);

		if ( empty( $assoc_args['ids'] ) && empty( $assoc_args['urls'] ) && empty( $assoc_args['sites'] ) ) {
			A2_Optimized_Cache::clear_complete_cache();

			return WP_CLI::success( ( is_multisite() ) ? esc_html__( 'Network cache cleared.', 'a2-optimized-wp' ) : esc_html__( 'Site cache cleared.', 'a2-optimized-wp' ) );
		}

		if ( ! empty( $assoc_args['ids'] ) || ! empty( $assoc_args['urls'] ) ) {
			array_map( 'A2_Optimized_Cache::clear_page_cache_by_post_id', explode( ',', $assoc_args['ids'] ) );
			array_map( 'A2_Optimized_Cache::clear_page_cache_by_url', explode( ',', $assoc_args['urls'] ) );

			$separators = substr_count( $assoc_args['ids'], ',' ) + substr_count( $assoc_args['urls'], ',' );

			if ( $separators > 0 ) {
				return WP_CLI::success( esc_html__( 'Pages cache cleared.', 'a2-optimized-wp' ) );
			} else {
				return WP_CLI::success( esc_html__( 'Page cache cleared.', 'a2-optimized-wp' ) );
			}
		}

		if ( ! empty( $assoc_args['sites'] ) ) {
			array_map( 'A2_Optimized_Cache::clear_site_cache_by_blog_id', explode( ',', $assoc_args['sites'] ) );

			$separators = substr_count( $assoc_args['sites'], ',' );

			if ( $separators > 0 ) {
				return WP_CLI::success( esc_html__( 'Sites cache cleared.', 'a2-optimized-wp' ) );
			} else {
				return WP_CLI::success( esc_html__( 'Site cache cleared.', 'a2-optimized-wp' ) );
			}
		}
	}
	
	/**
	 * Enables various parts of the A2 Optimized plugin
	 *
	 * ## OPTIONS
	 *
	 * [module]
	 * Enable the specified module:
	 * - page_cache - Page Caching
	 * - object_cache - Object Caching
	 * - gzip - GZip compression
	 * - html_min - HTML Minification
	 * - cssjs_min - CSS/JS Minification
	 * - xmlrpc - Block XML-RPC requests
	 * - htaccess - Deny access to .htaccess
	 * - lock_plugins - Block editing of Plugins and Themes
	 *
	 * ## EXAMPLES
	 *
	 *    # Enable Page Caching
	 *    $ wp a2-optimized enable page_cache
	 *    Success: Site Page Cache enabled.
	 *
	 */
	
	public function enable($args, $assoc_args) {
		$options_manager = new A2_Optimized_OptionsManager;
		$to_enable = $args[0];

		$site_type = 'Site';
		if (is_multisite()) {
			$site_type = 'Network';
		}

		switch ($to_enable) {
			case 'page_cache':
				$options_manager->enable_a2_page_cache();

				return WP_CLI::success(esc_html__( $site_type . ' Page Cache enabled.', 'a2-optimized-wp' ));
				break;
			case 'object_cache':
				$options_manager->enable_a2_object_cache();
				
				return WP_CLI::success(esc_html__( $site_type . ' Object Cache enabled.', 'a2-optimized-wp' ));
				break;
			case 'gzip':
				$options_manager->enable_a2_page_cache_gzip();

				return WP_CLI::success(esc_html__( $site_type . ' GZIP enabled.', 'a2-optimized-wp' ));
				break;
			case 'html_min':
				$options_manager->enable_a2_page_cache_minify_html();

				return WP_CLI::success(esc_html__( $site_type . ' HTML Minify enabled.', 'a2-optimized-wp' ));
				break;
			case 'cssjs_min':
				$options_manager->enable_a2_page_cache_minify_jscss();

				return WP_CLI::success(esc_html__( $site_type . ' JS/CSS Minify enabled.', 'a2-optimized-wp' ));
				break;
			case 'xmlrpc':
				$options_manager->enable_xmlrpc_requests();

				return WP_CLI::success(esc_html__( $site_type . ' XML-RPC Request Blocking enabled.', 'a2-optimized-wp' ));
				break;
			case 'htaccess':
				$options_manager->set_deny_direct(true);
				$options_manager->write_htaccess();

				return WP_CLI::success(esc_html__( $site_type . ' Deny Direct Access to .htaccess enabled.', 'a2-optimized-wp' ));
				break;
			case 'lock_plugins':
				$options_manager->set_lockdown(true);
				$options_manager->write_wp_config();

				return WP_CLI::success(esc_html__( $site_type . ' Lock editing of Plugins and Themes enabled.', 'a2-optimized-wp' ));
				break;
		}
	}
	
	/**
	 * Disables various parts of the A2 Optimized plugin
	 *
	 * ## OPTIONS
	 *
	 * [module]
	 * Disable the specified module:
	 * - page_cache - Page Caching
	 * - object_cache - Object Caching
	 * - gzip - GZip compression
	 * - html_min - HTML Minification
	 * - cssjs_min - CSS/JS Minification
	 * - xmlrpc - Block XML-RPC requests
	 * - htaccess - Deny access to .htaccess
	 * - lock_plugins - Block editing of Plugins and Themes
	 *
	 * ## EXAMPLES
	 *
	 *    # Disable Page Caching
	 *    $ wp a2-optimized disable page_cache
	 *    Success: Site Page Cache disabled.
	 *
	 */
	
	public function disable($args, $assoc_args) {
		$options_manager = new A2_Optimized_OptionsManager;
		
		$to_disable = $args[0];

		$site_type = 'Site';
		if (is_multisite()) {
			$site_type = 'Network';
		}
		switch ($to_disable) {
			case 'page_cache':
				$options_manager->disable_a2_page_cache();

				return WP_CLI::success(esc_html__( $site_type . ' Page Cache disabled.', 'a2-optimized-wp' ));
				break;
			case 'object_cache':
				$options_manager->disable_a2_object_cache();
				
				return WP_CLI::success(esc_html__( $site_type . ' Object Cache disabled.', 'a2-optimized-wp' ));
				break;
			case 'gzip':
				$options_manager->disable_a2_page_cache_gzip();

				return WP_CLI::success(esc_html__( $site_type . ' GZIP disabled.', 'a2-optimized-wp' ));
				break;
			case 'html_min':
				$options_manager->disable_a2_page_cache_minify_html();

				return WP_CLI::success(esc_html__( $site_type . ' HTML Minify disabled.', 'a2-optimized-wp' ));
				break;
			case 'cssjs_min':
				$options_manager->disable_a2_page_cache_minify_jscss();

				return WP_CLI::success(esc_html__( $site_type . ' JS/CSS Minify disabled.', 'a2-optimized-wp' ));
				break;
			case 'xmlrpc':
				$options_manager->disable_xmlrpc_requests();

				return WP_CLI::success(esc_html__( $site_type . ' XML-RPC Request Blocking disabled.', 'a2-optimized-wp' ));
				break;
			case 'htaccess':
				$options_manager->set_deny_direct(false);
				$options_manager->write_htaccess();

				return WP_CLI::success(esc_html__( $site_type . ' Deny Direct Access to .htaccess disabled.', 'a2-optimized-wp' ));
				break;
			case 'lock_plugins':
				$options_manager->set_lockdown(false);
				$options_manager->write_wp_config();

				return WP_CLI::success(esc_html__( $site_type . ' Lock editing of Plugins and Themes disabled.', 'a2-optimized-wp' ));
				break;
		}
	}
	
	public function memcached_server($args, $assoc_args) {
		$server_address = $args[0];

		$site_type = 'Site';
		if (is_multisite()) {
			$site_type = 'Network';
		}
	
		update_option('a2_optimized_memcached_server', $server_address);

		$options_manager = new A2_Optimized_OptionsManager;
		$options_manager->write_wp_config();

		return WP_CLI::success(esc_html__( $site_type . ' Memcached server updated.', 'a2-optimized-wp' ));
	}
}

// add WP-CLI command
WP_CLI::add_command( 'a2-optimized', 'A2_Optimized_CLI' );
