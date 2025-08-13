<?php
/**
 * Class to handle payouts related settings
 *
 * @package     affiliate-for-woocommerce/includes/admin/settings/
 * @since       7.18.0
 * @version     1.4.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AFWC_Payouts_Admin_Settings' ) ) {

	/**
	 * Main class get payouts section settings
	 */
	class AFWC_Payouts_Admin_Settings {

		/**
		 * Variable to hold instance of AFWC_Payouts_Admin_Settings
		 *
		 * @var self $instance
		 */
		private static $instance = null;

		/**
		 * Section name
		 *
		 * @var string $section
		 */
		private $section = 'payouts';

		/**
		 * Get single instance of this class
		 *
		 * @return AFWC_Payouts_Admin_Settings Singleton object of this class
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 */
		private function __construct() {
			add_filter( "afwc_{$this->section}_section_admin_settings", array( $this, 'get_section_settings' ) );

			// automatic payouts.
			add_action( 'woocommerce_admin_field_afwc_ap_includes_list', array( $this, 'render_ap_include_list_input' ) );
			add_filter( 'woocommerce_admin_settings_sanitize_option_afwc_automatic_payout_includes', array( $this, 'sanitize_ap_include_list' ), 10, 2 );

			// Ajax action for automatic payouts.
			add_action( 'wp_ajax_afwc_search_ap_includes_list', array( $this, 'afwc_json_search_include_ap_list' ) );
		}

		/**
		 * Method to get payouts section settings
		 *
		 * @return array
		 */
		public function get_section_settings() {

			// Check if PayPal API is enabled.
			$paypal_api_settings = array();
			if ( is_callable( array( 'AFWC_PayPal_API', 'get_instance' ) ) ) {
				$afwc_paypal_api_instance = AFWC_PayPal_API::get_instance();
				if ( is_callable( array( $afwc_paypal_api_instance, 'get_api_setting_status' ) ) ) {
					$paypal_api_settings = $afwc_paypal_api_instance->get_api_setting_status();
				}
			}

			// Payout via coupons.
			$allowed_coupon_types   = apply_filters( 'afwc_allowed_coupon_type_for_payouts', array( 'fixed_cart' ), array( 'source' => $this ) );
			$available_coupon_types = array();
			// Intersect two arrays to get result as coupon_type => coupon label for the setting.
			$available_coupon_types = array_intersect_key( wc_get_coupon_types(), array_flip( $allowed_coupon_types ) );

			/* translators: %s Plugin URI of Affiliate Store Credit Payouts Integration for WooCommerce. */
			$payout_via_coupon_desc = apply_filters( 'afwc_coupon_payouts_setting_description', sprintf( _x( 'Select coupon types to allow payouts via coupons. Leave it blank to disable payout via coupons. Use %s (free WordPress plugin) to issue a Store Credit/Gift Certificate payout.', 'setting description', 'affiliate-for-woocommerce' ), '<a target="_blank" href="https://wordpress.org/plugins/affiliate-store-credit-payouts-integration-for-woocommerce/">' . _x( 'Affiliate Store Credit Payouts Integration', 'free plugin name', 'affiliate-for-woocommerce' ) . '</a>' ), array( 'source' => $this ) );

			// Stripe.
			$redirect_uri               = afwc_myaccount_dashboard_url() . '?afwc-tab=resources';
			$get_stripe_connect_details = 'https://dashboard.stripe.com/settings/connect/onboarding-options/oauth';

			$afwc_payouts_admin_settings = array(
				array(
					'title' => _x( 'Payouts', 'Payouts setting section title', 'affiliate-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'afwc_payouts_admin_settings',
				),
				array(
					'name'              => _x( 'Refund period', 'setting name', 'affiliate-for-woocommerce' ),
					'desc'              => _x( 'A refund isn\'t a successful referral. Therefore, enter how many days to wait before paying commissions for successful referrals. If you don\'t have a refund period, enter 0. Each referral within the refund period will be marked to show the remaining days in the refund period. In case automatic payouts are enabled, referrals within the refund period will not be included in it.', 'setting description', 'affiliate-for-woocommerce' ),
					'id'                => 'afwc_order_refund_period_in_days',
					'type'              => 'number',
					'default'           => 30,
					'autoload'          => false,
					'desc_tip'          => false,
					'custom_attributes' => array(
						'min' => 0,
					),
					'placeholder'       => _x( 'Enter the number of days. Default is 30.', 'placeholder for refund window setting', 'affiliate-for-woocommerce' ),
				),
				array(
					'name'              => _x( 'Minimum affiliate commission for payout', 'setting name', 'affiliate-for-woocommerce' ),
					'desc'              => _x( 'An affiliate earnings must reach this minimum threshold value to qualify for commission payouts. This setting ensures that only eligible order\'s referrals are included for payouts. In case automatic payouts are enabled, order referrals below this threshold will not qualify for payouts.', 'setting description', 'affiliate-for-woocommerce' ),
					'id'                => 'afwc_minimum_commission_balance',
					'type'              => 'number',
					'default'           => 50,
					'autoload'          => false,
					'desc_tip'          => false,
					'custom_attributes' => array(
						'min' => 1,
					),
					'placeholder'       => _x( 'Enter minimum commission amount. Default is 50.', 'placeholder for payment day setting', 'affiliate-for-woocommerce' ),
				),
				array(
					'name'     => _x( 'PayPal email address', 'setting name', 'affiliate-for-woocommerce' ),
					'desc'     => _x( 'Allow affiliates to enter their PayPal email address from their My Account > Affiliates > Profile for PayPal payouts', 'setting description', 'affiliate-for-woocommerce' ),
					'desc_tip' => _x( 'Disabling this will not show it to affiliates in their account.', 'setting description tip', 'affiliate-for-woocommerce' ),
					'id'       => 'afwc_allow_paypal_email',
					'type'     => 'checkbox',
					'default'  => 'no',
					'autoload' => false,
				),
				array(
					'name'              => _x( 'Payout via PayPal', 'setting name', 'affiliate-for-woocommerce' ),
					'type'              => 'checkbox',
					'default'           => 'no',
					'autoload'          => false,
					'value'             => ( ! empty( $paypal_api_settings['value'] ) ) ? $paypal_api_settings['value'] : 'no',
					'desc'              => ( ! empty( $paypal_api_settings['desc'] ) ) ? $paypal_api_settings['desc'] : '',
					'desc_tip'          => ( ! empty( $paypal_api_settings['desc_tip'] ) ) ? $paypal_api_settings['desc_tip'] : '',
					'id'                => 'afwc_paypal_payout',
					'custom_attributes' => array(
						'disabled' => 'disabled',
					),
				),
				array(
					'name'              => _x( 'Payout via Coupons', 'setting name', 'affiliate-for-woocommerce' ),
					'desc'              => $payout_via_coupon_desc,
					'id'                => 'afwc_enabled_for_coupon_payout',
					'type'              => 'multiselect',
					'class'             => 'wc-enhanced-select',
					'desc_tip'          => false,
					'options'           => $available_coupon_types,
					'row_class'         => ( 'no' === get_option( 'woocommerce_enable_coupons' ) ? 'afwc-hide' : '' ), // Hide if WooCommerce > Enable coupons is disabled.
					'autoload'          => false,
					'custom_attributes' => array(
						'data-placeholder' => _x( 'Select the coupon type...', 'placeholder for allowed coupon types for payouts', 'affiliate-for-woocommerce' ),
					),
				),
				// Stripe settings
				// allow enabling + same will show connect in affiliate's account.
				array(
					'name'     => _x( 'Payout via Stripe', 'setting name', 'affiliate-for-woocommerce' ),
					'desc'     => _x( 'Pay commissions via Stripe by allowing affiliates to link their Stripe accounts to your store', 'setting description', 'affiliate-for-woocommerce' ),
					'id'       => 'afwc_enable_stripe_payout',
					'type'     => 'checkbox',
					'default'  => 'no',
					'autoload' => false,
					'desc_tip' => _x( 'Disabling this will stop payouts through Stripe.', 'setting description tip', 'affiliate-for-woocommerce' ),
				),
				// to accept publishable_key.
				array(
					'name'              => _x( 'Stripe Publishable Key', 'setting name', 'affiliate-for-woocommerce' ),
					'desc'              => sprintf(
						/* translators: 1: Link to Stripe API keys dashboard 2: Stripe Connect documentation for testing with OAuth  */
						_x( 'To locate, go to <a href="%1$s" target="_blank">Stripe Dashboard > Developers > API keys ></a> <strong>Standard keys > Publishable key</strong> (more info: <a href="%2$s" target="_blank">%2$s</a>). Mandatory to add this for commission payouts to work.', 'setting description', 'affiliate-for-woocommerce' ),
						'https://dashboard.stripe.com/apikeys',
						'https://docs.stripe.com/keys'
					),
					'id'                => 'afwc_stripe_live_publishable_key',
					'type'              => 'text',
					'placeholder'       => _x( 'Values starting with "pk_"', 'Placeholder for Stripe Publishable Key setting', 'affiliate-for-woocommerce' ),
					'autoload'          => false,
					'desc_tip'          => false,
					'custom_attributes' => array(
						'data-afwc-hide-if' => 'afwc_enable_stripe_payout',
					),
				),
				// to accept secret_key.
				array(
					'name'              => _x( 'Stripe Secret Key', 'setting name', 'affiliate-for-woocommerce' ),
					'desc'              => sprintf(
						/* translators: 1: Link to Stripe dashboard 2: Stripe Connect documentation for testing with OAuth  */
						_x( 'To locate, go to <a href="%1$s" target="_blank">Stripe Dashboard > Developers > API keys ></a> <strong>Standard keys > Secret key > Reveal live key</strong> (more info: <a href="%2$s" target="_blank">%2$s</a>). Mandatory to add this for commission payouts to work.', 'setting description', 'affiliate-for-woocommerce' ),
						'https://dashboard.stripe.com/apikeys',
						'https://docs.stripe.com/keys'
					),
					'id'                => 'afwc_stripe_live_secret_key',
					'type'              => 'text',
					'placeholder'       => _x( 'Values starting with "sk_"', 'Placeholder for Stripe Secret Key setting', 'affiliate-for-woocommerce' ),
					'autoload'          => false,
					'desc_tip'          => false,
					'custom_attributes' => array(
						'data-afwc-hide-if' => 'afwc_enable_stripe_payout',
					),
				),
				// to accept client_id.
				array(
					'name'              => _x( 'Stripe Client ID', 'setting name', 'affiliate-for-woocommerce' ),
					'desc'              => sprintf(
						/* translators: 1: Link to Stripe dashboard 2: Stripe Connect documentation for testing with OAuth  */
						_x( 'To locate, go to <a href="%1$s" target="_blank">Stripe Dashboard > Settings > Connect > Onboarding options > OAuths ></a> <strong>Live mode client ID</strong> (more info: <a href="%2$s" target="_blank">%2$s</a>). Mandatory to add this for commission payouts to work.', 'setting description', 'affiliate-for-woocommerce' ),
						$get_stripe_connect_details,
						'https://docs.stripe.com/connect/testing#using-oauth'
					),
					'id'                => 'afwc_stripe_connect_live_client_id',
					'type'              => 'text',
					'placeholder'       => _x( 'Account ID - minimum 35 characters', 'Placeholder for Stripe Client ID setting', 'affiliate-for-woocommerce' ),
					'autoload'          => false,
					'desc_tip'          => false,
					'custom_attributes' => array(
						'data-afwc-hide-if' => 'afwc_enable_stripe_payout',
					),
				),
				// 3. to set redirect URI.
				array(
					'name'              => _x( 'Add redirect URIs', 'setting name', 'affiliate-for-woocommerce' ),
					'desc'              => sprintf(
						/* translators: 1: Link to Stripe dashboard 2: current site's affiliate's my account endpoint for resources/profile tab  */
						_x( 'A <strong>Redirection URI is required</strong> when users connect their account to your site.<br><br>Go to <a href="%1$s" target="_blank">Stripe Dashboard > Settings > Connect > Onboarding options > OAuths ></a> <strong>Redirects</strong> section and add the following URl to redirect: <code>%2$s</code><br><br>Redirects URI can be defined on test and live mode, we would recommend to test both scenarios.', 'setting description', 'affiliate-for-woocommerce' ),
						$get_stripe_connect_details,
						$redirect_uri
					),
					'id'                => 'afwc_stripe_add_redirect_uris',
					'type'              => 'checkbox',
					'default'           => 'no',
					'autoload'          => false,
					'desc_tip'          => _x( 'It is mandatory to set this in your Stripe account to process commission payouts. Otherwise, payouts won\'t be processed.', 'setting description tip', 'affiliate-for-woocommerce' ),
					// We do not want to allow selecting checkbox of this setting. So hide it for now.
					'custom_attributes' => array(
						'data-afwc-hide-if' => 'afwc_enable_stripe_payout',
						'style'             => 'display: none;',
					),
				),
				array(
					'name'     => _x( 'Automatic payouts', 'setting name', 'affiliate-for-woocommerce' ),
					'desc'     => _x( 'Enable this to automatically pay your affiliates', 'setting description', 'affiliate-for-woocommerce' ),
					'desc_tip' => _x( 'Supports PayPal, Stripe, Payout via Coupons - if enabled.', 'setting description tip', 'affiliate-for-woocommerce' ),
					'id'       => 'afwc_enable_automatic_payouts',
					'type'     => 'checkbox',
					'default'  => 'no',
					'autoload' => false,
				),
				array(
					'name'              => _x( 'Automatic payouts include affiliates', 'setting name for automatic payouts to include affiliates', 'affiliate-for-woocommerce' ),
					'desc'              => _x( 'Select up to 10 affiliates for automatic commission payouts (beta launch). Affiliates qualify for automatic payouts if they have set a PayPal email address OR connected their Stripe account OR selected payout via coupons as their payout method from their account.', 'Admin setting description for affiliates to include for automatic payouts', 'affiliate-for-woocommerce' ),
					'id'                => 'afwc_automatic_payout_includes',
					'type'              => 'afwc_ap_includes_list',
					'class'             => 'afwc-automatic-payouts-includes-search wc-enhanced-select',
					'placeholder'       => _x( 'Search affiliates by email, username or name', 'Admin setting placeholder to search affiliates to include them for automatic payouts', 'affiliate-for-woocommerce' ),
					'options'           => get_option( 'afwc_automatic_payout_includes', array() ),
					'row_class'         => ( 'no' === get_option( 'afwc_enable_automatic_payouts', 'no' ) ) ? 'afwc-hide' : '',
					'custom_attributes' => array(
						'data-afwc-hide-if' => 'afwc_enable_automatic_payouts',
					),
				),
				array(
					'name'              => _x( 'Maximum commission to pay an affiliate', 'setting name', 'affiliate-for-woocommerce' ),
					'desc'              => _x( 'Set the maximum commission an affiliate can receive in automatic payouts. Set it to 0 if no limit. This setting ensures automatic payouts stay within a specified limit. Referrals exceeding this limit won\'t be included in automatic payouts.', 'setting description', 'affiliate-for-woocommerce' ),
					'id'                => 'afwc_maximum_commission_balance',
					'type'              => 'number',
					'default'           => 0,
					'autoload'          => false,
					'desc_tip'          => false,
					'row_class'         => ( 'no' === get_option( 'afwc_enable_automatic_payouts', 'no' ) ) ? 'afwc-hide' : '',
					'custom_attributes' => array(
						'min'               => 0,
						'data-afwc-hide-if' => 'afwc_enable_automatic_payouts',
					),
					'placeholder'       => _x( 'Enter maximum commission amount. Default is 0.', 'placeholder for payment day setting', 'affiliate-for-woocommerce' ),
				),
				array(
					'name'              => _x( 'Commission payout day', 'setting name', 'affiliate-for-woocommerce' ),
					'desc'              => _x( 'Automatic commission payouts will be issued on this fixed day of each month you enter in the box.  Leaving it blank will set the default day to the 15th of each month. If the entered date falls between the 28th and 31st, payouts will be automatically sent on the last day of that particular month.', 'setting description', 'affiliate-for-woocommerce' ),
					'id'                => 'afwc_commission_payout_day',
					'type'              => 'number',
					'default'           => 15,
					'autoload'          => false,
					'desc_tip'          => false,
					'row_class'         => ( 'no' === get_option( 'afwc_enable_automatic_payouts', 'no' ) ) ? 'afwc-hide' : '',
					'custom_attributes' => array(
						'min'               => 1,
						'max'               => 31,
						'data-afwc-hide-if' => 'afwc_enable_automatic_payouts',
					),
					'placeholder'       => _x( 'Enter day of the month', 'placeholder for payment day setting', 'affiliate-for-woocommerce' ),
				),
				array(
					'type' => 'sectionend',
					'id'   => "afwc_{$this->section}_admin_settings",
				),
			);

			return $afwc_payouts_admin_settings;
		}

		/**
		 * Method to get the affiliates who has either PayPal or Stripe meta set - to allow in automatic payouts.
		 *
		 * @param string $term The value.
		 * @param bool   $for_search Whether the method will be used for searching or fetching the details by id.
		 *
		 * @return array The list of found affiliate users.
		 */
		public function get_affiliates_with_automatic_payout_meta_data( $term = '', $for_search = false ) {
			if ( empty( $term ) ) {
				return array();
			}

			global $affiliate_for_woocommerce;

			$values = array();

			if ( true === $for_search ) {
				$affiliate_search = array(
					'search'         => '*' . $term . '*',
					'search_columns' => array( 'ID', 'user_nicename', 'user_login', 'user_email', 'display_name' ),
					'number'         => 10, // We are fetching only 10 affiliates in the search - to start off.
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						// Check affiliate has either PayPal or Stripe details or payout method as coupons in their meta.
						'relation' => 'OR',
						array(
							'key'     => 'afwc_paypal_email',
							'value'   => '',
							'compare' => '!=',
						),
						array(
							'relation' => 'AND',
							array(
								'key'     => 'afwc_stripe_user_id',
								'value'   => '',
								'compare' => '!=',
							),
							array(
								'key'     => 'afwc_stripe_access_token',
								'value'   => '',
								'compare' => '!=',
							),
						),
						// Coupons will not have additional meta, so fallback on the affiliate's payout meta.
						array(
							'key'     => 'afwc_payout_method',
							'value'   => array( 'coupon-fixed-cart', 'wsc-store-credit' ),
							'compare' => 'IN',
						),
					),
				);
			} else {
				$affiliate_search = array(
					'include' => ( ! is_array( $term ) ) ? (array) $term : $term,
				);
			}

			$values = is_callable( array( $affiliate_for_woocommerce, 'get_affiliates' ) ) ? $affiliate_for_woocommerce->get_affiliates( $affiliate_search ) : array();

			return $values;
		}

		/**
		 * Method to rendering the include list input field.
		 *
		 * @param array $value The value.
		 *
		 * @return void.
		 */
		public function render_ap_include_list_input( $value = array() ) {
			if ( empty( $value ) ) {
				return;
			}

			$id                = ! empty( $value['id'] ) ? $value['id'] : '';
			$options           = ! empty( $value['options'] ) ? $value['options'] : array();
			$field_description = is_callable( array( 'WC_Admin_Settings', 'get_field_description' ) ) ? WC_Admin_Settings::get_field_description( $value ) : array();
			?>	
				<tr valign="top" class="<?php echo ! empty( $value['row_class'] ) ? esc_attr( $value['row_class'] ) : ''; ?>">
					<th scope="row" class="titledesc"> 
						<label for="<?php echo esc_attr( $id ); ?>"> <?php echo ( ! empty( $value['title'] ) ? esc_html( $value['title'] ) : '' ); ?> </label>
					</th>
					<td class="forminp">
						<select
							name="<?php echo esc_attr( ! empty( $value['field_name'] ) ? $value['field_name'] : $id ); ?>[]"
							id="<?php echo esc_attr( $id ); ?>"
							style="<?php echo ! empty( $value['css'] ) ? esc_attr( $value['css'] ) : ''; ?>"
							class="<?php echo ! empty( $value['class'] ) ? esc_attr( $value['class'] ) : ''; ?>"
							data-placeholder="<?php echo ! empty( $value['placeholder'] ) ? esc_attr( $value['placeholder'] ) : ''; ?>"
							multiple="multiple"
							<?php echo is_callable( array( 'AFWC_Admin_Settings', 'get_html_attributes_string' ) ) ? wp_kses_post( AFWC_Admin_Settings::get_html_attributes_string( $value ) ) : ''; ?>
						>
						<?php
						foreach ( $options as $ids ) {
							$current_list = $this->get_affiliates_with_automatic_payout_meta_data( $ids );
							if ( ! empty( $current_list ) && is_array( $current_list ) ) {
								foreach ( $current_list as $id => $text ) {
									?>
										<option
											value="<?php echo esc_attr( $id ); ?>"
											selected='selected'
										><?php echo ! empty( $text ) ? esc_html( $text ) : ''; ?></option>
										<?php
								}
							}
						}
						?>
						</select> <?php echo ! empty( $field_description['description'] ) ? wp_kses_post( $field_description['description'] ) : ''; ?>
					</td>
				</tr>
			<?php
		}

		/**
		 * Method to sanitize and format the value for ltc exclude list.
		 *
		 * @param array $value The value.
		 *
		 * @return array.
		 */
		public function sanitize_ap_include_list( $value = array() ) {
			// Return empty array if the value is empty.
			if ( empty( $value ) ) {
				return array();
			}

			$list = array();
			foreach ( $value as $id ) {
				$list[] = $id;
			}

			return $list;
		}

		/**
		 * Ajax callback function to search the affiliates and affiliate tag.
		 */
		public function afwc_json_search_include_ap_list() {
			check_admin_referer( 'afwc-search-include-ap-list', 'security' );

			$term = ( ! empty( $_GET['term'] ) ) ? (string) urldecode( wp_strip_all_tags( wp_unslash( $_GET ['term'] ) ) ) : '';
			if ( empty( $term ) ) {
				wp_die();
			}

			$searched_list = $this->get_affiliates_with_automatic_payout_meta_data( $term, true );
			if ( empty( $searched_list ) || ! is_array( $searched_list ) ) {
				wp_die();
			}

			$data = array();
			foreach ( $searched_list as $affiliate_user_id => $affiliate_details ) {
				$data[ $affiliate_user_id ] = $affiliate_details;
			}

			wp_send_json( $data );
		}

	}

}

AFWC_Payouts_Admin_Settings::get_instance();
