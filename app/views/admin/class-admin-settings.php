<?php

namespace A2_Optimized\App\Views\Admin;

use A2_Optimized\Core\View;
use A2_Optimized as A2_Optimized;

if (! class_exists(__NAMESPACE__ . '\\' . 'Admin_Settings')) {
	/**
	 * View class to load all templates related to Plugin's Admin Settings Page
	 *
	 * @since      1.0.0
	 * @package    A2_Optimized
	 * @subpackage A2_Optimized/views/admin
	 */
	class Admin_Settings extends View {
		/**
		 * Prints Settings Page.
		 *
		 * @param  array $args Arguments passed by `markup_settings_page` method from `A2_Optimized\App\Controllers\Admin\Admin_Settings` controller.
		 * @return void
		 * @since 1.0.0
		 */
		public function admin_pagespeed_page($args = []) {
			$temp_graphs = [
				'opt_perf' => [
					'score' => 38,
					'max' => 100,
					'text' => '3/8',
					'color_class' => 'success',
				],
				'opt_security' => [
					'score' => 20,
					'max' => 100,
					'text' => '1/5',
					'color_class' => 'danger',
				],
				'opt_bp' => [
					'score' => 0,
					'max' => 100,
					'text' => '0/7',
					'color_class' => 'danger',
				],
			];

			$data = [
				'content-element' => '<page-speed-score></page-speed-score>',
				'home_url' => home_url(),
				'nav' => [
					'pls_class' => 'current'
				],
				'explanations' => [
					'pagespeed' => 'super detailed information about pagespeed',
					'opt' => 'super detailed information about opt',
				],
				'graphs' => array_merge($args['graphs'], $temp_graphs)
				/*				
				'graphs' => [
					'pagespeed_mobile' => [
						'score' => 56,
						'max' => 100,
						'text' => '56',
						'color_class' => 'danger',
						'change' => '<p><span class="danger"><span class="glyphicon glyphicon-arrow-down" aria-hidden="true"></span> 20%</span> Since Last Check</p>',
					],
					'pagespeed_desktop' => [
						'score' => 93,
						'max' => 100,
						'text' => '93',
						'color_class' => 'success',
						'change' => '<p>&nbsp;</p>',
					],
					'opt_perf' => [
						'score' => 38,
						'max' => 100,
						'text' => '3/8',
						'color_class' => 'success',
					],
					'opt_security' => [
						'score' => 20,
						'max' => 100,
						'text' => '1/5',
						'color_class' => 'danger',
					],
					'opt_bp' => [
						'score' => 0,
						'max' => 100,
						'text' => '0/7',
						'color_class' => 'danger',
					],
				], */
			];

			$data_json = json_encode($data);
			if ($args['run_benchmarks']){
				echo $data_json;
				return;
			}
			$data['data_json'] = $data_json;
			$args['data'] = $data;
			echo $this->render_template(
				'admin/page-settings/page-settings.php',
				//'admin/page-settings/page-pagespeedscore.php',
				$args
			); // WPCS: XSS OK.
		}

		public function admin_server_performance_page($args = []){
			$temp_graphs = [];
			$data = [
				'content-element' => '<server-performance></server-performance>',
				'home_url' => home_url(),
				'nav' => [
					'wsp_class' => 'current'
				],
				'last_check' => 'Last Check May 35th 2024',
				'performance' => array_merge($args['graphs'], $temp_graphs),
				/*
				'performance' => [
					'Overall' => [
						'display_text' => 'Overall Score',
						'metric_text' => 'This is the overall score, with much words to be said about it',
						'color_class' => 'warn',
						'text' => '56%',
						'score' => 56,
						'max' => 100,
						'thresholds' => [
							'success' => 0,
							'warn' => 50,
							'danger' => 80
						],
						'last_check_percent' => '23%',
						'last_check_dir' => 'down',
						'explanation' => 'Here is a detailed explanation of what the Overall Score means, as rendered lovingly by George'
					],
					'TTFB' => [
						'display_text' => 'Server Speed',
						'metric_text' => 'Time to first Byte (TTFB)',
						'color_class' => 'success',
						'text' => '40mls',
						'score' => 40,
						'max' => 100,
						'thresholds' => [
							'success' => 0,
							'warn' => 30,
							'danger' => 60
						],
						'last_check_percent' => '37%',
						'last_check_dir' => 'up',
						'explanation' => 'Here is a detailed explanation of what the TTFB means, as rendered lovingly by George'
					],
					'FCP' => [
						'display_text' => 'User Perception',
						'metric_text' => 'First Contentful Paint (FCP)',
						'color_class' => 'danger',
						'text' => '40s',
						'score' => 40,
						'max' => 6.0,
						'thresholds' => [
							'success' => 0,
							'warn' => 2.5,
							'danger' => 4.0
						],
						'last_check_percent' => '23%',
						'last_check_dir' => 'down',
						'explanation' => 'Here is a detailed explanation of what the FCP means, as rendered lovingly by George'
					],
					'LCP' => [
						'display_text' => 'Page Load Speed',
						'metric_text' => 'Largest Contentful Paint (LCP)',
						'color_class' => 'danger',
						'text' => '2.0s',
						'score' => 2.0,
						'max' => 4.0,
						'thresholds' => [
							'success' => 0,
							'warn' => 2.5,
							'danger' => 4.0
						],
						'last_check_percent' => '',
						'last_check_dir' => '',
						'explanation' => 'Here is a detailed explanation of what the LCP means, as rendered lovingly by George'
					],
					'FID' => [
						'display_text' => 'Website Browser Speed',
						'metric_text' => 'First Input Delay (FID)',
						'color_class' => 'success',
						'text' => '21',
						'score' => 21,
						'max' => 300,
						'thresholds' => [
							'success' => 0,
							'warn' => 100,
							'danger' => 300
						],
						'last_check_percent' => '',
						'last_check_dir' => '',
						'explanation' => 'Here is a detailed explanation of what the FID means, as rendered lovingly by George'
					],
					'CLS' => [
						'display_text' => 'Visual Stability',
						'metric_text' => 'Cumulative Layout Shift (CLS)',
						'color_class' => 'warn',
						'text' => '.13',
						'score' => .13,
						'max' => .25,
						'thresholds' => [
							'success' => 0,
							'warn' => .1,
							'danger' => .25
						],
						'last_check_percent' => '37%',
						'last_check_dir' => 'down',
						'explanation' => 'Here is a detailed explanation of what the CLS means, as rendered lovingly by George'
					]
				],
				*/
				'recommendations' => [
					'display_text' => 'Opportunity',
					'list' => [
						['display_text' => 'Do better'],
						['display_text' => 'Make your ancestors proud'],
						['display_text' => 'Use less javascript']
					],
					'explanation' => 'Here is an explanation of what recommendations are for, and what to do about them'
				]
			];
			$data_json = json_encode($data);
			if ($args['run_benchmarks']){
				echo $data_json;
				return;
			}

			$data['data_json'] = $data_json;
			$args['data'] = $data;
			echo $this->render_template(
				'admin/page-settings/page-settings.php',
				$args
			); // WPCS: XSS OK.
		}

		public function admin_settings_page($args = []) {
			$data = [
				'content-element' => '<page-speed-score></page-speed-score>',
				'home_url' => home_url(),
				'nav' => [
					'pls_class' => 'current'
				],
				'explanations' => [
					'pagespeed' => 'super detailed information about pagespeed',
					'opt' => 'super detailed information about opt',
				],
				'graphs' => [
					'pagespeed_mobile' => [
						'score' => 56,
						'max' => 100,
						'text' => '56',
						'color_class' => 'danger',
						'change' => '<p><span class="danger"><span class="glyphicon glyphicon-arrow-down" aria-hidden="true"></span> 20%</span> Since Last Check</p>',
					],
					'pagespeed_desktop' => [
						'score' => 93,
						'max' => 100,
						'text' => '93',
						'color_class' => 'success',
						'change' => '<p>&nbsp;</p>',
					],
					'opt_perf' => [
						'score' => 38,
						'max' => 100,
						'text' => '3/8',
						'color_class' => 'success',
					],
					'opt_security' => [
						'score' => 20,
						'max' => 100,
						'text' => '1/5',
						'color_class' => 'danger',
					],
					'opt_bp' => [
						'score' => 0,
						'max' => 100,
						'text' => '0/7',
						'color_class' => 'danger',
					],
				],
			];
			$data_json = json_encode($data);
			$data['data_json'] = $data_json;
			$args['data'] = $data;
			echo $this->render_template(
				'admin/page-settings/page-settings.php',
				$args
			); // WPCS: XSS OK.
		}

		/**
		 * Prints Section's Description.
		 *
		 * @param  array $args Arguments passed by `markup_section_headers` method from  `A2_Optimized\App\Controllers\Admin\Admin_Settings` controller.
		 * @return void
		 * @since 1.0.0
		 */
		public function section_headers($args = []) {
			echo $this->render_template(
				'admin/page-settings/page-settings-section-headers.php',
				$args
			); // WPCS: XSS OK.
		}

		/**
		 * Prints text field
		 *
		 * @param  array $args Arguments passed by `markup_fields` method from `A2_Optimized\App\Controllers\Admin\Admin_Settings` controller.
		 * @return void
		 * @since 1.0.0
		 */
		public function markup_fields($args = []) {
			echo $this->render_template(
				'admin/page-settings/page-settings-fields.php',
				$args
			); // WPCS: XSS OK.
		}
	}
}
