<?php

namespace Mobilo\WpTheme\Feature;

use Mobilo\WpTheme\Feature\BaseFeature;
use Mobilo\WpTheme\PageTemplates\CartPageTemplate;

defined('ABSPATH') || exit;

use DOMDocument;
use Mobilo\WpTheme\Models\CartModel;
use Throwable;
use WC_Product;
use WC_Subscriptions_Product;
use WP_User;

/**
 * Class PlanFeature
 * 
 * @package Mobilo\WpTheme\Feature
 */
class PlanFeature extends BaseFeature
{
    private static $featureKey = 'new_plans';

    public static $accountSettingsPageInjectId = 'new-plans';
    public static $featurePageOption = 'react_new_plans_page_id';
    public static $userPlanMetaKey = MOBILO_PREFIX . '_chosen_plan_sku';
    public static $userOrgMetaKey = MOBILO_PREFIX . '_user_org';
    public static $skuPrefix = "MCP_";

    public static $customOrderCookieKey = MOBILO_PREFIX . '_custom_order_mode';

    public static $cardsSku = ['MBC', 'MCC', 'MCC-P', 'MCC-M', 'MCC-W'];
    public static $accessoriesSku = ['NFC-SB', 'NFC-KF'];
    public static $digitalCardSku = 'MC_DIGITAL';
    public static $plansSku = ['MCP_PRO', 'MCP_TEAM', 'MCP_ENTERPRISE'];

    public function __construct()
    {
        parent::__construct(self::$featureKey);
    }

    public function actionInit()
    {

    }

    /**
     * A function that deprecates a product on the admin side.
     *
     * @return void
     */
    public function deprecateProductOnAdmin()
    {
        $_GET['exclude'] = '11186';
    }

    /**
     * Adds the product ID and SKU to the localize object.
     * TODO: deprecate this
     *
     * @since 1.0.1
     */
    public function addProductIdAndSku($reactLocalizeObjData)
    {
        if (is_cart()) {
            $data = [
                self::$digitalCardSku => wc_get_product_id_by_sku(self::$digitalCardSku),
            ];
            $reactLocalizeObjData['productIdAndSku'] = $data;
        }

        return $reactLocalizeObjData;
    }

    /**
     * Retrieves the current plan for the cart, checking user meta, URL, cookie, and falling back to default.
     *
     * @return array|null|string The plan data array or null if not found.
     */
    public static function get_cart_plan($return_only_sku = false)
    {
        // 1. Try to get plan SKU from user meta
        $planSku = NewPlansUserMeta::get_plan_sku();

        // 2. If not valid, try to get from URL query param 'sku'
        if (!self::is_valid_plan_sku($planSku)) {
            $urlPlanSku = isset($_GET['sku']) ? trim(sanitize_text_field($_GET['sku'])) : '';
            if (!empty($urlPlanSku)) {
                $planSku = strtoupper($urlPlanSku);
            }
        }

        // 3. If still not valid, try to get from cookie
        if (!self::is_valid_plan_sku($planSku)) {
            $planSku = self::get_plan_sku_from_cookie();
        }

        // 4. Fallback to default plan if still not valid
        if (!self::is_valid_plan_sku($planSku)) {
            $planSku = self::$skuPrefix . 'PRO';
        }

        if ($return_only_sku) {
            return $planSku;
        }
        // 5. Retrieve plan by SKU
        $plan = self::get_plan_by_sku($planSku);

        // 6. If plan found, update the plan SKU in cookie for persistence
        if (!empty($plan)) {
            self::set_plan_sku_in_cookie($planSku);
        } else {
            // remove cookie if plan not found
            self::set_plan_sku_in_cookie();
        }

        return $plan;
    }


    /**
     * Sets the quantity of accessories in the cart based on the card quantity.
     *
     * @param int $card_quantity The quantity of the card in the cart.
     * @return void
     */
    public static function set_accessories_quantity($card_quantity)
    {
        $cart = mc_get_cart();
        if (!$cart) {
            return;
        }
        $cart_items = $cart->get_cart();
        $accessories_sku = self::$accessoriesSku;

        // loop the cart items
        foreach ($cart_items as $key => $cart_item) {
            $cart_product_sku = $cart_item['data']->get_sku();
            if (in_array($cart_product_sku, $accessories_sku)) {
                $cart->set_quantity($key, $card_quantity);
            }
        }
    }

    /**
     * Ensures only allowed products are in the cart based on the plan SKU.
     *
     * This function checks the user's cart and removes any products that are not related
     * to the user's current plan, determined by the plan SKU. If the card is empty after
     * this check, it completely empties the cart. The function is designed to not run during AJAX requests.
     *
     * @return void
     */

