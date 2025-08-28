<?php

namespace Mobilo\WpTheme\Actions\Cart;

use Mobilo\WpTheme\Actions\MobiloAjaxAction;
use Mobilo\WpTheme\Models\CartModel;

defined('ABSPATH') || exit;

/**
 * Add to Cart AJAX Action
 * Handles adding products to cart via AJAX
 */
class AddToCartAction extends MobiloAjaxAction
{
    public function __construct()
    {
        parent::__construct('add_to_cart');
    }

    public function action()
    {
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        $variation_id = isset($_POST['variation_id']) ? intval($_POST['variation_id']) : 0;
        $variation = isset($_POST['variation']) ? $_POST['variation'] : [];
        $card_color = isset($_POST['card_color']) ? sanitize_text_field($_POST['card_color']) : '';

        if (!$product_id) {
            $this->errorResponse('invalid_product', 'Invalid product ID', [], 400);
        }

        try {
            $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation);

            if ($cart_item_key && $card_color) {
                // Store card color in cart item
                WC()->cart->cart_contents[$cart_item_key]['card_color'] = $card_color;
            }

            if ($cart_item_key) {
                $cart_model = new CartModel();
                $cart_data = $cart_model->get_new_plan_cart();

                self::out([
                    'success' => true,
                    'message' => __('Product added to cart successfully', 'mobilo'),
                    'cart_data' => $cart_data,
                    'cart_count' => WC()->cart->get_cart_contents_count()
                ]);
            } else {
                $this->errorResponse('add_failed', 'Failed to add product to cart', [], 400);
            }
        } catch (\Exception $e) {
            mobilo_log(__METHOD__, $e->getMessage());
            $this->errorResponse('add_error', $e->getMessage(), [], 500);
        }
    }
}
