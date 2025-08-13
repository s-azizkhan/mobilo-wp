<?php
/**
 * Plugin Name: Affiliate For WooCommerce
 * Plugin URI: https://woocommerce.com/products/affiliate-for-woocommerce/
 * Description: The best affiliate management plugin for WooCommerce. Track, manage and payout affiliate commissions easily.
 * Version: 8.41.0
 * Author: StoreApps
 * Author URI: https://www.storeapps.org/
 * Developer: StoreApps
 * Developer URI: https://www.storeapps.org/
 * Requires PHP: 5.6
 * Requires at least: 5.0.0
 * Tested up to: 6.8.2
 * WC requires at least: 4.0.0
 * WC tested up to: 10.0.4
 * Requires Plugins: woocommerce
 * Text Domain: affiliate-for-woocommerce
 * Domain Path: /languages/
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright (c) 2019-2025 StoreApps All rights reserved.
 *
 * @package affiliate-for-woocommerce
 * Woo: 4830848:0f21ae7f876a631d2db8952926715502

 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

register_activation_hook( __FILE__, 'affiliate_for_woocommerce_activate' );

/**
 * Actions to perform on activation of the plugin
 */
function affiliate_for_woocommerce_activate() {
	include_once 'includes/class-afwc-install.php';
	add_option( 'afwc_default_commission_status', 'unpaid', '', 'no' );
	add_option( 'afwc_do_activation_redirect', true, '', 'no' );
	if ( get_option( '_afwc_current_db_version' ) ) {
		// Flag the onboarding complete if plugin is not activated first time.
		update_option( 'afwc_onboarding_completed', true );
	}
	add_option( 'afwc_pname', 'ref', '', 'no' );
	update_option( 'afwc_flushed_rules', 1, 'no' );

	// save affiliate registration form initial fields.
	$form_fields = apply_filters( 'afwc_registration_form_fields', afwc_reg_form_settings_initial_values() );
	add_option( 'afwc_form_fields', $form_fields, '', 'no' );
}

/**
 * Get default value of registration form settings
 *
 * @return array
 */
function afwc_reg_form_settings_initial_values() {
	return array(
		'afwc_reg_email'            => array(
			'type'     => 'email',
			'required' => 'required',
			'show'     => true,
			'label'    => _x( 'Email', 'registration form setting default label', 'affiliate-for-woocommerce' ),
			'field'    => _x( 'Email address', 'registration form setting field name', 'affiliate-for-woocommerce' ),
		),
		'afwc_reg_first_name'       => array(
			'type'     => 'text',
			'required' => '',
			'show'     => true,
			'label'    => _x( 'First Name', 'registration form setting default label', 'affiliate-for-woocommerce' ),
			'field'    => _x( 'First name', 'registration form setting field name', 'affiliate-for-woocommerce' ),
			'class'    => 'afwc_is_half',
		),
		'afwc_reg_last_name'        => array(
			'type'     => 'text',
			'required' => '',
			'show'     => true,
			'label'    => _x( 'Last Name', 'registration form setting default label', 'affiliate-for-woocommerce' ),
			'field'    => _x( 'Last name', 'registration form setting field name', 'affiliate-for-woocommerce' ),
			'class'    => 'afwc_is_half',
		),
		'afwc_reg_contact'          => array(
			'type'     => 'text',
			'required' => '',
			'show'     => true,
			'label'    => _x( 'Phone Number / Skype ID / Best method to talk to you', 'registration form setting default label', 'affiliate-for-woocommerce' ),
			'field'    => _x( 'Way to contact', 'registration form setting field name', 'affiliate-for-woocommerce' ),
		),
		'afwc_reg_website'          => array(
			'type'     => 'text',
			'required' => '',
			'show'     => true,
			'label'    => _x( 'Website', 'registration form setting default label', 'affiliate-for-woocommerce' ),
			'field'    => _x( 'Website link', 'registration form setting field name', 'affiliate-for-woocommerce' ),
		),
		'afwc_reg_password'         => array(
			'type'     => 'password',
			'required' => 'required',
			'show'     => true,
			'label'    => _x( 'Password', 'registration form setting default label', 'affiliate-for-woocommerce' ),
			'field'    => _x( 'Password', 'registration form setting field name', 'affiliate-for-woocommerce' ),
			'class'    => 'afwc_is_half',
		),
		'afwc_reg_confirm_password' => array(
			'type'     => 'password',
			'required' => 'required',
			'show'     => true,
			'label'    => _x( 'Confirm Password', 'registration form setting default label', 'affiliate-for-woocommerce' ),
			'field'    => _x( 'Confirm Password', 'registration form setting field name', 'affiliate-for-woocommerce' ),
			'class'    => 'afwc_is_half',
		),
		'afwc_reg_desc'             => array(
			'type'     => 'textarea',
			'required' => 'required',
			'show'     => true,
			'label'    => _x( 'Tell us more about yourself and why you\'d like to partner with us (please include your social media handles, experience promoting others, tell us about your audience etc)', 'registration form setting default label', 'affiliate-for-woocommerce' ),
			'field'    => _x( 'About yourself', 'registration form setting field name', 'affiliate-for-woocommerce' ),
		),
		'afwc_reg_terms'            => array(
			'type'     => 'checkbox',
			'required' => 'required',
			'show'     => true,
			'label'    => _x( 'I accept all the terms of this program', 'registration form setting default label', 'affiliate-for-woocommerce' ),
			'field'    => _x( 'Terms and conditions', 'registration form setting field name', 'affiliate-for-woocommerce' ),
		),

	);
}

