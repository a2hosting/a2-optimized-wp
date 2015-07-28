<?php


if(is_admin()){
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    class A2_Plugin_Installer_Skin Extends Plugin_Installer_Skin{
        public function feedback($type){}
        public function error($error){}
    }
}


class A2_Optimized_OptionsManager {

    private $optimizations;
    private $advanced_optimizations;
    private $advanced_optimization_status;
    private $optimization_count;
    private $advanced_optimization_count;
    private $plugin_list;
    private $install_status;


    public function get_install_status(){
        return $this->install_status;
    }

    public function set_install_status($name,$value){
        if(!isset($this->install_status)){
            $this->install_status = new StdClass;
        }
        $this->install_status->{$name} = $value;
    }


    public function get_plugins(){
        if(isset($this->plugin_list)){
            return $this->plugin_list;
        }
        else{
            return get_plugins();
        }
    }

    public function install_plugin($slug,$activate = false){
        require_once ABSPATH.'wp-admin/includes/plugin.php';
        require_once ABSPATH.'wp-admin/includes/plugin-install.php';
        $api = plugins_api( 'plugin_information', array( 'slug' => $slug ) );

        $found = false;

        $plugins = $this->get_plugins();

        foreach($plugins as $file=>$plugin){
            if($plugin['Name'] == $api->name){
                $found = true;
            }
        }

        if(!$found) {
            ob_start();
            $upgrader = new Plugin_Upgrader( new A2_Plugin_Installer_Skin(compact('title', 'url', 'nonce', 'plugin', 'api')) );
            $upgrader->install($api->download_link);
            ob_end_clean();
            $this->plugin_list = get_plugins();
        }

        if($activate) {
            $plugins = $this->get_plugins();
            foreach($plugins as $file=>$plugin){
                if($plugin['Name'] == $api->name){
                    $this->activate_plugin($file);
                }
            }
        }

        $this->clear_w3_total_cache();
    }

    public function update_w3tc($vars){
        $vars = array_merge($this->get_w3tc_defaults(),$vars);

        if(!class_exists('W3_ConfigData')){
            $this->enable_w3_total_cache();
        }

        $config_writer = new W3_ConfigWriter(0,false);
        foreach($vars as $name=>$val){
            $config_writer->set($name,$val);
        }
        $config_writer->set('common.instance_id',mt_rand());
        $config_writer->save();
        $this->refresh_w3tc();

    }


    public function set_w3tc_defaults(){
        $vars = $this->get_w3tc_defaults();
        if(!class_exists('W3_ConfigData')){
            $this->enable_w3_total_cache();
        }

        $config_writer = new W3_ConfigWriter(0,false);
        foreach($vars as $name=>$val){
            $config_writer->set($name,$val);
        }
        $config_writer->set('common.instance_id',mt_rand());
        $config_writer->save();
        $this->refresh_w3tc();
    }


    public function get_w3tc_defaults(){
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
            'minify.debug' =>false,
            'dbcache.debug' => false,
            'objectcache.debug' =>false,

            'mobile.enabled'=>true,


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
        /*if($this->get_litespeed()){
          $this->disable_litespeed_cache();
        }*/
        $permalink_structure = get_option('permalink_structure');
        $vars = array();
        //if (!function_exists('apc_store')){
        if($permalink_structure == ''){
            $vars['pgcache.engine']='file';
        }
        else{
            $vars['pgcache.engine']='file_generic';
        }
        $vars['dbcache.engine'] = 'file';
        $vars['objectcache.engine'] = 'file';
        //}
        //else{//apc is available
        //$vars['pgcache.engine']='apc';
        //}

        $vars['objectcache.enabled'] = true;
        $vars['dbcache.enabled'] = true;
        $vars['pgcache.enabled'] = true;
        $vars['browsercache.enabled'] = true;

        $this->update_w3tc($vars);
    }

    public function disable_w3tc_cache(){
        $this->update_w3tc(array(
            'pgcache.enabled'=>false,
            'dbcache.enabled'=>false,
            'objectcache.enabled'=>false,
            'browsercache.enabled'=>false,
        ));
    }


    public function disable_html_minify(){
        $this->update_w3tc(array(
            'minify.html.enable'=>false,
            'minify.html.enabled'=>false,
            'minify.auto'=>false
        ));
    }

    public function enable_html_minify(){
        $this->update_w3tc(array(
            'minify.html.enable'=>true,
            'minify.enabled'=>true,
            'minify.auto'=>false,
            'minify.engine'=> 'file'
        ));
    }


    /*public function enable_litespeed_cache(){
      $this->enable_w3tc_cache();
      $this->update_w3tc(
        array(
          'pgcache.enabled'=>false,
          'varnish.enabled'=>true,
          'varnish.servers'=>array(
            0=>str_replace(array('http://','https://'),'',get_site_url())
          )
        )
      );
      $this->set_litespeed(true);
    $this->write_htaccess();
    }

    public function disable_litespeed_cache(){
      $this->set_litespeed(false);
    $this->write_htaccess();
    $this->update_w3tc(array('varnish.enabled'=>false,'varnish.servers'=>array(0=>'')));
      $w3tc = $this->get_w3tc_config();
      if(!$w3tc['pgcache.enabled'] && $w3tc['objectcache.enabled'] && $w3tc['dbcache.enabled']){
        $this->enable_w3tc_cache();
      }
    }*/




    public function enable_w3_total_cache(){
        $file = 'w3-total-cache/w3-total-cache.php';
        $slug = 'w3-total-cache';
        if(!class_exists('W3_ConfigWriter')){
            $plugins = $this->get_plugins();
            if(isset($plugins[$file])){
                activate_plugin($file);
            }
            else{
                $this->install_plugin($slug);
                $this->activate_plugin($file);
                $this->hit_the_w3tc_page();
            }
        }
    }

    public function clear_w3_total_cache(){
        if(is_plugin_active('w3-total-cache/w3-total-cache.php')){
            /*$ch = curl_init();
            curl_setopt ($ch, CURLOPT_URL, wp_nonce_url('admin.php?page=A2_Optimized_Plugin_admin&w3tc_flush_pgcache','w3tc'));
            curl_setopt ($ch, CURLOPT_HEADER, 1);
            curl_setopt ($ch, CURLOPT_NOBODY, 1);
            curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt ($ch, CURLOPT_NETRC, 1); // omit if you know no urls are FTP links...
            curl_setopt ($ch, CURLOPT_TIMEOUT, 300);
            curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
            ob_start();
            curl_exec ($ch);
            ob_end_clean();
            curl_close($ch);

            $ch = curl_init();
            curl_setopt ($ch, CURLOPT_URL, wp_nonce_url("admin.php?page=A2_Optimized_Plugin_admin",'w3tc'));
            curl_setopt ($ch, CURLOPT_HEADER, 1);
            curl_setopt ($ch, CURLOPT_NOBODY, 1);
            curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt ($ch, CURLOPT_NETRC, 1); // omit if you know no urls are FTP links...
            curl_setopt ($ch, CURLOPT_TIMEOUT, 300);
            curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
            ob_start();
            curl_exec ($ch);
            ob_end_clean();
            curl_close($ch);

            */

        }
    }


