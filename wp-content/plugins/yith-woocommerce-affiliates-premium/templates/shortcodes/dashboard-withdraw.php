<?php
/**
 * Affiliate Dashboard Withdraw
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Affiliates
 * @version 1.0.5
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'YITH_WCAF' ) ) {
	exit;
} // Exit if accessed directly
?>

<div class="yith-wcaf yith-wcaf-withdraw woocommerce">

	<?php
	if ( function_exists( 'wc_print_notices' ) ) {
		wc_print_notices();
	}
	?>

	<?php do_action( 'yith_wcaf_before_dashboard_section', 'withdraw' ) ?>

	<div class="left-column <?php echo ( ! $show_right_column ) ? 'full-width' : '' ?>">
		<?php if( ! $can_withdraw ): ?>
			<?php echo apply_filters( 'yith_wcaf_affiliate_cannot_withdraw_message', sprintf( __('You already have an active payment request; please check payment status in <a href="%s">Payments\' page</a>','yith-woocommerce-affiliates' ), $payments_endpoint ) ) ?>
		<?php else: ?>
			<form method="POST" enctype="multipart/form-data">
				<div class="first-step">
					<div class="information-panel form-row form-row-wide">

						<p class="max-withdraw">
							<b><?php esc_html_e( 'Current balance:', 'yith-woocommerce-affiliates' ); ?></b>
							<?php echo wc_price( $max_withdraw ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</p>

						<?php if ( 'dates' == $amount_mode ) : ?>
							<p class="total">
								<b><?php esc_html_e( 'Withdraw Total:', 'yith-woocommerce-affiliates' ); ?></b>
								<span class="withdraw-current-total"><?php echo wc_price( $current_amount ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</p>
						<?php endif; ?>

						<?php if ( $min_withdraw ) : ?>
							<p class="min-withdraw">
								<b><?php esc_html_e( 'Minimum amount to withdraw:', 'yith-woocommerce-affiliates' ); ?></b>
								<?php echo wc_price( $min_withdraw ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</p>
						<?php endif; ?>

						<?php if ( $notes = apply_filters( 'yith_wcaf_withdraw_info_panel_additional_notes', '' ) ) : ?>
							<p class="additional-notes">
								<?php echo wp_kses_post( $notes ); ?>
							</p>
						<?php endif; ?>

					</div>

					<?php if ( 'dates' == $amount_mode ) : ?>
					<div class="withdraw-dates-container">
						<p class="form-row form-row-first">
							<label for="withdraw_from"><?php _e( 'From:', 'yith-woocommerce-affiliates' ) ?></label>
							<input type="text" id="withdraw_from" name="withdraw_from" class="datepicker" value="<?php echo esc_attr( $withdraw_from )?>" />
						</p>

						<p class="form-row form-row-last">
							<label for="withdraw_to"><?php _e( 'To:', 'yith-woocommerce-affiliates' ) ?></label>
							<input type="text" id="withdraw_to" name="withdraw_to" class="datepicker" value="<?php echo esc_attr( $withdraw_to )?>" />
						</p>
					</div>
					<?php else : ?>
					<div class="withdraw-amount-container">
						<p class="form-row form-row-wide">
							<label for="withdraw_amount"><?php esc_html_e( 'Amount:', 'yith-woocommerce-affiliates' ); ?></label>
							<span class="withdraw-amount">
								<span class="currency-symbol"><?php echo esc_html( apply_filters( 'yith_wcaf_withdraw_amount_currency_symbol', get_woocommerce_currency_symbol() ) ); ?></span>
								<input type="number" step="<?php echo esc_attr( apply_filters( 'yith_wcaf_withdraw_amount_step', '0.01' ) ); ?>" min="<?php echo esc_attr( $min_withdraw ); ?>" max="<?php echo esc_attr( $max_withdraw ); ?>" id="withdraw_amount" name="withdraw_amount" class="amount" value="<?php echo esc_attr( $current_amount ); ?>" />
							</span>
						</p>
					</div>
					<?php endif; ?>
				</div>

				<?php if ( apply_filters( 'yith_wcaf_payment_email_required', true ) || $require_invoice && in_array( $invoice_mode, array( 'both', 'generate' ) ) ) : ?>
					<div class="second-step">
						<h3><?php esc_html_e( 'Billing info', 'yith-woocommerce-affiliates' ); ?></h3>

						<?php if ( $require_invoice && in_array( $invoice_mode, array( 'both', 'generate' ) ) ) : ?>
							<p class="form-row">
								<?php if ( ! empty( $formatted_profile ) ) : ?>
									<span class="formatted-address">
										<?php echo wp_kses_post( $formatted_profile ); ?>
									</span>
									<a href="<?php echo esc_url( YITH_WCAF()->get_affiliate_dashboard_url( 'settings' ) ); ?>"><?php echo esc_html( __( 'edit &rsaquo;', 'yith-woocommerce-affiliates' ) ); ?></a>
								<?php else : ?>
									<?php
									// translators: 1. Url to affiliate dashboard.
									echo wp_kses_post( sprintf( __( 'Billing info not found. Please, fill the fields in the <a href="%s">Settings tab</a>', 'yith-woocommerce-affiliates' ), YITH_WCAF()->get_affiliate_dashboard_url( 'settings' ) ) );
									?>
								<?php endif; ?>
							</p>
						<?php endif; ?>

						<?php if ( apply_filters( 'yith_wcaf_payment_email_required', true ) ) : ?>
							<p class="form-row">
								<label for="payment_email"><?php esc_html_e( 'Payment email', 'yith-woocommerce-affiliates' ); ?></label>
								<input type="email" id="payment_email" name="payment_email" value="<?php echo esc_attr( $payment_email ); ?>" />
							</p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $require_invoice ) : ?>
					<div class="third-step woocommerce-billing-fields">
						<h3><?php echo esc_html( __( 'Invoice', 'yith-woocommerce-affiliates' ) ); ?></h3>

						<p>
							<?php
							echo esc_html( __( 'To process the payment we need you provide us an invoice.', 'yith-woocommerce-affiliates' ) );
							echo '&nbsp;';

							if ( 'both' === $invoice_mode ) {
								echo esc_html( __( 'You can create your own invoice and upload it in PDF or we can generate an invoice for you.', 'yith-woocommerce-affiliates' ) );
							} elseif ( 'upload' === $invoice_mode ) {
								echo esc_html( __( 'Create it using your own invoicing software and upload it in PDF using following form.', 'yith-woocommerce-affiliates' ) );
							} elseif ( 'generate' == $invoice_mode ) {
								echo esc_html( __( 'We\'ll automatically generate one for you; just make sure to fill in billing information.', 'yith-woocommerce-affiliates' ) );
							}
							?>
						</p>

						<?php if ( 'both' === $invoice_mode ) : ?>
							<ul class="invoice-modes">
								<li>
									<p class="form-row form-row-wide">
										<label for="invoice_mode_upload">
											<input type="radio" name="invoice_mode" id="invoice_mode_upload" class="invoice-mode-radio" value="upload" <?php checked( ! isset( $_POST['invoice_mode'] ) || 'upload' == $_POST['invoice_mode'] ); ?> >
											<?php echo esc_html( __( 'Attach a PDF invoice', 'yith-woocommerce-affiliates' ) ); ?>
										</label>
									</p>
									<div class="invoice-mode">
										<?php yith_wcaf_get_template( 'dashboard-withdraw-upload.php', $args, 'shortcodes' ); ?>
									</div>
								</li>
								<li>
									<p class="form-row form-row-wide">
										<label for="invoice_mode_generate">
											<input type="radio" name="invoice_mode" id="invoice_mode_generate" class="invoice-mode-radio" value="generate" <?php checked( isset( $_POST['invoice_mode'] ) && 'generate' == $_POST['invoice_mode'] ); ?> >
											<?php echo esc_html( __( 'Generate an automatic invoice', 'yith-woocommerce-affiliates' ) ); ?>
										</label>
									</p>
									<div class="invoice-mode">
										<?php yith_wcaf_get_template( 'dashboard-withdraw-generate.php', $args, 'shortcodes' ); ?>
									</div>
								</li>
							</ul>
						<?php elseif ( 'upload' === $invoice_mode ) : ?>
							<?php yith_wcaf_get_template( 'dashboard-withdraw-upload.php', $args, 'shortcodes' ); ?>
						<?php elseif ( 'generate' === $invoice_mode ) : ?>
							<?php yith_wcaf_get_template( 'dashboard-withdraw-generate.php', $args, 'shortcodes' ); ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php
				if ( 'yes' === $show_terms_field ) :

					$terms_link = sprintf( '<a target="_blank" href="%s">%s</a>', $terms_anchor_url, $terms_anchor_text );
					$label      = apply_filters( 'yith_wcaf_terms_label', str_replace( '%TERMS%', $terms_link, $terms_label ) );
					$required   = apply_filters( 'yith_wcaf_terms_required', true );

					?>
					<p class="form-row form-row-wide">
						<label for="terms">
							<input type="checkbox" name="terms" id="terms" value="yes" <?php checked( isset( $_POST['terms'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing ?> />
							<?php echo wp_kses_post( $label ); ?> <?php echo $required ? '<span class="required">*</span>' : ''; ?>
						</label>
					</p>
				<?php endif; ?>

				<?php wp_nonce_field( 'yith_wcaf_withdraw', '_withdraw_nonce' ); ?>

				<input class="button submit" type="submit" value="<?php echo esc_attr( apply_filters( 'yith_wcaf_withdraw_submit_button', __( 'Request Withdraw', 'yith-woocommerce-affiliates' ) ) ); ?>" />

			</form>
		<?php endif; ?>
	</div>

	<!--NAVIGATION MENU-->
	<?php
	$atts = array(
		'show_right_column'    => $show_right_column,
		'show_left_column'     => true,
		'show_dashboard_links' => $show_dashboard_links,
		'dashboard_links'      => $dashboard_links,
	);
	yith_wcaf_get_template( 'navigation-menu.php', $atts, 'shortcodes' )
	?>

	<?php do_action( 'yith_wcaf_after_dashboard_section', 'withdraw' ); ?>

</div>
