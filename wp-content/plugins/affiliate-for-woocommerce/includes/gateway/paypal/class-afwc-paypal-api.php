<?php
/**
 * Main class for Affiliate For WooCommerce - PayPal API
 *
 * @package     affiliate-for-woocommerce/includes/gateway/paypal/
 * @since       4.0.0
 * @version     1.2.12
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AFWC_PayPal_API' ) ) {

	/**
	 * PayPal API
	 */
	class AFWC_PayPal_API {

		/**
		 * The API username
		 *
		 * @var string $api_username
		 */
		public $api_username = null;

		/**
		 * The API password
		 *
		 * @var string $api_password
		 */
		public $api_password = null;

		/**
		 * The API signature
		 *
		 * @var string $api_signature
		 */
		public $api_signature = null;

		/**
		 * The API endpoint
		 *
		 * @var string $api_endpoint
		 */
		public $api_endpoint = '';

		/**
		 * The email subject
		 *
		 * @var string $email_subject
		 */
		public $email_subject = 'EmailSubject';

		/**
		 * The Payout method
		 *
		 * @var string $payout_method
		 */
		public $payout_method = '';

		/**
		 * The Test mode
		 *
		 * @var string $sandbox
		 */
		public $sandbox = '';

		/**
		 * The subject
		 *
		 * @var string $subject
		 */
		public $subject = '';

		/**
		 * The currency
		 *
		 * @var string $currency
		 */
		public $currency = 'USD';

		/**
		 * Variable to hold instance of this class
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Get single instance of this class
		 *
		 * @return AFWC_PayPal_API Singleton object of this class
		 */
		public static function get_instance() {

			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 */
		private function __construct() {
			$this->payout_method = $this->get_payout_method();
			$this->set_credentials();
		}

		/**
		 * Method to check whether the PayPal API is enabled for payouts.
		 * It detects based on whether the credential setup is complete or not.
		 *
		 * @return bool Return true if enabled otherwise false.
		 */
		public function is_enabled() {
			return (bool) $this->is_set_credentials( $this->payout_method );
		}

		/**
		 * Set API credentials.
		 *
		 * @param string $gateway The gateway.
		 *
		 * @return void
		 */
		public function set_credentials( $gateway = '' ) {

			if ( empty( $gateway ) ) {
				$gateway = $this->get_payment_method();
			}

			if ( ! is_scalar( $gateway ) || empty( $gateway ) ) {
				return;
			}

			$method      = 'get_' . $gateway . '_credentials';
			$credentials = is_callable( array( $this, $method ) ) ? call_user_func( array( $this, $method ) ) : array();

			if ( ! empty( $credentials ) ) {
				$this->sandbox       = isset( $credentials['testmode'] ) ? $credentials['testmode'] : true;
				$this->api_endpoint  = $this->sandbox ? 'https://api-m.sandbox.paypal.com/v1' : 'https://api-m.paypal.com/v1';
				$this->api_username  = ( ! empty( $credentials['username'] ) ) ? $credentials['username'] : '';
				$this->api_password  = ( ! empty( $credentials['password'] ) ) ? $credentials['password'] : '';
				$this->api_signature = ( ! empty( $credentials['signature'] ) ) ? $credentials['signature'] : '';
			}
		}

		/**
		 * Get payment gateway method.
		 *
		 * @return string
		 */
		public function get_payment_method() {
			$method = 'paypal_standard';

			if ( 'paypal_masspay' !== $this->payout_method && afwc_is_plugin_active( 'woocommerce-paypal-payments/woocommerce-paypal-payments.php' ) ) {
				$method = 'paypal_payments';
			}

			/**
			 * Filters the PayPal payment method.
			 *
			 * @param string $method.
			 * @param array
			 */
			return apply_filters( 'afwc_payout_paypal_payment_method', $method, array( 'source' => $this ) );
		}

		/**
		 * Get credentials from WooCommerce PayPal Payments plugin.
		 *
		 * @return array
		 */
		public function get_paypal_payments_credentials() {
			$credentials = array();

			if ( afwc_is_plugin_active( 'woocommerce-paypal-payments/woocommerce-paypal-payments.php' ) ) {
				$new_ui_enabled = ( '1' === get_option( 'woocommerce-ppcp-is-new-merchant' ) );

				/**
				 * Since the WooCommerce PayPal Payments plugin version 3.0.0, a new option has been introduced to store credentials - available only for those who have migrated to the new UI.
				 */
				$settings    = get_option(
					$new_ui_enabled ? 'woocommerce-ppcp-data-common' : 'woocommerce-ppcp-settings',
					array()
				);
				$credentials = array(
					'testmode' => $new_ui_enabled ? ! empty( $settings['use_sandbox'] ) : ! empty( $settings['sandbox_on'] ), // Key is different for new and old UI.
					'username' => ( ! empty( $settings['client_id'] ) ) ? $settings['client_id'] : '',
					'password' => ( ! empty( $settings['client_secret'] ) ) ? $settings['client_secret'] : '',
				);
			}

			return $credentials;
		}

		/**
		 * Get credentials from PayPal standard.
		 *
		 * @return array
		 */
		public function get_paypal_standard_credentials() {
			$settings = get_option( 'woocommerce_paypal_settings', array() );

			$credentials = array();

			if ( ! empty( $settings ) ) {
				$sandbox = ( ! empty( $settings['testmode'] ) && ( 'yes' === $settings['testmode'] ) ) ? 'sandbox_' : '';

				$credentials = array(
					'testmode'  => ( ! empty( $sandbox ) ) ? true : false,
					'username'  => ( ! empty( $settings[ $sandbox . 'api_username' ] ) ) ? $settings[ $sandbox . 'api_username' ] : '',
					'password'  => ( ! empty( $settings[ $sandbox . 'api_password' ] ) ) ? $settings[ $sandbox . 'api_password' ] : '',
					'signature' => ( ! empty( $settings[ $sandbox . 'api_signature' ] ) ) ? $settings[ $sandbox . 'api_signature' ] : '',
				);
			}

			return $credentials;
		}

		/**
		 * Process PayPal payment.
		 *
		 * @param  array  $payout_records The payout records.
		 * @param  string $currency   The currency code.
		 * @return array|WP_Error|null  $response
		 */
		public function process_paypal_mass_payment( $payout_records = array(), $currency = 'USD' ) {
			if ( ! empty( $this->payout_method ) && ! empty( $payout_records ) && is_array( $payout_records ) ) {
				// payment method loader.
				$loader = AFWC_PLUGIN_DIRPATH . '/includes/gateway/paypal/class-afwc-' . str_replace( '_', '-', $this->payout_method ) . '.php';

				if ( file_exists( $loader ) ) {

					include_once $loader;

					$class_name = 'AFWC_' . str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $this->payout_method ) ) );

					if ( class_exists( $class_name ) && is_callable( array( $class_name, 'get_instance' ) ) ) {

						$obj = call_user_func( array( $class_name, 'get_instance' ) );

						if ( is_callable( array( $obj, 'make_payment' ) ) && $this->is_set_credentials( $this->payout_method ) ) {

							$obj->currency = $currency;
							$result        = $obj->make_payment( $payout_records );

							if ( is_wp_error( $result ) || empty( $result ) || ! is_array( $result ) ) {
								return $result;
							}

							// Calculate total amount if 'amount' is missing in the result.
							if ( ! isset( $result['amount'] ) ) {
								$amounts          = ! empty( $payout_records ) && is_array( $payout_records ) ? array_column( $payout_records, 'amount' ) : array();
								$result['amount'] = ! empty( $amounts ) && is_array( $amounts ) ? floatval( array_sum( $amounts ) ) : 0.00;
							}

							/**
							 * Fires immediately after PayPal commission payout.
							 *
							 * @param array $result         The results
							 * @param array $payout_records  The Payout records.
							 * @param array
							 */
							do_action( 'afwc_after_paypal_commission_payout', $result, $payout_records, array( 'source' => $this ) );

							return $result;
						}
					}
				}
			}

			return array();
		}

		/**
		 * Get PayPal payout method.
		 *
		 * @param bool $check_for_payouts True if checked for paypal payout.
		 *
		 * @return string PayPal payout method.
		 */
		public function get_payout_method( $check_for_payouts = false ) {

			$method = get_option( 'afwc_commission_payout_method' );

			// Set method as paypal_payout if WooCommerce PayPal Payment credentials are set and PayPal standard is not loaded.
			// TODO: can move PayPal Standard code to a separate function OR under get_paypal_standard_credentials for consistency.
			if ( ! empty( $check_for_payouts ) && class_exists( 'WC_Gateway_Paypal' ) ) {
				$wc_paypal_gateway         = new WC_Gateway_Paypal();
				$is_loaded_paypal_standard = is_callable( array( $wc_paypal_gateway, 'should_load' ) ) ? $wc_paypal_gateway->should_load() : false;

				if ( false === $is_loaded_paypal_standard ) {
					$this->check_for_paypal_payout();
				}
			}

			if ( empty( $method ) || ( ( 'paypal_payout' !== $method ) && ! empty( $check_for_payouts ) ) ) {
				// Check if masspay is enabled on the user's account.
				if ( ! class_exists( 'AFWC_PayPal_Masspay' ) ) {
					include_once AFWC_PLUGIN_DIRPATH . '/includes/gateway/paypal/class-afwc-paypal-masspay.php';
				}

				if ( class_exists( 'AFWC_PayPal_Masspay' ) && is_callable( 'AFWC_PayPal_Masspay', 'mass_pay_status' ) ) {
					$mass_pay = AFWC_PayPal_Masspay::get_instance();
					$status   = $mass_pay->mass_pay_status();

					if ( ! empty( $status ) && 'not_allowed' !== $status ) {
						$method = 'paypal_masspay';
					} elseif ( 'not_allowed' === $status ) {
						$method = 'paypal_payout';
					}

					update_option( 'afwc_commission_payout_method', $method, 'no' );
				}
			}

			/**
			 * Filters the PayPal Payout method.
			 *
			 * @param string  $method The method can be 'paypal_payout|paypal_masspay' in default.
			 * @param array   $this   The arguments.
			 */
			$method = ( ! empty( $method ) ) ? $method : 'paypal_masspay';
			return apply_filters( 'afwc_commission_payout_method', $method, array( 'source' => $this ) );
		}

		/**
		 * Retrieve an API access token.
		 *
		 * @return array|WP_Error
		 */
		public function get_token() {
			$request = wp_remote_post(
				$this->api_endpoint . '/oauth2/token',
				array(
					'headers'     => array(
						'Accept'          => 'application/json',
						'Accept-Language' => 'en_US',
						'Authorization'   => 'Basic ' . base64_encode( $this->api_username . ':' . $this->api_password ), // phpcs:ignore
					),
					'timeout'     => 45,
					'httpversion' => '1.1',
					'body'        => array(
						'grant_type' => 'client_credentials',
					),
				)
			);

			$body    = wp_remote_retrieve_body( $request );
			$code    = wp_remote_retrieve_response_code( $request );
			$message = wp_remote_retrieve_response_message( $request );

			$body_data = json_decode( $body, true );

			if ( 200 === $code && 'ok' === strtolower( $message ) && ! is_wp_error( $request ) ) {
				return $body_data;
			}

			$error_data = trim( ( ! empty( $body_data['error'] ) ? $body_data['error'] : '' ) . ', ' . ( ! empty( $body_data['error_description'] ) ? $body_data['error_description'] : '' ), ', ' );

			/* translators: 1: Error code 2: Error message */
			$error_code_message = sprintf( _x( 'PayPal Payout token request failed with error code: %1$s, Message: %2$s', 'paypal payout error code & message for error log', 'affiliate-for-woocommerce' ), $code, $message );

			if ( is_wp_error( $request ) ) {
				/* translators: Error Info. */
				$error_details = ! empty( $error_data ) ? $error_code_message . sprintf( _x( ', error data: %s', 'paypal payout error data for error log', 'affiliate-for-woocommerce' ), $error_data ) : $error_code_message;

				Affiliate_For_WooCommerce::log( 'error', $error_details );
				return $request;
			}

			Affiliate_For_WooCommerce::log( 'error', $error_code_message );

			return new WP_Error( $code, $message, $error_data );
		}

		/**
		 * Function to get API setting status
		 *
		 * @return array $status
		 */
		public function get_api_setting_status() {
			$status = array(
				'value'    => 'no',
				'desc'     => __( 'Disabled', 'affiliate-for-woocommerce' ),
				'desc_tip' => sprintf(
					/* translators: Documentation link for how to payout commissions */
					_x( 'To enable, follow all the requirements mentioned %s.', 'description of how to enable Payout via PayPal', 'affiliate-for-woocommerce' ),
					'<a href="' . AFWC_DOC_DOMAIN . 'how-to-payout-commissions-in-affiliate-for-woocommerce/" target="_blank">' . _x( 'here', 'link text of commission payout documentation', 'affiliate-for-woocommerce' ) . '</a>'
				),
			);

			if ( true === $this->is_set_credentials( $this->payout_method ) ) {
				$paypal_setting_page_url = add_query_arg(
					array(
						'page'    => 'wc-settings',
						'tab'     => 'checkout',
						'section' => ( 'paypal_masspay' === $this->payout_method ) ? 'paypal' : 'ppcp-gateway',
					),
					admin_url( 'admin.php' )
				);

				/* translators: 1: Location of the PayPal Payout Documentation */
				$payout_deprecated_html = ( 'paypal_masspay' === $this->payout_method ) ? '<p class="notice notice-warning" style="width: fit-content;padding: 5px 8px;">' . sprintf( __( '[PayPal Payouts] PayPal have deprecated MassPay API and it will be removed soon. So we recommend you switch to a new Payout API for smoother commission payouts. Refer to %s for more details.', 'affiliate-for-woocommerce' ), '<a href="' . esc_url( AFWC_DOC_DOMAIN . 'how-to-payout-commissions-in-affiliate-for-woocommerce/' ) . '" target="_blank">' . esc_html__( 'this doc', 'affiliate-for-woocommerce' ) . '</a>' ) . ' </p>' : '';

				$status = array(
					'value'    => 'yes',
					'desc'     => __( 'Enabled', 'affiliate-for-woocommerce' ),
					'desc_tip' => sprintf(
						/* translators: 1: Link to the API Credentials 2: PayPal Masspay deprecated notice */
						_x( 'To disable, empty the API credentials from %1$s. %2$s', 'description of how to disable Payout via PayPal', 'affiliate-for-woocommerce' ),
						'<a href="' . esc_url( $paypal_setting_page_url ) . '" target="_blank">' . _x( 'here', 'link text for api credential page', 'affiliate-for-woocommerce' ) . '</a>',
						$payout_deprecated_html
					),
				);
			}

			return $status;
		}

		/**
		 * Check if the api credentials are available.
		 *
		 * @param string $method Payment method name.
		 * @return bool $is_set
		 */
		public function is_set_credentials( $method = '' ) {
			$is_set = false;

			if ( ! empty( $this->api_username ) || ! empty( $this->api_password ) ) {
				$is_set = true;
			}

			// Check if api_signature is exists for paypal_masspay payout method.
			if ( 'paypal_masspay' === $method && empty( $this->api_signature ) ) {
				$is_set = false;
			}

			return $is_set;
		}

		/**
		 * Check and update if PayPal Payout is available.
		 *
		 * @return void.
		 */
		public function check_for_paypal_payout() {
			$credentials = $this->get_paypal_payments_credentials();

			if ( ! empty( $credentials ) && ! empty( $credentials['username'] ) && ! empty( $credentials['password'] ) ) {
				update_option( 'afwc_commission_payout_method', 'paypal_payout', 'no' );
			}
		}

		/**
		 * Get affiliate's PayPal email address - if available.
		 *
		 * @param int $affiliate_id The Affiliate ID.
		 * @return string
		 */
		public function get_payout_meta( $affiliate_id = 0 ) {
			if ( empty( $affiliate_id ) ) {
				return '';
			}

			$paypal_email_address = get_user_meta( $affiliate_id, 'afwc_paypal_email', true );
			return $paypal_email_address;
		}
	}
}
