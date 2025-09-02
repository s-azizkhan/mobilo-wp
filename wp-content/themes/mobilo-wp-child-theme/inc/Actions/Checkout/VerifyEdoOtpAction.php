<?php

namespace Mobilo\WpTheme\Actions\Checkout;

use MobiloAuth\Core\FirebaseAuth;
use MobiloAuth\Core\UserManager;
use Mobilo\WpTheme\Actions\MobiloAjaxAction;
use Mobilo\WpTheme\Feature\EDOFeature;

defined('ABSPATH') || exit;


/**
 * Class VerifyEdoOtpAction
 *
 * @link logicwind.com
 * @version 1.0.2
 * @since 0.1.61
 * @package Mobilo\WpTheme\Actions\Checkout;
 */
class VerifyEdoOtpAction extends MobiloAjaxAction
{

    /**
     * GetFirebaseAddressAction constructor.
     *
     * @version 1.0.0
     * @since 1.0.0
     * @access public
     */
    public function __construct()
    {
        parent::__construct('verify_edo_otp', false);
    }

    /**
     * Execute the action callback
     *
     * @version 1.0.1
     * @since 1.0.0
     * @access public
     */
    public function action()
    {
        try {
            $email = $_REQUEST['email'] ?? '';
            $firebase_id = $_REQUEST['firebase_id'] ?? '';
            $otp = $_REQUEST['otp'] ?? '';
            $fingerprint = $_REQUEST['fingerprint'] ?? '';

            // get the register user firebase id from email
            if (!empty($email) && is_email($email) && empty($firebase_id)) {
                $user = get_user_by('email', $email);
                if (!empty($user)) {
                    $firebase_id = get_user_meta($user->ID, 'user_firebase_id', true);
                }
            }

            // sanitize input
            $email = sanitize_email($email);
            $fingerprint = sanitize_text_field($fingerprint);
            $otp = sanitize_text_field($otp);
            $firebase_id = sanitize_text_field($firebase_id);

            $message = '';

            $edo_feat = new EDOFeature();

            $verificationData = $edo_feat->verify_otp($email, $otp, $fingerprint);

            //$user = $edo_feat->get_profile_data($email)->data;
            $user_id = false;
            if ($verificationData['verify_status']) {
                // Set the transient
                $transient_key = "lw_edo_enabled_" . $email;
                delete_transient($transient_key);

                // get the user by email from wp
                $userByEmail = get_user_by('email', $email);

                if (!$userByEmail) {

                    // this password won't be used to login the user as we are using firebase auth
                    $password = wp_generate_password();
                    $wc_user = wc_create_new_customer($email, '', $password);

                    // Handle user creation error
                    if (is_wp_error($wc_user)) {
                        $message = $wc_user->get_error_message();
                    } else {

                        error_log("wc_user");
                        // set the user id
                        $user_id = $wc_user;
                    }
                } else {
                    // set the user id
                    error_log("userByEmail");

                    $user_id = $userByEmail->ID;
                }
            } else {
                mobilo_log(__METHOD__, "verification: " . print_r($verificationData['data'], true), "info");
                mobilo_log(__METHOD__, "verification message: " . $verificationData['data']->reason, "info");

                $message = $verificationData['data']->reason ?: "Provided OTP code does not match.";
                $res = [
                    'success' => false,
                    'message' => $message,
                    'data' => $verificationData,
                    'result' => [
                        "errors" => [
                            "message" => $message
                        ]
                    ],
                    'user_id' => $user_id,
                    'user' => [],
                ];

                parent::out($res, 400);
            }

            $user = [];
            if ($user_id) {
                $user = get_user_by('id', $user_id);

                //  get firebase Id of this USER
                $firebase_id = UserManager::get_firebase_uid_by_wordpress_id($user_id);

                $firebaseProvider = new FirebaseAuth();

                // $signInRes = $firebaseProvider->signInByUserId($firebase_id);
                $signInRes = $firebaseProvider->signInByUserId($firebase_id);

                if (empty($signInRes)) {
                    throw new \Exception('Firebase sign in failed');
                }

                // set the firebase token to cookie
                /*$firebase_token_data = array(
                    'id_token' => $signInRes
                );*/

                $firebase_token_data = [
                    'token_type' => 'Bearer',
                    'access_token' => $signInRes['idToken'],
                    'id_token' => $signInRes['idToken'],
                    'refresh_token' => $signInRes['refreshToken'],
                    'expires_in' => $signInRes['expiresIn'],
                ];

                setcookie('user_firebase_token_data', '', time() - 3600, '/'); // clear cookie
                setcookie('user_firebase_token_data', json_encode($firebase_token_data), 2147483647, '/'); // set cookie with never expiry
                # Set an auth cookie.
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);

                // clear all related cookie
                $related_cookie = [
                    "lwmc_edo_mail_otp",
                    "lwmc_show_edo_modal",
                    'lwmc_browser_fingerprint'
                ];
                foreach ($related_cookie as $cookie) {
                    setcookie($cookie, '', time() - 3600, '/');
                }

                $message = "OTP verified successfully";

                $res = [
                    'success' => true,
                    'message' => $message,
                    'data' => $verificationData,
                    'user_id' => $user_id,
                    'user' => $user,
                ];

                parent::out($res, 200);
            }


        } catch (\Throwable $e) {
            mobilo_log(__METHOD__, $e->getMessage());
            $response = apply_filters("wp_ajax_{$this->get_id()}_response", ['success' => false, 'message' => $e->getMessage()]);
            parent::out($response, 500);
        }
    }
}