    public function hit_the_w3tc_page(){
        $cookie = "";
        foreach($_COOKIE as $name=>$val){
            $cookie .= "{$name}={$val};";
        }
        rtrim($cookie,';');
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, get_admin_url().'admin.php?page=w3tc_general&nonce='.wp_create_nonce('w3tc'));
        curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
        curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt ($ch, CURLOPT_REFERER, get_admin_url());
        $result = curl_exec ($ch);
        curl_close($ch);
    }

    public function curl_save_w3tc($cookie,$url){
        $post ="w3tc_save_options=Save all settings&_wpnonce=".wp_create_nonce('w3tc')."&_wp_http_referer=%2Fwp-admin%2Fadmin.php%3Fpage%3Dw3tc_general%26&w3tc_note%3Dconfig_save";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, get_admin_url().$url);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_REFERER, get_admin_url().$url);
        //curl_setopt($ch, CURLOPT_NOBODY, TRUE); // remove body
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $head = curl_exec($ch);
        curl_close($ch);
    }


    public function refresh_w3tc(){
        $this->hit_the_w3tc_page();
    }

    public function deactivate_plugin($file){
        require_once ABSPATH.'wp-admin/includes/plugin.php';
        if(is_plugin_active($file)){
            deactivate_plugins($file);
            $this->clear_w3_total_cache();
        }
    }

    public function activate_plugin($file){
        require_once ABSPATH.'wp-admin/includes/plugin.php';
        activate_plugin($file);
        $this->clear_w3_total_cache();
    }

    public function uninstall_plugin($file,$delete=true){
        require_once ABSPATH.'wp-admin/includes/plugin.php';
        require_once ABSPATH.'wp-admin/includes/plugin-install.php';

        $this->deactivate_plugin($file);
        uninstall_plugin($file);
        if($delete){
            delete_plugins(array($file));
        }
        unset($this->plugin_list[$file]);
        $this->clear_w3_total_cache();
    }


    public function get_advanced_optimizations(){
        return $this->advanced_optimizations;
    }

    public function get_optimizations(){
        return $this->optimizations;
    }


    public $plugin_dir;



    public function get_plugin_status(){
        $thisclass = $this;



        $this->advanced_optimizations = array(
            'gtmetrix'=>array(
                'slug'=>'gtmetrix',
                'name'=>'GTmetrix',
                'plugin'=>'GTmetrix',
                'plugin_slug'=>'gtmetrix-for-wordpress',
                'file'=>'gtmetrix-for-wordpress/gtmetrix-for-wordpress.php',
                'configured'=>false,
                'partially_configured'=>false,
                'required_options'=>array('gfw_options'=>array('authorized')),
                'description'=>'
      			<p>
					Plugin that actively keeps track of your WP install and sends you alerts if your site falls below certain criteria.  
					The GTMetrix plugin requires an account with <a href="http://gtmetrix.com/" >gtmetrix.com</a>
      			</p>
				<p>
      				<b>Use this plugin only if your site is experiencing issues with slow load times.</b><br><b style="color:red">The GTMetrix plugin will slow down your site.</b>
      			</p>
      			',
                'not_configured_links'=>array(
                ),
                'configured_links'=>array(
                    'Configure GTmetrix'=>'admin.php?page=gfw_settings',
                    'GTmetrix Tests'=>'admin.php?page=gfw_tests',
                ),
                'partially_configured_links'=>array(
                    'Configure GTmetrix'=>'admin.php?page=gfw_settings',
                    'GTmetrix Tests'=>'admin.php?page=gfw_tests',
                ),
                'partially_configured_message'=>'Click &quot;Configure GTmetrix&quot; to enter your GTmetrix Account Email and GTmetrix API Key.',
                'kb'=>'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-w3-total-cache-and-gtmetrix',
                'is_configured'=>function(&$item) use(&$thisclass){
                    $gfw_options = get_option('gfw_options');
                    if(is_plugin_active($item['file']) && isset($gfw_options['authorized']) && $gfw_options['authorized'] == 1){
                        $item['configured'] = true;
                        $thisclass->set_install_status('gtmetrix',true);
                    }
                    elseif(is_plugin_active($item['file'])){
                        $item['partially_configured'] = true;
                    }
                    else{
                        $thisclass->set_install_status('gtmetrix',false);
                    }
                },
                'enable'=>function($slug) use(&$thisclass){
                    $item = $thisclass->get_advanced_optimizations();
                    $item = $item[$slug];
                    if(!isset($thisclass->plugin_list[$item['file']])){
                        $thisclass->install_plugin($item['plugin_slug']);
                    }
                    if(!is_plugin_active($item['file'])){
                        $thisclass->activate_plugin($item['file']);
                    }
                },
                'disable'=>function($slug) use(&$thisclass){
                    $item = $thisclass->get_advanced_optimizations();
                    $item = $item[$slug];
                    $thisclass->deactivate_plugin($item['file']);
                }
            ),
            'P3'=>array(
                'slug'=>'P3',
                'name'=>'P3 (Plugin Performance Profiler)',
                'description'=>'
      			<p>See which plugins are slowing down your site. 
      			This plugin creates a performance report for your site.</p>
      			<p>
      				<b>Use this plugin only if your site is experiencing issues with slow load times.</b><br><b style="color:red">The P3 plugin will slow down your site.</b>
      			</p>
',
                'plugin'=>'P3 Profiler',
                'plugin_slug'=>'p3-profiler',
                'file'=>'p3-profiler/p3-profiler.php',
                'configured'=>false,
                'configured_links'=>array(
                    'Test Performance'=>'tools.php?page=p3-profiler',
                ),
                'kb'=>'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/debugging-wordpress-with-p3-profiler',
                'is_configured'=>function(&$item) use(&$thisclass){
                    if(is_plugin_active($item['file'])){
                        $item['configured'] = true;
                        $thisclass->set_install_status('P3',true);
                    }
                    else{
                        $thisclass->set_install_status('P3',false);
                    }
                },
                'enable'=>function($slug) use(&$thisclass){
                    $item = $thisclass->get_advanced_optimizations();
                    $item = $item[$slug];
                    if(!isset($thisclass->plugin_list[$item['file']])){
                        $thisclass->install_plugin($item['plugin_slug']);
                    }
                    if(!is_plugin_active($item['file'])){
                        $thisclass->activate_plugin($item['file']);
                    }
                },
                'disable'=>function($slug) use(&$thisclass){
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
            'cloudflare'=>array(
                'slug'=>'cloudflare',
                'name'=>'CloudFlare',
                'description'=>'
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
                'plugin'=>'CloudFlare',
                'plugin_slug'=>'cloudflare',
                'configured'=>false,
                'kb'=>'http://www.a2hosting.com/kb/security/application-security/wordpress-security#a-namemethodCloudFlareaMethod-4.3A-Enable-CloudFlare-for-your-site',
                'configured_links'=>array(
                    'Dectivating CloudFlare'=>array('http://www.a2hosting.com/kb/add-on-services/cloudflare/how-to-activate-cloudflare','_blank'),
                    'Configure CloudFlare'=>array('http://www.a2hosting.com/kb/add-on-services/cloudflare/configuring-cloudflare','_blank'),
                    'About CloudFlare'=>array('http://www.cloudflare.com','_blank'),
                ),
                'not_configured_links'=>array(
                    'Activating Cloudflare'=>array('http://www.a2hosting.com/kb/add-on-services/cloudflare/how-to-activate-cloudflare','_blank'),
                    'About CloudFlare'=>array('http://www.cloudflare.com','_blank')
                ),
                'file'=>'cloudflare/cloudflare.php',
                'is_configured'=>function(&$item) use(&$thisclass){

                    $ch = curl_init();
                    curl_setopt ($ch, CURLOPT_URL, home_url());
                    curl_setopt ($ch, CURLOPT_HEADER, 1);
                    curl_setopt ($ch, CURLOPT_NOBODY, 1);
                    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt ($ch, CURLOPT_NETRC, 1); // omit if you know no urls are FTP links...
                    curl_setopt ($ch, CURLOPT_TIMEOUT, 300);
                    curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
                    ob_start();
                    curl_exec ($ch);
                    $header = ob_get_contents();
                    ob_end_clean();

                    curl_close ($ch);
                    $temp_headers = explode("\n",$header);
                    foreach($temp_headers as $i=>$header){
                        $header = explode(":",$header,2);
                        if(isset($header[1])){
                            $headers[$header[0]] = $header[1];
                        }
                        else{
                            $headers[$header[0]] = "";
                        }

                    }
                    if(isset($headers['Server']) && !(strpos(strtolower($headers['Server']),'cloudflare') === false)){
                        $item['configured'] = true;
                        $thisclass->set_install_status('cloudflare',true);
                    }
                    else{
                        $thisclass->set_install_status('cloudflare',false);
                    }


                    //curl() to homepage on www
                }
            )
        );




        /*if(strpos(gethostname(),'ben') === 0 || strpos(gethostname(),'a2s') === 0 || strpos(gethostname(),'ths') === 0  || $_SERVER["SERVER_SOFTWARE"] === 'LiteSpeed'){

          $this->litespeed = array(
            'slug'=>'litespeed',
            'name'=>'LiteSpeed Cache',
            'configured'=>false,
            'description'=>'Take Advantage of the built in caching available with the LiteSpeed web server (available only on Turbo Web Hosting)',
            'is_configured'=>function(&$item) use(&$thisclass){
              $htaccess = file_get_contents(ABSPATH.'.htaccess');
              if(strpos($htaccess,"# BEGIN LiteSpeed Cache") === false){
                if($thisclass->get_litespeed() == true){
                  $thisclass->disable_litespeed_cache();
                }
                //make sure the basic a2-optimized rules are present
                $thisclass->set_install_status('litespeed-cache',false);
              }
              else{
                if(!$thisclass->get_litespeed()){
                  $thisclass->enable_litespeed_cache();
                }
                $item['configured'] = true;
                $thisclass->set_install_status('litespeed-cache',true);
              }
            },
            'enable'=>function(&$item) use(&$thisclass){
              if($_SERVER["SERVER_SOFTWARE"] === 'LiteSpeed'){
                $thisclass->enable_litespeed_cache();
              }
              else{
                add_action( 'a2_notices', array(&$thisclass,'need_litespeed_notice'));
              }
            },
            'disable'=>function()  use(&$thisclass){
               $thisclass->disable_litespeed_cache();
            }
          );
        }
        else{
          $this->litespeed = null;
        }*/







        $this->optimizations = array(
            'cache'=>   array(
                'slug'=>'cache',
                'name'=>'Page Caching with W3 Total Cache',
                'plugin'=>'W3 Total Cache',
                'configured'=>false,
                'description'=>'Utilize W3 Total Cache to make the site faster by caching pages as static content.  Cache: a copy of rendered dynamic pages will be saved by the server so that the next user does not need to wait for the server to generate another copy.',
                'is_configured'=>function(&$item) use(&$thisclass){
                    $w3tc = $thisclass->get_w3tc_config();
                    if($w3tc['pgcache.enabled'] && $w3tc['dbcache.enabled'] && $w3tc['objectcache.enabled'] && $w3tc['browsercache.enabled']){
                        $item['configured'] = true;
                        $permalink_structure = get_option('permalink_structure');
                        $vars = array();
                        if($w3tc['pgcache.engine'] == 'apc'){
                            if($permalink_structure == ''){
                                $vars['pgcache.engine']='file';
                            }
                            else{
                                $vars['pgcache.engine']='file_generic';
                            }
                        }
                        else{
                            if($permalink_structure == '' && $w3tc['pgcache.engine'] != 'file'){
                                $vars['pgcache.engine']='file';
                            }
                            elseif($permalink_structure != '' && $w3tc['pgcache.engine'] == 'file'){
                                $vars['pgcache.engine']='file_generic';
                            }
                        }

                        if(class_exists('W3_Config')){
                            if(class_exists('WooCommerce')){
                                if(array_search('_wc_session_',$w3tc['dbcache.reject.sql']) === false){
                                    $vars['dbcache.reject.sql'] = $w3tc['dbcache.reject.sql'];
                                    $vars['dbcache.reject.sql'][] = '_wc_session_';
                                }
                            }
                        }

                        if( count($vars) != 0 ){
                            $thisclass->update_w3tc($vars);
                        }

                        $thisclass->set_install_status('cache',true);
                    }
                    else{
                        $thisclass->set_install_status('cache',false);
                    }
                },
                'kb'=>'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-w3-total-cache-and-gtmetrix',
                'disable'=>function() use(&$thisclass){
                    $thisclass->disable_w3tc_cache();
                },
                'enable'=>function() use(&$thisclass){
                    $thisclass->enable_w3tc_cache();
                }
            ),
            /*'litespeed'=>$this->litespeed,*/
            'wp-login'=>array(
                'name'=>'Login URL Change',
                'slug'=>'wp-login',
                'plugin'=>'Rename wp-login.php',
                'configured'=>false,
                'kb'=>'http://www.a2hosting.com/kb/security/application-security/wordpress-security#a-namemethodRenameLoginPageaMethod-3.3A-Change-the-WordPress-login-URL',
                'description'=>'',
                'is_configured'=>function(&$item) use(&$thisclass){
                    $plugins = $thisclass->get_plugins();
                    $active_plugins = get_option('active_plugins');
                    if(in_array('rename-wp-login/rename-wp-login.php',$active_plugins)){
                        if($rwl_page = get_option('rwl_page')){
                            if($rwl_page == ''){
                                if($rwl_page = get_option('a2_login_page')){
                                    update_option('rwl_page',$rwl_page);
                                }
                                else{
                                    $rwl_page = "";
                                    $length = 4;
                                    $valid_chars = "abcdefghijklmnopqrstuvwxyz";
                                    // count the number of chars in the valid chars string so we know how many choices we have
                                    $num_valid_chars = strlen($valid_chars);
                                    // repeat the steps until we've created a string of the right length
                                    for ($i = 0; $i < $length; $i++)
                                    {
                                        // pick a random number from 1 up to the number of valid chars
                                        $random_pick = mt_rand(1, $num_valid_chars);
                                        // take the random character out of the string of valid chars
                                        // subtract 1 from $random_pick because strings are indexed starting at 0, and we started picking at 1
                                        $random_char = $valid_chars[$random_pick-1];
                                        // add the randomly-chosen char onto the end of our string so far
                                        $rwl_page .= $random_char;
                                    }
                                    update_option('a2_login_page',$rwl_page);
                                    update_option('rwl_page',$rwl_page);
                                }
                            }
                            else{//sync rwl_page and a2_login_page
                                if($a2_login_page = get_option('a2_login_page')){
                                    if($a2_login_page != $rwl_page){
                                        $this->deleteOption("hide_login_url");
                                        update_option('a2_login_page',$rwl_page);
                                    }
                                }else{
                                    update_option('a2_login_page',$rwl_page);
                                }
                            }
                        }
                        elseif($rwl_page = get_option('a2_login_page')){
                            update_option('rwl_page',$rwl_page);
                        }
                        else{
                            return false;
                        }
                        if(class_exists('W3_ConfigWriter')){
                            $w3tc = $thisclass->get_w3tc_config();
                            if(!array_search($rwl_page,$w3tc['pgcache.reject.uri'])){
                                array_push($w3tc['pgcache.reject.uri'],$rwl_page);
                                $thisclass->update_w3tc(array('pgcache.reject.uri'=>$w3tc['pgcache.reject.uri']));
                            }
                        }
                        $item['configured'] = true;
                        $thisclass->set_install_status('wp-login',true);
                    }
                    else{
                        $thisclass->set_install_status('wp-login',false);
                        return false;
                    }
                    return $item['configured'];
                },
                'disable'=>function() use(&$thisclass){

                    $thisclass->uninstall_plugin('rename-wp-login/rename-wp-login.php');
                    //A2_Optimized_OptionsManager::uninstall('rename-wp-login/rename-wp-login.php');
                    delete_option( 'rwl_admin' );
                    delete_option( 'rwl_redirect' );
                    delete_option( 'rwl_page' );
                },
                'enable'=>function() use(&$thisclass){

                    if(!isset($thisclass->plugin_list['rename-wp-login/rename-wp-login.php'])){
                        $thisclass->install_plugin('rename-wp-login');
                    }
                    $thisclass->activate_plugin('rename-wp-login/rename-wp-login.php');


                    if(get_option('a2_login_page') === false){
                        if(get_option('rwl_page') === false){
                            $rwl_page = "";
                            $length = 4;
                            $valid_chars = "abcdefghijklmnopqrstuvwxyz";
                            // count the number of chars in the valid chars string so we know how many choices we have
                            $num_valid_chars = strlen($valid_chars);
                            // repeat the steps until we've created a string of the right length
                            for ($i = 0; $i < $length; $i++){
                                // pick a random number from 1 up to the number of valid chars
                                $random_pick = mt_rand(1, $num_valid_chars);
                                // take the random character out of the string of valid chars
                                // subtract 1 from $random_pick because strings are indexed starting at 0, and we started picking at 1
                                $random_char = $valid_chars[$random_pick-1];
                                // add the randomly-chosen char onto the end of our string so far
                                $rwl_page .= $random_char;
                            }
                            update_option('a2_login_page',$rwl_page);
                            update_option('rwl_page',$rwl_page);
                        }
                        else{
                            update_option('a2_login_page',get_option('rwl_page'));
                        }
                    }
                    else{
                        update_option('rwl_page',get_option('a2_login_page'));
                    }
                    delete_option( 'rwl_redirect' );
                }
            ),
            'captcha'=>array('name'=>'reCAPTCHA on comments and login',
                'plugin'=>'reCAPTCHA',
                'slug'=>'captcha',
                'configured'=>false,
                'description'=>'Decreases spam and increases site security by adding a CAPTCHA to comment forms and the login screen.  Without a CAPTCHA, bots will easily be able to post comments to you blog or brute force login to your admin panel.',
                'is_configured'=> function(&$item) use(&$thisclass){
                    if(get_option('A2_Optimized_Plugin_recaptcha') == 1){
                        $item['configured'] = true;
                        $thisclass->set_install_status('captcha',true);
                    }
                    else{
                        $thisclass->set_install_status('captcha',false);
                    }
                },
                'enable'=>function() use(&$thisclass){
                    if($thisclass->getOption('recaptcha') === false){
                        $thisclass->addOption('recaptcha',1);
                    }
                    else{
                        $thisclass->updateOption('recaptcha',1);
                    }
                },
                'disable'=>function() use(&$thisclass){
                    $thisclass->updateOption('recaptcha',0);
                }
            ),
            'minify'=>array(
                'name'=>'Minify HTML Pages',
                'slug'=>'minify',
                'plugin'=>'W3 Total Cache',
                'configured'=>false,
                'kb'=>'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-w3-total-cache-and-gtmetrix',
                'description'=>'Removes extra spaces,tabs and line breaks in the HTML to reduce the size of the files sent to the user.',
                'is_configured'=>function(&$item) use(&$thisclass){
                    $w3tc = $thisclass->get_w3tc_config();
                    if($w3tc['minify.enabled']&& $w3tc['minify.html.enable']){
                        $item['configured'] = true;
                        $thisclass->set_install_status('minify-html',true);
                    }
                    else{
                        $thisclass->set_install_status('minify-html',false);
                    }
                },
                'enable'=>function() use(&$thisclass){
                    $thisclass->enable_html_minify();
                },
                'disable'=>function() use(&$thisclass){
                    $thisclass->disable_html_minify();
                }
            ),
            'css_minify'=>array(
                'name'=>'Minify CSS Files',
                'slug'=>'css_minify',
                'plugin'=>'W3 Total Cache',
                'configured'=>false,
                'kb'=>'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-w3-total-cache-and-gtmetrix',
                'description'=>'Makes your site faster by condensing css files into a single downloadable file and by removing extra space in CSS files to make them smaller.',
                'is_configured'=>function(&$item) use(&$thisclass){
                    $w3tc = $thisclass->get_w3tc_config();
                    if($w3tc['minify.css.enable']){
                        $item['configured'] = true;
                        $thisclass->set_install_status('minify-css',true);
                    }
                    else{
                        $thisclass->set_install_status('minify-css',false);
                    }
                },
                'enable'=>function() use(&$thisclass){
                    $thisclass->update_w3tc(array(
                        'minify.css.enable'=>true,
                        'minify.enabled'=>true,
                        'minify.auto'=>0,
                        'minify.engine'=> 'file'
                    ));
                },
                'disable'=>function() use(&$thisclass){
                    $thisclass->update_w3tc(array(
                        'minify.css.enable'=>false,
                        'minify.auto'=>0
                    ));
                }
            ),
            'js_minify'=>array(
                'name'=>'Minify JS Files',
                'slug'=>'js_minify',
                'plugin'=>'W3 Total Cache',
                'configured'=>false,
                'kb'=>'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-w3-total-cache-and-gtmetrix',
                'description'=>'Makes your site faster by condensing JavaScript files into a single downloadable file and by removing extra space in JavaScript files to make them smaller.',
                'is_configured'=>function(&$item) use(&$thisclass){
                    $w3tc = $thisclass->get_w3tc_config();
                    if($w3tc['minify.js.enable']){
                        $item['configured'] = true;
                        $thisclass->set_install_status('minify-js',true);
                    }
                    else{
                        $thisclass->set_install_status('minify-js',false);
                    }
                },
                'enable'=>function() use(&$thisclass){
                    $thisclass->update_w3tc(array(
                        'minify.js.enable'=>true,
                        'minify.enabled'=>true,
                        'minify.auto'=>0,
                        'minify.engine'=> 'file'
                    ));
                },
                'disable'=>function() use(&$thisclass){
                    $thisclass->update_w3tc(array(
                        'minify.js.enable'=>false,
                        'minify.auto'=>0
                    ));
                }

            ),
            'images'=>array(
                'name'=>'Compress Images on Upload',
                'plugin'=>'EWWW Image Optimizer',
                'slug'=>'images',
                'configured'=>false,
                //'kb'=>'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-ewww-image-optimizer',
                'description'=>'Makes your site faster by compressing images to make them smaller.',
                'configured_links'=>array(
                    'Bulk Optimize'=>'upload.php?page=ewww-image-optimizer-bulk',
                ),
                'is_configured'=>function(&$item) use(&$thisclass){
                    if(is_plugin_active("ewww-image-optimizer/ewww-image-optimizer.php")){
                        $item['configured'] = true;
                        $thisclass->set_install_status('compress-images',true);
                    }
                    else{
                        $thisclass->set_install_status('compress-images',false);
                    }
                },
                'enable'=>function() use(&$thisclass){
                    $thisclass->install_plugin('ewww-image-optimizer');
                    $thisclass->activate_plugin('ewww-image-optimizer/ewww-image-optimizer.php');
                },
                'disable'=>function() use(&$thisclass){
                    $thisclass->uninstall_plugin('ewww-image-optimizer/ewww-image-optimizer.php',false);
                }
            ),
            'gzip'=>array(
                'name'=>'Gzip Compression Enabled',
                'slug'=>'gzip',
                'plugin'=>'W3 Total Cache',
                'configured'=>false,
                'kb'=>'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-w3-total-cache-and-gtmetrix',
                'description'=>'Makes your site significantly faster by compressing all text files to make them smaller.',
                'is_configured'=>function(&$item) use(&$thisclass){
                    $w3tc = $thisclass->get_w3tc_config();
                    if($w3tc['browsercache.other.compression']){
                        $item['configured'] = true;
                        $thisclass->set_install_status('gzip',true);
                    }
                    else{
                        $thisclass->set_install_status('gzip',false);
                    }
                },
                'enable'=>function() use(&$thisclass){
                    $thisclass->update_w3tc(array(
                        'browsercache.other.compression'=>true,
                        'browsercache.html.compression'=>true,
                        'browsercache.cssjs.compression'=>true
                    ));
                },
                'disable'=>function() use(&$thisclass){
                    $thisclass->update_w3tc(array(
                        'browsercache.other.compression'=>false,
                        'browsercache.html.compression'=>false,
                        'browsercache.cssjs.compression'=>false
                    ));
                }
            ),
            'htaccess'=>array(
                'name'=>'Deny Direct Access to Configuration Files and Comment Form',
                'slug'=>'htaccess',
                'plugin'=>'A2 Optimized',
                'configured'=>false,
                'kb'=>'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-the-a2-optimized-plugin',
                'description'=>'Protects your configuration files by generating a Forbidden error to web users and bots when trying to access WordPress configuration files. <br> Also prevents POST requests to the site not originating from a user on the site. <br> <span class="danger" >note</span>: if you are using a plugin to allow remote posts and comments, disable this option.',
                'is_configured'=>function(&$item) use(&$thisclass){
                    $htaccess = file_get_contents(ABSPATH.'.htaccess');
                    if(strpos($htaccess,"# BEGIN WordPress Hardening") === false){
                        if($thisclass->get_deny_direct() == true){
                            $thisclass->set_deny_direct(false);
                        }
                        //make sure the basic a2-optimized rules are present
                        $thisclass->set_install_status('htaccess-deny-direct-access',false);
                    }
                    else{
                        if($thisclass->get_deny_direct() == false){
                            $thisclass->set_deny_direct(true);
                        }
                        $item['configured'] = true;
                        $thisclass->set_install_status('htaccess-deny-direct-access',true);
                    }
                },
                'enable'=>function() use(&$thisclass){
                    $thisclass->set_deny_direct(true);
                    $thisclass->write_htaccess();
                },
                'disable'=>function() use(&$thisclass){
                    $thisclass->set_deny_direct(false);
                    $thisclass->write_htaccess();
                }
            ),
            'lock'=>array(
                'name'=>'Lock Editing of Plugins and Themes from the WP Admin',
                'slug'=>'lock',
                'plugin'=>'A2 Optimized',
                'configured'=>false,
                'kb'=>'http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-the-a2-optimized-plugin',
                'description'=>'Prevents exploits that use the built in editing capabilities of the WP Admin',
                'is_configured'=>function(&$item) use(&$thisclass){
                    $wpconfig = file_get_contents(ABSPATH.'wp-config.php');
                    if(strpos($wpconfig,"// BEGIN A2 CONFIG") === false){
                        if($thisclass->get_lockdown() == true){
                            $thisclass->get_lockdown(false);
                        }
                        $thisclass->set_install_status('lock-editing',false);
                    }
                    else{
                        if($thisclass->get_lockdown() == false){
                            $thisclass->set_lockdown(true);
                        }
                        $item['configured'] = true;
                        $thisclass->set_install_status('lock-editing',true);
                    }
                },
                'enable'=>function() use(&$thisclass){
                    $thisclass->set_lockdown(true);
                    $thisclass->write_wp_config();
                },
                'disable'=>function() use(&$thisclass){
                    $thisclass->set_lockdown(false);
                    $thisclass->write_wp_config();
                }
            )
        );


        $this->plugin_list = get_plugins();


        /*foreach($this->plugin_list as $file=>$plugin){
        	require_once ABSPATH.'wp-admin/includes/plugin.php';
			require_once ABSPATH.'wp-admin/includes/plugin-install.php';
			//ob_start();
			//$api = plugins_api( 'plugin_information', array( 'slug' => $slug ) );
        }*/

        if(isset($_GET['activate'])){
            foreach($this->plugin_list as $file=>$plugin){
                if($_GET['activate'] == $plugin['Name']){
                    $this->activate_plugin($file);
                }
            }
        }

        if(isset($_GET['hide_login_url'])){
            $this->addOption('hide_login_url',true);
        }


        if(isset($_GET['deactivate'])){
            foreach($this->plugin_list as $file=>$plugin){
                if($_GET['deactivate'] == $plugin['Name']){
                    $this->deactivate_plugin($file);
                }
            }
        }

        if(isset($_GET['delete'])){
            foreach($this->plugin_list as $file=>$plugin){
                if($_GET['delete'] == $plugin['Name']){
                    $this->uninstall_plugin($file);
                }
            }
        }

        if(isset($_GET['disable_optimization'])){
            $hash = "";



            if(isset($this->optimizations[$_GET['disable_optimization']])){
                $this->optimizations[$_GET['disable_optimization']]['disable']($_GET['disable_optimization']);
            }

            if(isset($this->advanced_optimizations[$_GET['disable_optimization']])){
                $this->advanced_optimizations[$_GET['disable_optimization']]['disable']($_GET['disable_optimization']);
                $hash = "#optimization-advanced-tab";
            }

            echo<<<JAVASCRIPT
<script type="text/javascript">
	window.location = 'admin.php?page=A2_Optimized_Plugin_admin{$hash}';
</script>
JAVASCRIPT;
            exit();
        }

        if(isset($_GET['enable_optimization'])){
            $hash = "";
            if(isset($this->optimizations[$_GET['enable_optimization']])){
                $this->optimizations[$_GET['enable_optimization']]['enable']($_GET['enable_optimization']);
            }

            if(isset($this->advanced_optimizations[$_GET['enable_optimization']])){
                $this->advanced_optimizations[$_GET['enable_optimization']]['enable']($_GET['enable_optimization']);
                $hash = "#optimization-advanced-tab";
            }

            echo<<<JAVASCRIPT
<script type="text/javascript">
	window.location = 'admin.php?page=A2_Optimized_Plugin_admin{$hash}';
</script>
JAVASCRIPT;
            exit();
        }


        ini_set('disable_functions','');

        require_once ABSPATH.'wp-admin/includes/plugin.php';
        require_once ABSPATH.'wp-admin/includes/plugin-install.php';

        $plugins_url = plugins_url();
        $plugins_url = explode('/',$plugins_url);
        array_shift($plugins_url);
        array_shift($plugins_url);
        array_shift($plugins_url);
        $this->plugin_dir = ABSPATH . implode('/',$plugins_url);


        $this->plugins_url = plugins_url();


        validate_active_plugins();

        $this->set_install_status('plugins',$this->plugin_list);
        $this->track();



    }


    /**
     * Creates HTML for the Administration page to set options for this plugin.
     * Override this method to create a customized page.
     * @return void
     */
    public function settingsPage() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access A2 Optimized.', 'a2-optimized'));
        }


        $thisclass = $this;

        $optimization_count = 0;
        $this->get_plugin_status();


        wp_enqueue_style( 'bootstrap', "{$this->plugins_url}/a2-optimized/resource/bootstrap/css/bootstrap.css");
        wp_enqueue_style( 'bootstrap-theme', "{$this->plugins_url}/a2-optimized/resource/bootstrap/css/bootstrap-theme.css");
        wp_enqueue_script( 'bootstrap-theme', "{$this->plugins_url}/a2-optimized/resource/bootstrap/js/bootstrap.js",array('jquery'));


        do_action('a2_notices');

        echo<<<STYLE
<style type='text/css'>
	div #honeypot {display: none;}
	div.kb-search {text-align:center}
	.kb-search input#kb-search-request {font-size: 22px; border-radius: 5px; color: #999; padding: 3px;}
	.kb-search button { font-size: 15px; padding: 8px 14px; vertical-align:top; }
	.btn.large { font-size: 15px; line-height: normal; padding: 9px 14px 9px; -webkit-border-radius: 6px; -moz-border-radius: 6px; border-radius: 6px; }

	.input-large, input.large { width: auto; }
	.input-large, input.large, textarea.large, select.large { width: 210px; }
	input[type=button], input[type=reset], input[type=submit] { width: auto; height: auto; }
	button, input[type="button"], input[type="reset"], input[type="submit"] { cursor: pointer; -webkit-appearance: button; }

	body{
		background-color: transparent !important;
	}

	.big-search .kb-search #kb-search-request {width: 300px;}
	
	
	
	#content-general {
		width:898px;
		border-left:1px solid #caced3; 
		border-right:1px solid #caced3; 
		padding:15px 20px 0 20px; 
		background:#fff url({$this->plugins_url}/a2-optimized/resource/images/background-both.png) no-repeat;
	}
	.fade{
		opacity: 100 !important;
	}
	.checkbox{
		width:24px;
		height:24px;
		float:left;
		background: url('{$this->plugins_url}/a2-optimized/resource/images/icons.png') no-repeat -452px -112px;
	}
	.checkbox.checked{
		background: url('{$this->plugins_url}/a2-optimized/resource/images/icons.png') no-repeat -424px -112px;
	}
	.optimization-status{
		width:260px;
		float:left;
		font-size:1.2em;
	}
	
	.glyphicon-ok{
		color:green;
	}
	
	.glyphicon-warning-sign{
		color:orange;
	}
	.glyphicon-exclamation-sign{
		color:red;
	}
	
	.danger{
		color:red;
	}
	
	.warning{
		color:orange;
	}
	
	.success{
		color:green;
	}
	
	
	
	.badge-warning{
		background-color:orange !important;
	}
	.badge-danger{
		background-color:red !important;
	}
	.badge-success{
		background-color:green !important;
	}
	.badge-default{
		background-color:blue !important;
	}
	
	.optimization-item{
		padding:10px;
		border-width: 0 0 2px 0;
		border-style: solid;
	}
	
	.tab-content{
		background-color:#fff;
		padding:10px;
		border-color: #dddddd;
		border-style: solid;
		border-width: 0 1px 1px 1px;
	}
	

	
