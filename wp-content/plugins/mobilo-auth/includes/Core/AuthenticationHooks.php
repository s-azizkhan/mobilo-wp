<?php

namespace MobiloAuth\Core;

use Throwable;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Authentication Hooks Class
 * 
 * @since 1.0.0
 */
class AuthenticationHooks
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Hook into WordPress authentication
        add_filter('authenticate', [$this, 'authenticate_user'], 999, 3);

        // Hook into login form
        // add_action('login_form', [$this, 'add_firebase_login_fields']);
        // add_action('login_form_register', [$this, 'add_firebase_register_fields']);

        // Handle custom authentication
        add_action('wp_ajax_mobilo_auth_login', [$this, 'handle_ajax_login']);
        add_action('wp_ajax_nopriv_mobilo_auth_login', [$this, 'handle_ajax_login']);
        add_action('wp_ajax_mobilo_auth_register', [$this, 'handle_ajax_register']);
        add_action('wp_ajax_nopriv_mobilo_auth_register', [$this, 'handle_ajax_register']);
        add_action('wp_ajax_mobilo_auth_logout', [$this, 'handle_ajax_logout']);

        // Add custom login/logout redirects
        add_filter('login_redirect', [$this, 'custom_login_redirect'], 10, 3);
        add_filter('logout_redirect', [$this, 'custom_logout_redirect'], 10, 3);

        // Add custom authentication endpoints
        add_action('init', [$this, 'add_rewrite_rules']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('template_redirect', [$this, 'handle_auth_endpoints']);
    }

    /**
     * Authenticate user with Firebase
     */
    public function authenticate_user($user, $username, $password)
    {
        // Check if Firebase auth is enabled
        $settings = new Settings();
        if (!$settings->is_firebase_auth_enabled()) {
            return $user;
        }

        // Skip if username is empty
        if (empty($username)) {
            return $user;
        }
        // get user email from username
        $user_email = filter_var($username, FILTER_VALIDATE_EMAIL) ? $username : get_user_by('login', $username)->user_email;

        try {
            // Try Firebase authentication
            $firebase_auth = new FirebaseAuth();
            $firebase_result = $firebase_auth->signInWithEmailAndPassword($user_email, $password);

            if (is_wp_error($firebase_result)) {
                // Firebase auth failed, return error
                return $firebase_result;
            }

            // Firebase auth successful, get or create WordPress user
            $firebase_user = $firebase_auth->getUserByEmail($user_email);
            if (!$firebase_user) {
                return new \WP_Error('firebase_user_not_found', __('Firebase user not found', 'mobilo-auth'));
            }

            // Get or create WordPress user
            $user_manager = new UserManager();
            $wordpress_user = $user_manager->create_wordpress_user($firebase_user, $password);

            if (!$wordpress_user) {
                return new \WP_Error('user_creation_failed', __('Failed to create WordPress user', 'mobilo-auth'));
            }

            // Log the successful authentication
            $this->log_auth_success($wordpress_user->ID, __METHOD__);

            return $wordpress_user;
        } catch (Throwable $e) {
            $this->log_error('Firebase authentication error: ' . $e->getMessage());
            return new \WP_Error('firebase_auth_error', __('Firebase authentication error', 'mobilo-auth'));
        }
    }

    /**
     * Add Firebase fields to login form
     */
    public function add_firebase_login_fields()
    {
        $settings = new Settings();
        if (!$settings->is_firebase_auth_enabled()) {
            return;
        }
?>
        <div id="mobilo-auth-firebase-login" style="display: none;">
            <p>
                <label for="firebase_token"><?php _e('Firebase Token', 'mobilo-auth'); ?></label>
                <input type="text" name="firebase_token" id="firebase_token" class="input" />
            </p>
            <p>
                <button type="button" id="mobilo-auth-login-btn" class="button button-primary">
                    <?php _e('Login with Firebase', 'mobilo-auth'); ?>
                </button>
            </p>
        </div>
        <script>
            jQuery(document).ready(function($) {
                // Show Firebase login option
                $('#mobilo-auth-firebase-login').show();

                // Handle Firebase login
                $('#mobilo-auth-login-btn').on('click', function() {
                    var token = $('#firebase_token').val();
                    if (token) {
                        // Submit Firebase token for authentication
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'firebase_auth_token',
                            value: token
                        }).appendTo('#loginform');
                    }
                });
            });
        </script>
    <?php
    }

    /**
     * Add Firebase fields to registration form
     */
    public function add_firebase_register_fields()
    {
        $settings = new Settings();
        if (!$settings->is_firebase_auth_enabled()) {
            return;
        }
    ?>
        <div id="mobilo-auth-firebase-register">
            <p>
                <label for="firebase_email"><?php _e('Email', 'mobilo-auth'); ?></label>
                <input type="email" name="firebase_email" id="firebase_email" class="input" required />
            </p>
            <p>
                <label for="firebase_password"><?php _e('Password', 'mobilo-auth'); ?></label>
                <input type="password" name="firebase_password" id="firebase_password" class="input" required />
            </p>
            <p>
                <label for="firebase_display_name"><?php _e('Display Name', 'mobilo-auth'); ?></label>
                <input type="text" name="firebase_display_name" id="firebase_display_name" class="input" />
            </p>
            <p>
                <button type="button" id="mobilo-auth-register-btn" class="button button-primary">
                    <?php _e('Register with Firebase', 'mobilo-auth'); ?>
                </button>
            </p>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $('#mobilo-auth-register-btn').on('click', function() {
                    var email = $('#firebase_email').val();
                    var password = $('#firebase_password').val();
                    var displayName = $('#firebase_display_name').val();

                    if (email && password) {
                        // Submit Firebase registration
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'firebase_register',
                            value: '1'
                        }).appendTo('#registerform');
                    }
                });
            });
        </script>
