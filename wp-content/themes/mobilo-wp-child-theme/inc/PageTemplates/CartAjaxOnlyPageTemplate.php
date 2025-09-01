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
    }

    public function handle_cart_update($cart_item_key)
    {
        // rearrage the cart quantity
        PlanFeature::checkProductsInCart();
    }
}
