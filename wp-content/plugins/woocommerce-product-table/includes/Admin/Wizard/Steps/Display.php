<?php

namespace Barn2\Plugin\WC_Product_Table\Admin\Wizard\Steps;

use Barn2\Plugin\WC_Product_Table\Dependencies\Barn2\Setup_Wizard\Api;
use Barn2\Plugin\WC_Product_Table\Dependencies\Barn2\Setup_Wizard\Step;
use Barn2\Plugin\WC_Product_Table\Util\Settings;

/**
 * The Display step in the setup wizard.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Display extends Step {

	/**
	 * Configure the step.
	 */
	public function __construct() {
		$this->set_id( 'display' );
		$this->set_name( esc_html__( 'Display', 'woocommerce-product-table' ) );
		$this->set_description( esc_html__( 'Do you want to use the product table layout for your main store pages? You can also create tables by adding the [product_table] shortcode to any page.', 'woocommerce-product-table' ) );
		$this->set_title( esc_html__( 'Store Pages', 'woocommerce-product-table' ) );
	}

	/**
	 * Setup fields for the step.
	 *
	 * @return array
	 */
	public function setup_fields() {
		$misc   = Settings::get_setting_misc();
		$fields = [];

		$fields['shop_override'] = [
			'title'   => esc_html__( 'Where to display product tables', 'woocommerce-product-table' ),
			'type'    => 'checkbox',
			'label'   => __( 'Shop page', 'woocommerce-product-table' ),
			'border'  => false,
			'classes' => [
				'first-checkbox'
			],
			'value'   => $misc['shop_override'] ?? false,
		];

		$fields['search_override'] = [
			'type'    => 'checkbox',
			'label'   => __( 'Product search results', 'woocommerce-product-table' ),
			'border'  => false,
			'classes' => [
				'no-top-pad'
			],
			'value'   => $misc['search_override'] ?? false,
		];

		$fields['archive_override'] = [
			'type'    => 'checkbox',
			'label'   => __( 'Product categories', 'woocommerce-product-table' ),
			'border'  => false,
			'classes' => [
				'no-top-pad'
			],
			'value'   => $misc['archive_override'] ?? false,
		];

		$fields['product_tag_override'] = [
			'type'    => 'checkbox',
			'label'   => __( 'Product tags', 'woocommerce-product-table' ),
			'border'  => false,
			'classes' => [
				'no-top-pad'
			],
			'value'   => $misc['product_tag_override'] ?? false,
		];

		$fields['attribute_override'] = [
			'type'    => 'checkbox',
			'label'   => __( 'Product attributes', 'woocommerce-product-table' ),
			'border'  => false,
			'classes' => [
				'no-top-pad'
			],
			'value'   => $misc['attribute_override'] ?? false,
		];

		return $fields;
	}

	/**
	 * {@inheritdoc}
	 */
	public function submit( array $values ) {
		$misc = array_merge( Settings::get_setting_misc(), $values );
		Settings::update_setting_misc( $misc );

		return Api::send_success_response();
	}

}
