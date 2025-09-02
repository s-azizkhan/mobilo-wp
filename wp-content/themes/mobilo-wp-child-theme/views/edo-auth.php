<?php

/**
 * EDO Auth Modals
 *
 */

defined('ABSPATH') || exit;

?>


<style>
    /* Hide the up and down arrows in number input */
    .otp-digit::-webkit-inner-spin-button,
    .otp-digit::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .otp-digit {
        -moz-appearance: textfield;
    }

    #cfw-alert-container {
        display: none !important;
    }

    /* Hide  Default Modal */
    .modaal-start_fade {
        display: none;
    }

    /* Style for the modal */
    /*#edoOtpModal {*/
    /*    display: none;*/
    /*    position: fixed;*/
    /*    top: 50%;*/
    /*    left: 50%;*/
    /*    transform: translate(-50%, -50%);*/
    /*    width: 590px;*/
    /*    height: 404px;*/
    /*    background-color: #fff;*/
    /*    padding: 20px;*/
    /*    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);*/
    /*    z-index: 9999;*/
    /*    border-radius: 8px;*/
    /*}*/
    
    #edoOtpModal {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 590px;
        min-height: 404px; /* Minimum height to start with */
        overflow-y: auto;
        background-color: #fff;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        z-index: 9999;
        border-radius: 8px;
    }


    /* For mobile responsive */
    @media only screen and (max-width: 600px) {
        #edoOtpModal {
            width: 90%;
            height: auto;
        }
    }

    /* Style for the modal overlay */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9998;
    }

    /* Style to center the modal in the viewport */
    .center {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .otp-field {
        display: flex;
        text-align: center;
        gap: 8px;
        justify-content: center;
        align-items: center;
    }

    .otp-field input {
        width: 56px;
        padding: 10px 16px;
        border-radius: 10px;
        border: 1px solid #E8E8E8;
        background: #FFF;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-bottom: 40px;

        outline: none;

        color: var(--Primary-Text, #262626);
        font-family: DM Sans;
        font-size: 24px;
        font-style: normal;
        font-weight: 700;
        line-height: 150%;
        text-align: center;
    }

    .otp-field input:focus {
        border-radius: 10px;
        border: 1px solid #E8E8E8;
        background: #F5F4F9;
    }

    .disabled {
        opacity: 0.5;
    }

    .space {
        margin-right: 1rem !important;
    }

    .title-header {
        color: var(--Primary-Text, #262626) !important;
        font-family: DM Sans;
        font-size: 24px;
        font-style: normal;
        font-weight: 700 !important;
        line-height: 150%;
        text-align: center;
    }

    .desc-text {
        color: var(--Main-Black, #262626);
        text-align: center;
        font-family: DM Sans;
        font-size: 18px;
        font-style: normal;
        font-weight: 400;
        line-height: 140%;
        margin-bottom: 32px !important;
    }

    .desc-text-bold {
        color: var(--Main-Black, #262626);
        font-family: DM Sans;
        font-size: 18px;
        font-style: normal;
        font-weight: 700;
        line-height: 140%;
    }

    .cfw-verify-btn {
        display: flex;
        width: 200px !important;
        height: 55px;
        padding: 0px 16px;
        justify-content: center;
        align-items: center;
        gap: 8px;
        border-radius: 10px;
        background: #E5505A;
        border: none;
        cursor: pointer;

        color: #FFF;
        font-family: DM Sans;
        font-size: 16px;
        font-style: normal;
        font-weight: 500;
        line-height: 174%;
    }

    #lwmc-verify-otp-btn {
        display: flex;
        width: 200px !important;
        height: 55px;
        padding: 0px 16px;
        justify-content: center;
        align-items: center;
        gap: 8px;
        border-radius: 10px;
        background: #E5505A;
        border: none;
        cursor: pointer;
        color: #FFF;
        font-family: DM Sans;
        font-size: 16px;
        font-style: normal;
        font-weight: 500;
        line-height: 174%;
    }

    /* close btn styles */
    .close-btn-wrapper {
        display: flex;
        justify-content: flex-end;
    }

    span#modalCloseBtn {
        cursor: pointer;
    }
</style>
<!-- Include DM Sans from Google Fonts -->
<!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap"> -->
<div class="modal-overlay" id="modalOverlay"></div>
<div class="center">
    <div id="edoOtpModal">
        <div class="close-btn-wrapper">
            <span class="close" id="modalCloseBtn" style="cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36" fill="none">
                    <rect x="0.5" y="35.5" width="35" height="35" rx="11.5" transform="rotate(-90 0.5 35.5)" fill="white" stroke="#EEEEEE" />
                    <path d="M22.7518 21.548L20.9359 23.3639L17.7454 20.1734L14.8095 23.1093L12.9597 21.2595L15.8956 18.3236L12.7222 15.1501L14.538 13.3343L17.7115 16.5078L20.6474 13.5719L22.4972 15.4217L19.5613 18.3576L22.7518 21.548Z" fill="#262626" />
                </svg>
            </span>
        </div>
        <form id="verifyEdoOtpForm" method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
            <input type="hidden" name="action" value="handle_custom_form_submission">
            <?php wp_nonce_field('handle_custom_form_nonce', 'handle_custom_form_nonce'); ?>
            <input type="hidden" name="fingerprint" id="lwmc_fingerprint" value="">
            <input type="hidden" name="email_id" id="lwmc_email_id" value="">
            <input type="hidden" name="firebase_id" id="lwmc_firebase_id" value="">
            <h2 class="title-header">Authentication</h2>

            <p class="desc-text">Welcome <span class="lwmc-user-full-name"></span>. To complete your account verification, please enter the one-time code you received that’s been emailed to <span class="desc-text-bold lwmc-show-email"></span></p>

            <div class="otp-field">
                <input type="number" class="otp-digit" maxlength="1" name="otp[]" required>
                <input type="number" class="otp-digit" maxlength="1" name="otp[]" required>
                <input type="number" class="otp-digit" maxlength="1" name="otp[]" required>
                <input type="number" class="otp-digit" maxlength="1" name="otp[]" required>
                <input type="number" class="otp-digit" maxlength="1" name="otp[]" required>
            </div>
            
            <?php if( !isset($_COOKIE['lwmc_organization_id'], $_COOKIE['lwmc_show_edo_modal']) ) : ?>
                <p class="form-row" id="lwmc_edo_pass_row" style="text-align: center;">
                    <span style="color: var(--Grey, #646D7A);font-family: DM Sans;font-size: 14px;font-style: normal;font-weight: 400;line-height: 174%;">Or,</span>                    
                    <span id="lwmc_edo_password_link" style="cursor: pointer;color: var(--Primary-Brand-Red, #D33F49);font-family: DM Sans;font-size: 16px;font-style: normal;font-weight: 500;line-height: 174%;"> Login with Password Instead</span>
                </p>
            <?php endif; ?>

            <div class="cfw-login-modal-footer">
                <p class="form-row">
                    <span style="color: var(--Grey, #646D7A);font-family: DM Sans;font-size: 14px;font-style: normal;font-weight: 400;line-height: 174%;">Haven't received it?</span>
                    <br>
                    <span id="lwmc_edo_resend_otp" style="cursor: pointer;color: var(--Primary-Brand-Red, #D33F49);font-family: DM Sans;font-size: 16px;font-style: normal;font-weight: 500;line-height: 174%;">Resend code.</span>
                </p>
                <button type="submit" id="lwmc-verify-otp-btn" class="" name="submit" value="Login">Submit</button>
            </div>
        </form>
    </div>
</div>