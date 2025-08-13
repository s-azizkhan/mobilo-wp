<?php

namespace Barn2\Plugin\WC_Product_Table\Data;

use Barn2\Plugin\WC_Product_Table\Util\Util;
use WC_Product;

/**
 * Gets data for the name column.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Product_Name extends Abstract_Product_Data {

	private $variation_format;

	/**
	 * Create a new Product_Name object.
	 *
	 * @param WC_Product $product          The product.
	 * @param string     $links            The link option.
	 * @param string     $variation_format The variation name format - full, parent, or attributes.
	 */
	public function __construct( WC_Product $product, $links = '', $variation_format = 'full' ) {
		parent::__construct( $product, $links );
		$this->variation_format = $variation_format;
	}

	public function get_data() {
		$name = apply_filters( 'wc_product_table_data_name_before_link', Util::get_product_name( $this->product, $this->variation_format ), $this->product );

		if ( array_intersect( [ 'all', 'name' ], $this->links ) ) {
			$name = Util::format_product_link( $this->product, $name );
		}

		return apply_filters( 'wc_product_table_data_name', $name, $this->product );
	}

}
