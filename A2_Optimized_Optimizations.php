<?php

/*
	Author: Benjamin Cool
	Author URI: https://www.a2hosting.com/
	License: GPLv2 or Later
*/

// Prevent direct access to this file
if ( ! defined( 'WPINC' ) )  die;

require_once 'A2_Optimized_Server_Info.php';

class A2_Optimized_Optimizations {
	private $thisclass;
	public $server_info;

	public function __construct($thisclass) {
		$this->thisclass = $thisclass;
		$w3tc = $thisclass->get_w3tc_config();
		$this->check_server_gzip();
		$this->server_info = new A2_Optimized_Server_Info($w3tc);
	}

	/**
	 * Checks if gzip test has been run to see if server is serving gzip, if not we run it.
	 * Expires after one week to reduce number of curl calls to server
	 */
	public function check_server_gzip() {
		$checked_gzip = get_transient('a2_checked_gzip');
		if (false === $checked_gzip) {
			$w3tc = $this->thisclass->get_w3tc_config();
			$previous_setting = $w3tc['browsercache.html.compression'];
			$this->thisclass->disable_w3tc_gzip();
			if ($previous_setting && (!$this->server_info->gzip || !$this->server_info->cf || !$this->server_info->br)) {
				$this->thisclass->enable_w3tc_gzip();
			}
			set_transient('a2_checked_gzip', true, WEEK_IN_SECONDS);
		}
	}

	public function get_optimizations() {
		$public_opts = $this->get_public_optimizations();
		$private_opts = $this->get_private_optimizations();

		return array_merge($public_opts, $private_opts);
	}