</style>
STYLE;






        $ini_error_reporting = ini_get('error_reporting');
        //ini_set('error_reporting',0);



        $optionMetaData = $this->getOptionMetaData();

        $kbpage = $this->curl('https://www.a2hosting.com/kb');
        $csrf_token = 0;


        if(preg_match('/name="csrf_token" value="([a-z0-9]{40})"/' , $kbpage, $csrf_match)){
            $csrf_token = $csrf_match[1];
        }

        $optimization_status = "";


        foreach($this->advanced_optimizations as $shortname=>&$item){
            $this->advanced_optimization_status .= $this->optimization_status($item);
            if($item['configured']){
                $this->advanced_optimization_count++;
            }
        }

        $this->optimization_count = 0;

        foreach($this->optimizations as $shortname=>&$item){
            $this->optimization_status .= $this->optimization_status($item);
            if($item['configured']){
                $this->optimization_count++;
            }
        }

        if($this->optimization_count == count($this->optimizations)){
            $optimization_alert = '<div  class="alert alert-success">Your site has been fully optimized!</div>';
        }
        elseif(!$this->optimizations['cache']['configured']){
            $optimization_alert = '<div  class="alert alert-danger">Your site is NOT optimized!</div>';
        }
        elseif($this->optimization_count > 5){
            $optimization_alert = '<div  class="alert alert-success">Your site has been partially optimized!</div>';
        }
        elseif($this->optimization_count > 2){
            $optimization_alert = '<div  class="alert alert-danger">Your site is barely optimized!</div>';
        }
        else{
            $optimization_alert = '<div  class="alert alert-danger">Your site is NOT optimized!</div>';
        }


        $optimization_number = count($this->optimizations);

        $optimization_circle = "";
        if($optimization_number > 0){
            $optimization_circle = <<<HTML
<span class="badge badge-success">{$this->optimization_count}/{$optimization_number}</span>
HTML;
        }


        /*$reccommended_plugins_text = "";
        foreach($reccommended_plugins as $name=>$arr){
        	if($arr['installed'] && $arr['active']){
        		$reccommended_plugins_text .= "<span class='glyphicon glyphicon-ok'></span> ";
        	}
        	elseif($arr['installed']){
        		$reccommended_plugins_text .= "<span class='glyphicon glyphicon-warning-sign'></span> ";
        	}
        	else{
        		$reccommended_plugins_text .= "<span class='glyphicon glyphicon-exclamation-sign'></span> ";
        	}
        	$reccommended_plugins_text .= "{$name}<br>";
        }
        */



        $kb_search_box=<<<HTML
