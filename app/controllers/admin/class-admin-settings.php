<?php

namespace A2_Optimized\App\Controllers\Admin;

use A2_Optimized\App\Controllers\Admin\Base_Controller;
use A2_Optimized as A2_Optimized;

if (! class_exists(__NAMESPACE__ . '\\' . 'Admin_Settings')) {
	/**
	 * Controller class that implements Plugin Admin Settings configurations
	 *
	 * @since      1.0.0
	 * @package    A2_Optimized
	 * @subpackage A2_Optimized/controllers/admin
	 */
	class Admin_Settings extends Base_Controller {
		/**
		 * Holds suffix for dynamic add_action called on settings page.
		 *
		 * @var string
		 * @since 1.0.0
		 */
		private static $hook_suffix = 'settings_page_' . A2_Optimized::PLUGIN_ID;

		/**
		 * Slug of the Settings Page
		 *
		 * @since    1.0.0
		 */
		public const SETTINGS_PAGE_SLUG = A2_Optimized::PLUGIN_ID;

		/**
		 * Capability required to access settings page
		 *
		 * @since 1.0.0
		 */
		public const REQUIRED_CAPABILITY = 'manage_options';

		/**
		 * Register callbacks for actions and filters
		 *
		 * @since    1.0.0
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
		}

		/**
		 * Create menu for Plugin inside Settings menu
		 *
		 * @since    1.0.0
		 */
		public function plugin_menu() {
			// @codingStandardsIgnoreStart.
			static::$hook_suffix = add_options_page(
				__(A2_Optimized::PLUGIN_NAME, A2_Optimized::PLUGIN_ID),        // Page Title.
				__(A2_Optimized::PLUGIN_NAME, A2_Optimized::PLUGIN_ID),        // Menu Title.
				static::REQUIRED_CAPABILITY,           // Capability.
				static::SETTINGS_PAGE_SLUG,             // Menu URL.
				[ $this, 'markup_settings_page' ] // Callback.
			);
			// @codingStandardsIgnoreEnd.
		}

		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts() {
			/**
			 * This function is provided for demonstration purposes only.
			 */

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
		}

		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since    1.0.0
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
		}

		/**
		 * Creates the markup for the Settings page
		 *
		 * @since    1.0.0
		 */
		public function markup_settings_page() {
			if (current_user_can(static::REQUIRED_CAPABILITY)) {
				if (!isset($_REQUEST['a2_page'])){
					$page = 'page_speed_score';
				}
				else {
					$page = $_REQUEST['a2_page'];
				}
				$run_benchmarks = false;
				switch ($page) {
					case 'server_performance':
						$graphs = $this->get_model()->get_frontend_benchmark($run_benchmarks);
						$this->view->admin_server_performance_page(
							[
								'page_title'    => A2_Optimized::PLUGIN_NAME,
								'settings_name' => $this->get_model()->get_plugin_settings_option_key(),
								'graphs' => $graphs['pagespeed_desktop'],
								'run_benchmarks' => $run_benchmarks
							]
						);

						break;

					case 'page_speed_score':
					default:
						$frontend_metrics = $this->get_model()->get_frontend_benchmark($run_benchmarks);
						$opt_data = $this->get_model()->get_optimization_benchmark();

						$graphs = array_merge($frontend_metrics, $opt_data);

						$this->view->admin_pagespeed_page(
							[
								'page_title'    => A2_Optimized::PLUGIN_NAME,
								'settings_name' => $this->get_model()->get_plugin_settings_option_key(),
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
		 * @since    1.0.0
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
		 * @since    1.0.0
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
		 * @since    1.0.0
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
		 * @since    1.0.0
		 */
		public function add_plugin_action_links($links) {
			$settings_link = '<a href="options-general.php?page=' . static::SETTINGS_PAGE_SLUG . '">' . __('Settings', A2_Optimized::PLUGIN_ID) . '</a>';
			array_unshift($links, $settings_link);

			return $links;
		}
	}
}
