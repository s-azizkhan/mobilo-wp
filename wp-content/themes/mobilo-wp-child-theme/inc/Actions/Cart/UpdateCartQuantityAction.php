<?php

namespace Mobilo\WpTheme\Actions\Cart;

use Mobilo\WpTheme\Actions\MobiloAjaxAction;
use Mobilo\WpTheme\Models\CartModel;

defined('ABSPATH') || exit;

/**
 * Update Cart Quantity AJAX Action
 * Handles updating cart item quantities via AJAX
 */
class UpdateCartQuantityAction extends MobiloAjaxAction
{
    public function __construct()
    {
        parent::__construct('update_cart_quantity', false, true);
    }

    public function action()
    {
        $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field($_POST['cart_item_key']) : '';
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

        if (!$cart_item_key || $quantity < 0) {
            $this->errorResponse('invalid_params', 'Invalid parameters', [], 400);
        }

        try {
            if ($quantity === 0) {
                WC()->cart->remove_cart_item($cart_item_key);
                $message = __('Item removed from cart', 'mobilo');
            } else {
                WC()->cart->set_quantity($cart_item_key, $quantity);
                $message = __('Cart updated successfully', 'mobilo');
            }

            $cart_model = new CartModel();
            $cart_data = $cart_model->get_new_plan_cart();

            self::out([
                'success' => true,
                'message' => $message,
                'cart_data' => $cart_data,
                'cart_count' => WC()->cart->get_cart_contents_count()
            ]);
        } catch (\Exception $e) {
            mobilo_log(__METHOD__, $e->getMessage());
            $this->errorResponse('update_error', $e->getMessage(), [], 500);
        }
    }
}
