<?php
use GithubDeployer\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1>
		<?php echo esc_attr__( 'Logs', 'github-deployer' ); ?>
	</h1>

	<?php if ( isset( $form_submitted ) && $form_submitted ) : ?>
		<?php if ( isset( $clear_log_result ) && $clear_log_result ) : ?>
			<div class="updated">
				<p><?php echo esc_attr__( 'Log file cleared', 'github-deployer' ); ?></p>
			</div>
		<?php else : ?>
			<div class="error">
				<p><?php echo esc_attr__( 'Can\'t clear the log file.', 'github-deployer' ); ?></p>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<?php $logger = new Logger(); ?>
	<textarea class="large-text code gd_log_textarea" readonly><?php echo esc_textarea( $logger->display_log_content() ); ?></textarea>

	<form method="post" action="" onsubmit="return confirm('<?php echo esc_attr__( 'Are you sure?', 'github-deployer' ); ?>');">
		<input type="hidden" name="action" value="clear_log_file">
		<?php wp_nonce_field( GD_SLUG . '_clear_log_file', GD_SLUG . '_nonce' ); ?>
		<input type="submit" class="button button-primary" value="<?php echo esc_attr__( 'Clear log file', 'github-deployer' ); ?>">
	</form>
</div>
