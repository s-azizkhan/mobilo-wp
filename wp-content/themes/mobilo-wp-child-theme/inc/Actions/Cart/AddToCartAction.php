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
        $product_id = $_POST['product_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        $variation_id = $_POST['variation_id'] ?? 0;
        $variation = $_POST['variation'] ?? null;
        $card_color = $_POST['card_color'] ?? '';

        if (!$product_id) {
            return $this->errorResponse('invalid_product', 'Invalid product ID', [], 400);
        }

        try {
            $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation);

            if ($cart_item_key && $card_color) {
                // Store card color in cart item
                WC()->cart->cart_contents[$cart_item_key]['card_color'] = $card_color;
            }

            if (!$cart_item_key) {
                return $this->errorResponse('add_failed', 'Failed to add product to cart', [], 400);

            }
            do_action('mc_add_to_cart_success', $cart_item_key);
            $cart_model = new CartModel();
            $cart_data = $cart_model->get_new_plan_cart();

            return self::out([
                'success' => true,
                'message' => __('Product added to cart successfully', 'mobilo'),
                'cart_data' => $cart_data,
            ]);
        } catch (\Exception $e) {
            mobilo_log(__METHOD__, $e->getMessage());
            return $this->errorResponse('add_error', $e->getMessage(), [], 500);
        }
    }
}
