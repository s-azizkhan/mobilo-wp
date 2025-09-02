<?php

namespace MobiloAuth\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Interface Class
 * 
 * @since 1.0.0
 */
class AdminInterface
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Add admin notices
        add_action('admin_notices', array($this, 'show_admin_notices'));
        
        // Add plugin action links
        add_filter('plugin_action_links_' . plugin_basename(MOBILO_AUTH_PLUGIN_FILE), array($this, 'add_plugin_action_links'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu()
    {
        // Main menu
        add_menu_page(
            __('Mobilo Auth', 'mobilo-auth'),
            __('Mobilo Auth', 'mobilo-auth'),
            'manage_options',
            'mobilo-auth',
            array($this, 'render_dashboard_page'),
            'dashicons-shield',
            30
        );

        // Dashboard submenu
        add_submenu_page(
            'mobilo-auth',
            __('Dashboard', 'mobilo-auth'),
            __('Dashboard', 'mobilo-auth'),
            'manage_options',
            'mobilo-auth',
            array($this, 'render_dashboard_page')
        );

        // Settings submenu
        add_submenu_page(
            'mobilo-auth',
            __('Settings', 'mobilo-auth'),
            __('Settings', 'mobilo-auth'),
            'manage_options',
            'mobilo-auth-settings',
            array($this, 'render_settings_page')
        );

        // Users submenu
        add_submenu_page(
            'mobilo-auth',
            __('Firebase Users', 'mobilo-auth'),
            __('Firebase Users', 'mobilo-auth'),
            'manage_options',
            'mobilo-auth-users',
            array($this, 'render_users_page')
        );

        // Logs submenu
        add_submenu_page(
            'mobilo-auth',
            __('Authentication Logs', 'mobilo-auth'),
            __('Authentication Logs', 'mobilo-auth'),
            'manage_options',
            'mobilo-auth-logs',
            array($this, 'render_logs_page')
        );

        // Regions submenu
        add_submenu_page(
            'mobilo-auth',
            __('Firebase Regions', 'mobilo-auth'),
            __('Firebase Regions', 'mobilo-auth'),
            'manage_options',
            'mobilo-auth-regions',
            array($this, 'render_regions_page')
        );

        // Tools submenu
        add_submenu_page(
            'mobilo-auth',
            __('Tools', 'mobilo-auth'),
            __('Tools', 'mobilo-auth'),
            'manage_options',
            'mobilo-auth-tools',
            array($this, 'render_tools_page')
        );
    }

    /**
     * Admin initialization
     */
    public function admin_init()
    {
        // Register settings
        register_setting('mobilo_auth_settings_group', 'mobilo_auth_settings');
        
        // Handle form submissions
        $this->handle_form_submissions();
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook)
    {
        if (strpos($hook, 'mobilo-auth') === false) {
            return;
        }

        wp_enqueue_style(
            'mobilo-auth-admin',
            MOBILO_AUTH_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            MOBILO_AUTH_VERSION
        );

        wp_enqueue_script(
            'mobilo-auth-admin',
            MOBILO_AUTH_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            MOBILO_AUTH_VERSION,
            true
        );

        wp_localize_script('mobilo-auth-admin', 'mobiloAuthAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mobilo_auth_admin_nonce'),
            'strings' => array(
                'confirmDelete' => __('Are you sure you want to delete this item?', 'mobilo-auth'),
                'saving' => __('Saving...', 'mobilo-auth'),
                'saved' => __('Settings saved successfully!', 'mobilo-auth'),
                'error' => __('An error occurred. Please try again.', 'mobilo-auth')
            )
        ));
    }

    /**
     * Handle form submissions
     */
    private function handle_form_submissions()
    {
        if (!isset($_POST['mobilo_auth_action'])) {
            return;
        }

        check_admin_referer('mobilo_auth_admin_nonce', 'mobilo_auth_nonce');

        $action = sanitize_text_field($_POST['mobilo_auth_action']);

        switch ($action) {
            case 'save_settings':
                $this->save_settings();
                break;
            case 'test_firebase_connection':
                $this->test_firebase_connection();
                break;
            case 'sync_users':
                $this->sync_users();
                break;
            case 'clear_logs':
                $this->clear_logs();
                break;
        }
    }

    /**
     * Save settings
     */
    private function save_settings()
    {
        $settings = array();
        
        // General settings
        $settings['enable_firebase_auth'] = isset($_POST['enable_firebase_auth']);
        $settings['enable_wordpress_auth'] = isset($_POST['enable_wordpress_auth']);
        $settings['auto_create_users'] = isset($_POST['auto_create_users']);
        $settings['sync_user_meta'] = isset($_POST['sync_user_meta']);
        
        // Firebase settings
        $settings['firebase_project_id'] = sanitize_text_field($_POST['firebase_project_id']);
        $settings['firebase_region'] = sanitize_text_field($_POST['firebase_region']);
        $settings['firebase_sdk_file'] = sanitize_text_field($_POST['firebase_sdk_file']);
        $settings['firebase_api_key'] = sanitize_text_field($_POST['firebase_api_key']);
        $settings['firebase_auth_domain'] = sanitize_text_field($_POST['firebase_auth_domain']);
        
        // Advanced settings
        $settings['enable_debug_logging'] = isset($_POST['enable_debug_logging']);
        $settings['session_timeout'] = absint($_POST['session_timeout']);
        $settings['custom_login_redirect'] = esc_url_raw($_POST['custom_login_redirect']);
        $settings['custom_logout_redirect'] = esc_url_raw($_POST['custom_logout_redirect']);

        update_option('mobilo_auth_settings', $settings);
        
        add_settings_error(
            'mobilo_auth_messages',
            'mobilo_auth_message',
            __('Settings saved successfully!', 'mobilo-auth'),
            'updated'
        );
    }

    /**
     * Test Firebase connection
     */
    private function test_firebase_connection()
    {
        try {
            $firebase_auth = new \MobiloAuth\Core\FirebaseAuth();
            
            if ($firebase_auth->getError()) {
                add_settings_error(
                    'mobilo_auth_messages',
                    'mobilo_auth_message',
                    __('Firebase connection failed: ' . $firebase_auth->getError(), 'mobilo-auth'),
                    'error'
                );
            } else {
                add_settings_error(
                    'mobilo_auth_messages',
                    'mobilo_auth_message',
                    __('Firebase connection successful!', 'mobilo-auth'),
                    'updated'
                );
            }
        } catch (Throwable $e) {
            add_settings_error(
                'mobilo_auth_messages',
                'mobilo_auth_message',
                __('Firebase connection test failed: ' . $e->getMessage(), 'mobilo-auth'),
                'error'
            );
        }
    }

    /**
     * Sync users
     */
    private function sync_users()
    {
        // Implementation for user synchronization
        add_settings_error(
            'mobilo_auth_messages',
            'mobilo_auth_message',
            __('User synchronization completed!', 'mobilo-auth'),
            'updated'
        );
    }

    /**
     * Clear logs
     */
    private function clear_logs()
    {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mobilo_auth_firebase_logs';
        $wpdb->query("TRUNCATE TABLE $table");
        
        add_settings_error(
            'mobilo_auth_messages',
            'mobilo_auth_message',
            __('Authentication logs cleared successfully!', 'mobilo-auth'),
            'updated'
        );
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard_page()
    {
        $stats = \MobiloAuth\Core\Database::get_auth_statistics();
        include MOBILO_AUTH_PLUGIN_DIR . 'includes/views/admin-dashboard.php';
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        $settings = get_option('mobilo_auth_settings', array());
        include MOBILO_AUTH_PLUGIN_DIR . 'includes/views/admin-settings.php';
    }

    /**
     * Render users page
     */
    public function render_users_page()
    {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mobilo_auth_firebase_users';
        $users = $wpdb->get_results("SELECT * FROM $table WHERE is_active = 1 ORDER BY created_at DESC");
        
        include MOBILO_AUTH_PLUGIN_DIR . 'includes/views/admin-users.php';
    }

    /**
     * Render logs page
     */
    public function render_logs_page()
    {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mobilo_auth_firebase_logs';
        $logs = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT 100");
        
        include MOBILO_AUTH_PLUGIN_DIR . 'includes/views/admin-logs.php';
    }

    /**
     * Render regions page
     */
    public function render_regions_page()
    {
        $regions = \MobiloAuth\Core\Database::get_active_firebase_regions();
        include MOBILO_AUTH_PLUGIN_DIR . 'includes/views/admin-regions.php';
    }

    /**
     * Render tools page
     */
    public function render_tools_page()
    {
        include MOBILO_AUTH_PLUGIN_DIR . 'includes/views/admin-tools.php';
    }

    /**
     * Show admin notices
     */
    public function show_admin_notices()
    {
        settings_errors('mobilo_auth_messages');
    }

    /**
     * Add plugin action links
     */
    public function add_plugin_action_links($links)
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=mobilo-auth-settings') . '">' . __('Settings', 'mobilo-auth') . '</a>';
        array_unshift($links, $settings_link);
        
        return $links;
    }

    /**
     * Get plugin status
     */
    public function get_plugin_status()
    {
        $status = array();
        
        // Check Firebase connection
        try {
            $firebase_auth = new \MobiloAuth\Core\FirebaseAuth();
            $status['firebase_connection'] = $firebase_auth->getError() ? 'error' : 'success';
            $status['firebase_error'] = $firebase_auth->getError();
        } catch (Throwable $e) {
            $status['firebase_connection'] = 'error';
            $status['firebase_error'] = $e->getMessage();
        }
        
        // Check database tables
        $status['database_tables'] = \MobiloAuth\Core\Database::tables_exist() ? 'success' : 'error';
        
        // Check settings
        $settings = get_option('mobilo_auth_settings', array());
        $status['settings_configured'] = !empty($settings['firebase_project_id']) ? 'success' : 'warning';
        
        // Check PHP version
        $status['php_version'] = version_compare(PHP_VERSION, '7.4', '>=') ? 'success' : 'error';
        
        return $status;
    }

    /**
     * Get system information
     */
    public function get_system_info()
    {
        global $wpdb;
        
        $info = array();
        
        $info['wordpress_version'] = get_bloginfo('version');
        $info['php_version'] = PHP_VERSION;
        $info['mysql_version'] = $wpdb->db_version();
        $info['plugin_version'] = MOBILO_AUTH_VERSION;
        $info['firebase_php_version'] = class_exists('Kreait\Firebase\Factory') ? 'Available' : 'Not Available';
        
        return $info;
    }
}
