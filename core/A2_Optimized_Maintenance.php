<?php
/**
 * Clean up tasks
 */
class A2_Optimized_Maintenance {
	public function __construct() {
		if ( ! $this->allow_load() ) {
			return;
		}

		$this->remove_old_classes();

		$this->hooks();
	}

	/**
	 * Indicate if Benchmarks is allowed to load
	 *
	 * @return bool
	 */
	private function allow_load() {
		global $wp_version;

		return version_compare( $wp_version, '5.2', '>=' );
	}

	/**
	 * Integration hooks
	 */
	protected function hooks() {
		if ($this->maybe_schedule_xmlsitemap_sync() && file_exists('/opt/a2-optimized/wordpress/class.A2_Optimized_Private_Optimizations_v3.php')) {
			add_action('a2_sync_xmlsitemap_location', [&$this, 'sync_xmlsitemap_location']);
			if (!wp_next_scheduled('a2_sync_xmlsitemap_location')) {
				wp_schedule_event(time(), 'daily', 'a2_sync_xmlsitemap_location');
			}
		}
	}

	private function maybe_schedule_xmlsitemap_sync() {
		if (!function_exists('is_plugin_active')) {
			include_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}
		if (is_plugin_active('litespeed-cache/litespeed-cache.php') && is_plugin_active('google-sitemap-generator/sitemap.php')) {
			return true;
		}
	}

	public function sync_xmlsitemap_location() {
		$sitemap_options = get_option('sm_options');
		$sitemap_url = $sitemap_options['sm_b_sitemap_name'];
		$expected_sitemap = get_site_url() . '/' . $sitemap_url . '.xml';
		$litespeed_sitemap = get_option('litespeed.conf.crawler-sitemap');
		if (!$litespeed_sitemap || $litespeed_sitemap != $expected_sitemap) {
			update_option('litespeed.conf.crawler-sitemap', $expected_sitemap);
		}
	}

	private function get_old_classes() {
		$outdated_files = [
			'A2_Optimized_CacheDisk.php',
			'A2_Optimized_CacheEngine.php',
			'A2_Optimized_Cache.php',
			'A2_Optimized_CLI.php',
			'A2_Optimized_DB_Optimizations.php',
			'A2_Optimized_Optimizations.php',
			'A2_Optimized_OptionsManager.php'
		];

		return $outdated_files;
	}

	private function remove_old_classes() {
		$files = $this->get_old_classes();
		foreach ($files as $file) {
			if (file_exists(A2OPT_DIR . '/' . $file)) {
				unlink(A2OPT_DIR . '/' . $file);
			}
		}
	}
}
