<?php

namespace MobiloAuth\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin Settings Management
 * 
 * @since 1.0.0
 */
class Settings
{
    /**
     * Settings option name
     */
    const OPTION_NAME = 'mobilo_auth_settings';

    /**
     * Default settings
     */
    private $defaults = [
        'firebase_project_id' => '',
        'firebase_region' => 'us',
        'firebase_sdk_file' => '',
        'firebase_sdk_file_eu' => '',
        'firebase_api_key' => '',
        'firebase_auth_domain' => '',
        'enable_firebase_auth' => true,
        'enable_wordpress_auth' => true,
        'auto_create_users' => true,
        'sync_user_meta' => true,
        'enable_multi_region' => false,
        'default_region' => 'us',
        'enable_admin_notifications' => true,
        'enable_debug_logging' => false,
        'session_timeout' => 3600,
        'enable_remember_me' => true,
        'enable_password_reset' => true,
        'enable_email_verification' => false,
        'custom_login_redirect' => '',
        'custom_logout_redirect' => '',
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Get all settings
     */
    public function get_all()
    {
        $settings = get_option(self::OPTION_NAME, []);
        return wp_parse_args($settings, $this->defaults);
    }

    /**
     * Get a specific setting
     */
    public function get($key, $default = null)
    {
        $settings = $this->get_all();

        if ($default === null && isset($this->defaults[$key])) {
            $default = $this->defaults[$key];
        }

        return isset($settings[$key]) ? $settings[$key] : $default;
    }

    /**
     * Set a setting
     */
    public function set($key, $value)
    {
        $settings = $this->get_all();
        $settings[$key] = $value;
        return update_option(self::OPTION_NAME, $settings);
    }

    /**
     * Set multiple settings
     */
    public function set_multiple($settings)
    {
        $current = $this->get_all();
        $new_settings = wp_parse_args($settings, $current);
        return update_option(self::OPTION_NAME, $new_settings);
    }

    /**
     * Delete a setting
     */
    public function delete($key)
    {
        $settings = $this->get_all();
        unset($settings[$key]);
        return update_option(self::OPTION_NAME, $settings);
    }

    /**
     * Reset all settings to defaults
     */
    public function reset_to_defaults()
    {
        return update_option(self::OPTION_NAME, $this->defaults);
    }

    /**
     * Set default options
     */
    public function set_default_options()
    {
        $current = get_option(self::OPTION_NAME, []);
        if (empty($current)) {
            update_option(self::OPTION_NAME, $this->defaults);
        }
    }

    /**
     * Register WordPress settings
     */
    public function register_settings()
    {
        register_setting(
            'mobilo_auth_settings_group',
            self::OPTION_NAME,
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_settings'],
                'default' => $this->defaults,
            ]
        );

        // General Settings Section
        add_settings_section(
            'mobilo_auth_general',
            __('General Settings', 'mobilo-auth'),
            [$this, 'render_general_section'],
            'mobilo_auth_settings'
        );

        // Firebase Settings Section
        add_settings_section(
            'mobilo_auth_firebase',
            __('Firebase Configuration', 'mobilo-auth'),
            [$this, 'render_firebase_section'],
            'mobilo_auth_settings'
        );

        // Authentication Settings Section
        add_settings_section(
            'mobilo_auth_authentication',
            __('Authentication Settings', 'mobilo-auth'),
            [$this, 'render_authentication_section'],
            'mobilo_auth_settings'
        );

        // Advanced Settings Section
        add_settings_section(
            'mobilo_auth_advanced',
            __('Advanced Settings', 'mobilo-auth'),
            [$this, 'render_advanced_section'],
            'mobilo_auth_settings'
        );

        // Register settings fields
        $this->register_settings_fields();
    }

