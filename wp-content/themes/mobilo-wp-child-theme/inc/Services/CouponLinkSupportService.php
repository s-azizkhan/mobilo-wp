<?php

namespace Mobilo\WpTheme\Services;

use Mobilo\WpTheme\Feature\EDOFeature;
use Mobilo\WpTheme\Feature\NewPlansUserMeta;
use Mobilo\WpTheme\Feature\PlanFeature;
use Throwable;

defined('ABSPATH') || exit;

/**
 * Service class that manages coupon-related logic in WooCommerce to support plan feature.
 *
 * This class extends FeatureV2 and provides methods to handle various
 * WooCommerce hooks and filters related to coupons, plan features, and EDO modes.
 *
 * @link logicwind.com
 * @version 1.0.1
 * @since 0.4.5
 * @author Aziz Khan <aziz@logicwind.com>
 */
class CouponLinkSupportService
{
    public static $freePlanSource = 'free_plan_source';  // Source identifier for free plan coupons
    public static $couponSource = 'wc_sc_product_source';  // Source identifier for product coupons

    /**
     * Constructor for the CouponLinkSupportService class.
     */
    public function __construct()
    {
    }

    /**
     * Initialize WooCommerce hooks and filters related to coupons and plan features.
     */
    public function init()
    {
        add_action('woocommerce_applied_coupon', [$this, 'clearPlanWhenCouponApplied'], 1);
        add_filter('woocommerce_add_cart_item_data', [$this, 'maybeRemoveCouponAttribute'], 10, 2);
        add_action('wp', [$this, 'clearCouponOnCartEmpty']);
        add_action('wp', [$this, 'checkProductsInCart'], 1);
    }

    /**
     * Clears EDO order mode and removes coupons when the cart is emptied.
     */
    public function clearCouponOnCartEmpty()
    {
        // Check if the user is on the cart page and EDO order mode is enabled
        if (!is_cart() || !EDOFeature::isEdoOrderModeEnabled()) {
            return;
        }

        try {
            // Disable EDO order mode and remove all coupons from the cart
            EDOFeature::disableDEdoOrderMode();
            WC()->cart->remove_coupons();
            mobilo_log(__METHOD__, "EDO coupon code removed due to user visited cart page after.", 'info');
        } catch (Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
        }
    }

    /**
     * Clears the user's plan and empties the cart when a coupon is applied.
     */
    public function clearPlanWhenCouponApplied()
    {
        // Clear the plan cookie
        setcookie(NewPlansUserMeta::$planMetaKey, '', time() - 7, '/');

        // If the cart is empty, return early
        if (WC()->cart->is_empty()) {
            return;
        }

        // Remove each item from the cart
        $cartData = WC()->cart->get_cart_contents();
        if ($cartData) {
            foreach (array_keys($cartData) as $key) {
                WC()->cart->remove_cart_item($key);
            }
        }
    }

    /**
     * Modifies cart item data to remove or update coupon attributes based on certain conditions.
     *
     * @param array $cart_item_data Data of the cart item being added.
     * @param int $product_id The ID of the product being added to the cart.
     * @return array The modified cart item data.
     */
    public function maybeRemoveCouponAttribute($cart_item_data, $product_id)
    {
        try {
            // Check if EDO order mode is enabled or if there's no coupon source in cart item data
            if (EDOFeature::isEdoOrderModeEnabled() || empty($cart_item_data[self::$couponSource])) {
                return $cart_item_data;
            }

            // Retrieve SKU of the product and validate if it's a valid plan SKU
            $planSku = mc_get_sku_from_product_id($product_id);
            if (!PlanFeature::is_valid_plan_sku($planSku)) {
                return $cart_item_data;
            }

            // Get product details and check if the product price is zero or less
            $planProduct = wc_get_product($product_id);
            if ($planProduct && $planProduct->get_price() <= 0) {
                // Update cart item data to switch the coupon attribute to free plan source
                $couponCode = $cart_item_data[self::$couponSource];
                unset($cart_item_data[self::$couponSource]);
                $cart_item_data[self::$freePlanSource] = $couponCode;
                mobilo_log(__METHOD__, "Coupon[$couponCode] attribute updated for plan $planSku", 'info');
            }
        } catch (Throwable $th) {
            // Log any exceptions encountered during the process
            mobilo_log(__METHOD__, $th->getMessage());
        }

        return $cart_item_data;
    }

    /**
     * Checks if there are any products in the cart and updates the cart accordingly.
     */
    public function checkProductsInCart()
    {
        // Check if WooCommerce cart is set and not empty
        $cart = mc_get_cart();
        if (!$cart) {
            return;
        }

        // Update cart contents based on conditions
        $this->maybeUpdateProPlanProductInCart($cart->get_cart_contents());
    }

    /**
     * Updates the quantity of a product in the cart to match the maximum allowed purchase quantity.
     *
     * @param array $cartData Array of cart item data.
     */
    public function maybeUpdateProPlanProductInCart($cartData)
    {
        try {
            // Find a free plan item in the cart
            $freePlanItem = $this->findFreePlanItem($cartData);
            if (!$freePlanItem) {
                return;
            }

            // Get the maximum purchase quantity for the plan
            $max_purchase_qty = $this->getMaxPurchaseQuantity($freePlanItem['sku']);
            if ($max_purchase_qty <= 0) {
                return;
            }

            // Update the cart item quantity to the maximum allowed purchase quantity
            $planCartItem = WC()->cart->get_cart_item($freePlanItem['key']);
            if ($planCartItem['quantity'] !== $max_purchase_qty) {
                WC()->cart->set_quantity($freePlanItem['key'], $max_purchase_qty);
                mobilo_log(__METHOD__, "Coupon's Plan quantity updated to max($max_purchase_qty) for {$freePlanItem['sku']}", 'info');
            }
        } catch (Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
        }
    }

    /**
     * Finds a free plan item in the cart data.
     *
     * @param array $cartData Array of cart item data.
     * @return array|null Returns the free plan item details or null if not found.
     */
    private function findFreePlanItem($cartData)
    {
        foreach ($cartData as $key => $data) {
            if (!empty($data[self::$freePlanSource])) {
                // Return the cart item key and SKU if a free plan source is found
                return [
                    'key' => $key,
                    'sku' => $data['data']->get_sku()
                ];
            }
        }
        return null; // Return null if no free plan item is found
    }

    /**
     * Retrieves the maximum purchase quantity for a given product SKU based on the plan's features.
     *
     * @param string $productSku SKU of the product.
     * @return int The maximum purchase quantity for the given SKU.
     */
    private function getMaxPurchaseQuantity($productSku)
    {
        // // TODO: change here to to modify pro plan max purchase quantity
        // if ($productSku == 'MCP_PRO') {
        //     return 5;
        // }
        // return unlimited
        return 99999;
    }
}
