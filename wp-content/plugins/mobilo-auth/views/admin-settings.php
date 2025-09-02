<?php
/**
 * Admin Settings View
 *
 * @package MobiloAuth
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap mobilo-auth-settings">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <form method="post" action="options.php">
        <?php
        settings_fields('mobilo_auth_settings');
        do_settings_sections('mobilo_auth_settings');
        submit_button();
        ?>
    </form>

    <div class="mobilo-auth-test-connection">
        <h2><?php _e('Test Firebase Connection', 'mobilo-auth'); ?></h2>
        <p><?php _e('Test your Firebase configuration to ensure everything is working correctly.', 'mobilo-auth'); ?>
        </p>
        <button type="button" class="button button-secondary" id="test-firebase-connection">
            <?php _e('Test Connection', 'mobilo-auth'); ?>
        </button>
        <div id="connection-test-result"></div>
    </div>
</div>

<style>
    .mobilo-auth-settings {
        margin: 20px 0;
    }

    .mobilo-auth-test-connection {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        margin: 20px 0;
    }

    #connection-test-result {
        margin-top: 15px;
        padding: 10px;
        border-radius: 4px;
    }

    .connection-success {
        background: #dff0d8;
        border: 1px solid #d6e9c6;
        color: #3c763d;
    }

    .connection-error {
        background: #f2dede;
        border: 1px solid #ebccd1;
        color: #a94442;
    }
</style>