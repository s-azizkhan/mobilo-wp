<?php
/**
 * Affiliate For WooCommerce About/Welcome/Landing page
 *
 * @package   affiliate-for-woocommerce/includes/admin/
 * @since     1.0.0
 * @version   1.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plugin_data = Affiliate_For_WooCommerce::get_plugin_data();
?>
<style type="text/css">
	.wrap.about-wrap,
	.has-2-columns.feature-section.col.two-col {
		max-width: unset !important;
	}
	.afw .has-2-columns.feature-section.col.two-col {
		margin-top: 1rem;
	}
	.about-wrap .column {
		margin-left: 0;
	}
	.about-text h2 {
		text-align: left;
		margin: 1rem 0;
	}
	.afw .column.last-feature {
		margin-right: 2px !important;
	}
	.afw-faq ul {
		list-style: disc;
		margin-left: 1.2rem;
	}
	.afw-faq li {
		margin-bottom: 1rem;
	}
	.afw-faq {
		margin-bottom: 2rem !important;
	}
	.how-to {
		margin-top: 2.5rem;
	}
	.how-to p {
		font-size: 1.1rem;
	}
	.about-wrap .afw .button-hero {
		color: #FFF!important;
		border-color: #03a025 !important;
		background: #03a025 !important;
		box-shadow: 0 1px 0 #03a025;
		font-weight: bold;
	}
	.about-wrap .afw .button-hero:hover {
		color: #FFF!important;
		background: #0aab2e !important; 
		border-color: #0aab2e !important;
	}
</style>
<script type="text/javascript">
	jQuery( function(){
		jQuery('#toplevel_page_woocommerce').find('a[href$="admin.php?page=affiliate-for-woocommerce"]').addClass('current');
		jQuery('#toplevel_page_woocommerce').find('a[href$="admin.php?page=affiliate-for-woocommerce"]').parent().addClass('current');
	});
</script>

<div class="wrap about-wrap">
	<h1><?php echo esc_html__( 'Thank you for installing Affiliate for WooCommerce', 'affiliate-for-woocommerce' ) . ' ' . esc_html( $plugin_data['Version'] ) . '!'; ?></h1>
	<?php
	if ( ( afwc_is_plugin_active( 'affiliates/affiliates.php' ) || afwc_is_plugin_active( 'affiliates-pro/affiliates-pro.php' ) ) && defined( 'AFFILIATES_TP' ) ) {
		$tables            = $wpdb->get_results( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $wpdb->prefix . AFFILIATES_TP ) . '%' ), ARRAY_A ); // phpcs:ignore
		$show_notification = get_option( 'show_migrate_affiliates_notification', 'yes' );
		// Note: To test migration uncomment following code.
		if ( ! empty( $tables ) && 'no' !== $show_notification ) {
			?>
				<div>
					<div>
				<?php echo esc_html__( 'We discovered that you are using another "Affiliates" plugin. Do you want to migrate your existing data to this new Affiliates for WooCommerce plugin?', 'affiliate-for-woocommerce' ); ?>
							<span class="migrate_affiliates_actions">
								<a href="
							<?php
							echo esc_url(
								add_query_arg(
									array(
										'page'         => 'affiliate-for-woocommerce-settings',
										'migrate'      => 'affiliates',
										'is_from_docs' => 1,
									),
									admin_url( 'admin.php' )
								)
							);
							?>
											" class="button-primary" id="migrate_yes" ><?php echo esc_html__( 'Yes, Migrate existing data.', 'affiliate-for-woocommerce' ); ?></a>
								<a href="
								<?php
								echo esc_url(
									add_query_arg(
										array(
											'page'         => 'affiliate-for-woocommerce-settings',
											'migrate'      => 'ignore_affiliates',
											'is_from_docs' => 1,
										),
										admin_url( 'admin.php' )
									)
								);
								?>
											" class="button" id="migrate_no" ><?php echo esc_html__( 'No, I want to start afresh.', 'affiliate-for-woocommerce' ); ?></a>
							</span>
						<p><?php echo esc_html__( 'Note: Once you migrate from Affiliates plugin, please deactivate it. Affiliates and Affiliate for WooCommerce can\'t work simultaneously.', 'affiliate-for-woocommerce' ); ?></p>
					</div>
				</div>
				<?php
		}
	}
	?>
	<div class="changelog afw">
		<div class="has-2-columns feature-section col two-col">
			<div class="column col">
				<p class="about-text"><?php echo esc_html__( 'Glad to have you onboard. We hope the plugin adds to your success 🏆', 'affiliate-for-woocommerce' ); ?></p>
			</div>
			<div class="column col last-feature">
				<p align="right">
					<a target="_blank" href="
					<?php
					echo esc_url(
						add_query_arg(
							array(
								'page' => 'wc-settings',
								'tab'  => 'affiliate-for-woocommerce-settings',
							),
							admin_url( 'admin.php' )
						)
					);
					?>
							"><?php echo esc_html_x( 'Settings', 'link to view plugin settings', 'affiliate-for-woocommerce' ); ?></a> | 
					<a target="_blank" href="<?php echo esc_url( AFWC_DOC_DOMAIN ); ?>"><?php echo esc_html_x( 'Docs', 'link to documentation', 'affiliate-for-woocommerce' ); ?></a> | 
					<a target="_blank" href="<?php echo esc_url( AFW_CONTACT_SUPPORT_URL ); ?>"><?php echo esc_html_x( 'Contact us', 'link to contact support', 'affiliate-for-woocommerce' ); ?></a>
				</p>
			</div>
		</div>
		<div class="about-text feature-section">
				<?php
				echo '<a class="button button-hero" target="_blank" href="' . esc_url(
					add_query_arg(
						array(
							'page' => 'affiliate-for-woocommerce',
						),
						admin_url( 'admin.php' )
					)
				) . '">' . esc_html_x( 'Visit Dashboard', 'Link to the admin affiliate dashboard', 'affiliate-for-woocommerce' ) . '</a>';
				?>
				<br>
		</div>
		<div class="how-to">
			<p><?php echo esc_html_x( 'Bookmark this page to find it easily later!', 'bookmark welcome page', 'affiliate-for-woocommerce' ); ?></p>
			<h3><?php echo esc_html__( "How to's", 'affiliate-for-woocommerce' ); ?></h3>
			<div class="has-2-columns feature-section col two-col">
				<div class="column col afw-faq">
					<ul>
						<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . '#section-6">' . esc_html__( 'How do I add/make a user an affiliate?', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
						<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . '#section-10">' . esc_html__( 'Where do affiliates login and get their stats from?', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
						<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . '#section-14">' . esc_html__( 'Where\'s the link an affiliate will use to refer to my site?', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
						<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-customize-affiliate-referral-link/">' . esc_html__( 'How to find, customize and share an affiliate referral link?', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
						<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-create-and-assign-coupons-to-affiliates/">' . esc_html__( 'How to give referral coupons to affiliates?', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
						<ul>
							<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-bulk-assign-coupons-to-affiliates/">' . esc_html__( 'How to bulk assign coupons to affiliates?', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
						</ul>
						<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-set-up-a-multilevel-referral-multi-tier-affiliate-program/">' . esc_html__( 'How to set up a multilevel referral/multi-tier affiliate program?', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
						<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-set-up-lifetime-commissions/">' . esc_html__( 'How to set up lifetime commissions?', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
						<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-assign-unassign-an-order-to-an-affiliate/">' . esc_html__( 'How to re-calculate the commission in an order?', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
						<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-export-affiliate-data-to-csv/">' . esc_html__( 'How to export affiliate data to CSV?', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
						<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'faqs/">' . esc_html__( 'FAQ\'s', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
					</ul>
				</div>
				<div class="column col last-feature afw-faq">
					<ul>
						<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-create-affiliate-commission-plans/">' . esc_html__( 'How to create affiliate commission plans?', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
						<ul>
							<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-set-different-affiliate-commission-rates-for-affiliates/">' . esc_html__( 'Set custom commission for affiliates', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
							<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-set-different-affiliate-commission-rates-for-affiliate-tags/">' . esc_html__( 'Set custom commission for affiliate tags', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
							<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-set-different-affiliate-commission-rates-for-product-or-product-category/">' . esc_html__( 'Set custom commission for products', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
							<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-set-different-commission-rates-based-on-product-taxonomies/">' . esc_html__( 'Set custom commission for custom product taxonomies - categories, tags, brands, type, etc', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
							<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-create-affiliate-commission-plans/#section-10">' . esc_html__( 'Set custom commissions with 15+ powerful rules', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
						</ul>
						<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-payout-commissions-in-affiliate-for-woocommerce/">' . esc_html__( 'How to payout commissions to affiliates and check all the processed payouts?', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
						<ul>
							<li>
								<?php
								/* translators: Link to the Affiliate For WooCommerce Doc */
								echo esc_html__( 'Payout via ', 'affiliate-for-woocommerce' ) . '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-payout-commissions-in-affiliate-for-woocommerce/#section-1">' . esc_html__( 'PayPal', 'affiliate-for-woocommerce' ) . '</a> | <a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-pay-affiliate-commissions-via-stripe/">' . esc_html__( 'Stripe', 'affiliate-for-woocommerce' ) . '</a> | <a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-pay-affiliate-commission-as-a-coupon/">' . esc_html__( 'Coupon', 'affiliate-for-woocommerce' ) . '</a> | <a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-pay-store-credit-as-affiliate-commission/">' . esc_html__( 'Store Credit', 'affiliate-for-woocommerce' ) . '</a>';
								?>
							</li>
						</ul>
						<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-restrict-affiliate-commission-for-products-on-sale/">' . esc_html__( 'How to restrict affiliate commission for products on sale?', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
						<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'affiliate-program-terms-and-conditions/">' . esc_html__( 'How to generate terms and conditions for affiliates and share them with affiliates?', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
						<li><?php /* translators: Link to the Affiliate For WooCommerce Doc */ echo '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . 'how-to-promote-affiliate-links-on-social-media/">' . esc_html__( 'How to promote affiliate links/referral coupons on social media?', 'affiliate-for-woocommerce' ) . '</a>'; ?></li>
					</ul>
				</div>
			</div>
		</div>
		<p><?php echo esc_html_x( 'For more information, ', 'description to view all docs', 'affiliate-for-woocommerce' ) . /* translators: Link to the Affiliate For WooCommerce Doc */ '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . '">' . esc_html_x( 'view all docs', 'link text to view all docs', 'affiliate-for-woocommerce' ) . '</a>.'; ?></p>
	</div>
</div>
<?php
