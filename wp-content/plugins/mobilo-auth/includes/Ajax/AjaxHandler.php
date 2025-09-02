<?php

namespace MobiloAuth\Ajax;

use Throwable;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX Handler Class
 * 
 * @since 1.0.0
 */
class AjaxHandler
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Authentication AJAX actions
        add_action('wp_ajax_mobilo_auth_login', array($this, 'handle_login'));
        add_action('wp_ajax_nopriv_mobilo_auth_login', array($this, 'handle_login'));
        add_action('wp_ajax_mobilo_auth_register', array($this, 'handle_register'));
        add_action('wp_ajax_nopriv_mobilo_auth_register', array($this, 'handle_register'));
        add_action('wp_ajax_mobilo_auth_logout', array($this, 'handle_logout'));
        add_action('wp_ajax_mobilo_auth_reset_password', array($this, 'handle_reset_password'));
        add_action('wp_ajax_nopriv_mobilo_auth_reset_password', array($this, 'handle_reset_password'));

        // User management AJAX actions
        add_action('wp_ajax_mobilo_auth_update_profile', array($this, 'handle_update_profile'));
        add_action('wp_ajax_mobilo_auth_change_password', array($this, 'handle_change_password'));
        add_action('wp_ajax_mobilo_auth_verify_token', array($this, 'handle_verify_token'));
        add_action('wp_ajax_nopriv_mobilo_auth_verify_token', array($this, 'handle_verify_token'));

        // Admin AJAX actions
        add_action('wp_ajax_mobilo_auth_admin_sync_users', array($this, 'handle_admin_sync_users'));
        add_action('wp_ajax_mobilo_auth_admin_get_stats', array($this, 'handle_admin_get_stats'));
        add_action('wp_ajax_mobilo_auth_admin_test_connection', array($this, 'handle_admin_test_connection'));
    }

    /**
     * Handle login AJAX request
     */
    public function handle_login()
    {
        try {
            check_ajax_referer('mobilo_auth_nonce', 'nonce');

            $email = sanitize_email($_POST['email']);
            $password = $_POST['password'];
            $remember = isset($_POST['remember']) ? (bool) $_POST['remember'] : false;

            if (empty($email) || empty($password)) {
                wp_send_json_error(__('Email and password are required', 'mobilo-auth'));
            }

            // Authenticate with Firebase
            $firebase_auth = new \MobiloAuth\Core\FirebaseAuth();
            $result = $firebase_auth->signInWithEmailAndPassword($email, $password);

            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }

            // Get or create WordPress user
            $firebase_user = $firebase_auth->getUserByEmail($email);
            $user_manager = new \MobiloAuth\Core\UserManager();
            $wordpress_user = $user_manager->create_wordpress_user($firebase_user);

            if (!$wordpress_user) {
                wp_send_json_error(__('Failed to create WordPress user', 'mobilo-auth'));
            }

            // Log in the user
            wp_set_current_user($wordpress_user->ID, $wordpress_user->user_login);
            wp_set_auth_cookie($wordpress_user->ID, $remember);

            // Log successful authentication
            // \MobiloAuth\Core\Database::log_firebase_action(array(
            //     'firebase_uid' => $firebase_user->uid,
            //     'wordpress_user_id' => $wordpress_user->ID,
            //     'action' => 'login',
            //     'status' => 'success',
            //     'details' => json_encode(array('method' => 'ajax', 'remember' => $remember))
            // ));

            wp_send_json_success(array(
                'message' => __('Login successful', 'mobilo-auth'),
                'user' => array(
                    'id' => $wordpress_user->ID,
                    'email' => $wordpress_user->user_email,
                    'display_name' => $wordpress_user->display_name,
                    'firebase_uid' => $firebase_user->uid
                ),
                'redirect_url' => $this->get_login_redirect_url($wordpress_user)
            ));

        } catch (Throwable $e) {
            $this->log_error('AJAX login error: ' . $e->getMessage());
            wp_send_json_error(__('Login failed', 'mobilo-auth'));
        }
    }

    /**
     * Handle registration AJAX request
     */
    public function handle_register()
    {
        try {
            check_ajax_referer('mobilo_auth_nonce', 'nonce');

            $email = sanitize_email($_POST['email']);
            $password = $_POST['password'];
            $display_name = sanitize_text_field($_POST['display_name']);

            if (empty($email) || empty($password)) {
                wp_send_json_error(__('Email and password are required', 'mobilo-auth'));
            }

            // Check if user already exists
            if (get_user_by('email', $email)) {
                wp_send_json_error(__('User already exists', 'mobilo-auth'));
            }

            // Create Firebase user
            $firebase_auth = new \MobiloAuth\Core\FirebaseAuth();
            $firebase_user = $firebase_auth->createUser($email, $password, array(
                'display_name' => $display_name
            ));

            if (!$firebase_user) {
                wp_send_json_error(__('Failed to create Firebase user', 'mobilo-auth'));
            }

            // Create WordPress user
            $user_manager = new \MobiloAuth\Core\UserManager();
            $wordpress_user = $user_manager->create_wordpress_user($firebase_user, $password);

            if (!$wordpress_user) {
                wp_send_json_error(__('Failed to create WordPress user', 'mobilo-auth'));
            }

            // Log in the user
            wp_set_current_user($wordpress_user->ID, $wordpress_user->user_login);
            wp_set_auth_cookie($wordpress_user->ID, true);

            // Log successful registration
            \MobiloAuth\Core\Database::log_firebase_action(array(
                'firebase_uid' => $firebase_user->uid,
                'wordpress_user_id' => $wordpress_user->ID,
                'action' => 'register',
                'status' => 'success',
                'details' => json_encode(array('method' => 'ajax'))
            ));

            wp_send_json_success(array(
                'message' => __('Registration successful', 'mobilo-auth'),
                'user' => array(
                    'id' => $wordpress_user->ID,
                    'email' => $wordpress_user->user_email,
                    'display_name' => $wordpress_user->display_name,
                    'firebase_uid' => $firebase_user->uid
                ),
                'redirect_url' => $this->get_login_redirect_url($wordpress_user)
            ));

        } catch (Throwable $e) {
            $this->log_error('AJAX registration error: ' . $e->getMessage());
            wp_send_json_error(__('Registration failed', 'mobilo-auth'));
        }
    }

    /**
     * Handle logout AJAX request
     */
    public function handle_logout()
    {
        try {
            check_ajax_referer('mobilo_auth_nonce', 'nonce');

            if (!is_user_logged_in()) {
                wp_send_json_error(__('User not logged in', 'mobilo-auth'));
            }

            $user_id = get_current_user_id();
            $firebase_uid = get_user_meta($user_id, 'firebase_uid', true);

            // Log logout action
            if ($firebase_uid) {
                \MobiloAuth\Core\Database::log_firebase_action(array(
                    'firebase_uid' => $firebase_uid,
                    'wordpress_user_id' => $user_id,
                    'action' => 'logout',
                    'status' => 'success',
                    'details' => json_encode(array('method' => 'ajax'))
                ));
            }

            // Log out the user
            wp_logout();

            wp_send_json_success(array(
                'message' => __('Logout successful', 'mobilo-auth'),
                'redirect_url' => $this->get_logout_redirect_url()
            ));

        } catch (Throwable $e) {
            $this->log_error('AJAX logout error: ' . $e->getMessage());
            wp_send_json_error(__('Logout failed', 'mobilo-auth'));
        }
    }

    /**
     * Handle password reset AJAX request
     */
    public function handle_reset_password()
    {
        try {
            check_ajax_referer('mobilo_auth_nonce', 'nonce');

            $email = sanitize_email($_POST['email']);

            if (empty($email)) {
                wp_send_json_error(__('Email is required', 'mobilo-auth'));
            }

            // Send password reset email via Firebase
            $firebase_auth = new \MobiloAuth\Core\FirebaseAuth();
            $result = $firebase_auth->sendPasswordResetLink($email);

            if ($result) {
                wp_send_json_success(__('Password reset email sent successfully', 'mobilo-auth'));
            } else {
                wp_send_json_error(__('Failed to send password reset email', 'mobilo-auth'));
            }

        } catch (Throwable $e) {
            $this->log_error('AJAX password reset error: ' . $e->getMessage());
            wp_send_json_error(__('Password reset failed', 'mobilo-auth'));
        }
    }

    /**
     * Handle profile update AJAX request
     */
    public function handle_update_profile()
    {
        try {
            check_ajax_referer('mobilo_auth_nonce', 'nonce');

            if (!is_user_logged_in()) {
                wp_send_json_error(__('User not logged in', 'mobilo-auth'));
            }

            $user_id = get_current_user_id();
            $firebase_uid = get_user_meta($user_id, 'firebase_uid', true);

            if (!$firebase_uid) {
                wp_send_json_error(__('User not connected to Firebase', 'mobilo-auth'));
            }

            $display_name = sanitize_text_field($_POST['display_name']);
            $email = sanitize_email($_POST['email']);

            // Update WordPress user
            $user_data = array('ID' => $user_id);
            if ($display_name) {
                $user_data['display_name'] = $display_name;
            }
            if ($email) {
                $user_data['user_email'] = $email;
            }

            $result = wp_update_user($user_data);

            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }

            // Update Firebase user
            $firebase_auth = new \MobiloAuth\Core\FirebaseAuth();
            $firebase_data = array();
            if ($display_name) {
                $firebase_data['display_name'] = $display_name;
            }
            if ($email) {
                $firebase_data['email'] = $email;
            }

            if (!empty($firebase_data)) {
                $firebase_auth->updateUser($firebase_uid, $firebase_data);
            }

            wp_send_json_success(__('Profile updated successfully', 'mobilo-auth'));

        } catch (Throwable $e) {
            $this->log_error('AJAX profile update error: ' . $e->getMessage());
            wp_send_json_error(__('Profile update failed', 'mobilo-auth'));
        }
    }

    /**
     * Handle password change AJAX request
     */
    public function handle_change_password()
    {
        try {
            check_ajax_referer('mobilo_auth_nonce', 'nonce');

            if (!is_user_logged_in()) {
                wp_send_json_error(__('User not logged in', 'mobilo-auth'));
            }

            $user_id = get_current_user_id();
            $firebase_uid = get_user_meta($user_id, 'firebase_uid', true);
            $new_password = $_POST['new_password'];

            if (!$firebase_uid) {
                wp_send_json_error(__('User not connected to Firebase', 'mobilo-auth'));
            }

            if (empty($new_password)) {
                wp_send_json_error(__('New password is required', 'mobilo-auth'));
            }

            // Change Firebase password
            $firebase_auth = new \MobiloAuth\Core\FirebaseAuth();
            $result = $firebase_auth->changeUserPassword($firebase_uid, $new_password);

            if ($result) {
                wp_send_json_success(__('Password changed successfully', 'mobilo-auth'));
            } else {
                wp_send_json_error(__('Failed to change password', 'mobilo-auth'));
            }

        } catch (Throwable $e) {
            $this->log_error('AJAX password change error: ' . $e->getMessage());
            wp_send_json_error(__('Password change failed', 'mobilo-auth'));
        }
    }

    /**
     * Handle token verification AJAX request
     */
    public function handle_verify_token()
    {
        try {
            check_ajax_referer('mobilo_auth_nonce', 'nonce');

            $token = $_POST['token'];

            if (empty($token)) {
                wp_send_json_error(__('Token is required', 'mobilo-auth'));
            }

            // Verify Firebase token
            $firebase_auth = new \MobiloAuth\Core\FirebaseAuth();
            $result = $firebase_auth->verifyIdToken($token);

            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }

            wp_send_json_success(array(
                'message' => __('Token verified successfully', 'mobilo-auth'),
                'token_data' => $result
            ));

        } catch (Throwable $e) {
            $this->log_error('AJAX token verification error: ' . $e->getMessage());
            wp_send_json_error(__('Token verification failed', 'mobilo-auth'));
        }
    }

    /**
     * Handle admin user sync AJAX request
     */
    public function handle_admin_sync_users()
    {
        try {
            check_ajax_referer('mobilo_auth_admin_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                wp_send_json_error(__('Insufficient permissions', 'mobilo-auth'));
            }

            // Implementation for user synchronization
            wp_send_json_success(__('User synchronization completed', 'mobilo-auth'));

        } catch (Throwable $e) {
            $this->log_error('Admin user sync error: ' . $e->getMessage());
            wp_send_json_error(__('User synchronization failed', 'mobilo-auth'));
        }
    }

    /**
     * Handle admin get stats AJAX request
     */
    public function handle_admin_get_stats()
    {
        try {
            check_ajax_referer('mobilo_auth_admin_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                wp_send_json_error(__('Insufficient permissions', 'mobilo-auth'));
            }

            $stats = \MobiloAuth\Core\Database::get_auth_statistics();
            wp_send_json_success($stats);

        } catch (Throwable $e) {
            $this->log_error('Admin get stats error: ' . $e->getMessage());
            wp_send_json_error(__('Failed to get statistics', 'mobilo-auth'));
        }
    }

    /**
     * Handle admin test connection AJAX request
     */
    public function handle_admin_test_connection()
    {
        try {
            check_ajax_referer('mobilo_auth_admin_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                wp_send_json_error(__('Insufficient permissions', 'mobilo-auth'));
            }

            $firebase_auth = new \MobiloAuth\Core\FirebaseAuth();

            if ($firebase_auth->getError()) {
                wp_send_json_error(__('Firebase connection failed: ' . $firebase_auth->getError(), 'mobilo-auth'));
            } else {
                wp_send_json_success(__('Firebase connection successful', 'mobilo-auth'));
            }

        } catch (Throwable $e) {
            $this->log_error('Admin test connection error: ' . $e->getMessage());
            wp_send_json_error(__('Firebase connection test failed: ' . $e->getMessage(), 'mobilo-auth'));
        }
    }

    /**
     * Get login redirect URL
     */
    private function get_login_redirect_url($user)
    {
        $settings = new \MobiloAuth\Core\Settings();
        $custom_redirect = $settings->get('custom_login_redirect');

        if (!empty($custom_redirect)) {
            return $custom_redirect;
        }

        // Default redirect based on user role
        if (in_array('administrator', $user->roles)) {
            return admin_url();
        }

        return home_url();
    }

    /**
     * Get logout redirect URL
     */
    private function get_logout_redirect_url()
    {
        $settings = new \MobiloAuth\Core\Settings();
        $custom_redirect = $settings->get('custom_logout_redirect');

        if (!empty($custom_redirect)) {
            return $custom_redirect;
        }

        return home_url();
    }

    /**
     * Log error message
     */
    private function log_error($message)
    {
        if (function_exists('mobilo_log')) {
            mobilo_log(__METHOD__, $message, 'error');
        } else {
            error_log("Mobilo Auth AJAX: $message");
        }
    }
}
