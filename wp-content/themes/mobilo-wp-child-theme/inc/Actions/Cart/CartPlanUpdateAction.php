<?php

namespace Mobilo\WpTheme\Actions\Cart;

use Mobilo\WpTheme\Actions\MobiloAjaxAction;
use Mobilo\WpTheme\Feature\PlanFeature;
use Mobilo\WpTheme\Models\CartModel;
use Mobilo\WpTheme\PageTemplates\CartPageTemplate;

defined('ABSPATH') || exit;

/**
 * Handle update cart plan
 */
class CartPlanUpdateAction extends MobiloAjaxAction
{
    public function __construct()
    {
        parent::__construct('update_cart_plan');
    }

    public function action()
    {

        $plan_sku = PlanFeature::get_cart_plan(true);
        if (!$plan_sku) {
            return $this->errorResponse('invalid_plan_sku', 'Invalid plan SKU', [], 400);
        }
        $action = $_POST['action'] ?? 'increment';
        // get plan product
        $plan_id = wc_get_product_id_by_sku($plan_sku);
        if (!$plan_id) {
            return $this->errorResponse('invalid_plan_id', 'Invalid plan', [], 400);
        }

        $cart = mc_get_cart();
        if (!$cart) {
            return $this->errorResponse('invalid_cart', 'Invalid cart', [], 400);
        }

        $plan_cart_item = null;
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if ($cart_item['data']->get_sku() == $plan_sku) {
                $plan_cart_item = $cart_item;
                break;
            }
        }

        if (!$plan_cart_item) {
            return $this->errorResponse('invalid_plan_cart_item', 'Invalid plan cart item', [], 400);
        }

        $cart_item_key = $plan_cart_item['key'];

        try {
            if ($action === 'remove') {
                $result = $cart->remove_cart_item($cart_item_key);
                // TODO: does we need to remove the plan from the cart?
                if (!$result) {
                    return $this->errorResponse('remove_failed', 'Failed to remove plan', [], 400);
                }
                CartPageTemplate::mark_plan_manual_updated();
                $msg = "Plan removed successfully";
            } else {
                $new_qty = $plan_cart_item['quantity'];
                if ($action == 'increment') {
                    $new_qty += 1;
                } else {
                    $new_qty -= $plan_cart_item['quantity'];
                }
                $result = $cart->set_quantity($cart_item_key, $new_qty);
                if (!$result) {
                    return $this->errorResponse('update_failed', 'Failed to update plan quantity', [], 400);
                }
                CartPageTemplate::mark_plan_manual_updated();
                $msg = "Plan updated successfully";
            }
            do_action('mc_update_cart_plan_success', $cart_item_key);
            $cart_model = new CartModel();
            $cart_data = $cart_model->get_new_plan_cart();

            return self::out([
                'success' => true,
                'message' => $msg,
                'cart_data' => $cart_data,
            ]);
        } catch (\Exception $e) {
            mobilo_log(__METHOD__, $e->getMessage());
            return $this->errorResponse('add_error', $e->getMessage(), [], 500);
        }
    }


}
