<?php
/**
 * Main class for WooCommerce Smart Coupons Compatibility
 *
 * @package     affiliate-for-woocommerce/includes/integration/woocommerce-smart-coupons/
 * @since       4.12.0
 * @version     1.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSC_AFWC_Compatibility' ) ) {

	/**
	 * Compatibility class for WooCommerce Smart Coupons.
	 */
	class WSC_AFWC_Compatibility {

		/**
		 * Variable to hold instance of WSC_AFWC_Compatibility
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Constructor
		 */
		private function __construct() {
			// Export headers.
			add_filter( 'wc_smart_coupons_export_headers', array( $this, 'export_headers' ) );
			add_filter( 'wc_sc_export_coupon_meta', array( $this, 'export_coupon_meta_data' ), 10, 2 );

			add_filter( 'smart_coupons_parser_postmeta_defaults', array( $this, 'postmeta_defaults' ) );

			// Bulk Generate.
			add_filter( 'sc_generate_coupon_meta', array( $this, 'generate_coupon_meta' ), 10, 2 );

			// Import.
			add_filter( 'wc_sc_process_coupon_meta_value_for_import', array( $this, 'process_coupon_meta_value_for_import' ), 10, 2 );

			// Filter to generate affiliate coupon URL.
			add_filter( 'afwc_coupon_url', array( $this, 'generate_coupon_url' ), 10, 2 );

			// Filter to set coupon visibility for coupons generated for payout via coupons.
			add_filter( 'afwc_coupon_payouts_custom_meta', array( $this, 'custom_coupon_meta' ), 10, 1 );
		}

		/**
		 * Get single instance of WSC_AFWC_Compatibility
		 *
		 * @return WSC_AFWC_Compatibility Singleton object of WSC_AFWC_Compatibility
		 */
		public static function get_instance() {
			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Add meta in export headers.
		 *
		 * @param  array $headers Existing headers.
		 * @return array
		 */
		public function export_headers( $headers = array() ) {
			$headers['afwc_referral_coupon_of'] = _x( 'Assign to affiliate', 'Coupon meta name for assigning a coupon to an affiliate during export', 'affiliate-for-woocommerce' );

			return $headers;
		}

		/**
		 * Function to handle coupon meta data during export of existing coupons.
		 *
		 * @param  mixed $meta_value The meta value.
		 * @param  array $args       Additional arguments.
		 * @return string Processed meta value
		 */
		public function export_coupon_meta_data( $meta_value = '', $args = array() ) {
			if ( empty( $args['meta_key'] ) ) {
				return $meta_value;
			}

			if ( 'afwc_referral_coupon_of' !== $args['meta_key'] ) {
				return $meta_value;
			}

			if ( ! isset( $args['meta_value'] ) || empty( $args['meta_value'] ) ) {
				return $meta_value;
			}

			$affiliate_id = stripslashes( $args['meta_value'] );
			if ( empty( $affiliate_id ) ) {
				return $meta_value;
			}

			$meta_value = intval( $affiliate_id );

			return $meta_value;
		}

		/**
		 * Post meta defaults for referral coupon meta
		 *
		 * @param  array $defaults Existing postmeta defaults.
		 * @return array
		 */
		public function postmeta_defaults( $defaults = array() ) {
			$defaults['afwc_referral_coupon_of'] = '';

			return $defaults;
		}

		/**
		 * Add referral coupon meta with value - affiliate id - in coupon meta.
		 *
		 * @param  array $data The row data.
		 * @param  array $post The POST values.
		 * @return array Modified data
		 */
		public function generate_coupon_meta( $data = array(), $post = array() ) {
			if ( ! empty( $post['afwc_referral_coupon_of'] ) ) {
				$data['afwc_referral_coupon_of'] = ( ! empty( $post['afwc_referral_coupon_of'] ) ) ? intval( wc_clean( wp_unslash( $post['afwc_referral_coupon_of'] ) ) ) : '';
			}

			return $data;
		}

		/**
		 * Process coupon meta value for import.
		 *
		 * @param  mixed $meta_value The meta value.
		 * @param  array $args       Additional Arguments.
		 * @return mixed $meta_value
		 */
		public function process_coupon_meta_value_for_import( $meta_value = null, $args = array() ) {
			if ( empty( $args['meta_key'] ) ) {
				return $meta_value;
			}

			if ( 'afwc_referral_coupon_of' !== $args['meta_key'] ) {
				return $meta_value;
			}

			if ( empty( $args['postmeta']['afwc_referral_coupon_of'] ) ) {
				return '';
			}

			$afwc_referral_coupon_of = $args['postmeta']['afwc_referral_coupon_of'];
			if ( is_email( trim( $afwc_referral_coupon_of ) ) ) {
				$user_email = sanitize_email( $afwc_referral_coupon_of );
				$user       = get_user_by( 'email', $user_email );
				$meta_value = $user instanceof WP_User && ! empty( $user->ID ) ? $user->ID : '';
			} elseif ( is_numeric( $afwc_referral_coupon_of ) ) {
				$meta_value = intval( wc_clean( wp_unslash( $afwc_referral_coupon_of ) ) );
			} else {
				$meta_value = '';
			}

			if ( empty( $meta_value ) ) {
				return '';
			}

			// Check if user_id/meta_value have a valid affiliate status.
			$is_affiliate = afwc_is_user_affiliate( $meta_value );
			if ( 'not_registered' === $is_affiliate ) {
				// Passing empty instead of 0 to not fill meta_value during import.
				return '';
			}

			return $meta_value;
		}

		/**
		 * Generate the coupon URL.
		 *
		 * @param string $link The existing link.
		 * @param string $code The coupon code.
		 *
		 * @return string The generated coupon URL.
		 */
		public function generate_coupon_url( $link = '', $code = '' ) {
			if ( empty( $code ) ) {
				return $link;
			}

			$args     = array( 'coupon-code' => $code );
			$base_url = home_url( '/' );

			// SC added function from version 9.12.0. So first check if the method exists.
			global $woocommerce_smart_coupon;

			return ( true === method_exists( $woocommerce_smart_coupon, 'generate_coupon_url' ) ) ? $woocommerce_smart_coupon->generate_coupon_url( $args, $base_url ) : add_query_arg( $args, $base_url );
		}

		/**
		 * Set any custom meta of SC for payout via coupons.
		 *
		 * @param array $args The coupon args.
		 *
		 * @return array The updated coupon args.
		 */
		public function custom_coupon_meta( $args = array() ) {
			if ( empty( $args ) || ! is_array( $args ) ) {
				return array();
			}

			$args['sc_is_visible_storewide'] = 'yes';

			return $args;
		}
	}
}

WSC_AFWC_Compatibility::get_instance();
