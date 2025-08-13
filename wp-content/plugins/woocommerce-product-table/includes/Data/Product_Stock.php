<?php

namespace Barn2\Plugin\WC_Product_Table\Data;

use WC_Product_Variable;

/**
 * Gets data for the stock column.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Product_Stock extends Abstract_Product_Data {

	public function get_data() {
		$availability = $this->product->get_availability();

		if ( empty( $availability['availability'] ) && $this->product->is_in_stock() ) {
			$availability['availability'] = __( 'In stock', 'woocommerce-product-table' );
		}

		if ( $this->product instanceof WC_Product_Variable ) {
			// Use 'object' return type as it requires less DB calls that 'array'.
			$available_variations = $this->product->get_available_variations( 'object' );

			if ( empty( $available_variations ) ) {
				$availability['availability'] = __( 'Out of stock', 'woocommerce-product-table' );
				$availability['class']        = 'out-of-stock';
			}
		}

		$stock = '<p class="stock ' . esc_attr( $availability['class'] ) . '">' . $availability['availability'] . '</p>';

		return apply_filters( 'wc_product_table_data_stock', $stock, $this->product );
	}

}