	protected function get_public_optimizations() {
		$thisclass = $this->thisclass;
		$thisclass->server_info = $this->server_info;

		return array(
			'page_cache' => array(
				'slug' => 'page_cache',
				'name' => 'Page Caching with W3 Total Cache',
				'plugin' => 'W3 Total Cache',
				'configured' => false,
				'description' => 'Utilize W3 Total Cache to make the site faster by caching pages as static content.  Cache: a copy of rendered dynamic pages will be saved by the server so that the next user does not need to wait for the server to generate another copy.',
				'is_configured' => function (&$item) use (&$thisclass) {
					$w3tc = $thisclass->get_w3tc_config();
					if ($w3tc['pgcache.enabled']) {
						$item['configured'] = true;
						$permalink_structure = get_option('permalink_structure');
						$vars = array();
						if ($w3tc['pgcache.engine'] == 'apc') {
							if ($permalink_structure == '') {
								$vars['pgcache.engine'] = 'file';
							} else {
								$vars['pgcache.engine'] = 'file_generic';
							}
						} else {
							if ($permalink_structure == '' && $w3tc['pgcache.engine'] != 'file') {
								$vars['pgcache.engine'] = 'file';
							} elseif ($permalink_structure != '' && $w3tc['pgcache.engine'] == 'file') {
								$vars['pgcache.engine'] = 'file_generic';
							}
						}

						if (count($vars) != 0) {
							$thisclass->update_w3tc($vars);
						}

						$thisclass->set_install_status('page_cache', true);
					} else {
						$thisclass->set_install_status('page_cache', false);
					}
				},
				'kb' => 'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-w3-total-cache-and-gtmetrix',
				'disable' => function () use (&$thisclass) {
					$thisclass->disable_w3tc_page_cache();
				},
				'enable' => function () use (&$thisclass) {
					$thisclass->enable_w3tc_page_cache();
				}
			),
			'db_cache' => array(
				'slug' => 'db_cache',
				'name' => 'DB Caching with W3 Total Cache',
				'plugin' => 'W3 Total Cache',
				'configured' => false,
				'description' => 'Speed up the site by storing the responses of common database queries in a cache.',
				'is_configured' => function (&$item) use (&$thisclass) {
					$w3tc = $thisclass->get_w3tc_config();
					if ($w3tc['dbcache.enabled']) {
						$vars = array();
						$item['configured'] = true;
						if (class_exists('W3_Config')) {
							if (class_exists('WooCommerce')) {
								if (array_search('_wc_session_', $w3tc['dbcache.reject.sql']) === false) {
									$vars['dbcache.reject.sql'] = $w3tc['dbcache.reject.sql'];
									$vars['dbcache.reject.sql'][] = '_wc_session_';
								}
							}
						}
						if (count($vars) != 0) {
							$thisclass->update_w3tc($vars);
						}

						$thisclass->set_install_status('db_cache', true);
					} else {
						$thisclass->set_install_status('db_cache', false);
					}
				},
				'kb' => 'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-w3-total-cache-and-gtmetrix',
				'disable' => function () use (&$thisclass) {
					$thisclass->disable_w3tc_db_cache();
				},
				'enable' => function () use (&$thisclass) {
					$thisclass->enable_w3tc_db_cache();
				}
			),

			'object_cache' => array(
				'slug' => 'object_cache',
				'name' => 'Object Caching with W3 Total Cache',
				'plugin' => 'W3 Total Cache',
				'configured' => false,
				'description' => 'Store a copy of widgets and menu bars in cache to reduce the time it takes to render pages.',
				'is_configured' => function (&$item) use (&$thisclass) {
					$w3tc = $thisclass->get_w3tc_config();
					if ($w3tc['objectcache.enabled']) {
						$item['configured'] = true;
						$thisclass->set_install_status('object_cache', true);
					} else {
						$thisclass->set_install_status('object_cache', false);
					}
				},
				'kb' => 'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-w3-total-cache-and-gtmetrix',
				'disable' => function () use (&$thisclass) {
					$thisclass->disable_w3tc_object_cache();
				},
				'enable' => function () use (&$thisclass) {
					$thisclass->enable_w3tc_object_cache();
				}
			),

			'browser_cache' => array(
				'slug' => 'browser_cache',
				'name' => 'Browser Caching with W3 Total Cache',
				'plugin' => 'W3 Total Cache',
				'configured' => false,
				'description' => 'Add Rules to the web server to tell the visitor&apos;s browser to store a copy of static files to reduce the load time pages requested after the first page is loaded.',
				'is_configured' => function (&$item) use (&$thisclass) {
					$w3tc = $thisclass->get_w3tc_config();
					if ($w3tc['browsercache.enabled']) {
						$item['configured'] = true;
						$thisclass->set_install_status('browser_cache', true);
					} else {
						$thisclass->set_install_status('browser_cache', false);
					}
				},
				'kb' => 'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-w3-total-cache-and-gtmetrix',
				'disable' => function () use (&$thisclass) {
					$thisclass->disable_w3tc_browser_cache();
				},
				'enable' => function () use (&$thisclass) {
					$thisclass->enable_w3tc_browser_cache();
				}
			),

			'minify' => array(
				'name' => 'Minify HTML Pages',
				'slug' => 'minify',
				'plugin' => 'W3 Total Cache',
				'configured' => false,
				'kb' => 'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-w3-total-cache-and-gtmetrix',
				'description' => 'Removes extra spaces,tabs and line breaks in the HTML to reduce the size of the files sent to the user.',
				'is_configured' => function (&$item) use (&$thisclass) {
					$w3tc = $thisclass->get_w3tc_config();
					if ($w3tc['minify.enabled'] && $w3tc['minify.html.enable']) {
						$item['configured'] = true;
						$thisclass->set_install_status('minify-html', true);
					} else {
						$thisclass->set_install_status('minify-html', false);
					}
				},
				'enable' => function () use (&$thisclass) {
					$thisclass->enable_html_minify();
				},
				'disable' => function () use (&$thisclass) {
					$thisclass->disable_html_minify();
				}
			),
			'css_minify' => array(
				'name' => 'Minify CSS Files',
				'slug' => 'css_minify',
				'plugin' => 'W3 Total Cache',
				'configured' => false,
				'kb' => 'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-w3-total-cache-and-gtmetrix',
				'description' => 'Makes your site faster by condensing css files into a single downloadable file and by removing extra space in CSS files to make them smaller.',
				'is_configured' => function (&$item) use (&$thisclass) {
					$w3tc = $thisclass->get_w3tc_config();
					if ($w3tc['minify.css.enable']) {
						$item['configured'] = true;
						$thisclass->set_install_status('minify-css', true);
					} else {
						$thisclass->set_install_status('minify-css', false);
					}
				},
				'enable' => function () use (&$thisclass) {
					$thisclass->update_w3tc(array(
						'minify.css.enable' => true,
						'minify.enabled' => true,
						'minify.auto' => 0,
						'minify.engine' => 'file'
					));
				},
				'disable' => function () use (&$thisclass) {
					$thisclass->update_w3tc(array(
						'minify.css.enable' => false,
						'minify.auto' => 0
					));
				}
			),
			'js_minify' => array(
				'name' => 'Minify JS Files',
				'slug' => 'js_minify',
				'plugin' => 'W3 Total Cache',
				'configured' => false,
				'kb' => 'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-w3-total-cache-and-gtmetrix',
				'description' => 'Makes your site faster by condensing JavaScript files into a single downloadable file and by removing extra space in JavaScript files to make them smaller.',
				'is_configured' => function (&$item) use (&$thisclass) {
					$w3tc = $thisclass->get_w3tc_config();
					if ($w3tc['minify.js.enable']) {
						$item['configured'] = true;
						$thisclass->set_install_status('minify-js', true);
					} else {
						$thisclass->set_install_status('minify-js', false);
					}
				},
				'enable' => function () use (&$thisclass) {
					$thisclass->update_w3tc(array(
						'minify.js.enable' => true,
						'minify.enabled' => true,
						'minify.auto' => 0,
						'minify.engine' => 'file'
					));
				},
				'disable' => function () use (&$thisclass) {
					$thisclass->update_w3tc(array(
						'minify.js.enable' => false,
						'minify.auto' => 0
					));
				}
			),
			'gzip' => array(
				'name' => 'Gzip Compression Enabled',
				'slug' => 'gzip',
				'plugin' => 'W3 Total Cache',
				'configured' => false,
				'description' => 'Makes your site significantly faster by compressing all text files to make them smaller.',
				'is_configured' => function (&$item) use (&$thisclass) {
					$w3tc = $thisclass->get_w3tc_config();
					if ($w3tc['browsercache.html.compression'] || $thisclass->server_info->cf || $thisclass->server_info->gzip || $thisclass->server_info->br) {
						$item['configured'] = true;
						$thisclass->set_install_status('gzip', true);
					} else {
						$thisclass->set_install_status('gzip', false);
					}
				},
				'enable' => function () use (&$thisclass) {
					$thisclass->enable_w3tc_gzip();
				},
				'disable' => function () use (&$thisclass) {
					$thisclass->disable_w3tc_gzip();
				},
				'remove_link' => true
			),
			'woo-cart-fragments' => array(
				'name' => 'Dequeue WooCommerce Cart Fragments AJAX calls',
				'slug' => 'woo-cart-fragments',
				'plugin' => 'A2 Optimized',
				'optional' => true,
				'configured' => false,
				'description' => '
                    <p>Disable WooCommerce Cart Fragments on your homepage. Also enables "redirect to cart page" option in WooCommerce</p>
				',
				'is_configured' => function (&$item) use (&$thisclass) {
					if (get_option('a2_wc_cart_fragments')) {
						$item['configured'] = true;
						$thisclass->set_install_status('woo-cart-fragments', true);
					} else {
						$thisclass->set_install_status('woo-cart-fragments', false);
					}
				},
				'enable' => function () use (&$thisclass) {
					$thisclass->enable_woo_cart_fragments();
				},
				'disable' => function () use (&$thisclass) {
					$thisclass->disable_woo_cart_fragments();
				},
			),
			'xmlrpc-requests' => array(
				'name' => 'Block Unauthorized XML-RPC Requests',
				'slug' => 'xmlrpc-requests',
				'plugin' => 'A2 Optimized',
				'optional' => true,
				'configured' => false,
				'description' => '
                    <p>Completely Disable XML-RPC services</p>
				',
				'is_configured' => function (&$item) use (&$thisclass) {
					if (get_option('a2_block_xmlrpc')) {
						$item['configured'] = true;
						$thisclass->set_install_status('xmlrpc-requests', true);
					} else {
						$thisclass->set_install_status('xmlrpc-requests', false);
					}
				},
				'enable' => function () use (&$thisclass) {
					$thisclass->enable_xmlrpc_requests();
				},
				'disable' => function () use (&$thisclass) {
					$thisclass->disable_xmlrpc_requests();
				},
			),
			'regenerate-salts' => array(
				'name' => 'Regenerate wp-config salts',
				'slug' => 'regenerate-salts',
				'plugin' => 'A2 Optimized',
				'optional' => true,
				'configured' => false,
				'is_configured' => function (&$item) use (&$thisclass) {
					if (get_option('a2_updated_regenerate-salts')) {
						$last_updated = strtotime(get_option('a2_updated_regenerate-salts'));
						if($last_updated > strtotime('-3 Months')){
							$item['configured'] = true;
						}
					}
				},
				'description' => '
                    <p>Generate new salt values for wp-config.php<br /><strong>This will log out all users including yourself</strong></p>
				',
				'last_updated' => true,
				'update' => true,
				'enable' => function () use (&$thisclass) {
					$thisclass->regenerate_wpconfig_salts();
				},
			),
			'htaccess' => array(
				'name' => 'Deny Direct Access to Configuration Files and Comment Form',
				'slug' => 'htaccess',
				'plugin' => 'A2 Optimized',
				'optional' => true,
				'configured' => false,
				'kb' => 'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-the-a2-optimized-plugin',
				'description' => 'Protects your configuration files by generating a Forbidden error to web users and bots when trying to access WordPress configuration files. <br> Also prevents POST requests to the site not originating from a user on the site. <br> <span class="danger" >note</span>: if you are using a plugin to allow remote posts and comments, disable this option.',
				'is_configured' => function (&$item) use (&$thisclass) {
					$htaccess = file_get_contents(ABSPATH . '.htaccess');
					if (strpos($htaccess, '# BEGIN WordPress Hardening') === false) {
						if ($thisclass->get_deny_direct() == true) {
							$thisclass->set_deny_direct(false);
						}
						//make sure the basic a2-optimized rules are present
						$thisclass->set_install_status('htaccess-deny-direct-access', false);
					} else {
						if ($thisclass->get_deny_direct() == false) {
							$thisclass->set_deny_direct(true);
						}
						$item['configured'] = true;
						$thisclass->set_install_status('htaccess-deny-direct-access', true);
					}
				},
				'enable' => function () use (&$thisclass) {
					$thisclass->set_deny_direct(true);
					$thisclass->write_htaccess();
				},
				'disable' => function () use (&$thisclass) {
					$thisclass->set_deny_direct(false);
					$thisclass->write_htaccess();
				}
			),
			'lock' => array(
				'name' => 'Lock Editing of Plugins and Themes from the WP Admin',
				'slug' => 'lock',
				'plugin' => 'A2 Optimized',
				'configured' => false,
				'kb' => 'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-the-a2-optimized-plugin',
				'description' => 'Prevents exploits that use the built in editing capabilities of the WP Admin',
				'is_configured' => function (&$item) use (&$thisclass) {
					$wpconfig = file_get_contents(ABSPATH . 'wp-config.php');
					if (strpos($wpconfig, '// BEGIN A2 CONFIG') === false) {
						if ($thisclass->get_lockdown() == true) {
							$thisclass->get_lockdown(false);
						}
						$thisclass->set_install_status('lock-editing', false);
					} else {
						if ($thisclass->get_lockdown() == false) {
							$thisclass->set_lockdown(true);
						}
						$item['configured'] = true;
						$thisclass->set_install_status('lock-editing', true);
					}
				},
				'enable' => function () use (&$thisclass) {
					$thisclass->set_lockdown(true);
					$thisclass->write_wp_config();
				},
				'disable' => function () use (&$thisclass) {
					$thisclass->set_lockdown(false);
					$thisclass->write_wp_config();
				}
			),
			'wp-login' => array(
				'name' => 'Login URL Change',
				'slug' => 'wp-login',
				'premium' => true,
				'plugin' => 'Rename wp-login.php',
				'configured' => false,
				'kb' => 'http://www.a2hosting.com/kb/security/application-security/wordpress-security#a-namemethodRenameLoginPageaMethod-3.3A-Change-the-WordPress-login-URL',
				'description' => '
                    <p>Change the URL of your login page to make it harder for bots to find it to brute force attack.</p>
                ',
				'is_configured' => function () {
					return false;
				}
			),
			'captcha' => array(
				'name' => 'reCAPTCHA on comments and login',
				'plugin' => 'reCAPTCHA',
				'slug' => 'captcha',
				'premium' => true,
				'configured' => false,
				'description' => 'Decreases spam and increases site security by adding a CAPTCHA to comment forms and the login screen.  Without a CAPTCHA, bots will easily be able to post comments to you blog or brute force login to your admin panel. You may override the default settings and use your own Site Key and select a theme.',
				'is_configured' => function () {
					return false;
				}
			),
			'images' => array(
				'name' => 'Compress Images on Upload',
				'plugin' => 'EWWW Image Optimizer',
				'slug' => 'images',
				'premium' => true,
				'configured' => false,
				'description' => 'Makes your site faster by compressing images to make them smaller.',
				'is_configured' => function () {
					return false;
				}
			),
			'turbo' => array(
				'name' => 'Turbo Web Hosting',
				'slug' => 'turbo',
				'configured' => false,
				'premium' => true,
				'description' => '
                    <ul>
                        <li>Turbo Web Hosting servers compile .htaccess files to make speed improvements. Any changes to .htaccess files are immediately re-compiled.</li>
                        <li>Turbo Web Hosting servers have their own PHP API that provides speed improvements over FastCGI and PHP-FPM (FastCGI Process Manager). </li>
                        <li>To serve static files, Turbo Web Hosting servers do not need to create a worker process as the user. Servers only create a worker process for PHP scripts, which results in faster performance.</li>
                        <li>PHP OpCode Caching is enabled by default. Accounts are allocated 256 MB of memory toward OpCode caching.</li>
                        <li>Turbo Web Hosting servers have a built-in caching engine for Full Page Cache and Edge Side Includes.</li>
                    </ul>
                ',
				'is_configured' => function () {
					return false;
				}
			),
			'memcached' => array(
				'name' => 'Memcached Database and Object Cache',
				'slug' => 'memcached',
				'configured' => false,
				'premium' => true,
				'description' => '
                    <ul>
                        <li>Extremely fast and powerful caching system.</li>
                        <li>Store frequently used database queries and WordPress objects in Memcached.</li>
                        <li>Memcached is an in-memory key-value store for small chunks of arbitrary data (strings, objects) from results of database calls, API calls, or page rendering.</li>
                        <li>Take advantage of A2 Hosting&apos;s one-click memcached configuration for WordPress.</li>
                    </ul>
                ',
				'is_configured' => function () {
					return false;
				}
			)
		);
	}

