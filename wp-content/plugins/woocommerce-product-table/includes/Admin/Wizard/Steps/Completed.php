<?php

namespace Barn2\Plugin\WC_Product_Table\Admin\Wizard\Steps;

use Barn2\Plugin\WC_Product_Table\Dependencies\Barn2\Setup_Wizard\Steps\Ready;
use Barn2\WPT_Lib\Util as Lib_Util;

/**
 * The Completed step in the setup wizard.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Completed extends Ready {

	/**
	 * Configure the step.
	 */
	public function __construct() {
		parent::__construct();
		$this->set_name( esc_html__( 'Ready', 'woocommerce-product-table' ) );
		$this->set_title( esc_html__( 'Setup Complete', 'woocommerce-product-table' ) );
		$this->set_description(
			sprintf(
			/* translators: 1: help link start tag, 2: help link end tag */
				__( 'You’re all set! Take a look at our %1$sKnowledge Base%2$s for further instructions, tutorials, videos, and much more.', 'woocommerce-product-table' ),
				Lib_Util::format_barn2_link_open( 'kb-categories/woocommerce-product-table-kb/', true ),
				'</a>'
			)
		);
	}

}
