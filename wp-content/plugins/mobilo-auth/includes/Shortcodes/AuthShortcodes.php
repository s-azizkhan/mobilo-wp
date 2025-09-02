<?php

namespace MobiloAuth\Shortcodes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Authentication Shortcodes Class
 * 
 * @since 1.0.0
 */
class AuthShortcodes
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_shortcode('mobilo_auth_login', array($this, 'render_login_form'));
        add_shortcode('mobilo_auth_register', array($this, 'render_register_form'));
        add_shortcode('mobilo_auth_profile', array($this, 'render_profile_form'));
        add_shortcode('mobilo_auth_reset_password', array($this, 'render_reset_password_form'));
        add_shortcode('mobilo_auth_status', array($this, 'render_auth_status'));
        
        // Enqueue frontend scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_frontend_scripts()
    {
        wp_enqueue_style(
            'mobilo-auth-frontend',
            MOBILO_AUTH_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            MOBILO_AUTH_VERSION
        );

        wp_enqueue_script(
            'mobilo-auth-frontend',
            MOBILO_AUTH_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            MOBILO_AUTH_VERSION,
            true
        );

        wp_localize_script('mobilo-auth-frontend', 'mobiloAuthFrontend', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mobilo_auth_nonce'),
            'strings' => array(
                'loading' => __('Loading...', 'mobilo-auth'),
                'error' => __('An error occurred. Please try again.', 'mobilo-auth'),
                'success' => __('Operation completed successfully!', 'mobilo-auth')
            )
        ));
    }

    /**
     * Render login form shortcode
     */
    public function render_login_form($atts)
    {
        $atts = shortcode_atts(array(
            'redirect' => '',
            'show_register_link' => 'true',
            'show_forgot_password' => 'true'
        ), $atts);

        if (is_user_logged_in()) {
            return $this->render_already_logged_in_message();
        }

        ob_start();
        ?>
        <div class="mobilo-auth-form mobilo-auth-login-form">
            <h3><?php _e('Login', 'mobilo-auth'); ?></h3>
            
            <form id="mobilo-auth-login-form" method="post">
                <div class="form-group">
                    <label for="login_email"><?php _e('Email', 'mobilo-auth'); ?></label>
                    <input type="email" id="login_email" name="email" required />
                </div>
                
                <div class="form-group">
                    <label for="login_password"><?php _e('Password', 'mobilo-auth'); ?></label>
                    <input type="password" id="login_password" name="password" required />
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="remember" value="1" />
                        <?php _e('Remember me', 'mobilo-auth'); ?>
                    </label>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="mobilo-auth-submit">
                        <?php _e('Login', 'mobilo-auth'); ?>
                    </button>
                </div>
                
                <div class="mobilo-auth-messages"></div>
                
                <?php if ($atts['show_forgot_password'] === 'true'): ?>
                    <div class="form-group">
                        <a href="#" class="mobilo-auth-forgot-password">
                            <?php _e('Forgot your password?', 'mobilo-auth'); ?>
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if ($atts['show_register_link'] === 'true'): ?>
                    <div class="form-group">
                        <p>
                            <?php _e("Don't have an account?", 'mobilo-auth'); ?>
                            <a href="#" class="mobilo-auth-show-register">
                                <?php _e('Register here', 'mobilo-auth'); ?>
                            </a>
                        </p>
                    </div>
                <?php endif; ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render register form shortcode
     */
    public function render_register_form($atts)
    {
        $atts = shortcode_atts(array(
            'redirect' => '',
            'show_login_link' => 'true'
        ), $atts);

        if (is_user_logged_in()) {
            return $this->render_already_logged_in_message();
        }

        ob_start();
        ?>
        <div class="mobilo-auth-form mobilo-auth-register-form">
            <h3><?php _e('Register', 'mobilo-auth'); ?></h3>
            
            <form id="mobilo-auth-register-form" method="post">
                <div class="form-group">
                    <label for="register_display_name"><?php _e('Display Name', 'mobilo-auth'); ?></label>
                    <input type="text" id="register_display_name" name="display_name" required />
                </div>
                
                <div class="form-group">
                    <label for="register_email"><?php _e('Email', 'mobilo-auth'); ?></label>
                    <input type="email" id="register_email" name="email" required />
                </div>
                
                <div class="form-group">
                    <label for="register_password"><?php _e('Password', 'mobilo-auth'); ?></label>
                    <input type="password" id="register_password" name="password" required minlength="6" />
                </div>
                
                <div class="form-group">
                    <label for="register_confirm_password"><?php _e('Confirm Password', 'mobilo-auth'); ?></label>
                    <input type="password" id="register_confirm_password" name="confirm_password" required minlength="6" />
                </div>
                
                <div class="form-group">
                    <button type="submit" class="mobilo-auth-submit">
                        <?php _e('Register', 'mobilo-auth'); ?>
                    </button>
                </div>
                
                <div class="mobilo-auth-messages"></div>
                
                <?php if ($atts['show_login_link'] === 'true'): ?>
                    <div class="form-group">
                        <p>
                            <?php _e('Already have an account?', 'mobilo-auth'); ?>
                            <a href="#" class="mobilo-auth-show-login">
                                <?php _e('Login here', 'mobilo-auth'); ?>
                            </a>
                        </p>
                    </div>
                <?php endif; ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render profile form shortcode
     */
    public function render_profile_form($atts)
    {
        if (!is_user_logged_in()) {
            return $this->render_login_required_message();
        }

        $user = wp_get_current_user();
        $firebase_uid = get_user_meta($user->ID, 'firebase_uid', true);

        if (!$firebase_uid) {
            return $this->render_firebase_not_connected_message();
        }

        ob_start();
        ?>
        <div class="mobilo-auth-form mobilo-auth-profile-form">
            <h3><?php _e('Profile', 'mobilo-auth'); ?></h3>
            
            <form id="mobilo-auth-profile-form" method="post">
                <div class="form-group">
                    <label for="profile_display_name"><?php _e('Display Name', 'mobilo-auth'); ?></label>
                    <input type="text" id="profile_display_name" name="display_name" value="<?php echo esc_attr($user->display_name); ?>" required />
                </div>
                
                <div class="form-group">
                    <label for="profile_email"><?php _e('Email', 'mobilo-auth'); ?></label>
                    <input type="email" id="profile_email" name="email" value="<?php echo esc_attr($user->user_email); ?>" required />
                </div>
                
                <div class="form-group">
                    <button type="submit" class="mobilo-auth-submit">
                        <?php _e('Update Profile', 'mobilo-auth'); ?>
                    </button>
                </div>
                
                <div class="mobilo-auth-messages"></div>
            </form>
            
            <hr />
            
            <h4><?php _e('Change Password', 'mobilo-auth'); ?></h4>
            <form id="mobilo-auth-change-password-form" method="post">
                <div class="form-group">
                    <label for="current_password"><?php _e('Current Password', 'mobilo-auth'); ?></label>
                    <input type="password" id="current_password" name="current_password" required />
                </div>
                
                <div class="form-group">
                    <label for="new_password"><?php _e('New Password', 'mobilo-auth'); ?></label>
                    <input type="password" id="new_password" name="new_password" required minlength="6" />
                </div>
                
                <div class="form-group">
                    <label for="confirm_new_password"><?php _e('Confirm New Password', 'mobilo-auth'); ?></label>
                    <input type="password" id="confirm_new_password" name="confirm_new_password" required minlength="6" />
                </div>
                
                <div class="form-group">
                    <button type="submit" class="mobilo-auth-submit">
                        <?php _e('Change Password', 'mobilo-auth'); ?>
                    </button>
                </div>
                
                <div class="mobilo-auth-messages"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render reset password form shortcode
     */
    public function render_reset_password_form($atts)
    {
        if (is_user_logged_in()) {
            return $this->render_already_logged_in_message();
        }

        ob_start();
        ?>
        <div class="mobilo-auth-form mobilo-auth-reset-password-form">
            <h3><?php _e('Reset Password', 'mobilo-auth'); ?></h3>
            
            <form id="mobilo-auth-reset-password-form" method="post">
                <div class="form-group">
                    <label for="reset_email"><?php _e('Email', 'mobilo-auth'); ?></label>
                    <input type="email" id="reset_email" name="email" required />
                </div>
                
                <div class="form-group">
                    <button type="submit" class="mobilo-auth-submit">
                        <?php _e('Send Reset Link', 'mobilo-auth'); ?>
                    </button>
                </div>
                
                <div class="mobilo-auth-messages"></div>
                
                <div class="form-group">
                    <p>
                        <a href="#" class="mobilo-auth-show-login">
                            <?php _e('Back to Login', 'mobilo-auth'); ?>
                        </a>
                    </p>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render authentication status shortcode
     */
    public function render_auth_status($atts)
    {
        $atts = shortcode_atts(array(
            'show_logout' => 'true'
        ), $atts);

        if (!is_user_logged_in()) {
            return $this->render_not_logged_in_message();
        }

        $user = wp_get_current_user();
        $firebase_uid = get_user_meta($user->ID, 'firebase_uid', true);

        ob_start();
        ?>
        <div class="mobilo-auth-status">
            <h4><?php _e('Welcome', 'mobilo-auth'); ?></h4>
            
            <div class="user-info">
                <p><strong><?php _e('Name:', 'mobilo-auth'); ?></strong> <?php echo esc_html($user->display_name); ?></p>
                <p><strong><?php _e('Email:', 'mobilo-auth'); ?></strong> <?php echo esc_html($user->user_email); ?></p>
                <?php if ($firebase_uid): ?>
                    <p><strong><?php _e('Firebase UID:', 'mobilo-auth'); ?></strong> <?php echo esc_html($firebase_uid); ?></p>
                <?php endif; ?>
            </div>
            
            <?php if ($atts['show_logout'] === 'true'): ?>
                <div class="user-actions">
                    <button type="button" class="mobilo-auth-logout-btn">
                        <?php _e('Logout', 'mobilo-auth'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render already logged in message
     */
    private function render_already_logged_in_message()
    {
        $user = wp_get_current_user();
        ob_start();
        ?>
        <div class="mobilo-auth-message mobilo-auth-info">
            <p>
                <?php printf(__('You are already logged in as %s.', 'mobilo-auth'), esc_html($user->display_name)); ?>
                <a href="<?php echo wp_logout_url(); ?>"><?php _e('Logout', 'mobilo-auth'); ?></a>
            </p>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render login required message
     */
    private function render_login_required_message()
    {
        ob_start();
        ?>
        <div class="mobilo-auth-message mobilo-auth-error">
            <p><?php _e('You must be logged in to view this content.', 'mobilo-auth'); ?></p>
            <p><a href="#" class="mobilo-auth-show-login"><?php _e('Login here', 'mobilo-auth'); ?></a></p>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render not logged in message
     */
    private function render_not_logged_in_message()
    {
        ob_start();
        ?>
        <div class="mobilo-auth-message mobilo-auth-info">
            <p><?php _e('You are not currently logged in.', 'mobilo-auth'); ?></p>
            <p><a href="#" class="mobilo-auth-show-login"><?php _e('Login here', 'mobilo-auth'); ?></a></p>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Firebase not connected message
     */
    private function render_firebase_not_connected_message()
    {
        ob_start();
        ?>
        <div class="mobilo-auth-message mobilo-auth-error">
            <p><?php _e('Your account is not connected to Firebase. Please contact an administrator.', 'mobilo-auth'); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get form validation rules
     */
    public static function get_validation_rules()
    {
        return array(
            'email' => array(
                'required' => true,
                'type' => 'email',
                'message' => __('Please enter a valid email address.', 'mobilo-auth')
            ),
            'password' => array(
                'required' => true,
                'minlength' => 6,
                'message' => __('Password must be at least 6 characters long.', 'mobilo-auth')
            ),
            'display_name' => array(
                'required' => true,
                'minlength' => 2,
                'message' => __('Display name must be at least 2 characters long.', 'mobilo-auth')
            )
        );
    }

    /**
     * Validate form data
     */
    public static function validate_form_data($data, $rules)
    {
        $errors = array();

        foreach ($rules as $field => $rule) {
            $value = isset($data[$field]) ? $data[$field] : '';

            if ($rule['required'] && empty($value)) {
                $errors[$field] = $rule['message'];
                continue;
            }

            if (!empty($value)) {
                switch ($rule['type']) {
                    case 'email':
                        if (!is_email($value)) {
                            $errors[$field] = $rule['message'];
                        }
                        break;
                }

                if (isset($rule['minlength']) && strlen($value) < $rule['minlength']) {
                    $errors[$field] = $rule['message'];
                }
            }
        }

        return $errors;
    }

    /**
     * Sanitize form data
     */
    public static function sanitize_form_data($data)
    {
        $sanitized = array();

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'email':
                    $sanitized[$key] = sanitize_email($value);
                    break;
                case 'display_name':
                    $sanitized[$key] = sanitize_text_field($value);
                    break;
                case 'password':
                case 'current_password':
                case 'new_password':
                case 'confirm_password':
                    $sanitized[$key] = $value; // Don't sanitize passwords
                    break;
                default:
                    $sanitized[$key] = sanitize_text_field($value);
                    break;
            }
        }

        return $sanitized;
    }
}