	protected function get_private_optimizations() {
		if (class_exists('A2_Optimized_Private_Optimizations')) {
			$a2opt_priv = new A2_Optimized_Private_Optimizations();

			return $a2opt_priv->get_optimizations($this->thisclass);
		} else {
			return array();
		}
	}

	public function get_advanced() {
		$public_opts = $this->get_public_advanced();
		$private_opts = $this->get_private_advanced();

		return array_merge($public_opts, $private_opts);
	}

	protected function get_public_advanced() {
		$thisclass = $this->thisclass;

		return array(
			'gtmetrix' => array(
				'slug' => 'gtmetrix',
				'name' => 'GTmetrix',
				'plugin' => 'GTmetrix',
				'plugin_slug' => 'gtmetrix-for-wordpress',
				'file' => 'gtmetrix-for-wordpress/gtmetrix-for-wordpress.php',
				'configured' => false,
				'partially_configured' => false,
				'required_options' => array('gfw_options' => array('authorized')),
				'description' => '
      			<p>
					Plugin that actively keeps track of your WP install and sends you alerts if your site falls below certain criteria.
					The GTMetrix plugin requires an account with <a href="http://gtmetrix.com/" >gtmetrix.com</a>
      			</p>
				<p>
      				<b>Use this plugin only if your site is experiencing issues with slow load times.</b><br><b style="color:red">The GTMetrix plugin will slow down your site.</b>
      			</p>
      			',
				'not_configured_links' => array(),
				'configured_links' => array(
					'Configure GTmetrix' => 'admin.php?page=gfw_settings',
					'GTmetrix Tests' => 'admin.php?page=gfw_tests',
				),
				'partially_configured_links' => array(
					'Configure GTmetrix' => 'admin.php?page=gfw_settings',
					'GTmetrix Tests' => 'admin.php?page=gfw_tests',
				),
				'partially_configured_message' => 'Click &quot;Configure GTmetrix&quot; to enter your GTmetrix Account Email and GTmetrix API Key.',
				'kb' => 'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-w3-total-cache-and-gtmetrix',
				'is_configured' => function (&$item) use (&$thisclass) {
					$gfw_options = get_option('gfw_options');
					if (is_plugin_active($item['file']) && isset($gfw_options['authorized']) && $gfw_options['authorized'] == 1) {
						$item['configured'] = true;
						$thisclass->set_install_status('gtmetrix', true);
					} elseif (is_plugin_active($item['file'])) {
						$item['partially_configured'] = true;
					} else {
						$thisclass->set_install_status('gtmetrix', false);
					}
				},
				'enable' => function ($slug) use (&$thisclass) {
					$item = $thisclass->get_advanced_optimizations();
					$item = $item[$slug];
					if (!isset($thisclass->plugin_list[$item['file']])) {
						$thisclass->install_plugin($item['plugin_slug']);
					}
					if (!is_plugin_active($item['file'])) {
						$thisclass->activate_plugin($item['file']);
					}
				},
				'disable' => function ($slug) use (&$thisclass) {
					$item = $thisclass->get_advanced_optimizations();
					$item = $item[$slug];
					$thisclass->deactivate_plugin($item['file']);
				}
			),
			'cloudflare' => array(
				'slug' => 'cloudflare',
				'name' => 'CloudFlare',
				'premium' => true,
				'description' => '
                        <p>
                                CloudFlare is a free global CDN and DNS provider that can speed up and protect any site online.
                        </p>

                        <dl style="padding-left:20px">
                                        <dt>CloudFlare CDN</dt>
                                        <dd>Distribute your content around the world so it&apos;s closer to your visitors (speeding up your site).</dd>
                                        <dt>CloudFlare optimizer</dt>
                                        <dd>Web pages with ad servers and third party widgets load snappy on both mobile and computers.</dd>
                                        <dt>CloudFlare security</dt>
                                        <dd>Protect your website from a range of online threats from spammers to SQL injection to DDOS.</dd>
                                        <dt>CloudFlare analytics</dt>
                                        <dd>Get insight into all of your website&apos;s traffic including threats and search engine crawlers.</dd>
                        </dl>
                        <div class="alert alert-info">
                                Host with A2 Hosting to take advantage of one click CloudFlare configuration.
                        </div>
                ',
				'configured' => $this->server_info->cf,
				'is_configured' => function () {
					return false;
				},
				'not_configured_links' => array('Host with A2' => 'https://www.a2hosting.com/wordpress-hosting?utm_source=A2%20Optimized&utm_medium=Referral&utm_campaign=A2%20Optimized')
			)
		);
	}

