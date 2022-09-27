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
	 * @since      1.0.0
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
		 * @since    1.0.0
		 */
		protected function __construct() {
			$this->register_hook_callbacks();
			$this->benchmark = new A2_Optimized_Benchmark();
			$this->optimizations = new A2_Optimized_Optimizations();
		}

		/**
		 * Register callbacks for actions and filters
		 *
		 * @since    1.0.0
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
			add_action( 'wp_ajax_add_notification', [$this, 'add_notification'] );
		}

		/* Callback for run_benchmarks AJAX request */
		public function run_benchmarks() {
			if ( !wp_verify_nonce($_POST['nonce'], 'a2opt_ajax_nonce') ){ 
				echo json_encode(['result' => 'fail', 'status' => 'Permission Denied']);
				wp_die(); 
			}
			
			$target_url = $_POST['target_url'];
			$page = $_POST['a2_page'];
			$run_checks = $_POST['run_checks'] !== 'false';

			switch($page){
				case 'server-performance':
				case 'page-speed-score':
					$frontend_data = $this->get_frontend_benchmark($run_checks);

					if ($page == 'server-performance') {
						$strategy = 'pagespeed_' . $_POST['a2_performance_strategy'];
						$frontend_data = $frontend_data[$strategy];
					}

					$opt_data = $this->get_opt_performance();

					$data = array_merge($frontend_data, $opt_data['graphs']);
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
			$new_notification = $_POST['a2_notification_text'] ?? '';
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
			if ( !wp_verify_nonce($_POST['nonce'], 'a2opt_ajax_nonce') ){ 
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
			$data['post_data'] = $_POST;
			$data['updated_data'] = $opt_perf;

			$data['result'] = 'success';

			echo json_encode($data);
			wp_die();
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
				$result['last_check_date'] = $bm['sysinfo']['time'];
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
				$result['graph_data'][$key] = $entry;
			}
			$result['graphs']['webperformance'] = self::BENCHMARK_DISPLAY_DATA['webperformance'];
			$result['graphs']['serverperformance'] = self::BENCHMARK_DISPLAY_DATA['serverperformance'];

			return $result;
		}

		public function get_frontend_benchmark($run = false) {
			if ($run) {
				$desktop_result = $this->benchmark->get_lighthouse_results('desktop');
				$mobile_result = $this->benchmark->get_lighthouse_results('mobile');

				if ($desktop_result['status'] != 'success' || $mobile_result['status'] != 'success') {
					// then what? dunno.
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
				$result['pagespeed_desktop'] = false;
				$result['pagespeed_mobile'] = false;
			}

			return $result;
		}

		public function get_opt_performance() {
			$result = [];
			$result['optimizations'] = $this->optimizations->get_optimizations();
			$result['best_practices'] = $this->optimizations->get_best_practices();
			$extra_settings = $result['optimizations']['extra_settings']; // has to be before $result['optimizations'] gets changed

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
			return $result;
		}

		public const BENCHMARK_DISPLAY_DATA = [
			'overall_score' => [
				'display_text' => 'Overall Score',
				'metric_text' => 'The Performance score is a weighted average of the metric scores.',
				'explanation' => 'The Performance score is a weighted average of the metric scores. Naturally, more heavily weighted metrics have a bigger effect on your overall Performance score. The metric scores are not visible in the report, but are calculated under the hood.'
			],
			'ttfb' => [
				'display_text' => 'Server Speed',
				'metric_text' => 'Time to first Byte (TTFB)',
				'explanation' => 'Time to First Byte (TTFB) is a foundational metric for measuring connection setup time and web server responsiveness in both the lab and the field. It helps identify when a web server is too slow to respond to requests. In the case of navigation requests—that is, requests for an HTML document—it precedes every other meaningful loading performance metric.'
			],
			'fcp' => [
				'display_text' => 'User Perception',
				'metric_text' => 'First Contentful Paint (FCP)',
				'explanation' => 'First Contentful Paint (FCP) is an important, user-centric metric for measuring perceived load speed because it marks the first point in the page load timeline where the user can see anything on the screen—a fast FCP helps reassure the user that something is happening.'
			],
			'lcp' => [
				'display_text' => 'Page Load Speed',
				'metric_text' => 'Largest Contentful Paint (LCP)',
				'explanation' => 'Largest Contentful Paint (LCP) is an important, user-centric metric for measuring perceived load speed because it marks the point in the page load timeline when the page\'s main content has likely loaded—a fast LCP helps reassure the user that the page is useful.'
			],
			'fid' => [
				'display_text' => 'Website Browser Speed',
				'metric_text' => 'First Input Delay (FID)',
				'explanation' => 'First Input Delay (FID) is an important, user-centric metric for measuring load responsiveness because it quantifies the experience users feel when trying to interact with unresponsive pages—a low FID helps ensure that the page is usable.'
			],
			'cls' => [
				'display_text' => 'Visual Stability',
				'metric_text' => 'Cumulative Layout Shift (CLS)',
				'explanation' => 'Cumulative Layout Shift (CLS) is an important, user-centric metric for measuring visual stability because it helps quantify how often users experience unexpected layout shifts—a low CLS helps ensure that the page is delightful.'
			],
			'recommendations' => [
				'display_text' => 'Opportunity',
				'metric_text' => 'Areas that can be improved',
				'explanation' => 'These suggestions can help your page load faster. They don\'t directly affect the Performance score.',
			],
			'benchmark-host' => [
				'display_text' => 'Your Host',
				'metric_text' => '',
				'explanation' => 'this is your pathetic slow host',
				'color_class' => 'thishost'
			],
			'a2hosting-turbo' => [
				'display_text' => 'Turbo Boost',
				'metric_text' => '',
				'explanation' => 'fast as lightning',
				'color_class' => 'warn'
			],
			'a2hosting-mwp' => [
				'display_text' => 'Managed Wordpress',
				'metric_text' => '',
				'explanation' => 'this is your pathetic slow host',
				'color_class' => 'success'
			],
			'webperformance' => [
				'display_text' => 'Web Performance',
				'metric_text' => 'How does your hosting provider compare to A2 Hosting?',
				'legend_text' => "Overall Wordpress Execution Time",
				'explanation' => 'web perf explanation',
				'color_class' => 'success'
			],
			'serverperformance' => [
				'display_text' => 'Server Performance',
				'metric_text' => "How fast is your hosting provider compare to A2 Hosting's server?",
				'legend_text' => "PHP, Mysql, and File I/O Response Time Comparison",
				'explanation' => 'server perf explanation',
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
			]

		];

		public const BENCHMARK_SCORE_PROFILES = [
			//higher is better [low/med/high] so we will invert the score when checking
			'overall_score' => ['success'=>100,  'warn'=>89, 'danger'=>49, 'max'=>100],
			'overall_score_inverted' => ['success'=>0, 'warn'=>11, 'danger'=>51],

			//lower is better [high/med/low]
			'fcp' => ['success'=> 1999, 'warn'=>3999, 'danger'=>6000, 'max'=>6000],
			'ttfb' => ['success'=> 99, 'warn'=>599, 'danger'=>1000, 'max'=>1000],
			'cls' => ['success'=> 0.1, 'warn'=>0.25, 'danger'=>1, 'max'=>1],
			'lcp' => ['success'=> 2499, 'warn'=>3999, 'danger'=>6000, 'max'=>6000],
			'fid' => ['success'=> 99, 'warn'=>299, 'danger'=>1000, 'max'=>1000],
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

		public function get_graph_data($last_check_date, $latest, $previous) {
			$metrics = ['overall_score', 'fcp', 'ttfb', 'cls','lcp', 'fid'];

			$result = [];
			foreach ($metrics as $metric) {
				$latest_score = $latest['scores'][$metric];
				$previous_score = $previous['scores'][$metric];
				$status_info = $this->get_score_status_and_thresholds($metric, $latest_score);
				/*
				$latest_score = rand(0, $status_info['thresholds']['max']);
				$previous_score = rand(0, $status_info['thresholds']['max']);
				$status_info = $this->get_score_status_and_thresholds($metric, $latest_score);
				*/

				$data = [
					'last_check_date' => $last_check_date,
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
		 * @since    1.0.0
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
		 * @since    1.0.0
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
		 * @since 1.0.0
		 * @return string
		 */
		public function get_plugin_settings_option_key() {
			return Settings_Model::get_plugin_settings_option_key();
		}

		/**
		 * Retrieves all of the settings from the database
		 *
		 * @param string $setting_name Setting to be retrieved.
		 * @since    1.0.0
		 * @return array
		 */
		public function get_setting($setting_name) {
			return Settings_Model::get_setting( $setting_name );
		}
	}
}
