<?php
/*
   Plugin Name: A2 Optimized
   Plugin URI: https://www.a2hosting.com/
   Version: 1.9.3.4
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
      $extensions = get_loaded_extensions(true);
      $ioncube = false;
      foreach($extensions as $extension){
        if(!(strpos($extension, "ionCube") === false)){
          $ioncube = true;
        }
      }
      if(!$ioncube){
        add_action('admin_notices', array( $this, 'A2_Optimized_noticeIonCube' ) );
      }
      else{
        require_once('a2-optimized_init.php');
        A2_Optimized_init(__FILE__);
      }
  }


  public function A2_Optimized_noticePHPSwitch() {
    $phpversion = phpversion();
    echo<<<HTML
    <div class="error">
      A2 Optimized for WordPress does not support PHP version {$phpversion} without the IonCube Loader Extension.<br>
      To learn how to change PHP to the default version: <a target="_blank" href="http://www.a2hosting.com/kb/cpanel/cpanel-software-and-services/php-version">read this Knowledge Base Article</a> and choose the "Default" PHP version in cpanel.<br><br>
    </div>
HTML;
  }
	
  public function A2_Optimized_noticeIonCube() {
    echo<<<HTML
    		<div class="error">
      		<span style="font-weight:bold">A2 Optimized</span> requires the ionCube Loader PHP extension.<br>
      		Read our Knowledge Base on how to install and configure the IonCube Loader Extension for PHP <a href="http://www.a2hosting.com/kb/developer-corner/php/ioncube-php-loader-support" target="_blank">here</a>.<br><br>
    		</div>
HTML;
    
    
    $server_phpversion = str_replace( 'PHP/', '', exec( "/usr/local/bin/php --version | head -n 1 | awk '{print $2}'" ) );
    if( version_compare( phpversion(), $server_phpversion, '==' ) === false ) { 
      $this->A2_Optimized_noticePHPSwitch();
    }

  }
}


$a2opt_class = new A2_Optimized();
