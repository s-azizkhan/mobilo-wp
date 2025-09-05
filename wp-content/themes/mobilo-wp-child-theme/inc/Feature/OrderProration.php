<?php

namespace Mobilo\WpTheme\Feature;

use DateTime;
use Mobilo\WpTheme\Feature\OrgOrderProration;
use Mobilo\WpTheme\Feature\PlanUpgradeFeature;
use Throwable;
use WC_Product;
use WP_Post;

defined('ABSPATH') || exit;

/**
 * OrderProration
 *
 * @package Mobilo\WpTheme\Feature
 * @category Class
 */
class OrderProration
{
    public static $proration_mode_session_name = MOBILO_PREFIX . '_proration_mode';

    public function __construct()
    {
    }

    public function init()
    {
        $this->action_init();
    }
    /**

     * Initializes the actions and filters for the specified functionality for this class.

     ** Adds extra user profile fields to the show_user_profile and edit_user_profile hooks.
     ** Saves extra user profile fields on personal_options_update and edit_user_profile_update hooks.
     ** Adds an action for calculating order taxes before taxes are calculated in the WooCommerce admin panel.
     ** Adds meta boxes to the shop_order pages in the admin panel.
     ** Saves the data of the meta field when a post is saved.
     */
    public function action_init()
    {
        // Add an action to update the user's expiry date on the 'woocommerce_thankyou' event.
        add_action('woocommerce_thankyou', [$this, 'order_update_user_expiry_date'], 4);

        // Add a filter to modify the subscription synchronization date on the 'woocommerce_subscriptions_product_sync_date' event.
        add_filter('woocommerce_subscriptions_product_sync_date', [$this, 'prorate_subscription_date'], 999, 2);

        // Add extra user profile fields to be displayed on the user profile pages.
        add_action('show_user_profile', [$this, 'extra_user_profile_fields']);
        add_action('edit_user_profile', [$this, 'extra_user_profile_fields']);

        // Save the extra user profile fields when the profile is updated.
        add_action('personal_options_update', [$this, 'save_extra_user_profile_fields']);
        add_action('edit_user_profile_update', [$this, 'save_extra_user_profile_fields']);

        // Perform calculations on order taxes before they are calculated in the WooCommerce admin panel.
        add_action("woocommerce_order_before_calculate_taxes", [$this, "order_before_calculate_totals"], 1, 2);

        // Add meta boxes to the shop_order pages in the admin panel.
        add_action('add_meta_boxes', [$this, 'add_proration_meta_boxes']);

        // Save the data of the meta field when a post is saved.
        add_action('save_post', [$this, 'save_wc_order_other_fields'], 10, 1);

        // Display a custom checkbox in My Account > Account details
        add_action('woocommerce_edit_account_form', [$this, 'display_edit_account_checkbox_field']);

        // Save checkbox field value for My Account > Account details
        add_action('woocommerce_save_account_details', [$this, 'save_checkbox_value_to_account_details'], 10, 1);

        // Add an action to update the user's expiry date on the 'woocommerce_subscription_renewal_payment_complete' event. when subscription renewed
        //add_action('woocommerce_subscription_renewal_payment_complete', [$this, 'renew_update_user_expiry_date']); // TODO: handle this (WIP)

        // Add an action to update the user's expiry date on the 'woocommerce_subscriptions_recurring_subscription_totals' event.
        //add_action('woocommerce_subscriptions_recurring_subscription_totals', [$this, 'updateSubscriptionNextPayDateOnCart'], 1);

        add_filter('gettext_woocommerce', [$this, 'maybeChangeSubtotalText'], 10, 2);
        add_action('wp', [$this, 'disableProrationModeSessionOnPageLoad']);
    }

    /**
     * Updates the next payment date for each recurring cart in the WooCommerce cart,
     * if the user is logged in and has a proration date.
     *
     * @param array $recurring_carts An array of recurring carts in the WooCommerce cart.
     * @return void
     * @deprecated 1.0.1
     */
    public function updateSubscriptionNextPayDateOnCart($recurring_carts)
    {
        if (!is_user_logged_in()) {
            return;
        }
        $next_payment_date = self::get_user_proration_date(get_current_user_id());

        if (!$next_payment_date) {
            return;
        }
        // Create a DateTime object from the original date
        $formatted_next_payment_date = DateTime::createFromFormat('Y-m-d\TH:i', $next_payment_date);

        // Check if the date object is created successfully
        if (!$formatted_next_payment_date) {
            return;
        }
        // Convert the date to the desired format 'Y-m-d H:i:s'
        $formatted_date = $formatted_next_payment_date->format('Y-m-d H:i:s');

        // update the next payment date for each recurring cart
        foreach (WC()->cart->recurring_carts as $recurring_cart_key => $recurring_cart) {
            if (isset($recurring_cart->next_payment_date) && $recurring_cart->next_payment_date) {
                $recurring_cart->next_payment_date = $formatted_date;
            }
        }
    }

