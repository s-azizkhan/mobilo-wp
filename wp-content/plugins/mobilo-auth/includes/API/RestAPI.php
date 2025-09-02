<?php

namespace MobiloAuth\API;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST API Class
 * 
 * @since 1.0.0
 */
class RestAPI
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register REST routes
     */
    public function register_routes()
    {
        // Authentication endpoints
        register_rest_route('mobilo-auth/v1', '/login', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_login'),
            'permission_callback' => '__return_true',
            'args' => array(
                'email' => array(
                    'required' => true,
                    'type' => 'string',
                    'format' => 'email',
                    'sanitize_callback' => 'sanitize_email'
                ),
                'password' => array(
                    'required' => true,
                    'type' => 'string'
                ),
                'remember' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false
                )
            )
        ));

        register_rest_route('mobilo-auth/v1', '/register', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_register'),
            'permission_callback' => '__return_true',
            'args' => array(
                'email' => array(
                    'required' => true,
                    'type' => 'string',
                    'format' => 'email',
                    'sanitize_callback' => 'sanitize_email'
                ),
                'password' => array(
                    'required' => true,
                    'type' => 'string',
                    'minLength' => 6
                ),
                'display_name' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));

        register_rest_route('mobilo-auth/v1', '/logout', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_logout'),
            'permission_callback' => array($this, 'check_user_logged_in')
        ));

        register_rest_route('mobilo-auth/v1', '/reset-password', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_reset_password'),
            'permission_callback' => '__return_true',
            'args' => array(
                'email' => array(
                    'required' => true,
                    'type' => 'string',
                    'format' => 'email',
                    'sanitize_callback' => 'sanitize_email'
                )
            )
        ));

        // User management endpoints
        register_rest_route('mobilo-auth/v1', '/profile', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_profile'),
            'permission_callback' => array($this, 'check_user_logged_in')
        ));

        register_rest_route('mobilo-auth/v1', '/profile', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_profile'),
            'permission_callback' => array($this, 'check_user_logged_in'),
            'args' => array(
                'display_name' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'email' => array(
                    'required' => false,
                    'type' => 'string',
                    'format' => 'email',
                    'sanitize_callback' => 'sanitize_email'
                )
            )
        ));

        register_rest_route('mobilo-auth/v1', '/change-password', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_change_password'),
            'permission_callback' => array($this, 'check_user_logged_in'),
            'args' => array(
                'current_password' => array(
                    'required' => true,
                    'type' => 'string'
                ),
                'new_password' => array(
                    'required' => true,
                    'type' => 'string',
                    'minLength' => 6
                )
            )
        ));

        // Token management endpoints
        register_rest_route('mobilo-auth/v1', '/verify-token', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_verify_token'),
            'permission_callback' => '__return_true',
            'args' => array(
                'token' => array(
                    'required' => true,
                    'type' => 'string'
                )
            )
        ));

        register_rest_route('mobilo-auth/v1', '/refresh-token', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_refresh_token'),
            'permission_callback' => '__return_true',
            'args' => array(
                'refresh_token' => array(
                    'required' => true,
                    'type' => 'string'
                )
            )
        ));

        // Admin endpoints
        register_rest_route('mobilo-auth/v1', '/admin/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_admin_stats'),
            'permission_callback' => array($this, 'check_admin_permissions')
        ));

        register_rest_route('mobilo-auth/v1', '/admin/users', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_admin_users'),
            'permission_callback' => array($this, 'check_admin_permissions'),
            'args' => array(
                'per_page' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 20,
                    'minimum' => 1,
                    'maximum' => 100
                ),
                'page' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 1,
                    'minimum' => 1
                )
            )
        ));
    }

    /**
     * Handle login request
     */
    public function handle_login($request)
    {
        try {
            $email = $request->get_param('email');
            $password = $request->get_param('password');
            $remember = $request->get_param('remember');

            // Authenticate with Firebase
            $firebase_auth = new \MobiloAuth\Core\FirebaseAuth();
            $result = $firebase_auth->signInWithEmailAndPassword($email, $password);

            if (is_wp_error($result)) {
                return new \WP_Error('firebase_auth_failed', $result->get_error_message(), array('status' => 401));
            }

            // Get or create WordPress user
            $firebase_user = $firebase_auth->getUserByEmail($email);
            $user_manager = new \MobiloAuth\Core\UserManager();
            $wordpress_user = $user_manager->create_wordpress_user($firebase_user);

            if (!$wordpress_user) {
                return new \WP_Error('user_creation_failed', __('Failed to create WordPress user', 'mobilo-auth'), array('status' => 500));
            }

            // Log in the user
            wp_set_current_user($wordpress_user->ID, $wordpress_user->user_login);
            wp_set_auth_cookie($wordpress_user->ID, $remember);

            // Log successful authentication
            \MobiloAuth\Core\Database::log_firebase_action(array(
                'firebase_uid' => $firebase_user->uid,
                'wordpress_user_id' => $wordpress_user->ID,
                'action' => 'login',
                'status' => 'success',
                'details' => json_encode(array('method' => 'rest_api', 'remember' => $remember))
            ));

            return array(
                'success' => true,
                'message' => __('Login successful', 'mobilo-auth'),
                'user' => array(
                    'id' => $wordpress_user->ID,
                    'email' => $wordpress_user->user_email,
                    'display_name' => $wordpress_user->display_name,
                    'firebase_uid' => $firebase_user->uid
                )
            );

        } catch (Throwable $e) {
            $this->log_error('REST API login error: ' . $e->getMessage());
            return new \WP_Error('login_failed', __('Login failed', 'mobilo-auth'), array('status' => 500));
        }
    }

    /**
     * Handle registration request
     */
    public function handle_register($request)
    {
        try {
            $email = $request->get_param('email');
            $password = $request->get_param('password');
            $display_name = $request->get_param('display_name');

            // Check if user already exists
            if (get_user_by('email', $email)) {
                return new \WP_Error('user_exists', __('User already exists', 'mobilo-auth'), array('status' => 409));
            }

            // Create Firebase user
            $firebase_auth = new \MobiloAuth\Core\FirebaseAuth();
            $firebase_user = $firebase_auth->createUser($email, $password, array(
                'display_name' => $display_name
            ));

            if (!$firebase_user) {
                return new \WP_Error('firebase_creation_failed', __('Failed to create Firebase user', 'mobilo-auth'), array('status' => 500));
            }

            // Create WordPress user
            $user_manager = new \MobiloAuth\Core\UserManager();
            $wordpress_user = $user_manager->create_wordpress_user($firebase_user, $password);

            if (!$wordpress_user) {
                return new \WP_Error('wordpress_creation_failed', __('Failed to create WordPress user', 'mobilo-auth'), array('status' => 500));
            }

            // Log successful registration
            \MobiloAuth\Core\Database::log_firebase_action(array(
                'firebase_uid' => $firebase_user->uid,
                'wordpress_user_id' => $wordpress_user->ID,
                'action' => 'register',
                'status' => 'success',
                'details' => json_encode(array('method' => 'rest_api'))
            ));

            return array(
                'success' => true,
                'message' => __('Registration successful', 'mobilo-auth'),
                'user' => array(
                    'id' => $wordpress_user->ID,
                    'email' => $wordpress_user->user_email,
                    'display_name' => $wordpress_user->display_name,
                    'firebase_uid' => $firebase_user->uid
                )
            );

        } catch (Throwable $e) {
            $this->log_error('REST API registration error: ' . $e->getMessage());
            return new \WP_Error('registration_failed', __('Registration failed', 'mobilo-auth'), array('status' => 500));
        }
    }

    /**
     * Handle logout request
     */
    public function handle_logout($request)
    {
        try {
            $user_id = get_current_user_id();
            $firebase_uid = get_user_meta($user_id, 'firebase_uid', true);

            // Log logout action
            if ($firebase_uid) {
                \MobiloAuth\Core\Database::log_firebase_action(array(
                    'firebase_uid' => $firebase_uid,
                    'wordpress_user_id' => $user_id,
                    'action' => 'logout',
                    'status' => 'success',
                    'details' => json_encode(array('method' => 'rest_api'))
                ));
            }

            // Log out the user
            wp_logout();

            return array(
                'success' => true,
                'message' => __('Logout successful', 'mobilo-auth')
            );

        } catch (Throwable $e) {
            $this->log_error('REST API logout error: ' . $e->getMessage());
            return new \WP_Error('logout_failed', __('Logout failed', 'mobilo-auth'), array('status' => 500));
        }
    }

    /**
     * Handle password reset request
     */
    public function handle_reset_password($request)
    {
        try {
            $email = $request->get_param('email');

            // Send password reset email via Firebase
            $firebase_auth = new \MobiloAuth\Core\FirebaseAuth();
            $result = $firebase_auth->sendPasswordResetLink($email);

            if ($result) {
                return array(
                    'success' => true,
                    'message' => __('Password reset email sent successfully', 'mobilo-auth')
                );
            } else {
                return new \WP_Error('reset_failed', __('Failed to send password reset email', 'mobilo-auth'), array('status' => 500));
            }

        } catch (Throwable $e) {
            $this->log_error('REST API password reset error: ' . $e->getMessage());
            return new \WP_Error('reset_failed', __('Password reset failed', 'mobilo-auth'), array('status' => 500));
        }
    }

    /**
     * Get user profile
     */
    public function get_profile($request)
    {
        try {
            $user = wp_get_current_user();
            
            if (!$user) {
                return new \WP_Error('user_not_found', __('User not found', 'mobilo-auth'), array('status' => 404));
            }

            $firebase_uid = get_user_meta($user->ID, 'firebase_uid', true);

            return array(
                'success' => true,
                'user' => array(
                    'id' => $user->ID,
                    'email' => $user->user_email,
                    'display_name' => $user->display_name,
                    'firebase_uid' => $firebase_uid,
                    'registered' => $user->user_registered
                )
            );

        } catch (Throwable $e) {
            $this->log_error('REST API get profile error: ' . $e->getMessage());
            return new \WP_Error('profile_failed', __('Failed to get profile', 'mobilo-auth'), array('status' => 500));
        }
    }

    /**
     * Update user profile
     */
    public function update_profile($request)
    {
        try {
            $user = wp_get_current_user();
            $firebase_uid = get_user_meta($user->ID, 'firebase_uid', true);

            if (!$firebase_uid) {
                return new \WP_Error('firebase_not_connected', __('User not connected to Firebase', 'mobilo-auth'), array('status' => 400));
            }

            $display_name = $request->get_param('display_name');
            $email = $request->get_param('email');

            // Update WordPress user
            $user_data = array('ID' => $user->ID);
            if ($display_name) {
                $user_data['display_name'] = $display_name;
            }
            if ($email) {
                $user_data['user_email'] = $email;
            }

            $result = wp_update_user($user_data);

            if (is_wp_error($result)) {
                return $result;
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

            return array(
                'success' => true,
                'message' => __('Profile updated successfully', 'mobilo-auth')
            );

        } catch (Throwable $e) {
            $this->log_error('REST API update profile error: ' . $e->getMessage());
            return new \WP_Error('update_failed', __('Profile update failed', 'mobilo-auth'), array('status' => 500));
        }
    }

    /**
     * Handle password change request
     */
    public function handle_change_password($request)
    {
        try {
            $user = wp_get_current_user();
            $firebase_uid = get_user_meta($user->ID, 'firebase_uid', true);
            $current_password = $request->get_param('current_password');
            $new_password = $request->get_param('new_password');

            if (!$firebase_uid) {
                return new \WP_Error('firebase_not_connected', __('User not connected to Firebase', 'mobilo-auth'), array('status' => 400));
            }

            // Verify current password
            $firebase_auth = new \MobiloAuth\Core\FirebaseAuth();
            $verify_result = $firebase_auth->signInWithEmailAndPassword($user->user_email, $current_password);

            if (is_wp_error($verify_result)) {
                return new \WP_Error('invalid_password', __('Current password is incorrect', 'mobilo-auth'), array('status' => 401));
            }

            // Change Firebase password
            $result = $firebase_auth->changeUserPassword($firebase_uid, $new_password);

            if ($result) {
                return array(
                    'success' => true,
                    'message' => __('Password changed successfully', 'mobilo-auth')
                );
            } else {
                return new \WP_Error('change_failed', __('Failed to change password', 'mobilo-auth'), array('status' => 500));
            }

        } catch (Throwable $e) {
            $this->log_error('REST API change password error: ' . $e->getMessage());
            return new \WP_Error('change_failed', __('Password change failed', 'mobilo-auth'), array('status' => 500));
        }
    }

    /**
     * Handle token verification request
     */
    public function handle_verify_token($request)
    {
        try {
            $token = $request->get_param('token');

            // Verify Firebase token
            $firebase_auth = new \MobiloAuth\Core\FirebaseAuth();
            $result = $firebase_auth->verifyIdToken($token);

            if (is_wp_error($result)) {
                return $result;
            }

            return array(
                'success' => true,
                'message' => __('Token verified successfully', 'mobilo-auth'),
                'token_data' => $result
            );

        } catch (Throwable $e) {
            $this->log_error('REST API token verification error: ' . $e->getMessage());
            return new \WP_Error('verification_failed', __('Token verification failed', 'mobilo-auth'), array('status' => 500));
        }
    }

    /**
     * Handle token refresh request
     */
    public function handle_refresh_token($request)
    {
        try {
            $refresh_token = $request->get_param('refresh_token');

            // Refresh Firebase token
            $firebase_auth = new \MobiloAuth\Core\FirebaseAuth();
            $result = $firebase_auth->refreshToken($refresh_token);

            if (is_wp_error($result)) {
                return $result;
            }

            return array(
                'success' => true,
                'message' => __('Token refreshed successfully', 'mobilo-auth'),
                'token_data' => $result
            );

        } catch (Throwable $e) {
            $this->log_error('REST API token refresh error: ' . $e->getMessage());
            return new \WP_Error('refresh_failed', __('Token refresh failed', 'mobilo-auth'), array('status' => 500));
        }
    }

    /**
     * Get admin statistics
     */
    public function get_admin_stats($request)
    {
        try {
            $stats = \MobiloAuth\Core\Database::get_auth_statistics();
            
            return array(
                'success' => true,
                'stats' => $stats
            );

        } catch (Throwable $e) {
            $this->log_error('REST API admin stats error: ' . $e->getMessage());
            return new \WP_Error('stats_failed', __('Failed to get statistics', 'mobilo-auth'), array('status' => 500));
        }
    }

    /**
     * Get admin users
     */
    public function get_admin_users($request)
    {
        try {
            global $wpdb;
            
            $per_page = $request->get_param('per_page');
            $page = $request->get_param('page');
            $offset = ($page - 1) * $per_page;

            $table = $wpdb->prefix . 'mobilo_auth_firebase_users';
            $users = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table WHERE is_active = 1 ORDER BY created_at DESC LIMIT %d OFFSET %d",
                    $per_page,
                    $offset
                )
            );

            $total = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE is_active = 1");

            return array(
                'success' => true,
                'users' => $users,
                'pagination' => array(
                    'total' => (int) $total,
                    'per_page' => $per_page,
                    'page' => $page,
                    'pages' => ceil($total / $per_page)
                )
            );

        } catch (Throwable $e) {
            $this->log_error('REST API admin users error: ' . $e->getMessage());
            return new \WP_Error('users_failed', __('Failed to get users', 'mobilo-auth'), array('status' => 500));
        }
    }

    /**
     * Check if user is logged in
     */
    public function check_user_logged_in()
    {
        return is_user_logged_in();
    }

    /**
     * Check admin permissions
     */
    public function check_admin_permissions()
    {
        return current_user_can('manage_options');
    }

    /**
     * Log error message
     */
    private function log_error($message)
    {
        if (function_exists('mobilo_log')) {
            mobilo_log(__METHOD__, $message, 'error');
        } else {
            error_log("Mobilo Auth REST API: $message");
        }
    }
}
