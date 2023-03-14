<?php
/**
 * Site Health A2 Optimized Info.
 */
class A2_Optimized_SiteHealth {
	public function __construct() {
		if ( ! $this->allow_load() ) {
			return;
		}
		$this->hooks();
	}

	/**
	 * Indicate if Site Health is allowed to load.
	 *
	 * @return bool
	 */
	private function allow_load() {
		global $wp_version;

		return version_compare( $wp_version, '5.2', '>=' );
	}

	/**
	 * Integration hooks.
	 */
	protected function hooks() {
		add_filter( 'debug_information', array( $this, 'add_debug_section' ) );
		add_filter( 'site_status_tests', array( $this, 'add_site_status_items' ) );

	}


	/**
	 * Add "Save" button to Debug tab
	 *
	 * @param array $tab Current tab being displayed within Site Health
	 *
	 * @return array Array with added A2 Optimized items.
	 */
	public function add_button_to_site_health_info_tab( $tab ) {
		// Do nothing if this is not the "debug" tab.
		if ( 'debug' !== $tab ) {
			return;
		}
		?>
		<div class='health-check-body health-check-debug-tab hide-if-no-js'>
			<a href='admin.php?a2-page=site_health&page=A2_Optimized_Plugin_admin'><button class='button'>Save Report</button></a>
		</div>
		<?php
	}

	/**
	 * Add A2 Optimized items to main Status tab.
	 *
	 * @param array $tests Array of existing site health tests.
	 *
	 * @return array Array with added A2 Optimized items.
	 */
	public function add_site_status_items($tests) {
		if (php_sapi_name() == 'litespeed') {
			// Litespeed server
			$tests['direct']['a2_optimized_turbocache'] = array(
				'label' => 'TurboCache',
				'test' => array( $this, 'a2opt_turbocache_test' ),
			);
		}
		
		$tests['direct']['a2_optimized_revisions'] = array(
			'label' => 'Large amount of revisions',
			'test' => array( $this, 'a2opt_revision_count_test' ),
		);
		
		$tests['direct']['a2_optimized_large_images'] = array(
			'label' => 'Large images in Media Library',
			'test' => array( $this, 'a2opt_large_image_test' ),
		);
		
		$tests['direct']['a2_optimized_blocking_search'] = array(
			'label' => 'Blocking search engines',
			'test' => array( $this, 'a2opt_block_search_test' ),
		);
		
		$tests['direct']['a2_optimized_caching'] = array(
			'label' => 'Caching enabled',
			'test' => array( $this, 'a2opt_cache_test' ),
		);
		
		$tests['direct']['a2_optimized_phpopcache'] = array(
			'label' => 'PHP OP Caching enabled',
			'test' => array( $this, 'a2opt_phpopcache_test' ),
		);
		
		$tests['direct']['a2_optimized_other_cache'] = array(
			'label' => 'Duplicate Caching plugins enabled',
			'test' => array( $this, 'a2opt_other_cache_test' ),
		);
		
		$tests['direct']['a2_optimized_php_memory'] = array(
			'label' => 'PHP has good memory limits',
			'test' => array( $this, 'a2opt_php_memory_test' ),
		);
		
		
		if (is_plugin_active('woocommerce/woocommerce.php')) {
			$tests['direct']['a2_optimized_cart_fragments'] = array(
				'label' => 'Dequeue WooCommerce Cart Fragments AJAX calls',
				'test' => array( $this, 'a2opt_cart_fragments_test' ),
			);
		}
		
		$tests['direct']['a2_optimized_gzip_compression'] = array(
			'label' => 'Gzip Compression Enabled',
			'test' => array( $this, 'a2opt_gzip_test' ),
		);
		
		$tests['direct']['a2_optimized_block_xmlrpc'] = array(
			'label' => 'Block Unauthorized XML-RPC Requests',
			'test' => array( $this, 'a2opt_xml_rpc_test' ),
		);
		
		$tests['direct']['a2_optimized_disable_fileedit'] = array(
			'label' => 'Lock Editing of Plugins and Themes from the WP Admin',
			'test' => array( $this, 'a2opt_file_edit_test' ),
		);
		
		return $tests;
	}

