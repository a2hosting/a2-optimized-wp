<?php
/**
 * Performance Benchmarks
 */
class A2_Optimized_Benchmark {
	public function __construct() {
		if ( ! $this->allow_load() ) {
			return;
		}

		$this->tmp_folder_name = dirname(__FILE__) . '/tmp';

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
		// Do the needful
	}
	
	/**
	 * Run all hosting benchmark tests and store results
	 * 
	 * @return array $results[] An array of benchmark results
	 */

	public function run_hosting_test_suite($result_desc = null) {
		$existing_results = [];
		
		try{
			$results = [];
			$results['version'] = A2OPT_FULL_VERSION;
			$results['sysinfo']['time'] = date("Y-m-d H:i:s");
			$results['sysinfo']['php_version'] = PHP_VERSION;
			$results['sysinfo']['platform'] = PHP_OS;
			if ( !defined( 'WP_CLI' ) || !WP_CLI ) {
				$results['sysinfo']['server_name'] = $_SERVER['SERVER_NAME'];
				$results['sysinfo']['server_addr'] = $_SERVER['SERVER_ADDR'];
			} else {
				$results['sysinfo']['server_name'] = home_url();
				$response = wp_remote_get( 'https://api.ipify.org' );
				if ( is_array( $response ) && ! is_wp_error( $response ) ) {
					$results['sysinfo']['server_addr'] = $response['body'];
				} else {
					$results['sysinfo']['server_addr'] = 'NA';
				}
			}

			$results['php'] = $this->run_php_benchmarks();
			$results['mysql'] = $this->run_mysql_benchmarks();
			$results['wordpress_db'] = $this->run_wordpress_benchmarks();
			$results['filesystem'] = $this->run_filesystem_benchmarks();

			$results['description'] = $result_desc;

			$existing_results[date('Y-m-d H:i:s')] = $results;

			update_option('a2opt-benchmarks-hosting', $existing_results);
			return [
				'status' => 'success',
				'message' => 'New Hosting Benchmarks results recorded.',
				'data' => $results
			];
		} catch (Exception $ex){
			return [
				'status' => 'error',
				'message' => 'Unable to run Hosting Benchmark tests.',
			];
		}
	}

	/**
	 * Run all PHP benchmark tests
	 * 
	 * @return array $results[] An array of benchmark results
	 */

	public function run_php_benchmarks($multiplier = 1.0) {
		$multiplier = $this->constrain($multiplier);
		$results = [];

		$time_start = microtime(true);

		$results['benchmark']['math'] = $this->test_php_math($multiplier);
		$results['benchmark']['string'] = $this->test_php_string($multiplier);
		$results['benchmark']['loop'] = $this->test_php_loops($multiplier);
		$results['benchmark']['ifelse'] = $this->test_php_ifelse($multiplier);

		$results['total'] = $this->timer_diff($time_start);

		return $results;
	}

	/**
	 * Run Math PHP benchmark tests
	 *
	 * @param float $multiplier Multiplier for number of times to run test 
	 * @return string Time taken for test 
	 */
	private function test_php_math($multiplier = 1.0){
		$count = 150000 * $multiplier;
		$time_start = microtime(true);

		for ($i = 0; $i < $count; $i++) {
			sin($i);
			asin($i);
			cos($i);
			acos($i);
			tan($i);
			atan($i);
			abs($i);
			floor($i);
			exp($i);
			is_finite($i);
			is_nan($i);
			sqrt($i);
			log10($i);
		}

		return $this->timer_diff($time_start);
	}

	/**
	 * Run string manipulation PHP benchmark tests
	 *
	 * @param float $multiplier Multiplier for number of times to run test 
	 * @return string Time taken for test 
	 */
	private function test_php_string($multiplier = 1.0){
		$count = 150000 * $multiplier;
		$time_start = microtime(true);
		$string = 'A2 Hosting - Up To 20X Faster Web Hosting To Help You Succeed';
		for ($i = 0; $i < $count; $i++) {
			addslashes($string);
			chunk_split($string);
			metaphone($string);
			strip_tags($string);
			md5($string);
			sha1($string);
			strtoupper($string);
			strtolower($string);
			strrev($string);
			strlen($string);
			soundex($string);
			ord($string);
		}
		return $this->timer_diff($time_start);
	}

