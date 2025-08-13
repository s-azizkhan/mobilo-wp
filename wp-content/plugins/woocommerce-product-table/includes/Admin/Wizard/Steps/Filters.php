<?php
/**
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */

namespace Barn2\Plugin\WC_Product_Table\Admin\Wizard\Steps;

use Barn2\Plugin\WC_Product_Table\Dependencies\Barn2\Setup_Wizard\Api;
use Barn2\Plugin\WC_Product_Table\Dependencies\Barn2\Setup_Wizard\Step;
use Barn2\Plugin\WC_Product_Table\Dependencies\Barn2\Setup_Wizard\Util;
use Barn2\Plugin\WC_Product_Table\Util\Settings;
use Barn2\WPT_Lib\Util as Lib_Util;

class Filters extends Step {

	/**
	 * Configure the step.
	 */
	public function __construct() {
		$this->set_id( 'filters' );
		$this->set_name( esc_html__( 'Filters', 'woocommerce-product-table' ) );
		$this->set_description( esc_html__( 'Add filter dropdowns so that customers can find what they’re looking for more easily. You can also add filter widgets to the sidebar.', 'woocommerce-product-table' ) );
		$this->set_title( esc_html__( 'Product filters', 'woocommerce-product-table' ) );
	}

	/**
	 * Setup fields.
	 *
	 * @return array
	 */
	public function setup_fields() {
		$table  = Settings::get_setting_table_defaults();
		$fields = [];

		$fields['filters'] = [
			'type'        => 'select',
			'label'       => __( 'Product filters', 'woocommerce-product-table' ),
			'description' => __( 'Filter the table by category, tag, attribute or taxonomy.', 'woocommerce-product-table' ),
			'options'     => Util::parse_array_for_dropdown(
				[
					'false'  => __( 'Disabled', 'woocommerce-product-table' ),
					'true'   => __( 'Show based on table content', 'woocommerce-product-table' ),
					'custom' => __( 'Custom', 'woocommerce-product-table' )
				],
				false
			),
			'value'       => $table['filters'] ?? 'true'
		];

		$fields['filters_custom'] = [
			'type'        => 'text',
			'label'       => esc_html__( 'Custom filters', 'woocommerce-product-table' ),
			'description' => sprintf(
				'%s %s',
				__( 'Enter the filters as a comma-separated list.', 'woocommerce-product-table' ),
				Lib_Util::barn2_link( 'kb/wpt-filters/#filter-dropdowns', false, true )
			),
			'conditions'  => [
				'filters' => [
					'op'    => 'eq',
					'value' => 'custom',
				]
			],
			'value'       => $table['filters_custom'] ?? '',
		];

		return $fields;
	}

	/**
	 * {@inheritdoc}
	 */
	public function submit( array $values ) {
		$table = Settings::get_setting_table_defaults();

		$table['filters']        = $values['filters'] ?? true;
		$table['filters_custom'] = $values['filters_custom'] ?? '';

		Settings::update_setting_table_defaults( $table );

		return Api::send_success_response();
	}

}
