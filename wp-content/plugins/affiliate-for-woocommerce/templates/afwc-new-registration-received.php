<?php
/**
 * Affiliate New Registration Email Content (Affiliate Manager - New Registration Received)
 *
 * @see      This template can be overridden by: https://woocommerce.com/document/affiliate-for-woocommerce/how-to-override-templates/
 * @package  affiliate-for-woocommerce/templates/
 * @since    2.4.0
 * @version  1.2.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$text_align = is_rtl() ? 'right' : 'left';

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
	<?php
	/* translators: %s: admin's first name */
	printf( esc_html_x( 'Hi %s,', 'greeting message for the admin', 'affiliate-for-woocommerce' ), esc_html( $admin_name ) );
	?>
</p>

<p>
	<?php echo 'yes' === $is_auto_approved ? esc_html__( 'Congratulations! You got a new affiliate partner.', 'affiliate-for-woocommerce' ) : esc_html__( 'Please review and respond to this potential affiliate partner request.', 'affiliate-for-woocommerce' ); ?>
</p>

<p><strong><?php echo esc_html__( 'Name: ', 'affiliate-for-woocommerce' ); ?></strong><?php echo esc_attr( $user_name ) . ' (' . esc_attr( $user_email ) . ')'; ?></p>

<?php if ( ! empty( $user_url ) ) { ?>
	<?php /* translators: %s: label for user website */ ?>
<p><strong><?php printf( esc_html_x( '%s: ', 'label for user website', 'affiliate-for-woocommerce' ), esc_attr( $user_website_label ) ); ?></strong><?php echo esc_url( $user_url ); ?></p>
<?php } ?>

<?php if ( ! empty( $additional_information ) ) { ?>
<p><strong><?php echo ! empty( $additional_information_label ) ? sprintf( '%s: ', esc_html( $additional_information_label ) ) : ''; ?></strong></p>
<div style="margin-bottom: 20px;">
	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
		<?php
		foreach ( $additional_information as $data ) {
			if ( ! isset( $data['value'] ) ) {
				continue;
			}
			$value = '';
			if ( ! empty( $data['type'] ) && ( 'file' === $data['type'] || 'url' === $data['type'] ) ) {
				$data_urls = ! empty( $data['value'] ) ? explode( ',', $data['value'] ) : array();
				if ( ! empty( $data_urls ) ) {
					$separator = '';
					foreach ( $data_urls as $url ) {
						$value    .= wp_kses_post( sprintf( '%1$s<a href="%2$s"> %2$s </a>', $separator, $url ) );
						$separator = ', ';
					}
				}
			} else {
				$value = $data['value'];
			}
			?>
			<tr>
				<th class="td" scope="row" style="text-align:<?php echo esc_attr( $text_align ); ?>; "> <?php echo ! empty( $data['label'] ) ? esc_html( $data['label'] ) : ''; ?> </th>
				<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; "> <?php echo wp_kses_post( $value ); ?> </td>
			</tr>
			<?php
		}
		?>
	</table>
</div>
<?php } ?>

<p><strong><?php echo esc_html__( 'Next Actions', 'affiliate-for-woocommerce' ); ?></strong>
<ul>
	<li>
		<a href="<?php echo esc_url( $manage_url ); ?>" target="_blank">
			<?php echo 'yes' === $is_auto_approved ? esc_html__( 'Manage this affiliate', 'affiliate-for-woocommerce' ) : esc_html__( 'Approve / reject / manage this affiliate', 'affiliate-for-woocommerce' ); ?>
		</a>
	</li>
	<?php /* translators: %s: Affiliate's first name */ ?>
	<li><a href="mailto:<?php echo esc_attr( $user_email ); ?>" target="_blank" ><?php printf( esc_html__( 'Email %s and discuss more details', 'affiliate-for-woocommerce' ), esc_html( $user_name ) ); ?></a></li>
</ul>
</p>

<?php /* translators: %1$s: Opening a tag for admin affiliate dashboard link %2$s: closing a tag for admin affiliate dashboard link */ ?>
<p><?php printf( esc_html__( 'BTW, you can review and manage all affiliates and also process pending requests from %1$shere%2$s.', 'affiliate-for-woocommerce' ), '<a href="' . esc_url( $dashboard_url ) . '">', '</a>' ); ?></p>
<?php
if ( 'yes' !== $is_auto_approved ) {
	?>
	<p>
	<?php
	/* translators: %1$s: Opening strong tag %2$s: affiliate's name %3$s: closing strong tag */
	printf( esc_html__( 'Do respond promptly. %1$s%2$s%3$s is waiting!', 'affiliate-for-woocommerce' ), '<strong>', esc_attr( $user_name ), '</strong>' );
	?>
	</p>
	<?php
}
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

?>
<p><?php echo esc_html__( 'Thanks!', 'affiliate-for-woocommerce' ); ?></p>
<?php

/**
 * Output the email footer
 *
 * @hooked WC_Emails::email_footer() Output the email footer.
 * @param string $email.
 */
do_action( 'woocommerce_email_footer', $email );
