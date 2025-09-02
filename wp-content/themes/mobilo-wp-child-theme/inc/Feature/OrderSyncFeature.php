<?php

namespace Mobilo\WpTheme\Feature;

use MobiloAuth\Core\FirebaseAuth;
use MobiloAuth\Core\UserManager;
use Throwable;
use WP_Error;

defined('ABSPATH') || exit;

use WC_Subscription;

/**
 * Class OrderSyncFeature
 *
 * @since 0.4.0
 * @version 1.0.3
 *
 * @package Mobilo\WpTheme\Feature
 */
class OrderSyncFeature extends BaseFeature
{
    private static $featureKey = 'order_sync';

    /**
     * Constructor.
     *
     * @param string $version The feature version.
     */
    public function __construct()
    {
        parent::__construct(self::$featureKey);
    }

    /**
     * Add necessary action hooks and filters.
     *
     * @since 1.0.1
     */
    public function action_init()
    {
        // Sync every time when subscription status change (TODO: not implemented in BE)
        add_action('woocommerce_subscription_status_changed', [$this, 'updateSubscriptionToMAT']);

        // Sync when subscription renewal payment complete
        add_action('woocommerce_subscription_renewal_payment_complete', [$this, 'updateSubscriptionToMAT']);
        add_action('woocommerce_subscription_renewal_payment_complete', [$this, 'updateSubscriptionNextPaymentDate']);

        //  Sync To MAT when a payment is completed in WooCommerce
        add_action('woocommerce_payment_complete', [$this, 'updateOrderToMAT']);

        // Clean up the customer's cart after payment is completed
        add_action('woocommerce_payment_complete', [$this, 'clean_cart']);

        // Sync To MAT when it transitions to the "Processing" status
        add_action('woocommerce_order_status_processing', [$this, 'updateOrderToMAT']);
        add_action('woocommerce_order_status_completed', [$this, 'updateOrderToMAT']);
    }

    /**
     * Clear cart
     *
     * @since 1.0.0
     */
    public function clean_cart()
    {
        mc_empty_cart();
    }

    /**
     * Updates an order to MAT.
     *
     * @param int $order_id The ID of the order to update.
     *
     * @since 1.0.1
     * @return void
     */
    public function updateOrderToMAT(int $order_id)
    {
        // Sync the order with MAT.
        self::syncOrderToMat($order_id);
    }

    public function syncUser($order_id)
    {
        mobilo_log(__METHOD__, "Sync user order id: $order_id", "info");
        $order = wc_get_order($order_id);

        // Get the user ID from the order
        $user_id = $order->get_user_id();

        // $userRegion = \LWMC_MultiRegion::get_user_region($user_id, false);
        $firebaseAuth = new FirebaseAuth('us');

        $firebaseId = UserManager::get_firebase_uid_by_wordpress_id($user_id);

        if (!$firebaseId) {
            mobilo_log(__METHOD__, 'firebaseId not found for <> ' . $user_id, 'info');

            // get the customer from order's customer's email
            $user_data = get_userdata($user_id);
            $user_email = $user_data->user_email;
            $firebase_user = $firebaseAuth->getUserByEmail($user_email);

            if (!$firebase_user) {
                throw new \Exception('firebase_user not found for <> ' . $user_email);
            }
            $firebaseId = $firebase_user->uid;
        }

        $accessToken = $firebaseAuth->signInByUserId($firebaseId);

        if (!isset($accessToken)) {
            throw new \Exception('OrgId update failed due to invalid id_token for <> ' . $firebaseId);
        }

        // Optional: Avoid repeated calls by checking if already triggered
        if (get_user_meta($user_id, 'user_synced', true)) {
            return;
        }

        $api_create_profile = MOBILO_API_GATEWAY . '/v1/profiles/profile';
        $request_data = json_encode(array(
            'Email Address' => $order->get_billing_email(),
            'user' => $order->get_billing_email(),
            'Full Name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'createdFrom' => 'WOOCOMMERCE',
            'firstName' => $order->get_billing_first_name(),
            'lastName' => $order->get_billing_last_name(),
            'displayName' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
        ));

        // Set the request headers (include your authentication token)
        $headers = array(
            'Content-Type' => 'application/json', // Set the content type to JSON
            'Authorization' => 'Bearer ' . $accessToken, // Replace with your actual authentication token
        );

        // Prepare the request arguments
        $args = array(
            'timeout' => 60,
            'headers' => $headers,
            'body' => $request_data,
        );

        mobilo_log(__METHOD__, 'Profile request url: ' . $api_create_profile, 'info');
        mobilo_log(__METHOD__, 'Profile request data: ' . print_r($args, true), 'info');

        $res = wp_remote_post($api_create_profile, $args);
        $responseProfile = wp_remote_retrieve_body($res);
        $jsonResp = json_decode($responseProfile);

        if ($jsonResp) {
            update_user_meta($user_id, 'external_api_called', true);
            mobilo_log(__METHOD__, 'Profile response data: ' . print_r($jsonResp->data, true), 'info');
            return $jsonResp->data;
        }
        return false;
    }

