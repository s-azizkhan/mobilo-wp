<?php

namespace Barn2\Plugin\WC_Product_Table\Data;

use WC_Product;

/**
 * Gets data for the price column.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Product_Price extends Abstract_Product_Data {

	/**
	 * @var int The number of decimals used for prices in WooCommerce.
	 */
	private $price_decimals;

	/**
	 * Constructor.
	 *
	 * @param WC_Product $product The product object
	 * @param string $links       The table links option
	 */
	public function __construct( WC_Product $product, $links = '' ) {
		parent::__construct( $product, $links );

		$this->price_decimals = wc_get_price_decimals();
	}

	public function get_data() {
		return apply_filters( 'wc_product_table_data_price', $this->product->get_price_html(), $this->product );
	}

	public function get_sort_data() {
		$price = $this->product->get_price();

		// Grouped and variable products don't reliably return the correct price using the get_price() function, so we do
		// some additional steps to fetch the correct price. The price used is always the lowest of the available prices.
		if ( $this->product->is_type( 'grouped' ) ) {
			$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
			$child_prices     = [];
			$children         = array_filter( array_map( 'wc_get_product', $this->product->get_children() ), 'wc_products_array_filter_visible_grouped' );

			foreach ( $children as $child ) {
				if ( '' !== $child->get_price() ) {
					$child_prices[] = 'incl' === $tax_display_mode ? wc_get_price_including_tax( $child ) : wc_get_price_excluding_tax( $child );
				}
			}

			if ( ! empty( $child_prices ) ) {
				$price = min( $child_prices );
			}
		} elseif ( $this->product->is_type( 'variable' ) ) {
			$prices = $this->product->get_variation_prices( false );

			if ( ! empty( $prices['price'] ) ) {
				$price = current( $prices['price'] );
			}
		}

		$price = number_format( floatval( $price ), $this->price_decimals, '.', '' );

		if ( apply_filters( 'wc_product_table_obfuscate_price_data_attributes', false ) ) {
			$price = $this->sortable_obfuscate( $price );
		}

		return apply_filters( 'wc_product_table_data_sort_price', $price, $this->product );
	}

	private function sortable_obfuscate( $n ) {
		// Define an array of 10 unique characters that will replace 0-9.
		// (coincidentally, the first 10 characters in 'barn2plugins' never occur twice in the string).
		$chars = str_split( 'barn2plugi', 1 );

		// Sort the array, this way the resulting string will keep the same order of the original numbers.
		sort( $chars );

		// use `PHP_INT_MAX` as a pseudo-random base number.
		$base = (int) substr( PHP_INT_MAX, 1 );
		$n    = $base + intval( $n * pow( 10, $this->price_decimals ) );

		return implode(
			'',
			array_map(
				function ( $d ) use ( $chars ) {
					return $chars[ $d ];
				},
				str_split( (string) $n, 1 )
			)
		);
	}

}
