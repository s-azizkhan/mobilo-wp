<?php

namespace Barn2\Plugin\WC_Product_Table\Admin\Wizard\Steps;

use Barn2\Plugin\WC_Product_Table\Dependencies\Barn2\Setup_Wizard\Api;
use Barn2\Plugin\WC_Product_Table\Dependencies\Barn2\Setup_Wizard\Step;
use Barn2\Plugin\WC_Product_Table\Dependencies\Barn2\Setup_Wizard\Util;
use Barn2\Plugin\WC_Product_Table\Util\Settings;
use Barn2\WPT_Lib\Util as Lib_Util;

/**
 * The Cart step in the setup wizard.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Cart extends Step {

	/**
	 * Configure the step.
	 */
	public function __construct() {
		$this->set_id( 'cart' );
		$this->set_name( esc_html__( 'Add to Cart', 'woocommerce-product-table' ) );
		$this->set_description( esc_html__( 'How will customers add products to their cart?', 'woocommerce-product-table' ) );
		$this->set_title( esc_html__( 'Adding to the cart', 'woocommerce-product-table' ) );
	}

	/**
	 * Setup fields.
	 *
	 * @return array
	 */
	public function setup_fields() {
		$table  = Settings::get_setting_table_defaults();
		$fields = [];

		$fields['cart_button'] = [
			'type'    => 'select',
			'label'   => esc_html__( 'Add to cart method', 'woocommerce-product-table' ),
			'options' => Util::parse_array_for_dropdown(
				[
					'button'          => __( 'Cart buttons', 'woocommerce-product-table' ),
					'checkbox'        => __( 'Checkboxes', 'woocommerce-product-table' ),
					'button_checkbox' => __( 'Cart buttons and checkboxes', 'woocommerce-product-table' )
				],
				false
			),
			'value'   => $table['cart_button'] ?? 'button'
		];

		$fields['quantities'] = [
			'type'        => 'checkbox',
			'label'       => esc_html__( 'Quantities', 'woocommerce-product-table' ),
			'description' => __( 'Show a quantity picker for each product', 'woocommerce-product-table' ),
			'value'       => $table['quantities'] ?? false,
		];

		$fields['variations'] = [
			'type'        => 'select',
			'label'       => esc_html__( 'Variations', 'woocommerce-product-table' ),
			'options'     => Util::parse_array_for_dropdown(
				[
					'dropdown' => __( 'Show as dropdown lists', 'woocommerce-product-table' ),
					'separate' => __( 'Show one variation per row', 'woocommerce-product-table' ),
					'false'    => __( 'Read More button linking to the product page', 'woocommerce-product-table' ),
				],
				false
			),
			'description' => sprintf(
				'%s %s',
				__( 'How to display the options for variable products.', 'woocommerce-product-table' ),
				Lib_Util::barn2_link( 'kb/product-variations', false, true )
			),
			'value'       => $table['variations'] ?? 'dropdown',
		];

		return $fields;
	}

	/**
	 * {@inheritdoc}
	 */
	public function submit( array $values ) {
		$table = Settings::get_setting_table_defaults();

		$table['cart_button'] = $values['cart_button'] ?? 'button';
		$table['quantities']  = $values['quantities'] ?? false;
		$table['variations']  = $values['variations'] ?? 'dropdown';

		Settings::update_setting_table_defaults( $table );

		return Api::send_success_response();
	}

}