	/**
	 * Run Loop PHP benchmark tests
	 *
	 * @param float $multiplier Multiplier for number of times to run test 
	 * @return string Time taken for test 
	 */
	private function test_php_loops($multiplier = 1.0){
		$count = 50000000 * $multiplier;
		$time_start = microtime(true);
		for ($i = 0; $i < $count; ++$i)
			;
		$i = 0;
		while ($i < $count) {
			++$i;
		}

		return $this->timer_diff($time_start);
	}

	/**
	 * Run If/Else PHP benchmark tests
	 *
	 * @param float $multiplier Multiplier for number of times to run test 
	 * @return string Time taken for test 
	 */
	private function test_php_ifelse($multiplier = 1.0){
		$count = 50000000 * $multiplier;
		$time_start = microtime(true);
		for ($i = 0; $i < $count; $i++) {
			if ($i == -1) {

			} elseif ($i == -2) {

			} else if ($i == -3) {

			}
		}
		return $this->timer_diff($time_start);
	}

	/**
	 * Delete benchmarking records
	 * 
	 * @param string $benchmarks_type frontend or backend
	 * @param int $items Number of items to keep; defaults to 25, constrained to [0-25]
	 * @return void
	 */
	public function prune_benchmarks($benchmarks_type, $items = 25) {
		if ($benchmarks_type != 'frontend' && $benchmarks_type != 'backend') return;
		$items = $this->constrain($items, 0, 25);

		$benchmarks = [];
		if ($benchmarks_type == 'frontend') $benchmarks = get_option('a2opt-benchmarks-frontend');
		elseif ($benchmarks_type == 'backend') $benchmarks = get_option('a2opt-benchmarks-hosting');
		if (empty($benchmarks)) return;

		$items_to_delete = count($benchmarks) - $items;
		if ($items_to_delete < 1) return;
		$items_deleted = 0;

		foreach ($benchmarks as $date => $data) {
			unset($benchmarks[$date]);
			++$items_deleted;
			if ($items_deleted >= $items_to_delete) break;
		}

		if ($benchmarks_type == 'frontend') update_option('a2opt-benchmarks-frontend', $benchmarks);
		elseif ($benchmarks_type == 'backend') update_option('a2opt-benchmarks-hosting', $benchmarks);
	}

	/**
	 * Private helper function to constrain a float to a range of values
	 * 
	 * @param float $value The value to constrain
	 * @param float $min   The minimum value for the value (inclusive)
	 * @param float $max   The maximum value for the value (inclusive)
	 * @return float  The value, modified if necessary to fit within the provided constraints
	 */
	private function constrain($value, $min = 0.01, $max = 10.0) {
		if ($value > $max) {
			$value = $max;
		}
		if ($value < $min) {
			$value = $min;
		}
		return $value;
	}

