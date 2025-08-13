<?php

namespace Barn2\Plugin\WC_Product_Table\Admin\Wizard\Steps;

use Barn2\Plugin\WC_Product_Table\Dependencies\Barn2\Setup_Wizard\Steps\Welcome;

/**
 * The license verification step in the setup wizard.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class License_Verification extends Welcome {

	/**
	 * Setup step.
	 */
	public function __construct() {
		parent::__construct();
		$this->set_name( esc_html__( 'Welcome', 'woocommerce-product-table' ) );
		$this->set_title( esc_html__( 'Welcome to WooCommerce Product Table', 'woocommerce-product-table' ) );
		$this->set_description( esc_html__( 'This wizard will guide you through the plugin setup process.', 'woocommerce-product-table' ) );
	}

}
