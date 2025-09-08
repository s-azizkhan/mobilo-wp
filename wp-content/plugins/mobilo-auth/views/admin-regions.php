<?php
/**
 * Admin Regions View
 *
 * @package MobiloAuth
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap mobilo-auth-regions">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="mobilo-auth-regions-header">
        <div class="regions-stats">
            <span class="stat-item">
                <strong><?php echo count($regions); ?></strong> <?php _e('Active Regions', 'mobilo-auth'); ?>
            </span>
        </div>
        <div class="regions-actions">
            <button type="button" class="button button-primary" id="refresh-regions">
                <?php _e('Refresh', 'mobilo-auth'); ?>
            </button>
            <button type="button" class="button button-secondary" id="add-region">
                <?php _e('Add Region', 'mobilo-auth'); ?>
            </button>
        </div>
    </div>

    <div class="mobilo-auth-regions-info">
        <div class="notice notice-info">
            <p>
                <strong><?php _e('Firebase Regions', 'mobilo-auth'); ?></strong><br>
                <?php _e('Firebase regions are geographic locations where your Firebase project data is stored. Each region has different latency and availability characteristics.', 'mobilo-auth'); ?>
            </p>
        </div>
    </div>

    <table class="wp-list-table widefat fixed striped" id="regions-table">
        <thead>
            <tr>
                <th><?php _e('Region ID', 'mobilo-auth'); ?></th>
                <th><?php _e('Region Name', 'mobilo-auth'); ?></th>
                <th><?php _e('Location', 'mobilo-auth'); ?></th>
                <th><?php _e('Status', 'mobilo-auth'); ?></th>
                <th><?php _e('Latency', 'mobilo-auth'); ?></th>
                <th><?php _e('Last Checked', 'mobilo-auth'); ?></th>
                <th><?php _e('Actions', 'mobilo-auth'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($regions)): ?>
                <?php foreach ($regions as $region): ?>
                    <tr data-region-id="<?php echo esc_attr($region->id); ?>">
                        <td>
                            <code><?php echo esc_html($region->region_id); ?></code>
                        </td>
                        <td>
                            <strong><?php echo esc_html($region->name); ?></strong>
                        </td>
                        <td>
                            <?php echo esc_html($region->location); ?>
                            <?php if ($region->country): ?>
                                <br><small><?php echo esc_html($region->country); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $region->is_active ? 'active' : 'inactive'; ?>">
                                <?php echo $region->is_active ? __('Active', 'mobilo-auth') : __('Inactive', 'mobilo-auth'); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($region->latency): ?>
                                <span
                                    class="latency-indicator latency-<?php echo $region->latency <= 100 ? 'good' : ($region->latency <= 300 ? 'medium' : 'poor'); ?>">
                                    <?php echo esc_html($region->latency); ?>ms
                                </span>
                            <?php else: ?>
                                <span class="text-muted"><?php _e('Unknown', 'mobilo-auth'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($region->last_checked): ?>
                                <?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($region->last_checked))); ?>
                            <?php else: ?>
                                <span class="text-muted"><?php _e('Never', 'mobilo-auth'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="row-actions">
                                <span class="test">
                                    <a href="#" class="test-region" data-region-id="<?php echo esc_attr($region->id); ?>">
                                        <?php _e('Test', 'mobilo-auth'); ?>
                                    </a>
                                </span>
                                <span class="edit">
                                    <a href="#" class="edit-region" data-region-id="<?php echo esc_attr($region->id); ?>">
                                        <?php _e('Edit', 'mobilo-auth'); ?>
                                    </a>
                                </span>
                                <?php if ($region->is_active): ?>
                                    <span class="deactivate">
                                        <a href="#" class="deactivate-region" data-region-id="<?php echo esc_attr($region->id); ?>">
                                            <?php _e('Deactivate', 'mobilo-auth'); ?>
                                        </a>
                                    </span>
                                <?php else: ?>
                                    <span class="activate">
                                        <a href="#" class="activate-region" data-region-id="<?php echo esc_attr($region->id); ?>">
                                            <?php _e('Activate', 'mobilo-auth'); ?>
                                        </a>
                                    </span>
                                <?php endif; ?>
                                <span class="delete">
                                    <a href="#" class="delete-region" data-region-id="<?php echo esc_attr($region->id); ?>">
                                        <?php _e('Delete', 'mobilo-auth'); ?>
                                    </a>
                                </span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="no-items">
                        <?php _e('No Firebase regions configured.', 'mobilo-auth'); ?>
                        <br>
                        <a href="#" id="add-first-region" class="button button-primary" style="margin-top: 10px;">
                            <?php _e('Add Your First Region', 'mobilo-auth'); ?>
                        </a>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Add/Edit Region Modal -->
    <div id="region-modal" class="mobilo-modal" style="display: none;">
        <div class="mobilo-modal-content">
            <div class="mobilo-modal-header">
                <h2 id="region-modal-title"><?php _e('Add Region', 'mobilo-auth'); ?></h2>
                <span class="mobilo-modal-close">&times;</span>
            </div>
            <div class="mobilo-modal-body">
                <form id="region-form">
                    <input type="hidden" id="region-id" name="region_id" value="">

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="region-region-id"><?php _e('Region ID', 'mobilo-auth'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="region-region-id" name="region_id" class="regular-text" required>
                                <p class="description">
                                    <?php _e('The Firebase region identifier (e.g., us-central1, europe-west1).', 'mobilo-auth'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="region-name"><?php _e('Region Name', 'mobilo-auth'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="region-name" name="name" class="regular-text" required>
                                <p class="description">
                                    <?php _e('A friendly name for this region.', 'mobilo-auth'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="region-location"><?php _e('Location', 'mobilo-auth'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="region-location" name="location" class="regular-text" required>
                                <p class="description">
                                    <?php _e('The geographic location of this region.', 'mobilo-auth'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="region-country"><?php _e('Country', 'mobilo-auth'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="region-country" name="country" class="regular-text">
                                <p class="description">
                                    <?php _e('The country where this region is located.', 'mobilo-auth'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="region-active"><?php _e('Status', 'mobilo-auth'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" id="region-active" name="is_active" value="1" checked>
                                    <?php _e('Active', 'mobilo-auth'); ?>
                                </label>
                                <p class="description">
                                    <?php _e('Whether this region is currently active and available for use.', 'mobilo-auth'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <div class="mobilo-modal-footer">
                        <button type="submit" class="button button-primary">
                            <?php _e('Save Region', 'mobilo-auth'); ?>
                        </button>
                        <button type="button" class="button button-secondary" id="cancel-region">
                            <?php _e('Cancel', 'mobilo-auth'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .mobilo-auth-regions {
        margin: 20px 0;
    }

    .mobilo-auth-regions-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 15px;
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
    }

    .regions-stats .stat-item {
        font-size: 14px;
        color: #666;
    }

    .regions-actions {
        display: flex;
        gap: 10px;
    }

    .mobilo-auth-regions-info {
        margin-bottom: 20px;
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

    .latency-indicator {
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
    }

    .latency-good {
        background: #dff0d8;
        color: #3c763d;
    }

    .latency-medium {
        background: #fcf8e3;
        color: #8a6d3b;
    }

    .latency-poor {
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

    .mobilo-modal-footer {
        padding: 20px;
        border-top: 1px solid #ccd0d4;
        text-align: right;
    }

    .mobilo-modal-footer .button {
        margin-left: 10px;
    }
</style>

<script>
    jQuery(document).ready(function ($) {
        // Add region
        $('#add-region, #add-first-region').on('click', function (e) {
            e.preventDefault();
            $('#region-modal-title').text('<?php _e('Add Region', 'mobilo-auth'); ?>');
            $('#region-form')[0].reset();
            $('#region-id').val('');
            $('#region-modal').show();
        });

        // Edit region
        $('.edit-region').on('click', function (e) {
            e.preventDefault();
            const regionId = $(this).data('region-id');

            // AJAX call to get region details
            $.post(ajaxurl, {
                action: 'mobilo_auth_get_region_details',
                region_id: regionId,
                nonce: mobiloAuthAdmin.nonce
            }, function (response) {
                if (response.success) {
                    const region = response.data;
                    $('#region-modal-title').text('<?php _e('Edit Region', 'mobilo-auth'); ?>');
                    $('#region-id').val(region.id);
                    $('#region-region-id').val(region.region_id);
                    $('#region-name').val(region.name);
                    $('#region-location').val(region.location);
                    $('#region-country').val(region.country);
                    $('#region-active').prop('checked', region.is_active == 1);
                    $('#region-modal').show();
                } else {
                    alert('<?php _e('Error loading region details: ', 'mobilo-auth'); ?>' + response.data);
                }
            });
        });

        // Test region
        $('.test-region').on('click', function (e) {
            e.preventDefault();
            const regionId = $(this).data('region-id');

            // AJAX call to test region
            $.post(ajaxurl, {
                action: 'mobilo_auth_test_region',
                region_id: regionId,
                nonce: mobiloAuthAdmin.nonce
            }, function (response) {
                if (response.success) {
                    alert('<?php _e('Region test completed successfully!', 'mobilo-auth'); ?>');
                    location.reload();
                } else {
                    alert('<?php _e('Region test failed: ', 'mobilo-auth'); ?>' + response.data);
                }
            });
        });

        // Save region
        $('#region-form').on('submit', function (e) {
            e.preventDefault();

            const formData = {
                action: 'mobilo_auth_save_region',
                nonce: mobiloAuthAdmin.nonce,
                region_id: $('#region-id').val(),
                region_region_id: $('#region-region-id').val(),
                name: $('#region-name').val(),
                location: $('#region-location').val(),
                country: $('#region-country').val(),
                is_active: $('#region-active').is(':checked') ? 1 : 0
            };

            $.post(ajaxurl, formData, function (response) {
                if (response.success) {
                    alert('<?php _e('Region saved successfully!', 'mobilo-auth'); ?>');
                    $('#region-modal').hide();
                    location.reload();
                } else {
                    alert('<?php _e('Error saving region: ', 'mobilo-auth'); ?>' + response.data);
                }
            });
        });

        // Close modal
        $('.mobilo-modal-close, #cancel-region').on('click', function () {
            $('#region-modal').hide();
        });

        // Close modal when clicking outside
        $(window).on('click', function (e) {
            if (e.target.id === 'region-modal') {
                $('#region-modal').hide();
            }
        });

        // Refresh regions
        $('#refresh-regions').on('click', function () {
            location.reload();
        });
    });
</script>