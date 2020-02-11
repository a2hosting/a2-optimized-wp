<?php

/*
	Author: Benjamin Cool
	Author URI: https://www.a2hosting.com/
	License: GPLv2 or Later
*/

// Prevent direct access to this file
if ( ! defined( 'WPINC' ) ) {
	die;
}

include_once('A2_Optimized_OptionsManager.php');

class A2_Optimized_Plugin extends A2_Optimized_OptionsManager {
	const optionInstalled = '_installed';
	const optionVersion = '_version';
	private $config_pages = array(
		'w3tc_dashboard',
		'w3tc_general',
		'w3tc_pgcache',
		'w3tc_minify',
		'w3tc_dbcache',
		'w3tc_objectcache',
		'w3tc_browsercache',
		'w3tc_mobile',
		'w3tc_mobile',
		'w3tc_referer',
		'w3tc_cdn',
		'w3tc_monitoring',
		'w3tc_extensions',
		'w3tc_install',
		'w3tc_about',
		'w3tc_faq'
	);

	//list of plugins that may conflict, displays a notice on installation of these plugins
	private $incompatible_plugins = array(
		'wp-super-cache',
		'wp-fastest-cache',
		'wp-file-cache',
		'better-wp-security',
	);

	public function install() {
		// Initialize Plugin Options
		$this->initOptions();

		// Initialize DB Tables used by the plugin
		$this->installDatabaseTables();

		// Other Plugin initialization - for the plugin writer to override as needed
		//$this->otherInstall();

		// Record the installed version
		$this->saveInstalledVersion();

		// To avoid running install() more then once
		$this->markAsInstalled();
	}

	protected function initOptions() {
		$options = $this->getOptionMetaData();
		if (!empty($options)) {
			foreach ($options as $key => $arr) {
				if (is_array($arr) && count($arr) > 1) {
					$this->addOption($key, $arr[1]);
				}
			}
		}
	}

	public function getOptionMetaData() {
		//  http://plugin.michael-simpson.com/?page_id=31
		return array(
			//'_version' => array('Installed Version'), // Leave this one commented-out. Uncomment to test upgrades.
			'recaptcha' => array('reCaptcha'),
			//'ATextInput' => array(__('Enter in some text', 'my-awesome-plugin')),
			//'CanSeeSubmitData' => array(__('Can See Submission data', 'my-awesome-plugin'),
			//                            'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber', 'Anyone')
		);
	}

	protected function installDatabaseTables() {
		//        global $wpdb;
		//        $tableName = $this->prefixTableName('mytable');
		//        $wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
		//            `id` INTEGER NOT NULL");
	}

	protected function saveInstalledVersion() {
		$this->setVersionSaved($this->getVersion());
	}

	protected function setVersionSaved($version) {
		return $this->updateOption(self::optionVersion, $version);
	}

	public function getVersion() {
		return $this->getPluginHeaderValue('Version');
	}

	public function getPluginHeaderValue($key) {
		// Read the string from the comment header of the main plugin file
		$data = file_get_contents($this->getPluginDir() . DIRECTORY_SEPARATOR . $this->getMainPluginFileName());
		$match = array();
		preg_match('/' . $key . ':\s*(\S+)/', $data, $match);
		if (count($match) >= 1) {
			return $match[1];
		}

		return null;
	}

	protected function getPluginDir() {
		return dirname(__FILE__);
	}

	protected function getMainPluginFileName() {
		return 'a2-optimized.php';
	}

	protected function markAsInstalled() {
		return $this->updateOption(self::optionInstalled, true);
	}

	public function uninstall() {
		$this->markAsUnInstalled();
	}

	protected function markAsUnInstalled() {
		return $this->deleteOption(self::optionInstalled);
	}

	public function activate() {
		touch(ABSPATH . '403.shtml');
		$this->write_htaccess();
	}

	public function deactivate() {
		//remove lines from .htaccess

		$htaccess = file_get_contents(ABSPATH . '.htaccess');

		$pattern = "/[\r\n]*# BEGIN WordPress Hardening.*# END WordPress Hardening[\r\n]*/msiU";
		$htaccess = preg_replace($pattern, '', $htaccess);

		//Write the rules to .htaccess
		$fp = fopen(ABSPATH . '.htaccess', 'c');

		if (flock($fp, LOCK_EX)) {
			ftruncate($fp, 0);	  // truncate file
			fwrite($fp, $htaccess);
			fflush($fp);			// flush output before releasing the lock
			flock($fp, LOCK_UN);	// release the lock
		} else {
			//no file lock :(
		}
	}

