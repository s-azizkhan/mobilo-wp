<?php

namespace Mobilo\WpTheme\PageTemplates;

use Mobilo\WpTheme\Feature\PlanFeature;
use Mobilo\WpTheme\Shortcode\MobiloCartShortcode;

defined('ABSPATH') || exit;

class CartPageTemplate extends PageTemplateLoader
{
    public $pageId = 'cart';

    public function __construct()
    {
        parent::__construct();
        // TODO:add cart enhancement hooks
    }

    public function init()
    {
        add_action('wp_enqueue_scripts', [$this, 'remove_smart_coupons_assets_on_cart'], 99999);
        // if above query params are set(?product_sku=MBC&sku=MCP_PRO), empty the cart & set the plan sku in cookie & add the product to cart
        $plan = PlanFeature::getCartPlan();
        if (isset($_GET['product_sku'])) {
            if (!empty($plan)) {
                // check if product sku is valid
                if (in_array(strtoupper($_GET['product_sku']), PlanFeature::$cardsSku)) {
                    $product_id = wc_get_product_id_by_sku(strtoupper($_GET['product_sku']));
                    if ($product_id) {
                        WC()->cart->empty_cart();
                        WC()->cart->add_to_cart($product_id);
                        // return to clean cart page (without query params)
                        $cart_page_url = wc_get_cart_url();
                        if (wp_safe_redirect($cart_page_url)) {
                            exit;
                        }
                    }
                }
            }
        }
        // add cart page specific assets here
        (new MobiloCartShortcode())->init($plan);
    }
    public function remove_smart_coupons_assets_on_cart()
    {
        // Dequeue Smart Coupons styles
        $styles = ['cfw-blocks-styles', 'mollie-gateway-icons', 'mollie-components', 'mollie-applepaydirect', 'wc-blocks-packages-style', 'wc-blocks-style', 'wc-blocks-editor-style', 'woocommerce-add-to-cart-form-style', 'woocommerce-multi-currency', 'photoswipe', 'select2', 'smart-coupon', 'smart-coupon-designs', 'common', 'wc-blocks-style-breadcrumbs', 'wc-blocks-style-cart-link', 'wcs-checkout', 'wc-gift-cards-blocks-integration', 'woocommerce-smart-coupons-available-coupons-block', 'woocommerce-smart-coupons-send-coupon-form-block', 'woocommerce-smart-coupons-action-tab-frontend', 'wc-gc-css', 'wc-gift-cards-blocks-integration', 'brands-styles', 'woocommerce_subscriptions_gifting', 'sendcloud-checkout-css', 'wc-blocks-integration'];
        foreach ($styles as $style) {
            wp_dequeue_style($style);
            wp_deregister_style($style);
        }

        // Dequeue Smart Coupons scripts
        $scripts = ['smart-coupon', 'smart-coupon-designs', 'woocommerce-multi-currency', 'cfw-blocks-styles', 'woocommerce-subscriptions', 'select2', 'wcs-checkout', 'wc-gift-cards-blocks-integration', 'wc-add-to-cart', 'wc-add-to-cart-variation', 'woocommerce', 'selectWoo', 'wc-cart-fragments', 'sendcloud-checkout-widget', 'wc-order-attribution', 'woocommerce_subscriptions_gifting', 'wc-gc-main', 'jquery-ui-datepicker', 'wcs-cart'];
        foreach ($scripts as $script) {
            wp_dequeue_script($script);
            wp_deregister_script($script);
        }
    }
}
