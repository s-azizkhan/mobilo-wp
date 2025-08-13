<?php
/**
 * Affiliate New Registration Email Content (Affiliate Manager - New Registration Received)
 *
 * @see      This template can be overridden by: https://woocommerce.com/document/affiliate-for-woocommerce/how-to-override-templates/
 * @package  affiliate-for-woocommerce/templates/plain/
 * @since    2.4.0
 * @version  1.2.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/* translators: %s: admin's first name */
printf( esc_html_x( 'Hi %s,', 'greeting message for the admin', 'affiliate-for-woocommerce' ), esc_html( $admin_name ) ) . "\n\n";

echo 'yes' === $is_auto_approved ? esc_html__( 'Congratulations! You got a new affiliate partner.', 'affiliate-for-woocommerce' ) : esc_html__( 'Please review and respond to this potential affiliate partner request.', 'affiliate-for-woocommerce' ) . "\n\n";

echo esc_html__( 'Name: ', 'affiliate-for-woocommerce' ) . "\t" . esc_attr( $user_name ) . ' (' . esc_attr( $user_email ) . ')' . "\n\n";

if ( ! empty( $user_url ) ) {
	/* translators: %s: label for user website */
	printf( esc_html_x( '%s: ', 'label for user website', 'affiliate-for-woocommerce' ), esc_attr( $user_website_label ) );
	echo "\t" . esc_attr( $user_url ) . "\n\n";
}

if ( ! empty( $additional_information ) ) {
	if ( ! empty( $additional_information_label ) ) {
		/* translators: %s: label for user info */
		printf( esc_html_x( '%s: ', 'label for user info', 'affiliate-for-woocommerce' ), esc_attr( $additional_information_label ) );
	}

	foreach ( $additional_information as $data ) {
		if ( ! isset( $data['value'] ) ) {
			continue;
		}
		echo "\n" . ( ! empty( $data['label'] ) ? esc_html( $data['label'] ) . ': ' : '' ) . wp_kses_post( $data['value'] );
	}

	echo "\n\n";
}

echo esc_html__( 'Next Actions', 'affiliate-for-woocommerce' ) . "\n\n";
/* translators: %s: user's profile link */
echo 'yes' === $is_auto_approved ? sprintf( esc_html__( 'Manage this affiliate : %s', 'affiliate-for-woocommerce' ), esc_url( $manage_url ) ) : sprintf( esc_html__( 'Approve / reject / manage this affiliate : %s', 'affiliate-for-woocommerce' ), esc_url( $manage_url ) ) . "\n\n";

/* translators: %1$s: Affiliate's name %2$s: Affiliate's email address */
printf( esc_html__( 'Email %1$s and discuss more details: %2$s', 'affiliate-for-woocommerce' ), esc_html( $user_name ), esc_html( $user_email ) ) . "\n\n";

/* translators: %s: Admin affiliate dashboard link */
printf( esc_html__( 'BTW, you can review and manage all affiliates and also process pending requests from here: %s.', 'affiliate-for-woocommerce' ), esc_url( $dashboard_url ) ) . "\n\n";

if ( 'yes' !== $is_auto_approved ) {
	/* translators: %s: affiliate user's name */
	printf( esc_html__( 'Do respond promptly. %s is waiting!', 'affiliate-for-woocommerce' ), esc_html( $user_name ) ) . "\n\n";
}

echo "\n\n----------------------------------------\n\n";

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( ! empty( $additional_content ) ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
	echo "\n\n----------------------------------------\n\n";
}

echo esc_html__( 'Thanks!', 'affiliate-for-woocommerce' );

// Output the email footer.
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