    /**
     * Update user expire date when subscription renewed
     *
     * @param \WC_Subscription $subscription
     * @since 1.0.0
     * @version 1.0.0
     * @return void
     */
    public function renew_update_user_expiry_date(\WC_Subscription $subscription)
    {
        $user_id = $subscription->get_customer_id();
        $next_payment_date = $subscription->get_date('next_payment');
        $user_expiration_date = self::get_user_subscription_expiration_date($user_id);
    }

    /**
     ** Adds the proration meta box to the shop_order post type in the admin panel.
     ** The meta box is added only if the admin side proration is enabled.
     */
    public function add_proration_meta_boxes()
    {
        // Check if admin side proration is enabled
        if ($this->isAdminSideProrationEnable()) {
            // Add the proration meta box to the shop_order post type
            add_meta_box('lwmc_proration_meta_box', __('Order Proration', 'woocommerce'), [$this, 'add_proration_meta_box_fields'], 'shop_order', 'side', 'high');
        }
    }

    private function isAdminSideProrationEnable()
    {
        try {
            $res = get_option('lwmc_settings_admin_side_proration') == 'true' ? true : false;
            return $res;
        } catch (Throwable $e) {
            error_log('Error loading ' . __FUNCTION__ . ' : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Displays and handles the meta box fields for proration.
     *
     * @param WP_Post $post The current post object.
     * @since 1.0.0
     * @version 1.0.0
     */
    public function add_proration_meta_box_fields($post)
    {
        $meta_field_data = get_post_meta($post->ID, '_lwmc_proration_enable', true);

        // Check if the current page is the create order page, then default to checked
        if (get_current_screen()->action === 'add' && $this->isAdminSideProrationDefaultEnable()) {
            $meta_field_data = 'checked';
        }

        $nonce_field = '<input type="hidden" name="lwmc_proration_meta_field_nonce" value="' . wp_create_nonce() . '">';
        $checkbox_field = '<p style="border-bottom:solid 1px #eee;padding-bottom:13px;">Check this box to enable:
            <input type="checkbox" id="lwmc_proration_enable" name="lwmc_proration_enable" ' . $meta_field_data . ' value="true" ></p>';

        echo $nonce_field . $checkbox_field;
    }
    private function isAdminSideProrationDefaultEnable()
    {
        try {
            $res = get_option('lwmc_settings_admin_side_proration_default') == 'true' ? true : false;
            return $res;
        } catch (Throwable $e) {
            error_log('Error loading ' . __FUNCTION__ . ' : ' . $e->getMessage());
            return null;
        }
    }


    public function save_wc_order_other_fields($post_id)
    {

        // We need to verify this with the proper authorization (security stuff).

        // Check if our nonce is set.
        if (!isset($_POST['lwmc_proration_meta_field_nonce']) || !isset($_POST['lwmc_proration_enable'])) {
            return $post_id;
        }
        $nonce = $_REQUEST['lwmc_proration_meta_field_nonce'];

        //Verify that the nonce is valid.
        if (!wp_verify_nonce($nonce)) {
            return $post_id;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        // Check the user's permissions.
        if ('page' == $_POST['post_type']) {

            if (!current_user_can('edit_page', $post_id)) {
                return $post_id;
            }
        } else {

            if (!current_user_can('edit_post', $post_id)) {
                return $post_id;
            }
        }
        // --- Its safe for us to save the data ! --- //

        $value = $_POST['lwmc_proration_enable'] == true ? 'checked' : '';
        // Sanitize user input  and update the meta field in the database.
        update_post_meta($post_id, '_lwmc_proration_enable', $value);
    }

    /**
     * Get the prorated price of a product based on the user's expiry date.
     *
     * @param WC_Product $product The product object.
     * @param string $user_exp_date The user's expiry date in the format `Y-m-d`.
     * @return string|number The prorated price.
     * @deprecated 1.0.0
     */
    public static function get_prorated_price_v1($product, $user_exp_date)
    {
        // Return the actual price if the user's expiry date is on past
        $p_price = $product->get_price();
        // Prorate only when the user expiry date is future date
        if (date('Y-m-d') < $user_exp_date) {
            $today = date_create(date('Y-m-d'));
            $exp_date = date_create((string) $user_exp_date);
            $datediff = date_diff($today, $exp_date)->format("%a"); // Getting the different days
            $n_price = $product->get_price(); // Get the product actual price
            $p_price = ($n_price / 365) * $datediff; // calculate the proration price
        }

        return mc_format_price($p_price);
    }

    /**
     * Get the prorated price of a product based on the user's expiry date.
     *
     * @param WC_Product $product The product object.
     * @param string $user_exp_date The user's expiry date in the format `Y-m-d`.
     * @return string|number The prorated price.
     * @since 1.0.0
     * @version 1.0.0
     */
    public static function get_prorated_price(WC_Product $product, string $user_exp_date)
    {
        // Return the actual price if the user's expiry date is in the past.
        if (date('Y-m-d') >= $user_exp_date) {
            return $product->get_price();
        }

        // Get the difference between the current date and the user's expiry date.
        $today = date_create(date('Y-m-d'));
        $exp_date = date_create($user_exp_date);
        $datediff = date_diff($today, $exp_date)->format("%a"); // Getting the different days
        $datediff = $datediff > 365 ? 365 : absint($datediff);

        // Calculate the prorated price.
        $prorated_price = $product->get_price() / 365 * absint($datediff);

        // Round the prorated price to two decimal places.
        return mc_format_price($prorated_price);
    }

    /**
     * Get the user's expiration date.( who already purchased any subscription Items ) from site
     *
     * @param int $user_id The user ID.
     * @return string|false The expiration date, or false if the user has no expiration date.
     * @since 1.0.0
     * @version 1.0.1
     */
    public static function get_user_subscription_expiration_date(int $user_id, $update_proration = false)
    {
        // Validate the user ID.
        $user_id = absint($user_id);

        // Get the user's expiration date from the meta table.
        $_exp_date = self::get_user_proration_date($user_id);
        $org_proration = OrgOrderProration::getProrationByAdminId($user_id);
        $exp_date = $org_proration ? $org_proration->expiry_date : $_exp_date;

        // If the expiration date is today or in the past, return current date.
        if (time() >= strtotime($exp_date)) {
            $user_exp_date = date('Y-m-d H:i:s', strtotime("+1 year", strtotime(date_format(new DateTime(date('Y-m-d H:i:s')), 'Y-m-d H:i:s'))));
            // update user's expiry date
            if ($update_proration) {
                self::set_user_proration_date($user_id, $user_exp_date);

                if ($org_proration && $org_proration->org_id) {
                    OrgOrderProration::updateOrgProrationDate($org_proration->org_id, $user_exp_date);
                }
            }
            return $user_exp_date;
        }

        // Return the expiration date.
        return $exp_date;
    }

    /**
     * Apply proration to subscription products in WooCommerce orders.
     *
     * @param array $args The arguments passed to the `woocommerce_order_before_calculate_taxes` hook.
     * @hook woocommerce_order_before_calculate_taxes
     * @param \WC_Order $order The WooCommerce order object.
     * @since 1.0.0
     * @version 1.0.0
     */
    public function order_before_calculate_totals(array $args, \WC_Order $order)
    {
        // Get the user ID from the cookie.
        $user_id = (int) mc_get_cookie("prorationUserId");
        // Get agreed pricing value
        $enable_proration = self::get_user_agree_pricing($user_id);
        // Check `lwmc_agree_pricing` is enabled or not
        if ($enable_proration != 1) {
            return false;
        }

        // Check if the current user has the `manage_woocommerce` capability.
        if (!current_user_can('manage_woocommerce')) {
            return false;
        }
        // Get the order ID.
        $order_id = $order->get_id();
        // Get the proration enabled status from the cookie.
        $isProrationEnabled = mc_get_cookie("prorationEnabled");
        // Check if the order type is `shop_order`, the user ID is not empty, and proration is enabled.
        if (get_post_type($order_id) == 'shop_order' && $user_id !== '') {
            $subscription_product_types = ['subscription', 'subscription_variation'];
            // Get the order items.
            foreach ($order->get_items() as $item_id => $item) {
                // Get the product object.
                $product = $item->get_product();
                // Check if the product is a subscription product.
                if ($product->is_type($subscription_product_types) && $isProrationEnabled == "true") {
                    // Get the user expiration date from the user ID.
                    $user_expiration_date = self::get_user_subscription_expiration_date($user_id, true);
                    // Get the prorated price.
                    $p_price = self::get_prorated_price($product, $user_expiration_date);
                    // Set the prorated price to the order item.
                    $item->set_subtotal($p_price);
                    $product_quantity = $item->get_quantity();
                    $item->set_total($product_quantity * $p_price);
                } else if ($product->is_type($subscription_product_types) && $isProrationEnabled !== "true") {
                    // If proration is not enabled, set the default product price.
                    $item->set_subtotal($product->get_price());
                    $product_quantity = $item->get_quantity();
                    $item->set_total($product_quantity * $product->get_price());
                }
            }
        }
    }

    public function extra_user_profile_fields($user)
    {
        $exp_date = strtotime(esc_attr(get_the_author_meta('expire_date', $user->ID)));
        $exp_date = date('Y-m-d\TH:i', $exp_date);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="address"><?php _e("Proration date, (formerly expiry date)"); ?></label></th>
                <td>
                    <input type="datetime-local" name="expire_date" id="expire_date" value="<?php echo $exp_date; ?>"
                        class="regular-date" />
                    <br />
                </td>
            </tr>
        </table>
        <?php
    }
    public function save_extra_user_profile_fields($user_id)
    {
        if (!current_user_can('edit_user', $user_id) || !isset($_POST['expire_date'])) {
            return false;
        }
        self::set_user_proration_date($user_id, $_POST['expire_date']);
    }

    public static function get_user_proration_date($user_id)
    {
        $r = get_user_meta($user_id, 'expire_date', true);
        return $r;
    }

    public static function set_user_proration_date($user_id, $date)
    {
        // return if date is less than current date
        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            return;
        }
        error_log("User's #$user_id expiry date update to => $date");
        $r = update_user_meta($user_id, 'expire_date', $date);
        return $r;
    }
    /**
     * Updates the expiry date for a user based on the order information.(proration)
     *
     * This function retrieves the order details based on the provided order ID and updates the expiry date
     * for the corresponding user. It checks the items in the order, updates the user's account type based on
     * the product category, and determines if proration needs to be updated. If proration update is required,
     * it calculates the next payment date and compares it with the current expiry date. If they are different,
     * it updates the user's expiry date and logs the change.
     *
     * @param int $order_id The ID of the order to process.
     * @since 1.0.0
     * @version 1.0.1
     * TODO: how to handle for new org/order/user
     */
    public function order_update_user_expiry_date($order_id)
    {
        self::disableProrationModeSession();
        // Get the order and user ID
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();

        // Initialize variables
        $_proration_date = self::get_user_proration_date($user_id);
        $org_proration = OrgOrderProration::getProrationByAdminId($user_id);
        if ($org_proration) {
            $current_proration_date = $org_proration->expiry_date;
        } else {
            $current_proration_date = $_proration_date;
        }

        //$subscription_product_types = ['subscription', 'subscription_variation'];

        // Check each item in the order
        //foreach ($order->get_items() as $item) {

        //$product = $item->get_product();
        //// Set the flag to update proration if there is a subscription product
        //if ($product->is_type($subscription_product_types)) {
        //    $update_proration = true;
        //    break;
        //}
        //}

        // Get all related subscriptions for this order
        $subscriptions = wcs_get_subscriptions_for_order($order_id, array('order_type' => 'any'));
        // if there is no subscription
        if (empty($subscriptions)) {
            return false;
        }

        $next_payment_date = null;
        $update_proration = false;

        foreach ($subscriptions as $subscription_id => $subscription_obj) {
            if ($subscription_obj->get_parent_id() == $order_id) {
                // Update the next payment date with the subscription's date
                $next_payment_date = $subscription_obj->get_time('next_payment');
                $next_payment_date = (new DateTime("@$next_payment_date"))->format('Y-m-d H:i:s');
                // Check if proration update is necessary
                if (strtotime($current_proration_date) == strtotime($next_payment_date)) {
                    $update_proration = false;
                } else {
                    $update_proration = true;
                }
                break; // Stop the loop after finding the correct subscription
            }
        }

        // Update the user's proration date if necessary
        if ($update_proration && $next_payment_date) {
            mobilo_log(__METHOD__, sprintf(__("%d User's proration date updated %s => to %s,  OrderId #%s", 'mobilo'), $user_id, $current_proration_date, $next_payment_date, $order_id), 'info');
            self::set_user_proration_date($user_id, $next_payment_date);

            if ($org_proration) {
                OrgOrderProration::updateOrgProrationDate($org_proration->org_id, $next_payment_date);
            } else {
                // TODO: need to to work on this ( when new org created)
                //OrgOrderProration::createOrgProration($user_id, $next_payment_date);
            }
        } else {
            // TODO: need to to work on this ( when new org created)
            mobilo_log(__METHOD__, sprintf(__("%d User's proration date no need to update  %s => to %s,  OrderId #%s", 'mobilo'), $user_id, $current_proration_date, $next_payment_date, $order_id), 'info');
        }
    }

    /**
     * Get prorated subscription payment date based on the user's current subscription status
     *
     * @param string $payment_date The default payment date
     * @param object $product The product being purchased
     *
     * @return array|string The prorated payment date as an array containing day and month
     * @since 1.0.0
     * @version 1.0.2
     */
    public function prorate_subscription_date($payment_date, $product)
    {
        try {
            // when user id not logged in or plan upgrading return the current date
            if (!is_user_logged_in() || PlanUpgradeFeature::is_plan_upgrade_mode_enabled()) {
                self::disableProrationModeSession();

                // return current date
                $day = date("d");
                $month = date("m");
                // Format payment date as an array
                $payment_date = [
                    "day" => $day,
                    "month" => $month,
                ];
                return $payment_date;
            }

            $currentUserId = get_current_user_id();
            // Return agreed pricing is enabled or not
            $enable_proration = self::get_user_agree_pricing($currentUserId);
            if ($enable_proration != 1) {
                return $payment_date;
            }

            // Get the user's subscription expiration date
            //$org_proration = OrgOrderProration::getProrationByAdminId(get_current_user_id());
            //if (!$org_proration) {
            //    return $payment_date;
            //}
            //$expire_date = $org_proration->expiry_date;
            $expire_date = self::get_user_subscription_expiration_date($currentUserId);

            // If the user has an expiration date & not in past, use that for prorated payment date
            if (!empty($expire_date) && strtotime($expire_date) > time()) {

                $time = strtotime($expire_date);
                $day = date("d", $time);
                $month = date("m", $time);

                // enable cooke when current day was not equal to day
                if ($day != date("d")) {
                    self::enableProrationModeCookie();
                } else {
                    self::disableProrationModeSession();
                }

                // Format payment date as an array
                $payment_date = [
                    "day" => $day,
                    "month" => $month,
                ];
            } else {
                self::disableProrationModeSession();

                // If no expiration date is found, use current date as prorated payment date
                $day = date("d");
                $month = date("m");
                // Format payment date as an array
                $payment_date = [
                    "day" => $day,
                    "month" => $month,
                ];
            }
        } catch (Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
        }

        return $payment_date;
    }

    /**
     * Display a custom checkbox in My Account > Account details
     *
     * @since 0.1.1
     * @version 1.0.0
     */
    public function display_edit_account_checkbox_field()
    {
        woocommerce_form_field('lwmc_agree_pricing', array(
            'type' => 'checkbox',
            'class' => array('form-row-wide'),
            'label' => __('Agreed Pricing', 'woocommerce'),
            'required' => true,
        ), get_user_meta(get_current_user_id(), 'lwmc_agree_pricing', true));
    }

    /**
     * Save checkbox field value for My Account > Account details
     *
     * @since 0.1.1
     * @version 1.0.0
     */
    public function save_checkbox_value_to_account_details($user_id)
    {
        $value = isset($_POST['lwmc_agree_pricing']) ? '1' : '0';
        update_user_meta($user_id, 'lwmc_agree_pricing', $value);
    }

    /**
     * Get the value of "lwmc_agree_pricing"
     *
     * @since 0.1.1
     * @version 1.0.0
     * TODO: update this
     */
    public static function get_user_agree_pricing($user_id)
    {
        // $prorate_calculate = get_user_meta($user_id, 'lwmc_agree_pricing', true);
        // return $prorate_calculate;
        // default enable for all users
        return 1;
    }

    public function disableProrationModeSessionOnPageLoad()
    {
        if (is_checkout()) {
            self::disableProrationModeSession();
        }
    }

    static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start(); // Start the session if it hasn't been started yet
        }
    }

    public static function enableProrationModeCookie()
    {
        self::startSession();

        if (!isset($_SESSION[self::$proration_mode_session_name])) {
            $_SESSION[self::$proration_mode_session_name] = 1;
        }
    }

    public static function disableProrationModeSession()
    {
        self::startSession();

        if (isset($_SESSION[self::$proration_mode_session_name])) {
            unset($_SESSION[self::$proration_mode_session_name]);
        }
    }

    public static function isProrationSessionEnabled()
    {
        self::startSession();

        return isset($_SESSION[self::$proration_mode_session_name]) && $_SESSION[self::$proration_mode_session_name] == 1;
    }

    public function maybeChangeSubtotalText($translation, $text)
    {
        if (is_checkout() && $text == 'Subtotal' && self::isProrationSessionEnabled()) {
            return "Subtotal (reflects pro-rated license)";
        }
        return $translation;
    }
}

