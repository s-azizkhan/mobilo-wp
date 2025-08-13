<?php
/**
 * Affiliate New Conversion Email Content (Affiliate - New Conversion Received)
 *
 * @see      This template can be overridden by: https://woocommerce.com/document/affiliate-for-woocommerce/how-to-override-templates/
 * @package  affiliate-for-woocommerce/templates/plain/
 * @since    2.3.0
 * @version  1.2.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/* translators: %s: Affiliate's first name */
printf( esc_html_x( 'Hi %s,', 'greeting message for the affiliate', 'affiliate-for-woocommerce' ), esc_html( $affiliate_name ) ) . "\n\n";

echo esc_html__( '{site_title} just made a sale - thanks to you!', 'affiliate-for-woocommerce' ) . "\n\n";

echo esc_html__( 'Here is the summary:', 'affiliate-for-woocommerce' ) . "\n\n";

echo "\n----------------------------------------\n\n";

if ( true === apply_filters( 'afwc_account_show_customer_column', false, array( 'source' => $email ) ) ) {
	echo esc_html_x( 'Customer: ', 'Label with customer name', 'affiliate-for-woocommerce' ) . "\t " . wp_kses_post( $order_customer_full_name ) . "\n";
}

echo esc_html__( 'Order total: ', 'affiliate-for-woocommerce' ) . "\t " . wp_kses_post( $order_currency_symbol . '' . $order_total ) . "\n";

echo esc_html__( 'Commission earned: ', 'affiliate-for-woocommerce' ) . "\t " . wp_kses_post( $order_currency_symbol . '' . $order_commission_amount ) . "\n\n";

echo "\n----------------------------------------\n\n";

/* translators: %s: Affiliate my account link */
printf( esc_html__( 'We have already updated your account to reflect this: %s', 'affiliate-for-woocommerce' ), esc_url( $my_account_afwc_url ) ) . "\n\n";

echo esc_html__( 'Thank you for promoting us. We look forward to sending another email like this very soon.', 'affiliate-for-woocommerce' ) . "\n\n";

echo "\n\n----------------------------------------\n\n";

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
	echo "\n\n----------------------------------------\n\n";
}

// Output the email footer.
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