	/**
	 * Run MySQL benchmark tests
	 *
	 * @param int $count Optional number of times to run test 
	 * @return string Time taken for test 
	 */
	public function run_mysql_benchmarks($count = 1){
		$results = [];

		// WordPress wp-config constants
		$wpdb_cfg['db.host'] = DB_HOST;
		$wpdb_cfg['db.user'] = DB_USER;
		$wpdb_cfg['db.pw'] = DB_PASSWORD;
		$wpdb_cfg['db.name'] = DB_NAME; 

		$time_start = microtime(true);

		//detect socket connection
		if(stripos($wpdb_cfg['db.host'], '.sock')!==false){
			//parse socket location
			//set a default guess
			$socket = "/var/lib/mysql.sock";
			$serverhost = explode(':', $wpdb_cfg['db.host']);
			if(count($serverhost) == 2 && $serverhost[0] == 'localhost'){
				$socket = $serverhost[1];
			}
			$link = mysqli_connect('localhost', $wpdb_cfg['db.user'], $wpdb_cfg['db.pw'], $wpdb_cfg['db.name'], null, $socket);
		} else {
			//parse out port number if exists
			$port = 3306;//default
			if(stripos($wpdb_cfg['db.host'],':')){
				$port = substr($wpdb_cfg['db.host'], stripos($wpdb_cfg['db.host'],':')+1);
				$wpdb_cfg['db.host'] = substr($wpdb_cfg['db.host'], 0, stripos($wpdb_cfg['db.host'],':'));
			}
			$link = mysqli_connect($wpdb_cfg['db.host'], $wpdb_cfg['db.user'], $wpdb_cfg['db.pw'], $wpdb_cfg['db.name'], $port);
		}
		$results['benchmark']['mysql_connect'] = $this->timer_diff($time_start);

		$result = mysqli_query($link, 'SELECT VERSION() as version;');
		$arr_row = mysqli_fetch_assoc($result);
		$results['sysinfo']['mysql_version'] = $arr_row['version'];
		$results['benchmark']['mysql_query_version'] = $this->timer_diff($time_start);

		for ($i = 0; $i < $count; $i++) {
			$query = "SELECT BENCHMARK(1000000, AES_ENCRYPT(CONCAT('a2hosting.com',RAND()), UNHEX(SHA2('Up To 20X Faster Web Hosting To Help You Succeed!',512))))";
			$result = mysqli_query($link, $query);
		}
		$results['benchmark']['mysql_query_benchmark'] = $this->timer_diff($time_start);

		mysqli_close($link);

		$results['benchmark']['mysql_total'] = $this->timer_diff($time_start);

		return $results;
	}

	/**
	 * Run WordPress DB benchmark tests
	 *
	 * @param int $count Optional number of times to run test 
	 * @return array Time taken for test and number of queries per second 
	 */
	public function run_wordpress_benchmarks($count = 500){
		global $wpdb;

		// dummy text to insert into database
		$dummytextseed = "Sed tincidunt malesuada viverra. Aliquam dolor diam, interdum quis mauris eget, accumsan imperdiet nisi. Proin mattis massa sapien, et tempor enim lacinia suscipit. Donec at massa ullamcorper, pellentesque lacus sit amet, facilisis metus. Proin lobortis elementum lorem eu volutpat. Aenean sed volutpat augue. Morbi vel dolor commodo, tempor ex ac, hendrerit ante. Vestibulum pellentesque fringilla ligula ac aliquet. Cras tempus enim nec imperdiet convallis. Aliquam elit ex, ornare vestibulum placerat quis, tincidunt ultrices dolor. Morbi gravida sapien congue leo sagittis, sed semper turpis blandit. Aenean malesuada eros vitae orci tristique, vitae imperdiet libero mollis. Nunc lacinia congue tempor. Etiam vitae enim ut eros fermentum elementum. Praesent aliquam iaculis velit, quis dictum nibh.";
		$dummytext = "";
		for($x=0; $x<100; $x++){
			$dummytext .= str_shuffle($dummytextseed);
		}

		//start timing wordpress mysql functions
		$time_start = microtime(true);
		$table = $wpdb->prefix . 'options';
		$optionname = 'a2opt_benchmark_';
		for($x=0; $x<$count;$x++){
			//insert
			$data = array('option_name' => $optionname . $x, 'option_value' => $dummytext);
			$wpdb->insert($table, $data);
			//select
			$select = "SELECT option_value FROM $table WHERE option_name='$optionname" . $x . "'";
			$wpdb->get_var($select);
			//update
			$data = array('option_value' => $dummytextseed);
			$where =  array('option_name' => $optionname . $x);
			$wpdb->update($table, $data, $where);
			//delete
			$where = array('option_name'=>$optionname.$x);
			$wpdb->delete($table,$where);    
		}

		$time = $this->timer_diff($time_start);
		$queries = ($count * 4) / $time;
		return [
			'time' => $time,
			'queries_per_second' => $queries,
		];     
	}

