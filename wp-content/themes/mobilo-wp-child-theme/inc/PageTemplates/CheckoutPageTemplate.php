<?php

namespace Mobilo\WpTheme\PageTemplates;

use Mobilo\WpTheme\Feature\EDOFeature;
use Mobilo\WpTheme\Feature\OrgOrderProration;
use Throwable;

defined('ABSPATH') || exit;

class CheckoutPageTemplate extends PageTemplateLoader
{
    public $pageId = 'checkout';
    public function init()
    {
        $edo_feat = new EDOFeature();
        $edo_feat->actions_init();

        add_filter('woocommerce_coupons_enabled', [$this, 'disableWoocommerceCouponOnCheckout'], 10);
        add_action('wp_head', [$this, 'addCheckoutCss']);

        try {
            (new OrgOrderProration())->run();
        } catch (Throwable $e) {
            mobilo_log(__METHOD__, 'Error running OrgOrderProration: ' . $e->getMessage());
        }
    }

    public function addCheckoutCss()
    {
        ?>
        <style id="mobilo-custom-checkout-css">
            /* Remove trial text as it repeat 2 times */
            .cfw-cart-item-subtotal .subscription-price .subscription-details {
                display: none;
            }
        </style>
        <?php
    }

    public function disableWoocommerceCouponOnCheckout($enabled)
    {
        // disable coupon on checkout page
        return false;
    }
}