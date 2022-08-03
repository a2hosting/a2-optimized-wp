<?php
namespace A2_Optimized\App\Models\Admin;

use A2_Optimized\App\Models\Settings as Settings_Model;
use A2_Optimized\App\Models\Admin\Base_Model;
use A2_Optimized_Benchmark;

require_once(__DIR__ . '/../../../A2_Optimized_Benchmark.php');

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
			$data = $this->get_benchmark(false);

			$data['pagespeed_desktop']['overall_score']['score'] = 55;
			$data['pagespeed_desktop']['overall_score']['text'] = '55';
			$data['pagespeed_desktop']['overall_score']['score'] = 55;
			$data['pagespeed_desktop']['overall_score']['last_check_percent'] = 20;
			$data['pagespeed_desktop']['overall_score']['last_check_direction'] = 'up';

			

			if ($page == 'server_performance'){
				$data = $data['pagespeed_desktop'];
			}
			echo json_encode($data);
			wp_die();

		}

		public function get_benchmark($run = false) {
			if ($run){
				$desktop_result = $this->benchmark->get_lighthouse_results('desktop');
				$mobile_result = $this->benchmark->get_lighthouse_results('mobile');

				if ($desktop_result['status'] != 'success' || $mobile_result['status'] != 'success'){
					// then what? dunno.
				}
			}
			$last_desktop = null;
			$last_mobile = null;
			$prev_desktop = null;
			$prev_mobile = null;
			$desktop_check_date = null;
			$mobile_check_date = null;

			$frontend_benchmarks = get_option('a2opt-benchmarks-frontend');

			foreach (array_reverse($frontend_benchmarks) as $check_date => $fbm){
				if ($fbm['strategy'] == 'desktop'){
					if ($last_desktop == null){
						$desktop_check_date = $check_date;
						$last_desktop = $fbm;
					}
					else if ($prev_desktop == null){
						$prev_desktop = $fbm;
					}
				}
				else if ($fbm['strategy'] == 'mobile'){
					if ($last_mobile == null){
						$mobile_check_date = $check_date;
						$last_mobile = $fbm;
					}
					else if ($prev_mobile == null){
						$prev_mobile = $fbm;
					}
				}

				if (isset($last_desktop) && isset($prev_desktop) && isset($last_mobile) && isset($prev_mobile) ){
					break;
				}
			}
			$result['pagespeed_desktop'] = $this->get_graph_data($desktop_check_date, $last_desktop, $prev_desktop);
			$result['pagespeed_mobile'] = $this->get_graph_data($mobile_check_date, $last_mobile, $prev_mobile);
			//print_r($result);

			return $result;
		}

		public const BENCHMARK_DISPLAY_DATA = [
			'overall_score' => [
				'display_text' => 'Overall Score',
				'metric_text' => 'This is the overall score, with much words to be said about it',
				'explanation' => 'Here is a detailed explanation of what the Overall Score means, as rendered lovingly by George'
			],
			'ttfb' => [
				'display_text' => 'Server Speed',
				'metric_text' => 'Time to first Byte (TTFB)',
				'explanation' => 'Here is a detailed explanation of what the TTFB means, as rendered lovingly by George'
			],
			'fcp' => [
				'display_text' => 'User Perception',
				'metric_text' => 'First Contentful Paint (FCP)',
				'explanation' => 'Here is a detailed explanation of what the FCP means, as rendered lovingly by George'
			],
			'lcp' => [
				'display_text' => 'Page Load Speed',
				'metric_text' => 'Largest Contentful Paint (LCP)',
				'explanation' => 'Here is a detailed explanation of what the LCP means, as rendered lovingly by George'
			],
			'fid' => [
				'display_text' => 'Website Browser Speed',
				'metric_text' => 'First Input Delay (FID)',
				'explanation' => 'Here is a detailed explanation of what the FID means, as rendered lovingly by George'
			],
			'cls' => [
				'display_text' => 'Visual Stability',
				'metric_text' => 'Cumulative Layout Shift (CLS)',
				'explanation' => 'Here is a detailed explanation of what the CLS means, as rendered lovingly by George'
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
	
		public function get_score_status_and_thresholds($metric, $score){
			// invert values when needed
			$thresholds = self::BENCHMARK_SCORE_PROFILES[$metric];
			$display_thresholds = $thresholds;
			if ($metric == 'overall_score'){
				$score = $thresholds['success'] - $score;
				$thresholds = self::BENCHMARK_SCORE_PROFILES[$metric . '_inverted'];
			}

			$status = 'success';
			if ($score >= $thresholds['warn']) { $status = 'warn';}
			if ($score >= $thresholds['danger']) { $status = 'danger';}

			return [
				'status' => $status,
				'thresholds' => $display_thresholds
			];
		}

		public function get_graph_data($last_check_date, $latest, $previous){
			$metrics = ['overall_score', 'fcp', 'ttfb', 'cls','lcp', 'fid'];

			//print_r(json_encode(array_keys($previous['scores'])) . "<br>");
			$latest['scores']['overall_score'] = 10;
			$latest['scores']['ttfb'] = 600;
			$latest['scores']['lcp'] = 5000;
			//$previous['scores']['overall_score'] = 50;
			$result = [];
			foreach ($metrics as $metric){
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
				if ($diff != 0){
					if ($diff < 0){
						$direction = 'down';
					}
					else if ($diff > 0){
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
	
			}
			return $result;
		}

		private function format_backend_results($data){
			$output = "\n\rBackend Results\n\r";
			$output .= "==========================\n\r";
			$output .= "Overall Score: " . round($data['wordpress_db']['queries_per_second']) . "\n\r";
			$output .= "PHP: " . $data['php']['total'] . "\n\r";
			$output .= "MySQL: " . $data['mysql']['benchmark']['mysql_total'] . "\n\r";
			$output .= "Filesystem: " . $data['filesystem'] . "\n\r";
			
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
				array( $this, 'sanitize' ) // Sanitize.
			);
		}

		/**
		 * Validates submitted setting values before they get saved to the database.
		 *
		 * @param array $input Settings Being Saved.
		 * @since    1.0.0
		 * @return array
		 */
		public function sanitize( $input ) {
			$new_input = array();
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
		public function get_setting( $setting_name ) {
			return Settings_Model::get_setting( $setting_name );
		}

	}

}