	/**
	 * Check if TurboCache is enabled. This is a litespeed server
	 *
	 * @return array Array with added A2 Optimized items.
	 */
	public function a2opt_turbocache_test() {
		$result = array(
			'label' => __( 'TurboCache is enabled' ),
			'status' => 'good', // Default "passing" section
			'badge' => array(
				'label' => __( 'Performance' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Caching can help load your site more quickly for visitors.' )
			),
			'actions' => '',
			'test' => 'caching_plugin',
		);
  
		if (!is_plugin_active('litespeed-cache/litespeed-cache.php') || !get_option('litespeed.conf.cache')) {
			$result['status'] = 'critical'; // "Critical" section
			$result['label'] = __( 'TurboCache is available but not enabled' );
			$result['description'] = sprintf(
				'<p>%s</p>',
				__( 'TurboCache is not currently enabled on your site. Caching can help load your site more quickly for visitors.' )
			);
			$result['actions'] .= sprintf(
				'<p><a href="%s">%s</a></p>',
				esc_url( admin_url( 'admin.php?page=litespeed-cache#cache' ) ),
				__( 'Enable Caching' )
			);
		}

		return $result;
	}
	
	/**
	 * Check if Revision count is too large
	 *
	 * @return array Array with added A2 Optimized items.
	 */
	public function a2opt_revision_count_test() {
		$result = array(
			'label' => __( 'Reasonable number of post revisions' ),
			'status' => 'good', // Default "passing" section
			'badge' => array(
				'label' => __( 'Performance' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Caching can help load your site more quickly for visitors.' )
			),
			'actions' => '',
			'test' => 'revision_count',
		);
 
		$args = array(
			'numberposts' => -1,
			'post_type' => 'revision'
		);
 
		$post_revisions = get_posts( $args );

		if (count($post_revisions) > 1000) {
			$result['status'] = 'recommended';
			$result['label'] = __( 'Large number of post revisions in the WordPress database' );
			$result['description'] = sprintf(
				'<p>%s</p>',
				__( "WordPress revisions can present a serious problem if you don't manage them properly. Essentially, the more pages and posts you add to your website, the more revisions it'll generate." )
			);
		}

		return $result;
	}
	
	/**
	 * Check if large images are in the media library
	 *
	 * @return array Array with added A2 Optimized items.
	 */
	public function a2opt_large_image_test() {
		$result = array(
			'label' => __( 'Media library images are a reasonable size' ),
			'status' => 'good', // Default "passing" section
			'badge' => array(
				'label' => __( 'Performance' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Large images can lead to poor site performance' )
			),
			'actions' => '',
			'test' => 'large_images',
		);

		$large_image_count = 0;

		$query_images_args = array(
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'post_status' => 'inherit',
			'posts_per_page' => -1,
		);

		$query_images = new WP_Query( $query_images_args );

		foreach ( $query_images->posts as $image ) {
			$attachment_meta = wp_prepare_attachment_for_js($image->ID);

			if ($attachment_meta['filesizeInBytes'] > 1000000) {
				// Image is more than 1mb
				$large_image_count++;
			}
		}

		if ($large_image_count > 3) {
			$result['status'] = 'recommended';
			$result['label'] = ( $large_image_count . ' large images found in media library' );
			$result['description'] = sprintf(
				'<p>%s</p>',
				__( 'Unoptimized images may slow down rendering of your site.' )
			);
		}

		return $result;
	}
	
	/**
	 * Check if discourage search engines is enabled
	 *
	 * @return array Array with added A2 Optimized items.
	 */
	public function a2opt_block_search_test() {
		$result = array(
			'label' => __( 'Search engines are allowed' ),
			'status' => 'good', // Default "passing" section
			'badge' => array(
				'label' => __( 'Performance' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Your site is visable to search engines' )
			),
			'actions' => '',
			'test' => 'blocking_search',
		);

		if (get_option('blog_public') == 0) {
			$result['status'] = 'critical';
			$result['label'] = ( 'Search engine traffic is current blocked' );
			$result['description'] = sprintf(
				'<p>%s</p>',
				__('Your site is currently disallowing traffic from search engines. This could lead to your site being de-indexed.')
			);
		}

		return $result;
	}
	
	/**
	 * Check if caching is enabled
	 *
	 * @return array Array with added A2 Optimized items.
	 */
	public function a2opt_cache_test() {
		$result = array(
			'label' => __( 'Site Caching is enabled' ),
			'status' => 'good', // Default "passing" section
			'badge' => array(
				'label' => __( 'Performance' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Your site is cached and fast!' )
			),
			'actions' => '',
			'test' => 'caching_enabled',
		);

		if (!is_plugin_active('litespeed-cache/litespeed-cache.php') && get_option('a2_cache_enabled') != 1) {
			$result['status'] = 'critical';
			$result['label'] = ( 'Caching is disabled' );
			$result['description'] = sprintf(
				'<p>%s</p>',
				__('Your site currently has caching disabled. This will lead to much slower page load times.')
			);
			$result['actions'] .= sprintf(
				'<p><a href="%s">%s</a></p>',
				esc_url( admin_url( 'admin.php?page=A2_Optimized_Plugin_admin' ) ),
				__( 'Enable Caching' )
			);
		}

		return $result;
	}
	
	/**
	 * Check if PHP OpCache is enabled
	 *
	 * @return array Array with added A2 Optimized items.
	 */
	public function a2opt_phpopcache_test() {
		$result = array(
			'label' => __( 'PHP OpCaching is enabled' ),
			'status' => 'good', // Default "passing" section
			'badge' => array(
				'label' => __( 'Performance' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Your site is cached and fast!' )
			),
			'actions' => '',
			'test' => 'opcaching_enabled',
		);

		if(function_exists('opcache_get_status')){
			$opcache_status = opcache_get_status();
		} else {
			$opcache_status = false;
		};

		if (!is_array($opcache_status) || !$opcache_status['opcache_enabled']) {
			$result['status'] = 'recommended';
			$result['label'] = ( 'PHP OpCache is not available' );
			$result['description'] = sprintf(
				'<p>%s</p>',
				__('Your PHP currently does not support OpCaching. This could lead to much slower site performance.')
			);
		}

		return $result;
	}
	
	/**
	 * Check if other caching plugins are enabled
	 *
	 * @return array Array with added A2 Optimized items.
	 */
	public function a2opt_other_cache_test() {
		$result = array(
			'label' => __( 'No conflicting cache plugins detected' ),
			'status' => 'good', // Default "passing" section
			'badge' => array(
				'label' => __( 'Performance' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Your site is cached and fast!' )
			),
			'actions' => '',
			'test' => 'caching_enabled',
		);

		$active_plugins = get_option('active_plugins');
		
		$incompatible_plugins = array(
			'wp-super-cache',
			'wp-fastest-cache',
			'wp-file-cache',
			'w3-total-cache',
			'sg-cachepress',
			'cache-enabler',
			'comet-cache',
			'wp-rocket',
			'surge'
		);
  
		$found_plugin = false;
		
		foreach ($active_plugins as $active_plugin) {
			$plugin_folder = explode('/', $active_plugin);
			if (in_array($plugin_folder[0], $incompatible_plugins)) {
				$found_plugin = true;
			}
		}

		if ($found_plugin) {
			$result['status'] = 'critical';
			$result['label'] = ( 'Conflicting cache plugin detected' );
			$result['description'] = sprintf(
				'<p>%s</p>',
				__('You have multiple caching plugins enabled. Please disable any that are not A2 Optimized or LiteSpeed Cache')
			);
			$result['actions'] .= sprintf(
				'<p><a href="%s">%s</a></p>',
				esc_url( admin_url( 'plugins.php' ) ),
				__( 'Disable plugins' )
			);
		}

		return $result;
	}
	
	/**
	 * Check PHP Memory limits
	 *
	 * @return array Array with added A2 Optimized items.
	 */
	public function a2opt_php_memory_test() {
		$result = array(
			'label' => __( 'PHP has good memory limits' ),
			'status' => 'good', // Default "passing" section
			'badge' => array(
				'label' => __( 'Performance' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'PHP Memory is good' )
			),
			'actions' => '',
			'test' => 'php_memory',
		);

		$memory_limit = $this->return_bytes(ini_get('memory_limit'));
		if ($memory_limit < 134217727) { // 128mb
			$result['status'] = 'recommended';
			$result['label'] = ( 'PHP Memory should be increased' );
			$result['description'] = sprintf(
				'<p>%s</p>',
				__('You have less than 128mb of memory avilable to PHP. We recommend raising this to at least 128mb for best performance.')
			);
		}

		return $result;
	}
	
	/**
	 * Check number of unused plugins
	 *
	 * @return array Array with added A2 Optimized items.
	 */
	public function a2opt_plugin_count_test() {
		$result = array(
			'label' => __( 'Large number of unused plugins' ),
			'status' => 'good', // Default "passing" section
			'badge' => array(
				'label' => __( 'Performance' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Unused plugins could present a security issue.' )
			),
			'actions' => '',
			'test' => 'plugin_count',
		);

		$plugins = get_plugins();
		$plugin_count = 0;

		foreach ($plugins as $slug => $plugin) {
			if (is_plugin_inactive($slug)) {
				$plugin_count++;
			}
		}

		if ($plugin_count > 4) {
			$result['status'] = 'recommended';
			$result['label'] = 'Remove unused plugins';
		}

		return $result;
	}
	
	/**
	 * Check number of unused themes
	 *
	 * @return array Array with added A2 Optimized items.
	 */
	public function a2opt_theme_count_test() {
		$result = array(
			'label' => __( 'Large number of unused themes' ),
			'status' => 'good', // Default "passing" section
			'badge' => array(
				'label' => __( 'Performance' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Unused themes could present a security issue.' )
			),
			'actions' => '',
			'test' => 'theme_count',
		);

		$themes = wp_get_themes();
		$theme_count = 0;

		foreach ($themes as $theme_name => $theme) {
			if (substr($theme_name, 0, 6) != 'twenty') {
				// We don't want default themes to count towards our warning total
				$theme_count++;
			}
		}

		if ($theme_count > 1) {
			$result['status'] = 'recommended';
			$result['label'] = 'Remove unused themes';
		}

		return $result;
	}
	
	/**
	 * Check if Cart Fragment AJAX calls are dequeued
	 *
	 * @return array Array with added A2 Optimized items.
	 */
	public function a2opt_cart_fragments_test() {
		$result = array(
			'label' => __( 'Dequeue WooCommerce Cart Fragments AJAX calls' ),
			'status' => 'good', // Default "passing" section
			'badge' => array(
				'label' => __( 'Performance' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Disable WooCommerce Cart Fragments on your homepage.' )
			),
			'actions' => '',
			'test' => 'cart_fragments',
		);

		if (!get_option('a2_wc_cart_fragments')) {
			$result['status'] = 'recommended';
		}

		return $result;
	}
	
	/**
	 * Check if GZIP compression is enabled
	 *
	 * @return array Array with added A2 Optimized items.
	 */
	public function a2opt_gzip_test() {
		$result = array(
			'label' => __( 'You should enable GZIP compression' ),
			'status' => 'recommended', // Default "failing" section
			'badge' => array(
				'label' => __( 'Performance' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Compresses all text files to make them smaller.' )
			),
			'actions' => '',
			'test' => 'gzip_compression',
		);

		if(is_plugin_active('litespeed-cache/litespeed-cache.php')){
			$result['status'] = 'good';
		}
		else if(get_option('a2_cache_enabled') == 1 && A2_Optimized_Cache_Engine::$settings['compress_cache']) {
			$result['status'] = 'good';
		}

		return $result;
	}
	
	/**
	 * Check if XML-RPC requests are blocked
	 *
	 * @return array Array with added A2 Optimized items.
	 */
	public function a2opt_xml_rpc_test() {
		$result = array(
			'label' => __( 'Block Unauthorized XML-RPC Requests' ),
			'status' => 'good', // Default "passing" section
			'badge' => array(
				'label' => __( 'Performance' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Completely Disable XML-RPC services' )
			),
			'actions' => '',
			'test' => 'block_xmlrpc',
		);

		if (!get_option('a2_block_xmlrpc')) {
			$result['status'] = 'recommended';
		}

		return $result;
	}

	/**
	 * Check if file editing is disabled
	 *
	 * @return array Array with added A2 Optimized items.
	 */
	public function a2opt_file_edit_test() {
		$result = array(
			'label' => __( 'Lock Editing of Plugins and Themes from the WP Admin' ),
			'status' => 'good', // Default "passing" section
			'badge' => array(
				'label' => __( 'Performance' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Prevents exploits that use the built in editing capabilities of the WP Admin' )
			),
			'actions' => '',
			'test' => 'file_edit',
		);

		if (!get_option('a2_optimized_lockdown')) {
			$result['status'] = 'recommended';
		}

		return $result;
	}
	
	/**
	 * Add A2 Optimized section to Info tab.
	 *
	 * @param array $debug_info Array of all information.
	 *
	 * @return array Array with added A2 Optimized info section.
	 */
	public function add_debug_section($debug_info) {
		$a2_optimized = array(
			'label' => 'A2Optimized',
			'fields' => array(
				'version' => array(
					'label' => 'Version',
					'value' => A2OPT_FULL_VERSION,
				),
			),
		);
	   
		/* MySQL Version */
		global $wpdb;
		$mysqlVersion = $wpdb->db_version();

		$a2_optimized['fields']['mysql_version'] = array(
			'label' => 'MySQL Version',
			'value' => $mysqlVersion
		);

		/* PHP Version */
		$a2_optimized['fields']['php_version'] = array(
			'label' => 'PHP Version',
			'value' => phpversion()
		);
		
		/* CPU Info */
		if(function_exists('exec')){
			$cpu_info = exec('cat /proc/cpuinfo | grep "model name\\|processor"');
			$cpu_info = str_replace('model name', '', $cpu_info);
			$cpu_info = str_replace('processor', '', $cpu_info);
			$cpu_info = str_replace(':', '', $cpu_info);
			$a2_optimized['fields']['cpu_info'] = array(
				'label' => 'CPU Info',
				'value' => $cpu_info
			);
		};
	
		/* Webserver info */
		$a2_optimized['fields']['http_server'] = array(
			'label' => 'Web Server',
			'value' => php_sapi_name()
		);
		
		/* PHP Memory Limit */
		$a2_optimized['fields']['php_memory'] = array(
			'label' => 'PHP Memory Limit',
			'value' => ini_get('memory_limit')
		);
		
		/* Frontend Benchmarks */
		$frontend_benchmarks = get_option('a2opt-benchmarks-frontend');		
		if(empty($frontend_benchmarks) || !is_array($frontend_benchmarks)){
			$a2_optimized['fields']['benchmark_frontend_overall'] = array(
				'label' => 'Frontend Benchmark',
				'value' => 'Test not run yet'
			);
		} else {
			$frontend_benchmarks_last = array_pop($frontend_benchmarks);

			$a2_optimized['fields']['benchmark_frontend_overall'] = array(
				'label' => 'Frontend Benchmark Overall Score',
				'value' => $frontend_benchmarks_last['scores']['overall_score']
			);
			$a2_optimized['fields']['benchmark_frontend_fcp'] = array(
				'label' => 'Frontend Benchmark FCP',
				'value' => $frontend_benchmarks_last['scores']['fcp']
			);
			$a2_optimized['fields']['benchmark_frontend_ttfb'] = array(
				'label' => 'Frontend Benchmark TTFB',
				'value' => $frontend_benchmarks_last['scores']['ttfb']
			);
			$a2_optimized['fields']['benchmark_frontend_lcp'] = array(
				'label' => 'Frontend Benchmark LCP',
				'value' => $frontend_benchmarks_last['scores']['lcp']
			);
			$a2_optimized['fields']['benchmark_frontend_fid'] = array(
				'label' => 'Frontend Benchmark FID',
				'value' => $frontend_benchmarks_last['scores']['fid']
			);
			$a2_optimized['fields']['benchmark_frontend_cls'] = array(
				'label' => 'Frontend Benchmark CLS',
				'value' => $frontend_benchmarks_last['scores']['cls']
			);
		}
		
		/* Backend Benchmarks */
		$backend_benchmarks = get_option('a2opt-benchmarks-hosting');
		if(empty($backend_benchmarks) || !is_array($backend_benchmarks)){
			$a2_optimized['fields']['benchmark_hosting_overall'] = array(
				'label' => 'Hosting Benchmark',
				'value' => 'Test not run yet'
			);
		} else {
			$backend_benchmarks_last = array_pop($backend_benchmarks);
			$a2_optimized['fields']['benchmark_hosting_overall'] = array(
				'label' => 'Hosting Benchmark Overall Score',
				'value' => round($backend_benchmarks_last['wordpress_db']['queries_per_second'])
			);
			$a2_optimized['fields']['benchmark_hosting_php'] = array(
				'label' => 'Hosting Benchmark PHP Score',
				'value' => $backend_benchmarks_last['php']['total']
			);
			$a2_optimized['fields']['benchmark_hosting_mysql'] = array(
				'label' => 'Hosting Benchmark MySQL Score',
				'value' => $backend_benchmarks_last['mysql']['benchmark']['mysql_total']
			);
			$a2_optimized['fields']['benchmark_hosting_filesystem'] = array(
				'label' => 'Hosting Benchmark Filesystem Score',
				'value' => $backend_benchmarks_last['filesystem']
			);
		}

		$debug_info['a2-optimized-wp'] = $a2_optimized;

		return $debug_info;
	}

	/**
	 * Takes raw string with size shorthand (K/M/G) and return int of bytes
	 *
	 * @param string $val Shorthand string
	 *
	 * @return int Actual value in bytes
	 */
	private function return_bytes($val) {
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		$val = substr($val, 0, -1);
		switch($last) {
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
		return $val;
	}
}
