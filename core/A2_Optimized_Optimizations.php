<?php
// Prevent direct access to this file
if (! defined('WPINC')) {
	die;
}

class A2_Optimized_Optimizations {

    public $private_opts;

	public function __construct() {
        $this->private_opts = false;
        if(file_exists('/opt/a2-optimized/wordpress/class.A2_Optimized_Private_Optimizations_v3.php')){
            require_once('/opt/a2-optimized/wordpress/class.A2_Optimized_Private_Optimizations_v3.php');
            $this->private_opts = new A2_Optimized_Private_Optimizations(); 
        }
        $this->hooks();
    }
   
    /**
	 * Integration hooks.
	 */
	protected function hooks() {
		$wpconfig_clean_cron = get_option('a2_optimized_wpconfig_cleanup');
		if ($wpconfig_clean_cron && $wpconfig_clean_cron == 1) {
			add_action('a2_execute_wpconfig_cleanup', [&$this, 'maybe_clean_wpconfig_backup']);
			if (!wp_next_scheduled('a2_execute_wpconfig_cleanup')) {
				wp_schedule_event(time(), 'daily', 'a2_execute_wpconfig_cleanup');
			}
		}
	}

    public function get_optimizations() {
		$public_opts = $this->get_public_optimizations();
        $extra_settings = $this->get_extra_settings();

        $result = $public_opts;

        foreach($result as $k => $item){
            $result[$k]['extra_setting'] = array_key_exists($k, $extra_settings);
        }

        $result['extra_settings'] = $extra_settings;
        $result['settings_tethers'] = [
            'on' => [
                'a2_page_cache_gzip' => ['a2_page_cache'],
                'a2_page_cache_minify_html' => ['a2_page_cache'],
                'a2_page_cache_minify_jscss' => ['a2_page_cache'],
            ],
            'off' => [
                'a2_page_cache' => [
                    'a2_page_cache_gzip',
                    'a2_page_cache_minify_html',
                    'a2_page_cache_minify_jscss'
                ]
            ]
        ];

        return $result;
	}

    const LITESPEED_SETTINGS_KEYS = [
        "_version",
        "hash",
        "auto_upgrade",
        "api_key",
        "server_ip",
        "guest",
        "guest_optm",
        "news",
        "guest_uas",
        "guest_ips",
        "cache",
        "cache-priv",
        "cache-commenter",
        "cache-rest",
        "cache-page_login",
        "cache-favicon",
        "cache-resources",
        "cache-mobile",
        "cache-mobile_rules",
        "cache-browser",
        "cache-exc_useragents",
        "cache-exc_cookies",
        "cache-exc_qs",
        "cache-exc_cat",
        "cache-exc_tag",
        "cache-force_uri",
        "cache-force_pub_uri",
        "cache-priv_uri",
        "cache-exc",
        "cache-exc_roles",
        "cache-drop_qs",
        "cache-ttl_pub",
        "cache-ttl_priv",
        "cache-ttl_frontpage",
        "cache-ttl_feed",
        "cache-ttl_rest",
        "cache-ttl_browser",
        "cache-ttl_status",
        "cache-login_cookie",
        "cache-vary_group",
        "purge-upgrade",
        "purge-stale",
        "purge-post_all",
        "purge-post_f",
        "purge-post_h",
        "purge-post_p",
        "purge-post_pwrp",
        "purge-post_a",
        "purge-post_y",
        "purge-post_m",
        "purge-post_d",
        "purge-post_t",
        "purge-post_pt",
        "purge-timed_urls",
        "purge-timed_urls_time",
        "purge-hook_all",
        "esi",
        "esi-cache_admbar",
        "esi-cache_commform",
        "esi-nonce",
        "util-instant_click",
        "util-no_https_vary",
        "debug-disable_all",
        "debug",
        "debug-ips",
        "debug-level",
        "debug-filesize",
        "debug-cookie",
        "debug-collaps_qs",
        "debug-inc",
        "debug-exc",
        "debug-exc_strings",
        "db_optm-revisions_max",
        "db_optm-revisions_age",
        "optm-css_min",
        "optm-css_comb",
        "optm-css_comb_ext_inl",
        "optm-ucss",
        "optm-ucss_inline",
        "optm-ucss_whitelist",
        "optm-ucss_exc",
        "optm-css_exc",
        "optm-js_min",
        "optm-js_comb",
        "optm-js_comb_ext_inl",
        "optm-js_exc",
        "optm-html_min",
        "optm-html_lazy",
        "optm-qs_rm",
        "optm-ggfonts_rm",
        "optm-css_async",
        "optm-ccss_per_url",
        "optm-ccss_sep_posttype",
        "optm-ccss_sep_uri",
        "optm-css_async_inline",
        "optm-css_font_display",
        "optm-js_defer",
        "optm-emoji_rm",
        "optm-noscript_rm",
        "optm-ggfonts_async",
        "optm-exc_roles",
        "optm-ccss_con",
        "optm-js_defer_exc",
        "optm-gm_js_exc",
        "optm-dns_prefetch",
        "optm-dns_prefetch_ctrl",
        "optm-exc",
        "optm-guest_only",
        "object",
        "object-kind",
        "object-host",
        "object-port",
        "object-life",
        "object-persistent",
        "object-admin",
        "object-transients",
        "object-db_id",
        "object-user",
        "object-pswd",
        "object-global_groups",
        "object-non_persistent_groups",
        "discuss-avatar_cache",
        "discuss-avatar_cron",
        "discuss-avatar_cache_ttl",
        "optm-localize",
        "optm-localize_domains",
        "media-lazy",
        "media-lazy_placeholder",
        "media-placeholder_resp",
        "media-placeholder_resp_color",
        "media-placeholder_resp_svg",
        "media-lqip",
        "media-lqip_qual",
        "media-lqip_min_w",
        "media-lqip_min_h",
        "media-placeholder_resp_async",
        "media-iframe_lazy",
        "media-add_missing_sizes",
        "media-lazy_exc",
        "media-lazy_cls_exc",
        "media-lazy_parent_cls_exc",
        "media-iframe_lazy_cls_exc",
        "media-iframe_lazy_parent_cls_exc",
        "media-lazy_uri_exc",
        "media-lqip_exc",
        "media-vpi",
        "media-vpi_cron",
        "img_optm-auto",
        "img_optm-cron",
        "img_optm-ori",
        "img_optm-rm_bkup",
        "img_optm-webp",
        "img_optm-lossless",
        "img_optm-exif",
        "img_optm-webp_replace",
        "img_optm-webp_attr",
        "img_optm-webp_replace_srcset",
        "img_optm-jpg_quality",
        "crawler",
        "crawler-usleep",
        "crawler-run_duration",
        "crawler-run_interval",
        "crawler-crawl_interval",
        "crawler-threads",
        "crawler-timeout",
        "crawler-load_limit",
        "crawler-sitemap",
        "crawler-drop_domain",
        "crawler-map_timeout",
        "crawler-roles",
        "crawler-cookies",
        "misc-heartbeat_front",
        "misc-heartbeat_front_ttl",
        "misc-heartbeat_back",
        "misc-heartbeat_back_ttl",
        "misc-heartbeat_editor",
        "misc-heartbeat_editor_ttl",
        "cdn",
        "cdn-ori",
        "cdn-ori_dir",
        "cdn-exc",
        "cdn-quic",
        "cdn-cloudflare",
        "cdn-cloudflare_email",
        "cdn-cloudflare_key",
        "cdn-cloudflare_name",
        "cdn-cloudflare_zone",
        "cdn-mapping",
        "cdn-attr",
        "qc-token",
        "qc-nameservers",
        "_cache"
    ];

    public function get_litespeed_settings_snapshot(){
        $snapshot = [];
        $litespeed_namespace = 'litespeed.conf.';
        foreach (self::LITESPEED_SETTINGS_KEYS as $key){
            $snapshot[$key] = get_option($litespeed_namespace . $key);
        }
        return $snapshot;
    }

    public function set_litespeed_from_snapshot($snapshot){
        $litespeed_namespace = 'litespeed.conf.';
        foreach (self::LITESPEED_SETTINGS_KEYS as $key){
            update_option($litespeed_namespace . $key, $snapshot[$key]);
        }
    }

