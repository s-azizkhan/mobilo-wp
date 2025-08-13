<?php
use GithubDeployer\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1>
		<?php echo esc_attr__( 'Miscellaneous', 'github-deployer' ); ?>
	</h1>

	<?php
	if ( isset( $regenerate_secret_key_result ) && $regenerate_secret_key_result !== null ) {
		if ( $regenerate_secret_key_result === false ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_attr__( 'Error while regenerating secret key.', 'github-deployer' ) . '</p></div>';
		} else {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_attr__( 'Secret key has been successfully regenerated.', 'github-deployer' ) . '</p></div>';
		}
	}
	?>

	<?php
	if ( isset( $flush_cache_result ) && $flush_cache_result !== null ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_attr__( 'Cache setting has been updated.', 'github-deployer' ) . '</p></div>';
	}
	?>

	<?php
	if ( isset( $alert_notification_result ) && $alert_notification_result !== null ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_attr__( 'Alert notification setting has been updated.', 'github-deployer' ) . '</p></div>';
	}
	?>

	<div class="gd_form_box">
		<h3><span class="dashicons dashicons-admin-network"></span> <?php echo esc_attr__( 'Secret Key management', 'github-deployer' ); ?></h3>

		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<?php echo esc_attr__( 'Current secret key:', 'github-deployer' ); ?>
				</th>
				<td>
					<span><?php echo esc_attr( Helper::get_api_secret() ); ?></span>
				</td>
			</tr>
		</table>

		<form method="post" action="" onsubmit="return confirm( '<?php echo esc_attr__( 'Are you sure?', 'github-deployer' ); ?>' );">
			<input type="hidden" name="action" value="regenerate_secret_key">
			<?php wp_nonce_field( GD_SLUG . '_regenerate_secret_key', GD_SLUG . '_nonce' ); ?>

			<input type="submit" class="button button-primary" value="<?php echo esc_attr__( 'Regenerate Secret Key', 'github-deployer' ); ?>">
		</form>
	</div>

	<div class="gd_form_box">
		<h3><span class="dashicons dashicons-database"></span> <?php echo esc_attr__( 'Flush cache', 'github-deployer' ); ?></h3>

		<p class="description">
			<?php echo esc_attr__( 'Activate this option if you want the plugin to clear the cache every time the package update link is triggered. We support next list of plugins:', 'github-deployer' ); ?>
		<ul>
			<li>
				WP Rocket |
				<?php if ( Helper::wp_rocket_activated() ) : ?>
					<span class="wp-ui-text-highlight">
						<?php echo esc_attr__( 'Active plugin found', 'github-deployer' ); ?>
					</span>
				<?php else : ?>
					<span class="wp-ui-text-notification">
						<?php echo esc_attr__( 'No plugin detected', 'github-deployer' ); ?>
					</span>
				<?php endif; ?>
			</li>
			<li>
				WP-Optimize |
				<?php if ( Helper::wp_optimize_activated() ) : ?>
					<span class="wp-ui-text-highlight">
						<?php echo esc_attr__( 'Active plugin found', 'github-deployer' ); ?>
					</span>
				<?php else : ?>
					<span class="wp-ui-text-notification">
						<?php echo esc_attr__( 'No plugin detected', 'github-deployer' ); ?>
					</span>
				<?php endif; ?>
			</li>
			<li>W3 Total Cache |
				<?php if ( Helper::w3tc_activated() ) : ?>
					<span class="wp-ui-text-highlight">
						<?php echo esc_attr__( 'Active plugin found', 'github-deployer' ); ?>
					</span>
				<?php else : ?>
					<span class="wp-ui-text-notification">
						<?php echo esc_attr__( 'No plugin detected', 'github-deployer' ); ?>
					</span>
				<?php endif; ?>
			</li>
			<li>
				LiteSpeed Cache |
				<?php if ( Helper::litespeed_cache_activated() ) : ?>
					<span class="wp-ui-text-highlight">
						<?php echo esc_attr__( 'Active plugin found', 'github-deployer' ); ?>
					</span>
				<?php else : ?>
					<span class="wp-ui-text-notification">
						<?php echo esc_attr__( 'No plugin detected', 'github-deployer' ); ?>
					</span>
				<?php endif; ?>
			</li>
			<li>
				WP Super Cache |
				<?php if ( Helper::wp_super_cache_activated() ) : ?>
					<span class="wp-ui-text-highlight">
						<?php echo esc_attr__( 'Active plugin found', 'github-deployer' ); ?>
					</span>
				<?php else : ?>
					<span class="wp-ui-text-notification">
						<?php echo esc_attr__( 'No plugin detected', 'github-deployer' ); ?>
					</span>
				<?php endif; ?>
			</li>
			<li>
				WP Fastest Cache |
				<?php if ( Helper::wp_fastest_cache_activated() ) : ?>
					<span class="wp-ui-text-highlight">
						<?php echo esc_attr__( 'Active plugin found', 'github-deployer' ); ?>
					</span>
				<?php else : ?>
					<span class="wp-ui-text-notification">
						<?php echo esc_attr__( 'No plugin detected', 'github-deployer' ); ?>
					</span>
				<?php endif; ?>
			</li>
			<li>
				Autoptimize |
				<?php if ( Helper::autoptimize_activated() ) : ?>
					<span class="wp-ui-text-highlight">
						<?php echo esc_attr__( 'Active plugin found', 'github-deployer' ); ?>
					</span>
				<?php else : ?>
					<span class="wp-ui-text-notification">
						<?php echo esc_attr__( 'No plugin detected', 'github-deployer' ); ?>
					</span>
				<?php endif; ?>
			</li>
		</ul>
		</p>

		<form method="post" action="">
			<input type="hidden" name="action" value="flush_cache">
			<?php wp_nonce_field( GD_SLUG . '_flush_cache', GD_SLUG . '_nonce' ); ?>

			<table class="form-table">
				<tr valign="top">
					<th scope="row">
						<?php echo esc_attr__( 'Enable', 'github-deployer' ); ?>
					</th>
					<td>
						<input type="checkbox" name="flush_cache_setting" <?php echo $data_manager->get_flush_cache_setting() === true ? 'checked' : ''; ?> value="1">
					</td>
				</tr>
			</table>

			<p class="submit">
				<input type="submit" class="button-primary" value="<?php echo esc_attr__( 'Save Caching Settings', 'github-deployer' ); ?>" />
			</p>
		</form>
	</div>

	<div class="gd_form_box">
		<h3><span class="dashicons dashicons-feedback"></span> <?php echo esc_attr__( 'Alert notification', 'github-deployer' ); ?></h3>

		<p class="description">
			<?php echo esc_attr__( 'Enable this option if you wish to display a notification message at the top of WordPress Admin interface (/wp-admin). The message is intended for developers, reminding them not to make any changes to the theme or plugin directly on this site, but rather use Git for such modifications.', 'github-deployer' ); ?>
		</p>

		<form method="post" action="">
			<input type="hidden" name="action" value="alert_notification">
			<?php wp_nonce_field( GD_SLUG . '_alert_notification', GD_SLUG . '_nonce' ); ?>

			<table class="form-table">
				<tr valign="top">
					<th scope="row">
						<?php echo esc_attr__( 'Enable', 'github-deployer' ); ?>
					</th>
					<td>
						<input type="checkbox" name="alert_notification_setting" <?php echo $data_manager->get_alert_notification_setting() === true ? 'checked' : ''; ?> value="1">
					</td>
				</tr>
			</table>

			<p class="submit">
				<input type="submit" class="button-primary" value="<?php echo esc_attr__( 'Save Alert Notification Settings', 'github-deployer' ); ?>" />
			</p>
		</form>
	</div>

</div>