    /**
     * Register settings fields
     */
    private function register_settings_fields()
    {
        // General Settings Fields
        add_settings_field(
            'enable_firebase_auth',
            __('Enable Firebase Authentication', 'mobilo-auth'),
            [$this, 'render_checkbox_field'],
            'mobilo_auth_settings',
            'mobilo_auth_general',
            [
                'label_for' => 'enable_firebase_auth',
                'description' => __('Enable Firebase authentication for users', 'mobilo-auth'),
            ]
        );

        add_settings_field(
            'enable_wordpress_auth',
            __('Enable WordPress Authentication', 'mobilo-auth'),
            [$this, 'render_checkbox_field'],
            'mobilo_auth_settings',
            'mobilo_auth_general',
            [
                'label_for' => 'enable_wordpress_auth',
                'description' => __('Allow fallback to WordPress authentication', 'mobilo-auth'),
            ]
        );

        // Firebase Settings Fields
        add_settings_field(
            'firebase_project_id',
            __('Firebase Project ID', 'mobilo-auth'),
            [$this, 'render_text_field'],
            'mobilo_auth_settings',
            'mobilo_auth_firebase',
            [
                'label_for' => 'firebase_project_id',
                'description' => __('Your Firebase project ID', 'mobilo-auth'),
            ]
        );

        add_settings_field(
            'firebase_sdk_file',
            __('Firebase SDK File', 'mobilo-auth'),
            [$this, 'render_text_field'],
            'mobilo_auth_settings',
            'mobilo_auth_firebase',
            [
                'label_for' => 'firebase_sdk_file',
                'description' => __('Path to Firebase service account JSON file', 'mobilo-auth'),
            ]
        );

        add_settings_field(
            'firebase_api_key',
            __('Firebase API Key', 'mobilo-auth'),
            [$this, 'render_text_field'],
            'mobilo_auth_settings',
            'mobilo_auth_firebase',
            [
                'label_for' => 'firebase_api_key',
                'description' => __('Your Firebase API key', 'mobilo-auth'),
            ]
        );

        add_settings_field(
            'firebase_auth_domain',
            __('Firebase Auth Domain', 'mobilo-auth'),
            [$this, 'render_text_field'],
            'mobilo_auth_settings',
            'mobilo_auth_firebase',
            [
                'label_for' => 'firebase_auth_domain',
                'description' => __('Your Firebase auth domain', 'mobilo-auth'),
            ]
        );

        // Authentication Settings Fields
        add_settings_field(
            'auto_create_users',
            __('Auto-create WordPress Users', 'mobilo-auth'),
            [$this, 'render_checkbox_field'],
            'mobilo_auth_settings',
            'mobilo_auth_authentication',
            [
                'label_for' => 'auto_create_users',
                'description' => __('Automatically create WordPress users when they sign up via Firebase', 'mobilo-auth'),
            ]
        );

        add_settings_field(
            'session_timeout',
            __('Session Timeout (seconds)', 'mobilo-auth'),
            [$this, 'render_number_field'],
            'mobilo_auth_settings',
            'mobilo_auth_authentication',
            [
                'label_for' => 'session_timeout',
                'description' => __('How long to keep users logged in (default: 3600)', 'mobilo-auth'),
            ]
        );

        // Advanced Settings Fields
        add_settings_field(
            'enable_debug_logging',
            __('Enable Debug Logging', 'mobilo-auth'),
            [$this, 'render_checkbox_field'],
            'mobilo_auth_settings',
            'mobilo_auth_advanced',
            [
                'label_for' => 'enable_debug_logging',
                'description' => __('Enable detailed logging for debugging', 'mobilo-auth'),
            ]
        );
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input)
    {
        $sanitized = [];

        foreach ($input as $key => $value) {
            switch ($key) {
                case 'firebase_project_id':
                case 'firebase_api_key':
                case 'firebase_auth_domain':
                    $sanitized[$key] = sanitize_text_field($value);
                    break;

                case 'firebase_sdk_file':
                case 'firebase_sdk_file_eu':
                    $sanitized[$key] = sanitize_text_field($value);
                    break;

                case 'session_timeout':
                    $sanitized[$key] = absint($value);
                    break;

                case 'enable_firebase_auth':
                case 'enable_wordpress_auth':
                case 'auto_create_users':
                case 'sync_user_meta':
                case 'enable_multi_region':
                case 'enable_admin_notifications':
                case 'enable_debug_logging':
                case 'enable_remember_me':
                case 'enable_password_reset':
                case 'enable_email_verification':
                    $sanitized[$key] = (bool) $value;
                    break;

                case 'firebase_region':
                case 'default_region':
                    $sanitized[$key] = sanitize_text_field($value);
                    break;

                case 'custom_login_redirect':
                case 'custom_logout_redirect':
                    $sanitized[$key] = esc_url_raw($value);
                    break;

                default:
                    $sanitized[$key] = sanitize_text_field($value);
                    break;
            }
        }

        return $sanitized;
    }

