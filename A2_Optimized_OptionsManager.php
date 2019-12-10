<?php

/*
    Author: Benjamin Cool
    Author URI: https://www.a2hosting.com/
    License: GPLv2 or Later
*/

if(is_admin()){
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    class A2_Plugin_Installer_Skin Extends Plugin_Installer_Skin{
        public function feedback($type){}
        public function error($error){}
    }
}

if (file_exists("/opt/a2-optimized/wordpress/Optimizations.php")) {
    require_once "/opt/a2-optimized/wordpress/Optimizations.php";
}
require_once 'A2_Optimized_Optimizations.php';

class A2_Optimized_OptionsManager {

    public $plugin_dir;
    private $optimizations;
    private $advanced_optimizations;
    private $advanced_optimization_status;
    private $optimization_count;
    private $advanced_optimization_count;
    private $plugin_list;
    private $install_status;

    public function set_w3tc_defaults()
    {
        $vars = $this->get_w3tc_defaults();
        if (!class_exists('W3_ConfigData')) {
            $this->enable_w3_total_cache();
        }

        $config_writer = new W3_ConfigWriter(0, false);
        foreach ($vars as $name => $val) {
            $config_writer->set($name, $val);
        }
        $config_writer->set('common.instance_id', mt_rand());
        $config_writer->save();
        $this->refresh_w3tc();
    }

    public function get_w3tc_defaults()
    {
        return array(
            'pgcache.check.domain' => true,
            'pgcache.prime.post.enabled' => true,
            'pgcache.reject.logged' => true,
            'pgcache.reject.request_head' => true,
            'pgcache.purge.front_page' => true,
            'pgcache.purge.home' => true,
            'pgcache.purge.post' => true,
            'pgcache.purge.comments' => true,
            'pgcache.purge.author' => true,
            'pgcache.purge.terms' => true,
            'pgcache.purge.archive.daily' => true,
            'pgcache.purge.archive.monthly' => true,
            'pgcache.purge.archive.yearly' => true,
            'pgcache.purge.feed.blog' => true,
            'pgcache.purge.feed.comments' => true,
            'pgcache.purge.feed.author' => true,
            'pgcache.purge.feed.terms' => true,
            'pgcache.cache.feed' => true,
            'pgcache.debug' => false,
            'pgcache.purge.postpages_limit' => 0,//purge all pages that list posts
            'pgcache.purge.feed.types' => array(
                0 => 'rdf',
                1 => 'rss',
                2 => 'rss2',
                3 => 'atom'
            ),
            'minify.debug' => false,
            'dbcache.debug' => false,
            'objectcache.debug' => false,

            'mobile.enabled' => true,


            'minify.auto' => false,
            'minify.html.engine' => 'html',
            'minify.html.inline.css' => true,
            'minify.html.inline.js' => true,


            'minify.js.engine' => 'js',
            'minify.css.engine' => 'css',

            'minify.js.header.embed_type' => 'nb-js',
            'minify.js.body.embed_type' => 'nb-js',
            'minify.js.footer.embed_type' => 'nb-js',

            'minify.lifetime' => 14400,
            'minify.file.gc' => 144000,

            'dbcache.lifetime' => 3600,
            'dbcache.file.gc' => 7200,


            'objectcache.lifetime' => 3600,
            'objectcache.file.gc' => 7200,

            'browsercache.cssjs.last_modified' => true,
            'browsercache.cssjs.compression' => true,
            'browsercache.cssjs.expires' => true,
            'browsercache.cssjs.lifetime' => 31536000,
            'browsercache.cssjs.nocookies' => false,
            'browsercache.cssjs.cache.control' => true,
            'browsercache.cssjs.cache.policy' => 'cache_maxage',
            'browsercache.cssjs.etag' => true,
            'browsercache.cssjs.w3tc' => true,
            'browsercache.cssjs.replace' => true,
            'browsercache.html.compression' => true,
            'browsercache.html.last_modified' => true,
            'browsercache.html.expires' => true,
            'browsercache.html.lifetime' => 30,
            'browsercache.html.cache.control' => true,
            'browsercache.html.cache.policy' => 'cache_maxage',
            'browsercache.html.etag' => true,
            'browsercache.html.w3tc' => true,
            'browsercache.html.replace' => true,
            'browsercache.other.last_modified' => true,
            'browsercache.other.compression' => true,
            'browsercache.other.expires' => true,
            'browsercache.other.lifetime' => 31536000,
            'browsercache.other.nocookies' => false,
            'browsercache.other.cache.control' => true,
            'browsercache.other.cache.policy' => 'cache_maxage',
            'browsercache.other.etag' => true,
            'browsercache.other.w3tc' => true,
            'browsercache.other.replace' => true,

            'config.check' => true,

            'varnish.enabled' => false

        );
    }

    public function enable_w3_total_cache()
    {
        $file = 'w3-total-cache/w3-total-cache.php';
        $slug = 'w3-total-cache';
        $this->install_plugin($slug);
        $this->activate_plugin($file);
        $this->hit_the_w3tc_page();
    }

    public function get_plugins()
    {
        if (isset($this->plugin_list)) {
            return $this->plugin_list;
        } else {
            return get_plugins();
        }
    }

    public function install_plugin($slug, $activate = false)
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        $api = plugins_api('plugin_information', array('slug' => $slug));

        $found = false;

        $plugins = $this->get_plugins();

        foreach ($plugins as $file => $plugin) {
            if ($plugin['Name'] == $api->name) {
                if (version_compare($plugin['Version'], '162.0.0.0') === -1) {
                    $this->uninstall_plugin($file);
                    break;
                }
                
                $found = true;
            }
        }

        if (!$found) {
            ob_start();
            $upgrader = new Plugin_Upgrader(new A2_Plugin_Installer_Skin(compact('title', 'url', 'nonce', 'plugin', 'api')));
            
            if ($slug == 'w3-total-cache') {
                $api->download_link = 'http://wp-plugins.a2hosting.com/wp-content/uploads/rkv-repo/w3-total-cache.zip';
            }
            
            $upgrader->install($api->download_link);
            ob_end_clean();
            $this->plugin_list = get_plugins();
        }

