<?php

namespace Barn2\Plugin\WC_Product_Table\Integration;

use Barn2\Plugin\WC_Product_Table\Data\Abstract_Product_Data;
use Barn2\WPT_Lib\Registerable;
use Barn2\WPT_Lib\Service;
use Barn2\WPT_Lib\Table\Table_Data_Interface;
use WC_Product;

/**
 * Integration for YITH WooCommerce Request A Quote Premium.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class YITH_Request_Quote implements Registerable, Service {

	public function register() {
		// Premium version must be installed and active.
		if ( ! defined( 'YITH_YWRAQ_PREMIUM' ) || ! defined( 'YITH_YWRAQ_INC' ) ) {
			return;
		}

		if ( ! function_exists( 'YITH_YWRAQ_Frontend' ) ) {
			require_once YITH_YWRAQ_INC . 'class.yith-request-quote-frontend.php';
		}

		add_filter( 'wc_product_table_custom_table_data_request_quote', [ $this, 'get_data_object' ], 10, 3 );
	}

	public function get_data_object( $data, $product, $args ) {
		return new class( $product ) extends Abstract_Product_Data implements Table_Data_Interface {

			public function __construct( WC_Product $product ) {
				parent::__construct( $product );
			}

			public function get_data() {
				ob_start();
				?>
				<div class="ywraq_container_add_to_quote">
					<?php
					if ( $this->product->is_type( 'variation' ) ) :
						?>
						<form class="cart" method="post" enctype="multipart/form-data" style="display:none;">
							<input type="hidden" name="variation_id" value="<?php echo absint( $this->product->get_id() ); ?>"/>

							<?php foreach ( $this->product->get_variation_attributes() as $attribute => $value ) : ?>
								<input type="hidden" name="<?php echo esc_attr( sanitize_title( $attribute ) ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
							<?php endforeach; ?>
						</form>
					<?php endif; ?>
					<?php YITH_YWRAQ_Frontend()->print_button( $this->product ); ?>
				</div>
				<?php
				return ob_get_clean();
			}

		};
	}

}