    /**
     * Syncs an order with MAT.
     *
     * @param int $order_id The ID of the order to sync.
     * @since 1.2.4
     * @version 1.0.1
     * @return array|WP_Error|string|null The response from MAT, or a WP_Error object on error.
     */
    public static function syncOrderToMat(int $order_id)
    {
        // don't sync org orders
        $orgIdKey = MOBILO_PREFIX . '_organization_id';
        $orgId = get_post_meta($order_id, $orgIdKey, true);
        if ($orgId && !EDOFeature::is_purchase_type_edo_custom($orgId)) {
            // Log that the order sync has started.
            mobilo_log(__METHOD__, '#' . $order_id . __(' Org EDO order found'), 'info');
            return null;
        }
        // Log that the order sync has started.
        mobilo_log(__METHOD__, '#' . $order_id . __(' Order sync started'), 'info');

        // Check if the order has already been synced.
        if (get_post_meta($order_id, 'lwmc_is_synchronized', true)) {
            // Log that the order has already been synced.
            $msg = sprintf("#%s Order already sync", $order_id);
            mobilo_log(__METHOD__, $msg, 'info');

            // Return a message indicating that the order has already been synced.
            return $msg;
        }

        // TODO: if needed add region to the api here
        // Get the MAT order sync API for the region.
        $api = env('MC_MAT_ORDER_SYNC_API'); // TODO: add this to env file

        // Create the URL for the MAT order sync request.
        $url = sprintf('%s?orderNumber=%s', $api, $order_id);

        // Make the GET request to MAT.
        $response = wp_remote_get($url, [
            'timeout' => 60,
        ]);

        // Get the response body.
        $res = wp_remote_retrieve_body($response);

        // Decode the response body as JSON.
        $jsonResp = json_decode($res);

        // Log the function name, the URL, and the response body.
        mobilo_log(__METHOD__, __FUNCTION__ . ' - ' . $url . ' - ' . $res, 'info');

        // Check if the response is successful.
        if ($jsonResp && wp_remote_retrieve_response_code($response) == 200 && $jsonResp->status == 'SUCCESS') {
            // Log that the order has been synced.
            mobilo_log(__METHOD__, sprintf("#%s Order synced", $order_id), 'info');

            // Update the post meta to indicate that the order has been synced.
            update_post_meta($order_id, 'lwmc_is_synchronized', true);

            // Return the response from MAT.
            return $jsonResp;
        } else {
            // Log that the order sync failed.
            mobilo_log(__METHOD__, sprintf("#%s Order synced failed", $order_id));

            // Update the post meta to indicate that the order sync failed.
            update_post_meta($order_id, 'lwmc_is_synchronized', false);

            // Return a WP_Error object indicating that the order sync failed.
            return new WP_Error('order_sync_failed', 'Order sync failed');
        }
    }

