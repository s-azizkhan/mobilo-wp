<?php
/**
 * Admin Tools View
 *
 * @package MobiloAuth
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap mobilo-auth-tools">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="mobilo-auth-tools-grid">
        <!-- Firebase Connection Test -->
        <div class="mobilo-auth-tool-card">
            <h3><?php _e('Firebase Connection Test', 'mobilo-auth'); ?></h3>
            <p><?php _e('Test your Firebase configuration and connection status.', 'mobilo-auth'); ?></p>
            <form method="post" action="">
                <?php wp_nonce_field('mobilo_auth_admin_nonce', 'mobilo_auth_nonce'); ?>
                <input type="hidden" name="mobilo_auth_action" value="test_firebase_connection">
                <button type="submit" class="button button-primary">
                    <?php _e('Test Connection', 'mobilo-auth'); ?>
                </button>
            </form>
        </div>

        <!-- User Synchronization -->
        <div class="mobilo-auth-tool-card">
            <h3><?php _e('User Synchronization', 'mobilo-auth'); ?></h3>
            <p><?php _e('Sync Firebase users with WordPress users.', 'mobilo-auth'); ?></p>
            <form method="post" action="">
                <?php wp_nonce_field('mobilo_auth_admin_nonce', 'mobilo_auth_nonce'); ?>
                <input type="hidden" name="mobilo_auth_action" value="sync_users">
                <button type="submit" class="button button-secondary">
                    <?php _e('Sync Users', 'mobilo-auth'); ?>
                </button>
            </form>
        </div>

        <!-- Clear Logs -->
        <div class="mobilo-auth-tool-card">
            <h3><?php _e('Clear Authentication Logs', 'mobilo-auth'); ?></h3>
            <p><?php _e('Clear all authentication logs from the database.', 'mobilo-auth'); ?></p>
            <form method="post" action="">
                <?php wp_nonce_field('mobilo_auth_admin_nonce', 'mobilo_auth_nonce'); ?>
                <input type="hidden" name="mobilo_auth_action" value="clear_logs">
                <button type="submit" class="button button-secondary"
                    onclick="return confirm('<?php _e('Are you sure you want to clear all logs? This action cannot be undone.', 'mobilo-auth'); ?>')">
                    <?php _e('Clear Logs', 'mobilo-auth'); ?>
                </button>
            </form>
        </div>

        <!-- Database Maintenance -->
        <div class="mobilo-auth-tool-card">
            <h3><?php _e('Database Maintenance', 'mobilo-auth'); ?></h3>
            <p><?php _e('Optimize and clean up plugin database tables.', 'mobilo-auth'); ?></p>
            <button type="button" class="button button-secondary" id="optimize-database">
                <?php _e('Optimize Database', 'mobilo-auth'); ?>
            </button>
        </div>

        <!-- Export Settings -->
        <div class="mobilo-auth-tool-card">
            <h3><?php _e('Export Settings', 'mobilo-auth'); ?></h3>
            <p><?php _e('Export your plugin settings for backup or migration.', 'mobilo-auth'); ?></p>
            <button type="button" class="button button-secondary" id="export-settings">
                <?php _e('Export Settings', 'mobilo-auth'); ?>
            </button>
        </div>

        <!-- Import Settings -->
        <div class="mobilo-auth-tool-card">
            <h3><?php _e('Import Settings', 'mobilo-auth'); ?></h3>
            <p><?php _e('Import settings from a backup file.', 'mobilo-auth'); ?></p>
            <form method="post" action="" enctype="multipart/form-data">
                <?php wp_nonce_field('mobilo_auth_admin_nonce', 'mobilo_auth_nonce'); ?>
                <input type="hidden" name="mobilo_auth_action" value="import_settings">
                <input type="file" name="settings_file" accept=".json" required>
                <button type="submit" class="button button-secondary">
                    <?php _e('Import Settings', 'mobilo-auth'); ?>
                </button>
            </form>
        </div>
    </div>

    <!-- System Information -->
    <div class="mobilo-auth-system-info">
        <h2><?php _e('System Information', 'mobilo-auth'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <tbody>
                <tr>
                    <th><?php _e('Plugin Version', 'mobilo-auth'); ?></th>
                    <td><?php echo esc_html(MOBILO_AUTH_VERSION); ?></td>
                </tr>
                <tr>
                    <th><?php _e('WordPress Version', 'mobilo-auth'); ?></th>
                    <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                </tr>
                <tr>
                    <th><?php _e('PHP Version', 'mobilo-auth'); ?></th>
                    <td><?php echo esc_html(PHP_VERSION); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Database Version', 'mobilo-auth'); ?></th>
                    <td><?php global $wpdb;
                    echo esc_html($wpdb->db_version()); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Firebase PHP SDK', 'mobilo-auth'); ?></th>
                    <td><?php echo class_exists('Kreait\Firebase\Factory') ? __('Available', 'mobilo-auth') : __('Not Available', 'mobilo-auth'); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
    .mobilo-auth-tools {
        margin: 20px 0;
    }

    .mobilo-auth-tools-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin: 20px 0;
    }

    .mobilo-auth-tool-card {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
    }

    .mobilo-auth-tool-card h3 {
        margin: 0 0 10px 0;
        color: #23282d;
        font-size: 16px;
        font-weight: 600;
    }

    .mobilo-auth-tool-card p {
        margin: 0 0 15px 0;
        color: #666;
        line-height: 1.4;
    }

    .mobilo-auth-tool-card form {
        margin: 0;
    }

    .mobilo-auth-tool-card input[type="file"] {
        margin-bottom: 10px;
        width: 100%;
    }

    .mobilo-auth-system-info {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        margin: 20px 0;
    }

    .mobilo-auth-system-info h2 {
        margin: 0 0 15px 0;
        color: #23282d;
    }

    .mobilo-auth-system-info th {
        width: 200px;
        font-weight: 600;
    }
</style>

<script>
    jQuery(document).ready(function ($) {
        // Optimize Database
        $('#optimize-database').on('click', function () {
            if (confirm('<?php _e('Are you sure you want to optimize the database?', 'mobilo-auth'); ?>')) {
                // AJAX call to optimize database
                $.post(ajaxurl, {
                    action: 'mobilo_auth_optimize_database',
                    nonce: mobiloAuthAdmin.nonce
                }, function (response) {
                    if (response.success) {
                        alert('<?php _e('Database optimized successfully!', 'mobilo-auth'); ?>');
                    } else {
                        alert('<?php _e('Error optimizing database: ', 'mobilo-auth'); ?>' + response.data);
                    }
                });
            }
        });

        // Export Settings
        $('#export-settings').on('click', function () {
            // AJAX call to export settings
            $.post(ajaxurl, {
                action: 'mobilo_auth_export_settings',
                nonce: mobiloAuthAdmin.nonce
            }, function (response) {
                if (response.success) {
                    // Create download link
                    const blob = new Blob([response.data], { type: 'application/json' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'mobilo-auth-settings.json';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                } else {
                    alert('<?php _e('Error exporting settings: ', 'mobilo-auth'); ?>' + response.data);
                }
            });
        });
    });
</script>