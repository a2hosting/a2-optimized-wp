<?php
namespace A2_Optimized\App\Models\Admin;

use A2_Optimized\App\Models\Settings as Settings_Model;
use A2_Optimized\App\Models\Admin\Base_Model;
use A2_Optimized_Benchmark;

//require_once(__DIR__ . '/../../../includes/A2_Optimized_Benchmark.php');

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

		/**
		 * Constructor
		 *
		 * @since    1.0.0
		 */
		protected function __construct() {
			$this->register_hook_callbacks();
			$this->benchmark = new A2_Optimized_Benchmark();
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
		}

		public function run_benchmarks() {
			$target_url = $_POST['target_url'];
			$page = $_POST['a2_page'];
			$frontend_data = $this->get_frontend_benchmark(false);//false for now while getting the UI in order

			if ($page == 'server_performance') {
				$frontend_data = $frontend_data['pagespeed_desktop'];
			}

			$opt_data = $this->get_optimization_benchmark();

			$data = array_merge($frontend_data, $opt_data);

			echo json_encode($data);
			wp_die();
		}

		public function get_optimization_benchmark() {
			$temp_graphs = [
				'opt_perf' => [
					'display_text' => 'Performance',
					'metric_text' => '',
					'thresholds' => [],
					'explanation' => '',
					'last_check_percent' => 0,
					'last_check_dir' => 'none',
					'score' => ((5 / 8) * 100),
					'max' => 100,
					'text' => '5/8',
					'color_class' => 'warn',
				],
				'opt_security' => [
					'display_text' => 'Security',
					'metric_text' => '',
					'thresholds' => [],
					'explanation' => '',
					'last_check_percent' => 0,
					'last_check_dir' => 'none',
					'score' => ((1 / 5) * 100),
					'max' => 100,
					'text' => '1/5',
					'color_class' => 'danger',
				],
				'opt_bp' => [
					'display_text' => 'Best Practices',
					'metric_text' => '',
					'thresholds' => [],
					'explanation' => '',
					'last_check_percent' => 0,
					'last_check_dir' => 'none',
					'score' => ((7 / 7) * 100),
					'max' => 100,
					'text' => '7/7',
					'color_class' => 'success',
				],
			];

			return $temp_graphs;
		}

		public function get_frontend_benchmark($run = false) {
			if ($run) {
				//print_r("running benchmarks<br>");
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

			//print_r($result);

			//$backend_benchmarks = get_option('a2opt-benchmarks-hosting');
			//$bm = array_pop($backend_benchmarks);
			//print_r($bm);
			//wp_die();

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

			//print_r(json_encode(array_keys($previous['scores'])) . "<br>");
			//$latest['scores']['overall_score'] = 10;
			//$latest['scores']['ttfb'] = 600;
			//$latest['scores']['lcp'] = 5000;
			//$previous['scores']['overall_score'] = 50;
			$result = [];
			foreach ($metrics as $metric) {
				$latest_score = $latest['scores'][$metric];
				$previous_score = $previous['scores'][$metric];
				$status_info = $this->get_score_status_and_thresholds($metric, $latest_score);
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
				//print_r("{$metric}: {$latest_score} - {$previous_score}<br>");
				$diff = $latest_score - $previous_score;
				//print_r("{$diff}<br>");
				if ($diff != 0) {
					if ($diff < 0) {
						$direction = 'down';
					} elseif ($diff > 0) {
						$direction='up';
					}
					$diff = abs($diff);
					$percent_change = $diff; // i'm not really sure what formula they want us to use for this
				}
				//print_r("{$percent_change} - {$direction}<br>");
				$data['last_check_percent'] = $percent_change;
				$data['last_check_dir'] = $direction;

				// pull in display data
				$data = array_merge($data, self::BENCHMARK_DISPLAY_DATA[$metric]);
				$result[$metric] = $data;

				$audits = [];
				//print_r(json_encode($fbm['scores']['audit_result']['first-contentful-paint']));
				$lcv = 0;
				foreach ($latest['scores']['audit_result'] as $audit) {
					$audits[] = [
						'display_text' => $audit['title'],
						'description' => $audit['description']
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
				//print_r($result); wp_die();
			}

			return $result;
		}

		private function format_backend_results($data) {
			$output = "\n\rBackend Results\n\r";
			$output .= "==========================\n\r";
			$output .= 'Overall Score: ' . round($data['wordpress_db']['queries_per_second']) . "\n\r";
			$output .= 'PHP: ' . $data['php']['total'] . "\n\r";
			$output .= 'MySQL: ' . $data['mysql']['benchmark']['mysql_total'] . "\n\r";
			$output .= 'Filesystem: ' . $data['filesystem'] . "\n\r";

			return $output;
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
