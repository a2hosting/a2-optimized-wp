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
			$last_check = 'None';
			$graphs = $args['graphs'];
			if ($graphs['pagespeed_desktop']) {
				$last_check = $graphs['pagespeed_desktop']['overall_score']['last_check_date'];
			} elseif ($graphs['pagespeed_mobile']) {
				$last_check = $graphs['pagespeed_mobile']['overall_score']['last_check_date'];
			}

			$data = [
				'mainkey' => 1,
				'updateView' => 0,
				'notifications' => $args['notifications'],
				'content-element' => '<page-speed-score :update-Child="updateView" :key="mainkey"></page-speed-score>',
				'home_url' => home_url(),
				'nav' => [
					'pls_class' => 'current',
					'wsp_class' => '',
					'opt_class' => ''
				],
				'last_check_date' => $last_check,
				'explanations' => [
					'pagespeed' => 'The Performance score is a weighted average of the metric scores. Naturally, more heavily weighted metrics have a bigger effect on your overall Performance score. The metric scores are not visible in the report, but are calculated under the hood.',
					'opt' => 'Vearious optimizations hand-selected to help keep your site fast, safe and secure.',
				],
				'graphs' => $args['graphs']
			];

			$data_json = json_encode($data);
			/*
			if ($args['run_benchmarks']) {
				echo $data_json;

				return;
			}
			*/
			$data['data_json'] = $data_json;
			$args['data'] = $data;
			echo $this->render_template(
				'admin/page-settings/page-settings.php',
				$args
			); // WPCS: XSS OK.
		}

		public function admin_server_performance_page($args = []) {
			$pagespeed_last_check = 'None';
			$graphs = $args['graphs'];
			if ($graphs) {
				$pagespeed_last_check = $graphs['overall_score']['last_check_date'];
			}

			$data = [
				'mainkey' => 1,
				'updateView' => 0,
				'content-element' => '<server-performance :update-Child="updateView" :key="mainkey"></server-performance>',
				'home_url' => home_url(),
				'nav' => [
					'wsp_class' => 'current',
					'webperf_class' => 'current'
				],
				'last_check_date' => $pagespeed_last_check,
				'graphs' => $graphs,
			];
			$data_json = json_encode($data);

			$data['data_json'] = $data_json;
			$args['data'] = $data;
			echo $this->render_template(
				'admin/page-settings/page-settings.php',
				$args
			); // WPCS: XSS OK.
		}

		public function admin_hosting_matchup_page($args = []) {
			$last_check = 'None';
			$data = $args['data'];
			if ($data) {
				$last_check = $data['last_check_date'];
			}

			$data = [
				'mainkey' => 1,
				'updateView' => 0,
				'content-element' => '<hosting-matchup :update-Child="updateView" :key="mainkey"></hosting-matchup>',
				'home_url' => home_url(),
				'nav' => [
					'wsp_class' => 'current',
					'hmatch_class' => 'current'
				],
				'explanations' => [
					'webperformance' => 'you want good performance for your web',
					'serverperformance' => 'you want good performance for your server',
				],
				'last_check_date' => $last_check,
				'graphs' => $data['graphs'],
				'graph_data' => $data['graph_data']
			];
			$data_json = json_encode($data);

			$data['data_json'] = $data_json;
			$args['data'] = $data;

			echo $this->render_template(
				'admin/page-settings/page-settings.php',
				$args
			); // WPCS: XSS OK.
		}

		public function admin_opt_performance_page($args = []) {
			
			$data = $args['data'];

			$data = [
				'mainkey' => 1,
				'updateView' => 0,
				'content-element' => '<optimizations-performance :update-Child="updateView" :key="mainkey"></optimizations-performance>',
				'home_url' => home_url(),
				'nav' => [
					'opt_class' => 'current',
					'optperf_class' => 'current'
				],
				'sidenav' => 'optperf',
				'perf_more' => 'false',
				'sec_more' => 'false',
				'opt_counts' => $data['opt_counts'],
				'optimizations' => $data['optimizations'],
				'other_optimizations' => $data['other_optimizations'],
				'best_practices' => $data['best_practices'],
				'extra_settings' => $data['extra_settings'],
				'graphs' => $data['graphs']
			];
			$data_json = json_encode($data);

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
