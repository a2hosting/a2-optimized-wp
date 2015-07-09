<?php
include_once('A2_Optimized_LifeCycle.php');


error_reporting(E_ERROR);

class A2_Optimized_Plugin extends A2_Optimized_LifeCycle {



    /**
     * See: http://plugin.michael-simpson.com/?page_id=31
     * @return array of option meta data.
     */
    public function getOptionMetaData() {
        //  http://plugin.michael-simpson.com/?page_id=31
        return array(
            '_version' => array('Installed Version'), // Leave this one commented-out. Uncomment to test upgrades.
            'recaptcha' => array('reCaptcha'),
            //'ATextInput' => array(__('Enter in some text', 'my-awesome-plugin')),
            //'Donated' => array(__('I have donated to this plugin', 'my-awesome-plugin'), 'false', 'true'),
            //'CanSeeSubmitData' => array(__('Can See Submission data', 'my-awesome-plugin'),
            //                            'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber', 'Anyone')
        );
    }

//    protected function getOptionValueI18nString($optionValue) {
//        $i18nValue = parent::getOptionValueI18nString($optionValue);
//        return $i18nValue;
//    }

    protected function initOptions() {
        $options = $this->getOptionMetaData();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr > 1)) {
                    $this->addOption($key, $arr[1]);
                }
            }
        }
    }





    public function getPluginDisplayName() {
        return 'A2 Optimized';
    }



    protected function getMainPluginFileName() {
        return 'a2-optimized.php';
    }



    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Called by install() to create any database tables if needed.
     * Best Practice:
     * (1) Prefix all table names with $wpdb->prefix
     * (2) make table names lower case only
     * @return void
     */
    protected function installDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
        //            `id` INTEGER NOT NULL");
    }



    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Drop plugin-created tables on uninstall.
     * @return void
     */
    protected function unInstallDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("DROP TABLE IF EXISTS `$tableName`");
    }


    public function activate(){
        touch(ABSPATH.'403.shtml');
        $this->write_htaccess();

        $files = $this->get_tracking_files();
        if(is_multisite()){
            foreach($files['a2_optimized_mu_files'] as $file){
                $json = json_decode(file_get_contents($file));
                $json->disabled = false;
                $fh = fopen($file,'w+');
                fwrite($fh,json_encode($json));
                fclose($fh);
            }
        }
        else{
            ob_start();
            $this->get_plugin_status();
            ob_end_clean();
        }
    }

    public function deactivate(){

        //remove lines from .htaccess

        $htaccess = file_get_contents(ABSPATH.'.htaccess');

        $pattern = "/[\r\n]*# BEGIN A2 Optimized.*# END A2 Optimized[\r\n]*/msiU";
        $htaccess = preg_replace($pattern,'',$htaccess);
        $pattern = "/[\r\n]*# BEGIN WordPress Hardening.*# END WordPress Hardening[\r\n]*/msiU";
        $htaccess = preg_replace($pattern,'',$htaccess);

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

        $files = $this->get_tracking_files();
        if(is_multisite()){
            foreach($files['a2_optimized_mu_files'] as $file){
                $json = json_decode(file_get_contents($file));
                $json->disabled = true;
                $fh = fopen($file,'w+');
                fwrite($fh,json_encode($json));
                fclose($fh);
            }
        }
        else{
            $json = json_decode(file_get_contents($files['a2_optimized_file']));
            $json->disabled = true;
            $fh = fopen($files['a2_optimized_file'],'w+');
            fwrite($fh,json_encode($json));
            fclose($fh);
        }
    }


    /**
     * Perform actions when upgrading from version X to version Y
     * See: http://plugin.michael-simpson.com/?page_id=35
     * @return void
     */
    public function upgrade() {
        /*if(is_admin()){
            $upgrade_ok = true;
            global $code_version, $saved_version;
            
            $code_version = $this->getVersion();
            $saved_version = $this->getVersionSaved();
            if ($this->isSavedVersionLessThan($code_version)) {
                switch($saved_version){
                    case '0.3.4.5':
                        mkdir(WP_PLUGIN_DIR . '/a2-optimized/images/');
                        copy( dirname(__FILE__) . '/images/a2optimized.png',WP_PLUGIN_DIR.'/a2-optimized/images/a2optimized.png');
                        copy( dirname(__FILE__) . '/images/background-left.png',WP_PLUGIN_DIR.'/a2-optimized/images/background-left.png');
                        copy( dirname(__FILE__) . '/images/background-right.png',WP_PLUGIN_DIR.'/a2-optimized/images/background-right.png');
                    case '0.3.4.6':
                        $this_dir = dirname(__FILE__);
                        $plugin_dir = WP_PLUGIN_DIR;
                        //mkdir("{$plugin_dir}/a2-optimized/bootstrap/");
                        exec("cp -R {$this_dir}/bootstrap/ {$plugin_dir}/a2-optimized/");
                    case '0.3.4.7':
                        copy( dirname(__FILE__) . '/images/background-both.png',WP_PLUGIN_DIR.'/a2-optimized/images/background-both.png');
                    case '0.3.4.9':
                        
                }
                
                add_action( 'admin_notices', array(&$this,'update_notice'));
                $this->saveInstalledVersion();
                
            }          
              
            
            
            
        }*/
    }


    function update_notice() {
        global $code_version, $saved_version;
        echo<<<HTML
    <div class="updated">
        <p>
HTML;
        _e( "A2 Optimized has been Updated from {$saved_version} to {$code_version} !", 'a2-text-domain' );
        echo<<<HTML
        </p>
    </div>
HTML;
    }


    public function login_captcha(){

        include_once('recaptchalib.php');


        $a2_recaptcha = $this->getOption('recaptcha');
        if($a2_recaptcha == 1 ){
            $captcha = a2recaptcha_get_html("6LdoEPQSAAAAAIXao_gJk8QotRtcjQ8vOabKzuG6",null,true);
            echo<<<HTML
            <style>
              #recaptcha_area, #recaptcha_table{
                margin-left: -12px !important;
              }
            </style>
    
            {$captcha}
HTML;
        }
    }


    public function comment_captcha(){
        if(!$this->checkUserCapability('moderate_comments', get_current_user_id() )){
            include_once('recaptchalib.php');

            $a2_recaptcha = $this->getOption('recaptcha');
            if($a2_recaptcha == 1){
                $captcha = a2recaptcha_get_html("6LdoEPQSAAAAAIXao_gJk8QotRtcjQ8vOabKzuG6",null,true);
                echo<<<HTML
							<style>
								#recaptcha_area{
									margin: 10px auto !important;
								}
							</style>
		
							{$captcha}
HTML;
            }
        }
    }

    public function captcha_authenticate($user,$username,$password){
        if($username != '' && !(defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)){
            $a2_recaptcha = $this->getOption('recaptcha');
            if($a2_recaptcha == 1){
                include_once('recaptchalib.php');
                $privatekey = "6LdoEPQSAAAAABSp-Ef1QjmrotS-ssXrczHb9-4B";
                $resp = a2recaptcha_check_answer ($privatekey,
                    $_SERVER["REMOTE_ADDR"],
                    $_POST["recaptcha_challenge_field"],
                    $_POST["recaptcha_response_field"]);

                if(!empty($username)){
                    if (!$resp->is_valid) {
                        remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
                        //wp_die("<strong>The reCAPTCHA wasn't entered correctly. Go back and try it again.</strong>: (reCAPTCHA said: {$resp->error})");
                        return new WP_Error('recaptcha_error', "<strong>The reCAPTCHA wasn't entered correctly. Please try it again.</strong>");
                    }
                }
            }
        }
    }

    public function captcha_comment_authenticate($commentdata) {

        if(!$this->checkUserCapability('moderate_comments', get_current_user_id() ) && !(defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)){
            include_once('recaptchalib.php');

            $a2_recaptcha = $this->getOption('recaptcha');
            if($a2_recaptcha == 1){
                $privatekey = "6LdoEPQSAAAAABSp-Ef1QjmrotS-ssXrczHb9-4B";
                $resp = a2recaptcha_check_answer ($privatekey,
                    $_SERVER["REMOTE_ADDR"],
                    $_POST["recaptcha_challenge_field"],
                    $_POST["recaptcha_response_field"]);

                if(!empty($commentdata)){
                    if (!$resp->is_valid) {
                        wp_die("<strong>The reCAPTCHA wasn't entered correctly. Please use your browsers back button and try again.</strong>");
                    }
                }
                else{
                    wp_die("<strong>There was an error. Please try again.</strong>");
                }
            }
        }
        return $commentdata;
    }


    public function permalink_changed(){

        $cookie = "";
        foreach($_COOKIE as $name=>$val){
            $cookie .= "{$name}={$val};";
        }
        rtrim($cookie,';');
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, get_admin_url().'admin.php?page=A2_Optimized_Plugin_admin');
        curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
        curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt ($ch, CURLOPT_REFERER, get_admin_url());
        $result = curl_exec ($ch);
        curl_close($ch);

    }

    public function addActionsAndFilters() {

        add_action('permalink_structure_changed',array(&$this,'permalink_changed'));

        $date = date("Y-m-d");
        if(strpos($_SERVER['REQUEST_URI'],"login-{$date}") > 0){
            add_action('template_redirect', array(&$this,'get_moved_login'));
        }

        /*
        add_filter( 'allow_minor_auto_core_updates', '__return_true' );
        add_filter( 'allow_major_auto_core_updates', '__return_true' );
        add_filter( 'auto_update_plugin', '__return_true' );
        add_filter( 'auto_update_theme', '__return_true' );
        add_filter( 'auto_update_translation', '__return_true' );
        */

        //if(!function_exists('wp_get_current_user')){
        //  include_once ABSPATH.WPINC.'/pluggable.php';
        //}


        //if(is_admin()){
        //if(current_user_can('manage_options')){


        if( is_admin() ) {
            add_filter('admin_init', array(&$this,'admin_init'));
            add_action( 'admin_bar_menu', array(&$this,'addAdminBar'), 8374 );
            add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));
            if( defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT ){
                add_action('admin_menu', array(&$this, 'addLockedEditor'),100,100);
            }
            add_action( 'admin_print_styles', array(&$this,'myStyleSheet') );
            add_action( 'wp_dashboard_setup', array(&$this,'dashboard_widget') );
            $a2_plugin_basename = plugin_basename($GLOBALS['A2_Plugin_Dir'].'/a2-optimized.php');
            add_filter("plugin_action_links_{$a2_plugin_basename}", array( &$this, 'plugin_settings_link') );
        }


        if(get_option('A2_Optimized_Plugin_recaptcha',0) == 1  && !is_admin()){
            add_action('woocommerce_login_form', array( &$this, 'login_captcha' ) );
            add_action('login_form', array( &$this, 'login_captcha' ) );
            add_filter('authenticate', array( &$this, 'captcha_authenticate'),1,3);
            add_action('comment_form_after_fields', array(&$this, 'comment_captcha'));
            add_filter('preprocess_comment', array(&$this, 'captcha_comment_authenticate'),1,3);
        }
        //add_action('switch_theme', array(&$this, 'theme_check'));
        //add_action('update-custom_install-theme', array(&$this, 'theme_check'));
        //add_action('update-custom_upload-theme', array(&$this, 'theme_check'));



        // Example adding a script & style just for the options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
            //wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));
            //wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));




        }


        // Add Actions & Filters
        // http://plugin.michael-simpson.com/?page_id=37


        // Adding scripts & styles to all pages
        // Examples:
        //        wp_enqueue_script('jquery');
        //        wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));


        // Register short codes
        // http://plugin.michael-simpson.com/?page_id=39


        // Register AJAX hooks
        // http://plugin.michael-simpson.com/?page_id=41
    }

    public function plugin_settings_link($links) {
        $settings_link = '<a href="admin.php?page=A2_Optimized_Plugin_admin">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }


    public function get_moved_login(){
        wp_redirect(wp_login_url(),302);
        exit();
    }


    public function myStyleSheet() {

        echo<<<CSS
   <style type="text/css">
        #edge-mode{
            display:none !important;
        }
        #gfw-hosting-meta-box{
            display:none !important;
        }
        img[title=Logo]{
            display:none;
        }
   </style>