    /**
     * Sync subscription
     *
     * @param string $subscription_id
     * @version 1.0.3
     */
    public static function syncSubscription($subscription_id, $isPlanChange = false)
    {
        if (!$subscription_id) {
            mobilo_log(__METHOD__, '#' . $subscription_id . ' subscription error');
            return false;
        }

        mobilo_log(__METHOD__, '#' . $subscription_id . ' subscription sync started', 'info');

        // Retrieve the subscription object
        $subscription = wcs_get_subscription($subscription_id);

        // Check if the subscription object exists and retrieve the parent order ID
        if ($subscription && $parent_order_id = $subscription->get_parent_id()) {
            // Get the organization ID associated with the parent order
            $orgId = get_post_meta($parent_order_id, 'lwmc_organization_id', true);

            // Check if the organization ID exists and if it's not empty adn not an EDOCustomDesign
            if ($orgId && $orgId !== '' && !EDOFeature::is_purchase_type_edo_custom($orgId)) {
                // Log a message indicating that it's an EDO order and not syncing
                mobilo_log(__METHOD__, __FUNCTION__ . ' #' . $subscription_id . ' EDO order found, not syncing');

                // Return false to indicate that syncing should not be performed
                return false;
            }
        }

        // $region = LWMC_MultiRegion::get_order_user_region($subscription_id);

        // if (LWMC_AdSync::isOnCreditPurchaseMode()) {
        //     $sync_api = LWMC_MultiRegion::get_adsync_sub_api($region);
        // } else {
        //     $sync_api = LWMC_MultiRegion::get_sub_order_sync_api($region);
        // }

        $sync_api = env('MC_MAT_SUBSCRIPTION_SYNC_API'); // TODO: add this to env file

        $url = sprintf('%s?subscriptionNumber=%s&isPlanChange=%s', $sync_api, $subscription_id, $isPlanChange ? 1 : 0);

        $response = wp_remote_post($url, array(
            'timeout' => 45,
            'blocking' => true,
            'headers' => EDOFeature::get_api_key_headers()
        ));

        $api_response = wp_remote_retrieve_body($response);

        mobilo_log(__METHOD__, $url . ' => ' . __METHOD__ . ' => ' . $api_response, 'info');
        mobilo_log(__METHOD__, '#' . $subscription_id . ' subscription sync response', 'info');

        return $api_response;
    }
    /**
     * Updates a subscription to MAT.
     *
     * @param int|WC_Subscription $subscription_id The ID of the subscription to update.
     * @since 1.0.0
     * @return bool True if the subscription was updated successfully, false otherwise.
     */
    public function updateSubscriptionToMAT($subscription_id)
    {
        mobilo_log(__METHOD__, "woocommerce_subscription_renewal_payment_complete #$subscription_id ", 'info');
        // Check if the subscription ID is a valid WC_Subscription object.
        if ($subscription_id instanceof WC_Subscription) {
            // Get the subscription object.
            $subscription = $subscription_id;
        } else {
            // Create a new WC_Subscription object from the subscription ID.
            $subscription = new WC_Subscription($subscription_id);
        }

        // Check if the subscription object is valid.
        if (!$subscription) {
            // Return false if the subscription object is not valid.
            return false;
        }

        // Only call this function once per subscription.
        static $called = 1;

        // Get the subscription status.
        $subStatus = $subscription->get_status();

        // Log the function name, the number of times it has been called, and the subscription status.
        mobilo_log(__METHOD__, " called: $called, on statusssss: $subStatus", 'info');

        // Get the subscription ID.
        $subscriptions_id = $subscription->get_id();

        $isPlanChange = $this->updateUserPlanSku($subscription);

        $check = $this->is_free_trial_active($subscriptions_id);
        if ($check['has_trial'] && $check['trial_active']) {

            $new_next_payment_date = $this->get_subscription_trial_end_date($subscription);
            mobilo_log(__METHOD__, "Free trial next payment date: " . $new_next_payment_date, 'info');

            $subscription->update_dates(array(
                'next_payment' => gmdate('Y-m-d H:i:s', $new_next_payment_date),
            ));

            $subscription->save();
        }

        // Sync the subscription with MAT.
        self::syncSubscription($subscriptions_id, $isPlanChange); // TODO: deprecate this after new GroupSubscriptionFeature fully live

        // TODO: sync subscription to MAT
        // GroupSubscriptionFeature::syncSubToMat($subscriptions_id);

        // Increment the number of times the function has been called.
        $called++;

        return true;
    }

    public function updateSubscriptionNextPaymentDate($subscription_id)
    {
        mobilo_log(__METHOD__, "updateSubscriptionNextPaymentDate #$subscription_id ", 'info');

        // Check if the subscription ID is a valid WC_Subscription object.
        if ($subscription_id instanceof WC_Subscription) {
            // Get the subscription object.
            $subscription = $subscription_id;
        } else {
            // Create a new WC_Subscription object from the subscription ID.
            $subscription = new WC_Subscription($subscription_id);
        }

        // Check if the subscription object is valid.
        if (!$subscription) {
            // Return false if the subscription object is not valid.
            return false;
        }

        $new_next_payment_date = $this->get_next_payment_date($subscription);
        $subscription->update_dates(array(
            'next_payment' => gmdate('Y-m-d H:i:s', $new_next_payment_date),
        ));

        $subscription->save();

    }