    public function get_extra_settings(){
        $cache_settings = A2_Optimized_Cache::get_settings();
        $cache_expires = $cache_settings['cache_expires'] ? 'true' : 'false';
        $cache_expiry_time = $cache_settings['cache_expiry_time'];
        $clear_on_saved_post = $cache_settings['clear_site_cache_on_saved_post'] ? 'true' : 'false';
        $clear_on_saved_comment = $cache_settings['clear_site_cache_on_saved_comment'] ? 'true' : 'false';
        $clear_on_changed_plugin = $cache_settings['clear_site_cache_on_changed_plugin'] ? 'true' : 'false'; 
        $memcached_server = get_option('a2_optimized_memcached_server');
        $redis_server = get_option('a2_optimized_redis_server');
        $cache_type = get_option('a2_optimized_objectcache_type');
        $db_optimizations = get_option('a2_db_optimizations');
        $litespeed_lock = get_option('a2_litespeed_lock');

        if(!$db_optimizations){
            $db_optimizations = [
                'remove_revision_posts' => 0,
                'remove_trashed_posts' => 0,
                'remove_spam_comments' => 0,
                'remove_trashed_comments' => 0,
                'remove_expired_transients' => 0,
                'optimize_tables' => 0
            ];
            update_option('a2_db_optimizations', $db_optimizations);
        }

        $extra_settings = [];

        if (!is_plugin_active( 'litespeed-cache/litespeed-cache.php' )) {
            $extra_settings['a2_page_cache'] =  [
                'title' => 'Cache Behavior',
                'explanation' => 'Caching allows visitors to save copies of your web pages on their devices or browser.  When they return to your website in the future, your site files will load faster',
                'settings_sections' => [
                    'cache_expiration' => [
                        'title' => '',
                        'description' => '',
                        'settings' => [
                            'cache_expires' => [
                                'description' => 'Cache pages expire after (hours):',
                                'label' => "Cached pages expire {$cache_expiry_time} hours after created",
                                'input_type' => 'checkbox',
                                'value' => $cache_expires,
                                'extra_fields' => [
                                    'cache_expiry_time' => [
                                        'label' => "Cached pages expire {$cache_expiry_time} hours after created",
                                        'input_type' => 'number',
                                        'value' => $cache_expiry_time
                                    ],
                                ]
                            ],
                        ]
                    ],
                    'site_clear' => [
                        'title' => 'Clear Site Cache if:',
                        'description' => '',
                        'settings' => [
                            'clear_site_cache_on_saved_post' => [
                                'description' => 'Post has been published, updated, spammed or trashed.',
                                'explanation' => 'Only in place of the pages and/or associated cache',
                                'label' => "Clear the site cache if any post type has been published, updated, or trashed (instead of only the page and/or associated cache).",
                                'input_type' => 'checkbox',
                                'value' => $clear_on_saved_post
                            ],
                            'clear_site_cache_on_saved_comment' => [
                                'description' => 'Comment has been posted, updated, spammed or trashed.',
                                'explanation' => 'This is instead of only caching the page',
                                'label' => "Clear the site cache if a comment has been posted, updated, spammed, or trashed (instead of only the page cache).",
                                'input_type' => 'checkbox',
                                'value' => $clear_on_saved_comment
                            ],
                            'clear_site_cache_on_changed_plugin' => [
                                'description' => 'Plugin has been activated, updated or deactivated',
                                'explanation' => '',
                                'label' => "Clear the site cache if a plugin has been activated, updated, or deactivated.",
                                'input_type' => 'checkbox',
                                'value' => $clear_on_changed_plugin
                            ]
                        ]
                    ]
                ]
            ];
        }
        else {
            // override the object cache settings with the litespeed values
            $litespeed_cache_type = get_option('litespeed.conf.object-kind');
            if(get_option('litespeed.conf.object-port') == '0'){
                // socket connection
                $object_cache = get_option('litespeed.conf.object-host');
            } else {
                // TCP connection, include port
                $object_cache = get_option('litespeed.conf.object-host') . ":" . get_option('litespeed.conf.object-port');
            }
            if ($litespeed_cache_type == 1){
                $cache_type = 'redis';
                $redis_server = $object_cache;
            }
            else {
                $cache_type = 'memcached';
                $memcached_server = $object_cache;
            }
        }

        $extra_settings['a2_object_cache'] = [
            'title' => 'Object Cache Settings',
            'explanation' => 'settings for the on disk object cache',
            'settings_sections' => [
                'a2_optimized_objectcache_type' => [
                    'title' => '',
                    'description' => '',
                    'settings' => [
                        'a2_optimized_objectcache_type' => [
                            'description' => 'Object Cache Type',
                            'label' => '',
                            'input_type' => 'options',
                            'input_options' => [
                                'Memcached' => 'memcached',
                                'Redis' => 'redis'
                            ],
                            'value' => $cache_type,
                        ]
                    ]
                ],
                'memcached_server' => [
                    'title' => '',
                    'description' => '',
                    'settings' => [
                        'memcached_server' => [
                            'description' => 'Memcached Server',
                            'label' => '',
                            'input_type' => 'text',
                            'value' => $memcached_server,
                        ]
                    ]
                ],
                'redis_server' => [
                    'title' => '',
                    'description' => '',
                    'settings' => [
                        'redis_server' => [
                            'description' => 'Redis Server',
                            'label' => '<b>Handled by Litespeed Cache</b>. You can make changes <a href="admin.php?page=litespeed-cache#object">here</a>',
                            'input_type' => 'text',
                            'disabled' => '1',
                            'value' => $redis_server,
                        ]
                    ]
                ]
            ]
        ];

        $extra_settings['turbo'] = [
            'title' => 'LiteSpeed Adjustment Prevention',
            'explanation' => 'Changes to LiteSpeed will automatically revert to the optimized version. Turn this off to be able to make custom changes.',
            'settings_sections' => [
                'a2_litespeed_lock' => [
                    'title' => '',
                    'description' => '',
                    'settings' => [
                        'a2_litespeed_lock' => [
                            'description' => 'Changes to LiteSpeed will automatically revert to the optimized version. Turn this off to be able to make custom changes.',
                            'label' => "",
                            'input_type' => 'checkbox',
                            'value' => $litespeed_lock['locked'] == 1 ? 'true' : 'false'
                        ]
                    ]
                ]
            ]
        ];

        $extra_settings['a2_db_optimizations'] = [
            'title' => 'Database Optimization Settings',
            'explanation' => '',
            'settings_sections' => [
                'remove_revision_posts' => [
                    'title' => '',
                    'description' => '',
                    'settings' => [
                        'remove_revision_posts' => [
                            'description' => 'Delete all history of post revisions',
                            'label' => "",
                            'input_type' => 'checkbox',
                            'value' => $db_optimizations['remove_revision_posts'] ? 'true' : 'false'
                        ]
                    ]
                ],
                'remove_trashed_posts' => [
                    'title' => '',
                    'description' => '',
                    'settings' => [
                        'remove_trashed_posts' => [
                            'description' => 'Permanently delete all posts in trash',
                            'label' => "",
                            'input_type' => 'checkbox',
                            'value' => $db_optimizations['remove_trashed_posts'] ? 'true' : 'false'
                        ]
                    ]
                ],
                'remove_spam_comments' => [
                    'title' => '',
                    'description' => '',
                    'settings' => [
                        'remove_spam_comments' => [
                            'description' => 'Delete all comments marked as spam',
                            'label' => "",
                            'input_type' => 'checkbox',
                            'value' => $db_optimizations['remove_spam_comments'] ? 'true' : 'false'
                        ]
                    ]
                ],
                'remove_trashed_comments' => [
                    'title' => '',
                    'description' => '',
                    'settings' => [
                        'remove_trashed_comments' => [
                            'description' => 'Permanently delete all comments in trash',
                            'label' => "",
                            'input_type' => 'checkbox',
                            'value' => $db_optimizations['remove_trashed_comments'] ? 'true' : 'false'
                        ]
                    ]
                ],
                'remove_expired_transients' => [
                    'title' => '',
                    'description' => '',
                    'settings' => [
                        'remove_expired_transients' => [
                            'description' => 'Delete temporary data that has expired',
                            'label' => "",
                            'input_type' => 'checkbox',
                            'value' => $db_optimizations['remove_expired_transients'] ? 'true' : 'false'
                        ]
                    ]
                ],
                'optimize_tables' => [
                    'title' => '',
                    'description' => '',
                    'settings' => [
                        'optimize_tables' => [
                            'description' => 'Perform optimizations on all database tables',
                            'label' => "",
                            'input_type' => 'checkbox',
                            'value' => $db_optimizations['optimize_tables'] ? 'true' : 'false'
                        ]
                    ]
                ]
            ]
        ];
        return $extra_settings;
    }

	private function get_litespeed_description() {
		return '<strong>Requires:</strong> LiteSpeed Cache (Installed by default on new A2 Hosting WordPress installs. Can be installed/re-installed if necessary <a href="' . get_admin_url() . 'plugin-install.php?s=litespeed%2520cache&tab=search&type=term">here</a>)<br />';
	}

