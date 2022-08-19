<?php
// Prevent direct access to this file
if (! defined('WPINC')) {
	die;
}

class A2_Optimized_Optimizations {

    public function get_optimizations() {
		$public_opts = $this->get_public_optimizations();
		$private_opts = $this->get_private_optimizations();

		return array_merge($public_opts, $private_opts);
	}

    public function get_public_optimizations(){

        $optimizations = [
            'a2_page_cache' => [
                'name' => 'Page Caching',
                'slug' => 'a2_page_cache',
                'configured' => $this->is_active('a2_page_cache'),
                'category' => 'performance',
                'compatibility' => ['caching'],
                'description' => 'Enable Disk Cache to make the site faster by caching pages as static content.  Cache: a copy of rendered dynamic pages will be saved by the server so that the next user does not need to wait for the server to generate another copy.<br /><a href="admin.php?a2-page=cache_settings&page=A2_Optimized_Plugin_admin">Advanced Settings</a>',
            ],
            'a2_page_cache_gzip' => [
                'name' => 'Gzip Compression',
                'slug' => 'a2_page_gzip',
                'configured' => $this->is_active('a2_page_cache_gzip'),
                'category' => 'performance',
                'compatibility' => ['caching'],
                'description' => 'Makes your site significantly faster by compressing all text files to make them smaller.',
            ],
            'a2_object_cache' => [
                'name' => 'Object Caching',
                'slug' => 'a2_object_cache',
                'configured' => $this->is_active('a2_object_cache'),
                'category' => 'performance',
                'compatibility' => ['caching'],
                'description' => '
                    <ul>
                        <li>Extremely fast and powerful caching system.</li>
                        <li>Store frequently used database queries and WordPress objects in an in-memory object cache.</li>
                        <li>Object caching is a key-value store for small chunks of arbitrary data (strings, objects) from results of database calls, API calls, or page rendering.</li>
                        <li>Take advantage of A2 Hosting&apos;s one-click memcached configuration for WordPress.</li>
                    </ul>
                    <strong>A supported object cache server and the corresponding PHP extension are required.</strong><br /><a href="admin.php?a2-page=cache_settings&page=A2_Optimized_Plugin_admin">Configure Object Cache Settings</a>',
            ],
            'a2_page_cache_minify_html' => [
                'name' => 'Minify HTML Pages',
                'slug' => 'a2_page_cache_minify_html',
                'category' => 'performance',
                'configured' => $this->is_active('a2_page_cache_minify_html'),
                'compatibility' => ['pagebuilder', 'jsmin'],
                'description' => 'Removes extra spaces, tabs and line breaks in the HTML to reduce the size of the files sent to the user.',
                'remove_link' => true
            ],
            'a2_page_cache_minify_jscss' => [
                'name' => 'Minify Inline CSS and Javascript',
                'slug' => 'a2_page_cache_minify_jscss',
                'category' => 'performance',
                'configured' => $this->is_active('a2_page_cache_minify_jscss'),
                'compatibility' => ['pagebuilder', 'jsmin'],
                'optional' => true,
                'description' => 'Removes extra spaces, tabs and line breaks in inline CSS and Javascript to reduce the size of the files sent to the user. <strong>Note:</strong> This may cause issues with some page builders or other Javascript heavy front end plugins/themes.',
                'remove_link' => true
            ],
            'a2_db_optimizations' => [
                'name' => 'Schedule Automatic Database Optimizations',
                'slug' => 'a2_db_optimizations',
                'configured' => $this->is_active('a2_db_optimizations'),
                'category' => 'performance',
                'description' => 'If enabled, will periodically clean the MySQL database of expired transients, trashed comments, spam comments, and optimize all tables. You may also select to remove post revisions and trashed posts from the Database Optimization Settings.<br />
                <a href="admin.php?a2-page=cache_settings&page=A2_Optimized_Plugin_admin">Configure Database Optimization Settings</a>',
            ],
            'woo_cart_fragments' => [
                'name' => 'Dequeue WooCommerce Cart Fragments AJAX calls',
                'slug' => 'woo_cart_fragments',
                'optional' => true,
                'category' => 'performance',
                'configured' => $this->is_active('woo_cart_fragments'),
                'description' => 'Disable WooCommerce Cart Fragments on your homepage. Also enables "redirect to cart page" option in WooCommerce',
            ],
            'xmlrpc_requests' => [
                'name' => 'Block Unauthorized XML-RPC Requests',
                'slug' => 'xmlrpc_requests',
                'optional' => true,
                'category' => 'security',
                'configured' => $this->is_active('xmlrpc_requests'),
                'description' => '
                    <p>Completely Disable XML-RPC services</p>
                ',
            ],
            'regenerate_salts' => [
                'name' => 'Regenerate wp-config salts',
                'slug' => 'regenerate_salts',
                'optional' => true,
                'configured' => $this->is_active('regenerate_salts'),
                'category' => 'security',
                'description' => "<p>Generate new salt values for wp-config.php</p><p>WordPress salts and security keys help secure your site's login process and the cookies that WordPress uses to authenticate users. There are security benefits to periodically changing your salts to make it even harder for malicious actors to access them. You may need to clear your browser cookies after activating this option.</p><p><strong>This will log out all users including yourself</strong></p>",
                'last_updated' => true,
                'update' => true,
            ],
            'htaccess' => [
                'name' => 'Deny Direct Access to Configuration Files and Comment Form',
                'slug' => 'htaccess',
                'optional' => true,
                'configured' => $this->is_active('htaccess'),
                'category' => 'security',
                'kb' => 'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-the-a2-optimized-plugin',
                'description' => 'Protects your configuration files by generating a Forbidden error to web users and bots when trying to access WordPress configuration files. <br> Also prevents POST requests to the site not originating from a user on the site. <br> <span class="danger" >note</span>: if you are using a plugin to allow remote posts and comments, disable this option.',
            ],
            'lock_editing' => [
                'name' => 'Lock Editing of Plugins and Themes from the WP Admin',
                'slug' => 'lock_editing',
                'configured' => $this->is_active('lock_editing'),
                'category' => 'security',
                'kb' => 'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-the-a2-optimized-plugin',
                'description' => 'Prevents exploits that use the built in editing capabilities of the WP Admin',
            ],
            'hide_login' => [
                'name' => 'Login URL Change',
                'slug' => 'hide_login',
                'premium' => true,
                'category' => 'security',
                'configured' => false,
                'kb' => 'http://www.a2hosting.com/kb/security/application-security/wordpress-security#a-namemethodRenameLoginPageaMethod-3.3A-Change-the-WordPress-login-URL',
                'description' => '
                    <p>Change the URL of your login page to make it harder for bots to find it to brute force attack.</p>
                ',
            ],
            'captcha' => [
                'name' => 'CAPTCHA on comments and login',
                'slug' => 'captcha',
                'premium' => true,
                'category' => 'security',
                'configured' => false,
                'description' => 'Decreases spam and increases site security by adding a CAPTCHA to comment forms and the login screen.  Without a CAPTCHA, bots will easily be able to post comments to you blog or brute force login to your admin panel. You may override the default settings and use your own Site Key and select a theme.',
            ],
            'compress_images' => [
                'name' => 'Compress Images on Upload',
                'slug' => 'compress_images',
                'premium' => true,
                'category' => 'performance',
                'configured' => false,
                'description' => 'Makes your site faster by compressing images to make them smaller.',
            ],
            'turbo' => [
                'name' => 'Turbo Web Hosting',
                'slug' => 'turbo',
                'configured' => false,
                'category' => 'performance',
                'compatibility' => ['caching'],
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
                $optimizations['a2_object_cache']['description'] .= '<br /><strong>This feature is provided by the LiteSpeed Cache plugin.</strong></p>';
                unset($optimizations['a2_object_cache']['disable']);
            }
        }

        if (get_option('a2_optimized_memcached_invalid')) {
            unset($optimizations['a2_object_cache']['enable']);
        }
            if (class_exists('A2_Optimized_Private_Optimizations')) {
                $a2opt_priv = new A2_Optimized_Private_Optimizations();
                // reserved for future use
            }

        return $optimizations;
    }