    public function is_free_trial_active($subscription_id)
    {
        mobilo_log(__METHOD__, 'is_free_trial_active: #' . $subscription_id, 'info');

        $subscription = wcs_get_subscription($subscription_id);

        if (!$subscription instanceof WC_Subscription) {
            return [
                'has_trial' => false,
                'trial_active' => false,
                'trial_end' => null,
            ];
        }

        $trial_end = $subscription->get_time('trial_end');
        mobilo_log(__METHOD__, 'Trial end...: ' . $trial_end, 'info');

        if ($trial_end) {
            $now = current_time('timestamp');
            mobilo_log(__METHOD__, 'Now time...: ' . $now, 'info');
            return [
                'has_trial' => true,
                'trial_active' => $now < $trial_end,
                'trial_end' => date('Y-m-d H:i:s', $trial_end),
            ];
        }

        return [
            'has_trial' => false,
            'trial_active' => false,
            'trial_end' => null,
        ];
    }


    public function get_next_payment_date($subscription)
    {
        // Only affect yearly subscriptions
        if ($subscription->get_billing_period() !== 'year') {
            // TODO: handle other periods (previously it was returning $next_payment_date)
            return false;
            // return $next_payment_date;
        }

        // Get the original start date
        $start_date = $subscription->get_date_created()->getTimestamp(); // timestamp
        $interval = $subscription->get_billing_interval(); // usually 1
        $period = $subscription->get_billing_period();     // usually 'year'

        // Calculate the number of periods since the original start date
        $current_time = current_time('timestamp');
        $current_year = date('Y', $current_time);
        $next_payment_time = $start_date;

        mobilo_log(__METHOD__, "current time before renewal order: $current_time", "info");
        mobilo_log(__METHOD__, "next payment date before: $next_payment_time => #$interval => #$period", "info");

        $manual_base = get_post_meta($subscription->get_id(), '_manual_next_payment_override', true);
        mobilo_log(__METHOD__, "manual payment date #" . $manual_base, 'info');
        if ($manual_base) {
            $manual_base_year = date('Y', strtotime($manual_base));
            if ($current_year >= $manual_base_year) {
                $next_payment_time = strtotime("+{$interval} {$period}", strtotime($manual_base));
                mobilo_log(__METHOD__, "manual next payment date #" . $next_payment_time, 'info');
            }

        } else {
            while ($next_payment_time <= $current_time) {
                $next_payment_time = strtotime("+{$interval} {$period}", $next_payment_time);
            }
        }

        if ($current_year == date('Y', $next_payment_time)) {
            mobilo_log(__METHOD__, "Next payment date is of current year", "info");
            $next_payment_time = strtotime("+{$interval} {$period}", $next_payment_time);
        }
        mobilo_log(__METHOD__, "next payment date #" . $next_payment_time, 'info');

        return $next_payment_time;
    }

