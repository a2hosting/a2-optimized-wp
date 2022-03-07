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
		add_filter( 'debug_information', [ $this, 'add_debug_section' ] );
        add_filter( 'site_status_tests', [ $this, 'add_site_status_item' ] );
	}


	/**
	 * Add A2 Optimized items to main Status tab.
	 *
	 * @param array $tests Array of existing site health tests.
	 *
	 * @return array Array with added A2 Optimized items.
	 */
    public function add_site_status_item( $tests ) {
        $tests['direct']['a2_optimized'] = array(
            'label' => 'Site Status Test',
            'test'  => [ $this, 'a2opt_caching_test' ],
        );
        return $tests;
    }

 
	/**
	 * Placeholder method to demo how to add items
	 *
	 * @return array Array with added A2 Optimized items.
	 */
    public function a2opt_caching_test() {
        $result = array(
            'label'       => __( 'Caching is enabled' ),
            'status'      => 'good', // Default "passing" section
            'badge'       => array(
                'label' => __( 'Performance' ),
                'color' => 'orange',
            ),
            'description' => sprintf(
                '<p>%s</p>',
                __( 'Caching can help load your site more quickly for visitors.' )
            ),
            'actions'     => '',
            'test'        => 'caching_plugin',
        );
   
        $caching_enabled = false;

        if ( !$caching_enabled ) {
            //$result['status'] = 'recommended'; // "Recommended" section
            $result['status'] = 'critical'; // "Critical" section
            $result['label'] = __( 'Caching is not enabled' );
            $result['description'] = sprintf(
                '<p>%s</p>',
                __( 'Caching is not currently enabled on your site. Caching can help load your site more quickly for visitors.' )
            );
            $result['actions'] .= sprintf(
                '<p><a href="%s">%s</a></p>',
                esc_url( admin_url( 'admin.php?page=cachingplugin&action=enable-caching' ) ),
                __( 'Enable Caching' )
            );
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
	public function add_debug_section( $debug_info ) {

		$a2_optimized = [
			'label'  => 'A2Optimized',
			'fields' => [
				'version' => [
					'label' => 'Version',
					'value' => A2OPT_FULL_VERSION,
				],
			],
		];

		// Placeholder to demo feature working. 
        // TODO: Replace with actual information
		$site_health_working = true;
        if($site_health_working){
            $a2_optimized['fields']['site_health'] = [
                'label' => 'Site Health Test',
                'value' => $site_health_working,
            ];
        }

		$debug_info['a2-optimized-wp'] = $a2_optimized;

		return $debug_info;
	}
}