    protected function get_private_optimizations() {
        if (class_exists('A2_Optimized_Private_Optimizations')) {
            $a2opt_priv = new A2_Optimized_Private_Optimizations();

            return $a2opt_priv->get_optimizations();
        } else {
            return [];
        }
    }

    public function get_best_practicies() {
        //TODO: should this be the site health items instead?
        $response = [
            'posts_per_page' => [
                'title' => 'Recent Post Limit',
                'description' => 'The number of recent posts per page should be less than fifteen for most sites. This could slow down page loads.',
                'config_url' => admin_url() . 'options-reading.php',
                'is_warning' => $this->is_active('posts_per_page'),
            ],
            'posts_per_rss' => [
                'title' => 'RSS Post Limit',
                'description' => 'The number of posts in the RSS feeds should be less than than 20 for most sites. This could slow down page loads.',
                'config_url' => admin_url() . 'options-reading.php',
                'is_warning' => $this->is_active('posts_per_rss'),
            ],
            'show_on_front' => [
                'title' => 'Recent Posts showing on home page',
                'description' => 'Speed up your home page by selecting a static page to display.',
                'config_url' => admin_url() . 'options-reading.php',
                'is_warning' => $this->is_active('show_on_front'),
            ],
            'permalink_structure' => [
                'title' => 'Permalink Structure',
                'description' => 'To fully optimize page caching, and get added SEO benefits, you should set a permalink structure other than "Default".',
                'config_url' => admin_url() . 'options-permalink.php',
                'is_warning' => $this->is_active('permalink_structure'),
            ],
            'themes' => [
                'title' => 'Unused Themes',
                'description' => 'Unused, non-default themes should be deleted.  For more information read the Wordpress.org Codex on <a target="_blank" href="http://codex.wordpress.org/WordPress_Housekeeping#Theme_Housekeeping">WordPress Housekeeping</a>',
                'config_url' => admin_url() . 'themes.php',
                'is_warning' => $this->is_active('themes'),
            ],
            'plugins' => [
                'title' => 'Inactive Plugins',
                'description' => 'Unused, inactive plugins should be deleted. WordPress will still check for updates on each plugin even if it is not active, which could slow down your site. For more information read the Wordpress.org Codex on <a target="_blank" href="http://codex.wordpress.org/WordPress_Housekeeping">WordPress Housekeeping</a>',
                'config_url' => admin_url() . 'plugins.php',
                'is_warning' => $this->is_active('plugins'),
            ],
            'a2_hosting' => [
                'title' => 'Hosted with A2 Hosting',
                'description' => 'Get faster page load times and more optimizations when you <a href="https://www.a2hosting.com/wordpress-hosting?utm_source=A2%20Optimized&utm_medium=Referral&utm_campaign=A2%20Optimized" target="_blank">host with A2 Hosting</a>.',
                'is_warning' => $this->is_active('a2_hosting'),
                'config_url' => 'https://www.a2hosting.com/wordpress-hosting?utm_source=A2%20Optimized&utm_medium=Referral&utm_campaign=A2%20Optimized'
            ],
        ];

        return $response;
    }