/**
 * Handle redirect
 */
function afwc_redirect() {
	$activation_redirect  = get_option( 'afwc_do_activation_redirect', false );
	$onboarding_completed = get_option( 'afwc_onboarding_completed', false );

	// Redirect to onboarding for first time after plugin activation.
	if ( ! empty( $activation_redirect ) && empty( $onboarding_completed ) ) {
		update_option( 'afwc_onboarding_completed', true, 'no' );
		delete_option( 'afwc_do_activation_redirect' );
		wp_safe_redirect( admin_url( 'admin.php?page=affiliate-for-woocommerce#!/onboarding' ) );
		exit;
	}

	// If onboarding is completed, redirect to documentation page after plugin activation.
	if ( ! empty( $activation_redirect ) && ! empty( $onboarding_completed ) ) {
		delete_option( 'afwc_do_activation_redirect' );
		wp_safe_redirect( admin_url( 'admin.php?page=affiliate-for-woocommerce-documentation' ) );
		exit;
	}
}
add_action( 'admin_init', 'afwc_redirect' );

if ( ! function_exists( 'afwc_show_required_plugin_notice' ) ) {
	/**
	 * Show the required plugin notice.
	 */
	function afwc_show_required_plugin_notice() {
		?>
		<div class="notice notice-error">
			<p><?php echo esc_html_x( 'Affiliate for WooCommerce requires WooCommerce to be activated.', 'Required plugin notice', 'affiliate-for-woocommerce' ); ?></p>
		</div>
		<?php
	}
}

/**
 * Load Affiliate For WooCommerce only if woocommerce is activated
 */
function initialize_affiliate_for_woocommerce() {
	define( 'AFWC_PLUGIN_FILE', __FILE__ );
	if ( ! defined( 'AFWC_PLUGIN_DIRPATH' ) ) {
		define( 'AFWC_PLUGIN_DIRPATH', __DIR__ );
	}

	// To insert the option on plugin update.
	$afwc_admin_contact_email_address = get_option( 'new_admin_email', '' );
	$afwc_admin_contact_email_address = empty( $afwc_admin_contact_email_address ) ? get_option( 'admin_email', '' ) : $afwc_admin_contact_email_address;
	add_option( 'afwc_contact_admin_email_address', $afwc_admin_contact_email_address, '', 'no' );

	$active_plugins = (array) get_option( 'active_plugins', array() );
	if ( is_multisite() ) {
		$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	}

	if ( ( in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins ) ) ) {
		include_once 'includes/class-affiliate-for-woocommerce.php';
		$GLOBALS['affiliate_for_woocommerce'] = Affiliate_For_WooCommerce::get_instance();
	} elseif ( is_admin() ) {
		add_action( 'admin_notices', 'afwc_show_required_plugin_notice' );
	}
}
add_action( 'plugins_loaded', 'initialize_affiliate_for_woocommerce' );

// Declare WooCommerce custom order tables i.e HPOS compatibility.
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

// Declare WooCommerce cart checkout blocks compatibility.
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
		}
	}
);