<div class='big-search' style="margin-top:34px" >
	<div class='kb-search' >
		<form method="post" action="https://www.a2hosting.com/" target="_blank"  >
			<div class='hiddenFields'>
				<input type="hidden" name="ACT" value="25" />
				<input type="hidden" name="RP" value="kb/results" />
				<input type="hidden" name="site_id" value="1" />
				<input type="hidden" name="csrf_token" value="{$csrf_token}" />
			</div>
			<input type="text" id="kb-search-request" name="keywords" placeholder="Search The Knowledge Base">
			<button class='btn btn-success' type='submit'>Search</button>
			<div id='honeypot'><input type='text' class='input'></div>
		</form>
	</div>
</div>
HTML;




        $plugin_html = "";
        /*
        foreach($this->plugin_list as $file=>&$plugin){
        	if($file != "a2-optimized/a2-optimized.php"){
				$plugin['file'] = $file;
				if(is_plugin_active($file)){
					$plugin['active'] = true;
				}
				else{
					$plugin['active'] = false;
				
				}
				$plugin_html .= $this->plugin_display($plugin);
        	}
        }
        
        */

        list($warnings,$num_warnings) = $this->warnings();

        $plugin_circle = "";
        $advanced_circle = "";

        $warning_circle = "";
        if($num_warnings > 0){
            $warning_circle = <<<HTML
<span class="badge badge-warning">{$num_warnings}</span>
HTML;
        }


        $settingsGroup = get_class($this) . '-settings-group';


        echo<<<HTML



