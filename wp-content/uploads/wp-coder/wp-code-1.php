<?php

  defined( 'ABSPATH' ) || exit;
add_action('template_redirect', 'set_free_shipping_cookie_and_redirect_for_new_customers');
function set_free_shipping_cookie_and_redirect_for_new_customers() {
    try {
        if (isset($_GET['free_shipping_token']) && $_GET['free_shipping_token'] == '057535c52e5bcd98cbd21fd7105b83c5') {
            // Check if the user is logged in and has no previous orders
            if (lwmc_has_user_placed_order_before(get_current_user_id())) {
                return; // Do nothing if the user has previous orders
            }

            // Set cookie for 5 minutes for new customers only & set current plan
            setcookie('free_shipping_token', '1', time() + 600, COOKIEPATH, COOKIE_DOMAIN);
            setcookie('lwmc_chosen_plan_sku', 'MCP_PRO', time() + 30000, COOKIEPATH, COOKIE_DOMAIN);

            // Redirect to cart page after setting the cookie
            wp_redirect(wc_get_cart_url());
            exit;
        }
    } catch (Exception $e) {
        error_log('Error setting free shipping cookie and redirecting: ' . $e->getMessage());
    }
}

add_filter('woocommerce_package_rates', 'apply_free_shipping_if_cookie_exists_for_new_customers', 10, 2);
function apply_free_shipping_if_cookie_exists_for_new_customers($rates, $package) {
    try {
        if (isset($_COOKIE['free_shipping_token']) && $_COOKIE['free_shipping_token'] == '1') {
            // Check if the user is logged in and has no previous orders
            if (lwmc_has_user_placed_order_before(get_current_user_id())) {
                return $rates; // Do nothing if the user has previous orders
            }

            foreach ($rates as $rate_id => $rate) {
                if ('free_shipping' === $rate->method_id) {
                    return array($rate_id => $rate);
                }
            }
        }
    } catch (Exception $e) {
        error_log('Error applying free shipping: ' . $e->getMessage());
    }
    return $rates;
}
