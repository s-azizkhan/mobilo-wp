<?php

namespace Mobilo\WpTheme\PageTemplates;

use Mobilo\WpTheme\Feature\PlanFeature;

defined('ABSPATH') || exit;

class CartAjaxOnlyPageTemplate
{

    public function __construct()
    {
        // add cart enhancement hooks
        add_action('mc_add_to_cart_success', [$this, 'handle_cart_update']);
        add_action('mc_update_cart_quantity_success', [$this, 'handle_cart_update']);
        add_action('mc_remove_cart_item_success', [$this, 'handle_cart_update']);
        add_action('mc_cart_quantity_update_action', [$this, 'handle_cart_update']);
        // Action to auto apply coupons when added to cart via ajax ( self API ), fix: Auto apply coupon not working on new react theme.
        $autoApplyCouponPluginInstance = \WC_SC_Auto_Apply_Coupon::get_instance();
        add_action('woocommerce_ajax_after_added_to_cart', [$autoApplyCouponPluginInstance, 'auto_apply_coupons']);
    }

    public function handle_cart_update($cart_item_key)
    {
        // rearrage the cart quantity
        PlanFeature::checkProductsInCart();
    }
}