	public function upgrade() {
		if (file_exists(ABSPATH . 'wp-config.php.bak.a2')) {
			unlink(ABSPATH . 'wp-config.php.bak.a2');
		}
	}

	public function update_notice() {
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

	public function login_captcha() {
		if (file_exists('/opt/a2-optimized/wordpress/recaptchalib_v2.php') && !$this->is_a2_managed()) {
			include_once('/opt/a2-optimized/wordpress/recaptchalib_v2.php');

			$a2_recaptcha = $this->getOption('recaptcha');
			if ($a2_recaptcha == 1) {
				$captcha = a2recaptcha_get_html();
				echo <<<HTML
                <style>
                  .g-recaptcha{
                    position: relative;
                    top: -6px;
                    left: -15px;
                  }
                </style>

                {$captcha}
HTML;
			}
		}
	}

	public function comment_captcha() {
		if (!$this->checkUserCapability('moderate_comments', get_current_user_id() )) {
			if (file_exists('/opt/a2-optimized/wordpress/recaptchalib_v2.php')) {
				include_once('/opt/a2-optimized/wordpress/recaptchalib_v2.php');

				$a2_recaptcha = $this->getOption('recaptcha');
				if ($a2_recaptcha == 1) {
					$captcha = a2recaptcha_get_html();
					echo <<<HTML

                                {$captcha}
HTML;
				}
			}
		}
	}

	public function captcha_authenticate($user, $username, $password) {
		if ($username != '' && !(defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) && !$this->is_a2_managed()) {
			$a2_recaptcha = $this->getOption('recaptcha');
			if ($a2_recaptcha == 1) {
				if (file_exists('/opt/a2-optimized/wordpress/recaptchalib_v2.php')) {
					include_once('/opt/a2-optimized/wordpress/recaptchalib_v2.php');
					$resp = a2recaptcha_check_answer($_POST['g-recaptcha-response']);

					if (!empty($username)) {
						if (!$resp) {
							remove_filter('authenticate', 'wp_authenticate_username_password', 20);

							return new WP_Error('recaptcha_error', "<strong>The reCAPTCHA wasn't entered correctly. Please try it again.</strong>");
						}
					}
				}
			}
		}
	}

	public function captcha_comment_authenticate($commentdata) {
		if (!$this->checkUserCapability('moderate_comments', get_current_user_id()) && !(defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)) {
			if (file_exists('/opt/a2-optimized/wordpress/recaptchalib_v2.php')) {
				include_once('/opt/a2-optimized/wordpress/recaptchalib_v2.php');

				$a2_recaptcha = $this->getOption('recaptcha');
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
		}

		return $commentdata;
	}

	public function permalink_changed() {
		$cookie = '';
		foreach ($_COOKIE as $name => $val) {
			$cookie .= "{$name}={$val};";
		}
		rtrim($cookie, ';');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, get_admin_url() . 'admin.php?page=A2_Optimized_Plugin_admin');
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6');
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		curl_setopt($ch, CURLOPT_REFERER, get_admin_url());
		$result = curl_exec($ch);
		curl_close($ch);
	}
	
	public function addActionsAndFilters() {
		add_action('permalink_structure_changed', array(&$this, 'permalink_changed'));

		$date = date('Y-m-d');
		if (strpos($_SERVER['REQUEST_URI'], "login-{$date}") > 0) {
			add_action('template_redirect', array(&$this, 'get_moved_login'));
		}

		add_filter( 'allow_minor_auto_core_updates', '__return_true' );
		add_filter('auto_update_translation', '__return_true');
		/*add_filter( 'allow_major_auto_core_updates', '__return_true' );
		add_filter( 'allow_minor_auto_core_updates', '__return_true' );
		add_filter( 'auto_update_plugin', '__return_true' );
		add_filter( 'auto_update_theme', '__return_true' );
		add_filter( 'auto_update_translation', '__return_true' );
		*/

		if (is_admin()) {
			add_filter('admin_init', array(&$this, 'admin_init'));
			add_action('admin_bar_menu', array(&$this, 'addAdminBar'), 8374);
			add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));
			if (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT) {
				add_action('admin_menu', array(&$this, 'addLockedEditor'), 100, 100);
			}
			add_action('admin_print_styles', array(&$this, 'myStyleSheet'));
			add_action('wp_dashboard_setup', array(&$this, 'dashboard_widget'));
			$a2_plugin_basename = plugin_basename($GLOBALS['A2_Plugin_Dir'] . '/a2-optimized.php');
			add_filter("plugin_action_links_{$a2_plugin_basename}", array(&$this, 'plugin_settings_link'));
		}

