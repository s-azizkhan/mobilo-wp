<?php
/**
 * Plugin Name: Mobilo Auth - Firebase Authentication
 * Plugin URI: https://buy.mobilocard.com
 * Description: A comprehensive Firebase Authentication plugin for WordPress with multi-region support, user management, and enhanced security features.
 * Version: 1.0.0
 * Author: Hypertics AI
 * Author URI: https://hypertics.ai
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mobilo-auth
 * Domain Path: /languages
 * Requires at least: 5.6
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MOBILO_AUTH_VERSION', '1.0.0');
define('MOBILO_AUTH_PLUGIN_FILE', __FILE__);
define('MOBILO_AUTH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MOBILO_AUTH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MOBILO_AUTH_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader for the plugin
spl_autoload_register(function ($class) {
    $prefix = 'MobiloAuth\\';
    $base_dir = MOBILO_AUTH_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Main plugin class
 */
class MobiloAuth
{
    /**
     * Plugin instance
     */
    private static $instance;

    /**
     * Firebase Auth instance
     */
    private $firebase_auth;

    /**
     * Plugin settings
     */
    private $settings;

    /**
     * Get plugin instance
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->init();
    }

    /**
     * Initialize plugin
     */
    private function init()
    {
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            add_action('admin_notices', [$this, 'php_version_notice']);
            return;
        }

        // Load dependencies
        $this->load_dependencies();

        // Initialize components
        $this->init_components();

        // Hook into WordPress
        $this->hook_into_wordpress();
    }

    /**
     * Load plugin dependencies
     */
    private function load_dependencies()
    {
        // Check if Composer autoloader exists
        $composer_autoload = MOBILO_AUTH_PLUGIN_DIR . 'vendor/autoload.php';
        if (file_exists($composer_autoload)) {
            require_once $composer_autoload;
        } else {
            // Fallback: Check if theme's vendor directory exists
            $theme_vendor = get_template_directory() . '/vendor/autoload.php';
            if (file_exists($theme_vendor)) {
                require_once $theme_vendor;
            }
        }
    }

    /**
     * Initialize plugin components
     */
    private function init_components()
    {
        // Initialize settings
        $this->settings = new MobiloAuth\Core\Settings();

        // Initialize Firebase Auth
        $this->firebase_auth = new MobiloAuth\Core\FirebaseAuth();

        // Initialize user management
        (new MobiloAuth\Core\UserManager())->init();

        // Initialize authentication hooks
        new MobiloAuth\Core\AuthenticationHooks();

        // Initialize admin interface
        if (is_admin()) {
            new MobiloAuth\Admin\AdminInterface();
        }

        // Initialize REST API
        new MobiloAuth\API\RestAPI();

        // Initialize shortcodes
        new MobiloAuth\Shortcodes\AuthShortcodes();

        // Initialize AJAX handlers
        new MobiloAuth\Ajax\AjaxHandler();
    }

    /**
     * Hook into WordPress
     */
    private function hook_into_wordpress()
    {
        // Activation hook
        register_activation_hook(__FILE__, [$this, 'activate']);

        // Deactivation hook
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        // Uninstall hook
        register_uninstall_hook(__FILE__, [$this, 'uninstall']);

        // Load text domain
        add_action('init', [$this, 'load_textdomain']);

        // Add settings link to plugins page
        // add_filter('plugin_action_links_' . MOBILO_AUTH_PLUGIN_BASENAME, [$this, 'add_settings_link']);
    }

    /**
     * Plugin activation
     */
    public function activate()
    {
        // Create database tables
        // MobiloAuth\Core\Database::create_tables();

        // Set default options
        // $this->settings->set_default_options();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate()
    {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin uninstall
     */
    public static function uninstall()
    {
        // Remove plugin options
        delete_option('mobilo_auth_settings');
        delete_option('mobilo_auth_firebase_config');

        // Remove database tables
        MobiloAuth\Core\Database::drop_tables();
    }

    /**
     * Load plugin text domain
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('mobilo-auth', false, dirname(MOBILO_AUTH_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Add settings link to plugins page
     */
    public function add_settings_link($links)
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=mobilo-auth-settings') . '">' . __('Settings', 'mobilo-auth') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * PHP version notice
     */
    public function php_version_notice()
    {
        echo '<div class="notice notice-error"><p>';
        echo sprintf(
            __('Mobilo Auth requires PHP version 7.4 or higher. Your current version is %s. Please upgrade your PHP version.', 'mobilo-auth'),
            PHP_VERSION
        );
        echo '</p></div>';
    }

    /**
     * Get Firebase Auth instance
     */
    public function getFirebaseAuth()
    {
        return $this->firebase_auth;
    }

    /**
     * Get plugin settings
     */
    public function getSettings()
    {
        return $this->settings ?? null;
    }
}

// Initialize the plugin
function mobilo_auth_init()
{
    return mobilo_auth();
}

// Start the plugin
add_action('plugins_loaded', 'mobilo_auth_init');

// Global function to access plugin instance
function mobilo_auth()
{
    return MobiloAuth::getInstance();
}
