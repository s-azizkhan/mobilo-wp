<?php
/**
 * Admin Users View
 *
 * @package MobiloAuth
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap mobilo-auth-users">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="mobilo-auth-users-header">
        <div class="users-stats">
            <span class="stat-item">
                <strong><?php echo count($users); ?></strong> <?php _e('Total Users', 'mobilo-auth'); ?>
            </span>
        </div>
        <div class="users-actions">
            <button type="button" class="button button-primary" id="refresh-users">
                <?php _e('Refresh', 'mobilo-auth'); ?>
            </button>
            <button type="button" class="button button-secondary" id="sync-users">
                <?php _e('Sync with Firebase', 'mobilo-auth'); ?>
            </button>
        </div>
    </div>

    <div class="mobilo-auth-users-filters">
        <input type="text" id="user-search" placeholder="<?php _e('Search users...', 'mobilo-auth'); ?>"
            class="regular-text">
        <select id="user-status-filter">
            <option value=""><?php _e('All Status', 'mobilo-auth'); ?></option>
            <option value="active"><?php _e('Active', 'mobilo-auth'); ?></option>
            <option value="inactive"><?php _e('Inactive', 'mobilo-auth'); ?></option>
        </select>
    </div>

    <table class="wp-list-table widefat fixed striped" id="users-table">
        <thead>
            <tr>
                <th><?php _e('User ID', 'mobilo-auth'); ?></th>
                <th><?php _e('Email', 'mobilo-auth'); ?></th>
                <th><?php _e('Display Name', 'mobilo-auth'); ?></th>
                <th><?php _e('Firebase UID', 'mobilo-auth'); ?></th>
                <th><?php _e('Status', 'mobilo-auth'); ?></th>
                <th><?php _e('Last Login', 'mobilo-auth'); ?></th>
                <th><?php _e('Created', 'mobilo-auth'); ?></th>
                <th><?php _e('Actions', 'mobilo-auth'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr data-user-id="<?php echo esc_attr($user->id); ?>">
                        <td><?php echo esc_html($user->id); ?></td>
                        <td>
                            <strong><?php echo esc_html($user->email); ?></strong>
                        </td>
                        <td><?php echo esc_html($user->display_name); ?></td>
                        <td>
                            <code><?php echo esc_html($user->firebase_uid); ?></code>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $user->is_active ? 'active' : 'inactive'; ?>">
                                <?php echo $user->is_active ? __('Active', 'mobilo-auth') : __('Inactive', 'mobilo-auth'); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user->last_login): ?>
                                <?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($user->last_login))); ?>
                            <?php else: ?>
                                <span class="text-muted"><?php _e('Never', 'mobilo-auth'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo esc_html(wp_date(get_option('date_format'), strtotime($user->created_at))); ?>
                        </td>
                        <td>
                            <div class="row-actions">
                                <span class="view">
                                    <a href="#" class="view-user" data-user-id="<?php echo esc_attr($user->id); ?>">
                                        <?php _e('View', 'mobilo-auth'); ?>
                                    </a>
                                </span>
                                <span class="edit">
                                    <a href="#" class="edit-user" data-user-id="<?php echo esc_attr($user->id); ?>">
                                        <?php _e('Edit', 'mobilo-auth'); ?>
                                    </a>
                                </span>
                                <?php if ($user->is_active): ?>
                                    <span class="deactivate">
                                        <a href="#" class="deactivate-user" data-user-id="<?php echo esc_attr($user->id); ?>">
                                            <?php _e('Deactivate', 'mobilo-auth'); ?>
                                        </a>
                                    </span>
                                <?php else: ?>
                                    <span class="activate">
                                        <a href="#" class="activate-user" data-user-id="<?php echo esc_attr($user->id); ?>">
                                            <?php _e('Activate', 'mobilo-auth'); ?>
                                        </a>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="no-items">
                        <?php _e('No Firebase users found.', 'mobilo-auth'); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- User Details Modal -->
    <div id="user-details-modal" class="mobilo-modal" style="display: none;">
        <div class="mobilo-modal-content">
            <div class="mobilo-modal-header">
                <h2><?php _e('User Details', 'mobilo-auth'); ?></h2>
                <span class="mobilo-modal-close">&times;</span>
            </div>
            <div class="mobilo-modal-body" id="user-details-content">
                <!-- User details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<style>
    .mobilo-auth-users {
        margin: 20px 0;
    }

    .mobilo-auth-users-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 15px;
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
    }

    .users-stats .stat-item {
        font-size: 14px;
        color: #666;
    }

    .users-actions {
        display: flex;
        gap: 10px;
    }

    .mobilo-auth-users-filters {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        padding: 15px;
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
    }

    .mobilo-auth-users-filters input,
    .mobilo-auth-users-filters select {
        margin: 0;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-active {
        background: #dff0d8;
        color: #3c763d;
    }

    .status-inactive {
        background: #f2dede;
        color: #a94442;
    }

    .row-actions {
        color: #666;
    }

    .row-actions span {
        margin-right: 8px;
    }

    .row-actions a {
        text-decoration: none;
    }

    .row-actions a:hover {
        text-decoration: underline;
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
        max-width: 600px;
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
</style>

<script>
    jQuery(document).ready(function ($) {
        // User search functionality
        $('#user-search').on('keyup', function () {
            const searchTerm = $(this).val().toLowerCase();
            $('#users-table tbody tr').each(function () {
                const rowText = $(this).text().toLowerCase();
                $(this).toggle(rowText.indexOf(searchTerm) > -1);
            });
        });

        // Status filter
        $('#user-status-filter').on('change', function () {
            const status = $(this).val();
            $('#users-table tbody tr').each(function () {
                if (status === '') {
                    $(this).show();
                } else {
                    const hasStatus = $(this).find('.status-badge').hasClass('status-' + status);
                    $(this).toggle(hasStatus);
                }
            });
        });

        // View user details
        $('.view-user').on('click', function (e) {
            e.preventDefault();
            const userId = $(this).data('user-id');

            // AJAX call to get user details
            $.post(ajaxurl, {
                action: 'mobilo_auth_get_user_details',
                user_id: userId,
                nonce: mobiloAuthAdmin.nonce
            }, function (response) {
                if (response.success) {
                    $('#user-details-content').html(response.data);
                    $('#user-details-modal').show();
                } else {
                    alert('<?php _e('Error loading user details: ', 'mobilo-auth'); ?>' + response.data);
                }
            });
        });

        // Close modal
        $('.mobilo-modal-close').on('click', function () {
            $('#user-details-modal').hide();
        });

        // Close modal when clicking outside
        $(window).on('click', function (e) {
            if (e.target.id === 'user-details-modal') {
                $('#user-details-modal').hide();
            }
        });

        // Refresh users
        $('#refresh-users').on('click', function () {
            location.reload();
        });

        // Sync users
        $('#sync-users').on('click', function () {
            if (confirm('<?php _e('Are you sure you want to sync users with Firebase?', 'mobilo-auth'); ?>')) {
                // AJAX call to sync users
                $.post(ajaxurl, {
                    action: 'mobilo_auth_sync_users',
                    nonce: mobiloAuthAdmin.nonce
                }, function (response) {
                    if (response.success) {
                        alert('<?php _e('Users synced successfully!', 'mobilo-auth'); ?>');
                        location.reload();
                    } else {
                        alert('<?php _e('Error syncing users: ', 'mobilo-auth'); ?>' + response.data);
                    }
                });
            }
        });
    });
</script>