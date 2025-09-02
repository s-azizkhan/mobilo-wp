jQuery(function ($) {

    // Select the checkbox by its ID
    const checkbox = document.getElementById('kl_sms_consent_checkbox');

    // Check if the checkbox exists, then set it to checked
    if (checkbox) {
        checkbox.checked = true;  // This will check the checkbox
    }

    // Select the p element with ID 'kl_sms_consent_checkbox_field'
    const klTargetElement = document.querySelector('p#kl_sms_consent_checkbox_field');

    // Check if the target element exists and has a parent element
    if (klTargetElement && klTargetElement.parentElement) {
        // Apply styles directly to the parent element
        klTargetElement.parentElement.style.display = 'none';
    }

    const mcTargetElement = document.querySelector('p.form-row.form-row-wide.mailchimp-newsletter');

    // Check if the target element exists and has a parent element
    if (mcTargetElement) {
        // Apply styles directly to the parent element
        mcTargetElement.style.display = 'none';
    }

    // Remove the href from the logo
    jQuery('.logo').attr('href', '#');
    // Disabled the logo click
    jQuery('.logo').click(function (e) {
        e.preventDefault();
    });
    // change the mouse pointer to default
    jQuery('.logo').css('cursor', 'default');

    document.querySelectorAll('.recurring-total').forEach(function (element) {
        element.style.display = 'none';
    });

    class AjaxOptions {
        constructor(url, method = 'get', data = {}) {
            this.url = url;
            this.method = method;
            this.data = data;
        }
    }

    class AjaxService {
        request(options, successCallback, errorCallback, completeCallback) {
            const currentRequest = $.ajax({
                url: options.url,
                type: options.method,
                data: options.data,
                cache: false,
                success: successCallback,
                error: (d) => {
                    if (errorCallback) {
                        errorCallback(d);
                    } else {
                        const errorTitle = `Error in (${options.url})`;
                        const fullError = JSON.stringify(d);
                        console.log(errorTitle, fullError);
                        alert(`${errorTitle}\n${fullError}`);
                    }
                },
                complete: completeCallback,
            });
            return currentRequest;
        }

        get(url, successCallback, errorCallback) {
            this.request(new AjaxOptions(url), successCallback, errorCallback);
        }

        getWithDataInput(url, data, successCallback, errorCallback) {
            this.request(
                new AjaxOptions(url, 'get', data),
                successCallback,
                errorCallback
            );
        }

        post(url, successCallback, errorCallback) {
            this.request(
                new AjaxOptions(url, 'post'),
                successCallback,
                errorCallback
            );
        }

        postWithData(url, data, successCallback, errorCallback) {
            this.request(
                new AjaxOptions(url, 'post', data),
                successCallback,
                errorCallback
            );
        }
    }

    class Cookies {
        static get(name) {
            const cookieArr = decodeURIComponent(document.cookie).split(';');
            for (let cookie of cookieArr) {
                cookie = cookie.trim();
                if (cookie.startsWith(`${name}=`)) {
                    return cookie.substring(name.length + 1);
                }
            }
            return null;
        }

        static set(name, value, options) {
            let cookieString = `${encodeURIComponent(
                name
            )}=${encodeURIComponent(value)}`;
            if (options.expires) {
                cookieString += `; expires=${new Date(
                    options.expires
                ).toUTCString()}`;
            }
            if (options.path) cookieString += `; path=${options.path}`;
            if (options.domain) cookieString += `; domain=${options.domain}`;
            if (options.secure) cookieString += '; secure';
            document.cookie = cookieString;
        }

        static remove(name) {
            Cookies.set(name, '', { expires: new Date(0) });
        }
    }

    let EdoCurrentRequest = null;

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    class EdoFeature {
        constructor() {
            this.setListeners();

            const orgId = Cookies.get('lwmc_organization_id');
            if (orgId) {
                // hide a div by
                jQuery('.showlogin').hide();
                jQuery('.cfw-have-acc-text').hide();
                jQuery('#cfw-login-modal-trigger').hide();
            }
        }

        setListeners() {
            jQuery(document.body).on(
                'cfw_account_exists updated_checkout cfw_account_not_exists',
                ({ type }) => console.log('type :>> ', type)
            );

            jQuery(document.body).on('click', '#lwmc_edo_password_link', (e) => {
                if (!Cookies.get('lwmc_organization_id')) {
                    EdoFeature.openDefaultLoginModal()
                }
            });

            jQuery(document.body).on('submit', 'form#cfw_lost_password_form', (e) => {
                e.preventDefault();

                const url = `${window.location.origin}/?wc-ajax=lwmc_reset_password`;

                const formData = {
                    email: $('form#cfw_lost_password_form #user_login').val(),
                    _ajaxNonce: MyAppData.wpnonces
                };

                const service = new AjaxService();
                const options = new AjaxOptions(url, 'POST', formData);
                console.log(options);
                EdoCurrentRequest = service.request(
                    options,
                    null,
                    null,
                    EdoFeature.completeCallback
                );
            });

            jQuery(document.body).on('click', '#cfw-login-modal-trigger', () => {
                EdoFeature.closeLoginModal();
                window.location.href = `${window.location.origin}/login`;
            });

            jQuery(document.body).on('click', '.showlogin', () => {
                window.location.href = `${window.location.origin}/login`;
                // EdoFeature.openModal()
            });

            jQuery(document.body).on('cfw_account_exists', () => {
                EdoFeature.openModal();
            });
            jQuery(document.body).on('updated_checkout', () => {
                if (authData) return;
                const orgId = Cookies.get('lwmc_organization_id');
                const userData = Cookies.get('lwmc_show_edo_modal');
                const authData = Cookies.get('user_firebase_token_data');
                console.log('updated_checkout :>> ', orgId);
                console.log('updated_checkout :>> ', userData);
                if (orgId && !userData) {
                    const billing_email = jQuery('#billing_email').val().trim();
                    console.log('cfw_account_not_exists :>> ', billing_email);
                    // if email not valid then do nothing
                    if (!billing_email || !isValidEmail(billing_email)) {
                        return;
                    }

                    console.log('userData :>> ', userData);
                    alert(
                        'Please enter a email address that belongs to corresponding organization.'
                    );
                    // clear the email input
                    jQuery('#billing_email').val('');
                    EdoFeature.closeLoginModal();
                }
            });

            jQuery(document.body).on('click', '#cfw_login_modal_close', (e) => {
                e.preventDefault();
                EdoFeature.closeModal();
            });

            jQuery(document.body).on('click', '#lwmc_edo_resend_otp', (e) => {
                e.preventDefault();
                jQuery('.otp-digit').val('');
                EdoFeature.handleResendOtp();
            });

            jQuery('.otp-digit').on('keyup', function () {
                if (
                    jQuery(this).val().length === parseInt(jQuery(this).attr('maxlength'))
                ) {
                    jQuery(this).next('.otp-digit').trigger('focus');
                }
            });

            jQuery('.otp-digit').on('keydown', function (e) {
                if (e.which === 8 && !jQuery(this).val()) {
                    jQuery(this).prev('.otp-digit').trigger('focus');
                } else if (
                    jQuery(this).val().length === parseInt(jQuery(this).attr('maxlength'))
                ) {
                    jQuery(this).next('.otp-digit').trigger('focus');
                }
            });

            jQuery('.otp-digit').on('input', function () {
                jQuery(this).val(jQuery(this).val().slice(0, 1));
                EdoFeature.checkAndAutoSubmitOtp();
            });

            jQuery(document.body).on('submit', '#verifyEdoOtpForm', (e) => {
                e.preventDefault();
                EdoFeature.handleOtpFormSubmit();
            });
        }

        static checkAndAutoSubmitOtp() {
            if (Cookies.get('lwmc_autoOtpSubmitted')) return;

            let fullOTP = '';
            jQuery('.otp-digit').each(function () {
                fullOTP += jQuery(this).val();
            });
            if (fullOTP.length > 4) {
                EdoFeature.handleOtpFormSubmit();
                Cookies.set('lwmc_autoOtpSubmitted', '1', {
                    expires: new Date(Date.now() + 86400000),
                });
            }
        }

        static async handleOtpFormSubmit() {
            if (
                EdoCurrentRequest &&
                typeof EdoCurrentRequest.abort === 'function'
            ) {
                EdoCurrentRequest.abort();
            }

            jQuery('#lwmc-verify-otp-btn')
                .prop('disabled', true)
                .html('Verifying...');

            const email = jQuery('#lwmc_email_id').val();
            const firebaseId = jQuery('#lwmc_firebase_id').val();
            const fingerprint = jQuery('#lwmc_fingerprint').val();

            let fullOTP = '';
            jQuery('.otp-digit').each(function () {
                fullOTP += jQuery(this).val();
            });

            //if (!fullOTP || !email || !firebaseId) {
            if (!fullOTP || !email) {
                jQuery('#lwmc-verify-otp-btn').prop('disabled', false);
                return;
            }

            const formData = {
                fingerprint,
                email,
                firebase_id: firebaseId,
                otp: fullOTP,
            };
            const url = EdoFeature.getAjaxUrl('verify_edo_otp');

            const service = new AjaxService();
            //const formData = {
            //    'fingerprint': fingerprint,
            //    'email': email,
            //    'firebase_id': firebaseId,
            //    'otp': fullOTP
            //};
            const options = new AjaxOptions(url, 'POST', formData);
            EdoCurrentRequest = await service.request(
                options,
                EdoFeature.verifyOtpResponse,
                EdoFeature.verifyOtpError,
                EdoFeature.completeCallback
            );

            setTimeout(() => {
                jQuery('#lwmc-verify-otp-btn')
                    .prop('disabled', false)
                    .html('Submit');
            }, 10);
        }

        static completeCallback() {
            EdoCurrentRequest = null;
        }

        static async handleResendOtp() {
            const emailInput = jQuery('#lwmc_email_id').val();
            if (!emailInput) return;

            const cookieEmail = Cookies.get('lwmc_edo_mail_otp');
            const cookieExpireTime = Cookies.get('lwmc_edo_mail_send_after');
            if (!cookieEmail || !cookieExpireTime) return;

            const cookieExpireTimeInMs = parseInt(cookieExpireTime) * 1000;
            const remainingTime = cookieExpireTimeInMs - Date.now();

            if (emailInput === cookieEmail && remainingTime <= 0) {
                jQuery('#lwmc_edo_resend_otp')
                    .text('Sending code...')
                    .prop('disabled', true);
                await EdoFeature.sendOtp(emailInput);
                jQuery('#lwmc_edo_resend_otp').text('The code has been resent');
                setTimeout(() => {
                    jQuery('#lwmc_edo_resend_otp')
                        .text('Resend Code')
                        .prop('disabled', false);
                }, 10000);
            } else {
                const remainingSeconds = Math.max(
                    Math.round(remainingTime / 1000),
                    0
                );
                alert(
                    `Please try again after ${remainingSeconds} second${remainingSeconds !== 1 ? 's' : ''
                    }.`
                );
            }
        }

        static async sendOtp(email) {
            const url = EdoFeature.getAjaxUrl('send_edo_otp');
            const formData = { email };
            const service = new AjaxService();
            const options = new AjaxOptions(url, 'POST', formData);
            EdoCurrentRequest = await service.request(
                options,
                EdoFeature.sendOtpResponse,
                EdoFeature.sendOtpError,
                EdoFeature.completeCallback
            );
        }

        static sendOtpResponse(response) {
            // Handle OTP send response
        }

        static sendOtpError(err) {
            console.log('sendOtpError :>> ', err);
            alert('OTP request failed, please try again');
        }

        static verifyOtpResponse(response) {
            if (response.user_id) {
                [
                    'lwmc_edo_mail_otp',
                    'lwmc_show_edo_modal',
                    'lwmc_browser_fingerprint',
                ].forEach(Cookies.remove);
                EdoFeature.closeModal();
                window.location.reload();
                return;
            }

            const errorCode = response.data.data.code;
            const errorMsg =
                errorCode === 'otp-code-expired'
                    ? 'The OTP code has expired. Please request a new one.'
                    : errorCode === 'otp-code-mismatch'
                        ? 'The OTP code you entered does not match. Please try again.'
                        : 'Invalid OTP. Please try again.';
            alert(errorMsg);
            jQuery('.otp-digit').val('');
        }

        static verifyOtpError(error) {
            console.log('verifyOtpError :>> ', error);
            alert('OTP verification failed, please try again');
            jQuery('.otp-digit').val('');
        }

        static getAjaxUrl(action) {
            const domain = new URL(window.location.href).hostname;
            return `${domain}?wc-ajax=lwmc_${action}`;
        }

        static openDefaultLoginModal() {
            EdoFeature.closeModal();
            $(".react-responsive-modal-root").show();
        }

        static openModal() {
            const billing_email = jQuery('#billing_email').val().trim();
            // 			if (!Cookies.get('lwmc_organization_id') || billing_email === '') {
            if (billing_email === '') {
                return;
            }

            // close default login modal
            EdoFeature.closeLoginModal();

            if (Cookies.get('lwmc_user_modal_name')) {
                jQuery('#verifyEdoOtpForm .lwmc-user-full-name').text(Cookies.get('lwmc_user_modal_name'));
            }

            if (Cookies.get('lwmc_organization_id')) {

                $('#lwmc_edo_pass_row').hide();

                const userData = Cookies.get('lwmc_show_edo_modal');

                console.log('userData :>> ', userData);

                if (!userData) {
                    alert('Please enter a email address that belongs to corresponding organization.');
                    // clear the email input
                    jQuery('#billing_email').val('');
                    return;
                }

                const parsedUserData = JSON.parse(userData);
                console.log('parsedUserData :>> ', parsedUserData);

                // set the name to the modal
                jQuery('#verifyEdoOtpForm .lwmc-user-full-name').html(
                    parsedUserData.fullName
                );

                // set the firebase id
                jQuery('#verifyEdoOtpForm #lwmc_firebase_id').val(
                    parsedUserData.fUserId
                );
            }

            // Set the email to the modal
            jQuery('#verifyEdoOtpForm .lwmc-show-email').html(billing_email);
            jQuery('#verifyEdoOtpForm #lwmc_email_id').val(billing_email);

            // Show the modal
            jQuery('#edoOtpModal').css('display', 'block');
            jQuery('#modalOverlay').css('display', 'block');

            jQuery('#modalCloseBtn').on('click', function () {
                EdoFeature.closeModal();
            });
        }

        static closeModal() {
            const modal = jQuery('#edoOtpModal');
            const modalOverlay = jQuery('#modalOverlay');
            const modalOverlay2 = jQuery('.modaal-overlay');
            modal.css('display', 'none');
            modalOverlay.css('display', 'none');
            modalOverlay2.css('display', 'none');
        }

        static closeLoginModal() {
            // we added this because modal was not closing(due to element not found) on safari browser
            const interval = setInterval(() => {
                const closeButton = jQuery('.react-responsive-modal-closeButton');
                const modalRoot = jQuery('.react-responsive-modal-root');

                if (closeButton.length && modalRoot.length) {
                    // 	closeButton.trigger('click');
                    modalRoot.css('display', 'none');
                    clearInterval(interval); // Stop checking once elements are found
                }
            }, 1); // Check every 100ms

            setTimeout(() => clearInterval(interval), 5000); // Stop trying after 5 seconds
        }
    }

    jQuery(document).ready(() => {
        new EdoFeature();
        setTimeout(() => {
            if (jQuery(".cfw-have-acc-text").text().toLowerCase().includes("welcome")) {
                jQuery(".cfw-have-acc-text").show();
            }
        }, 100)

        // is proration enabled
        const subProratedDate = Cookies.get('lwmc_sub_proration_date');
        console.log('subProratedDate :>> ', subProratedDate);
        if (subProratedDate) {
            console.log('subProratedDate :>> ', subProratedDate);
            // jQuery('#sub-prorated-date').html(subProratedDate);
        }
    });
});