<?php
    }

    /**
     * Handle AJAX login
     */
    public function handle_ajax_login()
    {
        try {
            check_ajax_referer('mobilo_auth_nonce', 'nonce');

            $email = sanitize_email($_POST['email']);
            $password = $_POST['password'];

            if (empty($email) || empty($password)) {
                wp_send_json_error(__('Email and password are required', 'mobilo-auth'));
            }

            // Authenticate with Firebase
            $firebase_auth = new FirebaseAuth();
            $result = $firebase_auth->signInWithEmailAndPassword($email, $password);

            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }

            // Get or create WordPress user
            $firebase_user = $firebase_auth->getUserByEmail($email);
            $user_manager = new UserManager();
            $wordpress_user = $user_manager->create_wordpress_user($firebase_user);

            if (!$wordpress_user) {
                wp_send_json_error(__('Failed to create WordPress user', 'mobilo-auth'));
            }

            // Log in the user
            wp_set_current_user($wordpress_user->ID);
            wp_set_auth_cookie($wordpress_user->ID, true);

            // Log successful authentication
            $this->log_auth_success($wordpress_user->ID, 'firebase_ajax');

            wp_send_json_success([
                'message' => __('Login successful', 'mobilo-auth'),
                'redirect_url' => $this->get_login_redirect_url($wordpress_user)
            ]);
        } catch (Throwable $e) {
            $this->log_error('AJAX login error: ' . $e->getMessage());
            wp_send_json_error(__('Login failed', 'mobilo-auth'));
        }
    }

    /**
     * Handle AJAX registration
     */
    public function handle_ajax_register()
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
            $firebase_auth = new FirebaseAuth();
            $firebase_user = $firebase_auth->createUser($email, $password, [
                'display_name' => $display_name
            ]);

            if (!$firebase_user) {
                wp_send_json_error(__('Failed to create Firebase user', 'mobilo-auth'));
            }

            // Create WordPress user
            $user_manager = new UserManager();
            $wordpress_user = $user_manager->create_wordpress_user($firebase_user, $password);

            if (!$wordpress_user) {
                wp_send_json_error(__('Failed to create WordPress user', 'mobilo-auth'));
            }

            // Log in the user
            wp_set_current_user($wordpress_user->ID);
            wp_set_auth_cookie($wordpress_user->ID, true);

            // Log successful registration
            $this->log_auth_success($wordpress_user->ID, 'firebase_register');

            wp_send_json_success([
                'message' => __('Registration successful', 'mobilo-auth'),
                'redirect_url' => $this->get_login_redirect_url($wordpress_user)
            ]);
        } catch (Throwable $e) {
            $this->log_error('AJAX registration error: ' . $e->getMessage());
            wp_send_json_error(__('Registration failed', 'mobilo-auth'));
        }
    }

    /**
     * Handle AJAX logout
     */
    public function handle_ajax_logout()
    {
        try {
            check_ajax_referer('mobilo_auth_nonce', 'nonce');

            if (!is_user_logged_in()) {
                wp_send_json_error(__('User not logged in', 'mobilo-auth'));
            }

            // Log out the user
            wp_logout();

            wp_send_json_success([
                'message' => __('Logout successful', 'mobilo-auth'),
                'redirect_url' => $this->get_logout_redirect_url()
            ]);
        } catch (Throwable $e) {
            $this->log_error('AJAX logout error: ' . $e->getMessage());
            wp_send_json_error(__('Logout failed', 'mobilo-auth'));
        }
    }

    /**
     * Custom login redirect
     */
    public function custom_login_redirect($redirect_to, $requested_redirect_to, $user)
    {
        if (!$user || is_wp_error($user)) {
            return $redirect_to;
        }

        $settings = new Settings();
        $custom_redirect = $settings->get('custom_login_redirect');

        if (!empty($custom_redirect)) {
            return $custom_redirect;
        }

        return $redirect_to;
    }

    /**
     * Custom logout redirect
     */
    public function custom_logout_redirect($redirect_to, $requested_redirect_to, $user)
    {
        $settings = new Settings();
        $custom_redirect = $settings->get('custom_logout_redirect');

        if (!empty($custom_redirect)) {
            return $custom_redirect;
        }

        return $redirect_to;
    }

    /**
     * Add rewrite rules for authentication endpoints
     */
    public function add_rewrite_rules()
    {
        add_rewrite_rule(
            '^mobilo-auth/([^/]+)/?$',
            'index.php?mobilo_auth_action=$matches[1]',
            'top'
        );
    }

    /**
     * Add query variables
     */
    public function add_query_vars($vars)
    {
        $vars[] = 'mobilo_auth_action';
        return $vars;
    }

    /**
     * Handle authentication endpoints
     */
    public function handle_auth_endpoints()
    {
        $action = get_query_var('mobilo_auth_action');

        if (!$action) {
            return;
        }

        switch ($action) {
            case 'login':
                $this->handle_auth_endpoint_login();
                break;
            case 'register':
                $this->handle_auth_endpoint_register();
                break;
            case 'logout':
                $this->handle_auth_endpoint_logout();
                break;
            case 'verify':
                $this->handle_auth_endpoint_verify();
                break;
        }
    }

    /**
     * Handle login endpoint
     */
    private function handle_auth_endpoint_login()
    {
        // Handle login logic
        wp_die(__('Login endpoint', 'mobilo-auth'));
    }

    /**
     * Handle registration endpoint
     */
    private function handle_auth_endpoint_register()
    {
        // Handle registration logic
        wp_die(__('Registration endpoint', 'mobilo-auth'));
    }

    /**
     * Handle logout endpoint
     */
    private function handle_auth_endpoint_logout()
    {
        // Handle logout logic
        wp_die(__('Logout endpoint', 'mobilo-auth'));
    }

    /**
     * Handle verification endpoint
     */
    private function handle_auth_endpoint_verify()
    {
        // Handle token verification logic
        wp_die(__('Verification endpoint', 'mobilo-auth'));
    }

    /**
     * Get login redirect URL
     */
    private function get_login_redirect_url($user)
    {
        $settings = new Settings();
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
        $settings = new Settings();
        $custom_redirect = $settings->get('custom_logout_redirect');

        if (!empty($custom_redirect)) {
            return $custom_redirect;
        }

        return home_url();
    }

    /**
     * Log successful authentication
     */
    private function log_auth_success($user_id, $method)
    {
        $settings = new Settings();
        if (!$settings->is_debug_logging_enabled()) {
            return;
        }

        $user = get_user_by('id', $user_id);
        $log_message = sprintf(
            'Successful authentication: User ID %d (%s) via %s from IP %s',
            $user_id,
            $user ? $user->user_email : 'Unknown',
            $method,
            $this->get_client_ip()
        );

        $this->log_info($log_message);
    }

    /**
     * Get client IP address
     */
    private function get_client_ip()
    {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];

        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
    }

    /**
     * Log info message
     */
    private function log_info($message)
    {
        if (function_exists('mobilo_auth_log')) {
            mobilo_auth_log(__METHOD__, $message, 'info');
        } else {
            error_log("Mobilo Auth: $message");
        }
    }

    /**
     * Log error message
     */
    private function log_error($message)
    {
        if (function_exists('mobilo_auth_log')) {
            mobilo_auth_log(__METHOD__, $message, 'error');
        } else {
            error_log("Mobilo Auth Error: $message");
        }
    }
}
