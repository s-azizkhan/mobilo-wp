<?php
/**
 * The template for displaying product listing
 *
 * Override this template by copying it to yourtheme/woocommerce/wwof-product-details.php
 *
 * @author 		Rymera Web Co
 * @package 	WooCommerceWholeSaleOrderForm/Templates
 * @version     1.14.1
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



// NOTE: Don't Remove any ID or Classes inside this template when overriding it.
// Some JS Files Depend on it. You are free to add ID and Classes without any problem.

$product_id                 = WWOF_Functions::wwof_get_product_id( $product );
$product_type               = WWOF_Functions::wwof_get_product_type( $product );
$parent_product_id          = ( $product_type == 'variation' ) ? WWOF_Functions::wwof_get_product_variation_parent( $product , true ) : 0;
$product_post_data          = ( $product_type == 'variation' && $parent_product_id ) ? get_post( $parent_product_id ) : get_post( $product_id );
$is_all_variations_in_stock = $product_type == 'variable' && ! WWOF_Product_Listing_Helper::wwof_out_of_stock_variations_check( $product , $product_id );
$productPrice               = $wholesale_prices->wwof_get_product_price( $product ); 
$wholesale_role             = $wholesale_prices->_get_current_user_wholesale_role(); 
$available_variations       = array();

if ( $product_type == 'variable' ) {
    
    $available_variations = WWOF_Product_Listing_Helper::wwof_get_available_variations( $product , $product_id , $wholesale_role );

    if ( !empty( $wholesale_role ) ) {
        
        // get wholesale price for all variations
        WWOF_Product_Listing_Helper::wwof_get_variations_wholesale_price( $available_variations , $wholesale_role );

    }

    // update available variations input arguments
    WWOF_Product_Listing_Helper::wwof_update_variations_input_args( $available_variations );
    
} ?>

<div class="wwof-popup-product-details-container">
    <div class="wwof-popup-product-images"><?php
        // Main Product Image
        echo $product->get_image('medium'); ?>
        <div class="gallery"><?php
            // Product Gallery
            $product_gallery_ids = WWOF_Functions::wwof_get_gallery_image_ids( $product );
            foreach( $product_gallery_ids as $gallery_id ) {
                echo wp_get_attachment_image( $gallery_id );
            } ?>
            <div style="clear: both; float: none; display: block;"></div>
        </div>
    </div><!--.wwof-popup-product-images-->

    <div class="wwof-popup-product-summary">
        <h2 class="product-title"><?php echo $product->get_title(); ?></h2>
        <?php if ( $product_type == 'variation' ) : ?>
            <div class="selected-variation">
                <?php echo $product_listing->wwof_get_product_variation_selected_options( $product , $product_type ); ?>
            </div>
        <?php endif; ?>
        <div class="product-rating">
            <?php echo WWOF_Functions::wwof_get_rating_html( $product ); ?>
            <div style="clear: both; float: none; display: block;"></div>
        </div>
        <div class="product-price">
            <?php echo empty( $productPrice ) ? $product->get_price_html() : ''; ?>
        </div>
        <div class="product-desc"><?php
            echo do_shortcode( wpautop( $product_post_data->post_content ) );
            echo $product_listing->wwof_get_variations_description( $product ); ?>
        </div><?php

        WWOF_Functions::wwof_get_product_category_list( $product ); ?>

        <table class="dummy-table" >
            <tr>
                <td>
                    <div class="product_meta_col" style="display: none !important;" data-product_variations="" >
                        <?php echo $product_listing->wwof_get_product_meta( $product ); ?>
                    </div>
                    <div class="product_title_col">
                        <?php
                            if ( $is_all_variations_in_stock ) {
                                echo $product_listing->wwof_get_product_variation_field( $product , $product_type , $available_variations );
                                echo $product_listing->wwof_get_product_variation_selected_options( $product , $product_type );
                            }

                            echo $product_listing->wwof_get_product_addons( $product , $product_id , $product_type );
                        ?>
                    </div>

                    <div class="product_price_col">
                        <?php echo $productPrice; ?>
                    </div>

                    <?php if ( $product->is_in_stock() || $is_all_variations_in_stock ) : ?>
                        <div class="product_stock_quantity_col <?php echo $product_listing->wwof_get_product_stock_quantity_visibility_class(); ?>">
                            <?php echo $product_listing->wwof_get_product_stock_quantity( $product , $product_type ); ?>
                        </div>
                        <div class="product_quantity_col">
                            <?php echo $wholesale_prices->wwof_get_product_quantity_field( $product , $product_id , $product_type , $wholesale_role ); ?>
                        </div>
                        <div class="product_row_action">
                            <?php echo $product_listing->wwof_get_product_row_action_fields( $product ); ?>
                        </div>
                    <?php else : ?>
                        <div class="product_stock_quantity_col outofstock">
                            <?php _e( 'Out of stock' , 'woocommerce-wholesale-order-form' ); ?>
                        </div>
                    <?php endif; ?>

                </td>
            </tr>
        </table>

    </div><!--.wwof-popup-product-summary-->
    <div style="clear: both; float: none; display: block;"></div>
</div>