    public function get_public_optimizations(){

        $optimizations = [
            'a2_page_cache' => [
                'error' => '',
                'name' => 'Page Caching',
                'slug' => 'a2_page_cache',
                'premium' => false,
                'configured' => $this->is_active('a2_page_cache'),
                'category' => 'performance',
                'compatibility' => ['caching'],
                'description' => '<strong>Benefit:</strong> Makes your site faster for your visitors.<br />
                <strong>How-it-works:</strong> Allows site visitors to save copies of your web pages on their device or browser. When they return to your website in the future, your site files will load faster.<br /> 
                <strong>What does it impact:</strong> Time to First Byte (TTFB)',
                'extra_info' => ''
            ],
            'a2_page_cache_gzip' => [
                'error' => '',
                'name' => 'Gzip Compression',
                'slug' => 'a2_page_cache_gzip',
                'premium' => false,
                'configured' => $this->is_active('a2_page_cache_gzip'),
                'category' => 'performance',
                'compatibility' => ['caching'],
                'description' => '<strong>Benefit:</strong> Makes your site faster for visitors.<br />
                <strong>How it works:</strong> Compresses all text files to make them smaller.<br />
                <strong>What does it impact:</strong> Time to First Byte (TTFB)',
                'extra_info' => ''
            ],
            'a2_object_cache' => [
                'error' => '',
                'name' => 'Object Caching',
                'slug' => 'a2_object_cache',
                'premium' => false,
                'configured' => $this->is_active('a2_object_cache'),
                'category' => 'performance',
                'compatibility' => ['caching'],
                'description' => $this->get_litespeed_description() . '<strong>Benefit:</strong> Makes your site faster.<br />
                <strong>How-it-works:</strong> Serves cached items such as images, files, and metadata in less than a millisecond. You have the option to modify these settings to best suit your needs. <br /> 
                Tap Modify to make changes.',
                'extra_info' => ''
            ],
            'a2_page_cache_minify_html' => [
                'error' => '',
                'name' => 'Minify HTML Pages',
                'slug' => 'a2_page_cache_minify_html',
                'premium' => false,
                'category' => 'performance',
                'configured' => $this->is_active('a2_page_cache_minify_html'),
                'compatibility' => ['pagebuilder', 'jsmin'],
                'description' => '<strong>Benefit:</strong> Increases your site’s speed by reducing the file size sent to site visitors.<br />
                <strong>How it works:</strong> Removes extra spaces, tables, and line breaks in the HTML.',
                'remove_link' => true,
                'extra_info' => ''
            ],
            'a2_page_cache_minify_jscss' => [
                'error' => '',
                'name' => 'Minify Inline CSS and Javascript',
                'slug' => 'a2_page_cache_minify_jscss',
                'premium' => false,
                'category' => 'performance',
                'configured' => $this->is_active('a2_page_cache_minify_jscss'),
                'compatibility' => ['pagebuilder', 'jsmin'],
                'optional' => true,
                'description' => '<strong>Benefit:</strong> Reduces the size of files sent to your customer.<br />
                <strong>How it works:</strong> Removes extra spaces, tabs, and line breaks in inline CSS and Javascript.<br />
                <strong>What to know:</strong> This may cause issues with some page builders or other Javascript-heavy front-end plugins/themes. ',
                'remove_link' => true,
                'extra_info' => ''
            ],
            'a2_db_optimizations' => [
                'error' => '',
                'name' => 'Schedule Automatic Database Optimizations',
                'slug' => 'a2_db_optimizations',
                'premium' => false,
                'configured' => $this->is_active('a2_db_optimizations'),
                'category' => 'performance',
                'description' => '<strong>Benefit:</strong> Improves your database performance<br />
                <strong>How it works:</strong> Periodically cleans MySQL database of expired transients (a type of cached data used in WordPress) as well as trashed and spam comments. It will also optimize all tables. You can select to also remove post revisions and trashed posts from the Database Optimization Settings. You have the option to modify these settings to best suit your needs. <br />
                Tap Modify to make changes.
                ',
                'extra_info' => ''
            ],
            'woo_cart_fragments' => [
                'error' => '',
                'name' => 'Dequeue WooCommerce Cart Fragments AJAX calls',
                'slug' => 'woo_cart_fragments',
                'premium' => false,
                'optional' => true,
                'category' => 'performance',
                'configured' => $this->is_active('woo_cart_fragments'),
                'description' => '<strong>Benefit:</strong> Makes your WooCommerce site faster<br />
                <strong>How-it-works:</strong> Disables WooCommerce Cart Fragments on your homepage, and enables the "redirect to cart page" option in WooCommerce.<br />
                <strong>What to know:</strong> Slow performance and errors on WooCommerce sites are caused by a high number of AJAX requests because they are  uncached. If you are running a WooCommerce site and notice a high number of AJAX requests, disabling Cart Fragments AJAX may help improve your site\'s stability. ',
                'extra_info' => ''
            ],
            'xmlrpc_requests' => [
                'error' => '',
                'name' => 'Block Unauthorized XML-RPC Requests',
                'slug' => 'xmlrpc_requests',
                'premium' => false,
                'optional' => true,
                'category' => 'security',
                'configured' => $this->is_active('xmlrpc_requests'),
                'description' => '<strong>Benefit:</strong> Improves the security of your website.<br />
                <strong>How-it-works:</strong> Disables XML-RPC services. Although XML-RPC API is safe and is enabled by default, some WordPress security experts suggest disabling it.<br />
                <strong>What to know:</strong> Closes one more door that a potential hacker may try to exploit to hack your website.',
                'extra_info' => ''
            ],
            'htaccess' => [
                'error' => '',
                'name' => 'Deny Direct Access to Configuration Files and Comment Form',
                'slug' => 'htaccess',
                'premium' => false,
                'optional' => true,
                'configured' => $this->is_active('htaccess'),
                'category' => 'security',
                'kb' => 'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-the-a2-optimized-plugin',
                'description' => '<strong>Benefit:</strong> Protects your configuration files - parameters, options, settings, and preferences applied to an operating system.<br />
                <strong>How-it-works:</strong> Generates a Forbidden error to web users and bots when trying to access WordPress configuration files.<br />
                <strong>What to know:</strong> Prevents POST requests to the site not originating from a user on the site. If you are using a plugin to allow remote posts and comments, disable this option.',
                'extra_info' => ''
            ],
            'lock_editing' => [
                'error' => '',
                'name' => 'Lock Editing of Plugins and Themes from the WP Admin',
                'slug' => 'lock_editing',
                'premium' => false,
                'configured' => $this->is_active('lock_editing'),
                'category' => 'security',
                'kb' => 'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-the-a2-optimized-plugin',
                'description' => '<strong>Benefit:</strong> Improves the security of your website.<br />
                <strong>How-it-works:</strong> Prevents misuse of the WordPress Admin built-in editing capabilities.',
                'extra_info' => ''
            ],
            'hide_login' => [
                'error' => '',
                'name' => 'Login URL Change',
                'slug' => 'hide_login',
                'premium' => true,
                'category' => 'security',
                'configured' => false,
                'kb' => 'http://www.a2hosting.com/kb/security/application-security/wordpress-security#a-namemethodRenameLoginPageaMethod-3.3A-Change-the-WordPress-login-URL',
                'description' => '<strong>Benefit:</strong> Makes it more difficult for bad actors and bots to hack your website.<br />
                <strong>How it works:</strong> Changes the URL of the login page for your WordPress website.<br />
                <strong>What to know:</strong> Record the new login page URL so you don’t forget where to log in. ',
                'extra_info' => ''
            ],
            'captcha' => [
                'error' => '',
                'name' => 'CAPTCHA on comments and login',
                'slug' => 'captcha',
                'premium' => true,
                'category' => 'security',
                'configured' => false,
                'description' => '<strong>Benefit:</strong> Decreases spam and increase your site’s security. <br />
                <strong>How-it-works:</strong> Adds CAPTCHA to comment forms and login screens to deter bots from posting comments to your blog or brute force login - a hacking method that uses trial and error to crack passwords, login credentials, and encryption keys - to your admin panel.',
                'extra_info' => ''
            ],
            'compress_images' => [
                'error' => '',
                'name' => 'Compress Images on Upload',
                'slug' => 'compress_images',
                'premium' => true,
                'category' => 'performance',
                'configured' => false,
                'description' => '<strong>Benefit:</strong> Reduces the file size of your images to make your website load faster.<br />
                <strong>How it works:</strong> Automatically compresses images when you upload them to your site.',
                'extra_info' => ''
            ],
            'turbo' => [
                'error' => '',
                'name' => 'Turbo Web Hosting',
                'slug' => 'turbo',
                'configured' => false,
                'category' => 'performance',
                'compatibility' => ['caching'],
                'premium' => true,
                'description' => $this->get_litespeed_description() . '<strong>Benefit:</strong> Operates on a limited occupancy, upgraded server hardware with advanced caching software making the CPU (central processing unit) 40% faster and 20x faster page loads.<br /> 
                <strong>How it works:</strong> Servers compile .htaccess files - a file that controls the high-level configuration of your website - to make speed improvements. Any changes to .htaccess files are immediately re-compiled. Turbo Web Hosting servers have their own PHP API that provides speed improvements.<br />
                <strong>What to know:</strong> Turbo servers can handle 9X more traffic with 3X faster read/write speeds.',
                'extra_info' => ''
            ],
            'a2_wpconfig_cleanup' => [
                'error' => '',
                'name' => 'Remove old wp-config.php backups',
                'slug' => 'a2_wpconfig_cleanup',
                'premium' => false,
                'optional' => true,
                'configured' => $this->is_active('a2_wpconfig_cleanup'),
                'category' => 'security',
                'description' => 'A2 Optimized will create a backup file of your wp-config.php if there are possible breaking changes made. This feature will enable automatic removal of the backup files after 1 week.',
                'extra_info' => ''
            ],
            'a2_bcrypt_passwords' => [
                'error' => '',
                'name' => 'Use bcrypt password hashing',
                'slug' => 'a2_bcrypt_passwords',
                'premium' => false,
                'optional' => true,
                'configured' => $this->is_active('a2_bcrypt_passwords'),
                'category' => 'security',
                'description' => 'Replaces native WordPress password hashing with the modern and secure bcrypt method.' . WPMU_PLUGIN_DIR,
                'extra_info' => ''
            ],
        ];

        $optimizations = $this->apply_optimization_filter($optimizations);

        return $optimizations;
    }

