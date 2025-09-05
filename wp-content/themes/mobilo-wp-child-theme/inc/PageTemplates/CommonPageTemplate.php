<?php

namespace Mobilo\WpTheme\PageTemplates;

use Mobilo\WpTheme\Feature\EDOFeature;
use Mobilo\WpTheme\Feature\OrganizationIdManager;
use Mobilo\WpTheme\Feature\OrderProration;
use Mobilo\WpTheme\Services\CouponLinkSupportService;

defined('ABSPATH') || exit;

class CommonPageTemplate extends PageTemplateLoader
{
    public $pageId = '';
    public function init()
    {
        // Your initialization code here
        (new CouponLinkSupportService())->init();
        add_action('woocommerce_applied_coupon', [$this, 'set_org_from_coupon'], 10);
        add_action('wp_loaded', [$this, 'empty_existing_coupon'], 18);
        add_action('wp_loaded', [$this, 'empty_cart'], 20);
        // disable the admin bar on the frontend
        if (env('WP_ENV') === 'prod') {
            add_filter('show_admin_bar', '__return_false');
        }

        $orderProration = new OrderProration();
        $orderProration->init();

    }

    public function empty_cart()
    {
        if (isset($_GET['empty_cart']) && 'yes' === esc_html($_GET['empty_cart'])) {
            mc_empty_cart();

            $referer = wp_get_referer() ? esc_url(remove_query_arg('empty_cart')) : wc_get_cart_url();
            wp_safe_redirect($referer);
        }
    }
    /**
     * Empty existing coupon when new coupon is being applied
     *
     */
    public function empty_existing_coupon()
    {
        if (isset($_GET['coupon-code']) && !empty($_GET['coupon-code'])) {
            // TODO: validate the coupon code ( single & multi )
            // if this is a EDO coupon code, empty the cart
            // $coupon_code = sanitize_text_field($_GET['coupon-code']);
            // $coupon_id = wc_get_coupon_id_by_code($coupon_code);

            // $has_edo_by_coupon = OrganizationIdManager::check_coupon_edo_status($coupon_id);

            // if ($has_edo_by_coupon) {
            //     mc_empty_cart();
            // }
            $cart = mc_get_cart();
            if ($cart) {
                $cart->remove_coupons();
            }
        }
    }

    public function set_org_from_coupon($coupon_code)
    {
        $coupon_id = wc_get_coupon_id_by_code($coupon_code);

        // Get the Organization ID from the coupon
        $org_id = get_post_meta($coupon_id, OrganizationIdManager::getOrganizationIdKey(), true);
        debugg($org_id);
        if (!empty($org_id) && mc_get_cart()->get_cart_contents_count() > 0) {
            // Save the organization ID in a cookie
            setcookie(OrganizationIdManager::getOrganizationIdKey(), $org_id, time() + 36000, '/'); // Expire in 10 hour

            // Redirect to the checkout page without logging out
            wp_redirect(wc_get_checkout_url());
            exit;
        }
    }
}
