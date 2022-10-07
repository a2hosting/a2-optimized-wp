<?php
namespace A2_Optimized\App\Controllers\Admin;

use A2_Optimized\Core\Controller;


if ( ! class_exists( __NAMESPACE__ . '\\' . 'Base_Controller' ) ) {
	/**
	 * Blueprint for Admin related Controllers. All Admin Controllers should extend this Base_Controller
	 *
	 * @since      3.0.0
	 * @package    A2_Optimized
	 * @subpackage A2_Optimized/controllers/admin
	 */
	abstract class Base_Controller extends Controller {

		/**
		 * Register callbacks for actions and filters. Most of your add_action/add_filter
		 * go into this method.
		 *
		 * NOTE: register_hook_callbacks method is not called automatically. You
		 * as a developer have to call this method where you see fit. For Example,
		 * You may want to call this in constructor, if you feel hooks/filters
		 * callbacks should be registered when the new instance of the class
		 * is created.
		 *
		 * The purpose of this method is to set the convention that first place to
		 * find add_action/add_filter is register_hook_callbacks method.
		 *
		 * @since    3.0.0
		 */
		abstract protected function register_hook_callbacks();
	}

}
