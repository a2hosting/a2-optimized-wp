<?php
namespace A2_Optimized\App;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    A2_Optimized
 * @subpackage A2_Optimized/App
 * @author     Your Name <email@example.com>
 */
class Deactivator {
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
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
		} else {
			//no file lock :(
		}

		// deactivate the scheduled weekly database optimizations
		wp_clear_scheduled_hook('a2_execute_db_optimizations');
	}
}
