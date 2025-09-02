<?php

namespace Mobilo\WpTheme\Feature;

use Mobilo\WpTheme\Feature\EDOFeature;
use Mobilo\WpTheme\Feature\NewPlansUserMeta;
use MobiloAuth\Core\FirebaseAuth;
use MobiloAuth\Core\UserManager;

// use Mobilo\WpTheme\Feature\ReactPageSetup;

defined('ABSPATH') || exit;

/**
 * Class OrganizationIdManager
 *
 * @package Mobilo\WpTheme\Feature
 */
class OrganizationIdManager
{

    /**
     * The organization ID constant.
     * @since 1.0.0
     * @version 1.0.0
     * @access public
     * @var string $org_id
     */
    public const string ORGANIZATION_ID_KEY = 'organization_id';

    /**
     * The organization ID meta key.
     * @since 1.0.0
     * @version 1.0.0
     * @access public
     * @var string $organization_id_meta_key
     */
    public static string $organization_id_meta_key = 'lwmc_' . self::ORGANIZATION_ID_KEY;

    /**
     * Get the full organization ID with prefix.
     * @since 1.0.0
     * @version 1.0.0
     * @return string
     */
    public static function getOrganizationIdKey()
    {
        return self::$organization_id_meta_key;
    }

    public function __construct()
    {
        // Set the organization ID meta key
        // $this->organization_id_meta_key = self::getOrganizationIdKey();
    }

    public function init()
    {
        $this->actions_init();
    }

    /**
     * Register the actions
     *
     * @return void
     */
    public function actions_init()
    {
        # Added organization id field
        add_action('edit_form_after_title', [$this, 'add_organization_id_field_to_coupon']);
        # To save the value entered in the text input field
        add_action('save_post', [$this, 'save_organization_id_field']);
        # Hook the 'maybe_sync_org_order_after_payment' method to run after payment is completed
        add_action('woocommerce_payment_complete', [$this, 'maybe_sync_org_order_after_payment'], 1);
        # Hook the 'custom_redirect_after_payment' method to the 'woocommerce_thankyou' action.
        add_action('woocommerce_thankyou', [$this, 'custom_redirect_after_payment'], 1);
        # Hook the 'custom_redirect_after_payment' method to when new order created
        add_action('woocommerce_new_order', [$this, 'mark_order_as_org_order'], 1, 2);
        # Add meta boxes to the coupon pages in the admin panel.
        // add_action('add_meta_boxes', [$this, 'add_edo_compatible_meta_boxes']);
        # Save the data of the EDO compatible.
        // add_action('save_post', [$this, 'save_edo_compatible'], 10, 1);
    }

    /**
     * Add org ID to newly created order if the coupon has organization ID
     * 
     * @since 1.0.1
     * @version 1.0.1
     * @return void
     */
    function mark_order_as_org_order($order_id, $order)
    {
        // Get applied coupons from the order
        $applied_coupons = $order->get_coupon_codes();

        // Check if coupons are applied to the order
        if (!empty($applied_coupons)) {
            foreach ($applied_coupons as $coupon_code) {
                // Get the coupon object based on the coupon code
                $coupon = new \WC_Coupon($coupon_code);

                // Get the coupon ID
                $coupon_id = $coupon->get_id();

                // Get the Organization ID from the coupon
                $org_id = get_post_meta($coupon_id, self::$organization_id_meta_key, true);

                // If Organization ID is found, store it as order metadata and sync with MAT
                if (!empty($org_id)) {
                    // Store the Organization ID as order metadata
                    update_post_meta($order_id, self::$organization_id_meta_key, $org_id);

                    // Update Organization ID in profile collection
                    $this->updateOrgIdToProfile($org_id);

                    // Exit the loop after getting the organization ID
                    break;
                }
            }
        }
    }
    /**
     * Add Organization ID field to the WooCommerce coupon form and populate it with the saved value if available.
     * 
     * @since 1.0.0
     * @version 1.0.0
     */
    function add_organization_id_field_to_coupon()
    {
        global $post;

        if ('shop_coupon' === $post->post_type) {
            $organization_id = get_post_meta($post->ID, self::$organization_id_meta_key, true);
            ?>
            <div class="custom-coupon-field">
                <input type="text" id="<?php echo esc_attr(self::$organization_id_meta_key); ?>"
                    name="<?php echo esc_attr(self::$organization_id_meta_key); ?>"
                    value="<?php echo esc_attr($organization_id); ?>"
                    placeholder="<?php esc_attr_e('Organization ID (optional)', 'woocommerce'); ?>"
                    style="width: 100%; margin-top: 10px;" />
            </div>
            <?php
        }
    }

