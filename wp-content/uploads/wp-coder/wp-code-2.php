<?php

  defined( 'ABSPATH' ) || exit;
/* FREE ACCESSORIES */
// Set a cookie when the user visits the special link
add_action('template_redirect', 'validate_gift_token_and_set_cookie');
function validate_gift_token_and_set_cookie()
{
    try {

        if (isset($_GET['token']) && $_GET['token'] == '936969b26e94a782fe3771b261c79144') {
            // Check if the user is logged in and has no previous orders
            if (lwmc_has_user_placed_order_before()) {
                return; // Do nothing if the user has previous orders
            }

            setcookie('gift_accessories_product_active', '1', time() + 600, '/'); // 10-minute expiry

            if (!isset($_COOKIE['lwmc_chosen_plan_sku'])) {
                setcookie('lwmc_chosen_plan_sku', 'MCP_PRO', time() + 6000, '/'); // 100-minute expiry
            }

            if(!is_cart()) {
                // Redirect to cart page
                wp_redirect(wc_get_cart_url());
                exit;
            }
        }
    } catch (Exception $e) {
        error_log('Error validating gift token: ' . $e->getMessage());
    }
}

// Automatically add the free gift product when a user adds any item to the cart
add_action('woocommerce_add_to_cart', 'add_free_gift_product_to_cart', 10);
add_action('wp_loaded', 'add_free_gift_product_to_cart', 10);
function add_free_gift_product_to_cart()
{
    try {
        if (!isset($_COOKIE['gift_accessories_product_active']) || lwmc_has_user_placed_order_before()) {
            return; // Only apply if the cookie exists
        }

        // Check if the user is logged in and has no previous orders
        if (WC()->cart->is_empty()) {
            return; // Do nothing if the user has previous orders
        }

        $free_sku = ['NFC-KF', 'NFC-SB'];

        foreach ($free_sku as $sku) {

            // Get product ID by SKU
            $gift_product_id = wc_get_product_id_by_sku($sku);
            if (!$gift_product_id) {
                continue; // If SKU is incorrect, do nothing
            }

            // Check if the free gift is already in the cart
            foreach (WC()->cart->get_cart() as $cart_item) {
                if ($cart_item['product_id'] == $gift_product_id) {
                    // Free product is already in cart, no need to add again, update the meta data
                    if (!isset($cart_item['free_gift'])) {
                        $cart_item['free_gift'] = true;
                    }
                    continue;
                }
            }

            // Add free product to cart
            WC()->cart->add_to_cart($gift_product_id, 1, 0, array(), array('free_gift' => true));
        }

    } catch (Exception $e) {
        error_log('Error adding free gift to cart: ' . $e->getMessage());
    }
}

// Ensure the free gift has a price of $0 in the cart
add_action('woocommerce_before_calculate_totals', 'set_gift_product_price', 9999);
function set_gift_product_price($cart)
{
    try {
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item['free_gift']) && $cart_item['free_gift']) {

                // Check if the user is logged in and has no previous orders
                if (lwmc_has_user_placed_order_before()) {
                    //     set the actual price
                    $cart_item['data']->set_price($cart_item['data']->get_price());
                } else {
                    $cart_item['data']->set_price(0); // Set price to zero
                }
            }
        }
    } catch (\Throwable $th) {
        error_log('Error setting gift product price: ' . $th->getMessage());
    }
}

// Clear the cookie once the cart is emptied or the order is placed
// add_action('woocommerce_cart_emptied', 'clear_gift_cookie');
add_action('woocommerce_thankyou', 'clear_gift_cookie');
function clear_gift_cookie()
{
    if (isset($_COOKIE['gift_accessories_product_active'])) {
        setcookie('gift_accessories_product_active', '', time() - 3600, '/'); // Expire the cookie
    }
}
