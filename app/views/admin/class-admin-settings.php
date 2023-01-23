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
		const EVENT_LISTENERS = '@nav-change-url="loadSubPage"';
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

			if(isset($_GET['data-collection']) && isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'a2opt_datacollection_nonce') ){
				if($_GET['data-collection'] == 'yes'){
					update_option('a2_sitedata_allow', '1');
				}
				if($_GET['data-collection'] == 'no'){
					update_option('a2_sitedata_allow', '2');
				}
			}

			$data = [
				'mainkey' => 1,
				'updateView' => 0,
				'notifications' => $args['notifications'],
				'content-element' => '<page-speed-score :update-Child="updateView" :key="mainkey" ' . self::EVENT_LISTENERS . '></page-speed-score>',
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
				'content-element' => '<server-performance :update-Child="updateView" :key="mainkey" ' . self::EVENT_LISTENERS . '></server-performance>',
				'home_url' => home_url(),
				'nav' => [
					'wsp_class' => 'current',
					'webperf_class' => 'current'
				],
				'last_check_date' => $pagespeed_last_check,
				'graphs' => $graphs,
				'default_strategy' => $args['default_strategy'],
				'status_message' => $args['status_message'],
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
				'content-element' => '<hosting-matchup :update-Child="updateView" :key="mainkey" ' . self::EVENT_LISTENERS . '></hosting-matchup>',
				'home_url' => home_url(),
				'nav' => [
					'wsp_class' => 'current',
					'hmatch_class' => 'current'
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
		
		public function admin_advanced_settings_page($args = []) {
			$data = $args['data'];

			$data = [
				'mainkey' => 1,
				'updateView' => 0,
				'content-element' => '<advanced-settings :update-Child="updateView" :key="mainkey" ' . self::EVENT_LISTENERS . '></advanced-settings>',
				'home_url' => home_url(),
				'nav' => [
					'wsp_class' => 'current',
					'advs_class' => 'current'
				],
				'advanced_settings' => $data
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
				'content-element' => '<optimizations-performance :update-Child="updateView" :key="mainkey" ' . self::EVENT_LISTENERS . '></optimizations-performance>',
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