    public function apply_optimization($optimization, $enable){
        switch ($optimization) {
            case 'a2_page_cache':
                if($enable == 'true'){
                    return $this->enable_a2_page_cache();
                } else {
                    return $this->disable_a2_page_cache();
                }
                break;
            case 'a2_page_cache_gzip':
                if($enable == 'true'){
                    return $this->enable_a2_page_cache_gzip();
                } else {
                    return $this->disable_a2_page_cache_gzip();
                }
                break;
            case 'a2_object_cache':
                if($enable == 'true'){
                    return $this->enable_a2_object_cache();
                } else {
                    return $this->disable_a2_object_cache();
                }
                break;
            case 'a2_page_cache_minify_html':
                if($enable == 'true'){
                    return $this->enable_a2_page_cache_minify_html();
                } else {
                    return $this->disable_a2_page_cache_minify_html();
                }
                break;
            case 'a2_page_cache_minify_jscss':
                if($enable == 'true'){
                    return $this->enable_a2_page_cache_minify_jscss();
                } else {
                    return $this->disable_a2_page_cache_minify_jscss();
                }
                break;
            case 'a2_db_optimizations':
                if($enable == 'true'){
                    A2_Optimized_DBOptimizations::set('cron_active', true);
                } else {
                    A2_Optimized_DBOptimizations::set('cron_active', false);
                }
                break;
            case 'woo_cart_fragments':
                if($enable == 'true'){
                    return $this->enable_woo_cart_fragments();
                } else {
                    return $this->disable_woo_cart_fragments();
                }
                break;
            case 'xmlrpc_requests':
                if($enable == 'true'){
                    return $this->enable_xmlrpc_requests();
                } else {
                    return $this->disable_xmlrpc_requests();
                }
                break;
            case 'regenerate_salts':
                if($enable == 'true'){
                    // This is a fire once optimization
                    // TODO: UI considerations for this?
                    return $this->regenerate_wpconfig_salts();
                }
                break;
            case 'htaccess':
                if($enable == 'true'){
                    return $this->set_deny_direct(true);
                } else {
                    return $this->set_deny_direct(false);
                }
                $this->write_htaccess();
                break;
            case 'lock_editing':
                if($enable == 'true'){
                    return $this->set_lockdown(true);
                } else {
                    return $this->set_lockdown(false);
                }
                $this->write_wp_config();
                break;
        }
    }

