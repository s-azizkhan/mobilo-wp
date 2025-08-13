<?php

namespace Barn2\Plugin\WC_Product_Table\Data;

use WC_Product;

/**
 * Gets data for the summary (i.e. short description) column.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Product_Summary extends Abstract_Product_Data {

	private $description_length;
	private $process_shortcodes;

	/**
	 * Constructor.
	 *
	 * @param WC_Product $product            The product object
	 * @param int        $description_length The length of the description, when the main product description is used.
	 * @param bool       $process_shortcodes Whether to process shortcodes in the short description.
	 */
	public function __construct( $product, $description_length, $process_shortcodes = false ) {
		parent::__construct( $product );

		$this->description_length = $description_length;
		$this->process_shortcodes = $process_shortcodes;
	}

	/**
	 * Get the product short description.
	 *
	 * @return string
	 */
	public function get_data() {
		$summary = $this->get_parent_product()->get_short_description();

		// If no short description, fall back to the main product description.
		if ( ! $summary ) {
			$description_data = new Product_Description( $this->product, $this->description_length, $this->process_shortcodes );
			$summary          = $description_data->get_data();
		} else {
			$summary = apply_filters( 'woocommerce_short_description', parent::maybe_strip_shortcodes( $summary, $this->process_shortcodes ) );
		}

		// @deprecated 3.0 - replaced by wc_product_table_data_summary.
		$summary = apply_filters_deprecated( 'wc_product_table_data_short_description', [ $summary, $this->product ], '3.0', 'wc_product_table_data_summary' );

		return apply_filters( 'wc_product_table_data_summary', $summary, $this->product );
	}

}