    public static function check_products_in_cart(): void
    {
        try {
            $cart = mc_get_cart();
            if (!$cart) {
                return;
            }

            $plan_upgrade_sku = PlanUpgradeFeature::is_plan_upgrade_mode_enabled();
            if ($plan_upgrade_sku && !self::is_valid_plan_sku($plan_upgrade_sku)) {
                mobilo_log(__METHOD__, "Invalid plan upgrade found on cookie, sku: {$plan_upgrade_sku}");
            }

            // Get the plan
            $plan_sku = $plan_upgrade_sku ? $plan_upgrade_sku : self::get_cart_plan(true);
            // Get the product ID associated with the plan SKU.
            $plan_product_id = wc_get_product_id_by_sku($plan_sku);
            $plan = wc_get_product($plan_product_id);
            // If there's still no plan
            if (!$plan) {
                return;
            }
            $plan_sku = $plan->get_sku();

            // Get upsells and cross-sells.
            $products_sku = self::$cardsSku;
            $accessories_sku = self::$accessoriesSku;
            // Include the additional allowed product IDs
            $extra_allowed_skus = ['CSB_TEAM'];

            $allowed_skus = array_merge($products_sku, $accessories_sku, [self::$digitalCardSku], $extra_allowed_skus);

            // Combine all allowed product IDs
            $allowed_ids = [];
            foreach ($allowed_skus as $sku) {
                $product_id = wc_get_product_id_by_sku($sku);
                if ($product_id) {
                    $allowed_ids[] = $product_id;
                }
            }
            // add current plan product id & sku to allowed ids
            $allowed_ids[] = $plan_product_id;
            $allowed_skus[] = $plan_sku;

            // Retrieve the current cart contents.
            $cart_items = $cart->get_cart_contents();
            $card_count = 0;
            $accessories_count = 0;
            $plan_quantity = 0;
            foreach ($cart_items as $key => $cart_item) {
                $cart_product_sku = $cart_item['data']->get_sku();
                if (in_array($cart_product_sku, $products_sku)) {
                    $card_count += $cart_item['quantity'];
                }
                if ($cart_product_sku === self::$digitalCardSku) {
                    $plan_quantity += $cart_item['quantity'];
                }
                if (in_array($cart_product_sku, $accessories_sku)) {
                    $accessories_count += $cart_item['quantity'];
                }

                // If the current cart's plan SKU is not the current plan SKU and not in extra allowed list, empty cart.
                if (self::is_valid_plan_sku($cart_product_sku) && $cart_product_sku !== $plan_sku && !in_array($cart_product_sku, $extra_allowed_skus)) {
                    mc_empty_cart();
                    mobilo_log(__METHOD__, "Cart emptied, due to plan SKU mismatch: {$plan_sku} !== {$cart_product_sku}", 'info');
                    break;
                }

                // If the product ID is not in the allowed list, remove it.
                if (!in_array($cart_item['product_id'], $allowed_ids)) {
                    $cart->remove_cart_item($key);
                    $log_msg = "Removed product " . $cart_item['product_id'] . " from cart, due to not being in the allowed list of planSKU: {$plan_sku}";
                    mobilo_log(__METHOD__, $log_msg, 'info');
                }
            }
            // add the card quantity to the plan quantity
            $plan_quantity += $card_count;
            if ($cart->is_empty()) {
                return;
            }

            // plan quantity update, if not manual updated
            if (!CartPageTemplate::is_plan_manual_updated()) {
                $plan_cart_item = mc_get_cart_item_by_product_sku($plan_sku);
                if ($plan_cart_item) {
                    WC()->cart->set_quantity($plan_cart_item['key'], $plan_quantity);
                } else {
                    $result = WC()->cart->add_to_cart($plan_product_id, $plan_quantity);
                    if (!$result) {
                        mobilo_log(__METHOD__, "Failed to add plan to cart", 'error', [
                            'plan_product_id' => $plan_product_id,
                            'plan_quantity' => $plan_quantity,
                        ]);
                    }
                }
            } else {
                mobilo_log(__METHOD__, "Plan manual updated, skipping plan quantity update", 'info');
            }

            if ($accessories_count > 0) {
                self::set_accessories_quantity($card_count);
            }
            // If plan upgrade or custom order mode is enabled, adjust accessories and skip further processing.
            if (!PlanUpgradeFeature::is_plan_upgrade_mode_enabled() || !self::is_custom_order_mode_enabled()) {
                return;
            }

            // Prevent emptying cart if CSB_TEAM is present
            $csbTeamPresent = false;
            foreach ($cart->get_cart_contents() as $item) {
                $cart_product_sku = $item['data']->get_sku();
                if (in_array($cart_product_sku, $extra_allowed_skus)) {
                    $csbTeamPresent = true;
                    break;
                }
            }
            if ($csbTeamPresent) {
                mobilo_log(__METHOD__, "CSB_TEAM found — skipping cart emptying.", 'info');
                return;
            }

            // If the cart no longer contains the main plan product, empty it.
            // if (!self::isCardInCart($plan)) {
            //     $cart->empty_cart();
            // }

            // Update cart quantities based on main product.
            // self::update_cart_item_quantity(null, $plan); //TODO: add this function

        } catch (Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
        }
    }