    /**
     * Save the Organization ID value when the coupon is created or updated.
     * 
     * @since 1.0.0
     * @version 1.0.0
     */
    function save_organization_id_field($post_id)
    {
        if ('shop_coupon' !== get_post_type($post_id)) {
            return;
        }

        // Get the Organization ID value from the POST data
        $organization_id = sanitize_text_field($_POST[self::$organization_id_meta_key]);

        // Save the Organization ID value as post meta
        update_post_meta($post_id, self::$organization_id_meta_key, $organization_id);
    }

    /**
     * Custom function triggered after a payment is marked as complete in WooCommerce.
     *
     * This function is hooked to the 'woocommerce_payment_complete' action, which is triggered
     * when the payment for an order has been successfully processed.
     *
     * @since 1.0.0
     * @version 1.0.0
     * @param int $order_id The ID of the WooCommerce order for which payment is completed.
     */
    function maybe_sync_org_order_after_payment($order_id)
    {
        $org_id = get_post_meta($order_id, self::$organization_id_meta_key, true);
        if ($org_id) {
            // Check if the org's purchase type is EDOCustomDesign then don't call place order API
            if (EDOFeature::is_purchase_type_edo_custom($org_id)) {
                return;
            }
            // Log that the order sync has started.
            mobilo_log(__METHOD__, '#' . $order_id . ' Sync Org order', 'info');
            // Perform synchronization with MAT
            $this->syncOrgOrderToMat($order_id, $org_id);
            return;
        }
    }

