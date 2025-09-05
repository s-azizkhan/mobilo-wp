<?php

namespace Mobilo\WpTheme\PageTemplates;

use Mobilo\WpTheme\Feature\PlanFeature;
use Throwable;
use WCS_Customer_Store;

defined('ABSPATH') || exit;

class CheckoutAjaxOnlyPageTemplate
{

    public function __construct()
    {
        add_filter('woocommerce_subscriptions_product_trial_length', [$this, 'modifySubscriptionTrialLength'], 9999999);
        add_filter('woocommerce_subscriptions_product_trial_period', [$this, 'modifySubscriptionTrialPeriod'], 9999999);
    }

    public function modifySubscriptionTrialLength($length)
    {
        if ($this->hasBoughtSubscriptionBefore() || PlanFeature::is_custom_order_mode_enabled()) {
            return 0;
        }
        if ($this->isEligibleForTrialModification()) {
            $length = 1;
        }
        return $length;
    }

    public function modifySubscriptionTrialPeriod($period)
    {
        if (!$this->hasBoughtSubscriptionBefore() && $this->isEligibleForTrialModification()) {
            $period = 'year';
        }
        return $period;
    }

    /**
     * Check if current user has any subscription/plan before
     * @return bool
     */
    public function hasBoughtSubscriptionBefore()
    {
        // check: if user already purchased any subscription/plan
        if (is_user_logged_in()) {
            $ids = WCS_Customer_Store::instance()->get_users_subscription_ids(get_current_user_id());
            if ($ids) {
                return true;
            }
        }
        return false;

    }


    /**
     * Check if the trial modification is eligible for the current cart
     * @return bool
     */
    public function isEligibleForTrialModification()
    {
        try {
            $cart = mc_get_cart();
            if (!$cart || $cart->is_empty()) {
                return false;
            }

            // Check: if cart contains any physical card SKUs then only processed
            if (!PlanFeature::cart_contains_physical_card()) {
                return false;
            }

            // Find MCP_PRO subscription in cart and modify its trial length to 1 year
            $cart_items = $cart->get_cart_contents();
            $subscription_types = ['variable-subscription', 'subscription'];

            foreach ($cart_items as $cart_item_key => $cart_item) {
                $product = $cart_item['data'];
                $product_sku = $product->get_sku();
                if (!in_array($product->get_type(), $subscription_types)) {
                    continue;
                }
                // Check if this is the MCP_PRO subscription and modify its trial
                if ($product_sku === 'MCP_PRO') {
                    return true;
                }
            }
            return false;
        } catch (Throwable $e) {
            mobilo_log(__METHOD__, $e->getMessage());
            return false;
        }
    }

}
