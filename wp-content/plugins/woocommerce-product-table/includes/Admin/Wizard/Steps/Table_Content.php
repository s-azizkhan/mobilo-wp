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
use Barn2\Plugin\WC_Product_Table\Util\Settings;
use Barn2\WPT_Lib\Util as Lib_Util;

class Table_Content extends Step {

	/**
	 * Configure the step.
	 */
	public function __construct() {
		$this->set_id( 'table-content' );
		$this->set_name( esc_html__( 'Content', 'woocommerce-product-table' ) );
		$this->set_description( esc_html__( 'Now, choose the information that will appear in your product tables.', 'woocommerce-product-table' ) );
		$this->set_title( esc_html__( 'Table Content', 'woocommerce-product-table' ) );
	}

	/**
	 * Setup fields
	 *
	 * @return array
	 */
	public function setup_fields() {
		$table  = Settings::get_setting_table_defaults();
		$fields = [];

		$fields['columns'] = [
			'type'        => 'text',
			'label'       => esc_html__( 'Columns', 'woocommerce-product-table' ),
			'description' => sprintf(
				'%s %s',
				__( 'Enter the columns for your product tables.', 'woocommerce-product-table' ),
				Lib_Util::barn2_link( 'kb/product-table-columns', false, true )
			),
			'value'       => $table['columns'] ?? ''
		];

		$fields['lazy_load'] = [
			'title'       => __( 'Lazy load', 'woocommerce-product-table' ),
			'type'        => 'checkbox',
			'label'       => esc_html__( 'Load products one page at a time', 'woocommerce-product-table' ),
			'description' => esc_html__( 'Enable this if you have a large number of products.', 'woocommerce-product-table' ),
			'value'       => $table['lazy_load'] ?? false
		];

		return $fields;
	}

	/**
	 * {@inheritdoc}
	 */
	public function submit( array $values ) {
		$table = Settings::get_setting_table_defaults();

		$table['columns']   = $values['columns'] ?? '';
		$table['lazy_load'] = $values['lazy_load'] ?? false;

		Settings::update_setting_table_defaults( $table );

		return Api::send_success_response();
	}

}