    public function apply_optimization_filter($optimizations) {
        if (get_template() == 'Divi') {
            $optimizations['minify']['optional'] = true;
            $optimizations['css_minify']['optional'] = true;
            $optimizations['js_minify']['optional'] = true;
        }

        if (is_plugin_active('litespeed-cache/litespeed-cache.php')) {
            $optimizations['a2_object_cache']['name'] = 'Object Caching with Memcached or Redis';
            if (get_option('litespeed.conf.object') == 1) {
                $optimizations['a2_object_cache']['configured'] = true;
                $optimizations['a2_object_cache']['description'] .= '<br /><strong>The plugin LiteSpeed Cache (Turbo Feature) must be enabled for Redis caching to be available.</strong></p>';
                $optimizations['a2_object_cache']['locked'] = true;
            }
            unset($optimizations['a2_page_cache']);
        }

        if (class_exists('A2_Optimized_Private_Optimizations')) {
            $private_optimizations = $this->private_opts->get_optimizations();
            // pull any fields from opt to private opt that may be missing.
            $new_optimizations = array_merge($optimizations, $private_optimizations);
            $preserve_keys = ['description', 'error', 'kb', 'extra_info'];
            foreach ($new_optimizations as $key => $opt){
                foreach ($preserve_keys as $preserve){
                    if (!isset($opt[$preserve])){
                        if (array_key_exists($key, $optimizations)){
                            if (array_key_exists($preserve, $optimizations[$key])){
                                $new_optimizations[$key][$preserve] = $optimizations[$key][$preserve];
                            }
                        }
                        else {
                            $new_optimizations[$key][$preserve] = '';
                        }
                    }
                }
                $new_optimizations[$key]['disabled'] = false; // no disabling when using the private optimizations class
            }

            $optimizations = $new_optimizations;

        } else {
            foreach($optimizations as $k => $optimization){
                // Disable A2 exclusive items
                $optimizations[$k]['disabled'] = false;
                if($optimization['premium']){
                    $optimizations[$k]['disabled'] = true;
                }
            }
        }

        $invalid = get_option('a2_optimized_memcached_invalid');
        if ($invalid) {
            $optimizations['a2_object_cache']['configured'] = false;
            $optimizations['a2_object_cache']['error'] = "Unable to update object cache: {$invalid}. Please check your connection information.";
            delete_option('a2_optimized_memcached_invalid'); // we've displayed the error, and we'll allow the user to reset and check again.
        }

        if ($optimizations['hide_login']['configured'] == true){
            $login_url_option = get_option('wpseh_l01gnhdlwp');
            $login_url = home_url() . "?" . $login_url_option;
            $message = "Your login url is: <a href='{$login_url}'>{$login_url}</a>";
            $optimizations['hide_login']['extra_info'] = $message;
        }

        return $optimizations;
    }

    protected function get_private_optimizations() {
        if (class_exists('A2_Optimized_Private_Optimizations')) {
            return $this->private_opts->get_optimizations();
        } else {
            return [];
        }
    }

    public function get_best_practices() {
        $response = [
            'regenerate_salts' => [
                'title' => 'Regenerate wp-config salts',
                'slug' => 'regenerate_salts',
                'description' => '<strong>Benefit:</strong> SALT Keys scramble your password to keep your site secure. By using SALT keys, you protect your account and passwords even if login cookies are compromised. <br />
                <strong>How-it-works:</strong> Generates new salt values for wp-config.php WordPress salts and security keys.<br />
                <strong>What to know:</strong> We recommend you change them every 3 months and will remind you via the Alarm Bell. This update will log out all users including yourself.',
                'status' => $this->is_active('regenerate_salts', false),
            ],
            'posts_per_page' => [
                'title' => 'Recent Post Limit',
                'description' => 'The number of recent posts per page should be less than fifteen for most sites. This could slow down page loads.',
                'config_url' => admin_url() . 'options-reading.php',
                'status' => $this->is_active('posts_per_page', false),
            ],
            'posts_per_rss' => [
                'title' => 'RSS Post Limit',
                'description' => 'The number of posts in the RSS feeds should be less than than 20 for most sites. This could slow down page loads.',
                'config_url' => admin_url() . 'options-reading.php',
                'status' => $this->is_active('posts_per_rss', false),
            ],
            'show_on_front' => [
                'title' => 'Recent Posts showing on home page',
                'description' => 'Speed up your home page by selecting a static page to display.',
                'config_url' => admin_url() . 'options-reading.php',
                'status' => $this->is_active('show_on_front', false),
            ],
            'permalink_structure' => [
                'title' => 'Permalink Structure',
                'description' => 'To fully optimize page caching, and get added SEO benefits, you should set a permalink structure other than "Default".',
                'config_url' => admin_url() . 'options-permalink.php',
                'status' => $this->is_active('permalink_structure', false),
            ],
            'themes' => [
                'title' => 'Unused Themes',
                'description' => 'Unused, non-default themes should be deleted.  For more information read the Wordpress.org Codex on <a target="_blank" href="http://codex.wordpress.org/WordPress_Housekeeping#Theme_Housekeeping">WordPress Housekeeping</a>',
                'config_url' => admin_url() . 'themes.php',
                'status' => $this->is_active('themes', false),
            ],
            'plugins' => [
                'title' => 'Inactive Plugins',
                'description' => 'Unused, inactive plugins should be deleted. WordPress will still check for updates on each plugin even if it is not active, which could slow down your site. For more information read the Wordpress.org Codex on <a target="_blank" href="http://codex.wordpress.org/WordPress_Housekeeping">WordPress Housekeeping</a>',
                'config_url' => admin_url() . 'plugins.php',
                'status' => $this->is_active('plugins', false),
            ],
            'a2_hosting' => [
                'title' => 'Hosted with A2 Hosting',
                'description' => 'Get faster page load times and more optimizations when you <a href="https://www.a2hosting.com/wordpress-hosting?utm_source=A2%20Optimized&utm_medium=Referral&utm_campaign=A2%20Optimized" target="_blank">host with A2 Hosting</a>.',
                'status' => $this->is_active('a2_hosting', false),
                'config_url' => 'https://www.a2hosting.com/wordpress-hosting?utm_source=A2%20Optimized&utm_medium=Referral&utm_campaign=A2%20Optimized'
            ],
        ];

        return $response;
    }

