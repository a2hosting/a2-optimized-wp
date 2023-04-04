<?php

class A2_Optimized_SiteData {

    const A2_URL = 'https://wp-plugins.a2hosting.com/stats/api';

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
		$reporting_active = get_option('a2_sitedata_allow');
        if($reporting_active == "1"){
            add_action('a2_sitedata_report', [&$this, 'send_sitedata']);
            if (!wp_next_scheduled('a2_sitedata_report')) {
                wp_schedule_event(time(), 'weekly', 'a2_sitedata_report');
            }
            
            $existing_benchmarks = get_option('a2opt-benchmarks-hosting');
            if($existing_benchmarks && is_array($existing_benchmarks)){
                // If we have existing benchmarks, run then again on update
                $last_benchmark_version = get_option('a2_last_benchmark_ver');
                if(!$last_benchmark_version || version_compare(A2OPT_FULL_VERSION, $last_benchmark_version, '>')){
                    update_option('a2_last_benchmark_ver', A2OPT_FULL_VERSION);
                    $benchmarks = new A2_Optimized_Benchmark;
                    $benchmarks->run_hosting_test_suite();
                    $this->send_sitedata();
                }
            }
        }
	}

	/**
	 * Execute optimizations that have been enabled by the user
	 */
	public function send_sitedata() {
        // Prepare the data.
        $data = $this->get_sitedata();

        // Prepare the resposne.
        $response = wp_remote_post(
            self::A2_URL,
            [
                'timeout' => 10,
                'headers' => [
                    'X-auth-token' => $this->get_auth_token(),
                ],
                'sslverify' => false,
                'body' => [
                    'action' => 'site_report',
                    'hash' => md5(home_url()),
                    'data' => $data
                ],
            ]
        );

        // Retry if the request fails.
        if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return wp_schedule_single_event( strtotime( '+' . rand( 12, 24 ) . ' hours' ), 'a2_sitedata_cron', [1] );
        }

        update_option('a2_sitedata_lastsent', time());
        
        return true;
    
    }

    private function get_sitedata($benchmarks = true, $extended_info = true){
        global $wpdb;

        $data = [
            'php_version' => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
            'wp_version' => get_bloginfo( 'version' ),
        ];

        if($benchmarks){
            $benchmark_data = get_option('a2opt-benchmarks-hosting');
            if($benchmark_data && is_array($benchmark_data)){
                $latest = array_pop($benchmark_data);
                $data['benchmark_date'] = $latest['sysinfo']['time'];
                $data['benchmark_php'] = $latest['php']['total'];
                $data['benchmark_mysql'] = $latest['mysql']['benchmark']['mysql_total'];
                $data['benchmark_wordpress_db'] = $latest['wordpress_db']['time'];
            }
        };

        if($extended_info){
			$theme_info = wp_get_theme();
            $data['a2_optimized_verion'] = A2OPT_FULL_VERSION;
            $data['wp_memory_limit'] = WP_MEMORY_LIMIT;
            $data['mysql_version'] = $wpdb->db_version();
            $data['server_version'] = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
            $data['theme_name'] = $theme_info->name;
            $data['theme_version'] = $theme_info->version;
            $data['locale'] = get_locale();
            $data['server'] = strtolower( PHP_OS );
            $data['hosting_provider'] = $this->get_hosting_provider();
            $data['enabled_opts'] = $this->get_enabled_opts();
        }

        return $data;
    }

    private function get_auth_token() {
        $token = get_option( 'a2_opt_auth_token', false );

        if ( ! empty( $token ) ) {
            return $token;
        }

        return $this->refresh_auth_token();
    }

    private function refresh_auth_token(){
        $response = wp_remote_post(
            self::A2_URL,
            [
                'timeout' => 10,
                'body' => [
                    'action' => 'get_auth_token',
                    'hash' => md5(home_url())
                ],
                'sslverify' => false,
            ]
        );

        if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }

        $json_data = wp_remote_retrieve_body( $response );
        $data = json_decode( $json_data, true );

        if ( empty( $data['auth_token'] ) ) {
            return false;
        }

        update_option( 'a2_opt_auth_token', $data['auth_token'] );

        return $data['auth_token'];
    }

    private function get_hosting_provider(){
        if(file_exists('/opt/a2-optimized/wordpress/class.A2_Optimized_Private_Optimizations.php')){
            return 'A2Hosting';
        }
        
        if(class_exists('WPaaS\Plugin')) {
            return 'GoDaddy';
        }

        if(class_exists('\Presslabs\Cache\CacheHandler') && defined('PL_INSTANCE_REF')) {
            return 'Presslabs';
        }

        if(file_exists('/etc/yum.repos.d/baseos.repo') && file_exists('/Z')){
            return 'SiteGround';
        }
        
        
        $provider = 'Unknown';
        // A list of hosting provider headers.
        // See more: https://github.com/rviscomi/ismyhostfastyet/blob/main/ttfb.sql
        $host_headers = [
            'zoneos'                           => 'Zone.eu',
            'seravo'                           => 'Seravo',
            'wordpress.com'                    => 'Automattic',
            'x-ah-environment'                 => 'Acquia',
            'x-pantheon-styx-hostname'         => 'Pantheon',
            'wpe-backend'                      => 'WP Engine',
            'wp engine'                        => 'WP Engine',
            'x-kinsta-cache'                   => 'Kinsta',
            'x-github-request'                 => 'GitHub',
            'alproxy'                          => 'AlwaysData',
            'flywheel'                         => 'Flywheel',
            'c2hhcmVkLmJsdWVob3N0LmNvbQ=='     => 'Bluehost',
        ];

        $response = wp_remote_get( get_home_url() );

        $host_header = wp_remote_retrieve_header( $response, 'X-Powered-By' );

        if ( empty( $host_header ) ) {
            return $provider;
        }

        return array_key_exists( $host_header, $host_headers );
    }

    private function get_enabled_opts(){
        $a2_optimizations = new A2_Optimized_Optimizations;
        
        $enabled_opts = [];

        foreach($a2_optimizations->get_optimizations() as $k => $opt){
            if($opt['configured']){
                $enabled_opts[$k] = true;
            } else {
                $enabled_opts[$k] = false;
            }
        };

        return json_encode($enabled_opts);
    }
}

?>