		if (get_option('A2_Optimized_Plugin_recaptcha', 0) == 1 && !is_admin()) {
			add_action('woocommerce_login_form', array(&$this, 'login_captcha'));
			add_action('login_form', array(&$this, 'login_captcha'));
			add_filter('authenticate', array(&$this, 'captcha_authenticate'), 1, 3);
			add_action('comment_form_after_fields', array(&$this, 'comment_captcha'));
			add_filter('preprocess_comment', array(&$this, 'captcha_comment_authenticate'), 1, 3);
		}
		if ($this->is_xmlrpc_request() && get_option('a2_block_xmlrpc')) {
			$this->block_xmlrpc_request();
			add_filter('xmlrpc_methods', array(&$this, 'remove_xmlrpc_methods'));
		}
	}

	public function plugin_settings_link($links) {
		$settings_link = '<a href="admin.php?page=A2_Optimized_Plugin_admin">Settings</a>';
		array_unshift($links, $settings_link);

		return $links;
	}

	public function get_moved_login() {
		wp_redirect(wp_login_url(), 302);
		exit();
	}

	public function myStyleSheet() {
		wp_enqueue_style('a2-optimized-css', plugins_url('/assets/css/style.css', __FILE__), '', $this->getVersion());
	}

	/**
	 * Add a widget to the dashboard.
	 *
	 * This function is hooked into the 'wp_dashboard_setup' action below.
	 */
	public function dashboard_widget() {
		$logo_url = plugins_url() . '/a2-optimized-wp/assets/images/a2optimized.png';

		wp_add_dashboard_widget(
			'a2_optimized',		 // Widget slug.
			"<a href=\"admin.php?page=A2_Optimized_Plugin_admin\"><img src=\"{$logo_url}\" /></a>",		 // Title.
			array(&$this, 'a2_dashboard_widget') // Display function.
		);

		wp_add_dashboard_widget(
			'a2_optimized_kb',		 // Widget slug.
			'Have any questions? Search the A2 Hosting Knowledge Base for answers.',		 // Title.
			array(&$this, 'kb_dashboard_widget') // Display function.
		);

		//force the widget to the top of the dashboard

		global $wp_meta_boxes;

		// Get the regular dashboard widgets array
		// (which has our new widget already but at the end)

		unset($wp_meta_boxes['dashboard']['normal']['core']['wp_welcome_widget']);

		$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
		// Backup and delete our new dashboard widget from the end of the array
		$example_widget_backup = array('a2_optimized' => $normal_dashboard['a2_optimized'], 'a2_optimized_kb' => $normal_dashboard['a2_optimized_kb']);

		// Merge the two arrays together so our widget is at the beginning
		$sorted_dashboard = array_merge($example_widget_backup, $normal_dashboard);
		// Save the sorted array back into the original metaboxes
		$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
	}

	/**
	 * Create the function to output the contents of our Dashboard Widget.
	 */
	public function a2_dashboard_widget() {
		// Display whatever it is you want to show.

		echo <<<HTML

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

	public function kb_dashboard_widget() {
		echo <<<HTML
		<p>
    	<a class="button button-primary" href="http://www.a2hosting.com/kb" target="_blank">Search the Knowledge Base</a>
		</p>
HTML;
	}

	public function locked_files_notice() {
		echo <<<HTML
<div id="editing-locked" class="notice notice-success" >
     <p ><b style="color:#00CC00">Editing of plugin and theme files</b> in the wp-admin is <b style="color:#00CC00">disabled</b> by A2 Optimized<br>
     <b style="color:#00CC00">This is recommended for security reasons</b>. You can modify this setting on the <a href="admin.php?page=A2_Optimized_Plugin_admin">A2 Optimized Configuration page</a></p>
</div>
HTML;
	}

	public function recaptcha_installed_notice() {
		echo <<<HTML
<div id="recaptcha-installed" class="notice notice-error" >
     <p ><b style="color:#00CC00">A ReCaptacha plugin is installed.</b><br>
     Disable and delete any plugins using reCaptcha to use the reCaptcha functionality built into A2 Optimized.
     <br> </p>
</div>
HTML;
	}

	public function not_locked_files_notice() {
		echo <<<HTML
<div id="editing-locked" class="notice notice-error" >
     <p ><b style="color:red">Editing of plugin and theme files</b> in the wp-admin is <b style="color:red">enabled</b><br>
     <b style="color:red">This is not recommended for security reasons</b>. You can modify this setting on the <a href="admin.php?page=A2_Optimized_Plugin_admin">A2 Optimized Configuration page</a></p>
</div>
HTML;
	}

	public function divi_notice() {
		$current_theme = get_template();
		
		echo <<<HTML
<div id="divi-minify-notice" class="notice notice-error" >
     <p><strong style="color:red">Your theme, {$current_theme}, currently provides HTML/JS/CSS minification. This feature is also enabled by A2 Optimized. This may cause issues with some functionality of your theme.</strong></p>
     <p>You can disable HTML/JS/CSS either in your theme options or within the <a href="admin.php?page=A2_Optimized_Plugin_admin">A2 Optimized Configuration page</a></p>
</div>
HTML;
	}

	public function rwl_notice() {
		$rwl_page = get_option('wpseh_l01gnhdlwp');
		$home_page = get_home_url();
		$admin_url = get_admin_url();

		if ($a2_login_page = get_option('a2_login_page')) {//synch rwl_page and a2_login_page
			if ($a2_login_page != $rwl_page) {
				update_option('a2_login_page', $rwl_page);
			}
		} else {
			update_option('a2_login_page', $rwl_page);
		}

		$link = get_home_url() . '?' . $rwl_page;

		if (!(strpos(get_option('a2_login_bookmarked', ''), $link) === 0)) {
			echo <<<HTML
<div id="bookmark-login" class="updated" >
  <p>Your login page is now here: <a href="{$link}" >{$link}</a>. Bookmark this page!</p>
</div>
HTML;
		}
	}

	public function admin_init() {
		if (!$this->checkUserCapability('manage_options', get_current_user_id())) {
			return false;
		}

		$active_plugins = get_option('active_plugins');
		if (in_array('easy-hide-login/wp-hide-login.php', $active_plugins)) {
			if ($rwl_page = get_option('wpseh_l01gnhdlwp')) {
				if ($rwl_page != '') {
					add_action('admin_notices', array(&$this, 'rwl_notice'));
					if ($a2_login_page = get_option('a2_login_page')) {
						if ($a2_login_page != $rwl_page) {
							update_option('a2_login_page', $rwl_page);
						}
					} else {
						update_option('a2_login_page', $rwl_page);
					}
				}
			}
		}
		if (in_array('a2-w3-total-cache/a2-w3-total-cache.php', $active_plugins)) {
			wp_enqueue_script('a2_functions', plugins_url('/assets/js/functions.js', __FILE__), array('jquery'));
		}

		if (isset($_GET['page']) && in_array($_GET['page'], $this->config_pages)) {
			add_action('admin_notices', array(&$this, 'config_page_notice'));
		}

		if (get_template() == 'Divi') {
			$w3tc = $this->get_w3tc_config();
			
			if ($w3tc['minify.html.enable'] || $w3tc['minify.css.enable'] || $w3tc['minify.js.enable']) {
				add_action('admin_notices', array(&$this, 'divi_notice'));
			}
		}

		foreach ($active_plugins as $active_plugin) {
			$plugin_folder = explode('/', $active_plugin);
			if (in_array($plugin_folder[0], $this->incompatible_plugins)) {
				add_action('admin_notices', array(&$this, 'incompatible_plugin_notice'));
			}
			// Check for W3 Total Cache and show upgrade notice
			if ($plugin_folder[0] == 'w3-total-cache' && !$_GET['a2-page']) {
				add_action('admin_notices', array(&$this, 'w3totalcache_plugin_notice'));
			}
			// Check for Wordfence and if WAF rules are setup correctly, show notice if not
			if ($plugin_folder[0] == 'wordfence' && $this->wordfence_waf_check() === false) {
				add_action('admin_notices', array(&$this, 'wordfence_plugin_notice'));
			}
		}

		//we don't need this function anymore since the new reCaptcha is now compatible with other recaptcha plugins
		//if(function_exists('recaptcha_get_html')){
		//add_action( 'admin_notices', array(&$this,'recaptcha_installed_notice'));
		//}

		if (!(strpos($_SERVER['SCRIPT_FILENAME'], 'plugins.php') === false) && defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT) {
			add_action('admin_notices', array(&$this, 'locked_files_notice'));
		} elseif (!(strpos($_SERVER['SCRIPT_FILENAME'], 'plugins.php') === false)) {
			add_action('admin_notices', array(&$this, 'not_locked_files_notice'));
		}
	}

	/**
	 * Puts the configuration page in the Plugins menu by default.
	 * Override to put it elsewhere or create a set of submenus
	 * Override with an empty implementation if you don't want a configuration page
	 * @return void
	 */
	public function addSettingsSubMenuPage() {
		$this->addSettingsSubMenuPageToMenu();
	}

	protected function addSettingsSubMenuPageToMenu() {
		$this->requireExtraPluginFiles();
		$displayName = $this->getPluginDisplayName();
		add_menu_page(
			$displayName,
			$displayName,
			'manage_options',
			$this->getSettingsSlug(),
			array(&$this, 'settingsPage'),
			null,
			3
		);
	}

	protected function requireExtraPluginFiles() {
		//require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH . 'wp-admin/includes/plugin.php');
	}

	public function getPluginDisplayName() {
		return 'A2 Optimized';
	}

	protected function getSettingsSlug() {
		return get_class($this) . '_admin';
	}

	public function addAdminBar() {
		$this->requireExtraPluginFiles();
		global $wp_admin_bar;

		if (current_user_can('manage_options')) {
			$wp_admin_bar->add_node(array(
				'id' => 'a2-optimized-admin-bar',
				'title' => 'A2 Optimized',
				'href' => admin_url('admin.php?page=' . $this->getSettingsSlug())
			));
		}
	}

	public function addLockedEditor() {
		$this->requireExtraPluginFiles();
		add_theme_page('<span style="color:red !important">Editor Locked</span>', '<span style="color:red !important">Editor Locked</span>', 'manage_options', 'editor-locked', array(&$this, 'settingsPage'));
	}

	/**
	 * @return bool indicating if the plugin is installed already
	 */
	public function isInstalled() {
		return $this->getOption(self::optionInstalled) == true;
	}

	protected function get_recaptcha_public_key() {
		if (file_exists('/opt/a2-optimized/wordpress_encoded/pk.php')) {
			return file_get_contents('/opt/a2-optimized/wordpress_encoded/pk.php');
		}
		if ($key = get_option('a2_recaptcha_pubkey')) {
			return $key;
		}

		return null;
	}

	protected function addSettingsSubMenuPageToPluginsMenu() {
		$this->requireExtraPluginFiles();
		$displayName = $this->getPluginDisplayName();
		add_submenu_page(
			'plugins.php',
			$displayName,
			$displayName,
			'manage_options',
			$this->getSettingsSlug(),
			array(&$this, 'settingsPage')
		);
	}

	protected function addSettingsSubMenuPageToDashboard() {
		$this->requireExtraPluginFiles();
		$displayName = $this->getPluginDisplayName();
		add_dashboard_page(
			$displayName,
			$displayName,
			'manage_options',
			$this->getSettingsSlug(),
			array(&$this, 'settingsPage')
		);
	}

	protected function addSettingsSubMenuPageToSettingsMenu() {
		$this->requireExtraPluginFiles();
		$displayName = $this->getPluginDisplayName();
		add_options_page(
			$displayName,
			$displayName,
			'manage_options',
			$this->getSettingsSlug(),
			array(&$this, 'settingsPage')
		);
	}

	public function incompatible_plugin_notice() {
		$active_plugins = get_option('active_plugins');
		$plugins_arr = array();
		foreach ($active_plugins as $active_plugin) {
			$plugin_folder = explode('/', $active_plugin);
			if (in_array($plugin_folder[0], $this->incompatible_plugins)) {
				$folder = WP_PLUGIN_DIR . '/' . $active_plugin;
				$plugin_data = get_plugin_data($folder, false, false);
				$plugins_arr[] = $plugin_data['Name'];
			}
		}
		if (count($plugins_arr) > 1) {
			$plugin_output = implode(', ', $plugins_arr);
		} else {
			$plugin_output = $plugins_arr[0];
		}

		echo <<<HTML
    <div class="notice notice-warning">
        <p class="danger">Proceed with caution: A currently active plugin, {$plugin_output} may be incompatible with A2 Optimized.</p>
    </div>
HTML;
	}

	/*
	 *	XML-RPC Functions
	 */

	/* Is this a xmlrpc request? */
	public function is_xmlrpc_request() {
		return defined('XMLRPC_REQUEST') && XMLRPC_REQUEST;
	}
	
	/* Block this xmlrpc request unless other criteria are met */
	private function block_xmlrpc_request() {
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
	public function remove_xmlrpc_methods($xml_rpc_methods) {
		if ($this->client_is_automattic()) {
			return $xml_rpc_methods;
		}
		
		if ($this->clientip_whitelisted()) {
			return $xml_rpc_methods;
		}

		unset($xml_rpc_methods['pingback.ping']); // block pingbacks
		unset($xml_rpc_methods['pingback.extensions.getPingbacks']); // block pingbacks

		return $xml_rpc_methods;
	}
	
	public function clientip_whitelisted() {
		// For future consideration
		return false;
	}
	
	/* Checks if a Automattic plugin is installed
		Checks if IP making request if from Automattic
		https://jetpack.com/support/hosting-faq/
	*/
	public function client_is_automattic() {
		//check for jetpack / akismet / vaultpress
		if (
			!is_plugin_active('jetpack/jetpack.php')
			&& !is_plugin_active('akismet/akismet.php')
			&& !is_plugin_active('vaultpress/vaultpress.php')) {
			return false;
		}
		
		$ip_address = $_SERVER['REMOTE_ADDR'];
		if ($this->is_ip_in_range(
			$ip_address,
			array(
				'122.248.245.244', // Jetpack
				'54.217.201.243', // Jetpack
				'54.232.116.4', // Jetpack
				array('195.234.108.0', '195.234.111.255'), // Jetpack
				array('192.0.64.1', '192.0.127.255'), // VaultPress range
				//array('192.0.80.0', '192.0.95.255'), // Akismet (covered by VaultPress range)
				//array('192.0.96.0', '192.0.111.255'), // Akismet
				//array('192.0.112.0', '192.0.127.255'), // Akismet
			)
		)) {
			return true;
		}

		return false;
	}
	
	/* Use ip2long to do comparisons */
	public function is_ip_in_range($ip_address, $range_array) {
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
	
	/*
	 *	WordFence WAF Functions
	 */

	public function wordfence_waf_check() {
		// Check if the .htaccess file has a Wordfence WAF entry
		$htaccess = file_get_contents(ABSPATH . '.htaccess');

		return strpos($htaccess, 'Wordfence WAF');
	}
	
	public function wordfence_plugin_notice() {
		echo <<<HTML
    <div class="notice notice-warning">
        <p class="danger">Wordfence is not properly configured to work with A2 Optimized. Please review the Wordfence help document below to update your Wordfence settings.</p>
        <p><a href="https://www.wordfence.com/help/firewall/optimizing-the-firewall/" class="button-primary" target="_blank">Optimizing The Wordfence Firewall</a></p>
    </div>
HTML;
	}

	public function w3totalcache_plugin_notice() {
		$admin_url = admin_url('admin.php?a2-page=upgrade_wizard&page=A2_Optimized_Plugin_admin');
		echo <<<HTML
    <div class="notice notice-warning">
        <p class="danger">We noticed you have W3 Total Cache already installed. We are not able to fully support this version of W3 Total Cache with A2 Optimized. To get the best options for optimizing your WordPress site, we can help you disable this W3 Total Cache plugin version and install a A2 Hosting supported version of W3 Total Cache in its place.</p>
        <p><a href="{$admin_url}" class="button-primary">Disable W3 Total Cache</a></p>
    </div>
HTML;
	}

	public function config_page_notice() {
		echo <<<HTML
    <div class="notice notice-info">
        <p>This site has been configured using the A2 Optimized plugin.  We, at A2 Hosting, have spent quite a bit of time figuring out the best set of options for this plugin; however, if you think you need to customize configuration: by all means... Continue.  If you have arrived here by mistake, you may use the <a href="admin.php?page=A2_Optimized_Plugin_admin">A2 Optimized administration page to configure this plugin</a>.</p>
    </div>
HTML;
	}
}