    public function apply_optimization($optimization, $value){
        switch ($optimization) {
            case 'a2_page_cache':
                if($value == 'true'){
                    return $this->enable_a2_page_cache();
                } else {
                    return $this->disable_a2_page_cache();
                }
                break;
            case 'a2_page_cache_gzip':
                if($value == 'true'){
                    return $this->enable_a2_page_cache_gzip();
                } else {
                    return $this->disable_a2_page_cache_gzip();
                }
                break;
            case 'a2_object_cache':
                if($value == 'true'){
                    return $this->enable_a2_object_cache();
                } else {
                    return $this->disable_a2_object_cache();
                }
                break;
            case 'a2_page_cache_minify_html':
                if($value == 'true'){
                    return $this->enable_a2_page_cache_minify_html();
                } else {
                    return $this->disable_a2_page_cache_minify_html();
                }
                break;
            case 'a2_page_cache_minify_jscss':
                if($value == 'true'){
                    return $this->enable_a2_page_cache_minify_jscss();
                } else {
                    return $this->disable_a2_page_cache_minify_jscss();
                }
                break;
            case 'a2_db_optimizations':
                if($value == 'true'){
                    A2_Optimized_DBOptimizations::set('cron_active', true);
                } else {
                    A2_Optimized_DBOptimizations::set('cron_active', false);
                }
                return true;
                break;
            case 'woo_cart_fragments':
                if($value == 'true'){
                    return $this->enable_woo_cart_fragments();
                } else {
                    return $this->disable_woo_cart_fragments();
                }
                break;
            case 'a2_wpconfig_cleanup':
                if($value == 'true'){
                    return $this->enable_wpconfig_cleanup();
                } else {
                    return $this->disable_wpconfig_cleanup();
                }
                break;
            case 'a2_bcrypt_passwords':
                if($value == 'true'){
                    return $this->enable_bcrypt_passwords();
                } else {
                    return $this->disable_bcrypt_passwords();
                }
                break;
            case 'xmlrpc_requests':
                if($value == 'true'){
                    return $this->enable_xmlrpc_requests();
                } else {
                    return $this->disable_xmlrpc_requests();
                }
                break;
            case 'regenerate_salts':
                if($value == 'true'){
                    return $this->regenerate_wpconfig_salts();
                }
                break;
            case 'htaccess':
                if($value == 'true'){
                    $this->set_deny_direct(true);
                } else {
                    $this->set_deny_direct(false);
                }
                $this->write_htaccess();
                return true;
                break;
            case 'lock_editing':
                if($value == 'true'){
                    $this->write_wp_config();
                    $this->set_lockdown(true);
                } else {
                    $this->write_wp_config();
                    $this->set_lockdown(false);
                }
                $this->write_wp_config();
                return true;
                break;

            // Page cache extra settings
            case 'cache_expires':
            case 'clear_site_cache_on_saved_post':
            case 'clear_site_cache_on_saved_comment':
            case 'clear_site_cache_on_changed_plugin':
                $cache_settings = A2_Optimized_Cache::get_settings();
                if($value == 'true'){
                    $cache_settings[$optimization] = '1';
                } else {
                    $cache_settings[$optimization] = '0';
                }
                $updated = update_option('a2opt-cache', $cache_settings);
                
                A2_Optimized_Cache_Disk::create_settings_file($cache_settings);
                return true;
                break;
            case 'cache_expiry_time':
                $cache_settings = A2_Optimized_Cache::get_settings();
                $expiry = intval($value);
                if(is_int($expiry) && $expiry > 0){
                    if($expiry > 96){
                        $expiry = '96';
                    }
                    $cache_settings[$optimization] = $expiry;
                } else {
                    $cache_settings[$optimization] = '0';
                }
                update_option('a2opt-cache', $cache_settings);
                A2_Optimized_Cache_Disk::create_settings_file($cache_settings);
                return true;
                break;
            case 'a2_optimized_objectcache_type':
                if ($value != 'redis' && $value != 'memcached'){
                    return;
                }
                update_option('a2_optimized_objectcache_type', $value);
                return true;
                break;
            case 'memcached_server':
                $cache_type = get_option('a2_optimized_objectcache_type');

                if ($cache_type != 'memcached' || empty($value)){
                    return;
                }
                $a2_memcached_server = sanitize_text_field($value);
                $validated_server_address = A2_Optimized_Cache::validate_object_cache($a2_memcached_server);
                if (!$validated_server_address){
                    return;
                }
                update_option('a2_optimized_memcached_server', $validated_server_address);
                $this->write_wp_config();
                return true;
                break;
            case 'redis_server':
                $cache_type = get_option('a2_optimized_objectcache_type');
                if ($cache_type != 'redis' || empty($value)){
                    return;
                }
                $a2_redis_server = sanitize_text_field($value);
                $validated_server_address = A2_Optimized_Cache::validate_object_cache($a2_redis_server);
                if (!$validated_server_address){
                    return;
                }
                update_option('a2_optimized_redis_server', $validated_server_address);
                $this->write_wp_config();
                return true;
                break;
            case 'remove_revision_posts':
            case 'remove_trashed_posts':
            case 'remove_spam_comments':
            case 'remove_trashed_comments':
            case 'remove_expired_transients':
            case 'optimize_tables':
                $db_optimizations = get_option('a2_db_optimizations');
                if($value == 'true'){
                    $db_optimizations[$optimization] = '1';
                } else {
                    $db_optimizations[$optimization] = '0';
                }
                $updated = update_option('a2_db_optimizations', $db_optimizations);
                return true;
                break;
            case 'a2_litespeed_lock':
                $litespeed_lock = get_option('a2_litespeed_lock');
                $setting_val = $value == 'true' ? 1 : 0;
                $new_value = [
                    'locked' => $setting_val,
                    'snapshot' => []
                ];
                if ($value == 'true'){
                    $snapshot = $this->get_litespeed_settings_snapshot();
                    $new_value['snapshot'] = $snapshot;
                }
                $updated = update_option('a2_litespeed_lock', $new_value);
                return true;
                break;
            default:
               // Try private optimization
                if (class_exists('A2_Optimized_Private_Optimizations')) {
                    return $this->private_opts->apply_optimization($optimization, $value);
                }
                break;

        }
    }

    /***
     * check if a given optimization is currently active
     */
    public function is_active($optimization, $value_only = true){
        $result = [
            'value' => false,
            'is_warning' => false,
            'current' => ''
        ];

        switch ($optimization) {
            case 'a2_page_cache':
                if(get_option('a2_cache_enabled') == '1'){
                    $result['value'] = true;
                }
                break;
            case 'a2_page_cache_gzip':
                $settings = A2_Optimized_Cache::get_settings();
                if ($settings['compress_cache']) {
                    $result['value'] = true;
                }
                break;
            case 'a2_object_cache':
                if (get_option('a2_object_cache_enabled') == 1 && file_exists( WP_CONTENT_DIR . '/object-cache.php')) {
                    $result['value'] = true;
                }
                break;
            case 'a2_page_cache_minify_html':
                $settings = A2_Optimized_Cache::get_settings();
                if (isset($settings['minify_html']) && $settings['minify_html'] == 1) {
                    $result['value'] = true;
                }
                break;
            case 'a2_page_cache_minify_jscss':
                $settings = A2_Optimized_Cache::get_settings();
                if (isset($settings['minify_inline_css_js']) && $settings['minify_inline_css_js'] == 1) {
                    $result['value'] = true;
                }
                break;
            case 'a2_db_optimizations':
                $a2_db_opt = get_option('a2_db_optimizations');
                if (isset($a2_db_opt['cron_active']) && $a2_db_opt['cron_active']) {
                    $result['value'] = true;
                }
                break;
            case 'woo_cart_fragments':
                if(get_option('a2_wc_cart_fragments') == '1'){
                    $result['value'] = true;
                }
                break;
            case 'xmlrpc_requests':
                if(get_option('a2_block_xmlrpc') == '1'){
                    $result['value'] = true;
                }
                break;
            case 'regenerate_salts':
                if(get_option('a2_updated_regenerate-salts')){
                    $last_updated_date = get_option('a2_updated_regenerate-salts');
                    $last_updated = strtotime($last_updated_date);
                    $result['current'] = "Salts last Regenerated on {$last_updated_date}";
                    if ($last_updated > strtotime('-3 Months')) {
                        $result['value'] = true;
                        $result['is_warning'] = true;
                    }
                }
                break;
            case 'htaccess':
                $htaccess = file_get_contents(ABSPATH . '.htaccess');
                if(strpos($htaccess, '# BEGIN WordPress Hardening') !== false && get_option('a2_optimized_deny_direct') == '1') {
                    $result['value'] = true;
                }
                break;
            case 'lock_editing':
                $wpconfig = file_get_contents(ABSPATH . 'wp-config.php');
                if (strpos($wpconfig, '// BEGIN A2 CONFIG') !== false && get_option('a2_optimized_lockdown') == '1') {
                    $result['value'] = true;
                }
                break;
            case 'no_mods':
                if(get_option('a2_optimized_nomods')){
                    $result['value'] = true;
                }
                break;
            case 'posts_per_page':
                $ppp = get_option('posts_per_page');
                $result['current'] = "{$ppp} recent posts per page";
                if($ppp <= 15){
                    $result['value'] = true;
                }
                break;
            case 'posts_per_rss':
                $ppr = get_option('posts_per_rss');
                $result['current'] = "{$ppr} posts from external feeds.";
                if($ppr <= 20){
                    $result['value'] = true;
                }
                break;
            case 'show_on_front':
                $sof = get_option('show_on_front');
                $result['current'] = "Showing {$sof} on front page.";
                if($sof != 'posts'){
                    $result['value'] = true;
                }
                break;
            case 'permalink_structure':
                $ps = get_option('permalink_structure');
                $result['current'] = empty($ps) ? "Permalink structure is not set." : "Permalink structure set to '{$ps}'";
                if (!empty($ps)){
                    $result['value'] = true;
                }
                break;
            case 'themes':
                $theme_count = 0;
                $themes = wp_get_themes();
                foreach ($themes as $theme_name => $theme) {
                    if (substr($theme_name, 0, 6) != 'twenty') {
                        // We don't want default themes to count towards our warning total
                        ++$theme_count;
                    }
                }
                switch ($theme_count) {
                    case 1:
                        $result['current'] = "One theme configured.";
                        $result['value'] = true;
                        break;
                    case 2:
                        $theme = wp_get_theme();
                        if ($theme->get('Template') != '') {
                            $result['current'] = "One child theme configured.";
                            $result['value'] = true;
                        }
                        break;
                    default:
                        $result['current'] = "{$theme_count} themes configured.";
                        $result['value'] = false;
                        break;
                }
                break;
            case 'plugins':
                $plugins = get_plugins();
                $inactive_plugin_count = 0;

                foreach ($plugins as $slug => $plugin) {
                    if (is_plugin_inactive($slug)) {
                        $inactive_plugin_count++;
                    }
                }
                $result['current'] = "{$inactive_plugin_count} inactive plugins.";
                if($inactive_plugin_count <= 4){
                    $result['value'] = true;
                }
                break;
            case 'a2_hosting':
                $dir = '/opt/a2-optimized';
                $exists = is_dir($dir);
                $result['current'] = $exists ? "You are on A2 Hosting" : "You are not on an A2 Hosting server.";
                if ($exists) {
                    $result['value'] = true;
                }
                break;
            case 'a2_wpconfig_cleanup':
                if(get_option('a2_optimized_wpconfig_cleanup')){
                    $result['value'] = true;
                }
                break;
            case 'a2_bcrypt_passwords':
                if(file_exists(WPMU_PLUGIN_DIR . '/wp-password-bcrypt.php')){
                    $result['value'] = true;
                }
                break;
        }

        $result['is_warning'] = !$result['value'];
        if ($value_only){
            return $result['value'];
        }
        else {
            return $result;
        }
    }

