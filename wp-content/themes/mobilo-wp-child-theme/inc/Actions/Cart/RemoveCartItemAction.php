<?php

namespace Mobilo\WpTheme\Actions\Cart;

use Mobilo\WpTheme\Actions\MobiloAjaxAction;
use Mobilo\WpTheme\Models\CartModel;

defined('ABSPATH') || exit;

/**
 * Remove Cart Item AJAX Action
 * Handles removing items from cart via AJAX
 */
class RemoveCartItemAction extends MobiloAjaxAction
{
    public function __construct()
    {
        parent::__construct('remove_cart_item');
    }

    public function action()
    {
        $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field($_POST['cart_item_key']) : '';

        if (!$cart_item_key) {
            $this->errorResponse('invalid_item', 'Invalid cart item', [], 400);
        }

        try {
            WC()->cart->remove_cart_item($cart_item_key);

            $cart_model = new CartModel();
            $cart_data = $cart_model->get_new_plan_cart();

            self::out([
                'success' => true,
                'message' => __('Item removed from cart', 'mobilo'),
                'cart_data' => $cart_data,
                'cart_count' => WC()->cart->get_cart_contents_count()
            ]);
        } catch (\Exception $e) {
            mobilo_log(__METHOD__, $e->getMessage());
            $this->errorResponse('remove_error', $e->getMessage(), [], 500);
        }
    }
}
