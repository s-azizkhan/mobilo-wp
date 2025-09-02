<?php

namespace Mobilo\WpTheme\Actions\Checkout;

use Mobilo\WpTheme\Actions\MobiloAjaxAction;
use Mobilo\WpTheme\Feature\EDOFeature;

defined('ABSPATH') || exit;


/**
 * Class SendEdoOtpAction
 *
 * @version 1.0.1
 * @since 0.1.61
 * @package Mobilo\WpTheme\Actions\Checkout;
 */
class SendEdoOtpAction extends MobiloAjaxAction
{

    /**
     * GetFirebaseAddressAction constructor.
     *
     * @version 1.0.1
     * @since 1.0.0
     * @access public
     */
    public function __construct()
    {
        parent::__construct('send_edo_otp', false);
    }

    /**
     * Execute the action callback
     *
     * @version 1.0.0
     * @since 1.0.0
     * @access public
     */
    public function action()
    {
        try {
            $email = $_REQUEST['email'] ?? '';

            // sanitize input
            $email = sanitize_email($email);

            $message = 'Resend OTP failed, please try again';

            if ($email) {

                $edo_feat = new EDOFeature();
                $otp_status = $edo_feat->create_otp($email, true);

                if ($otp_status) {
                    $message = 'OTP sent successfully';
                }
            } else {
                $otp_status = false;
                $message = 'Please provide valid email address';
            }

            $res = [
                'success' => true,
                'otp_status' => $otp_status,
                'message' => $message,
            ];

            parent::out($res, 200);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            $response = apply_filters("wp_ajax_{$this->get_id()}_response", array('success' => false, 'message' => $e->getMessage()));
            parent::out($response, 500);
        }
    }
}