<section id="content-general"> 
	<div  class="wrap">
		<div>
			<div>
				<div>
					<div style="float:left;clear:both">
						<img src="{$this->plugins_url}/a2-optimized/resource/images/a2optimized.png"  style="margin-top:20px" />
					</div> 
					<div style="float:right;">
						{$kb_search_box}
					</div>
				</div>
				<div style="clear:both;"></div>
			</div>
			<div >			
				<div style="margin:20px 0;">
					Your WordPress installation has been optimized to run at full 
					speed while hosted at A2 Hosting. You may use the settings on this page
					to further customize and optimize your WordPress site.
				</div>
                <div style="margin:20px 0;">
    				{$optimization_alert}
				</div>
			</div>
		</div>
		

		<ul class="nav nav-tabs">
		  <li role="presentation" id="li-optimization-status" ><a onclick='document.location.hash="#optimization-status-tab"' href="#optimization-status" data-toggle="tab">Optimization Status {$optimization_circle}</a></li>
		  <li role="presentation" id="li-optimization-warnings" ><a onclick='document.location.hash="#optimization-warnings-tab"' href="#optimization-warnings" data-toggle="tab">Warnings {$warning_circle}</a></li>
		  <!--<li role="presentation" id="li-optimization-plugins" ><a onclick='document.location.hash="#optimization-plugins-tab"' href="#optimization-plugins" data-toggle="tab">Installed Plugins {$plugin_circle}</a></li>-->
		  <li role="presentation" id="li-optimization-advanced" ><a onclick='document.location.hash="#optimization-advanced-tab"' href="#optimization-advanced" data-toggle="tab">Advanced Optimizations {$advanced_circle}</a></li>
		</ul>
		
		
		

		<div class="tab-content">
			<div role="tabpanel" id="optimization-status" class="tab-pane">
				<h3>Optimization Status</h3>						
				<div >
					{$this->optimization_status}
				</div>
			</div>
			<div role="tabpanel" id="optimization-warnings" class="tab-pane">
				<h3>Warnings</h3>
				{$warnings}
			</div>
			<!--<div role="tabpanel" id="optimization-plugins" class="tab-pane">
				<h3>Installed Plugins</h3>
					{$plugin_html}
			</div>-->
			
			<div role="tabpanel" id="optimization-advanced" class="tab-pane">
				<h3>Advanced Optimizations</h3>
					{$this->advanced_optimization_status}
				<!--<div class="optimization-status">
					<span class="glyphicon glyphicon-warning-sign"></span> GT-Metrix <a href="" class="glyphicon glyphicon-download"></a><br>
					<span class="glyphicon glyphicon-warning-sign"></span> P3 Profiler <a href="" class="glyphicon glyphicon-download"></a><br>
					<span class="glyphicon glyphicon-warning-sign"></span> Cloudflare <a href="" class="glyphicon glyphicon-download"></a><br>
					<span class="glyphicon glyphicon-ok"></span> 6Scan <a href="" class="glyphicon glyphicon-cog"></a><br>
				</div>-->
			</div>
		
		</div>
		
		
		
		<div  style="margin:10px 0;" class="alert alert-success">
			We want to hear from you! Please share your thoughts and feedback in our <a href="https://my.a2hosting.com/a2-suggestion-box.php" target="_blank">Suggestion Box!</a>
		</div>
		
		
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





        /*  feed not in use at this time Oct 13, 2014 - bcool
        //$xml = $this->curl('http://www.a2hosting.com/kb/wpfeed');
            //require_once("xml2json.php");
            //$jsonContents = "";
            // Convert it to JSON now.
            // xml2json simply takes a String containing XML contents as input.
            //$jsonContents = xml2json::transformXmlStringToJson($xml);
            //$feed = json_decode($jsonContents);
            //$arr = $feed->{rss}->{channel}->{item};
            */

        ini_set('error_reporting',$ini_error_reporting);



    }

    public function get_lockdown(){
        return get_option('a2_optimized_lockdown');
    }

    public function set_lockdown($lockdown = true){
        update_option('a2_optimized_lockdown',$lockdown);
    }


    public function get_deny_direct(){
        return get_option('a2_optimized_deny_direct');
    }

    public function set_deny_direct($deny = true){
        update_option('a2_optimized_deny_direct',$deny);
    }

    /*public function get_litespeed(){
      return get_option('a2_optimized_litespeed');
    }*/

    /*public function set_litespeed($litespeed = true){
      update_option('a2_optimized_litespeed',$litespeed);
    }*/

    public function write_wp_config(){

        $lockdown = $this->get_lockdown();
        touch(ABSPATH.'wp-config.php');
        copy(ABSPATH.'wp-config.php',ABSPATH.'wp-config.php.bak.a2');

        $a2_config = '';
        if($lockdown){
            $a2_config =<<<PHP

// BEGIN A2 CONFIG
define('DISALLOW_FILE_EDIT', true);
// END A2 CONFIG
PHP;
        }

        $wpconfig = file_get_contents(ABSPATH.'wp-config.php');
        $pattern = "/[\r\n]*[\/][\/] BEGIN A2 CONFIG.*[\/][\/] END A2 CONFIG[\r\n]*/msU";
        $wpconfig = preg_replace($pattern,'',$wpconfig);

        $wpconfig = str_replace("<?php","<?php{$a2_config}",$wpconfig);

        //Write the rules to .htaccess
        $fh = fopen(ABSPATH.'wp-config.php','w+');
        fwrite($fh,$wpconfig);
        fclose($fh);

    }





    public function write_htaccess(){


        //make sure .htaccess exists
        touch(ABSPATH.'.htaccess');
        touch(ABSPATH."404.shtml");
        touch(ABSPATH."403.shtml");

        //make sure it is writable by owner and readable by everybody
        chmod(ABSPATH.'.htaccess', 0644);




        $home_path = explode("/",str_replace(array("http://","https://"),"",home_url()),2);

        if(!isset($home_path[1]) || $home_path[1] == ""){
            $home_path = "/";
        }
        else{
            $home_path = "/{$home_path[1]}/";
        }

        $relative_content_dir = str_replace(ABSPATH,"",WP_CONTENT_DIR);
        $relative_plugin_dir = str_replace(ABSPATH,"",WP_PLUGIN_DIR);
        $resource_pattern = "{$relative_plugin_dir}/a2-optimized/resource/(.*)";





        $a2htaccess  =<<<APACHE

# BEGIN A2 Optimized
# DO NOT REMOVE
<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteRule {$resource_pattern} {$relative_content_dir}/a2-resource.php?resource=$1 [L]
</IfModule>
ErrorDocument 403 /403.shtml
# END A2 Optimized
APACHE;


        $a2hardening = "";



        if($this->get_deny_direct()){
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

        /*$login_uri = preg_replace("/[^\/]+\/\??(.+)/","$1",str_replace("http://","",trim(wp_login_url(),"/")));
    
        if($this->get_litespeed()){
          $litespeed=<<<HTACCESS

# BEGIN LiteSpeed Cache
<IfModule LiteSpeed>
  RewriteEngine On
  RewriteCond %{REQUEST_METHOD} ^HEAD|GET|PURGE$
  RewriteCond %{REQUEST_URI} !.*admin.* [NC]
  RewriteCond %{REQUEST_URI} !.*login.* [NC]
  RewriteCond %{REQUEST_URI} !.*{$login_uri}.* [NC]
  RewriteCond %{HTTP_COOKIE} !^.*wordpress_logged_in.*$ [NC]
  RewriteRule .* - [E=Cache-Control:max-age=900]
</IfModule>
# END LiteSpeed Cache
HTACCESS;
        }
        */

        $htaccess = file_get_contents(ABSPATH.'.htaccess');

        $pattern = "/[\r\n]*# BEGIN A2 Optimized.*# END A2 Optimized[\r\n]*/msiU";
        $htaccess = preg_replace($pattern,'',$htaccess);
        //$pattern = "/[\r\n]*# BEGIN LiteSpeed Cache.*# END LiteSpeed Cache[\r\n]*/msiU";
        //$htaccess = preg_replace($pattern,'',$htaccess);
        $pattern = "/[\r\n]*# BEGIN WordPress Hardening.*# END WordPress Hardening[\r\n]*/msiU";
        $htaccess = preg_replace($pattern,'',$htaccess);

        $htaccess =<<<HTACCESS
$a2htaccess
$litespeed
$a2hardening
$htaccess
HTACCESS;

        //Write the rules to .htaccess
        $fp = fopen(ABSPATH.'.htaccess', "c");

        if (flock($fp, LOCK_EX)) {
            ftruncate($fp, 0);      // truncate file
            fwrite($fp, $htaccess);
            fflush($fp);            // flush output before releasing the lock
            flock($fp, LOCK_UN);    // release the lock
        }
        else{
            //no file lock :(
        }

    }




    function optimization_status(&$item){
        if($item != null){
            $settings_slug = $this->getSettingsSlug();

            if(isset($item['is_configured'])){
                $item['is_configured']($item);
            }
            $active_color = 'danger';
            $active_text = 'Not Activated';
            $glyph = 'exclamation-sign';
            $links = array();


            if($item['configured']){
                $active_color = 'success';
                $active_text = 'Configured';
                $glyph = 'ok';

                if(isset($item['disable'])){
                    $links[] = array("?page=$settings_slug&disable_optimization={$item['slug']}","Disable","_self");
                }
                if(isset($item['settings'])){
                    $links[] = array("{$item['settings']}","Configure","_self");
                }

                if(isset($item['configured_links'])){
                    foreach($item['configured_links'] as $name=>$link){
                        if(gettype($link) == 'array' ){
                            $links[] = array($link[0],$name,$link[1]);
                        }
                        else{
                            $links[] = array($link,$name,"_self");
                        }
                    }
                }

            }
            elseif(isset($item['partially_configured']) && $item['partially_configured']){
                $active_color = 'warning';
                $active_text = "Partially Configured. {$item['partially_configured_message']}";
                $glyph = 'warning-sign';

                if(isset($item['disable'])){
                    $links[] = array("?page=$settings_slug&disable_optimization={$item['slug']}","Disable","_self");
                }
                if(isset($item['settings'])){
                    $links[] = array("{$item['settings']}","Configure","_self");
                }

                if(isset($item['partially_configured_links'])){
                    foreach($item['partially_configured_links'] as $name=>$link){
                        if(gettype($link) == 'array' ){
                            $links[] = array($link[0],$name,$link[1]);
                        }
                        else{
                            $links[] = array($link,$name,"_self");
                        }
                    }
                }
            }
            else{
                if(isset($item['enable'])){
                    $links[] = array("?page=$settings_slug&enable_optimization={$item['slug']}","Enable","_self");
                }

                if(isset($item['not_configured_links'])){
                    foreach($item['not_configured_links'] as $name=>$link){
                        if(gettype($link) == 'array' ){
                            $links[] = array($link[0],$name,$link[1]);
                        }
                        else{
                            $links[] = array($link,$name,"_self");
                        }
                    }
                }
            }
            if(isset($item['kb'])){
                $links[] = array($item['kb'],"Learn More","_blank");
            }
            $link_html = '';
            foreach($links as $i=>$link){
                if(isset($link[0]) && isset($link[1]) && isset($link[2])){
                    $link_html .=<<<HTML
	 <a href="{$link[0]}" target="{$link[2]}">{$link[1]}</a> |
HTML;
                }
            }

            $link_html = rtrim($link_html,"|");

            return <<<HTML
<div class="optimization-item">
	<div style="float:left;width:44px;font-size:36px">
		<span class="glyphicon glyphicon-{$glyph}"></span>
	</div>
	<div style="float:left;">
		<b>{$item['name']}</b><br>
		<span class="{$active_color}">{$active_text}</span>
	</div>
	<div style="clear:both;">
		<p>{$item['description']}</p>
	</div>
	<div>
		{$link_html}
	</div>
</div>
HTML;
        }
        return true;
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

    private function curl($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }

    private function warnings(){

        $num_warnings = 0;
        $warnings = array(
            'Bad WP Options'=>array(
                'posts_per_page'=>array(
                    'title'=>'Recent Post Limit',
                    'description'=>'The number of recent posts per page is set greater than five. This could be slowing down page loads.',
                    'type'=>'numeric',
                    'threshold_type'=>'>',
                    'threshold'=>5,
                    'config_url'=>admin_url().'options-reading.php'
                ),
                'posts_per_rss'=>array(
                    'title'=>'RSS Post Limit',
                    'description'=>'The number of posts from external feeds is set greater than 5. This could be slowing down page loads.',
                    'type'=>'numeric',
                    'threshold_type'=>'>',
                    'threshold'=>5,
                    'config_url'=>admin_url().'options-reading.php'
                ),
                'show_on_front'=>array(
                    'title'=>'Recent Posts showing on home page',
                    'description'=>'Speed up your home page by selecting a static page to display.',
                    'type'=>'text',
                    'threshold_type'=>'=',
                    'threshold'=>'posts',
                    'config_url'=>admin_url().'options-reading.php'
                ),
                'permalink_structure'=>array(
                    'title'=>'Permalink Structure',
                    'description'=>'To fully optimize page caching with "Disk Enhanced" mode:<br>you must set a permalink structure other than "Default".',
                    'type'=>'text',
                    'threshold_type'=>'=',
                    'threshold'=>'',
                    'config_url'=>admin_url().'options-permalink.php'
                )
            ),
            'Advanced Warnings'=>array(
                'themes'=>array(
                    'is_warning'=>function(){
                        $themes = wp_get_themes();
                        switch( count($themes) ){
                            case 1:
                                return false;
                            case 2:
                                $theme = wp_get_theme();
                                if($theme->get('Template') != '') {
                                    return false;
                                }
                        }
                        return true;
                    },
                    'title'=>'Unused Themes',
                    'description'=>'One or more unused themes are installed. Unused themes should be deleted.  For more information read the Wordpress.org Codex on <a target="_blank" href="http://codex.wordpress.org/WordPress_Housekeeping#Theme_Housekeeping">WordPress Housekeeping</a>',
                    'config_url'=>admin_url().'themes.php'
                )
            ),
            'Bad Plugins'=>array(
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


        $warning_html = "";

        foreach($warnings as $type=>$warning_set){
            switch($type){
                case 'Bad WP Options':
                    foreach($warning_set as $option_name=>$warning){
                        $warn = false;
                        $value = get_option($option_name);
                        switch($warning['type']){
                            case 'numeric':
                                switch($warning['threshold_type']){
                                    case '>':
                                        if($value > $warning['threshold']){
                                            $warning_html .= $this->warning_display($warning);
                                            $num_warnings++;
                                        }
                                        break;
                                    case '<':
                                        if($value < $warning['threshold']){
                                            $warning_html .= $this->warning_display($warning);
                                            $num_warnings++;
                                        }
                                        break;
                                    case '=':
                                        if($value == $warning['threshold']){
                                            $warning_html .= $this->warning_display($warning);
                                            $num_warnings++;
                                        }
                                        break;
                                }
                                break;
                            case 'text':
                                switch($warning['threshold_type']){
                                    case '=':
                                        if($value == $warning['threshold']){
                                            $warning_html .= $this->warning_display($warning);
                                            $num_warnings++;
                                        }
                                        break;
                                    case '!=':
                                        if($value != $warning['threshold']){
                                            $warning_html .= $this->warning_display($warning);
                                            $num_warnings++;
                                        }
                                        break;
                                }
                                break;
                            case 'array_count':
                                switch($warning['threshold_type']){
                                    case '>':
                                        if(is_array($value) && count($value) > $warning['threshold']){
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
                    foreach($warning_set as $name=>$warning){
                        if($warning['is_warning']()){
                            $warning_html .= $this->warning_display($warning);
                            $num_warnings++;
                        }
                    }
                    break;
                case 'Bad Plugins':
                    foreach($warning_set as $plugin_folder=>$warning){
                        $warn = false;
                    }
            }



        }


        $warn = false;
        $plugins = $this->get_plugins();
        foreach($plugins as $file=>$plugin){
            if(!is_plugin_active($file)){
                $plugin['file'] = $file;
                $warning_html .= $this->plugin_not_active_warning($plugin);
                $num_warnings++;
            }
        }


        return array($warning_html,$num_warnings);
    }


    public function banned_plugin_notice(){
        echo<<<HTML
    <div class="error">
        <p class="danger">The Plugin you are trying to install has been flagged as incompatible with A2 Optimized.</p>
    </div>
HTML;

    }


    public function config_page_notice(){
        echo<<<HTML
    <div class="updated">
        <p>This site has been configured using the A2 Optimized plugin.  We at A2 hosting have spent quite a bit of time figuring out the best set of options for this plugin; however, if you think you know better: by all means... Continue.  If you have arrived here by mistake, you may use the <a href="admin.php?page=A2_Optimized_Plugin_admin">A2 Optimized administration page to configure this plugin</a>.</p>
    </div>
HTML;

    }


    private function plugin_display($plugin){



        $links['Delete'] = admin_url()."admin.php?page=".$this->getSettingsSlug()."&delete={$plugin['Name']}";

        $glyph = 'warning-sign';
        if(!$plugin['active']){
            $glyph = 'exclamation-sign';
            $links['Activate'] = admin_url()."admin.php?page=".$this->getSettingsSlug()."&activate={$plugin['Name']}";
        }
        else{
            $glyph = 'ok';
            $links['Deactivate'] = admin_url()."admin.php?page=".$this->getSettingsSlug()."&deactivate={$plugin['Name']}";
            if(isset($plugin['config_url'])){
                $links['Configure'] = $plugin['config_url'];
            }
        }



        $link_html = "";
        foreach($links as $name=>$href){
            $link_html .=<<<HTML
<a href="{$href}">$name</a> | 
HTML;
        }

        $link_html = trim($link_html," |");


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



    private function plugin_not_active_warning($plugin){
        $manage = "plugins.php?plugin_status=inactive";
        return <<<HTML
<div class="optimization-item">
	<div style="float:left;width:44px;font-size:36px">
		<span class="glyphicon glyphicon-exclamation-sign"></span>
	</div>
	<div style="float:left;">
		<b>{$plugin['Name']} is not Active</b><br>
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

    private function warning_display($warning){
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


    function get_plugin_slug($uri){
        return array_pop(explode('/',trim($uri,'/')));
    }

    public function getOptionNamePrefix() {
        return get_class($this) . '_';
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
    public function getOptionMetaData() {
        return array();
    }

    /**
     * @return array of string name of options
     */
    public function getOptionNames() {
        return array_keys($this->getOptionMetaData());
    }

    /**
     * Override this method to initialize options to default values and save to the database with add_option
     * @return void
     */
    protected function initOptions() {
    }

    /**
     * Cleanup: remove all options from the DB
     * @return void
     */
    protected function deleteSavedOptions() {
        $optionMetaData = $this->getOptionMetaData();
        if (is_array($optionMetaData)) {
            foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
                $prefixedOptionName = $this->prefix($aOptionKey); // how it is stored in DB
                delete_option($prefixedOptionName);
            }
        }
    }

    /**
     * @return string display name of the plugin to show as a name/title in HTML.
     * Just returns the class name. Override this method to return something more readable
     */
    public function getPluginDisplayName() {
        return get_class($this);
    }

    /**
     * Get the prefixed version input $name suitable for storing in WP options
     * Idempotent: if $optionName is already prefixed, it is not prefixed again, it is returned without change
     * @param  $name string option name to prefix. Defined in settings.php and set as keys of $this->optionMetaData
     * @return string
     */
    public function prefix($name) {
        $optionNamePrefix = $this->getOptionNamePrefix();
        if (strpos($name, $optionNamePrefix) === 0) { // 0 but not false
            return $name; // already prefixed
        }
        return $optionNamePrefix . $name;
    }

    /**
     * Remove the prefix from the input $name.
     * Idempotent: If no prefix found, just returns what was input.
     * @param  $name string
     * @return string $optionName without the prefix.
     */
    public function &unPrefix($name) {
        $optionNamePrefix = $this->getOptionNamePrefix();
        if (strpos($name, $optionNamePrefix) === 0) {
            return substr($name, strlen($optionNamePrefix));
        }
        return $name;
    }

    /**
     * A wrapper function delegating to WP get_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param $default string default value to return if the option is not set
     * @return string the value from delegated call to get_option(), or optional default value
     * if option is not set.
     */
    public function getOption($optionName, $default = null) {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        $retVal = get_option($prefixedOptionName);
        if (!$retVal && $default) {
            $retVal = $default;
        }
        return $retVal;
    }

    /**
     * A wrapper function delegating to WP delete_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @return bool from delegated call to delete_option()
     */
    public function deleteOption($optionName) {
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
    public function addOption($optionName, $value) {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return add_option($prefixedOptionName, $value);
    }

    /**
     * A wrapper function delegating to WP add_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param  $value mixed the new value
     * @return null from delegated call to delete_option()
     */
    public function updateOption($optionName, $value) {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return update_option($prefixedOptionName, $value);
    }

    /**
     * A Role Option is an option defined in getOptionMetaData() as a choice of WP standard roles, e.g.
     * 'CanDoOperationX' => array('Can do Operation X', 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber')
     * The idea is use an option to indicate what role level a user must minimally have in order to do some operation.
     * So if a Role Option 'CanDoOperationX' is set to 'Editor' then users which role 'Editor' or above should be
     * able to do Operation X.
     * Also see: canUserDoRoleOption()
     * @param  $optionName
     * @return string role name
     */
    public function getRoleOption($optionName) {
        $roleAllowed = $this->getOption($optionName);
        if (!$roleAllowed || $roleAllowed == '') {
            $roleAllowed = 'Administrator';
        }
        return $roleAllowed;
    }



    /**
     * Checks if a particular user has a role.
     * Returns true if a match was found.
     *
     * @param string $role Role name.
     * @param int $user_id (Optional) The ID of a user. Defaults to the current user.
     * @return bool
     */
    function checkUserRole( $role, $user_id = null ) {
        if (is_numeric( $user_id )){
            $user = get_userdata( $user_id );
        }
        else{
            $user = wp_get_current_user();
        }

        return empty( $user ) ? false : in_array( $role, (array) $user->roles );
    }


    /**
     * Checks if a particular user has a given capability without calling current_user_can.
     * Returns true if a match was found.
     *
     * @param string $capability Capability name.
     * @param int $user_id (Optional) The ID of a user. Defaults to the current user.
     * @return bool
     */
    function checkUserCapability( $capability, $user_id = null ) {
        if (! is_numeric( $user_id )){
            $user_id = wp_get_current_user();
        }
        if (is_numeric( $user_id )) {
            $user = get_userdata($user_id);
        }
        else{
            return false;
        }
        $capabilities = (array) $user->allcaps;
        return empty( $user ) ? false : isset($capabilities["{$capability}"]) ? $capabilities["{$capability}"] : false;
    }



    /**
     * Given a WP role name, return a WP capability which only that role and roles above it have
     * http://codex.wordpress.org/Roles_and_Capabilities
     * @param  $roleName
     * @return string a WP capability or '' if unknown input role
     */
    protected function roleToCapability($roleName) {
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
     * @param $roleName string a standard WP role name like 'Administrator'
     * @return bool
     */
    public function isUserRoleEqualOrBetterThan($roleName) {
        if ('Anyone' == $roleName) {
            return true;
        }
        $capability = $this->roleToCapability($roleName);
        return $this->checkUserCapability($capability);
    }

    /**
     * @param  $optionName string name of a Role option (see comments in getRoleOption())
     * @return bool indicates if the user has adequate permissions
     */
    public function canUserDoRoleOption($optionName) {
        $roleAllowed = $this->getRoleOption($optionName);
        if ('Anyone' == $roleAllowed) {
            return true;
        }
        return $this->isUserRoleEqualOrBetterThan($roleAllowed);
    }

    /**
     * see: http://codex.wordpress.org/Creating_Options_Pages
     * @return void
     */
    public function createSettingsMenu() {
        $pluginName = $this->getPluginDisplayName();
        //create new top-level menu
        add_menu_page($pluginName . ' Plugin Settings',
            $pluginName,
            'administrator',
            get_class($this),
            array(&$this, 'settingsPage')
        /*,plugins_url('/images/icon.png', __FILE__)*/); // if you call 'plugins_url; be sure to "require_once" it

        //call register settings function
        add_action('admin_init', array(&$this, 'registerSettings'));
    }

    public function registerSettings() {
        $settingsGroup = get_class($this) . '-settings-group';
        $optionMetaData = $this->getOptionMetaData();
        foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
            register_setting($settingsGroup, $aOptionMeta);
        }
    }



    /**
     * Helper-function outputs the correct form element (input tag, select tag) for the given item
     * @param  $aOptionKey string name of the option (un-prefixed)
     * @param  $aOptionMeta mixed meta-data for $aOptionKey (either a string display-name or an array(display-name, option1, option2, ...)
     * @param  $savedOptionValue string current value for $aOptionKey
     * @return void
     */
    protected function createFormControl($aOptionKey, $aOptionMeta, $savedOptionValue) {
        if (is_array($aOptionMeta) && count($aOptionMeta) >= 2) { // Drop-down list
            $choices = array_slice($aOptionMeta, 1);
            ?>
            <p><select name="<?php echo $aOptionKey ?>" id="<?php echo $aOptionKey ?>">
                    <?php
                    foreach ($choices as $aChoice) {
                        $selected = ($aChoice == $savedOptionValue) ? 'selected' : '';
                        ?>
                        <option value="<?php echo $aChoice ?>" <?php echo $selected ?>><?php echo $this->getOptionValueI18nString($aChoice) ?></option>
                    <?php
                    }
                    ?>
                </select></p>
        <?php

        }
        else { // Simple input field
            ?>
            <p><input type="text" name="<?php echo $aOptionKey ?>" id="<?php echo $aOptionKey ?>"
                      value="<?php echo esc_attr($savedOptionValue) ?>" size="50"/></p>
        <?php

        }
    }

    /**
     * Override this method and follow its format.
     * The purpose of this method is to provide i18n display strings for the values of options.
     * For example, you may create a options with values 'true' or 'false'.
     * In the options page, this will show as a drop down list with these choices.
     * But when the the language is not English, you would like to display different strings
     * for 'true' and 'false' while still keeping the value of that option that is actually saved in
     * the DB as 'true' or 'false'.
     * To do this, follow the convention of defining option values in getOptionMetaData() as canonical names
     * (what you want them to literally be, like 'true') and then add each one to the switch statement in this
     * function, returning the "__()" i18n name of that string.
     * @param  $optionValue string
     * @return string __($optionValue) if it is listed in this method, otherwise just returns $optionValue
     */
    protected function getOptionValueI18nString($optionValue) {
        switch ($optionValue) {
            case 'true':
                return __('true', 'a2-optimized');
            case 'false':
                return __('false', 'a2-optimized');

            case 'Administrator':
                return __('Administrator', 'a2-optimized');
            case 'Editor':
                return __('Editor', 'a2-optimized');
            case 'Author':
                return __('Author', 'a2-optimized');
            case 'Contributor':
                return __('Contributor', 'a2-optimized');
            case 'Subscriber':
                return __('Subscriber', 'a2-optimized');
            case 'Anyone':
                return __('Anyone', 'a2-optimized');
        }
        return $optionValue;
    }

    /**
     * Query MySQL DB for its version
     * @return string|false
     */
    protected function getMySqlVersion() {
        global $wpdb;
        $rows = $wpdb->get_results('select version() as mysqlversion');
        if (!empty($rows)) {
            return $rows[0]->mysqlversion;
        }
        return false;
    }
}