        if ($activate) {
            $plugins = $this->get_plugins();
            foreach ($plugins as $file => $plugin) {
                if ($plugin['Name'] == $api->name) {
                    $this->activate_plugin($file);
                }
            }
        }

        $this->clear_w3_total_cache();
    }

    public function activate_plugin($file)
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        activate_plugin($file);
        $this->clear_w3_total_cache();
    }

    public function clear_w3_total_cache()
    {
        if (is_plugin_active('w3-total-cache/w3-total-cache.php')) {
            //TODO:  add clear cache
        }
    }

    public function hit_the_w3tc_page()
    {
        $cookie = "";
        foreach ($_COOKIE as $name => $val) {
            $cookie .= "{$name}={$val};";
        }
        rtrim($cookie, ';');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, get_admin_url() . 'admin.php?page=w3tc_general&nonce=' . wp_create_nonce('w3tc'));
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_REFERER, get_admin_url());
        $result = curl_exec($ch);
        curl_close($ch);
    }

    public function refresh_w3tc()
    {
        $this->hit_the_w3tc_page();
    }

    public function get_w3tc_config(){
        if(class_exists('W3_ConfigData')){
            $config_writer = new W3_ConfigWriter(0,false);
            return W3_ConfigData::get_array_from_file($config_writer->get_config_filename());
        }
        else{
            return false;
        }
    }

    public function enable_w3tc_cache(){
        $permalink_structure = get_option('permalink_structure');
        $vars = array();
        if($permalink_structure == ''){
            $vars['pgcache.engine']='file';
        }
        else{
            $vars['pgcache.engine']='file_generic';
        }
        $vars['dbcache.engine'] = 'file';
        $vars['objectcache.engine'] = 'file';

        $vars['objectcache.enabled'] = true;
        $vars['dbcache.enabled'] = true;
        $vars['pgcache.enabled'] = true;
        $vars['browsercache.enabled'] = true;

        $this->update_w3tc($vars);
    }


    public function enable_w3tc_page_cache(){
        $permalink_structure = get_option('permalink_structure');
        $vars = array();
        if($permalink_structure == ''){
            $vars['pgcache.engine']='file';
        }
        else{
            $vars['pgcache.engine']='file_generic';
        }

        $vars['pgcache.enabled'] = true;
        $this->update_w3tc($vars);
    }

    public function enable_w3tc_db_cache(){
        $permalink_structure = get_option('permalink_structure');
        $vars = array();
        $vars['dbcache.engine'] = 'file';
        $vars['dbcache.enabled'] = true;
        $this->update_w3tc($vars);
    }

    public function enable_w3tc_object_cache(){
        $permalink_structure = get_option('permalink_structure');
        $vars = array();

        $vars['objectcache.engine'] = 'file';
        $vars['objectcache.enabled'] = true;

        $this->update_w3tc($vars);
    }

    public function enable_w3tc_browser_cache(){
        $permalink_structure = get_option('permalink_structure');
        $vars = array();
        $vars['browsercache.enabled'] = true;
        $this->update_w3tc($vars);
    }



    public function update_w3tc($vars)
    {
        $vars = array_merge($this->get_w3tc_defaults(), $vars);

        if (!class_exists('W3_ConfigData')) {
            $this->enable_w3_total_cache();
        }

        $config_writer = new W3_ConfigWriter(0, false);
        foreach ($vars as $name => $val) {
            $config_writer->set($name, $val);
        }
        $config_writer->set('common.instance_id', mt_rand());
        $config_writer->save();
        $this->refresh_w3tc();

    }

    public function disable_w3tc_cache()
    {
        $this->update_w3tc(array(
            'pgcache.enabled' => false,
            'dbcache.enabled' => false,
            'objectcache.enabled' => false,
            'browsercache.enabled' => false,
        ));
    }


    public function disable_w3tc_page_cache(){
        $vars = array();
        $vars['pgcache.enabled'] = false;
        $this->update_w3tc($vars);
    }

    public function disable_w3tc_db_cache(){
        $vars = array();
        $vars['dbcache.enabled'] = false;
        $this->update_w3tc($vars);
    }

    public function disable_w3tc_object_cache(){
        $vars = array();
        $vars['objectcache.enabled'] = false;
        $this->update_w3tc($vars);
    }

    public function disable_w3tc_browser_cache(){
        $vars = array();
        $vars['browsercache.enabled'] = false;
        $this->update_w3tc($vars);
    }


    public function disable_html_minify()
    {
        $this->update_w3tc(array(
            'minify.html.enable' => false,
            'minify.html.enabled' => false,
            'minify.auto' => false
        ));
    }

    public function enable_html_minify()
    {
        $this->update_w3tc(array(
            'minify.html.enable' => true,
            'minify.enabled' => true,
            'minify.auto' => false,
            'minify.engine' => 'file'
        ));
    }

    public function curl_save_w3tc($cookie, $url)
    {
        $post = "w3tc_save_options=Save all settings&_wpnonce=" . wp_create_nonce('w3tc') . "&_wp_http_referer=%2Fwp-admin%2Fadmin.php%3Fpage%3Dw3tc_general%26&w3tc_note%3Dconfig_save";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, get_admin_url() . $url);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_REFERER, get_admin_url() . $url);
        //curl_setopt($ch, CURLOPT_NOBODY, TRUE); // remove body
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $head = curl_exec($ch);
        curl_close($ch);
    }

    public function get_optimizations()
    {
        return $this->optimizations;
    }

    /**
     * Creates HTML for the Administration page to set options for this plugin.
     * Override this method to create a customized page.
     * @return void
     */
    public function settingsPage()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access A2 Optimized.', 'a2-optimized'));
        }


        $thisclass = $this;

        $optimization_count = 0;
        $this->get_plugin_status();


        $thisdir = rtrim(__DIR__, "/");


        wp_enqueue_style('bootstrap', plugins_url('/assets/bootstrap/css/bootstrap.css',__FILE__));
        wp_enqueue_style('bootstrap-theme', plugins_url('/assets/bootstrap/css/bootstrap-theme.css',__FILE__));
        wp_enqueue_script('bootstrap-theme', plugins_url('/assets/bootstrap/js/bootstrap.js',__FILE__), array('jquery'));



        $image_dir = plugins_url('/assets/images',__FILE__);

        do_action('a2_notices');


        $ini_error_reporting = ini_get('error_reporting');
        //ini_set('error_reporting',0);

        $this->optimization_status = "";

        $optionMetaData = $this->getOptionMetaData();


        $optimization_status = "";


        foreach ($this->advanced_optimizations as $shortname => &$item) {
            $this->advanced_optimization_status .= $this->get_optimization_status($item);
            if ($item['configured']) {
                $this->advanced_optimization_count++;
            }
        }

        $this->optimization_count = 0;


        foreach ($this->optimizations as $shortname => &$item) {
            $this->optimization_status .= $this->get_optimization_status($item);
            if ($item['configured']) {
                $this->optimization_count++;
            }
        }

        if ($this->optimization_count == count($this->optimizations)) {
            $optimization_alert = '<div  class="alert alert-success">Your site has been fully optimized!</div>';
        } elseif (!$this->optimizations['page_cache']['configured']) {
            $optimization_alert = '<div  class="alert alert-danger">Your site is NOT optimized!</div>';
        } elseif ($this->optimization_count > 5) {
            $optimization_alert = '<div  class="alert alert-success">Your site has been partially optimized!</div>';
        } elseif ($this->optimization_count > 2) {
            $optimization_alert = '<div  class="alert alert-danger">Your site is barely optimized!</div>';
        } else {
            $optimization_alert = '<div  class="alert alert-danger">Your site is NOT optimized!</div>';
        }


        $optimization_number = count($this->optimizations);

        $optimization_circle = "";
        if ($optimization_number > 0) {
            $optimization_circle = <<<HTML
<span class="badge badge-success">{$this->optimization_count}/{$optimization_number}</span>
HTML;
        }


        $kb_search_box = <<<HTML
