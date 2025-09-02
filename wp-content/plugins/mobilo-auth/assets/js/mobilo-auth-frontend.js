/**
 * Mobilo Auth Frontend JavaScript
 *
 * @package MobiloAuth
 * @since 1.0.0
 */

(function ($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function () {
        MobiloAuthFrontend.init();
    });

    // Main plugin object
    var MobiloAuthFrontend = {

        // Initialize the plugin
        init: function () {
            this.bindEvents();
            this.setupForms();
        },

        // Bind event handlers
        bindEvents: function () {
            $(document).on('submit', '.mobilo-auth-form', this.handleFormSubmit);
            $(document).on('click', '.mobilo-auth-toggle', this.toggleForm);
            $(document).on('click', '.mobilo-auth-logout', this.handleLogout);
            $(document).on('click', '.mobilo-auth-reset-password', this.handleResetPassword);
        },

        // Setup form validation and styling
        setupForms: function () {
            $('.mobilo-auth-form input[type="email"]').on('blur', this.validateEmail);
            $('.mobilo-auth-form input[type="password"]').on('blur', this.validatePassword);
        },

        // Handle form submission
        handleFormSubmit: function (e) {
            e.preventDefault();

            var $form = $(this);
            var action = $form.data('action');
            var $submitBtn = $form.find('button[type="submit"]');
            var originalText = $submitBtn.text();

            // Show loading state
            $submitBtn.prop('disabled', true).text('Loading...');

            // Clear previous errors
            $form.find('.mobilo-auth-error').remove();

            // Validate form
            if (!MobiloAuthFrontend.validateForm($form)) {
                $submitBtn.prop('disabled', false).text(originalText);
                return;
            }

            // Prepare form data
            var formData = {
                action: action,
                nonce: mobiloAuthFrontend.nonce
            };

            $form.find('input, select, textarea').each(function () {
                var $input = $(this);
                var name = $input.attr('name');
                var value = $input.val();

                if (name && value) {
                    formData[name] = value;
                }
            });

            // Send AJAX request
            $.post(mobiloAuthFrontend.ajaxUrl, formData)
                .done(function (response) {
                    MobiloAuthFrontend.handleResponse(response, $form);
                })
                .fail(function (xhr, status, error) {
                    MobiloAuthFrontend.handleError('Request failed: ' + error, $form);
                })
                .always(function () {
                    $submitBtn.prop('disabled', false).text(originalText);
                });
        },

        // Validate form fields
        validateForm: function ($form) {
            var isValid = true;

            $form.find('input[required], select[required], textarea[required]').each(function () {
                var $field = $(this);
                var value = $field.val().trim();

                if (!value) {
                    MobiloAuthFrontend.showFieldError($field, 'This field is required.');
                    isValid = false;
                }
            });

            return isValid;
        },

        // Validate email field
        validateEmail: function () {
            var $field = $(this);
            var email = $field.val().trim();
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email && !emailRegex.test(email)) {
                MobiloAuthFrontend.showFieldError($field, 'Please enter a valid email address.');
                return false;
            } else {
                MobiloAuthFrontend.clearFieldError($field);
                return true;
            }
        },

        // Validate password field
        validatePassword: function () {
            var $field = $(this);
            var password = $field.val();

            if (password && password.length < 6) {
                MobiloAuthFrontend.showFieldError($field, 'Password must be at least 6 characters long.');
                return false;
            } else {
                MobiloAuthFrontend.clearFieldError($field);
                return true;
            }
        },

        // Show field error
        showFieldError: function ($field, message) {
            MobiloAuthFrontend.clearFieldError($field);

            var $error = $('<div class="mobilo-auth-error">' + message + '</div>');
            $field.after($error);
            $field.addClass('mobilo-auth-error-field');
        },

        // Clear field error
        clearFieldError: function ($field) {
            $field.siblings('.mobilo-auth-error').remove();
            $field.removeClass('mobilo-auth-error-field');
        },

        // Handle AJAX response
        handleResponse: function (response, $form) {
            if (response.success) {
                MobiloAuthFrontend.handleSuccess(response.data, $form);
            } else {
                MobiloAuthFrontend.handleError(response.data.message || 'An error occurred.', $form);
            }
        },

        // Handle successful response
        handleSuccess: function (data, $form) {
            var message = data.message || 'Operation completed successfully.';

            // Show success message
            MobiloAuthFrontend.showMessage($form, message, 'success');

            // Handle redirect
            if (data.redirect) {
                setTimeout(function () {
                    window.location.href = data.redirect;
                }, 1500);
            }

            // Handle form reset
            if (data.reset_form) {
                $form[0].reset();
            }

            // Handle page reload
            if (data.reload_page) {
                setTimeout(function () {
                    window.location.reload();
                }, 1500);
            }
        },

        // Handle error response
        handleError: function (message, $form) {
            MobiloAuthFrontend.showMessage($form, message, 'error');
        },

        // Show message
        showMessage: function ($form, message, type) {
            var $message = $('<div class="mobilo-auth-message mobilo-auth-message-' + type + '">' + message + '</div>');

            $form.find('.mobilo-auth-message').remove();
            $form.prepend($message);

            // Auto-hide success messages
            if (type === 'success') {
                setTimeout(function () {
                    $message.fadeOut();
                }, 5000);
            }
        },

        // Toggle between forms
        toggleForm: function (e) {
            e.preventDefault();

            var $link = $(this);
            var targetForm = $link.data('target');
            var $currentForm = $link.closest('.mobilo-auth-form-container');
            var $targetForm = $('#' + targetForm);

            if ($targetForm.length) {
                $currentForm.hide();
                $targetForm.show();
            }
        },

        // Handle logout
        handleLogout: function (e) {
            e.preventDefault();

            if (confirm('Are you sure you want to logout?')) {
                $.post(mobiloAuthFrontend.ajaxUrl, {
                    action: 'mobilo_auth_logout',
                    nonce: mobiloAuthFrontend.nonce
                })
                    .done(function (response) {
                        if (response.success) {
                            window.location.reload();
                        } else {
                            alert('Logout failed: ' + (response.data.message || 'Unknown error'));
                        }
                    })
                    .fail(function () {
                        alert('Logout request failed. Please try again.');
                    });
            }
        },

        // Handle password reset
        handleResetPassword: function (e) {
            e.preventDefault();

            var email = prompt('Please enter your email address:');
            if (email) {
                $.post(mobiloAuthFrontend.ajaxUrl, {
                    action: 'mobilo_auth_reset_password',
                    nonce: mobiloAuthFrontend.nonce,
                    email: email
                })
                    .done(function (response) {
                        if (response.success) {
                            alert('Password reset email sent. Please check your inbox.');
                        } else {
                            alert('Password reset failed: ' + (response.data.message || 'Unknown error'));
                        }
                    })
                    .fail(function () {
                        alert('Password reset request failed. Please try again.');
                    });
            }
        }
    };

})(jQuery);
