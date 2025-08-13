<?php
/**
 * Class for migrating affiliates data from Indeed Ultimate Affiliate Pro.
 *
 * @package     affiliate-for-woocommerce/includes/migrations
 * @since       8.34.0
 * @version     1.1.0
 */

namespace AFWC\Migrations;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\AFWC_Migration', false ) ) {
	require_once AFWC_PLUGIN_DIRPATH . '/includes/abstracts/class-afwc-migration.php';
}

if ( ! class_exists( Indeed_Affiliate_Pro::class ) && class_exists( '\AFWC_Migration' ) ) {

	/**
	 * Migration class for Indeed Ultimate Affiliate Pro plugin.
	 */
	class Indeed_Affiliate_Pro extends \AFWC_Migration {

		/**
		 * Variable to hold plugin slug.
		 *
		 * @var string
		 */
		public $source_slug = 'indeed-affiliate-pro';

		/**
		 * Instance of the current class
		 *
		 * @var self
		 */
		private static $instance = null;

		/**
		 * Get the single instance of this class.
		 *
		 * @return self
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {

			// Disable affiliate tracking in the plugin after migration completed.
			if ( $this->is_completed() ) {
				$this->update_setting();
				add_filter( 'uap_default_options_group_filter', array( $this, 'update_default_setting' ), 99 );
				// Do not allow users to update the referral variable option.
				add_filter( 'pre_update_option_uap_referral_variable', '__return_null' );
				add_filter( 'afwc_id_for_order', array( $this, 'maybe_get_affiliate_id_from_cookie' ), 999 );
				// Set the affiliate ID for renewal orders.
				add_filter( 'afwc_get_affiliate_by_order', array( $this, 'get_affiliate_by_subscription_order' ), 10, 2 );
				// Prevent the referral tracking.
				add_filter( 'uap_filter_before_valid_referral', '__return_false' );
			}

			// Migration will run only if the source plugin is active.
			if ( afwc_is_plugin_active( 'indeed-affiliate-pro/indeed-affiliate-pro.php' ) ) {
				parent::__construct();
			}
		}

		/**
		 * Retrieve affiliates from the old plugin's DB table that have not been migrated.
		 *
		 * @throws \Exception If any error during the process.
		 * @return array Affiliates data to migrate.
		 */
		protected function get_affiliates() {
			global $wpdb;

			$limit = $this->get_batch_limit();
			$limit = ! empty( $limit ) ? intval( $limit ) : -1;

			try {
				// Get the default referral format option.
				$ref_format = get_option( 'uap_default_ref_format' );

				// If the referral format is set to 'username', include the username in the results.
				if ( 'username' === $ref_format ) {
					if ( $limit > 0 ) {
						$data = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
							$wpdb->prepare(
								"SELECT 
									aff.id            AS affiliate_id, 
									aff.uid           AS user_id, 
									u.user_login      AS identifier,
									aff.start_data    AS signup_date
								FROM {$wpdb->prefix}uap_affiliates AS aff
								INNER JOIN {$wpdb->users} AS u
									ON u.ID = aff.uid
								LEFT JOIN {$wpdb->usermeta} AS um
									ON ( um.user_id = aff.uid AND um.meta_key = 'afwc_migrated_affiliate_id' )
								WHERE um.umeta_id IS NULL
									AND aff.status = 1
								LIMIT %d",
								$limit
							),
							'ARRAY_A'
						);
					} else {
						$data = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
							"SELECT 
									aff.id            AS affiliate_id, 
									aff.uid           AS user_id, 
									u.user_login      AS identifier,
									aff.start_data    AS signup_date
								FROM {$wpdb->prefix}uap_affiliates AS aff
								INNER JOIN {$wpdb->users} AS u
									ON u.ID = aff.uid
								LEFT JOIN {$wpdb->usermeta} AS um 
									ON ( um.user_id = aff.uid AND um.meta_key = 'afwc_migrated_affiliate_id' )
								WHERE um.umeta_id IS NULL
									AND aff.status = 1",
							'ARRAY_A'
						);
					}
				} else {
					// For any other referral format, use the original query without retrieving the username.
					if ( $limit > 0 ) {
						$data = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
							$wpdb->prepare(
								"SELECT 
									aff.id            AS affiliate_id, 
									aff.uid           AS user_id, 
									aff.start_data    AS signup_date
								FROM {$wpdb->prefix}uap_affiliates AS aff
								LEFT JOIN {$wpdb->usermeta} AS um
									ON ( um.user_id = aff.uid AND um.meta_key = 'afwc_migrated_affiliate_id' )
								WHERE um.umeta_id IS NULL
									AND aff.status = 1
								LIMIT %d",
								$limit
							),
							'ARRAY_A'
						);
					} else {
						$data = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
							"SELECT 
									aff.id            AS affiliate_id, 
									aff.uid           AS user_id, 
									aff.start_data    AS signup_date
								FROM {$wpdb->prefix}uap_affiliates AS aff
								LEFT JOIN {$wpdb->usermeta} AS um 
									ON ( um.user_id = aff.uid AND um.meta_key = 'afwc_migrated_affiliate_id' )
								WHERE um.umeta_id IS NULL
									AND aff.status = 1",
							'ARRAY_A'
						);
					}
				}

				if ( ! empty( $wpdb->last_error ) ) {
					throw new \Exception( $wpdb->last_error );
				}
			} catch ( \Exception $e ) {
				\Affiliate_For_WooCommerce::log_error( __METHOD__, ( is_callable( array( $e, 'getMessage' ) ) ) ? $e->getMessage() : '' );
			}

			return ! empty( $data ) ? $data : array();
		}

		/**
		 * Get the setup array for the migration.
		 *
		 * @return array Setup array.
		 */
		protected function get_setups() {

			global $indeed_db;

			$values = is_callable( array( $indeed_db, 'get_all_ump_wp_options' ) )
				? $indeed_db->get_all_ump_wp_options()
				: array();

			if ( empty( $values ) || ! is_array( $values ) ) {
				return array();
			}

			if ( is_callable( array( $indeed_db, 'return_settings_from_wp_option' ) ) ) {
				// Get the values from the Indeed Affiliate Pro settings which are not available in the values array.

				if ( ! isset( $value['uap_stripe_v3_enable'] ) ) {
					$strip_configs = is_callable( array( $indeed_db, 'return_settings_from_wp_option' ) )
						? $indeed_db->return_settings_from_wp_option( 'stripe_v3' )
						: array();

					if ( ! empty( $strip_configs ) && is_array( $strip_configs ) ) {
						// merge the stripe v3 settings with the values array.
						$values = array_merge( $values, $strip_configs );
					}
				}

				if ( ! isset( $value['uap_admin_referral_notifications_enable'] ) ) {
					$admin_referral_notifications = is_callable( array( $indeed_db, 'return_settings_from_wp_option' ) )
						? $indeed_db->return_settings_from_wp_option( 'admin_referral_notifications' )
						: array();

					if ( ! empty( $admin_referral_notifications ) && is_array( $admin_referral_notifications ) ) {
						$values['uap_admin_referral_notifications_enable'] = isset( $admin_referral_notifications['uap_admin_referral_notifications_enable'] ) ? $admin_referral_notifications['uap_admin_referral_notifications_enable'] : '';
					}
				}
			}

			return array(
				array(
					'callback' => array( $this, 'update_option' ),
					'args'     => array(
						'afwc_pname',
						isset( $values['uap_referral_variable'] ) ? $values['uap_referral_variable'] : '',
					),
				),
				array(
					'callback' => array( $this, 'update_option' ),
					'args'     => array(
						'afwc_cookie_expiration',
						isset( $values['uap_cookie_expire'] ) ? $values['uap_cookie_expire'] : '',
					),
				),
				array(
					'callback' => array( $this, 'update_option' ),
					'args'     => array(
						'afwc_auto_add_affiliate',
						isset( $values['uap_workflow_referral_status_dont_automatically_change'] )
							? ( in_array( $values['uap_workflow_referral_status_dont_automatically_change'], array( 1, '1' ), true ) ? 'no' : 'yes' )
							: '',
					),
				),
				array(
					'callback' => array( $this, 'update_option' ),
					'args'     => array(
						'afwc_enable_lifetime_commissions',
						isset( $values['uap_lifetime_commissions_enable'] ) ? $values['uap_lifetime_commissions_enable'] : '',
					),
				),
				array(
					'callback' => array( $this, 'update_option' ),
					'args'     => array(
						'afwc_enable_stripe_payout',
						isset( $values['uap_stripe_v3_enable'] ) ? $values['uap_stripe_v3_enable'] : '',
					),
				),
				array(
					'callback' => array( $this, 'update_option' ),
					'args'     => array(
						'afwc_stripe_live_publishable_key',
						isset( $values['uap_stripe_v3_publishable_key'] ) ? $values['uap_stripe_v3_publishable_key'] : '',
					),
				),
				array(
					'callback' => array( $this, 'update_option' ),
					'args'     => array(
						'afwc_stripe_live_secret_key',
						isset( $values['uap_stripe_v3_secret_key'] ) ? $values['uap_stripe_v3_secret_key'] : '',
					),
				),
				array(
					'callback' => array( $this, 'update_option' ),
					'args'     => array(
						'afwc_stripe_connect_live_client_id',
						isset( $values['uap_stripe_v3_client_id'] ) ? $values['uap_stripe_v3_client_id'] : '',
					),
				),
				array(
					'callback' => array( $this, 'update_option' ),
					'args'     => array(
						'afwc_allow_self_refer',
						isset( $values['uap_allow_own_referrence_enable'] ) ? $values['uap_allow_own_referrence_enable'] : '',
					),
				),
				array(
					'callback' => array( $this, 'update_option' ),
					'args'     => array(
						'afwc_enable_multi_tier',
						isset( $values['uap_mlm_enable'] ) ? $values['uap_mlm_enable'] : '',
					),
				),
				array(
					'callback' => array( $this, 'update_option' ),
					'args'     => array(
						'afwc_credit_affiliate',
						isset( $values['uap_rewrite_referrals_enable'] ) ? ( in_array( $values['uap_rewrite_referrals_enable'], array( 1, '1' ), true ) ? 'last' : 'first' ) : '',
					),
				),
				array(
					'callback' => array( $this, 'update_option' ),
					'args'     => array(
						'afwc_use_referral_coupons',
						isset( $values['uap_coupons_enable'] ) ? $values['uap_coupons_enable'] : '',
					),
				),
				array(
					'callback' => array( $this, 'update_option' ),
					'args'     => array(
						'afwc_use_pretty_referral_links',
						isset( $values['uap_friendly_links'] ) ? $values['uap_friendly_links'] : '',
					),
				),
				array(
					'callback' => array( $this, 'update_option' ),
					'args'     => array(
						'afwc_allow_custom_affiliate_identifier',
						isset( $values['uap_custom_affiliate_slug_on'] ) ? $values['uap_custom_affiliate_slug_on'] : '',
					),
				),
				array(
					'callback' => array( $this, 'update_option' ),
					'args'     => array(
						'afwc_add_referral_in_admin_emails',
						isset( $values['uap_admin_referral_notifications_enable'] ) ? $values['uap_admin_referral_notifications_enable'] : '',
					),
				),
				array(
					'callback' => array( $this, 'update_option' ),
					'args'     => array(
						'afwc_enable_landing_pages',
						isset( $values['uap_landing_pages_enabled'] ) ? $values['uap_landing_pages_enabled'] : '',
					),
				),
				array(
					'callback' => array( $this, 'update_option' ),
					'args'     => array(
						'afwc_show_product_referral_url',
						isset( $values['uap_product_links_enabled'] ) ? $values['uap_product_links_enabled'] : '',
					),
				),
				array(
					'callback' => array( $this, 'maybe_disable_recurring_commission' ),
					'args'     => array(
						isset( $values['uap_reccuring_referrals_enable'] ) ? $values['uap_reccuring_referrals_enable'] : '',
					),
				),
				array(
					'callback' => array( $this, 'maybe_enable_summary_email' ),
					'args'     => array(
						isset( $values['uap_periodically_reports_enable'] ) ? $values['uap_periodically_reports_enable'] : '',
					),
				),
			);
		}

		/**
		 * Get the user meta array map of user meta of both plugin.
		 *
		 * @return array User meta map (Holds AFW keys as key and UAP keys as value).
		 */
		public function get_user_meta_map() {
			return array(
				'afwc_payout_method' => 'uap_affiliate_payment_type',
				'afwc_paypal_email'  => 'uap_affiliate_paypal_email',
			);
		}

		/**
		 * Update the option from source plugin to the affiliate for woocommerce plugin.
		 *
		 * @param string $afwc_key The affiliate for woocommerce option key.
		 * @param mixed  $value The source value key.
		 *
		 * @return void
		 */
		public function update_option( $afwc_key = '', $value = '' ) {
			if ( '' === $value ) {
				return; // Do not update if the source value is blank.
			}

			if ( 0 === $value || '0' === $value ) {
				$value = 'no';
			}

			if ( 1 === $value || '1' === $value ) {
				$value = 'yes';
			}

			! empty( $value ) && update_option( $afwc_key, $value, 'no' );
		}

		/**
		 * Update default settings to disable tracking.
		 *
		 * @param array $settings Default settings array.
		 *
		 * @return array Updated settings array.
		 */
		public function update_default_setting( $settings = array() ) {
			if ( empty( $settings ) || empty( $settings['general-settings'] ) || empty( $settings['general-settings']['uap_referral_variable'] ) ) {
				return $settings;
			}

			$settings['general-settings']['uap_referral_variable'] = '';
			return $settings;
		}

		/**
		 * Update the setting for the plugin and set it as blank.
		 *
		 * @return void
		 */
		public function update_setting() {
			update_option( 'uap_referral_variable', '' );
		}

		/**
		 * Method to get the affiliate ID from cookie generated by the this plugin.
		 *
		 * @param int $affiliate_id The affiliate ID.
		 *
		 * @return int Return the migrated source plugin's affiliate ID if exists.
		 */
		public function maybe_get_affiliate_id_from_cookie( $affiliate_id = 0 ) {
			if ( ! empty( $affiliate_id ) || empty( $_COOKIE['uap_referral'] ) ) {
				return $affiliate_id; // Return the default affiliate ID if available or browser does not UAP cookie.
			}

			$cookie_data = maybe_unserialize( sanitize_text_field( wp_unslash( $_COOKIE['uap_referral'] ) ) );

			if ( ! is_array( $cookie_data ) || empty( $cookie_data['affiliate_id'] ) ) {
				return $affiliate_id;
			}

			$migrate = is_callable( array( Migrate_Data::class, 'get_instance' ) ) ? Migrate_Data::get_instance() : null;

			// Get the new affiliate ID after migrating from UAP plugin.
			$new_affiliate_id = is_callable( array( $migrate, 'get_new_affiliate_id_by_migrated_affiliate_id' ) )
				? $migrate->get_new_affiliate_id_by_migrated_affiliate_id( intval( $cookie_data['affiliate_id'] ) )
				: 0;

			return ! empty( $new_affiliate_id ) ? intval( $new_affiliate_id ) : $affiliate_id;
		}

		/**
		 * Retrieves affiliate information associated with a specific subscription order.
		 *
		 * @param array $affiliate_details Details of the affiliates.
		 * @param array $args The arguments.
		 *
		 * @return array Updated affiliate details if available, otherwise the original details.
		 */
		public function get_affiliate_by_subscription_order( $affiliate_details = array(), $args = array() ) {
			// Do not override the details if affiliate details are already present in the order. Order Id should not be empty.
			if ( ! empty( $affiliate_details ) || empty( $args['order_id'] ) || ! function_exists( 'wcs_order_contains_subscription' ) ) {
				return $affiliate_details;
			}

			if ( ! wcs_order_contains_subscription( $args['order_id'] ) ) {
				return $affiliate_details;
			}

			// This callback method is only to override affiliate details during commission tracking.
			// Note: It does not support the 'all' data type and will only run for affiliate ID.
			if ( ! doing_filter( 'afwc_id_for_order' ) || 'all' === $args['data'] ) {
				return $affiliate_details;
			}

			global $wpdb;

			try {
				$affiliate_details = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->prepare(
						"SELECT affiliate_id FROM {$wpdb->prefix}uap_referrals WHERE reference = %d",
						$args['order_id']
					),
					'ARRAY_A'
				);

				// Get the new affiliate ID from the migrated data.
				if ( ! empty( $affiliate_details ) && ! empty( $affiliate_details['affiliate_id'] ) ) {
					$migrate = is_callable( array( Migrate_Data::class, 'get_instance' ) ) ? Migrate_Data::get_instance() : null;

					// Get the new affiliate ID after migrating from UAP plugin.
					$new_affiliate_id = is_callable( array( $migrate, 'get_new_affiliate_id_by_migrated_affiliate_id' ) )
						? $migrate->get_new_affiliate_id_by_migrated_affiliate_id( intval( $affiliate_details['affiliate_id'] ) )
						: 0;

					if ( ! empty( $new_affiliate_id ) ) {
						return array( 'affiliate_id' => intval( $new_affiliate_id ) );
					}
				}
			} catch ( \Exception $e ) {
				\Affiliate_For_WooCommerce::log_error( __METHOD__, ( is_callable( array( $e, 'getMessage' ) ) ) ? $e->getMessage() : '' );
			}

			return $affiliate_details;
		}

		/**
		 * Method to disable recurring commissions in AFWC if it is disabled in UAP.
		 *
		 * @param string $uap_option_enabled The option value from UAP.
		 *
		 * @return void
		 */
		public function maybe_disable_recurring_commission( $uap_option_enabled = '' ) {
			if ( ! empty( $uap_option_enabled ) && '1' === $uap_option_enabled ) {
				return;
			}
			// If the recurring commissions are disabled in UAP, then disable it in AFWC.
			if ( is_callable( array( \AFWC_Commission_Plans::class, 'create_plan_for_disable_recurring_commissions' ) ) ) {
				\AFWC_Commission_Plans::create_plan_for_disable_recurring_commissions();
			}
		}

		/**
		 * Method to enable summary email in AFWC if it is enabled in UAP.
		 *
		 * @param string $uap_option_enabled The option value from UAP.
		 *
		 * @return void
		 */
		public function maybe_enable_summary_email( $uap_option_enabled = '' ) {
			$summary_email_setting            = get_option( 'woocommerce_afwc_summary_email_reports_settings', array() );
			$enable                           = '1' === $uap_option_enabled || 1 === $uap_option_enabled ? 'yes' : 'no';
			$summary_email_setting['enabled'] = $enable;

			// Update the summary email setting in AFWC.
			update_option( 'woocommerce_afwc_summary_email_reports_settings', $summary_email_setting );
		}
	}
}
