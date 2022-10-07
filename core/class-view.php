<?php
namespace A2_Optimized\Core;

use A2_Optimized as A2_Optimized;

/**
 * Class Responsible for Loading Templates
 *
 * @since      3.0.0
 * @package    A2_Optimized
 * @subpackage A2_Optimized/views
 * @author     Sumit P <sumit.pore@gmail.com>
 */
class View {
	/**
	 * Render Templates
	 *
	 * @access public
	 * @param mixed  $template_name Template file to render.
	 * @param array  $args Variables to make available inside template file.
	 * @param string $template_path Directory to search for template.
	 * @param string $default_path Fallback directory to search for template if not found at $template_path.
	 * @return void
	 */
	public static function render_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		if ( $args && is_array( $args ) ) {
			extract( $args ); // @codingStandardsIgnoreLine.
		}

		$located = static::locate_template( $template_name, $template_path, $default_path );
		if ( false == $located ) {
			return;
		}

		ob_start();
		do_action( 'a2_optimized_before_template_render', $template_name, $template_path, $located, $args );
		include( $located );
		do_action( 'a2_optimized_after_template_render', $template_name, $template_path, $located, $args );

		return ob_get_clean(); // @codingStandardsIgnoreLine.
	}

	/**
	 * Locate a template and return the path for inclusion.
	 *
	 * This is the load order:
	 *
	 *      yourtheme       /   $template_path  /   $template_name
	 *      yourtheme       /   $template_name
	 *      $default_path   /   $template_name
	 *
	 * @access public
	 * @param mixed  $template_name Template file to locate.
	 * @param string $template_path $template_path Directory to search for template.
	 * @param string $default_path Fallback directory to search for template if not found at $template_path.
	 * @return string
	 */
	public static function locate_template( $template_name, $template_path = '', $default_path = '' ) {
		if ( ! $template_path ) {
			$template_path = 'a2-optimized-templates/';
		}
		if ( ! $default_path ) {
			$default_path = A2_Optimized::get_plugin_path() . 'app/templates/';
		}

		// Look within passed path within the theme - this is priority.
		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name,
			)
		);

		// Get default template.
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}

		if ( file_exists( $template ) ) {
			// Return what we found.
			return apply_filters( 'a2_optimized_locate_template', $template, $template_name, $template_path );
		} else {
			return false;
		}
	}
}
