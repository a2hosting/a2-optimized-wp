<?php
namespace A2_Optimized\Core\Registry;

if ( ! class_exists( __NAMESPACE__ . '\\' . 'Model' ) ) {
	/**
	 * Model Registry
	 *
	 * Maintains the list of all models objects
	 *
	 * @since      3.0.0
	 * @package    A2_Optimized
	 * @subpackage A2_Optimized/Core/Registry
	 * @author     Your Name <email@example.com>
	 */
	class Model {
		use Base_Registry;
	}
}