<div class='big-search' style="margin-top:34px" >
	<div class='kb-search' >
		<form method="post" action="https://www.a2hosting.com/" target="_blank"  >
			<div class='hiddenFields'>
				<input type="hidden" name="ACT" value="25" />
				<input type="hidden" name="RP" value="kb/results" />
			</div>
			<input type="text" id="kb-search-request" name="keywords" placeholder="Search The A2 Knowledge Base">
			<button class='btn btn-success' type='submit'>Search</button>
		</form>
	</div>
</div>
HTML;



        list($warnings, $num_warnings) = $this->warnings();

        $advanced_circle = "";

        $warning_circle = "";
        if ($num_warnings > 0) {
            $warning_circle = <<<HTML
<span class="badge badge-warning">{$num_warnings}</span>
HTML;
        }

        $settingsGroup = get_class($this) . '-settings-group';
        $description = $this->get_plugin_description();

        if($this->is_a2()) {
            $feedback = <<<HTML
        <div  style="margin:10px 0;" class="alert alert-success">
            We want to hear from you! Please share your thoughts and feedback in our <a href="https://my.a2hosting.com/?m=a2_suggestionbox" target="_blank">Suggestion Box!</a>
        </div>
HTML;
        }
        else {
            $feedback = <<<HTML
        <div  style="margin:10px 0;" class="alert alert-success">
            We want to hear from you! Please share your thoughts and feedback in our wordpress.org <a href="https://wordpress.org/support/plugin/a2-optimized/" target="_blank">support forum!</a>
        </div>
HTML;
        }


        echo <<<HTML


