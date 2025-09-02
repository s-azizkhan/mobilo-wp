<?php

namespace Mobilo\WpTheme\PageTemplates;

use Mobilo\WpTheme\Feature\EDOFeature;

defined('ABSPATH') || exit;

class CheckoutPageTemplate extends PageTemplateLoader
{
    public $pageId = 'checkout';
    public function init()
    {
        $edo_feat = new EDOFeature();
        $edo_feat->actions_init();

        add_filter('woocommerce_coupons_enabled', [$this, 'disable_woocommerce_coupon_on_checkout'], 10);
    }

    public function disable_woocommerce_coupon_on_checkout($enabled)
    {
        // disable coupon on checkout page
        return false;
    }
}