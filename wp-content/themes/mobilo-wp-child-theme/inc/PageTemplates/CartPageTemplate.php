<?php

namespace Mobilo\WpTheme\PageTemplates;

use Mobilo\WpTheme\Shortcode\MobiloCartShortcode;
use Mobilo\WpTheme\Actions\Cart\CartAjaxActions;

defined('ABSPATH') || exit;

class CartPageTemplate extends PageTemplateLoader
{
    public $pageId = 'cart';

    public function __construct()
    {
    }

    public function init()
    {
        add_action('wp_enqueue_scripts', [$this, 'remove_smart_coupons_assets_on_cart'], 99999);
        // add cart page specific assets here
        (new MobiloCartShortcode())->init();
    }
    function remove_smart_coupons_assets_on_cart()
    {
        // Dequeue Smart Coupons styles
        $styles = ['cfw-blocks-styles', 'mollie-gateway-icons', 'mollie-components', 'mollie-applepaydirect', 'wc-blocks-packages-style', 'wc-blocks-style', 'wc-blocks-editor-style', 'woocommerce-add-to-cart-form-style', 'woocommerce-multi-currency', 'photoswipe', 'select2', 'smart-coupon', 'smart-coupon-designs', 'common', 'wc-blocks-style-breadcrumbs', 'wc-blocks-style-cart-link', 'wcs-checkout', 'wc-gift-cards-blocks-integration', 'woocommerce-smart-coupons-available-coupons-block', 'woocommerce-smart-coupons-send-coupon-form-block', 'woocommerce-smart-coupons-action-tab-frontend', 'wc-gc-css', 'wc-gift-cards-blocks-integration', 'brands-styles', 'woocommerce_subscriptions_gifting', 'sendcloud-checkout-css', 'ct-woocommerce-styles', 'wc-blocks-integration'];
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
