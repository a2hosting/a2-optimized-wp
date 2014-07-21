<?php
/*
   Plugin Name: A2 Optimized
   Plugin URI: http://wordpress.org/extend/plugins/a2-optimized/
   Version: 1.0beta
   Author: a2hosting.com
   Description: A2 Optimized WordPress optimization plugin
   Text Domain: a2-optimized
   License: GPLv3
  */


function files_not_found_notice(){

  $ip = $_SERVER['SERVER_ADDR'];
  
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json; charset=utf-8","Accept:application/json, text/javascript, */*; q=0.01"));
  curl_setopt($ch, CURLOPT_URL, "http://whois.arin.net/rest/ip/{$ip}");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $whois = json_decode(str_replace(array('@name','@handle'),array('name','handle'),curl_exec($ch)));
  curl_close($ch);
  

  $a2 = false;

  if(isset($whois->net->orgRef->handle)){
    if($whois->net->orgRef->handle == 'A2HOS'){
      //$a2 = true;
    }
  }



  if($a2){
    $message = '
        A2 Optimized for WordPress is only available on Web Hosting packages.
        <br><br>
        If your WordPress site is hosted on a VPS or Dedicated Server: please deactivate and delete this plugin <a href="plugins.php?plugin_status=active">here</a>.
        <br><br>
        If your WordPress site is hosted on a Web Hosting account: please submit a support ticket <a href="http://my.a2hosting.com" >here</a>
        <br>
      ';
  }
  else{
    $message = '
        A2 Optimized for WordPress is only available for sites hosted with <a href="http://a2hosting.com/wordpress-hosting" target="_blank" >A2 Hosting</a>.
        <br><br>
        If you are completely satisfied with the speed of your current host: please deactivate and delete this plugin <a href="plugins.php?plugin_status=active">here</a>. 
        <br><br>
        If you would like to take advantage of the A2 Optimized plugin for WordPress and enjoy extremely fast page load times: please visit <a target="_blank" href="http://a2hosting.com/wordpress-hosting">A2Hosting.com</a>
      <br>';

  }

  echo "<div class='error'><br>{$message}<br></div>";

}


	$A2_Optimized_minimalRequiredPhpVersion = '5.3';

	function A2_Optimized_noticePhpVersionWrong() {
    global $A2_Optimized_minimalRequiredPhpVersion;
    echo '<div class="updated fade">' .
      __('Error: plugin "A2 Optimized" requires a newer version of PHP to be running.',  'a2-optimized').
            '<br/>' . __('Minimal version of PHP required: ', 'a2-optimized') . '<strong>' . $A2_Optimized_minimalRequiredPhpVersion . '</strong>' .
            '<br/>' . __('Your site is running PHP version: ', 'a2-optimized') . '<strong>' . phpversion() . '</strong>' .
            '<br /> To learn how to change the version of php running on your site <a target="_blank" href="http://www.a2hosting.com/kb/cpanel/cpanel-software-and-services/php-version">read this Knowledge Base Article</a>.'.
         '</div>';
	}


    if(version_compare(phpversion(), $A2_Optimized_minimalRequiredPhpVersion) < 0) {
        add_action('admin_notices', 'A2_Optimized_noticePhpVersionWrong');
    }
		elseif(!file_exists('/opt/a2-optimized/wordpress_encoded/a2-optimized.php')){
		  add_action('admin_notices', 'files_not_found_notice');
		}
		else{
		  require_once '/opt/a2-optimized/wordpress_encoded/a2-optimized.php';
		}

?>
