<?php
namespace A2_Optimized\App\Models\Admin;

use A2_Optimized\App\Models\Settings as Settings_Model;
use A2_Optimized\App\Models\Admin\Base_Model;
use A2_Optimized_Benchmark;
use A2_Optimized_Optimizations;

if ( ! class_exists( __NAMESPACE__ . '\\' . 'Admin_Settings' ) ) {
	/**
	 * Model class that implements Plugin Admin Settings
	 *
	 * @since      3.0.0
	 * @package    A2_Optimized
	 * @subpackage A2_Optimized/models/admin
	 */
	class Admin_Settings extends Base_Model {
		private $benchmark;
		private $optimizations;

		const NOTIFICATIONS_KEY = 'a2_opt_notifications';

		/**
		 * Constructor
		 *
		 * @since    3.0.0
		 */
		protected function __construct() {
			$this->register_hook_callbacks();
			$this->benchmark = new A2_Optimized_Benchmark();
			$this->optimizations = new A2_Optimized_Optimizations();
		}

		/**
		 * Register callbacks for actions and filters
		 *
		 * @since    3.0.0
		 */
		protected function register_hook_callbacks() {
			/**
			 * If you think all model related add_actions & filters should be in
			 * the model class only, then this this the place where you can place
			 * them.
			 *
			 * You can remove this method if you are not going to use it.
			 */

			add_action( 'wp_ajax_run_benchmarks', [$this, 'run_benchmarks'] );
			add_action( 'wp_ajax_apply_optimizations', [$this, 'apply_optimizations'] );
			add_action( 'wp_ajax_update_advanced_options', [$this, 'update_advanced_options']);
			add_action( 'wp_ajax_add_notification', [$this, 'add_notification'] );
		}

		/* Callback for run_benchmarks AJAX request */
		public function run_benchmarks() {
			if ( !wp_verify_nonce($_POST['nonce'], 'a2opt_ajax_nonce') || !current_user_can('manage_options') ){ 
				echo json_encode(['result' => 'fail', 'status' => 'Permission Denied']);
				wp_die(); 
			}
			
			$target_url = $_POST['target_url'];
			$page = $_POST['a2_page'];
			$run_checks = $_POST['run_checks'] !== 'false';

			switch($page){
				case 'server-performance':
				case 'page-speed-score':
					$raw_frontend_data = $this->get_frontend_benchmark($run_checks);
					if ($page == 'server-performance') {
						$strategy = 'pagespeed_' . $_POST['a2_performance_strategy'];
						$frontend_data = $raw_frontend_data[$strategy];
					}
					else {
						$frontend_data = $raw_frontend_data;
					}

					$opt_data = $this->get_opt_performance();

					$data = array_merge($frontend_data, $opt_data['graphs']);
					$data['status_message'] = $raw_frontend_data['status_message'];
					break;
				case 'hosting-matchup':
					$hosting_data = $this->get_hosting_benchmark($run_checks);

					$data = $hosting_data;
					break;
			}

			echo json_encode($data);
			wp_die();
		}

		//// notification functions //
		public function get_notifications() {
			$notifications = get_option(self::NOTIFICATIONS_KEY);
			if ($notifications && count($notifications) == 0){
				$notifications = [];
			}

			return $notifications;

		}

		public function add_notification(){
			if ( !wp_verify_nonce($_POST['nonce'], 'a2opt_ajax_nonce') || !current_user_can('manage_options') ){ 
				echo json_encode(['result' => 'fail', 'status' => 'Permission Denied']);
				wp_die(); 
			}
			$new_notification = '';
			if(isset($_POST) && isset($_POST['a2_notification_text'])){
				$new_notification = esc_html($_POST['a2_notification_text']);
			}
			if (!empty($new_notification)){
				$notifications = $this->get_notifications();
				$max_id = 0;
				if (count($notifications) > 0){
					$max_id = max(array_keys($notifications));
				}
				$new_id = $max_id + 1;

				$notifications[$new_id] = $new_notification;
				update_option(self::NOTIFICATIONS_KEY, $notifications);
				echo json_encode($notifications);
			}
			wp_die();
		}
		////
		/* Callback for apply_optimizations AJAX request */
		public function apply_optimizations() {
			if ( !wp_verify_nonce($_POST['nonce'], 'a2opt_ajax_nonce') || !current_user_can('manage_options') ){ 
				echo json_encode(['result' => 'fail', 'status' => 'Permission Denied']);
				wp_die(); 
			}
			
			$data = [];

			$optimizations = [];
			foreach($_POST as $k => $value){
				if(substr($k, 0, 4) === "opt-"){
					$k = str_replace("opt-", "", $k);
					$optimizations[$k] = $value;
					$this->optimizations->apply_optimization($k, $value);
				}
			}

			$opt_perf = $this->get_opt_performance();
			
			$data['updated_data'] = $opt_perf;

			$data['result'] = 'success';

			echo json_encode($data);
			wp_die();
		}

		public function update_advanced_options() {
			if ( !wp_verify_nonce($_POST['nonce'], 'a2opt_ajax_nonce') || !current_user_can('manage_options') ){ 
				echo json_encode(['result' => 'fail', 'status' => 'Permission Denied']);
				wp_die(); 
			}

			$data = [];

			$settings = [];
			foreach($_POST as $k => $value){
				if(substr($k, 0, 4) === "opt-"){
					$k = str_replace("opt-", "", $k);
					$settings[$k] = $value;
				}
			}

			update_option('a2opt-pagespeed', $settings);

			$data['updated_data']['advanced_settings'] = $this->get_advanced_settings();

			$data['result'] = 'success';

			echo json_encode($data);
			wp_die();

		}

		public function get_advanced_settings(){
			$advanced_settings_current = get_option('a2opt-pagespeed');

			if ($advanced_settings_current == null){
				$advanced_settings_current = [
					'api-key' => '',
					'default-strategy' =>'desktop'
				];
			}

			$advanced_settings['advanced'] = [
				'title' => 'Advanced Settings',
				'explanation' => 'Advanced settings for the A2Opt application',
				'settings_sections' => [
					'a2opt-pagespeed' => [
						'title' => '',
						'description' => '',
						'settings' => [
							'default-strategy' => [
								'description' => 'Default strategy',
								'label' => '',
								'input_type' => 'options',
								'input_options' => [
									'Desktop' => 'desktop',
									'Mobile' => 'mobile'
								],
								'value' => $advanced_settings_current['default-strategy'],
							],
							'api-key' => [
								'description' => 'Google API Key',
								'label' => 'Check <a target="_blank" href="https://developers.google.com/speed/docs/insights/v5/get-started">HERE</a> for more information about using a Google API Key.  Leave blank if you are unsure.',
								'input_type' => 'text',
								'value' => $advanced_settings_current['api-key'],
							]
						]
					]
				]
			];
	
			return $advanced_settings;
		}

		public function get_hosting_benchmark($run = false) {
			if($run){
				$this->benchmark->run_hosting_test_suite();
			}
			$backend_benchmarks = get_option('a2opt-benchmarks-hosting');
			$baseline_benchmarks = $this->benchmark->get_baseline_results();

			$result = [
				'last_check_date' => 'None'
			];
			if ($backend_benchmarks){
				$bm = array_pop($backend_benchmarks);
				$result['last_check_date'] = $this->get_readable_last_check_date($bm['sysinfo']['time']);
				$hostentry = [
					'php' => $bm['php']['total'],
					'mysql' => $bm['mysql']['benchmark']['mysql_total'],
					'wordpress_db' => $bm['wordpress_db']['time'],
					'filesystem' => $bm['filesystem'],
				];
			}
			else {
				$hostentry = [
					'php' => null,
					'mysql' => null,
					'wordpress_db' => null,
					'filesystem' => null,
				];
			}
			$hostentry = array_merge(self::BENCHMARK_DISPLAY_DATA['benchmark-host'], $hostentry);
	
			$result['graph_data']['host'] = $hostentry;
	
			foreach ($baseline_benchmarks as $key => $benchmark){
				$entry = [
					'php' => $benchmark['php']['total'],
					'mysql' => $benchmark['mysql'],
					'wordpress_db' => $benchmark['wordpress_db']['time'],
					'filesystem' => $benchmark['filesystem']
				];
				$entry = array_merge(self::BENCHMARK_DISPLAY_DATA[$key], $entry);
				$entry['display_text'] = $benchmark['name'];
				$entry['explanation'] = $benchmark['explanation'];
				$result['graph_data'][$key] = $entry;
			}
			$result['graphs']['webperformance'] = self::BENCHMARK_DISPLAY_DATA['webperformance'];
			$result['graphs']['serverperformance'] = self::BENCHMARK_DISPLAY_DATA['serverperformance'];
			$result['graphs']['tooltips'] = self::BENCHMARK_DISPLAY_DATA['hostingmatchup_tooltips'];

			return $result;
		}

		public function get_frontend_benchmark($run = false) {
			$status_message = "";
			if ($run) {
				$desktop_result = $this->benchmark->get_lighthouse_results('desktop');
				$mobile_result = $this->benchmark->get_lighthouse_results('mobile');

				if ($desktop_result['status'] != 'success' || $mobile_result['status'] != 'success') {
					if(isset($desktop_result['message'])){
						$status_message = $desktop_result['message'];
					}
					if(isset($mobile_result['message'])){
						$status_message = $mobile_result['message'];
					}
				}
			}

			$frontend_benchmarks = get_option('a2opt-benchmarks-frontend');
			if ($frontend_benchmarks) {
				$last_desktop = null;
				$last_mobile = null;
				$prev_desktop = null;
				$prev_mobile = null;
				$desktop_check_date = null;
				$mobile_check_date = null;

				foreach (array_reverse($frontend_benchmarks) as $check_date => $fbm) {
					if ($fbm['strategy'] == 'desktop') {
						if ($last_desktop == null) {
							$desktop_check_date = $check_date;
							$last_desktop = $fbm;
						} elseif ($prev_desktop == null) {
							$prev_desktop = $fbm;
						}
					} elseif ($fbm['strategy'] == 'mobile') {
						if ($last_mobile == null) {
							$mobile_check_date = $check_date;
							$last_mobile = $fbm;
						} elseif ($prev_mobile == null) {
							$prev_mobile = $fbm;
						}
					}

					if (isset($last_desktop) && isset($prev_desktop) && isset($last_mobile) && isset($prev_mobile) ) {
						break;
					}
				}

				// set these to false if no benchmarks
				$result['pagespeed_desktop'] = $this->get_graph_data($desktop_check_date, $last_desktop, $prev_desktop);
				$result['pagespeed_mobile'] = $this->get_graph_data($mobile_check_date, $last_mobile, $prev_mobile);

			} else {
				$desktop = [
					"strategy" => "desktop",
					"description" => null,
					"scores" => [
						"fcp" => 0,
						"lcp" => 0,
						"cls" => 0,
						"fid" => 0,
						"ttfb" => 0,
						"audit_result" => [],
						"overall_score" => 0
					]
				];

				$mobile = [
					"strategy" => "mobile",
					"description" => null,
					"scores" => [
						"fcp" => 0,
						"lcp" => 0,
						"cls" => 0,
						"fid" => 0,
						"ttfb" => 0,
						"audit_result" => [],
						"overall_score" => 0
					]
				];
				$result['pagespeed_desktop'] = $this->get_graph_data('None', $desktop, null);
				$result['pagespeed_mobile'] = $this->get_graph_data('None', $mobile, null);
			}
			$result['status_message'] = $status_message;
			return $result;
		}

		public function get_opt_performance() {
			$result = [];
			$result['optimizations'] = $this->optimizations->get_optimizations();
			$result['best_practices'] = $this->optimizations->get_best_practices();
			$extra_settings = $result['optimizations']['extra_settings']; // has to be before $result['optimizations'] gets changed
			$settings_tethers = $result['optimizations']['settings_tethers'];


			$displayed_optimizations = [];
			$other_optimizations = [];
			$opt_counts = [];
			$graphs = [];
			$categories = ['performance', 'security', 'bestp'];
			
			/* Setup initial counts */
			foreach($categories as $cat){
				$opt_counts[$cat]['active'] = 0;
				$opt_counts[$cat]['total'] = 0;
			}

			/* Assign optimizations to display area and determine which are configured */
			foreach($result['optimizations'] as $k => $optimization){
				foreach($categories as $cat){
					if(isset($optimization['category']) && $optimization['category'] == $cat){
						if(isset($optimization['optional'])){
							$other_optimizations[$cat][$k] = $optimization;
						} else {
							$displayed_optimizations[$cat][$k] = $optimization;
							if($optimization['configured']){
								$opt_counts[$cat]['active']++;
							}
							$opt_counts[$cat]['total']++;
						}
					}
				}
			}

			foreach($result['best_practices'] as $key => $item){
				$color_class = 'warn';
				if(!$item['status']['is_warning']){
					$color_class = 'success';
				}
				$result['best_practices'][$key]['color_class'] = $color_class;

				if (!isset($item['slug'])){
					$opt_counts['bestp']['total']++;
					if ($color_class == 'success'){
						$opt_counts['bestp']['active']++;
					}
				}
			}

			/* Determine circle colors */
			foreach($categories as $cat){
				$color_class = 'danger';
				if($opt_counts[$cat]['active'] > 1){
					$color_class = 'warn';
				}
				if($opt_counts[$cat]['active'] == $opt_counts[$cat]['total']){
					$color_class = 'success';
				}
				if($opt_counts[$cat]['total'] == 0){
					$opt_counts[$cat]['total'] = 1;	
				}
				$graphs[$cat] = [
					'score' => ($opt_counts[$cat]['active'] / $opt_counts[$cat]['total']),
					'max' => 1, //todo not being used?  otherwise, shouldn't it be $opt_counts[$cat]['total'] ?
					'text' => $opt_counts[$cat]['active'] . "/" . $opt_counts[$cat]['total'],
					'color_class' => $color_class,  
				];
				$graphs[$cat] = array_merge($graphs[$cat], self::BENCHMARK_DISPLAY_DATA['optimizations'][$cat]);
	
			}

			$result['graphs'] = $graphs;
			$result['opt_counts'] = $opt_counts;
			$result['optimizations'] = $displayed_optimizations;
			$result['other_optimizations'] = $other_optimizations;
			$result['extra_settings'] = $extra_settings;
			$result['settings_tethers'] =$settings_tethers;
			return $result;
		}

		const BENCHMARK_DISPLAY_DATA = [
			'overall_score' => [
				'display_text' => 'Overall Score',
				'metric_text' => 'The Performance score is a weighted average of the metric scores.',
				'explanation' => 'The overall score measures how your website scores overall when looking at all metrics. 
				Metrics include: <br />
				- Time to First Byte (TTFB)<br /> 
				- Largest Contentful Paint (LCP)<br /> 
				- First Input Delay (FID) <br />
				- First Contentful Paint (FCP)<br /> 
				- Cumulative Layout Shift (CLS)<br /> 
				<br />
				The higher your overall score, the better your website will perform.'
			],
			'ttfb' => [
				'display_text' => 'Server Speed',
				'metric_text' => 'Time to first Byte (TTFB)',
				'explanation' => 'Time to First Byte (TTFB) refers to the time between the browser requesting a page and when it receives the first byte of information from the server. The less time taken, the faster your website will load. For example, a website with a TTFB of 40 ms (milliseconds) will load faster than a website with a TTFB of 90 MLS.'
			],
			'fcp' => [
				'display_text' => 'User Perception',
				'metric_text' => 'First Contentful Paint (FCP)',
				'explanation' => 'First Contentful Paint (FCP) measures the time taken for the browser to provide the first feedback to the user that the page is actually loading. The shorter the time, the faster your website will load.'
			],
			'lcp' => [
				'display_text' => 'Page Load Speed',
				'metric_text' => 'Largest Contentful Paint (LCP)',
				'explanation' => 'We measure a website’s page load speed with “Largest Contentful Paint” (LCP). LCP represents how quickly the main content of a web page is loaded. Specifically, LCP measures the time from when the user initiates loading the page until the largest image or text block is rendered within the viewable area of the browser window. The shorter the time, the faster your website will load.'
			],
			'fid' => [
				'display_text' => 'Website Browser Speed',
				'metric_text' => 'First Input Delay (FID)',
				'explanation' => 'First Input Delay (FID) measures the time from when a user first interacts with your site (i.e. when they click a link, tap on a button, or use a custom, JavaScript-powered control) to the time when the browser is actually able to respond to that interaction. Your website will load faster if you can shorten your FID time.'
			],
			'cls' => [
				'display_text' => 'Visual Stability',
				'metric_text' => 'Cumulative Layout Shift (CLS)',
				'explanation' => 'Cumulative Layout Shift (CLS) is the unexpected shifting of webpage elements while the page is still loading. When elements suddenly change position while loading, it can cause the user to accidentally click the wrong link on your webpage resulting in a bad user experience. CLS is an important ranking factor for search engines such as Google, and a bad user experience can negatively impact your website\'s SEO.'
			],
			'recommendations' => [
				'display_text' => 'Opportunity',
				'metric_text' => 'Areas that can be improved',
				'explanation' => 'These suggestions can help your page load faster. They don\'t directly affect the Performance score.',
			],
			'benchmark-host' => [
				'display_text' => 'Your Host',
				'metric_text' => '',
				'explanation' => 'Your Host',
				'color_class' => 'thishost'
			],
			'a2hosting-turbo' => [
				'display_text' => 'Turbo Boost',
				'metric_text' => '',
				'explanation' => '',
				'color_class' => 'warn'
			],
			'a2hosting-other' => [
				'display_text' => 'Managed Wordpress',
				'metric_text' => '',
				'explanation' => '',
				'color_class' => 'success'
			],
			'webperformance' => [
				'display_text' => 'Web Performance',
				'metric_text' => "How does your hosting <strong>compare</strong> to A2 Hosting's best plans? With the graphs below <strong>LOWER IS BETTER</strong>.",
				'legend_text' => "Overall Wordpress Execution Time",
				'explanation' => 'The web performance score measures how your current host performs compared to A2 Hosting. This web performance score looks at server speed and other metrics to determine how fast your website will load, based on which hosting company & plan you host your website with. <br /><br />
				The lower the score on the graph the faster your website will load. Not all hosting companies and plans use the same hardware. A2 Hosting uses the best server hardware on the market, focusing on speed & security. A2 Hosting also offers free site migration to help you move your existing websites to them.<br /><br />
				Graphs are representitive of the following, and individual results may vary based on current server load, PHP version, WordPress version, etc.<br />
				<li><strong>Fastest Shared</strong> is our Turbo Max 2022 Shared plan on our current fastest server.</li>
				<li><strong>Premium Managed</strong> is our Fly Managed WordPress plan on our current fastest server.</li>
				<li><strong>Avg. of all Plans</strong> is an calculated average of WordPress sites across all A2 Hosting plans and servers.</li>',
				'color_class' => 'success'
			],
			'serverperformance' => [
				'display_text' => 'Server Performance',
				'metric_text' => "How fast is your hosting <strong>compared</strong> to A2 Hosting's best plans? With the graphs below <strong>LOWER IS BETTER</strong>.",
				'legend_text' => "PHP, Mysql, and File I/O Response Time Comparison",
				'explanation' => 'The lower the scores on the graph, the faster your experience will be in the WordPress Admin dashboard and on pages that use dynamic content that can\'t be easily cached—like search forms and shopping carts. <br /><br />
				Not all hosting companies and plans use the same hardware. If your current host has a lower server performance score than A2 Hosting, then consider moving your websites to A2 Hosting. A2 Hosting uses the best server hardware on the market, focusing on speed & security. A2 Hosting also offers free site migration to help you move your existing websites to them.<br /><br />
				Graphs are representitive of the following, and individual results may vary based on current server load, PHP version, WordPress version, etc.<br />
				<li><strong>Fastest Shared</strong> is our Turbo Max 2022 Shared plan on our current fastest server.</li>
				<li><strong>Premium Managed</strong> is our Fly Managed WordPress plan on our current fastest server.</li>
				<li><strong>Avg. of all Plans</strong> is an calculated average of WordPress sites across all A2 Hosting plans and servers.</li>',
				'color_class' => 'success'
			],
			'optimizations' => [
				'performance' => [
					'display_text' => 'Performance',
					'metric_text' => "Optimizations that will help your performance.",
					'legend_text' => '',
					'explanation' => ''
				],
				'security' => [
					'display_text' => 'Security',
					'metric_text' => "Optimizations that will help your security.",
					'legend_text' => '',
					'explanation' => ''
				],
				'bestp' => [
					'display_text' => 'Best Practices',
					'metric_text' => "Optimizations that bring things in line with current best practices.",
					'legend_text' => '',
					'explanation' => ''
				],
			],
			'hostingmatchup_tooltips' => [
				'wordpress_db' => 'Wordpress Database Response Time',
				'filesystem' => 'Server Disk Response Time',
				'mysql' => 'MYSQL Query Response Time',
				'php' => 'PHP Response Time'
			]

		];

		const BENCHMARK_SCORE_PROFILES = [
			//higher is better [low/med/high] so we will invert the score when checking
			'overall_score' => ['success'=>100,  'warn'=>89, 'danger'=>49, 'max'=>100, 'decimalplaces'=>0],
			'overall_score_inverted' => ['success'=>0, 'warn'=>11, 'danger'=>51, 'decimalplaces'=>0],

			//lower is better [high/med/low]
			'fcp' => ['success'=> 1999, 'warn'=>3999, 'danger'=>6000, 'max'=>6000, 'decimalplaces'=>0],
			'ttfb' => ['success'=> 99, 'warn'=>599, 'danger'=>1000, 'max'=>1000, 'decimalplaces'=>0],
			'cls' => ['success'=> 0.1, 'warn'=>0.25, 'danger'=>1, 'max'=>1, 'decimalplaces'=>2],
			'lcp' => ['success'=> 2499, 'warn'=>3999, 'danger'=>6000, 'max'=>6000, 'decimalplaces'=>0],
			'fid' => ['success'=> 99, 'warn'=>299, 'danger'=>1000, 'max'=>1000, 'decimalplaces'=>0],
		];

		public function get_score_status_and_thresholds($metric, $score) {
			// invert values when needed
			$thresholds = self::BENCHMARK_SCORE_PROFILES[$metric];
			$display_thresholds = $thresholds;
			if ($metric == 'overall_score') {
				$score = $thresholds['success'] - $score;
				$thresholds = self::BENCHMARK_SCORE_PROFILES[$metric . '_inverted'];
			}

			$status = 'success';
			if ($score >= $thresholds['warn']) {
				$status = 'warn';
			}
			if ($score >= $thresholds['danger']) {
				$status = 'danger';
			}

			return [
				'status' => $status,
				'thresholds' => $display_thresholds
			];
		}

		public function get_readable_last_check_date($last_check_date){
			if ($last_check_date == 'None'){
				return $last_check_date;
			}
			else {
				return human_time_diff(strtotime($last_check_date)) . " ago";
			}
		}

		public function get_graph_data($last_check_date, $latest, $previous) {
			$metrics = ['overall_score', 'fcp', 'ttfb', 'cls','lcp', 'fid'];

			$result = [];
			
			foreach ($metrics as $metric) {
				$latest_score = 0;
				$previous_score = 0;
				
				if(isset($latest['scores'][$metric])){
					$latest_score = $latest['scores'][$metric];
				}

				if(isset($previous['scores'][$metric])){
					$previous_score = $previous['scores'][$metric];
				}

				/*
				// round test
				$latest_score += 0.14234636236256256;
				$previous_score += 0.0990809856203465203;
				// color test
				$latest_score = rand(0, $status_info['thresholds']['max']);
				$previous_score = rand(0, $status_info['thresholds']['max']);
				*/

				$status_info = $this->get_score_status_and_thresholds($metric, $latest_score);
				if ($last_check_date == 'None'){
					$status_info['status'] = 'empty';
				}


				$decimalplaces = self::BENCHMARK_SCORE_PROFILES[$metric]['decimalplaces'];
				$latest_score = round($latest_score, $decimalplaces);
				$data = [
					'last_check_date' =>  $this->get_readable_last_check_date($last_check_date),
					'score' => $latest_score,
					'max' => $status_info['thresholds']['max'],
					'text' => "{$latest_score}",
					'color_class' => $status_info['status'],
					'thresholds' => $status_info['thresholds'],
					'explanation' => 'Here is a detailed explanation of what the ' . $metric . ' means, as rendered lovingly by George'
				];

				$direction = 'none';
				$percent_change = 0;
				$diff = $latest_score - $previous_score;
				if ($diff != 0) {
					if ($diff < 0) {
						$direction = 'down';
					} elseif ($diff > 0) {
						$direction='up';
					}
					$diff = abs($diff);
					$diff = round($diff, $decimalplaces);
					$percent_change = $diff; // i'm not really sure what formula they want us to use for this
				}
				$data['last_check_percent'] = $percent_change;
				$data['last_check_dir'] = $direction;

				// pull in display data
				$data = array_merge($data, self::BENCHMARK_DISPLAY_DATA[$metric]);
				$result[$metric] = $data;

				$audits = [];
				$lcv = 0;
				$pattern = "/\[([^]]*)\] *\(([^)]*)\)/i";
                $replacement = '<a href="$2" target="_blank">$1</a>';
				if(isset($latest['scores']['audit_result'])){
					foreach ($latest['scores']['audit_result'] as $audit) {
						$display_value = '';
						$description = preg_replace($pattern, $replacement, $audit['description']);
						if(isset($audit['displayValue'])){
							$description .= '<br />' . $audit['displayValue'];
						}
						$audits[] = [
							'lcv' => $lcv,
							'display_text' => $audit['title'],
							'description' => $description,
						];
						++$lcv;
						if ($lcv > 4) {
							break;
						}
					}
				}
				$result['recommendations'] = [
					'list' => $audits
				];

				$result['recommendations'] = array_merge($result['recommendations'], self::BENCHMARK_DISPLAY_DATA['recommendations']);
			}

			return $result;
		}

		/**
		 * Register settings
		 *
		 * @since    3.0.0
		 */
		public function register_settings() {
			// The settings container.
			register_setting(
				Settings_Model::SETTINGS_NAME,     // Option group Name.
				Settings_Model::SETTINGS_NAME,     // Option Name.
				[ $this, 'sanitize' ] // Sanitize.
			);
		}

		/**
		 * Validates submitted setting values before they get saved to the database.
		 *
		 * @param array $input Settings Being Saved.
		 * @since    3.0.0
		 * @return array
		 */
		public function sanitize($input) {
			$new_input = [];
			if ( isset( $input ) && ! empty( $input ) ) {
				$new_input = $input;
			}

			return $new_input;
		}

		/**
		 * Returns the option key used to store the settings in database
		 *
		 * @since 3.0.0
		 * @return string
		 */
		public function get_plugin_settings_option_key() {
			return Settings_Model::get_plugin_settings_option_key();
		}

		/**
		 * Retrieves all of the settings from the database
		 *
		 * @param string $setting_name Setting to be retrieved.
		 * @since    3.0.0
		 * @return array
		 */
		public function get_setting($setting_name) {
			return Settings_Model::get_setting( $setting_name );
		}
	}
}
