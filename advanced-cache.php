<?php
/**
 * A2 Optimized advanced cache
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$a2opt_cache_dir = ( ( defined( 'WP_PLUGIN_DIR' ) ) ? WP_PLUGIN_DIR : WP_CONTENT_DIR . '/plugins' ) . '/a2-optimized-wp';
$a2opt_cache_engine_file = $a2opt_cache_dir . '/core/A2_Optimized_CacheEngine.php';
$a2opt_cache_disk_file = $a2opt_cache_dir . '/core/A2_Optimized_CacheDisk.php';

if ( file_exists( $a2opt_cache_engine_file ) && file_exists( $a2opt_cache_disk_file ) ) {
	require_once $a2opt_cache_engine_file;
	require_once $a2opt_cache_disk_file;
}

if ( class_exists( 'A2_Optimized_Cache_Engine' ) ) {
	$a2opt_cache_engine_started = A2_Optimized_Cache_Engine::start();

	if ( $a2opt_cache_engine_started ) {
		$a2opt_cache_delivered = A2_Optimized_Cache_Engine::deliver_cache();

		if ( ! $a2opt_cache_delivered ) {
			A2_Optimized_Cache_Engine::start_buffering();
		}
	}
}