    /**
     * Function to synchronize an order with MAT.
     *
     * @since 1.0.0
     * @version 1.0.2
     * @param int $order_id The Order ID.
     */
    function syncOrgOrderToMat($order_id = 0, $organization_id = '')
    {
        try {
            mobilo_log(__METHOD__, 'order_id#' . $order_id . ' - organization_id#' . $organization_id, 'info');
            if (get_post_meta($order_id, 'lwmc_is_synchronized', true)) {
                $msg = '#' . $order_id . ' Order already synced';
                throw new \Exception($msg);
            }

            $userId = get_current_user_id();

            // if userId not found then use order's customer ID
            if (!$userId) {
                $userId = get_post_meta($order_id, '_customer_user', true);
            }

            // throw error if userId not found
            if (!$userId) {
                $msg = ': userId not found for <> ' . $order_id;
                throw new \Exception($msg);
            }

            $userRegion = 'us';
            $firebaseAuth = new FirebaseAuth($userRegion);

            $firebaseId = UserManager::get_firebase_uid_by_wordpress_id($userId);

            if (!$firebaseId) {
                mobilo_log(__METHOD__, 'firebaseId not found for <> ' . $userId, 'info');

                // get the customer from order's customer's email
                $user_data = get_userdata($userId);
                $user_email = $user_data->user_email;
                $firebase_user = $firebaseAuth->getUserByEmail($user_email);

                if (!$firebase_user) {
                    throw new \Exception(': firebase_user not found for <> ' . $user_email);
                }
                $firebaseId = $firebase_user->uid;
            }

            $accessToken = $firebaseAuth->signInByUserId($firebaseId);

            if (!isset($accessToken)) {
                throw new \Exception('OrgId update failed due to invalid id_token for <> ' . $firebaseId);
            }

            // Set your API endpoint
            $api_endpoint = \LWMC_Settings::get_org_user_storefront_sync_api();

            // Prepare the request data (parameters) in JSON format
            $request_data = json_encode(array(
                'wooEdoOrderId' => $order_id,
                // 'orgId' => $organization_id,
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

            $response = wp_remote_post($api_endpoint, $args);
            $res = wp_remote_retrieve_body($response);
            $jsonResp = json_decode($res);
            mobilo_log(__METHOD__, $api_endpoint . ' - ' . $res, 'debug');
            if (!$jsonResp && wp_remote_retrieve_response_code($response) != 200) {
                update_post_meta($order_id, 'lwmc_is_synchronized', false);
                throw new \Exception(': Order sync failed due to invalid response for <> ' . $order_id);
            }
            update_post_meta($order_id, 'lwmc_is_synchronized', true);

            // $subscriptions = wcs_get_subscriptions_for_order($order_id);
            // if ($subscriptions) {
            //     foreach ($subscriptions as $subscription) {
            //         $subscription_id = $subscription->get_id();
            //         mobilo_log(__METHOD__, "EDO Subscription #$subscription_id of order #$order_id ", 'info');
            //         LWMC::syncSubscription($subscriptions_id);
            //         // $subscription->delete(true);
            //         // $subscription->update_status('cancelled', 'Attached to primary subscription.');
            //     }
            // }
            return true;
        } catch (\Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
            mobilo_log(__METHOD__, $th->getTraceAsString());
            return false;
        }
    }

    /**
     * This function redirects users to a custom React page after successful payment if the Organization ID is present.
     *
     * @since 1.0.0
     * @version 1.0.0
     * @param int $order_id The Order ID.
     */
    function custom_redirect_after_payment($order_id)
    {
        if ($order_id) {
            $user_plan_sku = NewPlansUserMeta::get_plan_sku();

            $upgrade_plan = isset($_COOKIE['lwmc_plan_upgrade_mode']) ? sanitize_text_field($_COOKIE['lwmc_plan_upgrade_mode']) : '';
            mobilo_log(__METHOD__, 'order_id#' . $order_id . ' - User existing plan# ' . $user_plan_sku . ' - User upgrade Plan# ' . $upgrade_plan, 'info');

            // Get the Organization ID from the order id
            $organization_id = get_post_meta($order_id, self::$organization_id_meta_key, true);

            $userId = get_current_user_id();
            // $orgId = get_user_meta($userId, 'lwmc_user_org', true);

            $customer_orders = wc_get_orders([
                'customer_id' => $userId,
                'limit' => -1, // Get all orders
            ]);

            if ($user_plan_sku != $upgrade_plan) {
                NewPlansUserMeta::set_plan_sku($upgrade_plan, $userId);
            }

            // echo "org id: " . ($this->organization_id_meta_key); die;

            // Check if the org's purchase type is EDOCustomDesign then do nothing
            if (EDOFeature::is_purchase_type_edo_custom($organization_id)) {
                return;
            }

            $redirectToManagement = WC()->session->get('redirect_to_management');
            WC()->session->__unset('redirect_to_management');

            if (count($customer_orders) > 1 || $redirectToManagement == 'management') {
                // $reactUrl = ReactPageSetup::get_react_app_page_url('react_account_management_page_id'); // TODO: add this
                // Redirect to the management page
                // wp_redirect($reactUrl);
                exit;
            }

            // Check if the order was paid successfully
            if (wc_get_order($order_id)->is_paid() && !empty($organization_id)) {
                // $reactUrl = ReactPageSetup::get_react_app_page_url('react_thank_you_page_id'); // TODO: add this
                // Redirect to the thank you page or profile page
                // wp_redirect($reactUrl);
                exit;
            }
        }
    }

    /**
     * Function to update the Organization ID in profile collection.
     *
     * @since 1.0.2
     * @version 1.0.3
     * @param int $organization_id The Organization ID.
     */
    function updateOrgIdToProfile($organization_id = '')
    {
        try {
            $userId = get_current_user_id();

            $userRegion = 'us';
            $firebaseId = UserManager::get_firebase_uid_by_wordpress_id($userId);

            if (!$firebaseId || !$userRegion) {
                mobilo_log(__METHOD__, 'Firebase user not found!');
                return false;
            }

            $firebaseAuth = new FirebaseAuth($userRegion);
            $accessToken = $firebaseAuth->signInByUserId($firebaseId);

            if (!isset($accessToken)) {
                mobilo_log(__METHOD__, 'OrgId update failed due to invalid id_token');
                return false;
            }

            // if the JSON decoding was successful
            $config = ReactConfig::get_data();
            $api = $config['reactAppMobiloServerUrl'];

            if (!$api) {
                mobilo_log(__METHOD__, 'OrgId update failed due to invalid API, orgId: ' . $organization_id);
                return false;
            }

            $update_profile_api = "$api/user/update-profile";

            // Prepare the request data (parameters) in JSON format
            $request_data = json_encode(array(
                'orgId' => $organization_id,
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

            $response = wp_remote_post($update_profile_api, $args);
            $res = wp_remote_retrieve_body($response);
            mobilo_log(__METHOD__, "#$organization_id  : " . $update_profile_api . ' - ' . $res, 'debug');

            return true;
        } catch (\Throwable $e) {
            mobilo_log(__METHOD__, $e->getMessage());
            mobilo_log(__METHOD__, $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Add Organization ID field to the WooCommerce coupon form and populate it with the saved value if available.
     * 
     * @since 1.0.0
     * @version 1.0.0
     */
    function add_edo_compatible_meta_boxes()
    {
        global $post;

        if ('shop_coupon' === $post->post_type) {
            // Add the edo compatible meta box to the shop_coupon post type
            add_meta_box('lwmc_edo_compatible_meta_box', __('EDO Compatible', 'woocommerce'), [$this, 'add_edo_compatible_meta_box_fields'], 'shop_coupon', 'side', 'high');
        }
    }

    /**
     * Displays and handles the meta box fields for proration.
     *
     * @param WP_Post $post The current post object.
     * @since 1.0.0
     * @version 1.0.1
     */
    public function add_edo_compatible_meta_box_fields($post)
    {
        $meta_field_data = get_post_meta($post->ID, '_lwmc_edo_compatible_enable', true);

        $nonce_field = '<input type="hidden" name="lwmc_edo_compatible_meta_field_nonce" value="' . wp_create_nonce() . '">';
        $checkbox_field = '<p style="border-bottom:solid 1px #eee;padding-bottom:13px;">Check this box to enable:
            <input type="checkbox" id="lwmc_edo_compatible_enable" name="lwmc_edo_compatible_enable" ' . $meta_field_data . ' value="true" ></p>';

        echo $nonce_field . $checkbox_field;
    }

    /**
     * Save the Organization ID value when the coupon is created or updated.
     * 
     * @since 1.0.0
     * @version 1.0.0
     */
    function save_edo_compatible($post_id)
    {
        if ('shop_coupon' !== get_post_type($post_id) || !isset($_POST['lwmc_edo_compatible_enable'])) {
            return;
        }

        // Get the Organization ID value from the POST data
        $value = $_POST['lwmc_edo_compatible_enable'] == true ? 'checked' : '';

        // Save the Organization ID value as post meta
        update_post_meta($post_id, '_lwmc_edo_compatible_enable', $value);
    }

    /**
     * Checks the status of a coupon in the EDO system.
     *
     * @since 1.0.4
     * @version 1.0.1
     * @param int $coupon_id The ID of the coupon to check.
     * @return bool The status of the coupon in the EDO system.
     */
    public static function check_coupon_edo_status($coupon_id)
    {
        /* $meta_field_data = get_post_meta($coupon_id, '_lwmc_edo_compatible_enable', true);

        return $meta_field_data == "checked"; */
        return true;
    }
}