	/**
	 * Run filesystem benchmark tests
	 *
	 * @param int $count Optional number of times to run test 
	 * @return array Time taken for test and number of queries per second 
	 */
	public function run_filesystem_benchmarks($count = 1){
		// Make sure tmp folder is empty and we can write to it
		if(!$this->clean_tmp_folder()){
			return false;
		};
		
		$time_start = microtime(true);
		
		$write_content = "";
		
		// Get 1mb of data
		for($m=0; $m < 1024; $m++){
			$write_content .= $this->get_1kb_text();
		}

		$fn = $this->tmp_folder_name . "/tmp.filewrite";

		for($i=0; $i < $count; $i++) {

			if(file_exists($fn)) {
				unlink($fn);
				clearstatcache();
			}

			$fp = fopen($fn, "w");

			// Write 50mb
			for($k=0; $k < 50; $k++){
				if($this->timer_diff($time_start) > 10){
					// If this is taking more than 10 seconds, we're being throttled, just bail
					fclose($fp);
					clearstatcache();
					$this->clean_tmp_folder();
					return 10;
				}
				
				fwrite($fp, $write_content);
			}
			fclose($fp);
			clearstatcache();
		}

		unset($write_content);

		$this->clean_tmp_folder();

		return $this->timer_diff($time_start);
	}

	/**
	 * Filter out the data being returned by lighthouse down to only the data that we actually care about.
	 * 
	 * @param array	array of data that's been returned from lighthouse api call
	 * @return array filtered array of only the data that we want to store
	 */
	private function filter_lighthouse_data($lighthouse_data){

		$scores = [];

		foreach($lighthouse_data['lighthouseResult']['categories'] as $group) {
			foreach($group['auditRefs'] as $ref){
				if ('server-response-time' === $ref['id']) {
					$scores['ttfb'] = round( $lighthouse_data['lighthouseResult']['audits'][ $ref['id'] ]['numericValue'] );
				}

				if ( 'first-contentful-paint' === $ref['id'] ) {
					$scores['fcp'] = $lighthouse_data['lighthouseResult']['audits'][ $ref['id'] ]['numericValue'];
				}
				
				if ( 'cumulative-layout-shift' === $ref['id'] ) {
					$scores['cls'] = $lighthouse_data['lighthouseResult']['audits'][ $ref['id'] ]['numericValue'];
				}
			
				if ( 'largest-contentful-paint' === $ref['id'] ) {
					$scores['lcp'] = $lighthouse_data['lighthouseResult']['audits'][ $ref['id'] ]['numericValue'];
				}
				
				if ( 'max-potential-fid' === $ref['id'] ) {
					$scores['fid'] = $lighthouse_data['lighthouseResult']['audits'][ $ref['id'] ]['numericValue'];
				}
			}
		}

		foreach($this->get_seo_audits() as $audit){
			if(isset($lighthouse_data['lighthouseResult']['audits'][$audit])){
				$scores['audit_result'][$audit] = $lighthouse_data['lighthouseResult']['audits'][$audit];
			}
		}

		$scores['overall_score'] = round( $lighthouse_data['lighthouseResult']['categories']['performance']['score'] * 100 );

		return $scores;
	}