     /**
     * Enable built-in page cache
     *
     */
    public function enable_a2_page_cache() {
        A2_Optimized_Cache_Disk::setup();
        A2_Optimized_Cache::update_backend();

        update_option('a2_cache_enabled', 1);

        return true;
    }

    /**
     * Disable built-in page cache
     *
     */
    public function disable_a2_page_cache() {
        update_option('a2_cache_enabled', 0);
       
        A2_Optimized_Cache_Disk::clean();
        A2_Optimized_Cache::update_backend();
        
        return true;
    }

    /**
     * Enable built-in page cache gzip
     *
     */
    public function enable_a2_page_cache_gzip() {
        $cache_settings = A2_Optimized_Cache::get_settings();

        $cache_settings['compress_cache'] = 1;

        update_option('a2opt-cache', $cache_settings);
        update_option('a2_cache_enabled', 1);

        // Rebuild cache settings file
        A2_Optimized_Cache_Disk::create_settings_file($cache_settings);
        return true;
    }

    /**
     * Disable built-in page cache gzip
     *
     */
    public function disable_a2_page_cache_gzip() {
        $cache_settings = A2_Optimized_Cache::get_settings();

        $cache_settings['compress_cache'] = 0;

        update_option('a2opt-cache', $cache_settings);

        // Rebuild cache settings file
        A2_Optimized_Cache_Disk::create_settings_file($cache_settings);
        return true;
    }

    /**
     * Enable memcached object cache
     *
     */
    public function enable_a2_object_cache() {
        if (get_option('a2_optimized_memcached_invalid')) {
            // Object cache server did not validate. exit.
            return false;
        }
        if (is_plugin_active('litespeed-cache/litespeed-cache.php')) {
            // Litespeed cache plugin is active, use that
            return $this->enable_lscache_object_cache();
        } else {
            // Try to enable A2 memcached object caching
            if (get_option('a2_optimized_memcached_server') == false) {
                // Second check for object cache server
                return false;
            }
            copy(A2OPT_DIR . '/object-cache.php', WP_CONTENT_DIR . '/object-cache.php');

            update_option('a2_object_cache_enabled', 1);
            $this->write_wp_config();
            
            return true;
        }
    }

    /**
     * Enable litespeed object cache
     *
     */
    public function enable_lscache_object_cache() {
        $object_cache_type = 'memcached';

        if (get_option('a2_optimized_objectcache_type')) {
            $object_cache_type = get_option('a2_optimized_objectcache_type');
        }

        /* Set type of object cache */
        if ($object_cache_type == 'memcached') {
            $server_host = get_option('a2_optimized_memcached_server');
            update_option('litespeed.conf.object-kind', 0);
        }
        if ($object_cache_type == 'redis') {
            $server_host = get_option('a2_optimized_redis_server');
            update_option('litespeed.conf.object-kind', 1);
        }

        update_option('litespeed.conf.object', 1); // Enable object cache
        update_option('litespeed.conf.object-host', $server_host); // Server host
        update_option('litespeed.conf.object-port', '0'); // Port is 0 for socket connections

        update_option('a2_object_cache_enabled', 1); // Flag that we have this enabled
    }

    /* Is redis supported */
    private function is_redis_supported() {
        if (class_exists('A2_Optimized_Private_Optimizations') && is_plugin_active('litespeed-cache/litespeed-cache.php')) {
            return $this->private_opts->is_redis_supported();
        }
        update_option('a2_optimized_objectcache_type', 'memcached');

        return false;
    }

    /**
     * Disable memcached object cache
     *
     */
    public function disable_a2_object_cache() {
        @unlink(WP_CONTENT_DIR . '/object-cache.php');

        $this->write_wp_config();

        update_option('a2_object_cache_enabled', 0);

        return true;
    }

    /**
     * Enable built-in page cache html minification
     *
     */
    public function enable_a2_page_cache_minify_html() {
        $cache_settings = A2_Optimized_Cache::get_settings();

        $cache_settings['minify_html'] = 1;

        update_option('a2opt-cache', $cache_settings);
        update_option('a2_cache_enabled', 1);

        // Rebuild cache settings file
        A2_Optimized_Cache_Disk::create_settings_file($cache_settings);

        return true;
    }

    /**
     * Disable built-in page cache html minification
     *
     */
    public function disable_a2_page_cache_minify_html() {
        $cache_settings = A2_Optimized_Cache::get_settings();
        $cache_settings['minify_html'] = 0;
        $cache_settings['minify_inline_css_js'] = 0; // Need to disable css/js as well

        update_option('a2opt-cache', $cache_settings);

        // Rebuild cache settings file
        A2_Optimized_Cache_Disk::create_settings_file($cache_settings);

        return true;
    }

     /**
     * Enable built-in page cache css/js minification
     *
     */
    public function enable_a2_page_cache_minify_jscss(){
        $cache_settings = A2_Optimized_Cache::get_settings();

        $cache_settings['minify_html'] = 1; // need html to be enabled as well
        $cache_settings['minify_inline_css_js'] = 1;

        update_option('a2opt-cache', $cache_settings);
        update_option('a2_cache_enabled', 1);

        // Rebuild cache settings file
        A2_Optimized_Cache_Disk::create_settings_file($cache_settings);

        return true;
    }

    /**
     * Disable built-in page cache css/js minification
     *
     */
    public function disable_a2_page_cache_minify_jscss(){
        $cache_settings = A2_Optimized_Cache::get_settings();

        $cache_settings['minify_inline_css_js'] = 0;

        update_option('a2opt-cache', $cache_settings);

        // Rebuild cache settings file
        A2_Optimized_Cache_Disk::create_settings_file($cache_settings);

        return true;
    }
    
    /**
    *  Enable automated removal of wp-config.php backups made by A2 Optimized
    */
    public function enable_wpconfig_cleanup(){
        update_option('a2_optimized_wpconfig_cleanup', 1);

        return true;
    }

    /**
    *  Disable automated removal of wp-config.php backups made by A2 Optimized
    */
    public function disable_wpconfig_cleanup(){
        delete_option('a2_optimized_wpconfig_cleanup');
        
        return true;
    }
    
    /**
    * Enable bcrypt password plugin
    */
    public function enable_bcrypt_passwords(){
        $plugin_file = 'wp-password-bcrypt.php';

        $src = trailingslashit( A2OPT_DIR . "/includes") . $plugin_file;
        $dest = trailingslashit( WPMU_PLUGIN_DIR ) . $plugin_file;

        if(!file_exists($dest)){
            // Create mu-plugins if it doesn't already exist
            if (!is_dir( WPMU_PLUGIN_DIR )){
                wp_mkdir_p( WPMU_PLUGIN_DIR );
            }

            // copy mu plugin over
            copy( $src, $dest );
        }
        return true;
    }

    /**
    * Disable bcrypt password plugin
    */
    public function disable_bcrypt_passwords(){
        $dest = trailingslashit( WPMU_PLUGIN_DIR ) . 'wp-password-bcrypt.php';
        if(file_exists($dest)){
            unlink($dest);
        }
        return true;
    }
    
