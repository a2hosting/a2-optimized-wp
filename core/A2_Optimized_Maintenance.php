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
    }

    private function get_old_classes(){
        $outdated_files = [
            "A2_Optimized_CacheDisk.php",
            "A2_Optimized_CacheEngine.php",
            "A2_Optimized_Cache.php",
            "A2_Optimized_CLI.php",
            "A2_Optimized_DB_Optimizations.php",
            "A2_Optimized_Optimizations.php",
            "A2_Optimized_OptionsManager.php"
        ];

        return $outdated_files;
    }

    private function remove_old_classes(){
        $files = $this->get_old_classes();
        foreach($files as $file){
            if(file_exists(A2OPT_DIR . '/' . $file)){
                unlink(A2OPT_DIR . '/' . $file);
            }
        }
    }

}