	/**
	 * Get Lighthouse results
	 *
	 * @param string $strategy Desktop or Mobile 
	 * @return array $results Array containing the results of the test 
	 */
	public function get_lighthouse_results($strategy = "desktop", $retry_count = 5, $result_desc = null){
		$output = [];
	
		$url = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=' . get_site_url() . '&strategy=' . $strategy;
		$pagespeed_options = get_option('a2opt-pagespeed');
		if($pagespeed_options && isset($pagespeed_options['api-key'])){
			$url .= '&key=' . $pagespeed_options['api-key'];
		}
		$response = wp_remote_get($url, ['timeout' => 15]);
		if(is_array($response) && !is_wp_error($response)){
			$lighthouse_data = json_decode($response['body'], true);
			if(!isset($lighthouse_data['error']) || empty($lighthouse_data['error'])){
				// success
				$option_key = 'a2opt-benchmarks-frontend';
				$existing_results = get_option($option_key);
				if(!$existing_results){
					$existing_results = [];
				}
				
				$existing_results[date('Y-m-d H:i:s')] = [
					'strategy' => $strategy,
					'description' => $result_desc,
					'scores' => $this->filter_lighthouse_data($lighthouse_data)
				];
				update_option($option_key, $existing_results);

				$output = [
					'status' => 'success',
					'message' => ''
				];
			} else {
				$error_code = $lighthouse_data['error']['code'];
				if (in_array($error_code, [500,400]) || $retry_count <= 0){
					$error_msg = '';
					if(isset($lighthouse_data['error']['message'])){
						$error_msg = $lighthouse_data['error']['message'];
					}
					$output = [
						'status' => 'error',
						'message' => 'There was an error retrieving results from Pagespeed. ' . $error_msg,
					];
				}
				else {
					$output = $this->get_lighthouse_results($strategy, --$retry_count, $result_desc);
				}
			};
		} else {
			$output = [
				'status' => 'error',
				'message' => 'Pagespeed was unable to connect to this WordPress site. Please check your site configuration.',
			];
		}

		return $output;
	}


	private function get_seo_audits(){
		$audits = [
			'first-contentful-paint',
			'resource-summary',
			'largest-contentful-paint',
			'total-byte-weight',
			'uses-rel-preconnect',
			'dom-size',
			'long-tasks',
			'mainthread-work-breakdown',
			'uses-passive-event-listeners',
			'preload-lcp-image',
			'third-party-summary',
			'time-to-first-byte',
			'render-blocking-resources',
			'largest-contentful-paint-element',
			'total-blocking-time',
			'max-potential-fid',
			'interactive',
			'first-contentful-paint',
			'critical-request-chains',
			'unminified-css',
			'unused-css-rules',
			'uses-rel-preload',
			'font-display',
			'render-blocking-resources',
			'unminified-javascript',
			'bootup-time',
			'unused-javascript',
			'no-document-write',
			'user-timings',
			'legacy-javascript',
			'duplicated-javascript',
			'uses-responsive-images',
			'offscreen-images',
			'uses-optimized-images',
			'uses-webp-images',
			'efficient-animated-content',
			'non-composited-animations',
			'third-party-facades',
			'server-response-time',
			'uses-text-compression',
			'redirects',
			'uses-long-cache-ttl'
		];
		
		return $audits;
	}


	/**
	 * Time elapsed since the passed in time
	 *
	 * @param string $time_start timestamp 
	 * @return string Time elapsed in seconds 
	 */
	private function timer_diff($time_start){
		return number_format(microtime(true) - $time_start, 3);
	}