<section id="a2opt-content-general">
	<div  class="wrap">
		<div>
			<div>
				<div>
					<div style="float:left;clear:both">
						<img src="{$image_dir}/a2optimized.png"  style="margin-top:20px" />
					</div>
					<div style="float:right;">
						{$kb_search_box}
					</div>
				</div>
				<div style="clear:both;"></div>
			</div>
			<div >

                <div style="margin:20px 0;">
    				{$optimization_alert}
				</div>
			</div>
		</div>


		<ul class="nav nav-tabs" roll="tablist">
		  <li role="tab" aria-controls="optimization-status" id="li-optimization-status" ><a onclick='document.location.hash="#optimization-status-tab"' href="#optimization-status" data-toggle="tab">Optimization Status {$optimization_circle}</a></li>
		  <li role="tab" aria-controls="optimization-warnings" id="li-optimization-warnings" ><a onclick='document.location.hash="#optimization-warnings-tab"' href="#optimization-warnings" data-toggle="tab">Warnings {$warning_circle}</a></li>
		  <li role="tab" aria-controls="optimization-advanced" id="li-optimization-advanced" ><a onclick='document.location.hash="#optimization-advanced-tab"' href="#optimization-advanced" data-toggle="tab">Advanced Optimizations {$advanced_circle}</a></li>
		  <li role="tab" aria-controls="optimization-about" id="li-optimization-about" ><a onclick='document.location.hash="#optimization-about-tab"' href="#optimization-about" data-toggle="tab">About A2 Optimized</a></li>
		</ul>




		<div class="tab-content">
			<div role="tabpanel" aria-labelledby="li-optimization-status" id="optimization-status" class="tab-pane">
				<h3>Optimization Status</h3>
				<div >
					{$this->optimization_status}
				</div>
			</div>
			<div role="tabpanel" aria-labelledby="li-optimization-warnings" id="optimization-warnings" class="tab-pane">
				<h3>Warnings</h3>
				{$warnings}
			</div>

			<div role="tabpanel" aria-labelledby="li-optimization-advanced" id="optimization-advanced" class="tab-pane">
				<h3>Advanced Optimizations</h3>
					{$this->advanced_optimization_status}
			</div>

            <div role="tabpanel" aria-labelledby="li-optimization-about" id="optimization-about" class="tab-pane">
				<div style="margin:20px 0;">
				    <h3>About A2 Optimized</h3>
                    <p>A2 Optimized was developed by A2 Hosting to make it faster and easier to configure the caching of all aspects of a WordPress site.</p>
                    <p>This free plugin comes with many of the popular Optimizations that come with WordPress hosted at A2 Hosting.</p>
                    <p>To get the full advantage of A2 Optimized, host your site at <a href='https://www.a2hosting.com/wordpress-hosting?utm_source=A2%20Optimized&utm_medium=Referral&utm_campaign=A2%20Optimized' target='_blank'>A2 Hosting</a></p>

				</div>
				<div style="margin:20px 0;">
				    <h3>Free Optimizations</h3>
				    <dt>Page Caching with W3 Total Cache</dt>
                    <dd>
                        <ul>
                            <li>Page Caching stores full copies of pages on the disk so that PHP code and database queries can be skipped by the web server.</li>
                        </ul>
                    </dd>
                    <dt>DB Caching with W3 Total Cache</dt>
                    <dd>
                        <ul>
                            <li>Database cache stores copies of common database queries on disk or in memory to speed up page rendering.</li>
                        </ul>
                    </dd>
                    <dt>Object Caching with W3 Total Cache</dt>
                    <dd>
                        <ul>
                            <li>Object Caching stores commonly used elements such as menus, widgets and forms on disk or in memory to speed up page rendering.</li>
                        </ul>
                    </dd>

                    <dt>Browser Caching with W3 Total Cache</dt>
                    <dd>
                        <ul>
                            <li>Add Rules to the web server to tell the visitor's browser to store a copy of static files to reduce the load time for pages requested after the first page is loaded.</li>
                        </ul>
                    </dd>



                    <dt>Minify HTML Pages</dt>
                    <dd>
                        <ul>
                            <li style="list-style-position: inside">Auto Configure W3 Total Cache to remove excess white space and comments from HTML pages to compress their size.</li>
                            <li>Smaller html pages download faster.</li>
                        </ul>
                    </dd>
                    <dt>Minify CSS Files</dt>
                    <dd>
                        <ul>
                            <li>Auto Configure W3 Total Cache to condense CSS files.</li>
                            <li>Combines multiple css files into a single download.</li>
                            <li>Can provide significant speed imporvements for page loads.</li>
                        </ul>
                    </dd>
                    <dt>Minify JS Files</dt>
                    <dd>
                        <ul>
                            <li>Auto Configure W3 Total Cache to condense JavaScript files into non human-readable compressed files.</li>
                            <li>Combines multiple js files into a single download.</li>
                            <li>Can provide significant speed improvements for page loads.</li>
                        </ul>
                    </dd>
                    <dt>Gzip Compression Enabled</dt>
                    <dd>
                        <ul>
                            <li>Turns on gzip compression using W3 Total Cache.</li>
                            <li>Ensures that files are compressed before sending them to the visitor's browser.</li>
                            <li>Can provide significant speed improvements for page loads.</li>
                            <li>Reduces bandwidth required to serve web pages.</li>
                        </ul>
                    </dd>
                    <dt>Deny Direct Access to Configuration Files and Comment Form</dt>
                    <dd>
                        <ul>
                            <li>Enables WordPress hardening rules in .htaccess to prevent browser access to certain files.</li>
                            <li>Prevents bots from submitting to comment forms.</li>
                            <li>Turn this off if you use systems that post to the comment form without visiting the page.</li>
                        </ul>
                    </dd>
                    <dt>Lock Editing of Plugins and Themes from the WP Admin</dt>
                    <dd>
                        <ul>
                            <li>Turns off the file editor in the wp-admin.</li>
                            <li>Prevents plugins and themes from being tampered with from the wp-admin.</li>
                        </ul>
                    </dd>
				</div>
				<div style="margin:20px 0;">
				    <h3>A2 Hosting Exclusive Optimizations</h3>
				    <p>
				        These one-click optimizations are only available while hosted at A2 Hosting.
                    </p>
				    <dt>Login URL Change</dt>
                    <dd>
                        <ul>
                            <li>Move the login page from the default wp-login.php to a random URL.</li>
                            <li>Prevents bots from automatically brute-force attacking wp-login.php</li>
                        </ul>
                    </dd>
                    <dt>reCAPTCHA on comments and login</dt>
                    <dd>
                        <ul>
                            <li>Provides google reCAPTCHA on both the Login form and comments.</li>
                            <li>Prevents bots from automatically brute-force attacking wp-login.php</li>
                            <li>Prevents bots from automatically spamming comments.</li>
                        </ul>
                    </dd>
                    <dt>Compress Images on Upload</dt>
                    <dd>
                        <ul>
                            <li>Enables and configures EWWW Image Optimizer.</li>
                            <li>Compresses images that are uploaded to save bandwidth.</li>
                            <li>Improves page load times: especially on sites with many images.</li>
                        </ul>
                    </dd>
                    <dt>Turbo Web Hosting</dt>
                    <dd>
                        <ul>
                            <li>Take advantage of A2 Hosting's Turbo Web Hosting platform.</li>
                            <li>Faster serving of static files.</li>
                            <li>Pre-compiled .htaccess files on the web server for imporved performance.</li>
                            <li>PHP OpCode cache enabled by default</li>
                            <li>Custom PHP engine that is faster than Fast-CGI and FPM</li>
                        </ul>
                    </dd>
                    <dt>Memcached Database and Object Cache</dt>
                    <dd>
                        <ul>
                            <li>Database and Object cache in memory instead of on disk.</li>
                            <li>More secure and faster Memcached using Unix socket files.</li>
                            <li>Significant improvement in page load times, especially on pages that can not use full page cache such as wp-admin</li>
                        </ul>
                    </dd>
                </div>
			</div>
		</div>

		$feedback

	</div>

	<div style="clear:both;padding:10px;"></div>
