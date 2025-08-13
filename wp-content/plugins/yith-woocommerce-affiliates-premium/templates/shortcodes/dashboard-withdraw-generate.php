<?php
/**
 * Affiliate Dashboard Withdraw - Generate invoice
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

<?php
if ( apply_filters( 'yith_wcaf_show_complete_invoice_form', false ) ) {
	foreach ( $invoice_fields as $field ) {
		$field_data = YITH_WCAF_Shortcode_Premium::get_field( $field );
		$value      = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$value      = ( ! $value && isset( $invoice_profile[ $field ] ) ) ? $invoice_profile[ $field ] : $value;

		woocommerce_form_field( $field, $field_data, $value );
	}
} else {
	woocommerce_form_field( 'number', YITH_WCAF_Shortcode_Premium::get_field( 'number' ) );
}
