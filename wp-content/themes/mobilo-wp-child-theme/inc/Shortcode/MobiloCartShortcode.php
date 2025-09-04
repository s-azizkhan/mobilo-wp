<?php

namespace Mobilo\WpTheme\Shortcode;

use Mobilo\WpTheme\Models\CartItemModel;
use Mobilo\WpTheme\Models\CartModel;

defined('ABSPATH') || exit;

class MobiloCartShortcode
{
    private $currency;
    private $currency_symbol;
    private $plan;

    private $cart_data;

    public function init($plan)
    {
        $this->currency = get_woocommerce_currency();
        $this->currency_symbol = get_woocommerce_currency_symbol($this->currency);
        $this->plan = $plan;
        $this->cart_data = $this->build_view_data();
        add_shortcode('mobilo_cart', [$this, 'render']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts()
    {
        if (has_shortcode(get_the_content(), 'mobilo_cart')) {
            // Enqueue CSS [we dont need this because we are using dynamic cart css from js(cart.js)]
            wp_enqueue_style(
                'mobilo-cart-css',
                MOBILO_THEME_URL . '/assets/dist/cart.css',
                [],
                '1.0.0'
            );

            // Enqueue JavaScript
            wp_enqueue_script(
                'mobilo-cart-js',
                MOBILO_THEME_URL . '/assets/js/cart.js',
                ['jquery'],
                '1.0.0',
                true
            );

            $actions = [
                'add_to_cart' => 'mc_add_to_cart',
                'remove_cart_item' => 'mc_remove_cart_item',
                'update_cart_quantity' => 'mc_update_cart_quantity',
                'update_cart_item_quantity' => 'mc_update_cart_item_quantity',
                'get_cart_data' => 'mc_get_cart_data',
                'add_upsell_all' => 'mc_add_upsell_all',
                'update_cart_plan' => 'mc_update_cart_plan',
            ];

            wp_localize_script('mobilo-cart-js', 'mobiloCart', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mobilo_cart_nonce'),
                'actions' => $actions,
                'currency' => $this->currency,
                'currency_symbol' => $this->currency_symbol,
                'themeUrl' => MOBILO_THEME_URL,
                'cart_data' => $this->cart_data,
                'strings' => [
                    'addToCart' => __('Add to Cart', 'mobilo'),
                    'removeFromCart' => __('Remove from Cart', 'mobilo'),
                    'updateCart' => __('Update Cart', 'mobilo'),
                    'loading' => __('Loading...', 'mobilo'),
                    'error' => __('An error occurred', 'mobilo'),
                    'success' => __('Success', 'mobilo'),
                ]
            ]);
        }
    }

    public function render()
    {
        $cartData = $this->cart_data;
        ob_start();
        include_once MOBILO_THEME_PATH . '/views/mobilo-cart.php';
        return ob_get_clean();
    }

    private function build_view_data()
    {
        $products = $this->get_products_by_sku();
        do_action('mc_cart_quantity_update_action');
        $cart_model = new CartModel();
        $cart_data = $cart_model->get_new_plan_cart();

        return [
            'products' => $products['products'],
            'upsell_products' => $products['upsell_products'],
            'cart_data' => $cart_data,
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'currency' => $this->currency,
            'currency_symbol' => $this->currency_symbol,
            'plan' => $this->plan,
        ];
    }

    private function get_cart_items()
    {
        $cart_items = WC()->cart->get_cart();
        return $cart_items;
    }

    private function get_products_by_sku()
    {
        $main_products_sku = ['MCC', 'MBC', 'MC_DIGITAL'];
        $main_product_ids = [];
        $upsell_products_sku = ['NFC-SB', 'NFC-KF'];
        $upsell_product_ids = [];

        foreach ($main_products_sku as $sku) {
            $product_id = wc_get_product_id_by_sku($sku);
            if ($product_id) {
                $main_product_ids[] = $product_id;
            }
        }

        foreach ($upsell_products_sku as $sku) {
            $product_id = wc_get_product_id_by_sku($sku);
            if ($product_id) {
                $upsell_product_ids[] = $product_id;
            }
        }

        $checkoutData = WC()->session->get('checkout_data');
        $billing_country = $checkoutData['billing_country'] ?? null;

        $products = [];
        foreach ($main_product_ids as $product_id) {
            $wc_product = wc_get_product($product_id);
            if (!$wc_product) {
                continue;
            }

            $regularPrice = $wc_product->get_regular_price();
            $salePrice = $wc_product->get_sale_price();

            if ($this->currency == 'EUR' && $billing_country !== 'GB') {
                $regularPrice = mc_get_include_vat_price($regularPrice);
                $salePrice = mc_get_include_vat_price($salePrice);
            }

            $desc = $wc_product->get_description();
            $short_desc = $wc_product->get_short_description();
            $product_type = $wc_product->get_type();
            $shipping_text = $wc_product->get_meta('_lwmc_shipping_text');

            $product = [
                'id' => $wc_product->get_id(),
                'name' => $wc_product->get_title(),
                'base_price' => mc_format_price($regularPrice),
                'price' => mc_format_price($salePrice),
                'sku' => $wc_product->get_sku(),
                'features' => CartItemModel::parse_product_description($desc, true),
                'short_description' => $short_desc,
                'shipping_text' => $shipping_text,
                'type' => $product_type,
            ];

            switch ($product_type) {
                case 'variable':
                    $product['default_attribute'] = array_values($wc_product->get_default_attributes())[0];
                    $product['variations'] = CartItemModel::parse_variable_product_variation($wc_product);
                    $product['payment_cycle'] = 'onetime';
                    break;
                case 'subscription':
                    $product['payment_cycle'] = 'yearly';
                    break;
            }


            // $product['in_cart'] = mc_is_product_in_cart($wc_product->get_id());
            $product['in_cart'] = false; // TODO: note: is is already handled in cart.js

            // Get product thumbnail
            if (has_post_thumbnail($product_id)) {
                $product['thumbnail'] = wp_get_attachment_url(get_post_thumbnail_id($product_id));
            } else {
                $product['thumbnail'] = '';
            }

            $products[] = $product;
        }

        // Sort products by price (paid plan: high to low, free plan: low to high)
        $isPaidPlan = true; // TODO: change this in future
        usort($products, function ($a, $b) use ($isPaidPlan) {
            if (isset($a['variations']) && isset($a['variations']['plastic'])) {
                $a_price = $a['variations']['plastic']['price'];
            } else {
                $a_price = $a['price'];
            }

            if (isset($b['variations']) && isset($b['variations']['plastic'])) {
                $b_price = $b['variations']['plastic']['price'];
            } else {
                $b_price = $b['price'];
            }

            return $isPaidPlan ? $b_price <=> $a_price : $a_price <=> $b_price;
        });

        $upsell_products = [];
        foreach ($upsell_product_ids as $product_id) {
            $wc_product = wc_get_product($product_id);
            if (!$wc_product) {
                continue;
            }

            $regularPrice = $wc_product->get_regular_price();
            $salePrice = $wc_product->get_sale_price();

            if ($this->currency == 'EUR' && $billing_country !== 'GB') {
                $regularPrice = mc_get_include_vat_price($regularPrice);
                $salePrice = mc_get_include_vat_price($salePrice);
            }

            $product = [
                'id' => $wc_product->get_id(),
                'name' => $wc_product->get_title(),
                'base_price' => mc_format_price($regularPrice),
                'price' => mc_format_price($salePrice),
                'sku' => $wc_product->get_sku(),
                'type' => $wc_product->get_type(),
                // 'in_cart' => mc_is_product_in_cart($wc_product->get_id()),
                'in_cart' => false, // TODO: note: is is already handled in cart.js
            ];

            // Get product thumbnail
            if (has_post_thumbnail($product_id)) {
                $product['thumbnail'] = wp_get_attachment_url(get_post_thumbnail_id($product_id));
            } else {
                $product['thumbnail'] = '';
            }

            $upsell_products[] = $product;
        }

        return ['products' => $products, 'upsell_products' => $upsell_products];
    }
}