/**
 * Updates WooCommerce checkout shipping and billing fields with the fetched address.
 * 
 * @param {Object} address - The default shipping address object.
 */

function updateWooCommerceShippingFields(address) {
    if (!address) return; // Exit if no address is provided

    jQuery(document).ready(function ($) {
        console.log("Updating checkout fields with:", address);

        // Update WooCommerce SHIPPING fields
        $('#shipping_first_name').val(address.first_name || '').trigger('change');
        $('#shipping_last_name').val(address.last_name || '').trigger('change');
        $('#shipping_company').val(address.company || '').trigger('change');
        $('#shipping_address_1').val(address.address_1 || '').trigger('change');
        $('#shipping_address_2').val(address.address_2 || '').trigger('change');
        $('#shipping_city').val(address.city || '').trigger('change');
        $('#shipping_state').val(address.state || '').trigger('change');
        $('#shipping_postcode').val(address.postcode || '').trigger('change');
        $('#shipping_country').val(address.country || '').trigger('change');
        $('#shipping_phone').val(address.phone || '').trigger('change');

        // Ensure WooCommerce recognizes the changes
        $('body').trigger('update_checkout');
        $(document.body).trigger('wc_address_updated');
        $(document.body).trigger('updated_checkout');

        console.log("WooCommerce checkout fields updated.");
    });
}

jQuery(function ($) {
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    function injectRecurringPriceFromCookie() {
        const cookieValue = getCookie('user_preferred_recurring_price');
        const recurringPrice = parseFloat(cookieValue);

        if (!isNaN(recurringPrice)) {
            $('input[name="preferred_recurring_price"]').remove();

            const input = $('<input>', {
                type: 'hidden',
                name: 'preferred_recurring_price',
                value: recurringPrice
            });

            $('form.checkout').append(input);
            console.log('✅ Injected preferred_recurring_price from cookie:', recurringPrice);
        } else {
            console.log('⚠️ No valid recurring price found in cookie.');
        }
    }

    // Run on page load + on checkout update
    $(document).ready(injectRecurringPriceFromCookie);
    $(document.body).on('updated_checkout', injectRecurringPriceFromCookie);

    // Also inject right before submit (failsafe)
    $('form.checkout').on('submit', injectRecurringPriceFromCookie);
});