</section>


	<script>
			if(document.location.hash != ""){
				switch(document.location.hash.replace("#","")){
					case 'optimization-status-tab':
						document.getElementById("li-optimization-status").setAttribute("class","active");
						document.getElementById("optimization-status").setAttribute("class","tab-pane active");
						break;
					case 'optimization-warnings-tab':
						document.getElementById("li-optimization-warnings").setAttribute("class","active");
						document.getElementById("optimization-warnings").setAttribute("class","tab-pane active");
						break;
					case "optimization-plugins-tab":
						document.getElementById("li-optimization-plugins").setAttribute("class","active");
						document.getElementById("optimization-plugins").setAttribute("class","tab-pane active");
						break;
					case "optimization-advanced-tab":
						document.getElementById("li-optimization-advanced").setAttribute("class","active");
						document.getElementById("optimization-advanced").setAttribute("class","tab-pane active");
						break;
					case "optimization-about-tab":
						document.getElementById("li-optimization-about").setAttribute("class","active");
						document.getElementById("optimization-about").setAttribute("class","tab-pane active");
						break;
					default:
						document.getElementById("li-optimization-status").setAttribute("class","active");
						document.getElementById("optimization-status").setAttribute("class","tab-pane active");
				}
			}
			else{
				document.getElementById("li-optimization-status").setAttribute("class","active");
				document.getElementById("optimization-status").setAttribute("class","tab-pane active");
			}
		</script>

