<?php

namespace Mobilo\WpTheme\Feature;

use Mobilo\WpTheme\Actions\Checkout\SendEdoOtpAction;
use Mobilo\WpTheme\Actions\Checkout\VerifyEdoOtpAction;

defined('ABSPATH') || exit;

/**
 * Class EDOFeature
 *
 * @package Mobilo\WpTheme\Feature
 */
class EDOFeature
{
    /**
     * Flag to indicate whether org IDs match
     *
     * @var bool
     */
    public $org_ids_match = false;

    /**
     * Flag to indicate userProfileData
     *
     * @since 1.0.1
     * @version 1.0.0
     * @access public
     * @var string $userProfileData
     */
    public $userProfileData = [];

    /**
     * Get the full organization ID with prefix.
     * @since 1.0.0
     * @version 1.0.0
     * @return string
     */
    public static function getOrganizationIdKey()
    {
        return OrganizationIdManager::getOrganizationIdKey();
    }

    public function __construct()
    {
        // Set the organization ID meta key
        // $this->organization_id_meta_key = self::getOrganizationIdKey();
    }

    public function run()
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
        add_filter('cfw_email_exists', [$this, 'check_organization'], 5, 2);
        add_action('cfw_checkout_main_container_start', [$this, 'maybe_output_edo_modal_container'], 10);