	/**
	 * 1kb of text to build out file operations
	 *
	 * @return string The blob of text 
	 */
	private function get_1kb_text() {
		return("AAA2A2A222222AAAA2AAA2AA22A222AA22AAA22A2A22AAA222A22A2A2A2AA22222AAAAAAA22A222AAA22AAAAA222AA2AAA22A2A2AA22AA2A2222A22AAAA2AAA2A222222AA22AAAA2A2A2AA2222A2A2AA2A2AAAA2AA222A222A22AAA22AA2AA22AA22AAA2AA2A22A222222A2AA2A222A22A22A2A2AAAA222A2A2A2A2AAAA2A22AA22AAA2A2AAA2A222A2AAAA2AA22A2AA222A222AA22222222AAAAA2A22A2AA22A2AAAAA2AAAAAA2AAAA22AAAA22A2AAAAAA2AA2A22A22AA2AA22A2AA2AAA2A2A22A222AA2AAAA22A2A2AAAA2AA2A2A22A2A2A2AA222AA22AAA2AAAAA2AA2A2A2A22AAA222A22AA22AA2A222A222AA2AAAA2222A2AA22AA2A22AAAA2AA222A2AAA22AA22A2AA2AA22A222AA2AAA22A2A222A2A2222A2A2222AAAAA2A222AA22A2A2A2222AAAA2AA22AAAA2AA2AAAAAAA22AAAA2AA2AA2A2A2AAAAAA22AA222AAAA2AAAA22AA222222A2AA222A22A2AA2A22222A2A2A2AA222222A2A222AAA2AA2A2A222A22222AAAAA2A2A22AAAAAA2A2A222A2AA2AAAA222AAAA2A22A222A2A2A2A2AA222AA2A2AA2A222AA2A2A2A22222A22A2A2A2AA2A2A2222A2222A2AAA2A2222AA222A22222222AA2AA222AA2AAAA2AA2AA22A2AA22AA2222A22A222AA2A2A22222AAA2A2A2A2AAAAAAA2222AA2222AA2222A2AAAA22AA2A2A2A2A22A2AAAAAAA222A22A22AA2AAA2AA222AA22A2AAAAA2A2AAA2AA22AA2A222AAA222A2");
	}

	/**
	 * Create the temp folder for file operations
	 */
	private function make_tmp_folder() {
		$tmp_folder = $this->tmp_folder_name;
		if (!is_dir($tmp_folder)) {
			if (!mkdir($tmp_folder)){
				return false;
			}
		}
		return true;
	}

	/**
	 * Remove contents of temp folder
	 */
	private function clean_tmp_folder() {
		$tmp_folder = $this->tmp_folder_name;

		if (!is_dir($tmp_folder)) {
			if($this->make_tmp_folder()){
				return true;
			} else {
				return false;
			}
		}
		
		if ($dh = opendir($tmp_folder)) {
			while(($file = readdir($dh)) !== false) {
				if ($file != '.' && $file != '..') {
					if (is_file($tmp_folder . '/' . $file)){
						unlink($tmp_folder . '/' . $file);
					}
				}
			}
			closedir($dh);
		}
		return true;
	}

	/**
	 * Baseline Hosting Results
	 */
	public static function get_baseline_results() {
		$results = [];

		$results['a2hosting-turbo'] = [
			'name' => 'Turbo Max',
			'explanation' => 'Fastest Shared',
			'php' => [
				'benchmark' => [
					'math' => '0.074',
					'string' => '0.201',
					'loop' => '0.280',
					'ifelse' => '0.512',
				],
				'total' => '1.068',
			],
			'mysql' => '1.907',
			'wordpress_db' => [
				'time' => '1.606',
				'queries_per_second' => '1245.33',
			],
			'filesystem' => '2.503',
		];
		
		$results['a2hosting-other'] = [
			'name' => 'Fly',
			'explanation' => 'Premium Managed',
			'php' => [
				'benchmark' => [
					'math' => '0.078',
					'string' => '0.196',
					'loop' => '0.268',
					'ifelse' => '0.393',
				],
				'total' => '0.935',
			],
			'mysql' => '1.863',
			'wordpress_db' => [
				'time' => '1.673',
				'queries_per_second' => '1195.4',
			],
			'filesystem' => '2.337',
		];

		if(file_exists('/opt/a2-optimized/wordpress/class.A2_Optimized_Private_Optimizations_v3.php')){
            require_once('/opt/a2-optimized/wordpress/class.A2_Optimized_Private_Optimizations_v3.php');
            $private_opts = new A2_Optimized_Private_Optimizations();
			$results['a2hosting-other'] = $private_opts->get_baseline_results();
        }

		return $results;
	}

}