<?php
/**
 * Admin Logs View
 *
 * @package MobiloAuth
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap mobilo-auth-logs">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="mobilo-auth-logs-header">
        <div class="logs-stats">
            <span class="stat-item">
                <strong><?php echo count($logs); ?></strong> <?php _e('Recent Logs', 'mobilo-auth'); ?>
            </span>
        </div>
        <div class="logs-actions">
            <button type="button" class="button button-primary" id="refresh-logs">
                <?php _e('Refresh', 'mobilo-auth'); ?>
            </button>
            <form method="post" action="" style="display: inline;">
                <?php wp_nonce_field('mobilo_auth_admin_nonce', 'mobilo_auth_nonce'); ?>
                <input type="hidden" name="mobilo_auth_action" value="clear_logs">
                <button type="submit" class="button button-secondary"
                    onclick="return confirm('<?php _e('Are you sure you want to clear all logs? This action cannot be undone.', 'mobilo-auth'); ?>')">
                    <?php _e('Clear All Logs', 'mobilo-auth'); ?>
                </button>
            </form>
            <button type="button" class="button button-secondary" id="export-logs">
                <?php _e('Export Logs', 'mobilo-auth'); ?>
            </button>
        </div>
    </div>

    <div class="mobilo-auth-logs-filters">
        <input type="text" id="log-search" placeholder="<?php _e('Search logs...', 'mobilo-auth'); ?>"
            class="regular-text">
        <select id="log-level-filter">
            <option value=""><?php _e('All Levels', 'mobilo-auth'); ?></option>
            <option value="info"><?php _e('Info', 'mobilo-auth'); ?></option>
            <option value="warning"><?php _e('Warning', 'mobilo-auth'); ?></option>
            <option value="error"><?php _e('Error', 'mobilo-auth'); ?></option>
            <option value="debug"><?php _e('Debug', 'mobilo-auth'); ?></option>
        </select>
        <select id="log-action-filter">
            <option value=""><?php _e('All Actions', 'mobilo-auth'); ?></option>
            <option value="login"><?php _e('Login', 'mobilo-auth'); ?></option>
            <option value="logout"><?php _e('Logout', 'mobilo-auth'); ?></option>
            <option value="register"><?php _e('Register', 'mobilo-auth'); ?></option>
            <option value="password_reset"><?php _e('Password Reset', 'mobilo-auth'); ?></option>
            <option value="token_validation"><?php _e('Token Validation', 'mobilo-auth'); ?></option>
        </select>
        <input type="date" id="log-date-filter" class="regular-text">
    </div>

    <table class="wp-list-table widefat fixed striped" id="logs-table">
        <thead>
            <tr>
                <th><?php _e('Time', 'mobilo-auth'); ?></th>
                <th><?php _e('Level', 'mobilo-auth'); ?></th>
                <th><?php _e('Action', 'mobilo-auth'); ?></th>
                <th><?php _e('User', 'mobilo-auth'); ?></th>
                <th><?php _e('IP Address', 'mobilo-auth'); ?></th>
                <th><?php _e('Message', 'mobilo-auth'); ?></th>
                <th><?php _e('Details', 'mobilo-auth'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($logs)): ?>
                <?php foreach ($logs as $log): ?>
                    <tr data-log-id="<?php echo esc_attr($log->id); ?>" class="log-level-<?php echo esc_attr($log->level); ?>">
                        <td>
                            <?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->created_at))); ?>
                        </td>
                        <td>
                            <span class="log-level-badge level-<?php echo esc_attr($log->level); ?>">
                                <?php echo esc_html(ucfirst($log->level)); ?>
                            </span>
                        </td>
                        <td>
                            <span class="log-action-badge action-<?php echo esc_attr($log->action); ?>">
                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $log->action))); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($log->user_email): ?>
                                <strong><?php echo esc_html($log->user_email); ?></strong>
                                <?php if ($log->firebase_uid): ?>
                                    <br><small><code><?php echo esc_html($log->firebase_uid); ?></code></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted"><?php _e('Anonymous', 'mobilo-auth'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code><?php echo esc_html($log->ip_address); ?></code>
                        </td>
                        <td>
                            <div class="log-message">
                                <?php echo esc_html($log->message); ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($log->details): ?>
                                <button type="button" class="button button-small view-log-details"
                                    data-log-id="<?php echo esc_attr($log->id); ?>">
                                    <?php _e('View', 'mobilo-auth'); ?>
                                </button>
                            <?php else: ?>
                                <span class="text-muted"><?php _e('No details', 'mobilo-auth'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="no-items">
                        <?php _e('No authentication logs found.', 'mobilo-auth'); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Log Details Modal -->
    <div id="log-details-modal" class="mobilo-modal" style="display: none;">
        <div class="mobilo-modal-content">
            <div class="mobilo-modal-header">
                <h2><?php _e('Log Details', 'mobilo-auth'); ?></h2>
                <span class="mobilo-modal-close">&times;</span>
            </div>
            <div class="mobilo-modal-body" id="log-details-content">
                <!-- Log details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<style>
    .mobilo-auth-logs {
        margin: 20px 0;
    }

    .mobilo-auth-logs-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 15px;
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
    }

    .logs-stats .stat-item {
        font-size: 14px;
        color: #666;
    }

    .logs-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .mobilo-auth-logs-filters {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        padding: 15px;
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        flex-wrap: wrap;
    }

    .mobilo-auth-logs-filters input,
    .mobilo-auth-logs-filters select {
        margin: 0;
    }

    .log-level-badge {
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .level-info {
        background: #d9edf7;
        color: #31708f;
    }

    .level-warning {
        background: #fcf8e3;
        color: #8a6d3b;
    }

    .level-error {
        background: #f2dede;
        color: #a94442;
    }

    .level-debug {
        background: #dff0d8;
        color: #3c763d;
    }

    .log-action-badge {
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        background: #e1e1e1;
        color: #666;
    }

    .action-login {
        background: #dff0d8;
        color: #3c763d;
    }

    .action-logout {
        background: #f2dede;
        color: #a94442;
    }

    .action-register {
        background: #d9edf7;
        color: #31708f;
    }

    .action-password_reset {
        background: #fcf8e3;
        color: #8a6d3b;
    }

    .action-token_validation {
        background: #e8e8e8;
        color: #555;
    }

    .log-message {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .text-muted {
        color: #999;
        font-style: italic;
    }

    .no-items {
        text-align: center;
        color: #666;
        font-style: italic;
        padding: 20px;
    }

    /* Modal Styles */
    .mobilo-modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .mobilo-modal-content {
        background-color: #fff;
        margin: 5% auto;
        padding: 0;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        width: 80%;
        max-width: 800px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .mobilo-modal-header {
        padding: 20px;
        border-bottom: 1px solid #ccd0d4;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .mobilo-modal-header h2 {
        margin: 0;
    }

    .mobilo-modal-close {
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
        color: #666;
    }

    .mobilo-modal-close:hover {
        color: #000;
    }

    .mobilo-modal-body {
        padding: 20px;
    }

    .log-details-json {
        background: #f8f8f8;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 15px;
        font-family: monospace;
        font-size: 12px;
        white-space: pre-wrap;
        max-height: 400px;
        overflow-y: auto;
    }
</style>

<script>
    jQuery(document).ready(function ($) {
        // Log search functionality
        $('#log-search').on('keyup', function () {
            const searchTerm = $(this).val().toLowerCase();
            $('#logs-table tbody tr').each(function () {
                const rowText = $(this).text().toLowerCase();
                $(this).toggle(rowText.indexOf(searchTerm) > -1);
            });
        });

        // Level filter
        $('#log-level-filter').on('change', function () {
            const level = $(this).val();
            $('#logs-table tbody tr').each(function () {
                if (level === '') {
                    $(this).show();
                } else {
                    $(this).toggle($(this).hasClass('log-level-' + level));
                }
            });
        });

        // Action filter
        $('#log-action-filter').on('change', function () {
            const action = $(this).val();
            $('#logs-table tbody tr').each(function () {
                if (action === '') {
                    $(this).show();
                } else {
                    const hasAction = $(this).find('.log-action-badge').hasClass('action-' + action);
                    $(this).toggle(hasAction);
                }
            });
        });

        // Date filter
        $('#log-date-filter').on('change', function () {
            const selectedDate = $(this).val();
            $('#logs-table tbody tr').each(function () {
                if (selectedDate === '') {
                    $(this).show();
                } else {
                    const rowDate = $(this).find('td:first').text().split(' ')[0];
                    const formattedDate = new Date(rowDate).toISOString().split('T')[0];
                    $(this).toggle(formattedDate === selectedDate);
                }
            });
        });

        // View log details
        $('.view-log-details').on('click', function (e) {
            e.preventDefault();
            const logId = $(this).data('log-id');

            // AJAX call to get log details
            $.post(ajaxurl, {
                action: 'mobilo_auth_get_log_details',
                log_id: logId,
                nonce: mobiloAuthAdmin.nonce
            }, function (response) {
                if (response.success) {
                    $('#log-details-content').html(response.data);
                    $('#log-details-modal').show();
                } else {
                    alert('<?php _e('Error loading log details: ', 'mobilo-auth'); ?>' + response.data);
                }
            });
        });

        // Close modal
        $('.mobilo-modal-close').on('click', function () {
            $('#log-details-modal').hide();
        });

        // Close modal when clicking outside
        $(window).on('click', function (e) {
            if (e.target.id === 'log-details-modal') {
                $('#log-details-modal').hide();
            }
        });

        // Refresh logs
        $('#refresh-logs').on('click', function () {
            location.reload();
        });

        // Export logs
        $('#export-logs').on('click', function () {
            // AJAX call to export logs
            $.post(ajaxurl, {
                action: 'mobilo_auth_export_logs',
                nonce: mobiloAuthAdmin.nonce
            }, function (response) {
                if (response.success) {
                    // Create download link
                    const blob = new Blob([response.data], { type: 'text/csv' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'mobilo-auth-logs.csv';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                } else {
                    alert('<?php _e('Error exporting logs: ', 'mobilo-auth'); ?>' + response.data);
                }
            });
        });
    });
</script>