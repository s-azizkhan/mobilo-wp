<?php

namespace Barn2\Plugin\WC_Product_Table\Data;

use Barn2\Plugin\WC_Product_Table\Util\Util;
use WC_Product;

/**
 * Gets data for the date modified column.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Product_Date_Modified extends Abstract_Product_Data {

	/**
	 * The date format string.
	 *
	 * @var string.
	 */
	private $date_format;

	/**
	 * Constructor.
	 *
	 * @param WC_Product $product The product object.
	 * @param string $date_format The date format (PHP date/time format string).
	 */
	public function __construct( $product, $date_format ) {
		parent::__construct( $product );

		$this->date_format = $date_format;
	}

	/**
	 * Get the modified date for this product, formatted accordingly.
	 *
	 * @return string The modified date.
	 */
	public function get_data() {
		$date_modified = Util::empty_if_false( get_the_modified_date( $this->date_format, $this->get_parent_product_id() ) );
		return apply_filters( 'wc_product_table_data_date_modified', $date_modified, $this->product );
	}

	/**
	 * Get the modified date for the sort data, so the column can be sorted correctly.
	 *
	 * @return int The number of seconds since the epoch.
	 */
	public function get_sort_data() {
		return apply_filters( 'wc_product_table_data_sort_date_modified', get_the_modified_date( 'U', $this->get_parent_product_id() ), $this->product );
	}

}