	protected function get_private_advanced() {
		if (class_exists('A2_Optimized_Private_Optimizations')) {
			$a2opt_priv = new A2_Optimized_Private_Optimizations();

			return $a2opt_priv->get_advanced($this->thisclass);
		} else {
			return array();
		}
	}

	public function get_warnings() {
		$public_opts = $this->get_public_warnings();
		$private_opts = $this->get_private_warnings();

		return array_merge($public_opts, $private_opts);
	}

	protected function get_public_warnings() {
		return array(
			'Bad WP Options' => array(
				'posts_per_page' => array(
					'title' => 'Recent Post Limit',
					'description' => 'The number of recent posts per page is set greater than five. This could be slowing down page loads.',
					'type' => 'numeric',
					'threshold_type' => '>',
					'threshold' => 5,
					'config_url' => admin_url() . 'options-reading.php'
				),
				'posts_per_rss' => array(
					'title' => 'RSS Post Limit',
					'description' => 'The number of posts from external feeds is set greater than 5. This could be slowing down page loads.',
					'type' => 'numeric',
					'threshold_type' => '>',
					'threshold' => 5,
					'config_url' => admin_url() . 'options-reading.php'
				),
				'show_on_front' => array(
					'title' => 'Recent Posts showing on home page',
					'description' => 'Speed up your home page by selecting a static page to display.',
					'type' => 'text',
					'threshold_type' => '=',
					'threshold' => 'posts',
					'config_url' => admin_url() . 'options-reading.php'
				),
				'permalink_structure' => array(
					'title' => 'Permalink Structure',
					'description' => 'To fully optimize page caching with "Disk Enhanced" mode:<br>you must set a permalink structure other than "Default".',
					'type' => 'text',
					'threshold_type' => '=',
					'threshold' => '',
					'config_url' => admin_url() . 'options-permalink.php'
				)
			),
			'Advanced Warnings' => array(
				'themes' => array(
					'is_warning' => function () {
						$themes = wp_get_themes();
						switch (count($themes)) {
							case 1:
								return false;
							case 2:
								$theme = wp_get_theme();
								if ($theme->get('Template') != '') {
									return false;
								}
						}

						return true;
					},
					'title' => 'Unused Themes',
					'description' => 'One or more unused themes are installed. Unused themes should be deleted.  For more information read the Wordpress.org Codex on <a target="_blank" href="http://codex.wordpress.org/WordPress_Housekeeping#Theme_Housekeeping">WordPress Housekeeping</a>',
					'config_url' => admin_url() . 'themes.php'
				),
				'a2_hosting' => array(
					'title' => 'Not Hosted with A2 Hosting',
					'description' => 'Get faster page load times and more optimizations when you <a href="https://www.a2hosting.com/wordpress-hosting?utm_source=A2%20Optimized&utm_medium=Referral&utm_campaign=A2%20Optimized" target="_blank">host with A2 Hosting</a>.',
					'is_warning' => function () {
						if (is_dir('/opt/a2-optimized')) {
							return false;
						}

						return true;
					},
					'config_url' => 'https://www.a2hosting.com/wordpress-hosting?utm_source=A2%20Optimized&utm_medium=Referral&utm_campaign=A2%20Optimized'
				)
			),
			'Bad Plugins' => array(
				'wp-super-cache',
				'wp-file-cache',
				'wp-db-backup',
			)
		);
	}

	protected function get_private_warnings() {
		if (class_exists('A2_Optimized_Private_Optimizations')) {
			$a2opt_priv = new A2_Optimized_Private_Optimizations();

			return $a2opt_priv->get_warnings($this->thisclass);
		} else {
			return array();
		}
	}
}
