<?php
/**
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\WC_Product_Table\Admin\Wizard\Steps;

use Barn2\Plugin\WC_Product_Table\Dependencies\Barn2\Setup_Wizard\Steps\Cross_Selling;

class Upsell extends Cross_Selling {
    
    public function __construct() {
		parent::__construct();
		$this->set_name( esc_html__( 'More', 'woocommerce-product-table' ) );
		$this->set_description( __( 'Enhance your store with these fantastic plugins from Barn2.', 'woocommerce-product-table' ) );
		$this->set_title( esc_html__( 'Extra features', 'woocommerce-product-table' ) );
	}

}