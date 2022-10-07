<?php

namespace A2_Optimized\App\Views\Admin;

use A2_Optimized\Core\View;
use A2_Optimized as A2_Optimized;

if (! class_exists(__NAMESPACE__ . '\\' . 'Admin_Settings')) {
	/**
	 * View class to load all templates related to Plugin's Admin Settings Page
	 *
	 * @since      3.0.0
	 * @package    A2_Optimized
	 * @subpackage A2_Optimized/views/admin
	 */
	class Admin_Settings extends View {
		/**
		 * Prints Settings Page.
		 *
		 * @param  array $args Arguments passed by `markup_settings_page` method from `A2_Optimized\App\Controllers\Admin\Admin_Settings` controller.
		 * @return void
		 * @since 3.0.0
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
				'content-element' => '<page-speed-score @nav-change-url="loadPageByUrl" :update-Child="updateView" :key="mainkey"></page-speed-score>',
				'home_url' => home_url(),
				'nav' => [
					'pls_class' => 'current',
					'wsp_class' => '',
					'opt_class' => ''
				],
				'last_check_date' => $last_check,
				'explanations' => [
					'pagespeed' => 'Our page load speed score is calculated by measuring how fast your website loads. The higher the score, the faster your website loads. You can visit the “Optimization Recommendations" section to see how you can improve your website\'s page load speed.',
					'opt' => 'Our optimization status score shows how well your website performs in three categories:<br />
						- Performance (page load speed)<br />
						- Security (can your website be hacked)<br />
						- Best Practices (speed & security)<br />
						<br />	
						Optimizing your website for these three categories will improve your user experience, improve conversion rates and prevent malicious people from accessing your website. ',
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
				'content-element' => '<server-performance @nav-change-url="loadPageByUrl" :update-Child="updateView" :key="mainkey"></server-performance>',
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
				'content-element' => '<hosting-matchup @nav-change-url="loadPageByUrl" :update-Child="updateView" :key="mainkey"></hosting-matchup>',
				'home_url' => home_url(),
				'nav' => [
					'wsp_class' => 'current',
					'hmatch_class' => 'current'
				],
				'explanations' => [
					'webperformance' => 'The web performance score measures how your current host performs compared to A2 Hosting. This web performance score looks at server speed and other metrics to determine how fast your website will load, based on which hosting company & plan you host your website with. <br />
					The higher the score on the graph the faster your website will load. Not all hosting companies and plans use the same hardware. A2 Hosting uses the best server hardware on the market, focusing on speed & security. A2 Hosting also offers free site migration to help you move your existing websites to them. ',
					'serverperformance' => 'The higher the score on the graph, the faster your experience will be in the WordPress Admin dashboard and on pages that use dynamic content that can\'t be easily cached—like search forms and shopping carts. <br />
					Not all hosting companies and plans use the same hardware. If your current host has a lower server performance score than A2 Hosting, then consider moving your websites to A2 Hosting. A2 Hosting uses the best server hardware on the market, focusing on speed & security. A2 Hosting also offers free site migration to help you move your existing websites to them.',
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
				'content-element' => '<optimizations-performance @nav-change-url="loadPageByUrl" :update-Child="updateView" :key="mainkey"></optimizations-performance>',
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
				'settings_tethers' => $data['settings_tethers'],
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

		/**
		 * Prints Section's Description.
		 *
		 * @param  array $args Arguments passed by `markup_section_headers` method from  `A2_Optimized\App\Controllers\Admin\Admin_Settings` controller.
		 * @return void
		 * @since 3.0.0
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
		 * @since 3.0.0
		 */
		public function markup_fields($args = []) {
			echo $this->render_template(
				'admin/page-settings/page-settings-fields.php',
				$args
			); // WPCS: XSS OK.
		}
	}
}
