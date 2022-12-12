<?php

namespace A2_Optimized\App\Controllers\Admin;

use A2_Optimized\App\Controllers\Admin\Base_Controller;
use A2_Optimized as A2_Optimized;

if (! class_exists(__NAMESPACE__ . '\\' . 'Admin_Settings')) {
	/**
	 * Controller class that implements Plugin Admin Settings configurations
	 *
	 * @since      3.0.0
	 * @package    A2_Optimized
	 * @subpackage A2_Optimized/controllers/admin
	 */
	class Admin_Settings extends Base_Controller {
		/**
		 * Holds suffix for dynamic add_action called on settings page.
		 *
		 * @var string
		 * @since 3.0.0
		 */
		private static $hook_suffix = 'toplevel_page_' . A2_Optimized::PLUGIN_ID;

		/**
		 * Slug of the Settings Page
		 *
		 * @since    3.0.0
		 */
		const SETTINGS_PAGE_SLUG = A2_Optimized::PLUGIN_ID;

		/**
		 * Capability required to access settings page
		 *
		 * @since 3.0.0
		 */
		const REQUIRED_CAPABILITY = 'manage_options';

		/**
		 * Register callbacks for actions and filters
		 *
		 * @since    3.0.0
		 */
		public function register_hook_callbacks() {
			// Create Menu.
			add_action('admin_menu', [ $this, 'plugin_menu' ]);

			// Enqueue Styles & Scripts.
			add_action('admin_print_scripts-' . static::$hook_suffix, [ $this, 'enqueue_scripts' ]);
			add_action('admin_print_styles-' . static::$hook_suffix, [ $this, 'enqueue_styles' ]);
			
			// Register Fields.
			add_action('load-' . static::$hook_suffix, [ $this, 'register_fields' ]);

			// Register Settings.
			add_action('admin_init', [ $this->get_model(), 'register_settings' ]);

			// Settings Link on Plugin's Page.
			add_filter(
				'plugin_action_links_' . A2_Optimized::PLUGIN_ID . '/' . A2_Optimized::PLUGIN_ID . '.php',
				[ $this, 'add_plugin_action_links' ]
			);

			add_filter('submenu_file', [ $this, 'highlight_active_submenu' ]);


		}


		/**
		 * Create menu for Plugin inside Settings menu
		 *
		 * @since    3.0.0
		 */
		public function plugin_menu() {
			// @codingStandardsIgnoreStart.

			$icon_base64 = 'PHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKCSB3aWR0aD0iMTAwJSIgdmlld0JveD0iMCAwIDMwNCAzMDQiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDMwNCAzMDQiIHhtbDpzcGFjZT0icHJlc2VydmUiPgo8cGF0aCBmaWxsPSJibGFjayIgb3BhY2l0eT0iMS4wMDAwMDAiIHN0cm9rZT0ibm9uZSIgCglkPSIKTTEwMi40MzExMjksMjIuMjQwMTcwIAoJQzExNy40MDM3NjMsMjEuMDIxNDI3IDEzMi4wMTkyMTEsMTguODIzMjcxIDE0Ni41NTY3MTcsMTkuMjU0MjcxIAoJQzE3NC4zNjY4MDYsMjAuMDc4Nzc3IDIwMS43MzY2MTgsMjQuMjMxNzU4IDIyNi41MjA5NjYsMzguMjEwNDc2IAoJQzI0Ni44ODEzMDIsNDkuNjkzOTkzIDI1OS44Mzg2NTQsNjcuMDc4MDk0IDI2NS45ODcxMjIsODkuNTcwOTM4IAoJQzI2OS42ODQ3ODQsMTAzLjA5ODEyMiAyNjkuOTIzMjc5LDExNy4wMzQzNDggMjY4Ljc1NDYwOCwxMzAuNzQ1MzQ2IAoJQzI2Ny41MTkzNzksMTQ1LjIzNzc0NyAyNjQuMjU4Nzg5LDE1OS41NTEyMDggMjYyLjA1NTc4NiwxNzMuOTY5MjM4IAoJQzI1OS45MjIzNjMsMTg3LjkzMjA2OCAyNTguMDg0MzIwLDIwMS45Mzk5MTEgMjU1Ljk3NjAxMywyMTUuOTA2NzU0IAoJQzI1NC4wODg2NTQsMjI4LjQwOTg2NiAyNTEuNzMxMzM5LDI0MC44NDgwNTMgMjUwLjEwOTYwNCwyNTMuMzgzMjA5IAoJQzI0OC45NTMxMjUsMjYyLjMyMjA4MyAyNDguNjQyMjU4LDI3MS4zNzIxMzEgMjQ4LjAyMTc0NCwyODAuMzc3MjU4IAoJQzI0Ny44NzQ5ODUsMjgyLjUwNzE0MSAyNDguMDAwMDAwLDI4NC42NTU3NjIgMjQ4LjAwMDAwMCwyODcuMTIzNjI3IAoJQzIyOS43MTc1NDUsMjg0LjA0MzY3MSAyMTEuODY0NzQ2LDI4MS4wMjgyNTkgMTk0LjAwNzg3NCwyNzguMDM3MTcwIAoJQzE5MS42MDYzNTQsMjc3LjYzNDk0OSAxODkuMTczODI4LDI3Ny40MTY4NzAgMTg2Ljc3Mzc3MywyNzcuMDA3Mzg1IAoJQzE3OS43MDExMTEsMjc1LjgwMDY5MCAxNzkuMTkzNDIwLDI3NS4yNjA0NjggMTc5LjAyMzExNywyNjcuODk2Njk4IAoJQzE3OC45MDM3OTMsMjYyLjczNzMzNSAxNzkuMDAwMDc2LDI1Ny41NzMwMjkgMTc5LjAwMDA3NiwyNTEuMjgzMTQyIAoJQzE3Ni4wMjczNTksMjUzLjQ4MjkxMCAxNzMuNzk1MzM0LDI1NS40MzQ0MzMgMTcxLjI5Mjc1NSwyNTYuOTM0NjYyIAoJQzE2MC4zMjI3ODQsMjYzLjUxMDc0MiAxNDguODk1NDkzLDI2OC42MTEwODQgMTM1Ljg5NjYyMiwyNzAuMTE2MDI4IAoJQzExOC43NDMyODYsMjcyLjEwMTkyOSAxMDIuMDE4Mzg3LDI3MS4xNDQwMTIgODUuODI0OTEzLDI2NC45ODA4MzUgCglDNjAuMzkyMzg3LDI1NS4zMDEyMzkgNDMuNTgwMTIwLDIzNy4zMDYzMDUgMzguMjYxNDU2LDIxMC42MTczODYgCglDMzMuMDI0NTkzLDE4NC4zMzg5ODkgNDAuMzg4MDAwLDE2MC40NjA4NzYgNTcuMDA2NTM1LDEzOS41MDgxNzkgCglDNzAuNTY5NTU3LDEyMi40MDc4NzUgODcuMTA5OTQ3LDEwOS41ODU2NDAgMTA4LjU2NzIzMCwxMDMuODE2OTMzIAoJQzEyMi43MjMxMTQsMTAwLjAxMTE2OSAxMzYuODIxMTM2LDk5LjQ0MDgxMSAxNTAuODk0NTkyLDEwNC4wMzA4MzggCglDMTU2LjQ4NzU2NCwxMDUuODU0OTY1IDE2MC41NDMxMDYsMTEyLjM1NjM5MiAxNjEuMDg2ODg0LDExOC4yNzE0NjkgCglDMTYyLjMxMzc1MSwxMzEuNjE2OTU5IDE1NC4zMTg3ODcsMTQwLjA4MDU1MSAxNDUuMDExMjE1LDE0Ni44MDczMjcgCglDMTI2LjMzODA2NiwxNjAuMzAyNzgwIDEwNy4xMTU2OTIsMTczLjA3NDc4MyA4Ny42ODAzMDUsMTg1LjQ1NTA5MyAKCUM4Mi4zODIyMzMsMTg4LjgyOTk0MSA4NC43MTI0ODYsMTkzLjUyMDE3MiA4My4xMzY2MjAsMTk3LjQzNDY0NyAKCUM4MS45NTY4NjMsMjAwLjM2NTE3MyA4Mi4zMjI4ODQsMjAzLjg4Mjg3NCA4MS40MzUxNTAsMjA2Ljk3Njg4MyAKCUM4MC42ODc1ODQsMjA5LjU4MjM4MiA4MS4yODg2NTgsMjEwLjYwOTIzOCA4My44MzkzODYsMjExLjAzMzgyOSAKCUM5MS40ODY5MDAsMjEyLjMwNjgyNCA5OS4xMDI5MjgsMjEzLjc3NDk2MyAxMDYuNzYxMjQ2LDIxNC45NzU1NzEgCglDMTE4LjMwMzkxNywyMTYuNzg1MTQxIDEyOS44ODA0NjMsMjE4LjM3ODAwNiAxNDEuNDI2NjgyLDIyMC4xNjU3NDEgCglDMTUxLjM1MTc5MSwyMjEuNzAyNDg0IDE2MS4yNTIxNTEsMjIzLjM5ODQ5OSAxNzEuMTY4MzA0LDIyNC45OTM4MjAgCglDMTc2LjQ2NDI2NCwyMjUuODQ1ODU2IDE4MS43NzA1MDgsMjI2LjYzMzk0MiAxODguMDQyNTI2LDIyNy42MDA0MTggCglDMTg5LjM4Nzg5NCwyMTcuMjM1MzgyIDE5MC42NjgxOTgsMjA3LjM3MTU2NyAxOTIuMDg2Nzc3LDE5Ni40NDI1MzUgCglDMTc1LjQ2MTA3NSwxOTMuMzU2MTU1IDE1OC44NjYxMTksMTkwLjI3NTQ4MiAxNDEuMTk4MjI3LDE4Ni45OTU2MzYgCglDMTQ1LjA2NDI4NSwxODQuNjc0MzYyIDE0OC4zODM5ODcsMTgyLjQxMjM4NCAxNTEuOTMyNjE3LDE4MC41OTgxNDUgCglDMTY1LjQxMDAxOSwxNzMuNzA3Nzk0IDE3Ny42NjkwNTIsMTY1LjE5OTAzNiAxODcuNjkxMzE1LDE1My43NjQwNTMgCglDMTk1LjE4Nzg4MSwxNDUuMjEwODQ2IDE5OS42ODQ4MzAsMTM1LjQ2ODQzMCAyMDEuMjQ5NTI3LDEyMy45MDcyMTkgCglDMjAyLjk1NTEwOSwxMTEuMzA0OTcwIDE5OS44ODc3MTEsMTAwLjQ2MjM2NCAxOTIuMjUzNjMyLDkwLjg5ODE4NiAKCUMxODQuMzcyMzYwLDgxLjAyNDI3NyAxNzMuNTg1Njc4LDc1LjI4NDY5OCAxNjEuMjg1ODczLDczLjEzNzYyNyAKCUMxNTEuNjY2NTUwLDcxLjQ1ODQ1OCAxNDEuODA1MTkxLDY5Ljk3MzA3NiAxMzIuMTAzMTM0LDcwLjI0NTQ1MyAKCUMxMTguMjE4MzA3LDcwLjYzNTI1NCAxMDQuNDY4OTE4LDczLjEzMTI4NyA5MS4zNTc5OTQsNzguMjA5OTQ2IAoJQzg5Ljg2MTg4NSw3OC43ODk0NzQgODguMjcwNzk4LDc5LjEyMzgwMiA4NS44NzQ0ODksNzkuODE3NzcyIAoJQzgzLjc2NDc3MSw2Mi43NjI5ODUgODEuNjcwODMwLDQ1LjgzNTY5NyA3OS40OTEyNzIsMjguMjE2MjQwIAoJQzg3LjI1ODU5MSwyNi4yMjkyMjMgOTQuNjQ4NjY2LDI0LjMzODcxMyAxMDIuNDMxMTI5LDIyLjI0MDE3MCAKeiIvPgo8L3N2Zz4=';

			$icon_data_uri = 'data:image/svg+xml;base64,' . $icon_base64;

			static::$hook_suffix = add_menu_page(
				__(A2_Optimized::PLUGIN_NAME, A2_Optimized::PLUGIN_ID),        // Page Title.
				__(A2_Optimized::PLUGIN_NAME, A2_Optimized::PLUGIN_ID),        // Menu Title.
				static::REQUIRED_CAPABILITY,           // Capability.
				static::SETTINGS_PAGE_SLUG,
				[ $this, 'markup_settings_page' ],
				$icon_data_uri,
				'3' // menu position
			);

			add_submenu_page(
				'a2-optimized',
				'Page Load Speed Score', // phpcs:ignore
				'Page Load Speed Score', // phpcs:ignore
				'manage_options',
				'admin.php?page=a2-optimized'
			);	
			add_submenu_page(
				'a2-optimized',
				'Website and Server Performance', // phpcs:ignore
				'Website and Server Performance', // phpcs:ignore
				'manage_options',
				'admin.php?page=a2-optimized&amp;a2_page=server_performance'
			);	
			add_submenu_page(
				'a2-optimized',
				'Optimization', // phpcs:ignore
				'Optimization', // phpcs:ignore
				'manage_options',
				'admin.php?page=a2-optimized&amp;a2_page=optimizations'
			);
			remove_submenu_page('a2-optimized','a2-optimized'); //Removes duplicated top level item	

			// @codingStandardsIgnoreEnd.
		}
		
		/**
		 * Highlights the current active submenu 
		 *
		 * @since    3.0.0
		 */
		function highlight_active_submenu($submenu_file){
			if (isset($_GET['page']) && $_GET['page'] == 'a2-optimized') {
				if(!isset($_GET['a2_page']) || $_GET['a2_page'] == 'page_speed_score'){
					return 'admin.php?page=a2-optimized';
				} else {
					return 'admin.php?page=a2-optimized&amp;a2_page=' . $_GET['a2_page'];
				}
			}
			return $submenu_file;
		}

		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since    3.0.0
		 */
		public function enqueue_scripts() {
			/**
			 * This function is provided for demonstration purposes only.
			 */

			wp_enqueue_script(
				A2_Optimized::PLUGIN_ID . '_vue-js',
				A2_Optimized::get_plugin_url() . 'assets/js/admin/vue.js',
				[],
				A2_Optimized::PLUGIN_VERSION,
				false
			);
			wp_enqueue_script(
				A2_Optimized::PLUGIN_ID . '_axios-js',
				A2_Optimized::get_plugin_url() . 'assets/js/admin/axios.js',
				[],
				A2_Optimized::PLUGIN_VERSION,
				true
			);
			wp_enqueue_script(
				A2_Optimized::PLUGIN_ID . '_admin-js',
				A2_Optimized::get_plugin_url() . 'assets/js/admin/a2-optimized.js',
				[ 'jquery' ],
				A2_Optimized::PLUGIN_VERSION,
				true
			);
			wp_enqueue_script(
				A2_Optimized::PLUGIN_ID . '_circles-js',
				A2_Optimized::get_plugin_url() . 'assets/js/admin/circles.min.js',
				[ 'jquery' ],
				A2_Optimized::PLUGIN_VERSION,
				true
			);
			wp_enqueue_script(
				A2_Optimized::PLUGIN_ID . '_bootstrap-js',
				A2_Optimized::get_plugin_url() . 'assets/bootstrap/js/bootstrap.js',
				[ 'jquery' ],
				A2_Optimized::PLUGIN_VERSION,
				true
			);
			wp_enqueue_script(
				A2_Optimized::PLUGIN_ID . '_chart-js',
				A2_Optimized::get_plugin_url() . 'assets/js/admin/chart.min.js',
				[ 'jquery' ],
				A2_Optimized::PLUGIN_VERSION,
				true
			);
			wp_localize_script(
				'jquery',
				'ajax',
				[
					'url' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('a2opt_ajax_nonce'),
				]
			);
		}

		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since    3.0.0
		 */
		public function enqueue_styles() {
			/**
			 * This function is provided for demonstration purposes only.
			 */

			wp_enqueue_style(
				A2_Optimized::PLUGIN_ID . '_admin-css',
				A2_Optimized::get_plugin_url() . 'assets/css/admin/a2-optimized.css',
				[],
				A2_Optimized::PLUGIN_VERSION,
				'all'
			);
			wp_enqueue_style(
				A2_Optimized::PLUGIN_ID . '_bootstrap-css',
				A2_Optimized::get_plugin_url() . 'assets/bootstrap/css/bootstrap.css',
				[],
				A2_Optimized::PLUGIN_VERSION,
				'all'
			);
			wp_enqueue_style(
				A2_Optimized::PLUGIN_ID . '_bootstraptheme-css',
				A2_Optimized::get_plugin_url() . 'assets/bootstrap/css/bootstrap-theme.css',
				[],
				A2_Optimized::PLUGIN_VERSION,
				'all'
			);
			wp_enqueue_style(
				A2_Optimized::PLUGIN_ID . '_animations-css',
				A2_Optimized::get_plugin_url() . 'assets/css/admin/animate.min.css',
				[],
				A2_Optimized::PLUGIN_VERSION,
				'all'
			);
			wp_enqueue_style(
				A2_Optimized::PLUGIN_ID . '_fonts-css',
				'https://fonts.googleapis.com/css?family=Raleway:300,500,700,900|Poppins:300,500,700,900',
				[],
				A2_Optimized::PLUGIN_VERSION,
				'all'
			);
		}

		/**
		 * Creates the markup for the Settings page
		 *
		 * @since    3.0.0
		 */
		public function markup_settings_page() {
			if (current_user_can(static::REQUIRED_CAPABILITY)) {
				if (!isset($_REQUEST['a2_page'])) {
					$page = 'page_speed_score';
				} else {
					$page = $_REQUEST['a2_page'];
				}
				$run_benchmarks = false;
				$notifications = $this->get_model()->get_notifications();
				$options = get_option('a2opt-pagespeed');
				$strategy = 'desktop';
				if (isset($options['default-strategy'])){
					$strategy = $options['default-strategy'];
				}
				switch ($page) {
					case 'server_performance':
						$graphs = $this->get_model()->get_frontend_benchmark($run_benchmarks);

						$this->view->admin_server_performance_page(
							[
								'page_title'    => A2_Optimized::PLUGIN_NAME,
								'settings_name' => $this->get_model()->get_plugin_settings_option_key(),
								'notifications' => $notifications,
								'graphs' => $graphs['pagespeed_' . $strategy],
								'default_strategy' => $strategy,
								'run_benchmarks' => $run_benchmarks,
								'status_message' => $graphs['status_message'],
							]
						);

						break;
					case 'hosting_matchup':
						$data = $this->get_model()->get_hosting_benchmark();
						$this->view->admin_hosting_matchup_page(
							[
								'page_title'    => A2_Optimized::PLUGIN_NAME,
								'settings_name' => $this->get_model()->get_plugin_settings_option_key(),
								'notifications' => $notifications,
								'data' => $data,
								'run_benchmarks' => $run_benchmarks
							]
						);
						break;
					case 'advanced_settings':
						$data = $this->get_model()->get_advanced_settings();
						$this->view->admin_advanced_settings_page(
							[
								'page_title'    => A2_Optimized::PLUGIN_NAME,
								'settings_name' => $this->get_model()->get_plugin_settings_option_key(),
								'notifications' => $notifications,
								'data' => $data,
							]
						);
						break;
					case 'optimizations':
						$data = $this->get_model()->get_opt_performance();

						$this->view->admin_opt_performance_page(
							[
								'page_title'    => A2_Optimized::PLUGIN_NAME,
								'settings_name' => $this->get_model()->get_plugin_settings_option_key(),
								'notifications' => $notifications,
								'data' => $data,
							]
						);
						break;
					case 'page_speed_score':
					default:
						$frontend_metrics = $this->get_model()->get_frontend_benchmark($run_benchmarks);
						$opt_data = $this->get_model()->get_opt_performance();

						$graphs = array_merge($frontend_metrics, $opt_data['graphs']);

						$this->view->admin_pagespeed_page(
							[
								'page_title'    => A2_Optimized::PLUGIN_NAME,
								'settings_name' => $this->get_model()->get_plugin_settings_option_key(),
								'notifications' => $notifications,
								'graphs' => $graphs,
								'run_benchmarks' => $run_benchmarks
							]
						);
						break;
				}
			} else {
				wp_die(__('Access denied.')); // WPCS: XSS OK.
			}
		}

		/**
		 * Registers settings sections and fields
		 *
		 * @since    3.0.0
		 */
		public function register_fields() {
			// Add Settings Page Section.
			add_settings_section(
				'a2_optimized_section',                    // Section ID.
				__('Settings', A2_Optimized::PLUGIN_ID), // Section Title.
				[ $this, 'markup_section_headers' ], // Section Callback.
				static::SETTINGS_PAGE_SLUG                 // Page URL.
			);

			// Add Settings Page Field.
			add_settings_field(
				'a2_optimized_field',                                // Field ID.
				__('A2 Optimized Field:', A2_Optimized::PLUGIN_ID), // Field Title.
				[ $this, 'markup_fields' ],                    // Field Callback.
				static::SETTINGS_PAGE_SLUG,                          // Page.
				'a2_optimized_section',                              // Section ID.
				[                                              // Field args.
					'id'        => 'a2_optimized_field',
					'label_for' => 'a2_optimized_field',
				]
			);
		}

		/**
		 * Adds the section introduction text to the Settings page
		 *
		 * @param array $section Array containing information Section Id, Section
		 *                       Title & Section Callback.
		 *
		 * @since    3.0.0
		 */
		public function markup_section_headers($section) {
			$this->view->section_headers(
				[
					'section'      => $section,
					'text_example' => __('This is a text example for section header', A2_Optimized::PLUGIN_ID),
				]
			);
		}

		/**
		 * Delivers the markup for settings fields
		 *
		 * @param array $field_args Field arguments passed in `add_settings_field`
		 *                          function.
		 *
		 * @since    3.0.0
		 */
		public function markup_fields($field_args) {
			$field_id = $field_args['id'];
			$settings_value = $this->get_model()->get_setting($field_id);
			$this->view->markup_fields(
				[
					'field_id'       => esc_attr($field_id),
					'settings_name'  => $this->get_model()->get_plugin_settings_option_key(),
					'settings_value' => ! empty($settings_value) ? esc_attr($settings_value) : '',
				]
			);
		}

		/**
		 * Adds links to the plugin's action link section on the Plugins page
		 *
		 * @param array $links The links currently mapped to the plugin.
		 * @return array
		 *
		 * @since    3.0.0
		 */
		public function add_plugin_action_links($links) {
			$settings_link = '<a href="options-general.php?page=' . static::SETTINGS_PAGE_SLUG . '">' . __('Settings', A2_Optimized::PLUGIN_ID) . '</a>';
			array_unshift($links, $settings_link);

			return $links;
		}
	}
}
