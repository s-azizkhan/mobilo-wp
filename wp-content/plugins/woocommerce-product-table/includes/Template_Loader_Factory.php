<?php

namespace Barn2\Plugin\WC_Product_Table;

use Barn2\WPT_Lib\Template_Loader;
use Barn2\WPT_Lib\WooCommerce\Templates;

/**
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Template_Loader_Factory {

	private static $template_loader = null;

	/**
	 * Get the shared template loader instance.
	 *
	 * @return Template_Loader The template loader.
	 */
	public static function create() {
		if ( null === self::$template_loader ) {
			self::$template_loader = new Templates( 'product-table', wpt()->get_dir_path() . 'templates/' );
		}
		return self::$template_loader;
	}

}