        // add_action('init', [$this, 'init_ajax'], 10);
        // add_action('woocommerce_applied_coupon', [$this, 'set_org_from_coupon'], 10);
        add_action('wp_head', [$this, 'enqueue_checkout_custom_scripts']);
        add_action('cfw_checkout_after_main_container', [$this, 'enqueue_feat_script'], 10000 * 99999);
    }

    /**
     * Initializes the AJAX functionality for this feature.
     *
     * This function sets up the AJAX action listeners and performs any necessary setup for AJAX functionality.
     *
     * @since 1.0.1
     * @version 1.0.0
     * @return void
     */
    public function init_ajax()
    {
        /**
         * Setup Ajax Action Listeners
         */
        (new VerifyEdoOtpAction())->load();
        (new SendEdoOtpAction())->load();
    }
    /**
     * Enqueues the featured script for checkout.
     *
     * @since 1.0.1
     * @version 1.0.2
     * @return void
     */
    public function enqueue_feat_script()
    {
        if (!cfw_is_checkout() || !is_checkout()) {
            return;
        }
        $script_path = MOBILO_THEME_URL . '/assets/js/checkout.js';
        ?>
        <script type='text/javascript' src="<?php echo $script_path; ?>"></script>
        <script type='text/javascript' id='lwmc-edo-feat-js'></script>
        <?php
    }

    // enqueue a checkout js script
    public function enqueue_checkout_custom_scripts()
    {
        if (is_checkout() || is_page('checkout')) {
            $nonce = wp_create_nonce('mobilo_react_ajax_nonce');
            ?>
            <script>
                window.MyAppData = {
                    wpnonces: "<?php echo esc_js($nonce); ?>"
                };
            </script>
            <?php
        }
    }

    public static function get_api_key_headers()
    {
        return [
            'Content-Type' => 'application/json', // Set the content type to JSON
            'X-API-KEY' => MOBILO_X_API_KEY,
        ];
    }

    /**
     * Sets the organization ID to cookie and redirects based on a coupon code.
     *
     * @param string $coupon_code The coupon code.
     * @return void
     */
    public function set_org_from_coupon($coupon_code)
    {
        $coupon_id = wc_get_coupon_id_by_code($coupon_code);

        $has_edo_by_coupon = OrganizationIdManager::check_coupon_edo_status($coupon_id);

        if (!$has_edo_by_coupon) {
            return;
        }

        // Get the Organization ID from the coupon
        $org_id = get_post_meta($coupon_id, OrganizationIdManager::getOrganizationIdKey(), true);

        if (!empty($org_id) && WC()->cart->get_cart_contents_count() > 0) {
            // Save the organization ID in a cookie
            setcookie(self::getOrganizationIdKey(), $org_id, time() + 36000, '/'); // Expire in 10 hour

            // Redirect to the checkout page without logging out
            wp_redirect(wc_get_checkout_url());
            exit;
        }
    }

    /**
     * Check is EDO order mode is enabled by checking cookie
     *
     * @since 1.0.7
     * @version 1.0.0
     */
    public static function isEdoOrderModeEnabled()
    {
        if (isset($_COOKIE[self::getOrganizationIdKey()])) {
            return true;
        }
        return false;
    }

    /**
     * Disable the EDO order mode
     *
     * @since 1.0.7
     * @version 1.0.0
     */
    public static function disableDEdoOrderMode()
    {
        setcookie(self::getOrganizationIdKey(), '', time() - 7, '/');
    }

    /**
     * Hides WooCommerce checkout notices if the current page is the checkout page.
     *
     * @since 1.0.1
     * @version 1.0.0
     */
    public function hide_wc_checkout_notices()
    {
        if (is_checkout()) {
            echo '<style>.woocommerce-notices-wrapper { display: none; }</style>';
        }
    }

    /**
     * Verify the one-time password (OTP) for a given email and fingerprint.
     *
     * @since 1.0.1
     * @version 1.0.0
     * @param string $email The email address associated with the OTP.
     * @param int $otp The OTP to be verified.
     * @param string $fingerprint The fingerprint of the device used to generate the OTP.
     * @return array The function returns an array containing the data received from the API and the verification status.
     */
    public function verify_otp($email, $otp, $fingerprint)
    {
        if (empty($email) || empty($otp)) {
            return ["data" => [], "verify_status" => false];
        }

        if (empty($fingerprint)) {
            $fingerprint = self::get_fingerprint();
        }
        $verify_api_url = MOBILO_APK_GATEWAY . '/v1/otp/' . $otp . '/' . $email . '/' . $fingerprint;
        // GET request using 'wp_remote_get'
        $response = wp_remote_get($verify_api_url, [
            'timeout' => 60,
            'headers' => $this->get_api_key_headers(),
        ]);

        $res = wp_remote_retrieve_body($response);
        $jsonResp = json_decode($res);

        return ["data" => $jsonResp, "verify_status" => wp_remote_retrieve_response_code($response) == 200];
    }

    /**
     * Retrieves & sets the browser fingerprint for the current user.
     *
     * This function checks if the 'lwmc_browser_fingerprint' cookie is set.
     * If it is set, the function retrieves and sanitizes the value.
     * If the cookie is not set, the function generates a fingerprint using the user agent and IP address,
     * saves it in the cookie, and returns the generated fingerprint.
     *
     * @since 1.0.1
     * @version 1.0.0
     * @return string The browser fingerprint.
     */
    public static function get_fingerprint()
    {
        $fingerprint = isset($_COOKIE['lwmc_browser_fingerprint']) ? sanitize_text_field(wp_unslash($_COOKIE['lwmc_browser_fingerprint'])) : '';
        if ($fingerprint == '') {
            $fingerprint = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
            // Save the fingerprint in a cookie
            setcookie('lwmc_browser_fingerprint', $fingerprint, time() + 36000, '/'); // set cookie with 10hr expiry
        }
        return $fingerprint;
    }

    /**
     * A function that may output the Edo Modal container.
     *
     * The function checks if login at checkout is allowed. If not, the function returns.
     * It also checks if the user is logged in. If true, the function returns.
     * The function then retrieves the organization ID from a cookie and sanitizes it.
     * If the organization ID is empty, the function returns.
     *
     * The function then outputs the HTML and CSS code for the Edo Modal container.
     * The code includes styles to hide the up and down arrows in number input,
     * hide the default modal, and style the modal and modal overlay.
     * The code also includes HTML elements for the modal content, including a form,
     * an input field for the one-time code, and a submit button.
     *
     * @since 1.0.1
     * @version 1.0.0
     */
    public function maybe_output_edo_modal_container()
    {
        if (!cfw_is_login_at_checkout_allowed()) {
            return;
        }

        if (is_user_logged_in()) {
            return;
        }

        // $org_id = isset($_COOKIE[self::getOrganizationIdKey()]) ? sanitize_text_field(wp_unslash($_COOKIE[self::getOrganizationIdKey()])) : '';
        // if (empty($org_id)) {
        //     return;
        // } 

        include_once MOBILO_THEME_PATH . '/views/edo-auth.php';
    }

    /**
     * Retrieves profile data for a given email.
     *
     * @since 1.0.1
     * @version 1.0.1
     * @param string $email The email address to retrieve the profile data for.
     * @throws Exception If there is an error retrieving the profile data.
     * @return stdClass|null The profile data as a stdClass object, or null if no profile data is found.
     */
    public static function get_profile_data($email)
    {
        $endPoint = '/v1/profiles/email/';
        $url = MOBILO_APK_GATEWAY . $endPoint . $email;
        $response = wp_remote_get($url, [
            'timeout' => 60,
            'headers' => self::get_api_key_headers(),
        ]);
        $getProfileResponse = wp_remote_retrieve_body($response);
        return json_decode($getProfileResponse);
    }


    /**
     * Checks if the organization exists and if the given email is valid.
     *
     * @since 1.0.1
     * @version 1.0.1
     * @param bool $exists Whether the organization exists or not.
     * @param string $email The email to be checked.
     * @return bool The value of $exists.
     */
    public function check_organization($exists, $email)
    {
        if ($exists && is_email($email)) {

            // normal otp login
            if (!isset($_COOKIE[self::getOrganizationIdKey()])) {

                $getProfileData = $this->get_profile_data($email);
                if (isset($getProfileData->data->displayName)) {
                    $uname = $getProfileData->data->displayName;
                }

                setcookie("lwmc_user_modal_name", $uname, time() + 36000, '/'); // Expire in 10 hour
                $this->create_otp($email);
            }

            $org_id = isset($_COOKIE[self::getOrganizationIdKey()]) ? sanitize_text_field(wp_unslash($_COOKIE[self::getOrganizationIdKey()])) : '';

            if (!empty($org_id)) {
                $isEdoEnabled = $this->check_is_edo_enabled($org_id, $email);

                if ($isEdoEnabled) {
                    $time = time();
                    setcookie("lwmc_show_edo_modal", json_encode($this->userProfileData), $time + 36000, '/'); // Expire in 10 hour

                    // Send OTP
                    $otp_created = $this->create_otp($email);
                    if ($otp_created) {
                        mobilo_log(__METHOD__, ": OTP created for " . $email, 'info');
                    } else {
                        mobilo_log(__METHOD__, ": OTP creation failed for " . $email);
                    }
                    return $exists;
                }
                //else {
                //    // Remove the cookie
                //    setcookie("lwmc_show_edo_modal", '', time() - 3600, '/');
                //}
            }
        }
        setcookie("lwmc_show_edo_modal", '', time() - 3600, '/');
        // Continue with the default behavior
        return $exists;
    }




    /**
     * Checks if EDO is enabled for a given organization and email.
     *
     * @since 1.0.1
     * @version 1.0.0
     * @param int $org_id The ID of the organization.
     * @param string $email The email address.
     * @return bool Returns true if EDO is enabled, false otherwise.
     */
    public function check_is_edo_enabled($org_id, $email)
    {
        $transient_key = "lw_edo_enabled_" . $email;

        // Check data from transient
        $t_data = get_transient($transient_key);
        if (!empty($t_data)) {
            // Extend the expiration time
            set_transient($transient_key, true, MINUTE_IN_SECONDS); // Store for 1 minute
            return true;
        }

        $getProfileData = $this->get_profile_data($email);

        if (!$getProfileData || empty($getProfileData)) {
            mobilo_log(__METHOD__, "getProfileData is empty for email: " . $email, 'debug');
            return false;
        }

        if (!isset($getProfileData->data)) {
            mobilo_log(__METHOD__, "getProfileData>data is empty for email: " . $email, 'debug');
            return false;
        }
        $getProfileData = $getProfileData->data;

        ['userId' => $fUserId, "Full Name" => $fullName] = (array) $getProfileData;
        $profileOrgId = isset($getProfileData->orgId) ? $getProfileData->orgId : '';

        $this->userProfileData = [
            "fUserId" => $fUserId,
            "fullName" => $fullName,
            "orgId" => $profileOrgId
        ];

        if ($profileOrgId && $profileOrgId == $org_id) {
            $has_edo_by_org = $this->is_edo_enabled_by_org_id($org_id);
            return $has_edo_by_org;
        }
        return false;
    }

    /**
     * Check if Edo is enabled for a given email address.
     *
     * @since 1.0.1
     * @version 1.0.0
     * @param string $email The email address to check.
     * @return bool Returns true if Edo is enabled for the email, false otherwise.
     */
    public static function is_edo_enabled_by_email($email)
    {
        $transient_key = "lw_edo_enabled_" . $email;
        $t_data = get_transient($transient_key);
        if (!empty($t_data)) {
            return true;
        }
        return false;
    }

    /**
     * Checks if EDO is enabled for a given organization ID.
     *
     * @since 1.0.1
     * @version 1.0.1
     * @param int $org_id The ID of the organization.
     * @throws \Throwable Error occurred during the execution of the function.
     * @return bool Returns true if EDO is enabled, false otherwise.
     */
    public function is_edo_enabled_by_org_id($org_id)
    {
        try {
            $transient_key = "lw_edo_enabled_" . $org_id;
            $response = $this->get_org_data($org_id);
            $res_data = $response->data;

            if (is_array($res_data) && !empty($res_data)) {
                // Check if there is an element with key "purchaseType" and value "EDO" || "EDOCustomDesign"
                $hasEDOElement = false;

                foreach ($res_data as $object) {
                    if (isset($object->key, $object->value) && $object->key === "purchaseType" && ($object->value === "EDO" || $object->value === "EDOCustomDesign")) {
                        $hasEDOElement = true;
                        self::set_org_purchase_type($org_id, $object->value);
                        break; // Exit the loop once a matching element is found
                    }
                }

                // configure transient based on the value of $hasEDOElement
                if ($hasEDOElement) {
                    set_transient($transient_key, true, MINUTE_IN_SECONDS); // Store for 1 minute
                } else {
                    // delete transient
                    delete_transient($transient_key);
                }

                return $hasEDOElement;
            }
            return false;
        } catch (\Throwable $e) {
            mobilo_log(__METHOD__, $e->getMessage());
            mobilo_log(__METHOD__, $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Retrieves organization data from the API.
     *
     * @since 1.0.1
     * @version 1.0.0
     * @param int $org_id The ID of the organization.
     * @throws Exception If the API call fails.
     * @return mixed The organization data.
     */
    public static function get_org_data($org_id)
    {
        // API call to get organization settings
        $api_url = MOBILO_APK_GATEWAY . '/v1/settings/organization/' . $org_id . '/key/purchaseType';
        $responseOrg = wp_remote_get($api_url, [
            'timeout' => 60,
            'headers' => self::get_api_key_headers(),
        ]);
        $res_body = wp_remote_retrieve_body($responseOrg);
        $res_data = json_decode($res_body);
        return $res_data;
    }

    /**
     * Create OTP for a given email.
     *
     * @since 1.0.1
     * @version 1.0.0
     * @param string $email The email for which the OTP is to be created.
     * @param bool $bypassCookie (optional) Whether to bypass the cookie check. Defaults to false.
     * @throws Some_Exception_Class If an error occurs during the OTP creation.
     * @return bool True if the OTP is successfully created, false otherwise.
     */
    public function create_otp($email, $bypassCookie = false)
    {

        // Check if email already sent OTP
        $otpSent = isset($_COOKIE['lwmc_edo_mail_otp']) ? sanitize_text_field(wp_unslash($_COOKIE['lwmc_edo_mail_otp'])) : '';
        if (!$bypassCookie && $otpSent == $email) {
            return true;
        }

        $api_create_otp = MOBILO_APK_GATEWAY . '/v1/otp';

        $browserFingerprint = self::get_fingerprint();

        // Prepare the request data (parameters) in JSON format
        $request_data = json_encode([
            'email' => $email,
            'fingerprint' => $browserFingerprint,
        ]);

        // Prepare the request arguments
        $args = [
            'timeout' => 60,
            'headers' => $this->get_api_key_headers(),
            'body' => $request_data,
        ];

        $res = wp_remote_post($api_create_otp, $args);
        $responseOtp = wp_remote_retrieve_body($res);
        $jsonResp = json_decode($responseOtp);

        if ($jsonResp && wp_remote_retrieve_response_code($res) == 201) {
            // set the cookie to remember that this mail sented otp for five minute
            setcookie("lwmc_edo_mail_otp", $email, time() + 300, '/');
            setcookie("lwmc_edo_mail_send_after", time() + 30, time() + 300, '/'); // after 30sec user can resend otp
            return true;
        } else {
            // clear the cookie
            setcookie("lwmc_edo_mail_send_after", "", time() - 3600, '/');
            setcookie("lwmc_edo_mail_otp", "", time() - 3600, '/');
            return false;
        }
    }

    /**
     * Set the organization purchase type.
     *
     * @since 1.0.9
     * @param int $org_id The ID of the organization.
     * @param string $purchase_type The purchase type of the organization.
     * @return void
     */
    public static function set_org_purchase_type($org_id, $purchase_type)
    {
        $transient_key = MOBILO_PREFIX . "_purchase_type_" . $org_id;
        set_transient($transient_key, $purchase_type, MINUTE_IN_SECONDS * 5); // Store for 5 minute
    }

    /**
     * Get the organization purchase type.
     *
     * @since 1.0.9
     * @param int $org_id The ID of the organization.
     * @return string The purchase type of the organization, or an empty string if not found.
     */
    public static function get_org_purchase_type($org_id)
    {
        $transient_key = MOBILO_PREFIX . "_purchase_type_" . $org_id;
        return get_transient($transient_key);
    }

    /**
     * Check if the organization purchase type is EDOCustomDesign.
     * 
     * @since 1.0.9
     * @param int $org_id The ID of the organization.
     */
    public static function is_purchase_type_edo_custom($org_id)
    {
        return self::get_org_purchase_type($org_id) === "EDOCustomDesign";
    }
}
