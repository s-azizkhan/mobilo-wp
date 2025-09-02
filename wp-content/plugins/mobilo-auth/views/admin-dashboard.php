<?php
/**
 * Admin Dashboard View
 *
 * @package MobiloAuth
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap mobilo-auth-dashboard">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="mobilo-auth-stats-grid">
        <div class="mobilo-auth-stat-card">
            <h3><?php _e('Total Users', 'mobilo-auth'); ?></h3>
            <div class="stat-number"><?php echo esc_html(isset($stats['total_users']) ? $stats['total_users'] : 0); ?>
            </div>
            <p><?php _e('Firebase-connected users', 'mobilo-auth'); ?></p>
        </div>

        <div class="mobilo-auth-stat-card">
            <h3><?php _e('Active Sessions', 'mobilo-auth'); ?></h3>
            <div class="stat-number">
                <?php echo esc_html(isset($stats['active_sessions']) ? $stats['active_sessions'] : 0); ?>
            </div>
            <p><?php _e('Current active sessions', 'mobilo-auth'); ?></p>
        </div>

        <div class="mobilo-auth-stat-card">
            <h3><?php _e('Today\'s Logins', 'mobilo-auth'); ?></h3>
            <div class="stat-number"><?php echo esc_html(isset($stats['today_logins']) ? $stats['today_logins'] : 0); ?>
            </div>
            <p><?php _e('Login attempts today', 'mobilo-auth'); ?></p>
        </div>

        <div class="mobilo-auth-stat-card">
            <h3><?php _e('Firebase Status', 'mobilo-auth'); ?></h3>
            <div class="stat-status <?php echo $stats['firebase_status'] ? 'status-ok' : 'status-error'; ?>">
                <?php echo $stats['firebase_status'] ? __('Connected', 'mobilo-auth') : __('Disconnected', 'mobilo-auth'); ?>
            </div>
            <p><?php _e('Connection status', 'mobilo-auth'); ?></p>
        </div>
    </div>

    <div class="mobilo-auth-quick-actions">
        <h2><?php _e('Quick Actions', 'mobilo-auth'); ?></h2>
        <div class="action-buttons">
            <a href="<?php echo admin_url('admin.php?page=mobilo-auth-settings'); ?>" class="button button-primary">
                <?php _e('Configure Settings', 'mobilo-auth'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=mobilo-auth-users'); ?>" class="button button-secondary">
                <?php _e('Manage Users', 'mobilo-auth'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=mobilo-auth-logs'); ?>" class="button button-secondary">
                <?php _e('View Logs', 'mobilo-auth'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=mobilo-auth-tools'); ?>" class="button button-secondary">
                <?php _e('Tools', 'mobilo-auth'); ?>
            </a>
        </div>
    </div>

    <div class="mobilo-auth-recent-activity">
        <h2><?php _e('Recent Activity', 'mobilo-auth'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Time', 'mobilo-auth'); ?></th>
                    <th><?php _e('User', 'mobilo-auth'); ?></th>
                    <th><?php _e('Action', 'mobilo-auth'); ?></th>
                    <th><?php _e('IP Address', 'mobilo-auth'); ?></th>
                    <th><?php _e('Status', 'mobilo-auth'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_activity)): ?>
                    <?php foreach ($recent_activity as $activity): ?>
                        <tr>
                            <td><?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($activity->created_at))); ?>
                            </td>
                            <td><?php echo esc_html(isset($activity->user_email) ? $activity->user_email : __('Unknown', 'mobilo-auth')); ?>
                            </td>
                            <td><?php echo esc_html($activity->action); ?></td>
                            <td><?php echo esc_html($activity->ip_address); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $activity->status; ?>">
                                    <?php echo esc_html(ucfirst($activity->status)); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5"><?php _e('No recent activity found.', 'mobilo-auth'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

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
                    <td><?php echo esc_html(isset($system_info['db_version']) ? $system_info['db_version'] : __('Unknown', 'mobilo-auth')); ?>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Firebase SDK Version', 'mobilo-auth'); ?></th>
                    <td><?php echo esc_html(isset($system_info['firebase_sdk_version']) ? $system_info['firebase_sdk_version'] : __('Unknown', 'mobilo-auth')); ?>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Active Regions', 'mobilo-auth'); ?></th>
                    <td><?php echo esc_html(implode(', ', isset($system_info['active_regions']) ? $system_info['active_regions'] : array())); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
    .mobilo-auth-dashboard {
        margin: 20px 0;
    }

    .mobilo-auth-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin: 20px 0;
    }

    .mobilo-auth-stat-card {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
    }

    .mobilo-auth-stat-card h3 {
        margin: 0 0 10px 0;
        color: #23282d;
        font-size: 14px;
        font-weight: 600;
    }

    .stat-number {
        font-size: 36px;
        font-weight: bold;
        color: #0073aa;
        margin: 10px 0;
    }

    .stat-status {
        font-size: 24px;
        font-weight: bold;
        margin: 10px 0;
    }

    .status-ok {
        color: #46b450;
    }

    .status-error {
        color: #dc3232;
    }

    .mobilo-auth-quick-actions {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        margin: 20px 0;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .mobilo-auth-recent-activity,
    .mobilo-auth-system-info {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        margin: 20px 0;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-success {
        background: #dff0d8;
        color: #3c763d;
    }

    .status-error {
        background: #f2dede;
        color: #a94442;
    }

    .status-warning {
        background: #fcf8e3;
        color: #8a6d3b;
    }

    .status-info {
        background: #d9edf7;
        color: #31708f;
    }
</style>