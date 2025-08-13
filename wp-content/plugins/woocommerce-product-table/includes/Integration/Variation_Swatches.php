<?php

namespace Barn2\Plugin\WC_Product_Table\Integration;

use Barn2\WPT_Lib\Registerable;
use Barn2\WPT_Lib\Service;

/**
 * Integration with Variation Swatches for WooCommerce (Emran Ahmed), both the free and Pro version.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Variation_Swatches implements Service, Registerable {

	/**
	 * Register the integration.
	 */
	public function register() {
		// Is Variation Swatches for WooCommerce active?
		if ( ! defined( 'WOO_VARIATION_SWATCHES_PLUGIN_FILE' ) && ! defined( 'WOO_VARIATION_SWATCHES_PRO_PLUGIN_FILE' ) ) {
			return;
		}

		add_filter(
			'wc_product_table_cart_form_class_variable',
			function ( $classes ) {
				$classes[] = 'woo_variation_swatches_variations_form';
				return $classes;
			}
		);
	}

}