    /**
    *  Enable WooCommerce Cart Fragment Dequeuing
    *
    */
    public function enable_woo_cart_fragments(){
        update_option('a2_wc_cart_fragments', 1);
        update_option('woocommerce_cart_redirect_after_add', 'yes'); // Recommended WooCommerce setting when disabling cart fragments

        return true;
    }

    /**
    *  Disable WooCommerce Cart Fragment Dequeuing
    *
    */
    public function disable_woo_cart_fragments(){
        delete_option('a2_wc_cart_fragments');
        delete_option('woocommerce_cart_redirect_after_add');
        
        return true;
    }

    /**
    *  Enable Blocking of XML-RPC Requests
    *  TODO: Check that the required code for this is running as expected
    */
    public function enable_xmlrpc_requests(){
        update_option('a2_block_xmlrpc', 1);

        return true;
    }

    /**
    *  Disable Blocking of XML-RPC Requests
    *
    */
    public function disable_xmlrpc_requests(){
        delete_option('a2_block_xmlrpc');
        
        return true;
    }

    /**
    *  Regenerate wp-config.php salts
    *
    */
    public function regenerate_wpconfig_salts(){
        $this->salts_array = [
            "define('AUTH_KEY',",
            'SECURE_AUTH_KEY',
            'LOGGED_IN_KEY',
            'NONCE_KEY',
            "define('AUTH_SALT',",
            'SECURE_AUTH_SALT',
            'LOGGED_IN_SALT',
            'NONCE_SALT',
        ];

        $returned_salts = file_get_contents('https://api.wordpress.org/secret-key/1.1/salt/');
        $this->new_salts = explode("\n", $returned_salts);

        update_option('a2_updated_regenerate-salts', date('F jS, Y'));

        return $this->writeSalts($this->salts_array, $this->new_salts);
    }

    public function regenerate_wpconfig_desc(){
        $output = '<p>Generate new salt values for wp-config.php<br /><strong>This will log out all users including yourself</strong><br />Last regenerated:</p>';

        return $output;
    }

    private function writeSalts($salts_array, $new_salts){
        $config_file = $this->config_file_path();

        $tmp_config_file = ABSPATH . 'wp-config-tmp.php';

        foreach ($salts_array as $salt_key => $salt_value) {
            $readin_config = fopen($config_file, 'r');
            $writing_config = fopen($tmp_config_file, 'w');

            $replaced = false;
            while (!feof($readin_config)) {
                $line = fgets($readin_config);
                if (stristr($line, $salt_value)) {
                    $line = $new_salts[$salt_key] . "\n";
                    $replaced = true;
                }
                fputs($writing_config, $line);
            }

            fclose($readin_config);
            fclose($writing_config);

            if ($replaced) {
                rename($tmp_config_file, $config_file);
            } else {
                unlink($tmp_config_file);
            }
        }
    }

    private function config_file_path(){
        $salts_file_name = 'wp-config';
        $config_file = ABSPATH . $salts_file_name . '.php';
        $config_file_up = ABSPATH . '../' . $salts_file_name . '.php';

        if (file_exists($config_file) && is_writable($config_file)) {
            return $config_file;
        } elseif (
            file_exists($config_file_up) && 
            is_writable($config_file_up) && 
            !file_exists(dirname(ABSPATH) . '/wp-settings.php')
            ) {
            return $config_file_up;
        }

        return false;
    }

    /**
     * Various .htaccess flags for security
     */
    public function set_lockdown($lockdown = true) {
        if ($lockdown == false) {
            delete_option('a2_optimized_lockdown');
        } else {
            update_option('a2_optimized_lockdown', '1');
        }
        $this->write_htaccess();
    }

    public function set_nomods($lockdown = true) {
        if ($lockdown == false) {
            delete_option('a2_optimized_nomods');
        } else {
            update_option('a2_optimized_nomods', '1');
        }
        $this->write_htaccess();
    }

    public function set_deny_direct($deny = true) {
        if ($deny == false) {
            update_option('a2_optimized_deny_direct', '0');
        } else {
            update_option('a2_optimized_deny_direct', '1');
        }
        $this->write_htaccess();
    }

    public function write_htaccess() {
        //make sure .htaccess exists
        touch(ABSPATH . '.htaccess');
        touch(ABSPATH . '404.shtml');
        touch(ABSPATH . '403.shtml');

        //make sure it is writable by owner and readable by everybody
        chmod(ABSPATH . '.htaccess', 0644);

        $home_path = explode('/', str_replace(['http://', 'https://'], '', home_url()), 2);

        if (!isset($home_path[1]) || $home_path[1] == '') {
            $home_path = '/';
        } else {
            $home_path = "/{$home_path[1]}/";
        }

        $htaccess = file_get_contents(ABSPATH . '.htaccess');
        $a2hardening = '';

        $deny_direct = get_option('a2_optimized_deny_direct');

        if ($deny_direct == '1' && strpos($htaccess, '# BEGIN WordPress Hardening') == false ) {
            $a2hardening = <<<APACHE
# BEGIN WordPress Hardening
<FilesMatch "^.*(error_log|wp-config\.php|php.ini|\.[hH][tT][aApP].*)$">
Order deny,allow
Deny from all
</FilesMatch>
<IfModule mod_rewrite.c>
    RewriteBase {$home_path}
    RewriteRule ^wp-admin/includes/ - [F,L]
    RewriteRule !^wp-includes/ - [S=3]
    RewriteRule ^wp-includes/[^/]+\.php$ - [F,L]
    RewriteRule ^wp-includes/js/tinymce/langs/.+\.php - [F,L]
    RewriteRule ^wp-includes/theme-compat/ - [F,L]
    RewriteRule ^wp-config\.php - [F,L]
    RewriteRule ^php\.ini - [F,L]
    RewriteRule \.htaccess - [F,L]
    RewriteCond %{REQUEST_METHOD} POST
    RewriteCond %{REQUEST_URI} .wp-comments-post.php*
    RewriteCond %{HTTP_REFERER} !.*{$_SERVER['HTTP_HOST']}.* [OR]
    RewriteCond %{HTTP_USER_AGENT} ^$
    RewriteRule (.*) - [F,L]
</IfModule>
# END WordPress Hardening

APACHE;
        }

        $pattern = "/[\r\n]*# BEGIN WordPress Hardening.*# END WordPress Hardening[\r\n]*/msiU";
        $htaccess = preg_replace($pattern, '', $htaccess);

        $htaccess = <<<HTACCESS
$a2hardening $htaccess
HTACCESS;

        //Write the rules to .htaccess
        $fp = fopen(ABSPATH . '.htaccess', 'c');

        if (flock($fp, LOCK_EX)) {
            ftruncate($fp, 0);	  // truncate file
            fwrite($fp, $htaccess);
            fflush($fp);			// flush output before releasing the lock
            flock($fp, LOCK_UN);	// release the lock

            return true;
        } else {
            return false;
        }
    }

    /**
     * Write the config options
     * TODO: confirm errors are returned to user on failure
     */
    public function write_wp_config() {
        $lockdown = $this->is_active('lock_editing');
        $nomods = $this->is_active('no_mods');
        $obj_server = get_option('a2_optimized_memcached_server');
        $backup_filename = 'wp-config.bak-a2.php';
        $error_message = '<div class="notice notice-error"><p>Unable to write to ' . ABSPATH . 'wp-config.php. Please check file permissions.</p><p><a href="' . admin_url('admin.php?page=A2_Optimized_Plugin_admin') . '">Back to A2 Optimized</a></p></div>';

        if (!file_exists(ABSPATH . 'wp-config.php')) {
            echo $error_message;
            exit;
        }

        touch(ABSPATH . 'wp-config.php');
        copy(ABSPATH . 'wp-config.php', ABSPATH . $backup_filename);

        $config_hash = sha1(file_get_contents(ABSPATH . 'wp-config.php'));
        $backup_config_hash = sha1(file_get_contents(ABSPATH . $backup_filename));
        if ($config_hash != $backup_config_hash || filesize(ABSPATH . $backup_filename) == 0) {
            echo $error_message;
            exit;
        }

        $a2_config = <<<PHP

// BEGIN A2 CONFIG

PHP;

        if ($lockdown) {
            $a2_config .= <<<PHP

define('DISALLOW_FILE_EDIT', true);

PHP;
        }

        if ($nomods) {
            $a2_config .= <<<PHP

define('DISALLOW_FILE_MODS', true);

PHP;
        }

        if ($obj_server) {
            $a2_config .= <<<PHP

define('MEMCACHED_SERVERS', array('default' => array('{$obj_server}')));

PHP;
        }

        $a2_config .= <<<PHP
// END A2 CONFIG
PHP;

        $wpconfig = file_get_contents(ABSPATH . 'wp-config.php');
        $pattern = "/[\r\n]*[\/][\/] BEGIN A2 CONFIG.*[\/][\/] END A2 CONFIG[\r\n]*/msU";
        $wpconfig = preg_replace($pattern, '', $wpconfig);

        $wpconfig = str_replace('<?php', "<?php{$a2_config}", $wpconfig);

        //Write the rules to .htaccess
        $fh = fopen(ABSPATH . 'wp-config.php', 'w+');
        fwrite($fh, $wpconfig);
        fclose($fh);

        $updated_config_hash = sha1(file_get_contents(ABSPATH . 'wp-config.php'));
        if ($updated_config_hash != sha1($wpconfig) || filesize(ABSPATH . 'wp-config.php') == 0) {
            copy(ABSPATH . $backup_filename, ABSPATH . 'wp-config.php');
            echo $error_message;
            exit;
        }
    }