    /**
     * Render general section
     */
    public function render_general_section()
    {
        echo '<p>' . __('Configure general authentication settings.', 'mobilo-auth') . '</p>';
    }

    /**
     * Render Firebase section
     */
    public function render_firebase_section()
    {
        echo '<p>' . __('Configure your Firebase project settings.', 'mobilo-auth') . '</p>';
    }

    /**
     * Render authentication section
     */
    public function render_authentication_section()
    {
        echo '<p>' . __('Configure authentication behavior and user management.', 'mobilo-auth') . '</p>';
    }

    /**
     * Render advanced section
     */
    public function render_advanced_section()
    {
        echo '<p>' . __('Advanced configuration options.', 'mobilo-auth') . '</p>';
    }

    /**
     * Render text field
     */
    public function render_text_field($args)
    {
        $key = $args['label_for'];
        $value = $this->get($key);
        $description = isset($args['description']) ? $args['description'] : '';

        printf(
            '<input type="text" id="%s" name="%s[%s]" value="%s" class="regular-text" />',
            esc_attr($key),
            esc_attr(self::OPTION_NAME),
            esc_attr($key),
            esc_attr($value)
        );

        if ($description) {
            printf('<p class="description">%s</p>', esc_html($description));
        }
    }

    /**
     * Render number field
     */
    public function render_number_field($args)
    {
        $key = $args['label_for'];
        $value = $this->get($key);
        $description = isset($args['description']) ? $args['description'] : '';

        printf(
            '<input type="number" id="%s" name="%s[%s]" value="%s" class="small-text" min="0" />',
            esc_attr($key),
            esc_attr(self::OPTION_NAME),
            esc_attr($key),
            esc_attr($value)
        );

        if ($description) {
            printf('<p class="description">%s</p>', esc_html($description));
        }
    }

    /**
     * Render checkbox field
     */
    public function render_checkbox_field($args)
    {
        $key = $args['label_for'];
        $value = $this->get($key);
        $description = isset($args['description']) ? $args['description'] : '';

        printf(
            '<input type="checkbox" id="%s" name="%s[%s]" value="1" %s />',
            esc_attr($key),
            esc_attr(self::OPTION_NAME),
            esc_attr($key),
            checked(1, $value, false)
        );

        printf(
            '<label for="%s">%s</label>',
            esc_attr($key),
            esc_html__('Enable', 'mobilo-auth')
        );

        if ($description) {
            printf('<p class="description">%s</p>', esc_html($description));
        }
    }

    /**
     * Get Firebase configuration
     */
    public function get_firebase_config()
    {
        return [
            'project_id' => $this->get('firebase_project_id'),
            'region' => $this->get('firebase_region', 'us'),
            'sdk_file' => $this->get('firebase_sdk_file'),
            'api_key' => $this->get('firebase_api_key'),
            'auth_domain' => $this->get('firebase_auth_domain'),
        ];
    }

    /**
     * Check if Firebase auth is enabled
     */
    public function is_firebase_auth_enabled()
    {
        return $this->get('enable_firebase_auth', true);
    }

    /**
     * Check if WordPress auth is enabled
     */
    public function is_wordpress_auth_enabled()
    {
        return $this->get('enable_wordpress_auth', true);
    }

    /**
     * Check if auto-create users is enabled
     */
    public function is_auto_create_users_enabled()
    {
        return $this->get('auto_create_users', true);
    }

    /**
     * Check if debug logging is enabled
     */
    public function is_debug_logging_enabled()
    {
        return $this->get('enable_debug_logging', false);
    }
}
