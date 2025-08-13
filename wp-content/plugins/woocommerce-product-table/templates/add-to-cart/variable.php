<?php
global $product;

// Get available variations?
use Barn2\Plugin\WC_Product_Table\Util\Util;

$get_variations       = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
$available_variations = $get_variations ? $product->get_available_variations() : false;

$variations_json = wp_json_encode( $available_variations );
$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

$form_class  = implode( ' ', apply_filters( 'wc_product_table_cart_form_class_variable', [ 'wpt_variations_form', 'cart' ] ) );
$form_action = apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() );

do_action( 'woocommerce_before_add_to_cart_form' );
?>

	<form class="<?php echo esc_attr( $form_class ); ?>" action="<?php echo esc_url( $form_action ); ?>" method="post" enctype="multipart/form-data" data-product_id="<?php echo esc_attr( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; ?>">
		<?php do_action( 'woocommerce_before_variations_form' ); ?>

		<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
			<p class="stock out-of-stock"><?php esc_html_e( 'Out of stock', 'woocommerce-product-table' ); ?></p>
		<?php else : ?>
			<?php
			$variation_attributes = $product->get_variation_attributes();
			?>
			<div class="variations">
				<?php foreach ( $variation_attributes as $attribute_name => $options ) : ?>
					<span class="select-parent" data-attribute="<?php echo esc_attr( $attribute_name ); ?>">
					<?php
					// Set the default variation if the product has a default attribute.
					$selected = $product->get_variation_default_attribute( $attribute_name );

					wc_dropdown_variation_attribute_options(
						[
							'options'          => $options,
							'attribute'        => $attribute_name,
							'id'               => sanitize_title( $attribute_name ) . '_' . $product->get_id(),
							'product'          => $product,
							'selected'         => $selected,
							'show_option_none' => Util::get_attribute_label( $attribute_name, $product )
						]
					);
					?>
					</span>
				<?php endforeach; ?>
			</div>

			<div class="single_variation_wrap">
				<?php
				do_action( 'woocommerce_before_single_variation' );
				do_action( 'woocommerce_single_variation' );
				do_action( 'woocommerce_after_single_variation' );
				?>
			</div>

		<?php endif; // if available variations ?>

		<?php do_action( 'woocommerce_after_variations_form' ); ?>
	</form>

<?php
do_action( 'woocommerce_after_add_to_cart_form' );
