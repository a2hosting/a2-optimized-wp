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
        }
        
    }

}