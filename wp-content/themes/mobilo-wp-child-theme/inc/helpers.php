<?php
defined('ABSPATH') || exit;


/**
 * Logs a message with the function/method name.
 */
function mobilo_log($from, $message, $context = [], $level = 'error'): void
{
    try {
        // If function name or message is empty, exit the function.
        if (empty($from) || empty($message)) {
            return;
        }

        // Validate and set the log level
        $valid_levels = ['error', 'warning', 'info', 'debug'];
        if (!in_array($level, $valid_levels)) {
            $level = 'error';
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            $current_time = date('Y-m-d H:i:s');
            $formatted_level = strtoupper($level);
            error_log("Mobilo[$formatted_level][$current_time]: {$from}: {$message}" . json_encode($context));
        }

        // log on woocommerce
        if (class_exists('WooCommerce')) {
            // WooCommerce logger
            $wcLogger = wc_get_logger();
            $context = array('source' => 'mobilo-log', 'function' => $from, 'context' => $context);

            // Write the log to WooCommerce log file
            $wcLogger->log($level, $message, $context);
        }
    } catch (Throwable $th) {
        error_log('Error logging: ' . $th->getMessage());
    }
}

if (!function_exists('to_ssl')) {

    /**
     * Replaces the 'http://' protocol in a URL with 'https://' protocol.
     */
    function to_ssl(string $url): string
    {
        return str_replace('http://', 'https://', $url);
    }
}

/**
 * Calculate the price including Value Added Tax (VAT).
 *
 * This function takes a price as input, calculates the VAT amount at a rate of 21% (0.21),
 * adds the calculated VAT to the original price, and returns the final price including VAT.
 *
 * @param float $price The original price before VAT.
 *
 * @return float The price including VAT.
 */
function mc_get_include_vat_price($price, $vatRate = 0.21)
{
    return $price * (1 + $vatRate);
}

function debug()
{
    echo "<pre>";
    array_map(function ($x) {
        var_dump($x);
    }, func_get_args());
    echo "</pre>";
}

function debugg()
{
    echo "<pre>";
    array_map(function ($x) {
        var_dump($x);
    }, func_get_args());
    debug_print_backtrace();
    echo "</pre>";
    wp_die("Ok");
}
function mc_format_price($price)
{
    return number_format((float) $price, 2, '.', '');
}

/**
 * Check a product ID is available in cart or not.
 *
 * @param int $product_id The ID of the product.
 * @return string|bool
 */
function mc_is_product_in_cart($product_id, $variation_id = 0)
{
    try {
        $variation = wc_get_product($product_id);
        $product_id = ($variation->get_parent_id()) ? $variation->get_parent_id() : $product_id;
        // Get the WooCommerce cart
        $cart = WC()->cart;

        $result = false;
        // Loop through cart items
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            // Get the product ID of the cart item
            $item_product_id = $cart_item['product_id'];

            // Check if the product ID matches
            if ($item_product_id == $product_id && !$variation_id) {
                $result = $cart_item_key; // Product found in the cart
                break;
            }
            // verify with variation
            else if ($item_product_id == $product_id && $variation_id) {
                $item_variation_id = $cart_item['variation_id'];
                if ($item_variation_id == $variation_id) {
                    $result = $cart_item_key; // Product found in the cart
                    break;
                }
                $result = false; // Product not found in the cart with given variation
                continue;
            }
        }

        return $result; // Product not found in the cart
    } catch (\Throwable $th) {
        mobilo_log(__METHOD__, $th->getMessage());
        return false;
    }
}

/**
 * Get cart item by product ID and variation ID
 *
 * @param int $product_id The ID of the product.
 * @param int $variation_id The ID of the variation (optional).
 * @return array|false Cart item data or false if not found.
 */
function mc_get_cart_item_by_product($product_id, $variation_id = 0)
{
    try {
        $cart = WC()->cart;
        $cart_items = $cart->get_cart();

        foreach ($cart_items as $cart_item_key => $cart_item) {
            if ($cart_item['product_id'] == $product_id) {
                if ($variation_id && $cart_item['variation_id'] == $variation_id) {
                    return $cart_item;
                } elseif (!$variation_id) {
                    return $cart_item;
                }
            }
        }

        return false;
    } catch (\Throwable $th) {
        mobilo_log(__METHOD__, $th->getMessage());
        return false;
    }
}

/**
 * Update cart item quantity
 *
 * @param string $cart_item_key The cart item key.
 * @param int $quantity The new quantity.
 * @return bool Success status.
 */
function mc_update_cart_item_quantity($cart_item_key, $quantity)
{
    try {
        if ($quantity <= 0) {
            WC()->cart->remove_cart_item($cart_item_key);
        } else {
            WC()->cart->set_quantity($cart_item_key, $quantity);
        }
        return true;
    } catch (\Throwable $th) {
        mobilo_log(__METHOD__, $th->getMessage());
        return false;
    }
}

/**
 * Add product to cart with custom data
 *
 * @param int $product_id The product ID.
 * @param int $quantity The quantity.
 * @param int $variation_id The variation ID (optional).
 * @param array $variation_data The variation data (optional).
 * @param array $cart_item_data Custom cart item data (optional).
 * @return string|false Cart item key or false on failure.
 */
function mc_add_product_to_cart($product_id, $quantity = 1, $variation_id = 0, $variation_data = [], $cart_item_data = [])
{
    try {
        $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation_data, $cart_item_data);
        return $cart_item_key;
    } catch (\Throwable $th) {
        mobilo_log(__METHOD__, $th->getMessage());
        return false;
    }
}