    public function get_subscription_trial_end_date($subscription)
    {
        if (!$subscription instanceof WC_Subscription) {
            return false;
        }

        foreach ($subscription->get_items() as $item) {
            $product = $item->get_product();

            if ($product && $product->is_type('subscription')) {
                $trial_end = $subscription->get_time('trial_end');
                mobilo_log(__METHOD__, "Trail end date: #$trial_end", "info");
                $trial_length = (int) get_post_meta($product->get_id(), '_subscription_trial_length', true);
                $trial_period = get_post_meta($product->get_id(), '_subscription_trial_period', true); // e.g., day, week, month, year

                if ($trial_length > 0 && $trial_period) {
                    $start_timestamp = $subscription->get_date_created()->getTimestamp();

                    // Calculate trial end timestamp
                    $trial_end_timestamp = strtotime("+{$trial_length} {$trial_period}", $start_timestamp);

                    if ($trial_end_timestamp < $trial_end) {
                        $trial_end_timestamp = $trial_end;
                    }

                    return $trial_end_timestamp;
                }
            }
        }

        return false; // No trial
    }
    /**
     * Updates the user's subscription plan SKU and handles conflicts with existing active subscriptions.
     *
     * This function retrieves the SKU from a given subscription that matches the specified prefix (e.g., 'MCP_').
     * If the user already has an active subscription with a different plan SKU, it cancels those subscriptions.
     * Finally, it updates the user's plan SKU in their metadata.
     *
     * @version 1.0.3
     * @param WC_Subscription $subscription The WooCommerce subscription object to extract the SKU from.
     * @return bool
     */
    public function updateUserPlanSku($subscription)
    {
        try {
            // Get all items (products) from the subscription.
            $sub_items = $subscription->get_items();

            $sub_sku = null;

            // Iterate over each item to find a product with a SKU that starts with the desired prefix.
            foreach ($sub_items as $item) {
                $product = $item->get_product();
                $sku = $product->get_sku();

                // Check if the SKU is valid based on the prefix defined in PlanFeature.
                if (PlanFeature::is_valid_plan_sku($sku)) {
                    if (str_contains($sku, 'TEAM_FREE')) {
                        $sku = 'MCP_TEAM';
                        mobilo_log(__METHOD__, 'free trial team plan purchased >> ' . $subscription->get_id(), 'info');
                    }
                    // Convert the SKU to uppercase for consistency.
                    $sub_sku = strtoupper($sku);
                    break;
                }

                // support legacy plan for MUL sku
                if ($sku === 'MUL') {
                    $sub_sku = PlanFeature::$skuPrefix . 'TEAM';
                    break;
                }
            }

            // If no valid SKU was found, exit the function.
            if (!$sub_sku) {
                mobilo_log(__METHOD__, 'No valid plan SKU found for subscription ' . $subscription->get_id(), 'info');
                return false;
            }

            // Retrieve the customer ID from the subscription.
            $customer_id = $subscription->get_customer_id();
            // Get the user's existing plan SKU from their metadata.
            $user_existing_plan_sku = NewPlansUserMeta::get_plan_sku($customer_id);

            /**
             * If the user has an existing valid plan SKU different from the new one,
             * cancel all active subscriptions associated with the old plan SKU.
             */
            if (
                $user_existing_plan_sku &&
                PlanFeature::is_valid_plan_sku($user_existing_plan_sku) &&
                $user_existing_plan_sku !== $sub_sku
            ) {

                global $wpdb;

                // SQL query to find all active subscriptions with the old plan SKU for the user.
                $query = "
                    SELECT DISTINCT p.ID AS order_id
                    FROM {$wpdb->prefix}posts AS p
                    JOIN {$wpdb->prefix}woocommerce_order_items AS oi ON p.ID = oi.order_id
                    JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim ON oi.order_item_id = oim.order_item_id
                    JOIN {$wpdb->prefix}postmeta AS pm ON pm.post_id = oim.meta_value
                    JOIN {$wpdb->prefix}postmeta AS om ON om.post_id = p.ID
                    WHERE p.post_type = 'shop_subscription'
                    AND p.post_status = 'wc-active'
                    AND oim.meta_key = '_product_id'
                    AND pm.meta_key = '_sku'
                    AND pm.meta_value = %s
                    AND om.meta_key = '_customer_user'
                    AND om.meta_value = %s
                ";

                // Execute the query with the old plan SKU and customer ID to get subscription IDs.
                $subscription_ids = $wpdb->get_col($wpdb->prepare($query, $user_existing_plan_sku, $customer_id));

                // Iterate over each subscription ID to cancel the active subscriptions.
                foreach ($subscription_ids as $subscription_id) {
                    $subscription = wcs_get_subscription($subscription_id);

                    // If the subscription object cannot be retrieved or is not active, skip it.
                    if (!$subscription || !$subscription->has_status('active')) {
                        continue;
                    }

                    // Create a note explaining the reason for cancellation.
                    $note = "Due to plan change from $user_existing_plan_sku to $sub_sku";
                    // Set the subscription status to 'cancelled' with the note.
                    $subscription->set_status('wc-cancelled', "$note, ");
                    // Save the changes to the subscription.
                    $subscription->save();

                    // Log the cancellation action for auditing purposes.
                    mobilo_log(__METHOD__, "Subscription $subscription_id cancelled due to plan change from $user_existing_plan_sku to $sub_sku", 'info');
                }
            }

            // If the current SKU is different from the existing one, update the user's plan SKU.
            if ($user_existing_plan_sku !== $sub_sku) {
                NewPlansUserMeta::set_plan_sku($sub_sku, $customer_id);

                // send the plan change to true if only user is purchasing new plan
                if (PlanFeature::is_valid_plan_sku($user_existing_plan_sku)) {
                    return true;
                }
            }

            return false;
        } catch (Throwable $th) {
            // Log any exceptions that occur during the process.
            mobilo_log(__METHOD__, $th->getMessage());
            mobilo_log(__METHOD__, $th->getTraceAsString());
            return false;
        }
    }
}
