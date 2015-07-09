<?php

$parts = explode(".",$_GET['resource'],2);
$extension = $parts[1];

switch($extension){
    case 'css':
    case 'min.css':
        header("Content-Type: text/css");
        break;
    case 'js':
    case 'min.js':
        header("Content-Type: text/javascript");
        break;
    case 'html':
    case 'min.html':
        header("Content-Type: text/html");
        break;
    case 'jpg':
        header("Content-Type: image/jpeg");
        break;
    case 'png':
        header("Content-Type: image/png");
        break;
    case 'gif':
        header("Content-Type: image/gif");
        break;
    case 'woff':
        header("Content-Type: application/font-woff");
        break;
    case 'ttf':
        header("Content-Type: application/x-font-truetype");
        break;
    default:
        wp_die("Invalid file extension.");
}

if(!(strpos($_GET['resource'],"..") === false)){
    wp_die("Invalid file name.");
}


echo file_get_contents(dirname(__FILE__)."/resources/{$_GET['resource']}");
