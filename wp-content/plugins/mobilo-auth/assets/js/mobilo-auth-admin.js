/**
 * Mobilo Auth Admin JavaScript
 *
 * @package MobiloAuth
 * @since 1.0.0
 */

(function ($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function () {
        MobiloAuthAdmin.init();
    });

    // Main admin plugin object
    var MobiloAuthAdmin = {

        // Initialize the plugin
        init: function () {
            this.bindEvents();
            this.setupTabs();
            this.setupTooltips();
        },

        // Bind event handlers
        bindEvents: function () {
            $(document).on('click', '#test-firebase-connection', this.testFirebaseConnection);
            $(document).on('click', '.mobilo-auth-sync-users', this.syncUsers);
            $(document).on('click', '.mobilo-auth-clear-logs', this.clearLogs);
            $(document).on('click', '.mobilo-auth-export-data', this.exportData);
            $(document).on('click', '.mobilo-auth-import-data', this.importData);
            $(document).on('change', '.mobilo-auth-region-selector', this.changeRegion);
            $(document).on('submit', '.mobilo-auth-bulk-action-form', this.handleBulkActions);
        },

        // Setup tab navigation
        setupTabs: function () {
            $('.mobilo-auth-tabs-nav a').on('click', function (e) {
                e.preventDefault();

                var target = $(this).attr('href');
                var $tab = $(target);

                if ($tab.length) {
                    $('.mobilo-auth-tab-content').hide();
                    $('.mobilo-auth-tabs-nav a').removeClass('active');

                    $tab.show();
                    $(this).addClass('active');

                    // Update URL hash
                    if (history.pushState) {
                        history.pushState(null, null, target);
                    }
                }
            });

            // Show first tab by default
            $('.mobilo-auth-tabs-nav a:first').click();
        },

        // Setup tooltips
        setupTooltips: function () {
            $('.mobilo-auth-tooltip').each(function () {
                var $element = $(this);
                var tooltipText = $element.data('tooltip');

                if (tooltipText) {
                    $element.attr('title', tooltipText);
                }
            });
        },

        // Test Firebase connection
        testFirebaseConnection: function (e) {
            e.preventDefault();

            var $button = $(this);
            var $result = $('#connection-test-result');
            var originalText = $button.text();

            // Show loading state
            $button.prop('disabled', true).text('Testing...');
            $result.removeClass('connection-success connection-error').html('');

            // Send test request
            $.post(mobiloAuthAdmin.ajaxUrl, {
                action: 'mobilo_auth_admin_test_connection',
                nonce: mobiloAuthAdmin.nonce
            })
                .done(function (response) {
                    if (response.success) {
                        $result.addClass('connection-success').html(
                            '<strong>Success!</strong> Firebase connection is working properly.'
                        );
                    } else {
                        $result.addClass('connection-error').html(
                            '<strong>Error:</strong> ' + (response.data.message || 'Connection failed')
                        );
                    }
                })
                .fail(function () {
                    $result.addClass('connection-error').html(
                        '<strong>Error:</strong> Request failed. Please try again.'
                    );
                })
                .always(function () {
                    $button.prop('disabled', false).text(originalText);
                });
        },

        // Sync users from Firebase
        syncUsers: function (e) {
            e.preventDefault();

            if (!confirm('This will synchronize all users from Firebase. Continue?')) {
                return;
            }

            var $button = $(this);
            var originalText = $button.text();

            // Show loading state
            $button.prop('disabled', true).text('Syncing...');

            // Send sync request
            $.post(mobiloAuthAdmin.ajaxUrl, {
                action: 'mobilo_auth_admin_sync_users',
                nonce: mobiloAuthAdmin.nonce
            })
                .done(function (response) {
                    if (response.success) {
                        alert('User synchronization completed successfully!');
                        if (response.data.reload_page) {
                            window.location.reload();
                        }
                    } else {
                        alert('User synchronization failed: ' + (response.data.message || 'Unknown error'));
                    }
                })
                .fail(function () {
                    alert('User synchronization request failed. Please try again.');
                })
                .always(function () {
                    $button.prop('disabled', false).text(originalText);
                });
        },

        // Clear logs
        clearLogs: function (e) {
            e.preventDefault();

            if (!confirm('This will clear all authentication logs. This action cannot be undone. Continue?')) {
                return;
            }

            var $button = $(this);
            var originalText = $button.text();

            // Show loading state
            $button.prop('disabled', true).text('Clearing...');

            // Send clear request
            $.post(mobiloAuthAdmin.ajaxUrl, {
                action: 'mobilo_auth_admin_clear_logs',
                nonce: mobiloAuthAdmin.nonce
            })
                .done(function (response) {
                    if (response.success) {
                        alert('Logs cleared successfully!');
                        if (response.data.reload_page) {
                            window.location.reload();
                        }
                    } else {
                        alert('Failed to clear logs: ' + (response.data.message || 'Unknown error'));
                    }
                })
                .fail(function () {
                    alert('Clear logs request failed. Please try again.');
                })
                .always(function () {
                    $button.prop('disabled', false).text(originalText);
                });
        },

        // Export data
        exportData: function (e) {
            e.preventDefault();

            var $button = $(this);
            var originalText = $button.text();

            // Show loading state
            $button.prop('disabled', true).text('Exporting...');

            // Create download link
            var downloadUrl = mobiloAuthAdmin.ajaxUrl + '?' + $.param({
                action: 'mobilo_auth_admin_export_data',
                nonce: mobiloAuthAdmin.nonce,
                format: 'json'
            });

            // Trigger download
            var $link = $('<a>', {
                href: downloadUrl,
                download: 'mobilo-auth-export-' + new Date().toISOString().split('T')[0] + '.json'
            });

            $('body').append($link);
            $link[0].click();
            $link.remove();

            // Reset button
            setTimeout(function () {
                $button.prop('disabled', false).text(originalText);
            }, 1000);
        },

        // Import data
        importData: function (e) {
            e.preventDefault();

            var $button = $(this);
            var $fileInput = $('<input type="file" accept=".json" style="display: none;">');

            $fileInput.on('change', function () {
                var file = this.files[0];
                if (!file) return;

                var reader = new FileReader();
                reader.onload = function (e) {
                    try {
                        var data = JSON.parse(e.target.result);
                        MobiloAuthAdmin.processImportData(data, $button);
                    } catch (error) {
                        alert('Invalid file format. Please select a valid JSON file.');
                        $button.prop('disabled', false).text('Import Data');
                    }
                };
                reader.readAsText(file);
            });

            $fileInput.click();
        },

        // Process imported data
        processImportData: function (data, $button) {
            if (!confirm('This will import data and may overwrite existing settings. Continue?')) {
                $button.prop('disabled', false).text('Import Data');
                return;
            }

            $button.prop('disabled', true).text('Importing...');

            $.post(mobiloAuthAdmin.ajaxUrl, {
                action: 'mobilo_auth_admin_import_data',
                nonce: mobiloAuthAdmin.nonce,
                data: JSON.stringify(data)
            })
                .done(function (response) {
                    if (response.success) {
                        alert('Data imported successfully!');
                        if (response.data.reload_page) {
                            window.location.reload();
                        }
                    } else {
                        alert('Data import failed: ' + (response.data.message || 'Unknown error'));
                    }
                })
                .fail(function () {
                    alert('Data import request failed. Please try again.');
                })
                .always(function () {
                    $button.prop('disabled', false).text('Import Data');
                });
        },

        // Change region
        changeRegion: function (e) {
            var region = $(this).val();
            var $regionContent = $('.mobilo-auth-region-content');

            if (region) {
                $regionContent.show();
                $regionContent.find('[data-region]').hide();
                $regionContent.find('[data-region="' + region + '"]').show();
            } else {
                $regionContent.hide();
            }
        },

        // Handle bulk actions
        handleBulkActions: function (e) {
            var action = $('#bulk-action-selector-top').val();
            var selectedUsers = $('input[name="user_ids[]"]:checked');

            if (action === '-1') {
                alert('Please select an action.');
                e.preventDefault();
                return;
            }

            if (selectedUsers.length === 0) {
                alert('Please select at least one user.');
                e.preventDefault();
                return;
            }

            if (!confirm('Are you sure you want to perform this action on ' + selectedUsers.length + ' user(s)?')) {
                e.preventDefault();
                return;
            }
        },

        // Show notification
        showNotification: function (message, type) {
            var $notification = $('<div class="mobilo-auth-notification mobilo-auth-notification-' + type + '">' + message + '</div>');

            $('body').append($notification);

            // Auto-hide after 5 seconds
            setTimeout(function () {
                $notification.fadeOut(function () {
                    $(this).remove();
                });
            }, 5000);
        },

        // Format date
        formatDate: function (dateString) {
            var date = new Date(dateString);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
        },

        // Format file size
        formatFileSize: function (bytes) {
            if (bytes === 0) return '0 Bytes';

            var k = 1024;
            var sizes = ['Bytes', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));

            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    };

})(jQuery);