HTML;


        ini_set('error_reporting', $ini_error_reporting);


    }

    public function get_plugin_status()
    {
        $thisclass = $this;

        $opts = new A2_Optimized_Optimizations($thisclass);
        $this->advanced_optimizations = $opts->get_advanced();
        $this->optimizations = $opts->get_optimizations();
        $this->plugin_list = get_plugins();

        if (isset($_GET['activate'])) {
            foreach ($this->plugin_list as $file => $plugin) {
                if ($_GET['activate'] == $plugin['Name']) {
                    $this->activate_plugin($file);
                }
            }
        }

        if (isset($_GET['hide_login_url'])) {
            $this->addOption('hide_login_url', true);
        }


        if (isset($_GET['deactivate'])) {
            foreach ($this->plugin_list as $file => $plugin) {
                if ($_GET['deactivate'] == $plugin['Name']) {
                    $this->deactivate_plugin($file);
                }
            }
        }

        if (isset($_GET['delete'])) {
            foreach ($this->plugin_list as $file => $plugin) {
                if ($_GET['delete'] == $plugin['Name']) {
                    $this->uninstall_plugin($file);
                }
            }
        }

        if (isset($_GET['disable_optimization'])) {
            $hash = "";


            if (isset($this->optimizations[$_GET['disable_optimization']])) {
                $this->optimizations[$_GET['disable_optimization']]['disable']($_GET['disable_optimization']);
            }

            if (isset($this->advanced_optimizations[$_GET['disable_optimization']])) {
                $this->advanced_optimizations[$_GET['disable_optimization']]['disable']($_GET['disable_optimization']);
                $hash = "#optimization-advanced-tab";
            }

            echo <<<JAVASCRIPT
<script type="text/javascript">
	window.location = 'admin.php?page=A2_Optimized_Plugin_admin{$hash}';
</script>
JAVASCRIPT;
            exit();
        }

        if (isset($_GET['enable_optimization'])) {
            $hash = "";
            if (isset($this->optimizations[$_GET['enable_optimization']])) {
                $this->optimizations[$_GET['enable_optimization']]['enable']($_GET['enable_optimization']);
            }

            if (isset($this->advanced_optimizations[$_GET['enable_optimization']])) {
                $this->advanced_optimizations[$_GET['enable_optimization']]['enable']($_GET['enable_optimization']);
                $hash = "#optimization-advanced-tab";
            }

            echo <<<JAVASCRIPT
<script type="text/javascript">
	window.location = 'admin.php?page=A2_Optimized_Plugin_admin{$hash}';
</script>
JAVASCRIPT;
            exit();
        }


        ini_set('disable_functions', '');

        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

        $plugins_url = plugins_url();
        $plugins_url = explode('/', $plugins_url);
        array_shift($plugins_url);
        array_shift($plugins_url);
        array_shift($plugins_url);
        $this->plugin_dir = ABSPATH . implode('/', $plugins_url);


        $this->plugins_url = plugins_url();


        validate_active_plugins();

        $this->set_install_status('plugins', $this->plugin_list);


    }

    /**
     * A wrapper function delegating to WP add_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param  $value mixed the new value
     * @return null from delegated call to delete_option()
     */
    public function addOption($optionName, $value)
    {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return add_option($prefixedOptionName, $value);
    }

    /**
     * Get the prefixed version input $name suitable for storing in WP options
     * Idempotent: if $optionName is already prefixed, it is not prefixed again, it is returned without change
     * @param  $name string option name to prefix. Defined in settings.php and set as keys of $this->optionMetaData
     * @return string
     */
    public function prefix($name)
    {
        $optionNamePrefix = $this->getOptionNamePrefix();
        if (strpos($name, $optionNamePrefix) === 0) { // 0 but not false
            return $name; // already prefixed
        }
        return $optionNamePrefix . $name;
    }

    public function getOptionNamePrefix()
    {
        return get_class($this) . '_';
    }

    public function deactivate_plugin($file)
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        if (is_plugin_active($file)) {
            deactivate_plugins($file);
            $this->clear_w3_total_cache();
        }
    }

    public function uninstall_plugin($file, $delete = true)
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

        $this->deactivate_plugin($file);
        uninstall_plugin($file);
        if ($delete) {
            delete_plugins(array($file));
        }
        unset($this->plugin_list[$file]);
        $this->clear_w3_total_cache();
    }

    public function set_install_status($name, $value)
    {
        if (!isset($this->install_status)) {
            $this->install_status = new StdClass;
        }
        $this->install_status->{$name} = $value;
    }

    /**
     * Define your options meta data here as an array, where each element in the array
     * @return array of key=>display-name and/or key=>array(display-name, choice1, choice2, ...)
     * key: an option name for the key (this name will be given a prefix when stored in
     * the database to ensure it does not conflict with other plugin options)
     * value: can be one of two things:
     *   (1) string display name for displaying the name of the option to the user on a web page
     *   (2) array where the first element is a display name (as above) and the rest of
     *       the elements are choices of values that the user can select
     * e.g.
     * array(
     *   'item' => 'Item:',             // key => display-name
     *   'rating' => array(             // key => array ( display-name, choice1, choice2, ...)
     *       'CanDoOperationX' => array('Can do Operation X', 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber'),
     *       'Rating:', 'Excellent', 'Good', 'Fair', 'Poor')
     */
    public function getOptionMetaData()
    {
        return array();
    }

    private function curl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }

    /*public function get_litespeed(){
      return get_option('a2_optimized_litespeed');
    }*/

    /*public function set_litespeed($litespeed = true){
      update_option('a2_optimized_litespeed',$litespeed);
    }*/

    function get_optimization_status(&$item)
    {
        if ($item != null) {
            $settings_slug = $this->getSettingsSlug();

            if (isset($item['is_configured'])) {
                $item['is_configured']($item);
            }
            $active_color = 'danger';
            $active_text = 'Not Activated';
            $glyph = 'exclamation-sign';
            $links = array();


            if ($item['configured']) {
                $active_color = 'success';
                $active_text = 'Configured';
                $glyph = 'ok';

                if (isset($item['disable'])) {
                    $links[] = array("?page=$settings_slug&amp;disable_optimization={$item['slug']}", "Disable", "_self");
                }
                if (isset($item['settings'])) {
                    $links[] = array("{$item['settings']}", "Configure", "_self");
                }

                if (isset($item['configured_links'])) {
                    foreach ($item['configured_links'] as $name => $link) {
                        if (gettype($link) == 'array') {
                            $links[] = array($link[0], $name, $link[1]);
                        } else {
                            $links[] = array($link, $name, "_self");
                        }
                    }
                }

            } elseif (isset($item['partially_configured']) && $item['partially_configured']) {
                $active_color = 'warning';
                $active_text = "Partially Configured. {$item['partially_configured_message']}";
                $glyph = 'warning-sign';

                if (isset($item['disable'])) {
                    $links[] = array("?page=$settings_slug&amp;disable_optimization={$item['slug']}", "Disable", "_self");
                }
                if (isset($item['settings'])) {
                    $links[] = array("{$item['settings']}", "Configure", "_self");
                }

                if (isset($item['partially_configured_links'])) {
                    foreach ($item['partially_configured_links'] as $name => $link) {
                        if (gettype($link) == 'array') {
                            $links[] = array($link[0], $name, $link[1]);
                        } else {
                            $links[] = array($link, $name, "_self");
                        }
                    }
                }
            } else {
                if (isset($item['enable'])) {
                    $links[] = array("?page=$settings_slug&amp;enable_optimization={$item['slug']}", "Enable", "_self");
                }

                if (isset($item['not_configured_links'])) {
                    foreach ($item['not_configured_links'] as $name => $link) {
                        if (gettype($link) == 'array') {
                            $links[] = array($link[0], $name, $link[1]);
                        } else {
                            $links[] = array($link, $name, "_self");
                        }
                    }
                }
            }
            if (isset($item['kb'])) {
                $links[] = array($item['kb'], "Learn More", "_blank");
            }
            $link_html = '';
            foreach ($links as $i => $link) {
                if (isset($link[0]) && isset($link[1]) && isset($link[2])) {
                    $link_html .= <<<HTML
	 <a href="{$link[0]}" target="{$link[2]}">{$link[1]}</a> |
HTML;
                }
            }

            $premium = "";
            if (isset($item['premium'])) {
                $premium = '<div style="float:right;padding-right:10px"><a href="https://www.a2hosting.com/wordpress-hosting?utm_source=A2%20Optimized&utm_medium=Referral&utm_campaign=A2%20Optimized" target="_blank" class="a2-exclusive"></a></div>';
            }

            $link_html = rtrim($link_html, "|");

            return <<<HTML
<div class="optimization-item">
	<div class="optimization-item-one" >
		<span class="glyphicon glyphicon-{$glyph}"></span>
	</div>
	<div class="optimization-item-two">
		<b>{$item['name']}</b><br>
		<span class="{$active_color}">{$active_text}</span>
	</div>
	{$premium}
	<div class="optimization-item-three">
		<p>{$item['description']}</p>
	</div>
	<div class="optimization-item-four">
		{$link_html}
	</div>
</div>
HTML;
        }
        return true;
    }

    private function warnings()
    {

        $num_warnings = 0;

        $opts = new A2_Optimized_Optimizations($this);
        $warnings = $opts->get_warnings();

        $warning_html = "";

        foreach ($warnings as $type => $warning_set) {
            switch ($type) {
                case 'Bad WP Options':
                    foreach ($warning_set as $option_name => $warning) {
                        $warn = false;
                        $value = get_option($option_name);
                        switch ($warning['type']) {
                            case 'numeric':
                                switch ($warning['threshold_type']) {
                                    case '>':
                                        if ($value > $warning['threshold']) {
                                            $warning_html .= $this->warning_display($warning);
                                            $num_warnings++;
                                        }
                                        break;
                                    case '<':
                                        if ($value < $warning['threshold']) {
                                            $warning_html .= $this->warning_display($warning);
                                            $num_warnings++;
                                        }
                                        break;
                                    case '=':
                                        if ($value == $warning['threshold']) {
                                            $warning_html .= $this->warning_display($warning);
                                            $num_warnings++;
                                        }
                                        break;
                                }
                                break;
                            case 'text':
                                switch ($warning['threshold_type']) {
                                    case '=':
                                        if ($value == $warning['threshold']) {
                                            $warning_html .= $this->warning_display($warning);
                                            $num_warnings++;
                                        }
                                        break;
                                    case '!=':
                                        if ($value != $warning['threshold']) {
                                            $warning_html .= $this->warning_display($warning);
                                            $num_warnings++;
                                        }
                                        break;
                                }
                                break;
                            case 'array_count':
                                switch ($warning['threshold_type']) {
                                    case '>':
                                        if (is_array($value) && count($value) > $warning['threshold']) {
                                            $warning_html .= $this->warning_display($warning);
                                            $num_warnings++;
                                        }
                                        break;

                                }
                                break;
                        }
                    }
                    break;
                case 'Advanced Warnings':
                    foreach ($warning_set as $name => $warning) {
                        if ($warning['is_warning']()) {
                            $warning_html .= $this->warning_display($warning);
                            $num_warnings++;
                        }
                    }
                    break;
                case 'Bad Plugins':
                    foreach ($warning_set as $plugin_folder => $warning) {
                        $warn = false;
                    }
            }


        }


        $warn = false;
        $plugins = $this->get_plugins();
        foreach ($plugins as $file => $plugin) {
            if (!is_plugin_active($file)) {
                $plugin['file'] = $file;
                $warning_html .= $this->plugin_not_active_warning($plugin);
                $num_warnings++;
            }
        }


        return array($warning_html, $num_warnings);
    }

    private function warning_display($warning)
    {
        return <<<HTML
<div class="optimization-item">
	<div style="float:left;width:44px;font-size:36px">
		<span class="glyphicon glyphicon-exclamation-sign"></span>
	</div>
	<div style="float:left;">
		<b>{$warning['title']}</b><br>
	</div>
	<div style="clear:both;">
		<p>{$warning['description']}</p>
	</div>
	<div>
		<a href="{$warning['config_url']}" >Configure</a>
	</div>
</div>
HTML;
    }

    /*
    public function plugin_list(){
        //Name,PluginURI,Version,Description,Author,AuthorURI,TextDomain,DomainPath,Network,Title,AuthorName

        $string = "";
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        $plugins = $this->get_plugins();
        foreach($plugins as $filename=>$plugin){
            $name = $plugin['Name'];
            $title = $plugin['Title'];
            $checked = "";
            if(is_plugin_active($filename)){
                $checked = "checked='checked'";
            }
            ob_start();
            $dump = ob_get_contents();
            ob_end_clean();
            $string .=<<<HTML
            <div class="wrap">
                <span style="font-size:16pt"><input type="checkbox" $checked> $title</span> <a href="">delete</a>
                {$dump}
            </div>
HTML;

        }
        return $string;
    }*/

    private function plugin_not_active_warning($plugin)
    {
        $manage = "plugins.php?plugin_status=inactive";
        return <<<HTML
<div class="optimization-item">
	<div style="float:left;width:44px;font-size:36px">
		<span class="glyphicon glyphicon-exclamation-sign"></span>
	</div>
	<div style="float:left;">
		<b>Inactive Plugin: {$plugin['Name']}</b><br>
	</div>
	<div style="clear:both;">
		<p>Deactivated plugins should be deleted. Deactivating a plugin does not remove the plugin and its files from your website.  Plugins with security flaws may still affect your site even when not active.</p>
		<p>{$plugin['Description']}</p>
	</div>
	<div>
		<a href="{$manage}" >Manage deactivated plugins</a>
	</div>
</div>
HTML;
    }

    public function get_advanced_optimizations()
    {
        return $this->advanced_optimizations;
    }

    public function set_lockdown($lockdown = true)
    {
        update_option('a2_optimized_lockdown', $lockdown);
    }

    public function set_nomods($lockdown = true)
    {
        update_option('a2_optimized_nomods', $lockdown);
    }

    public function set_deny_direct($deny = true)
    {
        update_option('a2_optimized_deny_direct', $deny);
    }

    public function write_wp_config()
    {

        $lockdown = $this->get_lockdown();


        $nomods = $this->get_nomods();

        touch(ABSPATH . 'wp-config.php');
        copy(ABSPATH . 'wp-config.php', ABSPATH . 'wp-config.php.bak.a2');


        $a2_config = "";
        if ($lockdown) {
            $a2_config = <<<PHP

// BEGIN A2 CONFIG
define('DISALLOW_FILE_EDIT', true);
// END A2 CONFIG
PHP;
        }

        if ($nomods) {
            $a2_config .= <<<PHP

define('DISALLOW_FILE_MODS', true);

PHP;
        }







        $wpconfig = file_get_contents(ABSPATH . 'wp-config.php');
        $pattern = "/[\r\n]*[\/][\/] BEGIN A2 CONFIG.*[\/][\/] END A2 CONFIG[\r\n]*/msU";
        $wpconfig = preg_replace($pattern, '', $wpconfig);

        $wpconfig = str_replace("<?php", "<?php{$a2_config}", $wpconfig);

        //Write the rules to .htaccess
        $fh = fopen(ABSPATH . 'wp-config.php', 'w+');
        fwrite($fh, $wpconfig);
        fclose($fh);

    }

    public function get_lockdown()
    {
        return get_option('a2_optimized_lockdown');
    }

    public function get_nomods()
    {
        return get_option('a2_optimized_nomods');
    }

    public function write_htaccess()
    {


        //make sure .htaccess exists
        touch(ABSPATH . '.htaccess');
        touch(ABSPATH . "404.shtml");
        touch(ABSPATH . "403.shtml");

        //make sure it is writable by owner and readable by everybody
        chmod(ABSPATH . '.htaccess', 0644);


        $home_path = explode("/", str_replace(array("http://", "https://"), "", home_url()), 2);

        if (!isset($home_path[1]) || $home_path[1] == "") {
            $home_path = "/";
        } else {
            $home_path = "/{$home_path[1]}/";
        }

        $a2hardening = "";


        if ($this->get_deny_direct()) {
            //Append the new rules to .htaccess

            //get the path to the WordPress install - nvm
            //$rewrite_base = "/".trim(explode('/',str_replace(array('https://','http://'),'',site_url()),2)[1],"/")."/";


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

        $litespeed = "";


        $htaccess = file_get_contents(ABSPATH . '.htaccess');

        $pattern = "/[\r\n]*# BEGIN WordPress Hardening.*# END WordPress Hardening[\r\n]*/msiU";
        $htaccess = preg_replace($pattern, '', $htaccess);

        $htaccess = <<<HTACCESS
$litespeed
$a2hardening
$htaccess
HTACCESS;

        //Write the rules to .htaccess
        $fp = fopen(ABSPATH . '.htaccess', "c");

        if (flock($fp, LOCK_EX)) {
            ftruncate($fp, 0);      // truncate file
            fwrite($fp, $htaccess);
            fflush($fp);            // flush output before releasing the lock
            flock($fp, LOCK_UN);    // release the lock
        } else {
            //no file lock :(
        }

    }

    public function get_deny_direct()
    {
        return get_option('a2_optimized_deny_direct');
    }


    /**
     * A wrapper function delegating to WP delete_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @return bool from delegated call to delete_option()
     */
    public function deleteOption($optionName)
    {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return delete_option($prefixedOptionName);
    }

    /**
     * A wrapper function delegating to WP add_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param  $value mixed the new value
     * @return null from delegated call to delete_option()
     */
    public function updateOption($optionName, $value)
    {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return update_option($prefixedOptionName, $value);
    }

    /**
     * Checks if a particular user has a role.
     * Returns true if a match was found.
     *
     * @param string $role Role name.
     * @param int $user_id (Optional) The ID of a user. Defaults to the current user.
     * @return bool
     */
    function checkUserRole($role, $user_id = null)
    {
        if (is_numeric($user_id)) {
            $user = get_userdata($user_id);
        } else {
            $user = wp_get_current_user();
        }

        return empty($user) ? false : in_array($role, (array)$user->roles);
    }



    /**
     * A wrapper function delegating to WP get_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param $default string default value to return if the option is not set
     * @return string the value from delegated call to get_option(), or optional default value
     * if option is not set.
     */
    public function getOption($optionName, $default = null)
    {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        $retVal = get_option($prefixedOptionName);
        if (!$retVal && $default) {
            $retVal = $default;
        }
        return $retVal;
    }

    /**
     * @param $roleName string a standard WP role name like 'Administrator'
     * @return bool
     */
    public function isUserRoleEqualOrBetterThan($roleName)
    {
        if ('Anyone' == $roleName) {
            return true;
        }
        $capability = $this->roleToCapability($roleName);
        return $this->checkUserCapability($capability);
    }

    /**
     * Given a WP role name, return a WP capability which only that role and roles above it have
     * http://codex.wordpress.org/Roles_and_Capabilities
     * @param  $roleName
     * @return string a WP capability or '' if unknown input role
     */
    protected function roleToCapability($roleName)
    {
        switch ($roleName) {
            case 'Super Admin':
                return 'manage_options';
            case 'Administrator':
                return 'manage_options';
            case 'Editor':
                return 'publish_pages';
            case 'Author':
                return 'publish_posts';
            case 'Contributor':
                return 'edit_posts';
            case 'Subscriber':
                return 'read';
            case 'Anyone':
                return 'read';
        }
        return '';
    }

    /**
     * Checks if a particular user has a given capability without calling current_user_can.
     * Returns true if a match was found.
     *
     * @param string $capability Capability name.
     * @param int $user_id (Optional) The ID of a user. Defaults to the current user.
     * @return bool
     */
    function checkUserCapability($capability, $user_id = null)
    {
        if (!is_numeric($user_id)) {
            $user_id = wp_get_current_user();
        }
        if (is_numeric($user_id)) {
            $user = get_userdata($user_id);
        } else {
            return false;
        }
        $capabilities = (array)$user->allcaps;
        return empty($user) ? false : isset($capabilities["{$capability}"]) ? $capabilities["{$capability}"] : false;
    }




    private function plugin_display($plugin)
    {


        $links['Delete'] = admin_url() . "admin.php?page=" . $this->getSettingsSlug() . "&delete={$plugin['Name']}";

        $glyph = 'warning-sign';
        if (!$plugin['active']) {
            $glyph = 'exclamation-sign';
            $links['Activate'] = admin_url() . "admin.php?page=" . $this->getSettingsSlug() . "&activate={$plugin['Name']}";
        } else {
            $glyph = 'ok';
            $links['Deactivate'] = admin_url() . "admin.php?page=" . $this->getSettingsSlug() . "&deactivate={$plugin['Name']}";
            if (isset($plugin['config_url'])) {
                $links['Configure'] = $plugin['config_url'];
            }
        }


        $link_html = "";
        foreach ($links as $name => $href) {
            $link_html .= <<<HTML
<a href="{$href}">$name</a> |
HTML;
        }

        $link_html = trim($link_html, " |");


        return <<<HTML
<div class="optimization-item">
	<div style="float:left;width:44px;font-size:36px">
		<span class="glyphicon glyphicon-{$glyph}"></span>
	</div>
	<div style="float:left;">
		<b>{$plugin['Name']}</b><br>
	</div>
	<div style="clear:both;">
		<p>{$plugin['Description']}</p>
	</div>
	<div>
		{$link_html}
	</div>
</div>
HTML;

    }


    protected function is_a2(){
        if( is_dir("/opt/a2-optimized") ){
            return true;
        }
        return false;
    }

    function get_plugin_description()
    {

            $description = <<<HTML

HTML;


        return $description;
    }
}