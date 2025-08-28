<?php

namespace Mobilo\WpTheme\Actions\Cart;

use Mobilo\WpTheme\Actions\MobiloAjaxAction;
use Mobilo\WpTheme\Models\CartModel;

defined('ABSPATH') || exit;

/**
 * Get Cart Data AJAX Action
 * Handles retrieving cart data via AJAX
 */
class GetCartDataAction extends MobiloAjaxAction
{
    public function __construct()
    {
        parent::__construct('get_cart_data', false, true);
    }

    public function action()
    {
        try {
            $cart_model = new CartModel();
            $cart_data = $cart_model->get_new_plan_cart();

            self::out([
                'success' => true,
                'cart_data' => $cart_data,
                'cart_count' => WC()->cart->get_cart_contents_count()
            ]);
        } catch (\Exception $e) {
            mobilo_log(__METHOD__, $e->getMessage());
            $this->errorResponse('get_error', $e->getMessage(), [], 500);
        }
    }
}
