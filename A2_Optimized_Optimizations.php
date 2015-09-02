<?php

/**
 * Created by PhpStorm.
 * Author: Benjamin cool
 * Date: 7/28/15
 * Time: 2:44 PM
 */
class A2_Optimized_Optimizations
{

    private $thisclass;


    public function __construct($thisclass)
    {
        $this->thisclass = $thisclass;
    }

    public function get_optimizations()
    {
        $public_opts = $this->get_public_optimizations();
        $private_opts = $this->get_private_optimizations();
        return array_merge($public_opts, $private_opts);
    }

    protected function get_public_optimizations()
    {
        $thisclass = $this->thisclass;

        return array(
            'cache' => array(
                'slug' => 'cache',
                'name' => 'Page Caching with W3 Total Cache',
                'plugin' => 'W3 Total Cache',
                'configured' => false,
                'description' => 'Utilize W3 Total Cache to make the site faster by caching pages as static content.  Cache: a copy of rendered dynamic pages will be saved by the server so that the next user does not need to wait for the server to generate another copy.',
                'is_configured' => function (&$item) use (&$thisclass) {
                    $w3tc = $thisclass->get_w3tc_config();
                    if ($w3tc['pgcache.enabled'] && $w3tc['dbcache.enabled'] && $w3tc['objectcache.enabled'] && $w3tc['browsercache.enabled']) {
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

                        $thisclass->set_install_status('cache', true);
                    } else {
                        $thisclass->set_install_status('cache', false);
                    }
                },
                'kb' => 'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-w3-total-cache-and-gtmetrix',
                'disable' => function () use (&$thisclass) {
                    $thisclass->disable_w3tc_cache();
                },
                'enable' => function () use (&$thisclass) {
                    $thisclass->enable_w3tc_cache();
                }
            ),
            /*'litespeed'=>$this->litespeed,*/
            'wp-login' => array(
                'name' => 'Login URL Change',
                'slug' => 'wp-login',
                'plugin' => 'Rename wp-login.php',
                'configured' => false,
                'kb' => 'http://www.a2hosting.com/kb/security/application-security/wordpress-security#a-namemethodRenameLoginPageaMethod-3.3A-Change-the-WordPress-login-URL',
                'description' => '',
                'is_configured' => function (&$item) use (&$thisclass) {
                    $plugins = $thisclass->get_plugins();
                    $active_plugins = get_option('active_plugins');
                    if (in_array('rename-wp-login/rename-wp-login.php', $active_plugins)) {
                        if ($rwl_page = get_option('rwl_page')) {
                            if ($rwl_page == '') {
                                if ($rwl_page = get_option('a2_login_page')) {
                                    update_option('rwl_page', $rwl_page);
                                } else {
                                    $rwl_page = "";
                                    $length = 4;
                                    $valid_chars = "abcdefghijklmnopqrstuvwxyz";
                                    // count the number of chars in the valid chars string so we know how many choices we have
                                    $num_valid_chars = strlen($valid_chars);
                                    // repeat the steps until we've created a string of the right length
                                    for ($i = 0; $i < $length; $i++) {
                                        // pick a random number from 1 up to the number of valid chars
                                        $random_pick = mt_rand(1, $num_valid_chars);
                                        // take the random character out of the string of valid chars
                                        // subtract 1 from $random_pick because strings are indexed starting at 0, and we started picking at 1
                                        $random_char = $valid_chars[$random_pick - 1];
                                        // add the randomly-chosen char onto the end of our string so far
                                        $rwl_page .= $random_char;
                                    }
                                    update_option('a2_login_page', $rwl_page);
                                    update_option('rwl_page', $rwl_page);
                                }
                            } else {//sync rwl_page and a2_login_page
                                if ($a2_login_page = get_option('a2_login_page')) {
                                    if ($a2_login_page != $rwl_page) {
                                        $this->deleteOption("hide_login_url");
                                        update_option('a2_login_page', $rwl_page);
                                    }
                                } else {
                                    update_option('a2_login_page', $rwl_page);
                                }
                            }
                        } elseif ($rwl_page = get_option('a2_login_page')) {
                            update_option('rwl_page', $rwl_page);
                        } else {
                            return false;
                        }
                        if (class_exists('W3_ConfigWriter')) {
                            $w3tc = $thisclass->get_w3tc_config();
                            if (!array_search($rwl_page, $w3tc['pgcache.reject.uri'])) {
                                array_push($w3tc['pgcache.reject.uri'], $rwl_page);
                                $thisclass->update_w3tc(array('pgcache.reject.uri' => $w3tc['pgcache.reject.uri']));
                            }
                        }
                        $item['configured'] = true;
                        $thisclass->set_install_status('wp-login', true);
                    } else {
                        $thisclass->set_install_status('wp-login', false);
                        return false;
                    }
                    return $item['configured'];
                },
                'disable' => function () use (&$thisclass) {

                    $thisclass->uninstall_plugin('rename-wp-login/rename-wp-login.php');
                    //A2_Optimized_OptionsManager::uninstall('rename-wp-login/rename-wp-login.php');
                    delete_option('rwl_admin');
                    delete_option('rwl_redirect');
                    delete_option('rwl_page');
                },
                'enable' => function () use (&$thisclass) {

                    if (!isset($thisclass->plugin_list['rename-wp-login/rename-wp-login.php'])) {
                        $thisclass->install_plugin('rename-wp-login');
                    }
                    $thisclass->activate_plugin('rename-wp-login/rename-wp-login.php');


                    if (get_option('a2_login_page') === false) {
                        if (get_option('rwl_page') === false) {
                            $rwl_page = "";
                            $length = 4;
                            $valid_chars = "abcdefghijklmnopqrstuvwxyz";
                            // count the number of chars in the valid chars string so we know how many choices we have
                            $num_valid_chars = strlen($valid_chars);
                            // repeat the steps until we've created a string of the right length
                            for ($i = 0; $i < $length; $i++) {
                                // pick a random number from 1 up to the number of valid chars
                                $random_pick = mt_rand(1, $num_valid_chars);
                                // take the random character out of the string of valid chars
                                // subtract 1 from $random_pick because strings are indexed starting at 0, and we started picking at 1
                                $random_char = $valid_chars[$random_pick - 1];
                                // add the randomly-chosen char onto the end of our string so far
                                $rwl_page .= $random_char;
                            }
                            update_option('a2_login_page', $rwl_page);
                            update_option('rwl_page', $rwl_page);
                        } else {
                            update_option('a2_login_page', get_option('rwl_page'));
                        }
                    } else {
                        update_option('rwl_page', get_option('a2_login_page'));
                    }
                    delete_option('rwl_redirect');
                }
            ),
            'captcha' => array('name' => 'reCAPTCHA on comments and login',
                'plugin' => 'reCAPTCHA',
                'slug' => 'captcha',
                'configured' => false,
                'description' => 'Decreases spam and increases site security by adding a CAPTCHA to comment forms and the login screen.  Without a CAPTCHA, bots will easily be able to post comments to you blog or brute force login to your admin panel.',
                'is_configured' => function (&$item) use (&$thisclass) {
                    if (get_option('A2_Optimized_Plugin_recaptcha') == 1) {
                        $item['configured'] = true;
                        $thisclass->set_install_status('captcha', true);
                    } else {
                        $thisclass->set_install_status('captcha', false);
                    }
                },
                'enable' => function () use (&$thisclass) {
                    if ($thisclass->getOption('recaptcha') === false) {
                        $thisclass->addOption('recaptcha', 1);
                    } else {
                        $thisclass->updateOption('recaptcha', 1);
                    }
                },
                'disable' => function () use (&$thisclass) {
                    $thisclass->updateOption('recaptcha', 0);
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
            'images' => array(
                'name' => 'Compress Images on Upload',
                'plugin' => 'EWWW Image Optimizer',
                'slug' => 'images',
                'configured' => false,
                //'kb'=>'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-ewww-image-optimizer',
                'description' => 'Makes your site faster by compressing images to make them smaller.',
                'configured_links' => array(
                    'Bulk Optimize' => 'upload.php?page=ewww-image-optimizer-bulk',
                ),
                'is_configured' => function (&$item) use (&$thisclass) {
                    if (is_plugin_active("ewww-image-optimizer/ewww-image-optimizer.php")) {
                        $item['configured'] = true;
                        $thisclass->set_install_status('compress-images', true);
                    } else {
                        $thisclass->set_install_status('compress-images', false);
                    }
                },
                'enable' => function () use (&$thisclass) {
                    $thisclass->install_plugin('ewww-image-optimizer');
                    $thisclass->activate_plugin('ewww-image-optimizer/ewww-image-optimizer.php');
                },
                'disable' => function () use (&$thisclass) {
                    $thisclass->uninstall_plugin('ewww-image-optimizer/ewww-image-optimizer.php', false);
                }
            ),
            'gzip' => array(
                'name' => 'Gzip Compression Enabled',
                'slug' => 'gzip',
                'plugin' => 'W3 Total Cache',
                'configured' => false,
                'kb' => 'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-w3-total-cache-and-gtmetrix',
                'description' => 'Makes your site significantly faster by compressing all text files to make them smaller.',
                'is_configured' => function (&$item) use (&$thisclass) {
                    $w3tc = $thisclass->get_w3tc_config();
                    if ($w3tc['browsercache.other.compression']) {
                        $item['configured'] = true;
                        $thisclass->set_install_status('gzip', true);
                    } else {
                        $thisclass->set_install_status('gzip', false);
                    }
                },
                'enable' => function () use (&$thisclass) {
                    $thisclass->update_w3tc(array(
                        'browsercache.other.compression' => true,
                        'browsercache.html.compression' => true,
                        'browsercache.cssjs.compression' => true
                    ));
                },
                'disable' => function () use (&$thisclass) {
                    $thisclass->update_w3tc(array(
                        'browsercache.other.compression' => false,
                        'browsercache.html.compression' => false,
                        'browsercache.cssjs.compression' => false
                    ));
                }
            ),
            'htaccess' => array(
                'name' => 'Deny Direct Access to Configuration Files and Comment Form',
                'slug' => 'htaccess',
                'plugin' => 'A2 Optimized',
                'configured' => false,
                'kb' => 'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-the-a2-optimized-plugin',
                'description' => 'Protects your configuration files by generating a Forbidden error to web users and bots when trying to access WordPress configuration files. <br> Also prevents POST requests to the site not originating from a user on the site. <br> <span class="danger" >note</span>: if you are using a plugin to allow remote posts and comments, disable this option.',
                'is_configured' => function (&$item) use (&$thisclass) {
                    $htaccess = file_get_contents(ABSPATH . '.htaccess');
                    if (strpos($htaccess, "# BEGIN WordPress Hardening") === false) {
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
                    if (strpos($wpconfig, "// BEGIN A2 CONFIG") === false) {
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
            )
        );
    }

    protected function get_private_optimizations()
    {
        if (class_exists("A2_Optimized_Private_Optimizations")) {
            $a2opt_priv = new A2_Optimized_Private_Optimizations();
            return $a2opt_priv->get_optimizations($this->thisclass);
        } else {
            return array();
        }
    }

    public function get_advanced()
    {
        $public_opts = $this->get_public_advanced();
        $private_opts = $this->get_private_advanced();
        return array_merge($public_opts, $private_opts);
    }

    protected function get_public_advanced()
    {

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
            'P3' => array(
                'slug' => 'P3',
                'name' => 'P3 (Plugin Performance Profiler)',
                'description' => '
      			<p>See which plugins are slowing down your site.
      			This plugin creates a performance report for your site.</p>
      			<p>
      				<b>Use this plugin only if your site is experiencing issues with slow load times.</b><br><b style="color:red">The P3 plugin will slow down your site.</b>
      			</p>
',
                'plugin' => 'P3 Profiler',
                'plugin_slug' => 'p3-profiler',
                'file' => 'p3-profiler/p3-profiler.php',
                'configured' => false,
                'configured_links' => array(
                    'Test Performance' => 'tools.php?page=p3-profiler',
                ),
                'kb' => 'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/debugging-wordpress-with-p3-profiler',
                'is_configured' => function (&$item) use (&$thisclass) {
                    if (is_plugin_active($item['file'])) {
                        $item['configured'] = true;
                        $thisclass->set_install_status('P3', true);
                    } else {
                        $thisclass->set_install_status('P3', false);
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
            /*'seo'=>array(
              'slug'=>'seo',
              'name'=>'SEO by Yoast',
              'description'=>'
                <p>
                  Improve your WordPress SEO: Write better content and have a fully optimized WordPress site using Yoast\'s WordPress SEO plugin.
                </p>
              ',
              'plugin'=>'wordpress-seo',
              'configured'=>false,
              'is_configured'=>function(&$item) uses($thisclass){
                if(function_exists('wpseo_init')){
                  $item['configured'] = true;

                }
                else{

                }
              },
              'enable'=>function(&$item) uses($thisclass){
                $thisclass->enable_yoast();
              },
              'disable'=>function(&$item) uses($thisclass){
                $thisclass->disable_yoast();
              }
            ),*/
            'cloudflare' => array(
                'slug' => 'cloudflare',
                'name' => 'CloudFlare',
                'description' => '
      			<p>
      				CloudFlare is a free global CDN and DNS provider that can speed up and protect any site online.
      			</p>

      			<dl style="padding-left:20px">
					<dt>CloudFlare CDN</dt>
					<dd>Distribute your content around the world so it’s closer to your visitors (speeding up your site).</dd>
					<dt>CloudFlare optimizer</dt>
					<dd>Web pages with ad servers and third party widgets load snappy on both mobile and computers.</dd>
					<dt>CloudFlare security</dt>
					<dd>Protect your website from a range of online threats from spammers to SQL injection to DDOS.</dd>
					<dt>CloudFlare analytics</dt>
					<dd>Get insight into all of your website’s traffic including threats and search engine crawlers.</dd>
      			</dl>
      			<p>
      				Use cPanel to activate CloudFlare.
      			</p>
',
                'plugin' => 'CloudFlare',
                'plugin_slug' => 'cloudflare',
                'configured' => false,
                'kb' => 'http://www.a2hosting.com/kb/security/application-security/wordpress-security#a-namemethodCloudFlareaMethod-4.3A-Enable-CloudFlare-for-your-site',
                'configured_links' => array(
                    'Dectivating CloudFlare' => array('http://www.a2hosting.com/kb/add-on-services/cloudflare/how-to-activate-cloudflare', '_blank'),
                    'Configure CloudFlare' => array('http://www.a2hosting.com/kb/add-on-services/cloudflare/configuring-cloudflare', '_blank'),
                    'About CloudFlare' => array('http://www.cloudflare.com', '_blank'),
                ),
                'not_configured_links' => array(
                    'Activating Cloudflare' => array('http://www.a2hosting.com/kb/add-on-services/cloudflare/how-to-activate-cloudflare', '_blank'),
                    'About CloudFlare' => array('http://www.cloudflare.com', '_blank')
                ),
                'file' => 'cloudflare/cloudflare.php',
                'is_configured' => function (&$item) use (&$thisclass) {
                    //curl to homepage
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, home_url());
                    curl_setopt($ch, CURLOPT_HEADER, 1);
                    curl_setopt($ch, CURLOPT_NOBODY, 1);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($ch, CURLOPT_NETRC, 1); // omit if you know no urls are FTP links...
                    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
                    ob_start();
                    curl_exec($ch);

                    $header = ob_get_contents();
                    ob_end_clean();
                    curl_close($ch);

                    $temp_headers = explode("\n", $header);
                    foreach ($temp_headers as $i => $header) {
                        $header = explode(":", $header, 2);
                        if (isset($header[1])) {
                            $headers[$header[0]] = $header[1];
                        } else {
                            $headers[$header[0]] = "";
                        }
                    }
                    if (isset($headers['Server']) && !(strpos(strtolower($headers['Server']), 'cloudflare') === false)) {
                        $item['configured'] = true;
                        $thisclass->set_install_status('cloudflare', true);
                    } else {
                        $thisclass->set_install_status('cloudflare', false);
                    }
                }
            )
        );
    }

    protected function get_private_advanced()
    {
        if (class_exists("A2_Optimized_Private_Optimizations")) {
            $a2opt_priv = new A2_Optimized_Private_Optimizations();
            return $a2opt_priv->get_advanced($this->thisclass);
        } else {
            return array();
        }
    }

    protected function get_private_warnings()
    {
        if (class_exists("A2_Optimized_Private_Optimizations")) {
            $a2opt_priv = new A2_Optimized_Private_Optimizations();
            return $a2opt_priv->get_warnings($this->thisclass);
        } else {
            return array();
        }
    }

    protected function get_public_warnings()
    {
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
                )
            ),
            'Bad Plugins' => array(
                'wp-super-cache',
                'wp-file-cache',
                'wordfence',
                'wp-db-backup',
                //'WP DB Manager',
                //'BackupWordPress',
                //'Broken Link Checker',
                //'MyReviewPlugin',
                //'LinkMan',
                //'Google XML Sitemaps',
                //'Fuzzy SEO Booster',
                //'Tweet Blender',
                //'Dynamic Related Posts',
                //'SEO Auto Links & Related Posts',
                //'Yet Another Related Posts Plugin',
                //'Similar Posts',
                //'Contextual Related Posts',

            )
        );
    }

}