    /*
	 *	XML-RPC Functions
	 */

	/* Is this a xmlrpc request? */
	public function is_xmlrpc_request() {
		return defined('XMLRPC_REQUEST') && XMLRPC_REQUEST;
	}

	/* Block this xmlrpc request unless other criteria are met */
	public function block_xmlrpc_request() {
		if ($this->client_is_automattic()) {
			return;
		}

		if ($this->clientip_whitelisted()) {
			return;
		}

		if (!headers_sent()) {
			header('Connection: close');
			header('Content-Type: text/xml');
			header('Date: ' . date('r'));
		}
		echo '<?xml version="1.0"?><methodResponse><fault><value><struct><member><name>faultCode</name><value><int>405</int></value></member><member><name>faultString</name><value><string>XML-RPC is disabled</string></value></member></struct></value></fault></methodResponse>';
		exit;
	}

	/* Stop advertising we accept certain requests */
    public static function remove_xmlrpc_methods($xml_rpc_methods) {
        if (self::client_is_automattic()) {
			return $xml_rpc_methods;
		}

		if (self::clientip_whitelisted()) {
			return $xml_rpc_methods;
		}

		unset($xml_rpc_methods['pingback.ping']); // block pingbacks
		unset($xml_rpc_methods['pingback.extensions.getPingbacks']); // block pingbacks

		return $xml_rpc_methods;
    }

    public static function clientip_whitelisted() {
		// For future consideration
		return false;
	}

	/* Checks if a Automattic plugin is installed
		Checks if IP making request if from Automattic
		https://jetpack.com/support/hosting-faq/
	*/
	public static function client_is_automattic() {
		$ip_address = $_SERVER['REMOTE_ADDR'];
		if (self::is_ip_in_range(
			$ip_address,
			[
				'122.248.245.244', // Jetpack
				'54.217.201.243', // Jetpack
				'54.232.116.4', // Jetpack
				['195.234.108.0', '195.234.111.255'], // Jetpack
				['192.0.64.1', '192.0.127.255'], // VaultPress range
				//array('192.0.80.0', '192.0.95.255'), // Akismet (covered by VaultPress range)
				//array('192.0.96.0', '192.0.111.255'), // Akismet
				//array('192.0.112.0', '192.0.127.255'), // Akismet
			]
		)) {
			return true;
		}

		return false;
	}

	/* Use ip2long to do comparisons */
	public static function is_ip_in_range($ip_address, $range_array) {
		$ip_long = ip2long($ip_address);
		foreach ($range_array as $item) {
			if (is_array($item)) {
				$ip_low = ip2long($item[0]);
				$ip_hi = ip2long($item[1]);
				if ($ip_long <= $ip_hi && $ip_low <= $ip_long) {
					return true;
				}
			} else {
				if ($ip_long == ip2long($item)) {
					return true;
				}
			}
		}

		return false;
	}

	public static function addLockedEditor() {
		require_once(ABSPATH . 'wp-admin/includes/plugin.php');
	}

    public static function login_captcha() {
		if (file_exists('/opt/a2-optimized/wordpress/recaptchalib_v2.php')) {
			include_once('/opt/a2-optimized/wordpress/recaptchalib_v2.php');

			$a2_recaptcha = get_option('A2_Optimized_Plugin_recaptcha');
			if ($a2_recaptcha == 1) {
				$captcha = a2recaptcha_get_html();
                ?>
                <style>
                  .g-recaptcha{
                    position: relative;
                    top: -6px;
                    left: -15px;
                  }
                </style>

                <?php 
                echo $captcha;
			}
		}
	}

	public static function comment_captcha() {
		if (!current_user_can('moderate_comments') && file_exists('/opt/a2-optimized/wordpress/recaptchalib_v2.php')){
            include_once('/opt/a2-optimized/wordpress/recaptchalib_v2.php');

            $a2_recaptcha = get_option('A2_Optimized_Plugin_recaptcha');
            if ($a2_recaptcha == 1) {
                $captcha = a2recaptcha_get_html();
                echo $captcha;
            }
		}
	}

	public static function captcha_authenticate($user, $username, $password) {
		if ($username != '' && !(defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)) {
			$a2_recaptcha = get_option('A2_Optimized_Plugin_recaptcha');
			if ($a2_recaptcha == 1) {
				if (file_exists('/opt/a2-optimized/wordpress/recaptchalib_v2.php')) {
					include_once('/opt/a2-optimized/wordpress/recaptchalib_v2.php');
					$resp = a2recaptcha_check_answer($_POST['g-recaptcha-response']);

					if (!empty($username)) {
						if (!$resp) {
							remove_filter('authenticate', 'wp_authenticate_username_password', 20);
							return new WP_Error('recaptcha_error', "<strong>The reCAPTCHA wasn't entered correctly. Please try again.</strong>");
						}
					}
				}
			}
		}
	}

	public static function captcha_comment_authenticate($commentdata) {
		if (!current_user_can('moderate_comments') && file_exists('/opt/a2-optimized/wordpress/recaptchalib_v2.php') && !(defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)){
            include_once('/opt/a2-optimized/wordpress/recaptchalib_v2.php');

            $a2_recaptcha = get_option('A2_Optimized_Plugin_recaptcha');
            if ($a2_recaptcha == 1) {
                $resp = a2recaptcha_check_answer($_POST['g-recaptcha-response']);

                if (!empty($commentdata)) {
                    if (!$resp) {
                        wp_die("<strong>The reCAPTCHA wasn't entered correctly. Please use your browsers back button and try again.</strong>");
                    }
                } else {
                    wp_die('<strong>There was an error. Please try again.</strong>');
                }
            }
		}

		return $commentdata;
	}

    public function maybe_display_litespeed_notice(){

        $litespeed_lock = get_option('a2_litespeed_lock');
        $current_page = isset($_GET['page']) ? $_GET['page'] : '';

        $this->maybe_keep_lightspeed_lock();

        if (is_array($litespeed_lock) && isset($litespeed_lock['locked']) && $litespeed_lock['locked'] == 1 && substr($current_page, 0, 9) == 'litespeed'){
            add_action( 'admin_notices', function() {
                ?>
                <div class="notice notice-error">
                        <p>Access to LiteSpeed settings has been restricted to protect the optimization work done by A2 Hosting Support. Visit <a href='admin.php?page=a2-optimized&a2_page=optimizations'>A2 Optimized</a> to turn restrictions off and allow custom changes. Changes made may require additional optimization work at an additional cost.</p>
                </div>
                <?php
            } );
    
            $this->set_litespeed_from_snapshot($litespeed_lock['snapshot']);
        }
    }

    private function maybe_keep_lightspeed_lock(){
        $litespeed_options = get_option('a2_litespeed_lock');
       
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $plugins = get_plugins();
        $active_plugins = '';
        foreach ($plugins as $slug => $plugin) {
            if (is_plugin_active($slug)) {
                $active_plugins .= $slug . ',';
            }
        }

        $active_theme = wp_get_theme();
        $theme_name = $active_theme->get('Name');

        if($litespeed_options && is_array($litespeed_options)){
            if(isset($litespeed_options['theme'])){ // Check that we have the same theme
                if($theme_name != $litespeed_options['theme']){
                    $litespeed_options['locked'] = 0;
                }
            }
            
            if(isset($litespeed_options['plugins'])){ // Check that we have the same plugins
                if($active_plugins != $litespeed_options['plugins']){
                    $litespeed_options['locked'] = 0;
                }
            }

        } else {
            $litespeed_options = [];
            $litespeed_options['locked'] = 0;
        }
        
        $litespeed_options['theme'] = $theme_name;
        $litespeed_options['plugins'] = $active_plugins;

        update_option('a2_litespeed_lock', $litespeed_options);
    }

    /*
     * Check if we should remove the backup of wp-config.php
     */
    public function maybe_clean_wpconfig_backup() {
        $wpconfig_clean = get_option('a2_optimized_wpconfig_cleanup');
        $backup_file = ABSPATH . 'wp-config.bak-a2.php';


        if ($wpconfig_clean && $wpconfig_clean == 1 && file_exists($backup_file)) {
            $last_modified = filemtime($backup_file);
            if((time() - $last_modified) > 604800){ // file was last modified more than a week ago
                unlink($backup_file);
            }
        }
    }

}