CSS;
    }





    /**
     * Add a widget to the dashboard.
     *
     * This function is hooked into the 'wp_dashboard_setup' action below.
     */
    public function dashboard_widget() {

        $logo_url = plugins_url()."/a2-optimized/resource/images/a2optimized.png";

        wp_add_dashboard_widget(
            'a2_optimized',         // Widget slug.
            "<a href=\"admin.php?page=A2_Optimized_Plugin_admin\"><img src=\"{$logo_url}\" /></a>",         // Title.
            array(&$this,'a2_dashboard_widget') // Display function.
        );

        wp_add_dashboard_widget(
            'a2_optimized_kb',         // Widget slug.
            "Have any questions? Search the A2 Hosting Knowledge Base for answers.",         // Title.
            array(&$this,'kb_dashboard_widget') // Display function.
        );

        //force the widget to the top of the dashboard


        global $wp_meta_boxes;

        // Get the regular dashboard widgets array
        // (which has our new widget already but at the end)

        unset($wp_meta_boxes['dashboard']['normal']['core']['wp_welcome_widget']);

        $normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
        // Backup and delete our new dashboard widget from the end of the array
        $example_widget_backup = array( 'a2_optimized' => $normal_dashboard['a2_optimized'], 'a2_optimized_kb' => $normal_dashboard['a2_optimized_kb'] );




        // Merge the two arrays together so our widget is at the beginning
        $sorted_dashboard = array_merge( $example_widget_backup, $normal_dashboard );
        // Save the sorted array back into the original metaboxes
        $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
    }

    /**
     * Create the function to output the contents of our Dashboard Widget.
     */
    public function a2_dashboard_widget() {
        // Display whatever it is you want to show.





        echo<<<HTML

    <div style="font-size:14px">
        <p style="font-size:14px">A2 Optimized will automatically configure your WordPress site for speed and security.</p>
        <p>
            <a class="button button-primary" href="admin.php?page=A2_Optimized_Plugin_admin">Optimize Your Site</a>
        </p>
        
        <p style="font-size:14px">A2 Optimized includes these features.</p>
        <ul style="list-style-type:disc;list-style-position:inside">
            <li>Page caching</li>
            <li>Database caching</li>
            <li>CSS/JS/HTML minification</li>
            <li>reCAPTCHA on comment and login forms</li>
            <li>Move the login page</li>
            <li>Image compression</li>
            <li>Compress pages with gzip</li>
        </ul>
        
        <p style="font-size:14px">To learn more about the A2 Optimized WordPress plugin: read this <a target="_blank" href="http://www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-the-a2-optimized-plugin">Knowledge Base article</a></p>
    </div>

    

HTML;
    }


    public function kb_dashboard_widget(){
        echo<<<HTML
		<p>
    	<a class="button button-primary" href="http://www.a2hosting.com/kb" target="_blank">Search the Knowledge Base</a>
		</p>
HTML;
    }



    /* this function is a stub
    public function theme_check($newtheme){
        $za = new ZipArchive(); 
        
        $za->open('theZip.zip'); 
        
        for( $i = 0; $i < $za->numFiles; $i++ ){ 
            $stat = $za->statIndex( $i ); 
            print_r( basename( $stat['name'] ) . PHP_EOL ); 
        }
    }
    */


    public function locked_files_notice(){
        echo<<<HTML
<div id="editing-locked" class="updated" >
     <p ><b style="color:#00CC00">Editing of plugin and theme files</b> in the wp-admin is <b style="color:#00CC00">disabled</b> by A2 Optimized<br> 
     <b style="color:#00CC00">This is recommended for security reasons</b>. You can modify this setting on the <a href="admin.php?page=A2_Optimized_Plugin_admin">A2 Optimized Configuration page</a></p>
</div>
HTML;
    }

    public function recaptcha_installed_notice(){
        echo<<<HTML
<div id="recaptcha-installed" class="error" >
     <p ><b style="color:#00CC00">A ReCaptacha plugin is installed.</b><br>
     Disable and delete any plugins using reCaptcha to use the reCaptcha functionality built into A2 Optimized.
     <br> </p>
</div>
HTML;
    }




    public function not_locked_files_notice(){
        echo<<<HTML
<div id="editing-locked" class="error" >
     <p ><b style="color:red">Editing of plugin and theme files</b> in the wp-admin is <b style="color:red">enabled</b><br> 
     <b style="color:red">This is not recommended for security reasons</b>. You can modify this setting on the <a href="admin.php?page=A2_Optimized_Plugin_admin">A2 Optimized Configuration page</a></p>
</div>
HTML;
    }

    public function rwl_notice(){
        $rwl_page = get_option('rwl_page');
        $home_page = get_home_url();
        $admin_url = get_admin_url();

        if($a2_login_page = get_option('a2_login_page')){//synch rwl_page and a2_login_page
            if($a2_login_page != $rwl_page){
                update_option('a2_login_page',$rwl_page);
            }
        }
        else{
            update_option('a2_login_page',$rwl_page);
        }

        $link = wp_login_url();

        if(! (strpos(get_option('a2_login_bookmarked',''),$link) === 0 )){
            echo<<<HTML
<div id="bookmark-login" class="updated" >
  <p>Your login page is now here: <a href="{$link}" >{$link}</a>. Bookmark this page!</p>
</div>
HTML;
        }
    }



    private $config_pages = array(
        'w3tc_dashboard',
        'w3tc_general',
        'w3tc_pgcache',
        'w3tc_minify',
        'w3tc_dbcache',
        'w3tc_objectcache',
        'w3tc_browsercache',
        'w3tc_mobile',
        'w3tc_referer',
        'w3tc_cdn',
        'w3tc_monitoring',
        'w3tc_extensions',
        'w3tc_install',
        'w3tc_about',
        'w3tc_faq'
    );

    private $banned_plugins = array(
        'wp-super-cache',
        'wp-fastest-cache',
        'wp-file-cache',
        'better-wp-security',
        'wordfence'
    );


    private function touch_tracking_files(){
        $tracking_files = $this->get_tracking_files();
        if(!is_multisite()){
            touch($tracking_files['a2_optimized_file']);
        }
        else{
            foreach($tracking_files['a2_optimized_mu_files'] as $file){
                touch($file);
            }
        }
    }

    public function admin_init(){

        if(!$this->checkUserCapability('manage_options',get_current_user_id())){
            return false;
        }

        $this->touch_tracking_files();

        $active_plugins = get_option('active_plugins');
        if(in_array('rename-wp-login/rename-wp-login.php',$active_plugins)){
            if($rwl_page = get_option('rwl_page')){
                if($rwl_page != ''){
                    add_action( 'admin_notices', array(&$this,'rwl_notice'));
                    if($a2_login_page = get_option('a2_login_page')){
                        if($a2_login_page != $rwl_page){
                            update_option('a2_login_page',$rwl_page);
                        }
                    }
                    else{
                        update_option('a2_login_page',$rwl_page);
                    }
                }
            }
        }



        if(!file_exists(WP_CONTENT_DIR."/a2-resource.php")){
            $fp = fopen(WP_CONTENT_DIR."/a2-resource.php","w+");
            $loc = dirname(__FILE__)."/resource.php";

            $file_contents =<<<SCRIPT
<?php
    
  require '{$loc}';
    
SCRIPT;

            fwrite($fp,$file_contents);
            fclose($fp);

        }





        if(isset($_GET['page']) &&  in_array($_GET['page'],$this->config_pages) ){
            add_action( 'admin_notices', array(&$this,'config_page_notice'));
        }
        if(isset($_GET['notice'])){
            switch($_GET['notice']){
                case 'banned_plugin':
                    add_action( 'admin_notices', array(&$this,'banned_plugin_notice'));
                    break;

            }
        }
        if(isset( $_GET['action']) &&  $_GET['action']=='install-plugin'){
            if(isset($_GET['plugin']) && in_array($_GET['plugin'],$this->banned_plugins)){
                require_once ABSPATH.'wp-includes/pluggable.php';
                wp_redirect("admin.php?page=A2_Optimized_Plugin_admin&notice=banned_plugin&plugin={$_GET['plugin']}", 302 );
                exit();
            }
        }

        //we don't need this function anymore since the new reCaptcha is now compatible with other recaptcha plugins
        //if(function_exists('recaptcha_get_html')){  
        //add_action( 'admin_notices', array(&$this,'recaptcha_installed_notice'));
        //}



        if(!(strpos( $_SERVER['SCRIPT_FILENAME'] , 'plugins.php') === false) && defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT){
            add_action( 'admin_notices', array(&$this,'locked_files_notice'));
        }
        else if(!(strpos( $_SERVER['SCRIPT_FILENAME'] ,'plugins.php') === false)){
            add_action( 'admin_notices', array(&$this,'not_locked_files_notice'));
        }



    }

}