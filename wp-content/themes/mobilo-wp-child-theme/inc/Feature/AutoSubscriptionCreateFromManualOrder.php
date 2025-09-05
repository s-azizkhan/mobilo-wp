<?php

namespace Mobilo\WpTheme\Feature;

defined('ABSPATH') || exit;

use Error;
use Exception;
use WCS_SQL_Transaction;
use WC_Subscription;
use WC_Order;
use WC_Payment_Tokens;
use WC_Stripe_Constants;
use WP_Error;
use Throwable;

/**
 * AutoSubscriptionCreateFromManualOrder Class
 *
 * Handles the automatic creation of a subscription from a manual order.
 *
 * @package Mobilo\WpTheme\Feature
 * @category Class
 */

if (!class_exists('AutoSubscriptionCreateFromManualOrder')) {

    /**
     * Represents the process of automatically creating a subscription from a manual order.
     *
     * @since 1.0.0
     */
    class AutoSubscriptionCreateFromManualOrder
    {

        /**
         * Feature version.
         *
         * @var string
         */
        private $version;

        public static $subscription_product_types = ['subscription', 'subscription_variation'];

        /**
         * Constructor.
         *
         * @param string $version The feature version.
         */
        public function __construct()
        {
        }

        /**
         * Initialize the subscription creation process from a manual order.
         *
         * @since 1.0.0
         */
        public function run()
        {
            $this->addActions();
        }

        /**
         * Add necessary action hooks and filters.
         *
         * @since 1.0.0
         */
        private function addActions()
        {
            add_action('woocommerce_order_status_changed', [$this, 'create_subscription']);
            add_action('woocommerce_payment_complete', [$this, 'create_subscription']);
        }

        /**
         * Perform the subscription creation from a manual order.
         *
         * Create a WooCommerce subscription from an order.
         *
         * @since 1.0.1
         * @param int $order_id The ID of the order.
         * @return WC_Subscription|WP_Error The subscription object, or a WP_Error object on failure.
         */
        public static function create_subscription(int $order_id)
        {
            mobilo_log(__METHOD__, "Order's Subscription creation initiated for order #$order_id");
            if (!self::is_order_created_by_admin($order_id)) {
                mobilo_log(__METHOD__, "Subscription skip due to order created by admin");
                return false;
            }

            return self::createSubForOrder($order_id, true);
        }

        /**
         * Create Subscription for Order that are created manually
         *
         * @since 1.0.3
         * @version 1.0.1
         */
        public static function createSubForOrder($order_id, $die_on_error = false)
        {
            try {
                // Get the order object.
                $order = wc_get_order($order_id);

                if (!$order) {
                    // Order not found.
                    return new WP_Error('order_not_found', __('Order not found.'));
                }

                if (self::check_update_subscriptions($order)) {
                    return false;
                }

                // Check if the order contains any subscription items.
                $has_subscription_items = self::hasSubscriptionItemOnOrder($order);

                // If the order does not contain any subscription items, return false.
                if (!$has_subscription_items) {
                    return false;
                }

                // Start transaction if available
                $transaction = new WCS_SQL_Transaction();
                $transaction->start();

                $subscription_args = [
                    'order_id' => $order->get_id(),
                    'status' => 'pending',
                    //'date_created' => $order->get_date_modified()->format('Y-m-d H:i:s'), // it automatically take the current date
                    'billing_period' => 'year',
                    'billing_interval' => 1,
                ];

                // Create the subscription.
                $subscription = wcs_create_subscription($subscription_args);

                if (is_wp_error($subscription)) {
                    if ($die_on_error) {
                        return wp_die($subscription->get_error_message(), 'Error from createSubForOrder');
                    }
                    throw new Error($subscription->get_error_message());
                }

                // Set the subscription's order ID.
                $subscription->set_parent_id($order_id);

                // update subscription dates
                self::updateSubDates($order, $subscription);

                // Set the subscription's billing and shipping address
                $subscription = wcs_copy_order_address($order, $subscription);

                // Add the order items to the subscription.
                self::addItemToSubFromOrder($order, $subscription);

                // add payment method
                self::addPaymentDataToSub($order, $subscription);

                // add fee
                self::addFeeToSub($order, $subscription);

                // Calculate totals
                $subscription->calculate_totals();

                // Save the subscription
                $subscription->save();

                // Set the subscription to active if order is in process
                if ($order->has_status('processing')) {
                    // Set the subscription to active.
                    $subscription->update_status('active', "Auto generated from order #$order_id by mobilo team.", false);
                }

                // The text for the note
                $order_note = __(sprintf("Subscription created with ID: %d for order ID: %d", $subscription->get_id(), $order_id));
                // Add the note
                $order->add_order_note($order_note);

                // If we got here, the subscription was created without problems
                $transaction->commit();

                // Return the subscription object.
                return $subscription;
            } catch (Throwable $th) {
                // There was an error adding the subscription
                $transaction->rollback();
                mobilo_log(__METHOD__, $th->getMessage());
                return null;
            }
        }

        /**
         * Checks if the order contains any subscription items.
         *
         * @param WC_Order $order The order to check.
         *
         * @return bool True if the order contains any subscription items, false otherwise.
         */
        public static function hasSubscriptionItemOnOrder(WC_Order $order)
        {
            try {
                // Check if the order contains any subscription items.
                $has_subscription_items = false;
                foreach ($order->get_items() as $item) {
                    if ($item->get_product()->is_type(self::$subscription_product_types)) {
                        $has_subscription_items = true;
                        break;
                    }
                }
                return $has_subscription_items;
            } catch (Throwable $th) {
                // There was an error adding the subscription
                mobilo_log(__METHOD__, $th->getMessage());
                return null;
            }
        }

        /**
         * Updates the subscription dates for a given order and subscription.
         *
         * The start date is set to 1 year before the user's expiration date if the user has an expiration date,
         * otherwise it is set to the order's modified date. The next payment date is set to the user's expiration date
         * if it exists, or 1 year after the start date otherwise.
         *
         * @param WC_Order $order The order the subscription is being created for.
         * @param WC_Subscription $subscription The subscription to update.
         *
         * @return void
         */
        public static function updateSubDates(WC_Order $order, WC_Subscription $subscription)
        {
            try {
                // Get the user expiration date from the user ID
                $user_expiration_date = OrderProration::get_user_subscription_expiration_date($order->get_user_id());

                if ($user_expiration_date) {
                    // If user expiration date exists, calculate the start date as 1 year before the expiration date
                    //$start_date = date('Y-m-d H:i:s', strtotime("-1 year", strtotime($user_expiration_date)));
                    $next_payment = gmdate('Y-m-d H:i:s', strtotime($user_expiration_date));
                } else {
                    // If user expiration date does not exist, use the order's modified date as the start date
                    $start_date = $order->get_date_modified()->format('Y-m-d H:i:s');
                    $next_payment = gmdate('Y-m-d H:i:s', strtotime("+1 year", strtotime($start_date)));
                }

                // Set the subscription dates.
                $dates = [
                    'next_payment' => $next_payment,
                ];
                $subscription->update_dates($dates);
            } catch (Throwable $th) {
                // There was an error adding the subscription
                mobilo_log(__METHOD__, $th->getMessage());
                return;
            }
        }

        /**
         * Add payment method data to the subscription from the order.
         *
         * @param WC_Order $order The order object.
         * @param WC_Subscription $subscription The subscription object.
         * @return void
         *
         * @since 1.0.0
         */
        public static function addPaymentDataToSub(WC_Order $order, WC_Subscription $subscription)
        {
            try {
                // Get the user ID from the subscription.
                $user_id = $subscription->get_user_id();

                // Retrieve all saved payment tokens for the user.
                $payment_tokens = WC_Payment_Tokens::get_customer_tokens($user_id);

                if (empty($payment_tokens)) {
                    return; // No tokens available, nothing to do.
                }

                // Retrieve all available payment gateways.
                $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
                if (empty($available_gateways)) {
                    return; // no gateways available, nothing to do.
                }

                $chosen_token = null;
                $chosen_gateway = null;

                // Attempt to find a token-based gateway that supports subscriptions.
                foreach ($payment_tokens as $token) {
                    $gateway_id = $token->get_gateway_id();
                    if (isset($available_gateways[$gateway_id]) && $available_gateways[$gateway_id]->supports('subscriptions')) {
                        $chosen_token = $token;
                        $chosen_gateway = $available_gateways[$gateway_id];
                        break;
                    }
                }

                // Copy relevant meta data from the order to the subscription.
                wcs_copy_order_meta($order, $subscription, 'subscription');

                // If no suitable token/gateway pair was found, return.
                if (!$chosen_token || !$chosen_gateway) {
                    return;
                }

                // Update subscription to use the chosen payment method.
                $subscription->set_requires_manual_renewal(false);
                $subscription->set_payment_method($chosen_gateway);

                // Additional handling for Stripe tokens.
                // TODO: if require add more payment method support
                if ($chosen_gateway->id === 'stripe_cc' && is_a($chosen_token, 'WC_Payment_Token_Stripe_CC')) {
                    $subscription->update_meta_data(WC_Stripe_Constants::PAYMENT_METHOD_TOKEN, $chosen_token->get_token());
                    $subscription->update_meta_data(WC_Stripe_Constants::CUSTOMER_ID, $chosen_token->get_customer_id());
                }

                $subscription->save();
            } catch (Throwable $th) {
                // There was an error adding the subscription
                mobilo_log(__METHOD__, $th->getMessage());
                return;
            }
        }

        /**
         * Adds fees from an order to a subscription.
         *
         * Iterates over each fee in a given order and adds them to the subscription.
         * Extensions may add recurring fees. If the fee cannot be added, an exception
         * is thrown. Plugins can add custom metadata to the fees using the appropriate action hook.
         *
         * @param WC_Order $order The order from which fees are added.
         * @param WC_Subscription $subscription The subscription to which fees are added.
         * @return void
         * @throws Exception If unable to add a fee to the subscription.
         */
        public static function addFeeToSub(WC_Order $order, WC_Subscription $subscription)
        {
            try {
                // Store fees (although no fees recur by default, extensions may add them)
                foreach ($order->get_fees() as $fee_key => $fee) {
                    $item_id = $subscription->add_fee($fee);

                    if (!$item_id) {
                        // translators: placeholder is an internal error number
                        throw new Exception(sprintf(__('Error %d: Unable to create subscription. Please try again.', 'woocommerce-subscriptions'), 403));
                    }

                    // Allow plugins to add order item meta to fees
                    do_action('woocommerce_add_order_fee_meta', $subscription->get_id(), $item_id, $fee, $fee_key);
                }
            } catch (Throwable $th) {
                // There was an error adding the subscription
                mobilo_log(__METHOD__, $th->getMessage());
                return;
            }
        }

        /**
         * Adds items from an order to a subscription.
         *
         * @param WC_Order $order The order containing the items to add.
         * @param WC_Subscription $subscription The subscription to add the items to.
         *
         * @throws Exception If the subscription can't be saved.
         */
        public static function addItemToSubFromOrder(WC_Order $order, WC_Subscription $subscription)
        {
            try {
                foreach ($order->get_items() as $item) {
                    $product = $item->get_product();
                    if (!$product->is_type(self::$subscription_product_types)) {
                        // Item is not a subscription product.
                        continue;
                    }

                    $subscription->add_product($product, $item->get_quantity());
                    $subscription->calculate_totals();
                    $subscription->save();

                    //TODO: need testing for this
                    //$next_payment = WC_Subscriptions_Product::get_first_renewal_payment_date($product, $start_date);
                }
            } catch (Throwable $th) {
                // There was an error adding the subscription
                mobilo_log(__METHOD__, $th->getMessage());
                return;
            }
        }

        /**
         * This function checks if the order has any subscriptions. If so, it updates the status of the subscriptions based on the order status.
         *
         * @param WC_Order $order The order object.
         * @return bool Whether or not a subscription already exists.
         * @version 1.0.1
         */
        public static function check_update_subscriptions(WC_Order $order)
        {

            // Get the subscriptions for the order.
            $subscriptions = wcs_get_subscriptions_for_order($order);

            // If there are no subscriptions, return false.
            if (empty($subscriptions)) {
                return false;
            }

            // Get the order status.
            $order_status = $order->get_status();

            // Loop through the subscriptions and update their status.
            foreach ($subscriptions as $subscription) {

                // Update the subscription status.
                switch ($order_status) {
                    case 'processing':
                        $subscription->update_status('active');
                        break;
                    case 'pending':
                        $subscription->update_status('pending-cancel', "Due to parent order status changed to {$order_status}");
                        // The text for the note
                        $order_note = __(sprintf("Subscription #%d was cancelled due to status change", $subscription->get_id()));
                        // Add the note
                        $order->add_order_note($order_note);
                        break;
                    case 'cancelled':
                        $subscription->update_status('cancelled');
                        break;
                }
            }

            // Log a message indicating that subscriptions already exist for this order.
            error_log(sprintf("Skipping Subscription creation as subscription are already exist for this order #%d, subscriptions are %s", $order->get_id(), json_encode($subscriptions)));

            // Return true to indicate that a subscription already exists.
            return true;
        }

        /**
         * Check if an order is created by an admin or shop manager.
         *
         * @param int $order_id The ID of the order to check.
         * @return bool True if the order is created by an admin or shop manager, false otherwise.
         */
        public static function is_order_created_by_admin(int $order_id)
        {
            // Retrieve the post associated with the order ID
            $post = get_post($order_id);

            // If the post doesn't exist, return false
            if (!$post) {
                return false;
            }

            // Retrieve the user associated with the post author's ID
            $user = get_user_by('id', $post->post_author);

            // If the user doesn't exist or has no roles, return false
            if (!$user || empty($user->roles)) {
                return false;
            }

            // Define the allowed roles for order creation
            $allowedRoles = ['administrator', 'shop_manager'];

            // Retrieve the roles assigned to the user
            $userRoles = (array) $user->roles;

            // Check if there is any overlap between allowed roles and user roles
            if (array_intersect($allowedRoles, $userRoles)) {
                return true; // User has an allowed role, return true
            }

            return false; // User doesn't have an allowed role, return false
        }
    }
}
