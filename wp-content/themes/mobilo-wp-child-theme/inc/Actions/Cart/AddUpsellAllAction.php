<?php

namespace Mobilo\WpTheme\Actions\Cart;

use Mobilo\WpTheme\Actions\MobiloAjaxAction;
use Mobilo\WpTheme\Models\CartModel;

defined('ABSPATH') || exit;

/**
 * Add Upsell All AJAX Action
 * Handles adding upsell products for all members via AJAX
 */
class AddUpsellAllAction extends MobiloAjaxAction
{
    public function __construct()
    {
        parent::__construct('add_upsell_all', false, true);
    }

    public function action()
    {
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

        if (!$product_id) {
            $this->errorResponse('invalid_product', 'Invalid product ID', [], 400);
        }

        try {
            // Get total members from cart (subscription products)
            $cart_model = new CartModel();
            $total_members = $cart_model->total_license;

            if ($total_members > 0) {
                $quantity = $total_members;
            }

            $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity);

            if ($cart_item_key) {
                $cart_data = $cart_model->get_new_plan_cart();

                self::out([
                    'success' => true,
                    'message' => sprintf(__('Added %d items for all members', 'mobilo'), $quantity),
                    'cart_data' => $cart_data,
                    'cart_count' => WC()->cart->get_cart_contents_count()
                ]);
            } else {
                $this->errorResponse('add_failed', 'Failed to add products to cart', [], 400);
            }
        } catch (\Exception $e) {
            mobilo_log(__METHOD__, $e->getMessage());
            $this->errorResponse('add_error', $e->getMessage(), [], 500);
        }
    }
}