    public function is_active($optimization){
        switch ($optimization) {
            case 'a2_page_cache':
                if(get_option('a2_cache_enabled') == '1'){
                    return true;
                }
                break;
            case 'a2_page_cache_gzip':
                if (isset(A2_Optimized_Cache_Engine::$settings['compress_cache'])) {
                    return true;
                }
                break;
            case 'a2_object_cache':
                if (get_option('a2_object_cache_enabled') == 1 && file_exists( WP_CONTENT_DIR . '/object-cache.php')) {
                    return true;
                }
                break;
            case 'a2_page_cache_minify_html':
                if (isset(A2_Optimized_Cache_Engine::$settings['minify_html'])) {
                    return true;
                }
                break;
            case 'a2_page_cache_minify_jscss':
                if (isset(A2_Optimized_Cache_Engine::$settings['minify_inline_css_js'])) {
                    return true;
                }
                break;
            case 'a2_db_optimizations':
                $a2_db_opt = get_option('a2_db_optimizations');
                if (isset($a2_db_opt['cron_active']) && $a2_db_opt['cron_active']) {
                    return true;
                }
                break;
            case 'woo_cart_fragments':
                if(get_option('a2_wc_cart_fragments') == '1'){
                    return true;
                }
                break;
            case 'xmlrpc_requests':
                if(get_option('a2_block_xmlrpc') == '1'){
                    return true;
                }
                break;
            case 'regenerate_salts':
                if(get_option('a2_updated_regenerate-salts')){
                    $last_updated = strtotime(get_option('a2_updated_regenerate-salts'));
                    if ($last_updated > strtotime('-3 Months')) {
                        return true;
                    }
                }
                break;
            case 'htaccess':
                $htaccess = file_get_contents(ABSPATH . '.htaccess');
                if(strpos($htaccess, '# BEGIN WordPress Hardening') !== false && get_option('a2_optimized_deny_direct') == '1') {
                    return true;
                }
                break;
            case 'lock_editing':
                $wpconfig = file_get_contents(ABSPATH . 'wp-config.php');
                if (strpos($wpconfig, '// BEGIN A2 CONFIG') !== false && get_option('a2_optimized_lockdown') == '1') {
                    return true;
                }
                break;
            case 'no_mods':
                if(get_option('a2_optimized_nomods')){
                    return true;
                }
                break;
            case 'posts_per_page':
                if(get_option('posts_per_page') > 15){
                    return true;
                }
                break;
            case 'posts_per_rss':
                if(get_option('posts_per_rss') > 20){
                    return true;
                }
                break;
            case 'show_on_front':
                if(get_option('show_on_front') == 'posts'){
                    return true;
                }
                break;
            case 'permalink_structure':
                if(get_option('permalink_structure') == ''){
                    return true;
                }
                break;
            case 'themes':
                $theme_count = 0;
                $themes = wp_get_themes();
                foreach ($themes as $theme_name => $theme) {
                    if (substr($theme_name, 0, 6) != 'twenty') {
                        // We don't want default themes to count towards our warning total
                        $theme_count++;
                    }
                }
                switch ($theme_count) {
                    case 1:
                        return false;
                    case 2:
                        $theme = wp_get_theme();
                        if ($theme->get('Template') != '') {
                            return false;
                        }
                }
                return true;
                break;
            case 'plugins':
                $plugins = get_plugins();
                $plugin_count = 0;

                foreach ($plugins as $slug => $plugin) {
                    if (is_plugin_inactive($slug)) {
                        $plugin_count++;
                    }
                }
                if($plugin_count > 4){
                    return false;
                }
                return true;
                break;
            case 'a2_hosting':
                if (is_dir('/opt/a2-optimized')) {
                    return false;
                }
                return true;
                break;


            
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
        A2_Optimized_Cache_Disk::clean();
        A2_Optimized_Cache::update_backend();

        update_option('a2_cache_enabled', 0);

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
            $a2opt_priv = new A2_Optimized_Private_Optimizations();

            return $a2opt_priv->is_redis_supported();
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
    }

    public function set_nomods($lockdown = true) {
        if ($lockdown == false) {
            delete_option('a2_optimized_nomods');
        } else {
            update_option('a2_optimized_nomods', '1');
        }
    }

    public function set_deny_direct($deny = true) {
        if ($deny == false) {
            delete_option('a2_optimized_deny_direct');
        } else {
            update_option('a2_optimized_deny_direct', '1');
        }
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

        $a2hardening = '';

        if ($this->is_active('htaccess')) {
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

        $htaccess = file_get_contents(ABSPATH . '.htaccess');

        $pattern = "/[\r\n]*# BEGIN WordPress Hardening.*# END WordPress Hardening[\r\n]*/msiU";
        $htaccess = preg_replace($pattern, '', $htaccess);

        $htaccess = <<<HTACCESS
$a2hardening
$htaccess
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
}