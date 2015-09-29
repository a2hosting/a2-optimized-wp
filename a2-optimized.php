<?php
/*
   Plugin Name: A2 Optimized
<<<<<<< HEAD
   Plugin URI: www.a2hosting.com/kb/installable-applications/optimization-and-configuration/wordpress2/optimizing-wordpress-with-the-a2-optimized-plugin
   Version: 1.7.8
=======
   Plugin URI: https://www.a2hosting.com/
   Version: 2.0
>>>>>>> 45a36e1314b91069aab0bd92b1844be2007208ed
   Author: a2hosting.com
   Description: A2 Optimized WordPress optimization plugin
   Text Domain: a2-optimized
   License: GPLv3
*/



//////////////////////////////////
// Run initialization
/////////////////////////////////


class A2_Optimized {
  function __construct() {

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
}

$a2opt_class = new A2_Optimized();
