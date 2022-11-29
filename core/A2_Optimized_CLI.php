<?php
/**
 * Interact with A2 Optimized
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (defined( 'WP_CLI' ) && WP_CLI && class_exists( 'WP_CLI' )) {
	WP_CLI::add_command( 'a2-optimized', 'A2_Optimized_CLI' );
}

class A2_Optimized_CLI {
	/**
	 * Clear the page cache.
	 *
	 * ## OPTIONS
	 *
	 * [--ids=<id>]
	 * : Clear the cache for given post ID(s). Separate multiple IDs with commas.
	 *
	 * [--urls=<url>]
	 * : Clear the cache for the given URL(s). Separate multiple URLs with commas.
	 *
	 * [--sites=<site>]
	 * : Clear the cache for the given blog ID(s). Separate multiple blog IDs with commas.
	 *
	 * ## EXAMPLES
	 *
	 *    # Clear all pages cache.
	 *    $ wp a2-optimized clear
	 *    Success: Site cache cleared.
	 *
	 *    # Clear the page cache for post IDs 1, 2, and 3.
	 *    $ wp a2-optimized clear --ids=1,2,3
	 *    Success: Pages cache cleared.
	 *
	 *    # Clear the page cache for a particular URL.
	 *    $ wp a2-optimized clear --urls=https://www.example.com/about-us/
	 *    Success: Page cache cleared.
	 *
	 *    # Clear all pages cache for sites with blog IDs 1, 2, and 3.
	 *    $ wp a2-optimized clear --sites=1,2,3
	 *    Success: Sites cache cleared.
	 *
	 * @alias clear
	 */

	public function clear($args, $assoc_args) {
		$assoc_args = wp_parse_args(
			$assoc_args,
			[
				'ids' => '',
				'urls' => '',
				'sites' => '',
			]
		);

		if ( empty( $assoc_args['ids'] ) && empty( $assoc_args['urls'] ) && empty( $assoc_args['sites'] ) ) {
			A2_Optimized_Cache::clear_complete_cache();

			return WP_CLI::success( ( is_multisite() ) ? esc_html__( 'Network cache cleared.', 'a2-optimized-wp' ) : esc_html__( 'Site cache cleared.', 'a2-optimized-wp' ) );
		}

		if ( ! empty( $assoc_args['ids'] ) || ! empty( $assoc_args['urls'] ) ) {
			array_map( 'A2_Optimized_Cache::clear_page_cache_by_post_id', explode( ',', $assoc_args['ids'] ) );
			array_map( 'A2_Optimized_Cache::clear_page_cache_by_url', explode( ',', $assoc_args['urls'] ) );

			$separators = substr_count( $assoc_args['ids'], ',' ) + substr_count( $assoc_args['urls'], ',' );

			if ( $separators > 0 ) {
				return WP_CLI::success( esc_html__( 'Pages cache cleared.', 'a2-optimized-wp' ) );
			} else {
				return WP_CLI::success( esc_html__( 'Page cache cleared.', 'a2-optimized-wp' ) );
			}
		}

		if ( ! empty( $assoc_args['sites'] ) ) {
			array_map( 'A2_Optimized_Cache::clear_site_cache_by_blog_id', explode( ',', $assoc_args['sites'] ) );

			$separators = substr_count( $assoc_args['sites'], ',' );

			if ( $separators > 0 ) {
				return WP_CLI::success( esc_html__( 'Sites cache cleared.', 'a2-optimized-wp' ) );
			} else {
				return WP_CLI::success( esc_html__( 'Site cache cleared.', 'a2-optimized-wp' ) );
			}
		}
	}

	/**
	 * Enables various parts of the A2 Optimized plugin
	 *
	 * ## OPTIONS
	 *
	 * [module]
	 * Enable the specified module:
	 * - page_cache - Page Caching
	 * - object_cache - Object Caching
	 * - gzip - GZip compression
	 * - html_min - HTML Minification
	 * - cssjs_min - CSS/JS Minification
	 * - xmlrpc - Block XML-RPC requests
	 * - htaccess - Deny access to .htaccess
	 * - lock_plugins - Block editing of Plugins and Themes
	 *
	 * ## EXAMPLES
	 *
	 *    # Enable Page Caching
	 *    $ wp a2-optimized enable page_cache
	 *    Success: Site Page Cache enabled.
	 *
	 */

	public function enable($args, $assoc_args) {
		$optimizations = new A2_Optimized_Optimizations;
		$to_enable = $args[0];

		$site_type = 'Site';
		if (is_multisite()) {
			$site_type = 'Network';
		}

		switch ($to_enable) {
			case 'page_cache':
				$optimizations->enable_a2_page_cache();

				return WP_CLI::success(esc_html__( $site_type . ' Page Cache enabled.', 'a2-optimized-wp' ));

				break;
			case 'object_cache':
				$optimizations->enable_a2_object_cache();

				return WP_CLI::success(esc_html__( $site_type . ' Object Cache enabled.', 'a2-optimized-wp' ));

				break;
			case 'gzip':
				$optimizations->enable_a2_page_cache_gzip();

				return WP_CLI::success(esc_html__( $site_type . ' GZIP enabled.', 'a2-optimized-wp' ));

				break;
			case 'html_min':
				$optimizations->enable_a2_page_cache_minify_html();

				return WP_CLI::success(esc_html__( $site_type . ' HTML Minify enabled.', 'a2-optimized-wp' ));

				break;
			case 'cssjs_min':
				$optimizations->enable_a2_page_cache_minify_jscss();

				return WP_CLI::success(esc_html__( $site_type . ' JS/CSS Minify enabled.', 'a2-optimized-wp' ));

				break;
			case 'xmlrpc':
				$optimizations->enable_xmlrpc_requests();

				return WP_CLI::success(esc_html__( $site_type . ' XML-RPC Request Blocking enabled.', 'a2-optimized-wp' ));

				break;
			case 'htaccess':
				$optimizations->set_deny_direct(true);
				$optimizations->write_htaccess();

				return WP_CLI::success(esc_html__( $site_type . ' Deny Direct Access to .htaccess enabled.', 'a2-optimized-wp' ));

				break;
			case 'lock_plugins':
				$optimizations->set_lockdown(true);
				$optimizations->write_wp_config();

				return WP_CLI::success(esc_html__( $site_type . ' Lock editing of Plugins and Themes enabled.', 'a2-optimized-wp' ));

				break;
		}
	}

	/**
	 * Disables various parts of the A2 Optimized plugin
	 *
	 * ## OPTIONS
	 *
	 * [module]
	 * Disable the specified module:
	 * - page_cache - Page Caching
	 * - object_cache - Object Caching
	 * - gzip - GZip compression
	 * - html_min - HTML Minification
	 * - cssjs_min - CSS/JS Minification
	 * - xmlrpc - Block XML-RPC requests
	 * - htaccess - Deny access to .htaccess
	 * - lock_plugins - Block editing of Plugins and Themes
	 *
	 * ## EXAMPLES
	 *
	 *    # Disable Page Caching
	 *    $ wp a2-optimized disable page_cache
	 *    Success: Site Page Cache disabled.
	 *
	 */

	public function disable($args, $assoc_args) {
		$optimizations = new A2_Optimized_Optimizations;

		$to_disable = $args[0];

		$site_type = 'Site';
		if (is_multisite()) {
			$site_type = 'Network';
		}
		switch ($to_disable) {
			case 'page_cache':
				$optimizations->disable_a2_page_cache();

				return WP_CLI::success(esc_html__( $site_type . ' Page Cache disabled.', 'a2-optimized-wp' ));

				break;
			case 'object_cache':
				$optimizations->disable_a2_object_cache();

				return WP_CLI::success(esc_html__( $site_type . ' Object Cache disabled.', 'a2-optimized-wp' ));

				break;
			case 'gzip':
				$optimizations->disable_a2_page_cache_gzip();

				return WP_CLI::success(esc_html__( $site_type . ' GZIP disabled.', 'a2-optimized-wp' ));

				break;
			case 'html_min':
				$optimizations->disable_a2_page_cache_minify_html();

				return WP_CLI::success(esc_html__( $site_type . ' HTML Minify disabled.', 'a2-optimized-wp' ));

				break;
			case 'cssjs_min':
				$optimizations->disable_a2_page_cache_minify_jscss();

				return WP_CLI::success(esc_html__( $site_type . ' JS/CSS Minify disabled.', 'a2-optimized-wp' ));

				break;
			case 'xmlrpc':
				$optimizations->disable_xmlrpc_requests();

				return WP_CLI::success(esc_html__( $site_type . ' XML-RPC Request Blocking disabled.', 'a2-optimized-wp' ));

				break;
			case 'htaccess':
				$optimizations->set_deny_direct(false);
				$optimizations->write_htaccess();

				return WP_CLI::success(esc_html__( $site_type . ' Deny Direct Access to .htaccess disabled.', 'a2-optimized-wp' ));

				break;
			case 'lock_plugins':
				$optimizations->set_lockdown(false);
				$optimizations->write_wp_config();

				return WP_CLI::success(esc_html__( $site_type . ' Lock editing of Plugins and Themes disabled.', 'a2-optimized-wp' ));

				break;
		}
	}

	public function memcached_server($args, $assoc_args) {
		$server_address = $args[0];

		$site_type = 'Site';
		if (is_multisite()) {
			$site_type = 'Network';
		}

		update_option('a2_optimized_memcached_server', $server_address);

		$optimizations = new A2_Optimized_Optimizations;
		$optimizations->write_wp_config();

		return WP_CLI::success(esc_html__( $site_type . ' Memcached server updated.', 'a2-optimized-wp' ));
	}

	/**
	 * Returns a site health report for the current site
	 */
	public function site_health($args, $assoc_args) {
		if ( ! class_exists( 'WP_Debug_Data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-debug-data.php';
		}

		$output_filename = ABSPATH . 'a2-opt-sitehealth-' . date('Y-m-d_H-i-s') . '.txt';

		if ($fh = fopen($output_filename, 'w+')) {
			$WP_Debug_Data = new WP_Debug_Data();
			new A2_Optimized_SiteHealth;
			$info = $WP_Debug_Data::debug_data();
			$current_site_health = $WP_Debug_Data::format( $info, 'debug' );

			fwrite($fh, $current_site_health);
			fclose($fh);

			echo 'Saved site health report to ' . $output_filename . "\n\r";
		} else {
			echo "Error writing report file, check file permissions\n\r";
		}
	}

	/**
	 * Returns a site health report for the current site
	 */
	public function send_report_data($args, $assoc_args) {

		$a2opt_sitedata = new A2_Optimized_SiteData();

		$a2opt_sitedata->send_sitedata();
			
		echo "Sent site data report\n\r";

	}

	/**
	 * This command is for pruning the number of benchmark results in the WP options.
	 * Interfaces with A2_Optimized_Benchmark class
	 *
	 * ## OPTIONS
	 *
	 * frontend|backend [which set of results to prune]
	 * items: Number of items to keep (defaults to 25)
	 *
	 * ## EXAMPLES
	 *
	 *    # Prune backend results to default (25)
	 *    $ wp a2-optimized prune backend
	 *
	 *    # Prune frontend results to 10
	 *    $ wp a2-optimized prune frontend 10
	 */
	public function prune($args, $assoc_args) {
		$a2opt_benchmark = new A2_Optimized_Benchmark();

		$benchmark_type = empty($args[0]) ? null : $args[0]; // frontend / backend

		if (empty($benchmark_type)) {
			echo "Usage: wp a2-optimized prune type [items_to_keep]\n\r";
			echo "\ttype: backend or frontend\n\r";
			echo "\titems_to_keep (optional): number of records to keep (an integer value between 0 and 25)\n\r";
			echo "\texamples:\n\r";
			echo "\twp a2-optimized prune backend\n\r";
			echo "\twp a2-optimized prune frontend 10\n\r";
		}

		if (!$benchmark_type || ($benchmark_type != 'frontend' && $benchmark_type != 'backend')) {
			echo "Please specify a benchmark to prune (frontend or backend)\n\r";
			echo "wp a2-optimized prune [items_to_keep]\n\r";

			return;
		}

		$items_to_keep = 25;
		if (count($args) > 1) {
			$items_to_keep = $args[1];
			if (!is_numeric($items_to_keep) || $items_to_keep < 0 || $items_to_keep > 25) {
				echo "Please enter an integer value between 0 and 25 for the number of records to keep.\n\r";

				return;
			}
			$items_to_keep = intval($args[1]);
		}

		echo 'Pruning records for ' . $benchmark_type . "\r\n";
		$a2opt_benchmark->prune_benchmarks($benchmark_type, $items_to_keep);

		$post_prune_benchmarks = [];
		if ($benchmark_type == 'frontend') {
			$post_prune_benchmarks = get_option('a2opt-benchmarks-frontend');
		} elseif ($benchmark_type == 'backend') {
			$post_prune_benchmarks = get_option('a2opt-benchmarks-hosting');
		}
		echo 'After pruning, there are ' . count($post_prune_benchmarks) . ' ' . $benchmark_type . " records\r\n";
	}

	/**
	 * Interfaces with A2_Optimized_Benchmark class
	 *
	 * ## OPTIONS
	 *
	 * run frontend|backend [frontend strategy]
	 * Run a benchmark module:
	 * - frontend - Google Pagespeed Insights, also accepts a strategy arguement, defaults to desktop
	 * - backend - Hosting account benchmarks, PHP / MySQL / WordPress
	 *
	 * view frontend|backend ['specific report']
	 * Run a benchmark module:
	 * - frontend - Google Pagespeed Inights, leaving report empty will return a list of saved results
	 * - backend - Hosting account benchmarks
	 *
	 * --test_runs: Multiplier for number of executions on the PHP benchmarks
	 * --json: If this argument is present, output the results in JSON format
	 *
	 * ## EXAMPLES
	 *
	 *    # Run backend tests
	 *    $ wp a2-optimized benchmarks run backend
	 *
	 *    # Run frontend tests for mobile
	 *    $ wp a2-optimized benchmarks run frontend mobile
	 *
	 *    # View list of frontend results
	 *    $ wp a2-optimized benchmarks view frontend
	 *
	 *    # View specific frontend result (enclose date in single quotes)
	 *    $ wp a2-optimized benchmarks view frontend '2022-04-11 10:37:15'
	 *
	 *    # Run backend PHP tests only, and output results as json
	 *    $ wp a2-optimized benchmarks run backend php --json
	 */
	public function benchmarks($args, $assoc_args) {
		$a2opt_benchmark = new A2_Optimized_Benchmark();

		$sub_command = empty($args[0]) ? null : $args[0]; // run/view
		$benchmark_type = empty($args[1]) ? null : $args[1]; // frontend / backend
		$selected_benchmark = empty($args[2]) ? null : $args[2]; // latest or date

		if (empty($sub_command) || empty($benchmark_type)) {
			echo "Usage: wp a2-optimized benchmarks action type [frontend_type|report]\n\r";
			echo "\taction: run or view\n\r";
			echo "\ttype: backend or frontend\n\r";
			echo "\tfrontend_type|report (optional): desktop or mobile (for frontend_type)|latest or report name (for report)\n\r";
			echo "\texample:\n\r";
			echo "\twp a2-optimized benchmarks run backend\n\r";
		}

		$selected_benchmark = empty($args[2]) ? null : $args[2]; // frontend: latest or date; backend: php mysql wordpress filesystem

		$test_runs_multiplier = 1.0;
		if (array_key_exists('test_runs', $assoc_args)) {
			$multiplier = $assoc_args['test_runs'];
			if (!is_numeric($multiplier)) {
				echo "Please enter a floating-point value between 0.1 and 10.0 for the test runs multiplier.\n\r";

				return;
			}
			$test_runs_multiplier = floatval($assoc_args['test_runs']);
		}

		$output_json = (array_key_exists('format', $assoc_args) && $assoc_args['format'] == 'json'); // output the results as JSON

		if (empty($sub_command) || empty($benchmark_type)) {
			echo "Usage: wp a2-optimized benchmarks action type [frontend_type|report] [--test_runs=MULTIPLIER] [--json]\n\r";
			echo "\taction: run or view\n\r";
			echo "\ttype: backend or frontend\n\r";
			echo "\tbenchmark_type|report (optional): desktop or mobile (for frontend_type)|php, mysql, wordpress, or filesystem (for backend_type)|latest or report name (for report)\n\r";
			echo "\rtest_runs: multiplier for number of times to execute backend PHP tests (1.0=default executions)\n\r";
			echo "\rjson: if present, this argument instructs the report to output the results in JSON format";
			echo "\texamples:\n\r";
			echo "\twp a2-optimized benchmarks run backend\n\r";
			echo "\twp a2-optimized benchmarks run backend php --test_runs=2.0\n\r";
			echo "\twp a2-optimized benchmarks run backend --json\n\r";
			echo "\twp a2-optimized benchmarks view backend latest\n\r";
			echo "\twp a2-optimized benchmarks view frontend '2022-04-11 10:37:15'\n\r";
		}

		if ($sub_command == 'run') {
			if (!$benchmark_type) {
				echo "Please specify a benchmark to run\n\r";
				echo "wp a2-optimized benchmark run frontend|backend\n\r";

				return;
			}

			if ($benchmark_type == 'frontend') {
				if (!$output_json) {
					echo "Running frontend benchmarks. This may take a couple of minutes\n\r";
				}
				if (!$selected_benchmark || !in_array($selected_benchmark, ['desktop', 'mobile'])) {
					$selected_benchmark = 'desktop';
				}

				$result = $a2opt_benchmark->get_lighthouse_results($selected_benchmark);

				if (!$output_json) {
					echo "Benchmarking complete\n\r";
					echo $result['message'] . "\n\r";
				}
				if ($result['status'] == 'success') {
					$frontend_benchmarks = get_option('a2opt-benchmarks-frontend');
					$frontend_benchmarks_last = array_pop($frontend_benchmarks);
					if ($output_json) {
						echo json_encode($frontend_benchmarks_last);
					} else {
						echo $this->format_frontend_results($frontend_benchmarks_last);
					}
				}
			}

			if ($benchmark_type == 'backend') {
				if ($selected_benchmark) {
					$result = false;
					switch ($selected_benchmark) {
						case 'php':
							if (!$output_json) {
								echo "Running PHP benchmarks. Please wait a moment.\r\n";
							}
							$result = $a2opt_benchmark->run_php_benchmarks($test_runs_multiplier);
							$result = $result['total'];

							break;
						case 'mysql':
							if (!$output_json) {
								echo "Running MySQL benchmarks. Please wait a moment.\r\n";
							}
							$result = $a2opt_benchmark->run_mysql_benchmarks();
							$result = $result['benchmark']['mysql_total'];

							break;
						case 'wordpress':
							if (!$output_json) {
								echo "Running Wordpress benchmarks. Please wait a moment.\r\n";
							}
							$result = $a2opt_benchmark->run_wordpress_benchmarks();
							$result = $result['queries_per_second'];

							break;
						case 'filesystem':
							if (!$output_json) {
								echo "Running filesystem benchmarks. Please wait a moment.\r\n";
							}
							$result = $a2opt_benchmark->run_filesystem_benchmarks();

							break;
						default:
							echo "Please supply one of the following benchmarks: php mysql wordpress filesystem\n\r";
					}

					if ($result) {
						if (!$output_json) {
							echo "Benchmarking complete\n\r";
						}
						if ($output_json) {
							echo json_encode($result);
						} else {
							echo 'Result: ' . $result . "\n\r";
						}
					}
				} else {
					if (!$output_json) {
						echo "Running backend benchmarks. This may take a couple of minutes\n\r";
					}
					$result = $a2opt_benchmark->run_hosting_test_suite();

					if (!$output_json) {
						echo "Benchmarking complete\n\r";
						echo $result['message'] . "\n\r";
					}
					if ($result['status'] == 'success') {
						if ($output_json) {
							echo json_encode($result['data']);
						} else {
							echo $this->format_backend_results($result['data']);
						}
					}
				}
			}
		}

		if ($sub_command == 'view') {
			if ($benchmark_type == 'frontend') {
				$benchmarks = get_option('a2opt-benchmarks-frontend');
				if (!$selected_benchmark) {
					if (!$output_json) {
						echo "\n\r";
						echo "Past Frontend Results\n\r";
						echo "=====================\n\r";
					}
					if (empty($benchmarks)) {
						echo "No results found\n\r";
					} else {
						if ($output_json) {
							echo json_encode($benchmarks);
						} else {
							foreach ($benchmarks as $benchmark_date => $item) {
								echo ' * ' . $benchmark_date . ' - ' . $item['strategy'] . "\n\r";
							}
						}
					}
				} else {
					$found_benchmark = false;
					if ($selected_benchmark == 'latest') {
						$benchmark = array_pop($benchmarks);
						$found_benchmark = true;
					} else {
						if (!array_key_exists($selected_benchmark, $benchmarks)) {
							echo "\n\rCould not find requested benchmark\n\r";
						} else {
							$benchmark = $benchmarks[$selected_benchmark];
							$found_benchmark = true;
						}
					}
					if ($found_benchmark) {
						if ($output_json) {
							echo json_encode($benchmark);
						} else {
							echo $this->format_frontend_results($benchmark);
						}
					}
				}
				echo "\n\r";
			}
			if ($benchmark_type == 'backend') {
				$benchmarks = get_option('a2opt-benchmarks-hosting');
				if (!$selected_benchmark) {
					if (!$output_json) {
						echo "\n\r";
						echo "Past Backend Results\n\r";
						echo "====================\n\r";
					}
					if (empty($benchmarks)) {
						echo "No results found\n\r";
					} else {
						if ($output_json) {
							echo json_encode($benchmarks);
						} else {
							foreach ($benchmarks as $benchmark_date => $item) {
								echo ' * ' . $benchmark_date . "\n\r";
							}
						}
					}
				} else {
					$found_benchmark = false;
					if ($selected_benchmark == 'latest') {
						$benchmark = array_pop($benchmarks);
						$found_benchmark = true;
					} else {
						if (!array_key_exists($selected_benchmark, $benchmarks)) {
							echo "\n\rCould not find requested benchmark\n\r";
						} else {
							$benchmark = $benchmarks[$selected_benchmark];
							$found_benchmark = true;
						}
					}
					if ($found_benchmark) {
						if ($output_json) {
							echo json_encode($benchmark);
						} else {
							echo $this->format_backend_results($benchmark);
						}
					}
				}
				echo "\n\r";
			}
		}

		$a2opt_benchmark->prune_benchmarks('frontend');
		$a2opt_benchmark->prune_benchmarks('backend');
	}

	private function format_frontend_results($data) {
		$output = "\n\rFrontend Results\n\r";
		$output .= "==========================\n\r";
		$output .= 'Device: ' . $data['strategy'] . "\n\r";
		$output .= 'Overall Score: ' . $data['scores']['overall_score'] . "\n\r";
		$output .= 'FCP: ' . $data['scores']['fcp'] . "\n\r";
		$output .= 'TTFB: ' . $data['scores']['ttfb'] . "\n\r";
		$output .= 'LCP: ' . $data['scores']['lcp'] . "\n\r";
		$output .= 'FID: ' . $data['scores']['fid'] . "\n\r";
		$output .= 'CLS: ' . $data['scores']['cls'] . "\n\r";

		return $output;
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

	/*
	 * Optimization Recommendations
	 *
	 * ## OPTIONS
	 *
	 * [action]
	 * Performs the specified action:
	 * - show - Display what optimizations should be applied
	 * - save - Saves the current optimization state
	 * - apply - Applies the recommended optimizations. Also saves the current state.
	 * - restore - Restores the optimizations from the previously saved state
	 *
	 * ## EXAMPLES
	 *
	 *    # Show recommended optimizations
	 *    $ wp a2-optimized recommendations show
	 *
	 */

	public function recommendations($args, $assoc_args) {
		if(file_exists('/opt/a2-optimized/wordpress/class.A2_Optimized_Private_Optimizations_v3.php')){
            require_once('/opt/a2-optimized/wordpress/class.A2_Optimized_Private_Optimizations_v3.php');
            $private_opts = new A2_Optimized_Private_Optimizations;

			$action = $args[0];

			$output = $private_opts->apply_recommendation($action);

        } else {
			$output = "Not available";
		}
		
		echo $output;
	}
}
