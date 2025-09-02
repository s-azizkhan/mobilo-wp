<?php
/**
 * Uninstall Mobilo Auth Plugin
 *
 * @package MobiloAuth
 * @since 1.0.0
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if user has permission to uninstall
if (!current_user_can('activate_plugins')) {
    return;
}

// Include WordPress database functions
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

// Get plugin options
$options = get_option('mobilo_auth_settings', array());

// Check if we should remove all data
$remove_all_data = isset($options['remove_all_data_on_uninstall']) ? $options['remove_all_data_on_uninstall'] : false;

if ($remove_all_data) {

    // Remove all plugin options
    delete_option('mobilo_auth_settings');
    delete_option('mobilo_auth_version');
    delete_option('mobilo_auth_db_version');
    delete_option('mobilo_auth_activation_time');

    // Remove user meta data
    global $wpdb;

    // Remove Firebase UID from all users
    $wpdb->delete(
        $wpdb->usermeta,
        array('meta_key' => 'mobilo_auth_firebase_uid')
    );

    // Remove Firebase user data
    $wpdb->delete(
        $wpdb->usermeta,
        array('meta_key' => 'mobilo_auth_firebase_user_data')
    );

    // Remove Firebase region
    $wpdb->delete(
        $wpdb->usermeta,
        array('meta_key' => 'mobilo_auth_firebase_region')
    );

    // Remove Firebase last sync
    $wpdb->delete(
        $wpdb->usermeta,
        array('meta_key' => 'mobilo_auth_firebase_last_sync')
    );

    // Drop custom database tables
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mobilo_auth_firebase_users");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mobilo_auth_sessions");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mobilo_auth_logs");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mobilo_auth_regions");

    // Remove scheduled events
    wp_clear_scheduled_hook('mobilo_auth_cleanup_expired_sessions');
    wp_clear_scheduled_hook('mobilo_auth_sync_users_cron');
    wp_clear_scheduled_hook('mobilo_auth_cleanup_logs_cron');

    // Remove transients
    delete_transient('mobilo_auth_firebase_connection_test');
    delete_transient('mobilo_auth_user_sync_status');
    delete_transient('mobilo_auth_system_info');

    // Remove uploaded files
    $upload_dir = wp_upload_dir();
    $plugin_upload_dir = $upload_dir['basedir'] . '/mobilo-auth';

    if (is_dir($plugin_upload_dir)) {
        // Remove all files in the plugin upload directory
        $files = glob($plugin_upload_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        // Remove the directory
        rmdir($plugin_upload_dir);
    }

    // Log uninstall action
    if (function_exists('error_log')) {
        error_log('Mobilo Auth Plugin uninstalled and all data removed at ' . current_time('mysql'));
    }

} else {

    // Only remove plugin files and basic options, keep user data
    delete_option('mobilo_auth_version');
    delete_option('mobilo_auth_activation_time');

    // Log uninstall action
    if (function_exists('error_log')) {
        error_log('Mobilo Auth Plugin uninstalled (data preserved) at ' . current_time('mysql'));
    }
}

// Clear any cached data
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
}

// Clear object cache if available
if (function_exists('wp_cache_flush_group')) {
    wp_cache_flush_group('mobilo_auth');
}

// Remove any rewrite rules
flush_rewrite_rules();
