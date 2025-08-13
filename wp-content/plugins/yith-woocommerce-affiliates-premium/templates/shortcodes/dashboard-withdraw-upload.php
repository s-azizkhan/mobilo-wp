<?php
/**
 * Affiliate Dashboard Withdraw - Upload invoice
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

<?php if ( ! empty( $company_profile ) ) : ?>
	<p class="form-row">
		<?php echo esc_html( __( 'Please, use this info for your invoice:', 'yith-woocommerce-affiliates' ) ); ?>
		<span class="formatted-address"><?php echo wp_kses_post( nl2br( $company_profile ) ); ?></span>
	</p>
<?php endif; ?>

<p class="form-row form-row-wide">
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo esc_attr( 1048576 * apply_filters( 'yith_wcaf_max_invoice_size', 3 ) ); ?>" />
	<input type="file" id="invoice_file" name="invoice_file" accept="<?php echo esc_attr( apply_filters( 'yith_wcaf_invoice_upload_mime', 'application/pdf' ) ); ?>" />
	<?php if ( $invoice_example ) : ?>
		<?php
		// translators: 1. Url to example invoice.
		echo apply_filters( 'yith_wcaf_example_invoice_text', sprintf( __( 'Please, refer to the following <a href="%s">example</a> for invoice creation', 'yith-woocommerce-affiliates' ), $invoice_example ), $invoice_example ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
	<?php endif; ?>
</p>