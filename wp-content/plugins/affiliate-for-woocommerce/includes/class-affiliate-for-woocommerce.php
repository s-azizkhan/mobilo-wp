<?php
/**
 * Main class for Affiliate For WooCommerce
 *
 * @package     affiliate-for-woocommerce/includes/
 * @since       1.0.0
 * @version     1.26.6
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Affiliate_For_WooCommerce' ) ) {

	/**
	 * Main class for Affiliate For WooCommerce
	 */
	final class Affiliate_For_WooCommerce {

		/**
		 * Variable to hold instance of this class
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Get single instance of this class
		 *
		 * @return Affiliate_For_WooCommerce Singleton object of this class
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

			$this->constants();
			add_action( 'init', array( $this, 'init_afwc' ) );
			add_action( 'woocommerce_init', array( $this, 'init_afwc_on_wc' ) );
			$this->includes();

			if ( is_admin() ) {
				add_action( 'admin_menu', array( $this, 'add_afwc_admin_menu' ), 20 );
				add_action( 'admin_head', array( $this, 'add_afwc_remove_submenu' ) );
			}

			// Calling it early so our process will be completed before anything else.
			add_action( 'wp_loaded', array( $this, 'afwc_parse_request' ), 1 );

			add_action( 'valid-paypal-standard-ipn-request', array( $this, 'handle_ipn_request' ) );

			// Show after add to cart button.
			add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'add_product_referral_button' ), 99 );

			// Ajax for updating the product affiliate link.
			add_action( 'wc_ajax_afwc_get_product_affiliate_link', array( $this, 'get_product_affiliate_link' ) );
			add_action( 'wp_ajax_afwc_json_search_affiliates', array( $this, 'afwc_json_search_affiliates' ) );

			// Register the scripts.
			add_action( 'wp_enqueue_scripts', array( $this, 'register_global_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_global_scripts' ) );
		}

		/**
		 * Function to handle WC compatibility related function call from appropriate class
		 *
		 * @param string $function_name Function to call.
		 * @param array  $arguments     Array of arguments passed while calling $function_name.
		 *
		 * @return mixed Result of function call.
		 */
		public function __call( $function_name = '', $arguments = array() ) {

			if ( empty( $function_name ) || ! is_callable( 'SA_WC_Compatibility', $function_name ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( 'SA_WC_Compatibility::' . $function_name, $arguments );
			} else {
				return call_user_func( 'SA_WC_Compatibility::' . $function_name );
			}
		}

		/**
		 * Function to define constants
		 */
		public function constants() {

			if ( ! defined( 'AFWC_AFFILIATES_COOKIE_NAME' ) ) {
				define( 'AFWC_AFFILIATES_COOKIE_NAME', 'affiliate_for_woocommerce' );
			}
			if ( ! defined( 'AFWC_CAMPAIGN_COOKIE_NAME' ) ) {
				define( 'AFWC_CAMPAIGN_COOKIE_NAME', 'afwc_campaign' );
			}
			if ( ! defined( 'AFWC_HIT_COOKIE_NAME' ) ) {
				define( 'AFWC_HIT_COOKIE_NAME', 'afwc_hit' );
			}
			if ( ! defined( 'AFWC_PLUGIN_BASENAME' ) ) {
				define( 'AFWC_PLUGIN_BASENAME', plugin_basename( dirname( AFWC_PLUGIN_FILE ) ) );
			}
			if ( ! defined( 'AFWC_PLUGIN_DIR' ) ) {
				define( 'AFWC_PLUGIN_DIR', dirname( plugin_basename( AFWC_PLUGIN_FILE ) ) );
			}
			if ( ! defined( 'AFWC_PLUGIN_URL' ) ) {
				define( 'AFWC_PLUGIN_URL', plugins_url( AFWC_PLUGIN_DIR ) );
			}
			if ( ! defined( 'AFWC_PLUGIN_DIR_PATH' ) ) {
				define( 'AFWC_PLUGIN_DIR_PATH', plugin_dir_path( AFWC_PLUGIN_FILE ) );
			}
			if ( ! defined( 'AFWC_COOKIE_TIMEOUT_BASE' ) ) {
				define( 'AFWC_COOKIE_TIMEOUT_BASE', 86400 );
			}
			if ( ! defined( 'AFWC_REGEX_PATTERN' ) ) {
				define( 'AFWC_REGEX_PATTERN', 'affiliates/([^/]+)/?$' );
			}
			if ( ! defined( 'AFWC_DEFAULT_COMMISSION_STATUS' ) ) {
				define( 'AFWC_DEFAULT_COMMISSION_STATUS', get_option( 'afwc_default_commission_status' ) );
			}
			if ( ! defined( 'AFWC_REFERRAL_STATUS_PENDING' ) ) {
				define( 'AFWC_REFERRAL_STATUS_PENDING', 'pending' );
			}
			if ( ! defined( 'AFWC_REFERRAL_STATUS_DRAFT' ) ) {
				define( 'AFWC_REFERRAL_STATUS_DRAFT', 'draft' );
			}
			if ( ! defined( 'AFWC_REFERRAL_STATUS_PAID' ) ) {
				define( 'AFWC_REFERRAL_STATUS_PAID', 'paid' );
			}
			if ( ! defined( 'AFWC_REFERRAL_STATUS_UNPAID' ) ) {
				define( 'AFWC_REFERRAL_STATUS_UNPAID', 'unpaid' );
			}
			if ( ! defined( 'AFWC_REFERRAL_STATUS_REJECTED' ) ) {
				define( 'AFWC_REFERRAL_STATUS_REJECTED', 'rejected' );
			}

			// My account - default limit to load records.
			if ( ! defined( 'AFWC_MY_ACCOUNT_DEFAULT_BATCH_LIMIT' ) ) {
				define( 'AFWC_MY_ACCOUNT_DEFAULT_BATCH_LIMIT', 15 );
			}

			// Admin - default limit to load orders and payouts.
			if ( ! defined( 'AFWC_ADMIN_DASHBOARD_DEFAULT_BATCH_LIMIT' ) ) {
				define( 'AFWC_ADMIN_DASHBOARD_DEFAULT_BATCH_LIMIT', 50 );
			}

			if ( ! defined( 'AFWC_TIMEZONE_STR' ) ) {
				$offset       = get_option( 'gmt_offset' );
				$timezone_str = sprintf( '%+02d:%02d', (int) $offset, ( $offset - floor( $offset ) ) * 60 );
				define( 'AFWC_TIMEZONE_STR', $timezone_str );
			}

			// Set the charset for SQL Queries.
			if ( ! defined( 'AFWC_SQL_CHARSET' ) ) {
				define( 'AFWC_SQL_CHARSET', 'utf32' );
			}

			// Set the collation for SQL Queries.
			if ( ! defined( 'AFWC_SQL_COLLATION' ) ) {
				define( 'AFWC_SQL_COLLATION', 'utf32_general_ci' );
			}

			// Set documentation link - WooCommerce.com.
			if ( ! defined( 'AFWC_DOC_DOMAIN' ) ) {
				define( 'AFWC_DOC_DOMAIN', 'https://woocommerce.com/document/affiliate-for-woocommerce/' );
			}
			// Set plugin review link - WooCommerce.com.
			if ( ! defined( 'AFWC_REVIEW_URL' ) ) {
				define( 'AFWC_REVIEW_URL', 'https://woocommerce.com/products/affiliate-for-woocommerce/?review' );
			}
			// Set contact human support link - WooCommerce.com.
			if ( ! defined( 'AFW_CONTACT_SUPPORT_URL' ) ) {
				define( 'AFW_CONTACT_SUPPORT_URL', 'https://woocommerce.com/my-account/contact-support/?select=affiliate-for-woocommerce#contact-us' );
			}
		}

		/**
		 * Init Affiliate for WooCommerce functions when WordPress Initializes.
		 */
		public function init_afwc() {
			$this->load_plugin_textdomain();
			$this->register_user_tags_taxonomy();
			$this->set_payout_method();
		}

		/**
		 * Init Affiliate for WooCommerce constants when WooCommerce Initializes.
		 */
		public function init_afwc_on_wc() {
			// Constant for WooCommerce store currency symbol.
			if ( ! defined( 'AFWC_CURRENCY' ) ) {
				define( 'AFWC_CURRENCY', get_woocommerce_currency_symbol() );
			}

			// Constant for WooCommerce store currency - in code.
			if ( ! defined( 'AFWC_CURRENCY_CODE' ) ) {
				define( 'AFWC_CURRENCY_CODE', get_woocommerce_currency() );
			}
		}

		/**
		 * Load plugin Localization files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
		 *
		 * Locales found in:
		 *      - WP_LANG_DIR/affiliate-for-woocommerce/affiliate-for-woocommerce-LOCALE.mo
		 *      - WP_LANG_DIR/plugins/affiliate-for-woocommerce-LOCALE.mo
		 */
		public function load_plugin_textdomain() {
			$locale = apply_filters( 'plugin_locale', determine_locale(), 'affiliate-for-woocommerce' );

			unload_textdomain( 'affiliate-for-woocommerce' );
			load_textdomain( 'affiliate-for-woocommerce', WP_LANG_DIR . '/affiliate-for-woocommerce/affiliate-for-woocommerce-' . $locale . '.mo' );
			load_plugin_textdomain( 'affiliate-for-woocommerce', false, AFWC_PLUGIN_BASENAME . '/languages' );
		}

		/**
		 * Function to register affiliate tags taxonomy
		 */
		public function register_user_tags_taxonomy() {
			register_taxonomy(
				'afwc_user_tags', // taxonomy name.
				'user', // object for which the taxonomy is created.
				array( // taxonomy details.
					'public'       => true,
					'labels'       => array(
						'name'          => __( 'Affiliate Tags', 'affiliate-for-woocommerce' ),
						'singular_name' => __( 'Affiliate Tag', 'affiliate-for-woocommerce' ),
						'menu_name'     => __( 'Affiliate Tags', 'affiliate-for-woocommerce' ),
						'search_items'  => __( 'Search Affiliate Tag', 'affiliate-for-woocommerce' ),
						'popular_items' => __( 'Popular Affiliate Tags', 'affiliate-for-woocommerce' ),
						'all_items'     => __( 'All Affiliate Tags', 'affiliate-for-woocommerce' ),
						'edit_item'     => __( 'Edit Affiliate Tag', 'affiliate-for-woocommerce' ),
						'update_item'   => __( 'Update Affiliate Tag', 'affiliate-for-woocommerce' ),
						'add_new_item'  => __( 'Add New Affiliate Tag', 'affiliate-for-woocommerce' ),
						'new_item_name' => __( 'New Affiliate Tag Name', 'affiliate-for-woocommerce' ),
						'not_found'     => __( 'No Affiliate Tags found', 'affiliate-for-woocommerce' ),
					),
					'show_in_menu' => false,
					'hierarchical' => true,
				)
			);

			$default_affiliate_tags    = array( 'Gold', 'Silver', 'Bronze', 'Platinum', 'Dormant', 'Active', 'Promoter', 'Influencer' );
			$afwc_default_tags_created = get_option( 'afwc_default_tags_created', false );
			if ( ! $afwc_default_tags_created ) {
				foreach ( $default_affiliate_tags  as $value ) {
					wp_insert_term( $value, 'afwc_user_tags' );
				}
				update_option( 'afwc_default_tags_created', true, 'no' );
			}
		}

		/**
		 * Includes
		 */
		public function includes() {
			include_once 'integration/woocommerce/compat/class-sa-wc-compatibility.php';
			include_once 'affiliate-for-woocommerce-functions.php';
			include_once 'afw-wp-compatibility-functions.php';
			include_once 'class-afwc-user-agent-parser.php';

			include_once 'rules/class-rule.php';
			$afwc_base_rule_classes = glob( AFWC_PLUGIN_DIRPATH . '/includes/rules/types/*.php' );
			foreach ( $afwc_base_rule_classes as $rule_class ) {
				if ( is_file( $rule_class ) ) {
					include_once $rule_class;
				}
			}
			include_once 'rules/class-context.php';
			include_once 'rules/class-rule-registry.php';
			include_once 'rules/class-group.php';
			include_once 'rules/class-groups.php';
			include_once 'commission-rules/class-afwc-commission-rules.php';

			include_once 'class-afwc-multi-tier-commission-calculation.php';
			include_once 'class-afwc-plans.php';

			// Include all common classes.
			include_once 'common/class-afwc-commission-plans.php';
			include_once 'common/class-afwc-payout-invoice.php';
			include_once 'common/class-afwc-user-roles-handler.php';
			include_once 'common/class-afwc-affiliate.php';
			include_once 'common/class-afwc-coupon.php';
			include_once 'common/class-afwc-landing-page.php';

			if ( is_admin() ) {
				include_once 'migrations/class-afwc-migrate-affiliates.php';
				include_once 'admin/class-afwc-admin-settings.php';
				foreach ( glob( AFWC_PLUGIN_DIRPATH . '/includes/admin/settings/*.php' ) as $setting_section_file ) {
					if ( is_file( $setting_section_file ) ) {
						include_once $setting_section_file;
					}
				}
				include_once 'admin/class-afwc-admin-affiliates.php';
				include_once 'admin/class-afwc-admin-summary-reports.php';
				include_once 'admin/class-afwc-admin-dashboard.php';
				include_once 'admin/class-afwc-campaign-dashboard.php';
				include_once 'admin/class-afwc-commission-dashboard.php';
				include_once 'admin/class-afwc-admin-affiliate.php';
				include_once 'admin/class-afwc-admin-docs.php';
				include_once 'admin/class-afwc-privacy.php';
				include_once 'admin/class-afwc-admin-notifications.php';
				include_once 'admin/class-afwc-admin-affiliate-users.php';
				include_once 'admin/class-afwc-admin-order-affiliate-details.php';
				include_once 'admin/class-afwc-admin-link-unlink-in-order.php';
				include_once 'admin/class-afwc-admin-housekeeping.php';
				include_once 'admin/class-afwc-onboarding.php';
				include_once 'admin/class-afwc-pending-payout-dashboard.php';
				include_once 'admin/class-afwc-system-status-report.php';

				// Admin Dashboard widget should be displayed if current user can mange affiliates.
				if ( afwc_current_user_can_manage_affiliate() ) {
					include_once 'admin/class-afwc-admin-dashboard-widget.php';
				}
			}

			include_once 'admin/class-afwc-admin-new-referral-email.php';

			include_once 'class-afwc-db-background-process.php';

			// Stripe payouts.
			if ( 'yes' === get_option( 'afwc_enable_stripe_payout', 'no' ) ) {
				include_once 'gateway/stripe/class-afwc-stripe-functions.php';
				include_once 'gateway/stripe/class-afwc-stripe-api.php';
				include_once 'gateway/stripe/class-afwc-stripe-connect.php';
			}

			if ( class_exists( 'WC_Subscriptions_Core_Plugin' ) || class_exists( 'WC_Subscriptions' ) ) {
				include_once 'integration/woocommerce-subscriptions/class-wcs-afwc-compatibility.php';
			}

			if ( 'yes' === get_option( 'woocommerce_enable_coupons' ) ) {
				$allowed_coupon_types_for_payout = get_option( 'afwc_enabled_for_coupon_payout', array() );
				if ( ! empty( $allowed_coupon_types_for_payout ) && is_array( $allowed_coupon_types_for_payout ) && in_array( 'fixed_cart', $allowed_coupon_types_for_payout, true ) ) {
					include_once 'integration/woocommerce/class-afwc-coupon-api.php';
				}

				if ( afwc_is_plugin_active( 'woocommerce-smart-coupons/woocommerce-smart-coupons.php' ) ) {
					if ( 'yes' === get_option( 'afwc_use_referral_coupons', 'yes' ) ) {
						include_once 'integration/woocommerce-smart-coupons/class-wsc-afwc-compatibility.php';
					}
				}
			}
			if ( afwc_is_plugin_active( 'elementor/elementor.php' ) && afwc_is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {
				include_once 'integration/elementor/class-afwc-elementor-form-actions.php';
				include_once 'integration/elementor/class-afwc-elementor-dynamic-tags.php';
			}
			if ( afwc_is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) && ! afwc_is_plugin_active( 'affiliate-contact-form-7-integration-for-woocommerce/affiliate-contact-form-7-integration-for-woocommerce.php' ) ) {
				include_once 'integration/contact-form-7/class-afwc-cf7-registration-form.php';
			}

			include_once 'upgrades/class-afwc-ip-field-updates.php';
			include_once 'upgrades/class-afwc-signup-date-batch-assign.php';
			include_once 'upgrades/class-afwc-paypal-payout-method-assign.php';

			include_once 'gateway/paypal/class-afwc-paypal-api.php'; // TODO: remove usage from my account and then move this file to include under only admin.
			include_once 'class-afwc-api.php';

			include_once 'class-afwc-db-upgrade.php';
			include_once 'class-afwc-emails.php';
			include_once 'class-afwc-registration-submissions.php';
			include_once 'class-afwc-rewrite-rules.php';
			include_once 'class-afwc-merge-tags.php';
			include_once 'class-afwc-visits.php';
			include_once 'class-afwc-multi-tier.php';
			include_once 'class-afwc-report-background-emailer.php';
			include_once 'class-afwc-admin-summary-email-scheduler.php';
			include_once 'payouts/class-afwc-payout-handler.php';
			include_once 'migrations/class-migrate-data.php';

			// commission payouts.
			include_once 'commission-payouts/class-afwc-commission-payouts.php';
			include_once 'commission-payouts/class-afwc-automatic-payouts-handler.php';

			include_once 'frontend/class-afwc-my-account.php';
			include_once 'frontend/class-afwc-registration-form.php';
			include_once 'frontend/class-afwc-templates.php';

			if ( 'yes' === get_option( 'woocommerce_analytics_enabled' ) ) {
				include_once 'integration/woocommerce/analytics/class-afwc-wc-orders-analytics.php';
			}

			// Admin bar link should be displayed if option is enabled and current user can mange affiliates.
			if ( 'yes' === get_option( 'afwc_show_admin_bar_menu', 'yes' ) && afwc_current_user_can_manage_affiliate() ) {
				include_once 'class-afwc-admin-bar-menu.php';
			}
		}

		/**
		 * Function to log messages generated by Affiliate plugin
		 *
		 * @param  string $level   Message type. Valid values: debug, info, notice, warning, error, critical, alert, emergency.
		 * @param  string $message The message to log.
		 */
		public static function log( $level = 'notice', $message = '' ) {
			if ( empty( $message ) ) {
				return;
			}

			if ( function_exists( 'wc_get_logger' ) ) {
				$logger  = wc_get_logger();
				$context = array( 'source' => 'affiliate-for-woocommerce' );
				$logger->log( $level, $message, $context );
			} else {
				include_once plugin_dir_path( WC_PLUGIN_FILE ) . 'includes/class-wc-logger.php';
				$logger = new WC_Logger();
				$logger->add( 'affiliate-for-woocommerce', $message );
			}
		}

		/**
		 * Admin menus
		 */
		public function add_afwc_admin_menu() {
			/* translators: A small arrow */
			add_submenu_page( 'woocommerce', __( 'Affiliates Dashboard', 'affiliate-for-woocommerce' ), __( 'Affiliates', 'affiliate-for-woocommerce' ), 'manage_woocommerce', 'affiliate-for-woocommerce', 'AFWC_Admin_Dashboard::afwc_dashboard_page' ); // phpcs:ignore WordPress.WP.Capabilities.Unknown

			$get_page = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore

			if ( empty( $get_page ) ) {
				return;
			}

			if ( 'affiliate-for-woocommerce-documentation' === $get_page ) {
				add_submenu_page( 'woocommerce', _x( 'Getting Started', 'Page title for setup guide page', 'affiliate-for-woocommerce' ), _x( 'Getting Started', 'Menu name for setup guide page', 'affiliate-for-woocommerce' ), 'manage_woocommerce', 'affiliate-for-woocommerce-documentation', 'AFWC_Admin_Docs::afwc_docs' ); // phpcs:ignore WordPress.WP.Capabilities.Unknown
			}
		}

		/**
		 * Remove Affiliate For WooCommerce's unnecessary submenus.
		 */
		public function add_afwc_remove_submenu() {
			remove_submenu_page( 'woocommerce', 'affiliate-for-woocommerce-documentation' );
		}

		/**
		 * Function to parse affiliates url & check for valid requests
		 */
		public function afwc_parse_request() {
			// Avoid any ajax request.
			if ( wp_doing_ajax() ) {
				return;
			}

			$pname = afwc_get_pname();
			// Use $_REQUEST to access and use values inside this method.
			$pname        = ( empty( $_REQUEST[ $pname ] ) && ! empty( $_REQUEST['ref'] ) ) ? 'ref' : $pname; // phpcs:ignore
			$affiliate_id = 0;

			if ( 'yes' === get_option( 'afwc_use_pretty_referral_links', 'no' ) ) {

				$url = afwc_get_current_url();
				if ( strpos( $url, $pname ) === false ) {
					return;
				}

				$values       = wp_parse_url( $url );
				$parsed       = explode( '/', $values['path'] );
				$parsed_count = count( $parsed );
				for ( $i = 0; $i < $parsed_count; $i++ ) {
					if ( ! empty( $parsed[ $i ] ) && $parsed[ $i ] === $pname ) {
						$affiliate_id = ( ! empty( $parsed[ $i + 1 ] ) ) ? $parsed[ $i + 1 ] : 0;

						// unset affiliate tracking param (pname) & affiliate identifier.
						if ( isset( $parsed[ $i ] ) ) {
							unset( $parsed[ $i ] );
						}
						if ( isset( $parsed[ $i + 1 ] ) ) {
							unset( $parsed[ $i + 1 ] );
						}
						break;
					}
				}
				// remove empty array values.
				$parsed       = array_filter( $parsed, 'strlen' );
				$query_string = implode( '/', $parsed );
				$current_url  = $values['scheme'] . '://' . $values['host'] . '/' . $query_string . ( ! empty( $values['query'] ) ? '/?' . $values['query'] : '' );
			} else {
				if ( empty( $_REQUEST[ $pname ] ) ) { // phpcs:ignore
					return;
				}

				$affiliates_pname = ( defined( 'AFFILIATES_PNAME' ) ) ? AFFILIATES_PNAME : 'affiliates';
				$migrated_pname   = get_option( 'afwc_migrated_pname', $affiliates_pname );

				// Handle older affiliates link through migrated pname.
				if ( isset( $_REQUEST[ $migrated_pname ] ) ) { // phpcs:ignore
					$id           = wc_clean( wp_unslash( $_REQUEST[ $migrated_pname ] ) ); // phpcs:ignore
					$affiliate_id = afwc_get_user_id_based_on_affiliate_id( $id );
				} elseif ( isset( $_REQUEST[ $pname ] ) ) { // phpcs:ignore
					$affiliate_id = wc_clean( wp_unslash( $_REQUEST[ $pname ] ) ); // phpcs:ignore
				} elseif ( isset( $_REQUEST['ref'] ) ) { // phpcs:ignore
					$affiliate_id = wc_clean( wp_unslash( $_REQUEST['ref'] ) ); // phpcs:ignore
				}

				if ( isset( $_REQUEST ) && isset( $_REQUEST[ $pname ] ) ) { // phpcs:ignore
					// Remove affiliate tracking param (pname) from current URL to get URL that can we use for redirect later if needed.
					$current_url = remove_query_arg( $pname, afwc_get_current_url() );
					// note that we must use delimiters other than / as these are used in AFFILIATES_REGEX_PATTERN.
					$current_url = preg_replace( '#' . str_replace( get_option( 'afwc_pname', 'ref' ), $pname, AFWC_REGEX_PATTERN ) . '#', '', $current_url );
				}
			}

			$affiliate_id = afwc_get_affiliate_id_by_identifier( $affiliate_id );

			if ( ! empty( $affiliate_id ) ) {
				$this->handle_hit( $affiliate_id );

				if ( ! empty( $current_url ) && ! apply_filters( 'afwc_preserve_referral_tracking_parameter_in_url', false, array( 'source' => $this ) ) ) {
					$status = apply_filters( 'affiliates_redirect_status_code', 302 );
					$status = intval( $status );
					switch ( $status ) {
						case 300:
						case 301:
						case 302:
						case 303:
						case 304:
						case 305:
						case 306:
						case 307:
							break;
						default:
							$status = 302;
					}

					wp_safe_redirect( $current_url, $status );
					exit;
				}
			}
		}

		/**
		 * Handle hits by referral
		 *
		 * @param integer $affiliate_id The affiliate id.
		 */
		public function handle_hit( $affiliate_id = 0 ) {

			// Prevent the hit tracking if the affiliate id is missing or referral determination is set for first referrer when an affiliate id is present in the cookies.
			if ( empty( $affiliate_id ) || ( ! empty( $_COOKIE[ AFWC_AFFILIATES_COOKIE_NAME ] ) && 'first' === get_option( 'afwc_credit_affiliate', 'last' ) ) ) {
				return;
			}

			$affiliate = new AFWC_Affiliate( $affiliate_id );

			if ( ! $affiliate instanceof AFWC_Affiliate || empty( $affiliate->ID ) || ! is_callable( array( $affiliate, 'is_valid' ) ) || ! $affiliate->is_valid() ) {
				return;
			}

			$encoded_affiliate_id = afwc_encode_affiliate_id( $affiliate_id );
			$days                 = get_option( 'afwc_cookie_expiration', 60 );
			$expire               = ( $days > 0 ) ? ( time() + AFWC_COOKIE_TIMEOUT_BASE * $days ) : 0;
			$params               = array();

			if ( ! empty( $encoded_affiliate_id ) ) {
				// Set affiliate ID in cookie.
				setcookie(
					AFWC_AFFILIATES_COOKIE_NAME,
					$encoded_affiliate_id,
					$expire,
					COOKIEPATH ? COOKIEPATH : '/',
					COOKIE_DOMAIN,
					( wc_site_is_https() && is_ssl() )
				);
			}

			// check for campaign.
			$utm_campaign = ( ! empty( $_REQUEST ) && ! empty( $_REQUEST['utm_campaign'] ) ) ? wc_clean( wp_unslash( $_REQUEST['utm_campaign'] ) ) : '';// phpcs:ignore
			$campaign_id  = ( ! empty( $utm_campaign ) ) ? afwc_get_campaign_id_by_slug( $utm_campaign ) : 0;

			// Set campaign ID in cookie.
			setcookie(
				AFWC_CAMPAIGN_COOKIE_NAME,
				$campaign_id,
				$expire,
				COOKIEPATH ? COOKIEPATH : '/',
				COOKIE_DOMAIN,
				( wc_site_is_https() && is_ssl() )
			);

			$params['campaign_id'] = $campaign_id;

			$affiliate_api = AFWC_API::get_instance();
			if ( is_callable( array( $affiliate_api, 'track_visitor' ) ) ) {
				$hit_id = $affiliate_api->track_visitor( $affiliate_id, 0, 'link', $params );

				if ( ! empty( $hit_id ) ) {
					// Set Hit ID in cookie.
					setcookie(
						AFWC_HIT_COOKIE_NAME,
						$hit_id,
						$expire,
						COOKIEPATH ? COOKIEPATH : '/',
						COOKIE_DOMAIN,
						( wc_site_is_https() && is_ssl() )
					);
				}
			}
		}

		/**
		 * Get referral type
		 *
		 * @param  integer $affiliate_id The affiliate id.
		 * @param  array   $used_coupons The used coupons.
		 * @return string
		 */
		public function get_referral_type( $affiliate_id = 0, $used_coupons = array() ) {
			if ( ! empty( $affiliate_id ) && ! empty( $used_coupons ) ) {
				$afwc_coupon      = AFWC_Coupon::get_instance();
				$referral_coupons = $afwc_coupon->get_referral_coupon( array( 'user_id' => $affiliate_id ) );
				if ( ! empty( $referral_coupons ) && is_array( $referral_coupons ) ) {
					foreach ( $referral_coupons as $coupon_id => $coupon_code ) {
						$referral_coupon = wc_strtolower( $coupon_code );
						if ( ! empty( $referral_coupon ) && in_array( $referral_coupon, array_map( 'wc_strtolower', $used_coupons ), true ) ) {
							return 'coupon';
						}
					}
				}
			}
			return 'link';
		}

		/**
		 * Handle IPN requests
		 *
		 * Used to save transaction ID of the commission payout
		 *
		 * @param array $posted The posted data.
		 */
		public function handle_ipn_request( $posted = array() ) {

			if ( empty( $posted )
				|| empty( $posted['ipn_track_id'] )
				|| empty( $posted['masspay_txn_id_1'] )
				|| empty( $posted['txn_type'] ) || 'masspay' !== $posted['txn_type']
				|| empty( $posted['unique_id_1'] ) || 'afwc_mass_payment' !== $posted['unique_id_1']
			) {
				return;
			}

			global $wpdb;

			$correlation_id = $posted['ipn_track_id'];
			$transaction_id = $posted['masspay_txn_id_1'];

			$search  = 'CorrelationID:' . $correlation_id;
			$replace = 'TransactionID:' . $transaction_id;

			// phpcs:disable
			$result = $wpdb->query(
									$wpdb->prepare("UPDATE {$wpdb->prefix}afwc_payouts
													SET payout_notes = REPLACE( payout_notes, %s, %s )",
													$search,
													$replace
												)
			);
			// phpcs:enable
		}

		/**
		 * Insert a setting or an array of settings after another specific setting by its ID.
		 *
		 * @since 1.2.1
		 * @param array  $settings                The original list of settings.
		 * @param string $insert_after_setting_id The setting id to insert the new setting after.
		 * @param array  $new_setting             The new setting to insert. Can be a single setting or an array of settings.
		 * @param string $insert_type             The type of insert to perform. Can be 'single_setting' or 'multiple_settings'. Optional. Defaults to a single setting insert.
		 *
		 * @credit: WooCommerce Subscriptions
		 */
		public static function insert_setting_after( &$settings = array(), $insert_after_setting_id = '', $new_setting = array(), $insert_type = 'single_setting' ) {
			if ( ! is_array( $settings ) ) {
				return;
			}

			$original_settings = $settings;
			$settings          = array();

			foreach ( $original_settings as $setting ) {
				$settings[] = $setting;

				if ( isset( $setting['id'] ) && $insert_after_setting_id === $setting['id'] ) {
					if ( 'single_setting' === $insert_type ) {
						$settings[] = $new_setting;
					} else {
						$settings = array_merge( $settings, $new_setting );
					}
				}
			}
		}

		/**
		 * Generate a unique string.
		 *
		 * @param  string $prefix The prefix.
		 * @return string
		 */
		public static function uniqid( $prefix = null ) {
			$uniqid = self::number_to_alphabet( gmdate( 'dmyHis', self::get_offset_timestamp() ) );
			if ( ! empty( $prefix ) ) {
				$uniqid = $prefix . $uniqid;
			}
			return $uniqid;
		}

		/**
		 * Convert number to alphabet
		 *
		 * @param  string $number The number to convert.
		 * @return string
		 */
		public static function number_to_alphabet( $number = null ) {
			if ( ! is_null( $number ) ) {
				$alphabets     = range( 'a', 'z' );
				$absint_number = absint( $number );
				$length        = strlen( $number );
				if ( 2 < $length || 25 < $absint_number ) {
					$numbers = str_split( strval( $number ), 2 );
				} else {
					$numbers = str_split( strval( $number ), 1 );
				}
				$string = '';
				foreach ( $numbers as $num ) {
					if ( ( 1 < strlen( $num ) && 10 > absint( $num ) ) || 25 < absint( $num ) ) {
						$nums = str_split( $num, 1 );
						foreach ( $nums as $_num ) { // This foreach loop will run for maximum 2 iterations.
							$string .= $alphabets[ $_num ];
						}
					} else {
						$string .= $alphabets[ $num ];
					}
				}
				return $string;
			}
			return '';
		}

		/**
		 * Get offset timestamp
		 *
		 * @param  int $timestamp The timestamp.
		 *
		 * @return int Return the timestamp offset.
		 */
		public static function get_offset_timestamp( $timestamp = 0 ) {
			if ( empty( $timestamp ) ) {
				$timestamp = time();
			}

			$gmt_offset = self::get_gmt_offset();
			return $timestamp + ( $gmt_offset ? intval( $gmt_offset ) : 0 );
		}

		/**
		 * Get site's GMT offset.
		 *
		 * @return int Return the offset. It will always return the integer due to the design of timezone.
		 */
		public static function get_gmt_offset() {
			$offset = get_option( 'gmt_offset', 0 );
			return floatval( $offset ) * HOUR_IN_SECONDS;
		}

		/**
		 * Get plugins data
		 *
		 * @param string $plugin_file The plugin file to get the data.
		 * @see https://developer.wordpress.org/reference/functions/get_plugin_data/
		 *
		 * @return array
		 */
		public static function get_plugin_data( $plugin_file = AFWC_PLUGIN_FILE ) {

			if ( ! function_exists( 'get_plugin_data' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			return get_plugin_data( $plugin_file, true, false );
		}

		/**
		 * Function to get products data.
		 *
		 * @param array $args arguments.
		 * @return array $products products data
		 */
		public static function get_products_data( $args = array() ) {
			global $wpdb;

			$from         = ( ! empty( $args['from'] ) ) ? $args['from'] : '';
			$to           = ( ! empty( $args['to'] ) ) ? $args['to'] : '';
			$affiliate_id = ( ! empty( $args['affiliate_id'] ) ) ? $args['affiliate_id'] : 0;
			$start_limit  = ( ! empty( $args['start_limit'] ) ) ? $args['start_limit'] : 0;
			$batch_limit  = ( ! empty( $args['batch_limit'] ) ) ? $args['batch_limit'] : AFWC_MY_ACCOUNT_DEFAULT_BATCH_LIMIT;

			$afwc_excluded_products = afwc_get_storewide_excluded_products();

			$prefixed_statuses   = afwc_get_prefixed_order_statuses();
			$option_order_status = 'afwc_order_statuses_' . uniqid();
			update_option( $option_order_status, implode( ',', $prefixed_statuses ), 'no' );

			// TODO:: Need to check query for limits and get products properly.
			if ( ! empty( $from ) && ! empty( $to ) ) {
				$products_result = $wpdb->get_results( // phpcs:ignore
														$wpdb->prepare( // phpcs:ignore
															"SELECT CONCAT(fpid,'_',fvid) as p_vid,
															fpid as pid,
															fvid as vid,
															IFNULL(SUM(fqty), 0) as tot_qty,
															IFNULL(SUM(ftot), 0) as tot_sales
														FROM
														(SELECT 
														CASE WHEN @order_item_id != order_item_id THEN @pid := -1 END,
														CASE WHEN @order_item_id != order_item_id THEN @vid := -1 END,
														CASE WHEN @order_item_id != order_item_id THEN @qty := -1 END,
														CASE WHEN @order_item_id != order_item_id THEN @tot := -1 END,
														@order_item_id := order_item_id as foid,
														@pid := CASE WHEN pid > -1 THEN pid ELSE @pid END as fpid,
														@vid := CASE WHEN vid > -1 THEN vid ELSE @vid END as fvid,
														@qty := CASE WHEN qty > -1 THEN qty ELSE @qty END as fqty,
														@tot := CASE WHEN tot > -1 THEN tot ELSE @tot END as ftot
														FROM(
																SELECT woim.order_item_id as order_item_id,
																IFNULL(CASE WHEN woim.meta_key = '_product_id' THEN woim.meta_value END, -1) as pid,
																IFNULL(CASE WHEN woim.meta_key = '_variation_id' THEN woim.meta_value END, -1) as vid,
																IFNULL(CASE WHEN woim.meta_key = '_line_total' THEN woim.meta_value END, -1) as tot,
																IFNULL(CASE WHEN woim.meta_key = '_qty' THEN woim.meta_value END, -1) as qty
																FROM {$wpdb->prefix}woocommerce_order_items AS woi
																	JOIN {$wpdb->prefix}afwc_referrals AS afwcr
																		ON (woi.order_id = afwcr.post_id
																			AND woi.order_item_type = 'line_item'
																			AND afwcr.affiliate_id = %d AND afwcr.status != %s)
																	JOIN {$wpdb->prefix}woocommerce_order_itemmeta as woim
																		ON(woim.order_item_id = woi.order_item_id
																			AND woim.meta_key IN ('_product_id', '_variation_id', '_line_total', '_qty'))
																WHERE (afwcr.datetime BETWEEN %s AND %s ) AND FIND_IN_SET( CONVERT(afwcr.order_status USING %s) COLLATE %s, (SELECT CONVERT(option_value USING %s) COLLATE %s FROM {$wpdb->prefix}options WHERE option_name = %s ))
														) as temp,
														(SELECT @order_item_id := 0,
																@pid := 0,
																@vid := 0,
																@qty := 0,
																@tot := 0
															) as temp_variable
														) as t1
														WHERE fpid > -1 
															AND fvid > -1
															AND fqty > -1
															AND ftot > -1
														GROUP BY p_vid
														ORDER BY tot_sales DESC, tot_qty DESC
														LIMIT %d, %d",
															$affiliate_id,
															AFWC_REFERRAL_STATUS_DRAFT,
															$from,
															$to,
															AFWC_SQL_CHARSET,
															AFWC_SQL_COLLATION,
															AFWC_SQL_CHARSET,
															AFWC_SQL_COLLATION,
															$option_order_status,
															$start_limit,
															$batch_limit
														),
					'ARRAY_A'
				);
			} else {
				$products_result = $wpdb->get_results( // phpcs:ignore
					$wpdb->prepare( // phpcs:ignore
						"SELECT CONCAT(fpid,'_',fvid) as p_vid,
						fpid as pid,
						fvid as vid,
						IFNULL(SUM(fqty), 0) as tot_qty,
						IFNULL(SUM(ftot), 0) as tot_sales
					FROM
					(SELECT 
					CASE WHEN @order_item_id != order_item_id THEN @pid := -1 END,
					CASE WHEN @order_item_id != order_item_id THEN @vid := -1 END,
					CASE WHEN @order_item_id != order_item_id THEN @qty := -1 END,
					CASE WHEN @order_item_id != order_item_id THEN @tot := -1 END,
					@order_item_id := order_item_id as foid,
					@pid := CASE WHEN pid > -1 THEN pid ELSE @pid END as fpid,
					@vid := CASE WHEN vid > -1 THEN vid ELSE @vid END as fvid,
					@qty := CASE WHEN qty > -1 THEN qty ELSE @qty END as fqty,
					@tot := CASE WHEN tot > -1 THEN tot ELSE @tot END as ftot
					FROM(
							SELECT woim.order_item_id as order_item_id,
							IFNULL(CASE WHEN woim.meta_key = '_product_id' THEN woim.meta_value END, -1) as pid,
							IFNULL(CASE WHEN woim.meta_key = '_variation_id' THEN woim.meta_value END, -1) as vid,
							IFNULL(CASE WHEN woim.meta_key = '_line_total' THEN woim.meta_value END, -1) as tot,
							IFNULL(CASE WHEN woim.meta_key = '_qty' THEN woim.meta_value END, -1) as qty
							FROM {$wpdb->prefix}woocommerce_order_items AS woi
								JOIN {$wpdb->prefix}afwc_referrals AS afwcr
									ON (woi.order_id = afwcr.post_id
										AND woi.order_item_type = 'line_item'
										AND afwcr.affiliate_id = %d AND afwcr.status != %s)
								JOIN {$wpdb->prefix}woocommerce_order_itemmeta as woim
									ON(woim.order_item_id = woi.order_item_id
										AND woim.meta_key IN ('_product_id', '_variation_id', '_line_total', '_qty'))
							WHERE FIND_IN_SET( CONVERT(afwcr.order_status USING %s) COLLATE %s, (SELECT CONVERT(option_value USING %s) COLLATE %s FROM {$wpdb->prefix}options WHERE option_name = %s ))
					) as temp,
					(SELECT @order_item_id := 0,
							@pid := 0,
							@vid := 0,
							@qty := 0,
							@tot := 0
						) as temp_variable
					) as t1
					WHERE fpid > -1 
						AND fvid > -1
						AND fqty > -1
						AND ftot > -1
					GROUP BY p_vid
					ORDER BY tot_sales DESC, tot_qty DESC
					LIMIT %d, %d",
						$affiliate_id,
						AFWC_REFERRAL_STATUS_DRAFT,
						AFWC_SQL_CHARSET,
						AFWC_SQL_COLLATION,
						AFWC_SQL_CHARSET,
						AFWC_SQL_COLLATION,
						$option_order_status,
						$start_limit,
						$batch_limit
					),
					'ARRAY_A'
				);
			}

			$products    = array();
			$product_ids = array();
			if ( ! empty( $products_result ) ) {
				// get the product id name map.
				$product_ids = array_map(
					function ( $res ) {
							$product_id = ! empty( $res['vid'] ) ? $res['vid'] : $res['pid'];
							return $product_id;
					},
					$products_result
				);

				$option_prod_ids = 'afwc_prod_ids_' . uniqid();
				update_option( $option_prod_ids, implode( ',', $product_ids ), 'no' );
				$prod_res = $wpdb->get_results(// phpcs:ignore
								$wpdb->prepare( // phpcs:ignore
									"SELECT ID, post_title
										FROM {$wpdb->prefix}posts
										WHERE FIND_IN_SET( ID, ( SELECT option_value 
																	FROM {$wpdb->prefix}options
																	WHERE option_name = %s ) )",
									$option_prod_ids
								),
					'ARRAY_A'
				);
				foreach ( $prod_res as $res ) {
					$prod_id_name_map[ $res['ID'] ] = $res['post_title'];
				}
				$products = array();
				foreach ( $products_result as $result ) {
					if ( in_array( $result['pid'], $afwc_excluded_products, true ) || in_array( $result['vid'], $afwc_excluded_products, true ) ) {
						continue;
					}
					// The product name will be blank if the product ID or variation ID is unavailable or if the product is deleted.
					$product_name                            = ( ! empty( $prod_id_name_map[ $result['vid'] ] ) )
						? $prod_id_name_map[ $result['vid'] ]
						: ( ( ! empty( $prod_id_name_map[ $result['pid'] ] ) ) ? $prod_id_name_map[ $result['pid'] ] : '' );
					$products[ $result['p_vid'] ]['product'] = $product_name;
					$products[ $result['p_vid'] ]['qty']     = $result['tot_qty'];
					$products[ $result['p_vid'] ]['sales']   = $result['tot_sales'];
				}

				delete_option( $option_prod_ids );
			}

			delete_option( $option_order_status );

			return apply_filters( 'afwc_products_result', $products, $args );
		}

		/**
		 * Function to get affiliates payout history
		 *
		 * @param array $args arguments.
		 * @return array affiliates payout history
		 */
		public static function get_affiliates_payout_history( $args = array() ) {
			global $wpdb;

			$affiliate_ids = ! empty( $args['affiliate_ids'] ) ? $args['affiliate_ids'] : '';
			$affiliate_ids = ! empty( $args['affiliate_id'] ) ? array( $args['affiliate_id'] ) : $args['affiliate_ids'];
			$from          = ! empty( $args['from'] ) ? $args['from'] : '';
			$to            = ! empty( $args['to'] ) ? $args['to'] : '';
			$start_limit   = ! empty( $args['start_limit'] ) ? $args['start_limit'] : 0;
			$batch_limit   = ! empty( $args['batch_limit'] ) ? $args['batch_limit'] : 5;

			$affiliates_payout_history = array();

			if ( ! empty( $affiliate_ids ) ) {
				if ( 1 === count( $affiliate_ids ) ) {

					if ( ! empty( $from ) && ! empty( $to ) ) {
						$affiliates_payout_history_results = $wpdb->get_results( // phpcs:ignore
																				$wpdb->prepare( // phpcs:ignore
																					"SELECT payouts.payout_id,
                                                                                                            DATE_FORMAT( CONVERT_TZ( payouts.datetime, '+00:00', %s ), %s ) as datetime,
																											payouts.amount AS amount,
																											payouts.currency AS currency,
																											payouts.payment_gateway AS method,
																											payouts.payout_notes
																								FROM {$wpdb->prefix}afwc_payouts AS payouts
																								WHERE payouts.affiliate_id = %d
																									AND payouts.datetime BETWEEN %s AND %s 
																								ORDER BY payouts.datetime DESC
																								LIMIT %d,%d",
																					AFWC_TIMEZONE_STR,
																					'%d-%b-%Y',
																					current( $affiliate_ids ),
																					$from,
																					$to,
																					$start_limit,
																					$batch_limit
																				),
							'ARRAY_A'
						);

					} else {
						$affiliates_payout_history_results = $wpdb->get_results( // phpcs:ignore
																				$wpdb->prepare( // phpcs:ignore
																					"SELECT payouts.payout_id,
                                                                                                            DATE_FORMAT( CONVERT_TZ( payouts.datetime, '+00:00', %s ), %s ) as datetime,
																											payouts.amount AS amount,
																											payouts.currency AS currency,
																											payouts.payment_gateway AS method,
																											payouts.payout_notes
																								FROM {$wpdb->prefix}afwc_payouts AS payouts
																								WHERE payouts.affiliate_id = %d
																								ORDER BY payouts.datetime DESC
																								LIMIT %d,%d",
																					AFWC_TIMEZONE_STR,
																					'%d-%b-%Y',
																					current( $affiliate_ids ),
																					$start_limit,
																					$batch_limit
																				),
							'ARRAY_A'
						);
					}
				} else {

					$option_nm = 'afwc_payout_history_affiliate_ids_' . uniqid();
					update_option( $option_nm, implode( ',', $affiliate_ids ), 'no' );

					if ( ! empty( $from ) && ! empty( $to ) ) {

						$affiliates_payout_history_results = $wpdb->get_results( // phpcs:ignore
																				$wpdb->prepare( // phpcs:ignore
																					"SELECT payouts.payout_id,
                                                                                                            DATE_FORMAT( CONVERT_TZ( payouts.datetime, '+00:00', %s ), %s ) as datetime,
																											payouts.amount AS amount,
																											payouts.currency AS currency,
																											payouts.payment_gateway AS method,
																											payouts.payout_notes
																								FROM {$wpdb->prefix}afwc_payouts AS payouts
																								WHERE FIND_IN_SET ( payouts.affiliate_id, ( SELECT option_value
																												FROM {$wpdb->prefix}options
																												WHERE option_name = %s ) )
																									AND payouts.datetime BETWEEN %s AND %s 
																								ORDER BY payouts.datetime DESC
																								LIMIT %d,%d",
																					AFWC_TIMEZONE_STR,
																					'%d-%b-%Y',
																					$option_nm,
																					$from,
																					$to,
																					$start_limit,
																					$batch_limit
																				),
							'ARRAY_A'
						);
					} else {
						$affiliates_payout_history_results = $wpdb->get_results( // phpcs:ignore
																				$wpdb->prepare( // phpcs:ignore
																					"SELECT payouts.payout_id,
	                                                                                                        DATE_FORMAT( CONVERT_TZ( payouts.datetime, '+00:00', %s ), %s ) as datetime,
																											payouts.amount AS amount,
																											payouts.currency AS currency,
																											payouts.payment_gateway AS method,
																											payouts.payout_notes
																								FROM {$wpdb->prefix}afwc_payouts AS payouts
																								WHERE FIND_IN_SET ( payouts.affiliate_id, ( SELECT option_value
																												FROM {$wpdb->prefix}options
																												WHERE option_name = %s ) )
																								ORDER BY payouts.datetime DESC,
																								LIMIT %d,%d",
																					AFWC_TIMEZONE_STR,
																					'%d-%b-%Y',
																					$option_nm,
																					$start_limit,
																					$batch_limit
																				),
							'ARRAY_A'
						);
					}

					delete_option( $option_nm );
				}
			} elseif ( ! empty( $from ) && ! empty( $to ) ) {
						$affiliates_payout_history_results = $wpdb->get_results( // phpcs:ignore
																				$wpdb->prepare( // phpcs:ignore
																					"SELECT payouts.payout_id,
                                                                                                            DATE_FORMAT( CONVERT_TZ( payouts.datetime, '+00:00', %s ), %s ) as datetime,
																											payouts.amount AS amount,
																											payouts.currency AS currency,
																											payouts.payment_gateway AS method,
																											payouts.payout_notes
																								FROM {$wpdb->prefix}afwc_payouts AS payouts
																								WHERE payouts.affiliate_id != %d
																									AND payouts.datetime BETWEEN %s AND %s 
																								ORDER BY payouts.datetime DESC,
																								LIMIT %d,%d",
																					AFWC_TIMEZONE_STR,
																					'%d-%b-%Y',
																					0,
																					$from,
																					$to,
																					$start_limit,
																					$batch_limit
																				),
							'ARRAY_A'
						);
			} else {
					$affiliates_payout_history_results = $wpdb->get_results( // phpcs:ignore
																			$wpdb->prepare( // phpcs:ignore
																				"SELECT payouts.payout_id,
                                                                                                            DATE_FORMAT( CONVERT_TZ( payouts.datetime, '+00:00', %s ), %s ) as datetime,
																											payouts.amount AS amount,
																											payouts.currency AS currency,
																											payouts.payment_gateway AS method,
																											payouts.payout_notes
																								FROM {$wpdb->prefix}afwc_payouts AS payouts
																								WHERE payouts.affiliate_id != %d
																								ORDER BY payouts.datetime DESC
																								LIMIT %d,%d",
																				AFWC_TIMEZONE_STR,
																				'%d-%b-%Y',
																				0,
																				$start_limit,
																				$batch_limit
																			),
						'ARRAY_A'
					);
			}

			$payout_ids           = array();
			$payout_order_details = array();
			if ( ! empty( $affiliates_payout_history_results ) ) {

				foreach ( $affiliates_payout_history_results as $result ) {
					$affiliates_payout_history[] = $result;
					$payout_ids[]                = $result['payout_id'];
				}

				if ( ! empty( $payout_ids ) ) {
					if ( is_callable( 'afwc_is_hpos_enabled' ) && afwc_is_hpos_enabled() ) {
						$results = $wpdb->get_results( // phpcs:ignore 
													$wpdb->prepare( // phpcs:ignore
														"SELECT po.payout_id,
																			IFNULL( COUNT( po.post_id ), 0 ) AS order_count,
																			DATE_FORMAT( CONVERT_TZ( MIN( wco.date_created_gmt ), '+00:00', %s ), %s ) AS from_date,
																			DATE_FORMAT( CONVERT_TZ( MAX( wco.date_created_gmt ), '+00:00', %s ), %s ) AS to_date
																	FROM {$wpdb->prefix}afwc_payout_orders AS po
																		JOIN {$wpdb->prefix}wc_orders AS wco
																			ON(wco.id = po.post_id
																				AND wco.type = 'shop_order')
																	WHERE po.payout_id IN (" . implode( ',', array_fill( 0, count( $payout_ids ), '%d' ) ) . ') 
																	GROUP BY po.payout_id',
														array_merge( array( AFWC_TIMEZONE_STR, '%d-%b-%Y', AFWC_TIMEZONE_STR, '%d-%b-%Y' ), $payout_ids )
													),
							'ARRAY_A'
						);
					} else {
						$results = $wpdb->get_results( // phpcs:ignore 
													$wpdb->prepare( // phpcs:ignore
														"SELECT po.payout_id,
																			IFNULL( COUNT( po.post_id ), 0 ) AS order_count,
																			DATE_FORMAT( MIN( p.post_date ), '%%d-%%b-%%Y' ) AS from_date,
																			DATE_FORMAT( MAX( p.post_date ), '%%d-%%b-%%Y' ) AS to_date
																	FROM {$wpdb->prefix}afwc_payout_orders AS po
																		JOIN {$wpdb->prefix}posts AS p
																			ON(p.ID = po.post_id
																				AND p.post_type = 'shop_order')
																	WHERE po.payout_id IN (" . implode( ',', array_fill( 0, count( $payout_ids ), '%d' ) ) . ')
																	GROUP BY po.payout_id',
														$payout_ids
													),
							'ARRAY_A'
						);
					}

					if ( ! empty( $results ) ) {
						foreach ( $results as $detail ) {
							$payout_order_details[ $detail['payout_id'] ] = array(
								'referral_count' => $detail['order_count'],
								'from_date'      => $detail['from_date'],
								'to_date'        => $detail['to_date'],
							);
						}
					}

					foreach ( $affiliates_payout_history as $key => $payout ) {
						$affiliates_payout_history[ $key ] = ( ! empty( $payout_order_details[ $payout['payout_id'] ] ) && is_array( $payout_order_details[ $payout['payout_id'] ] ) ) ? array_merge( $affiliates_payout_history[ $key ], $payout_order_details[ $payout['payout_id'] ] ) : $affiliates_payout_history_results[ $key ];
					}
				}
			}

			// Let 3rd party developers to add additional details in payout history.
			return apply_filters( 'afwc_payout_history', $affiliates_payout_history, $payout_order_details );
		}

		/**
		 * Get affiliate users.
		 *
		 * @param array $params Arguments of WP_User_Query.
		 * @return array
		 */
		public function get_affiliates( $params = array() ) {

			$args = array_merge(
				$params,
				array(
					'meta_key'   => 'afwc_is_affiliate', // phpcs:ignore
					'meta_value' => 'yes', // phpcs:ignore
				)
			);

			$affiliate_users = get_users( $args );
			// Get assigned affiliate roles.
			$affiliate_user_roles = get_option( 'affiliate_users_roles', '' );

			if ( ! empty( $affiliate_user_roles ) ) {
				$args = array_merge(
					$params,
					array(
						'role__in' => $affiliate_user_roles,
					)
				);
				// Get users by assigned affiliate user roles.
				$affiliate_role_users = get_users( $args );
				if ( ! empty( $affiliate_role_users ) ) {
					// Merge users of affiliate and users in affiliate user role.
					$affiliate_users = array_merge( $affiliate_users, $affiliate_role_users );
				}
			}

			$users = array();
			if ( ! empty( $affiliate_users ) ) {
				foreach ( $affiliate_users as $user ) {
					$user_data = ! empty( $user->data ) ? $user->data : null;
					if ( ! empty( $user_data ) && isset( $user_data->ID ) && isset( $user_data->user_email ) ) {
						$users[ $user_data->ID ] = sprintf(
							'%1$s (#%2$d &ndash; %3$s)',
							isset( $user_data->display_name ) ? $user_data->display_name : '',
							absint( $user_data->ID ),
							$user_data->user_email
						);
					}
				}
			}

			return apply_filters( 'afwc_get_affiliates', $users );
		}

		/**
		 * Function to get template base directory for Affiliate For WooCommerce' templates
		 *
		 * @param  string $template_name Template name.
		 * @return string $template_base_dir Base directory for Affiliate For WooCommerce' templates.
		 */
		public function get_template_base_dir( $template_name = '' ) {

			$template_base_dir = '';
			$plugin_base_dir   = substr( plugin_basename( AFWC_PLUGIN_FILE ), 0, strpos( plugin_basename( AFWC_PLUGIN_FILE ), '/' ) + 1 );
			$afwc_base_dir     = 'woocommerce/' . $plugin_base_dir;

			// First find the template in the active theme's or parent theme's woocommerce/affiliate-for-woocommerce folder.
			$template = locate_template(
				array(
					$afwc_base_dir . $template_name,
				)
			);

			if ( ! empty( $template ) ) {
				$template_base_dir = $afwc_base_dir;
			} else {
				// If not found then the template in the active theme's or parent theme's affiliate-for-woocommerce folder.
				$template = locate_template(
					array(
						$plugin_base_dir . $template_name,
					)
				);

				if ( ! empty( $template ) ) {
					$template_base_dir = $plugin_base_dir;
				}
			}

			$template_base_dir = apply_filters( 'afwc_template_base_dir', $template_base_dir, $template_name );

			return $template_base_dir;
		}

		/**
		 * Set payout method if not exist.
		 * To set method on plugin upgrade/activation for commission payouts
		 *
		 * @return void.
		 */
		public function set_payout_method() {
			if ( 'no' === get_option( 'afwc_is_set_commission_payout_method', 'no' ) ) {
				$afwc_paypal = is_callable( array( 'AFWC_PayPal_API', 'get_instance' ) ) ? AFWC_PayPal_API::get_instance() : null;

				if ( ! empty( $afwc_paypal ) && is_callable( array( $afwc_paypal, 'get_payout_method' ) ) ) {
					$afwc_paypal->get_payout_method( true );
				}

				update_option( 'afwc_is_set_commission_payout_method', 'yes', 'no' );
			} elseif ( empty( get_option( 'afwc_commission_payout_method' ) ) ) {
				$afwc_paypal = is_callable( array( 'AFWC_PayPal_API', 'get_instance' ) ) ? AFWC_PayPal_API::get_instance() : null;

				if ( ! empty( $afwc_paypal ) && is_callable( array( $afwc_paypal, 'check_for_paypal_payout' ) ) ) {
					$afwc_paypal->check_for_paypal_payout();
				}
			}
		}

		/**
		 * Method to render the affiliate search.
		 *
		 * @param string $id The ID of the field.
		 * @param array  $args The arguments.
		 *
		 * @return void
		 */
		public function render_affiliate_search( $id = '', $args = array() ) {

			if ( empty( $id ) ) {
				return;
			}

			$affiliate_id = ! empty( $args['affiliate_id'] ) ? intval( $args['affiliate_id'] ) : 0;
			$class        = 'afwc-affiliate-search';

			$plugin_data = self::get_plugin_data();
			wp_register_script( 'affiliate-user-search', AFWC_PLUGIN_URL . '/assets/js/affiliate-search.js', array( 'jquery', 'wp-i18n', 'select2', 'wc-enhanced-select' ), $plugin_data['Version'], true );
			wp_enqueue_script( 'affiliate-user-search' );

			wp_localize_script(
				'affiliate-user-search',
				'affiliateParams',
				array(
					'ajaxurl'  => admin_url( 'admin-ajax.php' ),
					'security' => wp_create_nonce( 'afwc-search-affiliate-users' ),
				)
			);

			$user_string = '';

			if ( ! empty( $affiliate_id ) ) {
				$user_id = afwc_get_user_id_based_on_affiliate_id( $affiliate_id );
				if ( ! empty( $user_id ) ) {
					$user = get_user_by( 'id', $user_id );
					if ( is_object( $user ) && $user instanceof WP_User ) {
						$user_string = sprintf(
							/* translators: 1: user display name 2: user ID 3: user email */
							esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'affiliate-for-woocommerce' ),
							! empty( $user->display_name ) ? $user->display_name : '',
							absint( $user_id ),
							! empty( $user->user_email ) ? $user->user_email : ''
						);
					}
				}
			}

			?>
			<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" style="width: 100%;" class="<?php echo esc_attr( $class ); ?>" data-placeholder="<?php echo esc_attr_x( 'Search by email, username or name', 'affiliate search placeholder', 'affiliate-for-woocommerce' ); ?>" data-allow-clear="true" data-action="afwc_json_search_affiliates">
				<?php
				if ( ! empty( $affiliate_id ) ) {
					?>
					<option value="<?php echo esc_attr( $affiliate_id ); ?>" selected="selected"><?php echo esc_html( wp_kses_post( $user_string ) ); ?><option>
					<?php
				}
				?>
			</select>

			<?php
		}

		/**
		 * Method to provide the values to select2 affiliate search.
		 *
		 * @return void
		 */
		public function afwc_json_search_affiliates() {

			check_ajax_referer( 'afwc-search-affiliate-users', 'security' );

			$term = ( ! empty( $_GET['term'] ) ) ? (string) urldecode( stripslashes( wp_strip_all_tags( $_GET ['term'] ) ) ) : ''; // phpcs:ignore
			if ( empty( $term ) ) {
				wp_die();
			}

			$users = $this->get_affiliates(
				array(
					'search'         => '*' . $term . '*',
					'search_columns' => array( 'ID', 'user_nicename', 'user_login', 'user_email', 'display_name' ),
				)
			);

			echo wp_json_encode( ! empty( $users ) ? $users : array() );
			wp_die();
		}

		/**
		 * Method to display the product referral the button.
		 *
		 * @return void.
		 */
		public function add_product_referral_button() {
			global $product;

			if ( ! ( $product instanceof WC_Product ) ) {
				return;
			}

			$product_id = is_callable( array( $product, 'get_id' ) ) ? absint( $product->get_id() ) : 0;
			if ( empty( $product_id ) ) {
				return;
			}

			$product_type = is_callable( array( $product, 'get_type' ) ) ? $product->get_type() : '';

			$style = '';
			$class = 'single-product-affiliate-link';

			if ( 'variable' === $product_type || 'variable-subscription' === $product_type ) {
				$style  = 'display:none;';
				$class .= ' disabled';
			}

			$theme = wp_get_theme();
			if ( $theme instanceof WP_Theme && is_callable( array( $theme, 'get_template' ) ) && 'astra' === $theme->get_template() ) {
				$style .= 'padding: 10px 20px;margin: 0 10px;';
			}

			$this->print_product_referral_button(
				$product_id,
				array(
					'class' => $class,
					'style' => $style,
				)
			);
		}

		/**
		 * Method to display the button with referral URL.
		 *
		 * @param int   $product_id The product Id.
		 * @param array $args The arguments.
		 * @return void.
		 */
		public function print_product_referral_button( $product_id = 0, $args = array() ) {
			$product_id = absint( $product_id );
			if ( empty( $product_id ) ) {
				return;
			}

			$link = afwc_get_product_affiliate_url( $product_id, get_current_user_id() );
			if ( empty( $link ) ) {
				return;
			}

			if ( ! wp_script_is( 'afwc-affiliate-link' ) ) {
				wp_enqueue_script( 'afwc-affiliate-link' );
			}

			if ( ! wp_script_is( 'afwc-click-to-copy' ) ) {
				wp_enqueue_script( 'afwc-click-to-copy' );
			}

			$class = 'woocommerce-button button afwc-click-to-copy ' . ( ! empty( $args['class'] ) ? $args['class'] : '' );
			$style = ! empty( $args['style'] ) ? $args['style'] : '';
			$label = apply_filters(
				'afwc_product_referral_link_label',
				_x( 'Click to copy referral link', "text to copy the affiliate's product-specific referral link", 'affiliate-for-woocommerce' ),
				array(
					'product_id' => $product_id,
					'source'     => $this,
				)
			);

			echo sprintf(
				'<a href="%1$s" data-ctp="%1$s" data-product-referral-link="%1$s" class="%2$s" style="%3$s">%4$s</a>',
				esc_url( $link ),
				esc_attr( $class ),
				esc_attr( $style ),
				esc_attr( $label )
			);
		}

		/**
		 * Ajax callback method to get the referral url.
		 *
		 * @return void.
		 */
		public function get_product_affiliate_link() {
			check_ajax_referer( 'afwc-product-affiliate-link', 'security' );

			if ( empty( $_POST['product_id'] ) ) {
				wp_send_json_error();
			}

			wp_send_json_success(
				array(
					'url' => afwc_get_product_affiliate_url( absint( $_POST['product_id'] ), get_current_user_id() ),
				)
			);
		}

		/**
		 * Register the global scripts.
		 *
		 * @return void.
		 */
		public function register_global_scripts() {
			$plugin_data = self::get_plugin_data();
			wp_register_script( 'afwc-click-to-copy', AFWC_PLUGIN_URL . '/assets/js/afwc-click-to-copy.js', array(), $plugin_data['Version'], true );
			wp_register_script( 'afwc-affiliate-link', AFWC_PLUGIN_URL . '/assets/js/afwc-affiliate-link.js', array( 'jquery', 'wp-i18n' ), $plugin_data['Version'], true );
			wp_localize_script(
				'afwc-affiliate-link',
				'afwcAffiliateLinkParams',
				array(
					'product' => array(
						'ajaxURL'  => WC_AJAX::get_endpoint( 'afwc_get_product_affiliate_link' ),
						'security' => wp_create_nonce( 'afwc-product-affiliate-link' ),
					),
				)
			);
			if ( ! wp_script_is( 'afwc-date-functions', 'registered' ) ) {
				wp_register_script( 'afwc-date-functions', AFWC_PLUGIN_URL . '/assets/js/afwc-date-functions.js', array(), $plugin_data['Version'], true );
			}
		}

		/**
		 * Method to log errors during any process within a function or method.
		 *
		 * @param string $callable_name  The name of the function or method.
		 * @param string $error          The error message.
		 *
		 * @return void.
		 */
		public static function log_error( $callable_name = '', $error = '' ) {
			self::log(
				'error',
				sprintf(
					/* translators: 1: Callable name 2: Error message */
					_x(
						'Error in %1$s. Details: %2$s',
						'Error message details',
						'affiliate-for-woocommerce'
					),
					! empty( $callable_name ) ? $callable_name : '',
					! empty( $error ) ? $error : ''
				)
			);
		}

		/**
		 * Get the minimum date time from the trackers.
		 * Currently, it considers only visitors and referrals.
		 *
		 * @param string $format The format to return the datetime.
		 * @param bool   $gmt Whether to return the GMT based datetime.
		 * @param int    $affiliate_id The affiliate ID if get the datetime based on affiliate ID.
		 *
		 * @return string Return the datetime.
		 */
		public function get_minimum_tracking_datetime( $format = 'Y-m-d H:i:s', $gmt = false, $affiliate_id = 0 ) {
			global $wpdb;

			try {
				if ( ! empty( $affiliate_id ) && is_scalar( $affiliate_id ) ) {
					$datetime = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
						$wpdb->prepare(
							"SELECT MIN(trackers.min_datetime)
								FROM (
									SELECT MIN(datetime) AS min_datetime
										FROM {$wpdb->prefix}afwc_hits
									WHERE affiliate_id = %d
									UNION ALL
									SELECT MIN(datetime) AS min_datetime
										FROM {$wpdb->prefix}afwc_referrals
									WHERE affiliate_id = %d
								) AS trackers",
							intval( $affiliate_id ),
							intval( $affiliate_id )
						)
					);
				} else {
					$datetime = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
						"SELECT MIN(trackers.min_datetime)
							FROM (
								SELECT MIN(datetime) AS min_datetime
									FROM {$wpdb->prefix}afwc_hits
								UNION ALL
								SELECT MIN(datetime) AS min_datetime
									FROM {$wpdb->prefix}afwc_referrals
							) AS trackers"
					);
				}
				if ( empty( $datetime ) || ! is_scalar( $datetime ) ) {
					return '';
				}
				$result = empty( $gmt ) ? get_date_from_gmt( $datetime, 'Y-m-d H:i:s' ) : gmdate( $format, strtotime( $datetime ) );
			} catch ( Exception $e ) {
				self::log_error( __METHOD__, ( is_callable( array( $e, 'getMessage' ) ) ) ? $e->getMessage() : '' );
				$result = '';
			}

			return ! empty( $result ) ? $result : '';
		}

		/**
		 * Method to save referral URL identifier.
		 *
		 * @param int    $user_id The affiliate ID.
		 * @param string $identifier The new identifier.
		 *
		 * @throws Exception If any error during the process.
		 * @return bool Return true if updated otherwise false.
		 */
		public function save_ref_url_identifier( $user_id = 0, $identifier = '' ) {

			if ( empty( $user_id ) ) {
				throw new Exception(
					_x(
						'Affiliate ID missing for updating affiliate URL identifier.',
						'referral url identifier updating error message',
						'affiliate-for-woocommerce'
					)
				);
			}

			if ( empty( $identifier ) ) {
				throw new Exception(
					_x(
						'The affiliate URL identifier can not be empty.',
						'referral url identifier updating error message',
						'affiliate-for-woocommerce'
					)
				);
			}

			if ( is_numeric( $identifier ) ) {
				throw new Exception( _x( 'Numeric values are not allowed for affiliate URL identifier.', 'referral url identifier updating error message', 'affiliate-for-woocommerce' ) );
			}

			$identifier_regex_pattern = afwc_affiliate_identifier_regex_pattern();

			if ( ! empty( $identifier_regex_pattern ) && ! preg_match( '/' . $identifier_regex_pattern . '/', $identifier ) ) {
				throw new Exception(
					apply_filters(
						'afwc_affiliate_identifier_regex_pattern_error_message',
						_x(
							'Invalid affiliate URL identifier. It should be a combination of alphabets and numbers, but the number should not be in the first position.',
							'referral identifier pattern validation error message',
							'affiliate-for-woocommerce'
						)
					)
				);
			}

			$user_with_ref_url_id = afwc_get_affiliate_id_by_assigned_identifier( $identifier );

			if ( ! empty( $user_with_ref_url_id ) ) {
				// Throw error if the identifier is already exists.
				if ( intval( $user_id ) === intval( $user_with_ref_url_id ) ) {
					throw new Exception(
						_x(
							'Old and new affiliate URL identifier are same. Please choose a different identifier.',
							'referral url identifier updating error message',
							'affiliate-for-woocommerce'
						)
					);
				} else {
					throw new Exception(
						_x(
							'The URL identifier already exists. Please choose a different identifier.',
							'referral url identifier updating error message',
							'affiliate-for-woocommerce'
						)
					);
				}
			}

			return (bool) update_user_meta( $user_id, 'afwc_ref_url_id', $identifier );
		}

		/**
		 * Method to get the remaining refund days for the order.
		 *
		 * @param int $order_created_date The order created date in UNIX timestamp.
		 *
		 * @return int Return the remaining days.
		 */
		public function get_remaining_refund_days_for_order( $order_created_date = '' ) {
			if ( empty( $order_created_date ) || ! is_numeric( $order_created_date ) || $order_created_date < 0 ) {
				return 0;
			}
			$refund_period_in_seconds          = absint( get_option( 'afwc_order_refund_period_in_days', 30 ) ) * DAY_IN_SECONDS;
			$order_refund_time_diff_in_seconds = time() - absint( $order_created_date );
			if ( $order_refund_time_diff_in_seconds < $refund_period_in_seconds ) {
				return ceil( ( $refund_period_in_seconds - $order_refund_time_diff_in_seconds ) / DAY_IN_SECONDS ); // Used `ceil` here to round-up remaining refund days, E.g.: return 2 for 1.3 days.
			}
			return 0;
		}
	}
}