    /**
     * Converts HTML content to a JSON array.
     *
     * This function takes an HTML string as input and uses the DOMDocument class to parse it.
     * It then searches for all `<ul>` elements in the parsed HTML and extracts the text content
     * of the previous sibling `<div>` element. The text content of each `<li>` element within
     * the `<ul>` is also extracted and stored in an array.
     *
     * The extracted data is then organized into an array of associative arrays, where each
     * array represents a `<ul>` element. The keys of the associative arrays are 'heading' and
     * 'contents', representing the text content of the previous sibling `<div>` element and
     * the text content of each `<li>` element, respectively.
     *
     * @param string $html The HTML content to be converted.
     * @return array The JSON array representation of the HTML content.
     *
     * @version 1.0.0
     * @since 1.0.0
     */
    public static function convert_html_to_json($html)
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true); // Disable error reporting for malformed HTML
        $dom->loadHTML($html);
        libxml_clear_errors();

        $ulNodes = $dom->getElementsByTagName('ul');
        $jsonData = [];

        foreach ($ulNodes as $ulNode) {
            $headingNode = $ulNode->previousSibling;
            while ($headingNode && $headingNode->nodeName !== 'div') {
                $headingNode = $headingNode->previousSibling;
            }

            if ($headingNode) {
                $heading = trim($headingNode->textContent);
                $listItems = $ulNode->getElementsByTagName('li');
                $contents = [];

                foreach ($listItems as $listItem) {
                    $contents[] = $listItem->textContent;
                }

                $jsonData[] = [
                    'heading' => $heading,
                    'contents' => $contents,
                ];
            }
        }

        return $jsonData;
    }

    /**
     * Retrieves a plan by its SKU.
     *
     * @param string $sku The SKU of the plan.
     * @return mixed The plan details, including title, price, sale price, billing cycle,
     *               short description, feature tagline, features (converted from HTML to JSON),
     *               billing interval, and billing period. Returns an empty array if the plan
     *               is not found or an error occurs.
     * @throws Throwable If an error occurs while retrieving the plan.
     */
    public static function get_plan_by_sku(string $sku, $return_wc_product = false)
    {
        try {
            if (!self::is_valid_plan_sku($sku)) {
                return [];
            }

            $productId = wc_get_product_id_by_sku($sku);

            if (!$productId) {
                return [];
            }
            $product = wc_get_product($productId);

            if (!$product || !WC_Subscriptions_Product::is_subscription($product)) {
                return [];
            }

            if ($return_wc_product) {
                return $product;
            }

            $currency = get_woocommerce_currency();
            $checkoutData = WC()->session->get('checkout_data');
            $billing_country = $checkoutData['billing_country'] ?? null;

            $regularPrice = $product->get_regular_price();

            $salePrice = $product->get_sale_price();
            if ($currency == 'EUR' && $billing_country !== 'GB') {
                $regularPrice = mc_get_include_vat_price($regularPrice);
                $salePrice = mc_get_include_vat_price($salePrice);
            }

            $desc = $product->get_description();
            $short_desc = $product->get_short_description();

            $billing_interval = WC_Subscriptions_Product::get_interval($product);
            $billing_period = WC_Subscriptions_Product::get_period($product);

            if ($billing_period == 'year' && $billing_interval == '1') {
                $regularPrice = (float) $regularPrice / 12;
                $salePrice = (float) $salePrice / 12;
            }

            $billing_cycle = "mo";
            $feature_tagline = $sku == 'MCP_PRO' ? '<strong style="color: #50A371;">First year FREE</strong> with a physical card' : '<strong style="color: #50A371;">All</strong> Mobilo features';
            $trial_length = $product->get_meta('_subscription_trial_length') ?? 0;
            $trial_text = "{$trial_length}-Day Free Trial";
            $plan = [
                'id' => $product->get_id(),
                'sku' => $product->get_sku(),
                'title' => $product->get_title(),
                'price' => mc_format_price($regularPrice),
                'sale_price' => mc_format_price($salePrice),
                'trial_text' => $trial_text,
                'billing_cycle' => $billing_cycle,
                'short_description' => $short_desc,
                'feature_tagline' => $feature_tagline,
                'features' => self::convert_html_to_json($desc),
                'billing_interval' => $billing_interval,
                'billing_period' => $billing_period,
            ];

            return $plan;
        } catch (Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
            return [];
        }
    }

    /**
     * Gets the plan SKU from the cookie.
     *
     * This function uses a static method to access the cookie data
     * and sanitize the retrieved value.
     *
     * @return string The plan SKU from the cookie, or an empty string if not found.
     */
    public static function get_plan_sku_from_cookie(): string
    {
        global $lwmc_chosen_plan_sku; // TODO: if needed change the name
        if (isset($lwmc_chosen_plan_sku)) {
            return $lwmc_chosen_plan_sku;
        }

        $planSku = $_COOKIE[self::$userPlanMetaKey] ?? '';
        return trim(sanitize_text_field($planSku));

    }

    /**
     * Sets the plan SKU in the cookie.
     *
     * This function uses a static method to set a cookie with the provided plan SKU.
     * The cookie has a very large expiry time (practically never expires).
     *
     * @param string $planSku The plan SKU to set in the cookie (optional, defaults to empty string).
     * @return void
     */
    public static function set_plan_sku_in_cookie(string $planSku = ''): void
    {
        $sanitizedSku = trim(sanitize_text_field($planSku));

        // TODO: fix Warning: Cannot modify header information - headers already
        // Set cookie with never expiry
        mc_set_cookie(self::$userPlanMetaKey, $sanitizedSku, time() + DAY_IN_SECONDS * 30);

        // Update global variable
        global $lwmc_chosen_plan_sku;
        $lwmc_chosen_plan_sku = $sanitizedSku;

        // Update $_COOKIE superglobal so the new value is available immediately
        $_COOKIE[self::$userPlanMetaKey] = $sanitizedSku;
    }


    /**
     * Checks if a plan SKU is valid.
     *
     * This function checks if the plan SKU starts with the expected prefix.
     *
     * @param string $planSku The plan SKU to check.
     * @return bool True if the plan SKU is valid, false otherwise.
     */
    public static function is_valid_plan_sku(string $planSku): bool
    {
        return str_starts_with(strtoupper($planSku), self::$skuPrefix);
    }

    /**
     * Checks if a product is in the cart (digital card ignored).
     *
     * @param WC_Product $plan_product The plan product.
     * @param bool $ignoreDigitalCard Whether to ignore the digital card. Defaults to true.
     * @return bool True if a product with the given IDs is in the cart, false otherwise.
     *
     * @version 1.0.2
     * @since 1.0.1
     */
    public static function isCardInCart(WC_Product $plan_product, bool $ignoreDigitalCard = true): bool
    {
        // Initialize the result variable
        $result = false;

        $cart = mc_get_cart();
        // Check if there is a cart
        if (!$cart) {
            return $result;
        }

        // Get the IDs of the upsell products
        $card_ids = $plan_product->get_upsell_ids();

        // Get the keys of the cart items that have IDs in the card_ids array
        $cart_item_keys = array_intersect(
            array_column($cart->get_cart(), 'product_id'),
            $card_ids
        );

        // Filter out the cart items that have the digital card SKU if ignoreDigitalCard is true
        if ($ignoreDigitalCard) {
            $cart_item_keys = array_filter($cart_item_keys, function ($product_id) {
                $product_sku = mc_get_sku_from_product_id($product_id);
                return $product_sku !== self::$digitalCardSku;
            });
        }

        // Check if there are any cart items left after filtering
        $result = !empty($cart_item_keys);

        return $result;
    }

    /**
     * May be Updates the cart when user logged in
     *
     * @since 1.0.2
     */
    public function maybeUpdateCart($username, WP_User $user)
    {
        try {
            $cart = mc_get_cart();
            // Skip execution if the request is an AJAX request or cart is empty.
            if (!$cart || wp_doing_ajax()) {
                return;
            }
            mobilo_log(__METHOD__, "maybeUpdateCart called after opt", "info");
            self::check_products_in_cart();
        } catch (Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
        }
    }

    /**
     * Enables the custom order mode by setting a cookie.
     *
     * @since 1.0.3
     * @version 1.0.0
     * @return void
     */
    public static function enable_custom_order_mode()
    {
        // disable the upgrade mode if enabled
        PlanUpgradeFeature::disable_plan_upgrade_mode();
        setcookie(self::$customOrderCookieKey, '1', time() + 10 * 60, '/'); // for 10 min
    }

    /**
     * Disables the custom order mode by deleting the cookie.
     *
     * @since 1.0.3
     * @version 1.0.0
     * @return void
     */
    public static function disable_custom_order_mode()
    {
        if (self::is_custom_order_mode_enabled()) {
            setcookie(self::$customOrderCookieKey, '', time() - 3600, '/');
        }
    }

    /**
     * Checks if the custom order mode is enabled.
     *
     * @since 1.0.3
     * @version 1.0.0
     * @return bool
     */
    public static function is_custom_order_mode_enabled()
    {
        return isset($_COOKIE[self::$customOrderCookieKey]) && $_COOKIE[self::$customOrderCookieKey] == '1';
    }
}
