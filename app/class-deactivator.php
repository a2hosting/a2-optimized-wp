<?php
namespace A2_Optimized\App;
require_once plugin_dir_path( __FILE__ ) . '../core/A2_Optimized_Cache.php';
require_once plugin_dir_path( __FILE__ ) . '../core/A2_Optimized_CacheDisk.php';

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      3.0.0
 * @package    A2_Optimized
 * @subpackage A2_Optimized/App
 */
class Deactivator {
	/**
	 * @since    3.0.0
	 */
	public function deactivate() {
		$htaccess = file_get_contents(ABSPATH . '.htaccess');

		$pattern = "/[\r\n]*# BEGIN WordPress Hardening.*# END WordPress Hardening[\r\n]*/msiU";
		$htaccess = preg_replace($pattern, '', $htaccess);

		//Write the rules to .htaccess
		$fp = fopen(ABSPATH . '.htaccess', 'c');

		if (flock($fp, LOCK_EX)) {
			ftruncate($fp, 0);	  // truncate file
			fwrite($fp, $htaccess);
			fflush($fp);			// flush output before releasing the lock
			flock($fp, LOCK_UN);	// release the lock
		}

		// deactivate the scheduled cron tasks
		wp_clear_scheduled_hook('a2_execute_db_optimizations');
		wp_clear_scheduled_hook('a2_execute_wpconfig_cleanup');
		
		// Remove bcrypt password plugin
		$dest = trailingslashit( WPMU_PLUGIN_DIR ) . 'wp-password-bcrypt.php';
        if(file_exists($dest)){
            unlink($dest);
        }

		// clean disk cache files
		\A2_Optimized_Cache::on_deactivation(is_multisite() && is_network_admin());
	}
}
