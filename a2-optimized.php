<?php
/*
    Plugin Name: A2 Optimized WP
    Plugin URI: https://wordpress.org/plugins/a2-optimized/
    Version: 2.0.7
    Author: A2 Hosting
    Author URI: https://www.a2hosting.com/
    Description: A2 Optimized - WordPress Optimization Plugin
    Text Domain: a2-optimized
    License: GPLv3
*/



//////////////////////////////////
// Run initialization
/////////////////////////////////


class A2_Optimized {
  function __construct() {

      $A2_Optimized_minimalRequiredPhpVersion = '5.3.0';

      if(version_compare(PHP_VERSION, $A2_Optimized_minimalRequiredPhpVersion) < 0) {
        add_action('admin_notices', array(&$this,'A2_Optimized_noticePhpVersionWrong'));
        return;
      }

      $GLOBALS['A2_Plugin_Dir'] = dirname(__FILE__);

      require_once('A2_Optimized_Plugin.php');
      require_once ABSPATH.'wp-admin/includes/plugin.php';


      $a2Plugin = new A2_Optimized_Plugin();

      // Install the plugin
      if (!$a2Plugin->isInstalled()) {
          $a2Plugin->install();
      }
      else {
          // Perform any version-upgrade activities prior to activation (e.g. database changes)
          $a2Plugin->upgrade();
      }

      // Add callbacks to hooks
      $a2Plugin->addActionsAndFilters();

      // Register the Plugin Activation Hook
      register_activation_hook(__FILE__, array(&$a2Plugin, 'activate'));

      // Register the Plugin Deactivation Hook
      register_deactivation_hook(__FILE__, array(&$a2Plugin, 'deactivate'));

  }


  function A2_Optimized_noticePhpVersionWrong() {
        global $A2_Optimized_minimalRequiredPhpVersion;
        echo '<div class="updated fade">' .
            __('Error: plugin "A2 Optimized" requires a newer version of PHP to be running.',  'a2-optimized').
            '<br/>' . __('Minimal version of PHP required: ', 'a2-optimized') . '<strong>' . $A2_Optimized_minimalRequiredPhpVersion . '</strong>' .
            '<br/>' . __('Your site is running PHP version: ', 'a2-optimized') . '<strong>' . phpversion() . '</strong>' .
            '<br />'. __(' To learn how to change the version of php running on your site'). ' <a target="_blank" href="http://www.a2hosting.com/kb/cpanel/cpanel-software-and-services/php-version">'. __('read this Knowledge Base Article').'</a>.'.
            '</div>';
    }

}

$a2opt_class = new A2_Optimized();
