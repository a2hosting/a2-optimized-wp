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
            'CloudFlare' => array(
                'slug' => 'cloudflare',
                'name' => 'CloudFlare',
                'description' => 'Host with A2 Hosting to take advantage of the CloudFlare CDN',
                'configured' => false,
                'is_configured' => function(){
                    return false;
                },
                'not_configured_links' => array('Host with A2'=>'https://www.a2hosting.com/wordpress-hosting?utm_source=A2%20Optimized&utm_medium=Referral&utm_campaign=A2%20Optimized')
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

    public function get_warnings()
    {
        $public_opts = $this->get_public_warnings();
        $private_opts = $this->get_private_warnings();
        return array_merge($public_opts, $private_opts);
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

    protected function get_private_warnings()
    {
        if (class_exists("A2_Optimized_Private_Optimizations")) {
            $a2opt_priv = new A2_Optimized_Private_Optimizations();
            return $a2opt_priv->get_warnings($this->thisclass);
        } else {
            return array();
        }
    }

}