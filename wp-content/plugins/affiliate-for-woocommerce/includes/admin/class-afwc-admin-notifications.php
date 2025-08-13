<?php
/**
 * Affiliate For WooCommerce Admin Notifications
 *
 * @package     affiliate-for-woocommerce/includes/admin/
 * @since       1.3.4
 * @version     1.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AFWC_Admin_Notifications' ) ) {

	/**
	 * Class for handling admin notifications of Affiliate For WooCommerce
	 */
	class AFWC_Admin_Notifications {

		/**
		 * Variable to hold instance of AFWC_Admin_Notifications
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * The notices option names
		 *
		 * @var array
		 */
		private $notices = array();

		/**
		 * Constructor
		 */
		private function __construct() {
			// Filter to add Settings link on Plugins page.
			add_filter( 'plugin_action_links_' . plugin_basename( AFWC_PLUGIN_FILE ), array( $this, 'plugin_action_links' ) );

			// To update footer text & style on AFW screens.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_footer_style' ) );
			add_filter( 'admin_footer_text', array( $this, 'afwc_footer_text' ), 99 );
			add_filter( 'update_footer', array( $this, 'afwc_update_footer_text' ), 99 );

			// Manage Admin notices.
			$this->notices = array(
				'afwc-commission-rule-update',
				'afwc_admin_summary_email_feature',
			);
			add_action( 'admin_notices', array( $this, 'rule_update_admin_notices' ) );
			add_action( 'admin_notices', array( $this, 'admin_summary_email_admin_notices' ) );
			add_action( 'wp_ajax_dismiss_admin_notice', array( $this, 'dismiss_admin_notice' ) );

			add_action( 'wp_ajax_update_feedback', array( $this, 'update_feedback' ) );
		}

		/**
		 * Get single instance of AFWC_Admin_Notifications
		 *
		 * @return AFWC_Admin_Notifications Singleton object of AFWC_Admin_Notifications
		 */
		public static function get_instance() {
			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Function to add more action on plugins page
		 *
		 * @param array $links Existing links.
		 * @return array $links
		 */
		public function plugin_action_links( $links ) {
			$settings_link = add_query_arg(
				array(
					'page' => 'wc-settings',
					'tab'  => 'affiliate-for-woocommerce-settings',
				),
				admin_url( 'admin.php' )
			);

			$getting_started_link = add_query_arg( array( 'page' => 'affiliate-for-woocommerce-documentation' ), admin_url( 'admin.php' ) );

			$action_links = array(
				'getting-started' => '<a href="' . esc_url( $getting_started_link ) . '">' . esc_html( __( 'Getting started', 'affiliate-for-woocommerce' ) ) . '</a>',
				'settings'        => '<a href="' . esc_url( $settings_link ) . '">' . esc_html( __( 'Settings', 'affiliate-for-woocommerce' ) ) . '</a>',
				'docs'            => '<a target="_blank" href="' . esc_url( AFWC_DOC_DOMAIN ) . '">' . __( 'Docs', 'affiliate-for-woocommerce' ) . '</a>',
				'support'         => '<a target="_blank" href="' . esc_url( AFW_CONTACT_SUPPORT_URL ) . '">' . __( 'Support', 'affiliate-for-woocommerce' ) . '</a>',
				// View all the reviews link.
				'review'          => '<a target="_blank" href="' . esc_url( 'https://woocommerce.com/products/affiliate-for-woocommerce/#reviews' ) . '">' . __( 'Reviews', 'affiliate-for-woocommerce' ) . '</a>',
			);

			return array_merge( $action_links, $links );
		}

		/**
		 * Method to check if the current admin page is related to the plugin to display the plugin footer.
		 *
		 * @return bool True if the page is related to the plugin, false otherwise.
		 */
		public function afwc_is_footer_page() {
			$get_page = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore
			$afw_pages = array( 'affiliate-for-woocommerce-documentation', 'affiliate-for-woocommerce' );

			$get_tab = ( ! empty( $_GET['tab'] ) ) ? wc_clean( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore
			$taxonomy_page = ( ! empty( $_GET['taxonomy'] ) ) ? wc_clean( wp_unslash( $_GET['taxonomy'] ) ) : ''; // phpcs:ignore

			return boolval( in_array( $get_page, $afw_pages, true ) || 'affiliate-for-woocommerce-settings' === $get_tab || 'afwc_user_tags' === $taxonomy_page );
		}

		/**
		 * Method to enqueue footer css file
		 */
		public function enqueue_admin_footer_style() {
			if ( ! $this->afwc_is_footer_page() ) {
				return;
			}
			$plugin_data = Affiliate_For_WooCommerce::get_plugin_data();
			wp_enqueue_style( 'afwc-footer-css', AFWC_PLUGIN_URL . '/assets/css/admin/afwc-admin-footer.css', array(), $plugin_data['Version'] );
		}

		/**
		 * Method to display plugin name and version in footer.
		 *
		 * @param  string $wp_thankyou_text Text in footer (left).
		 * @return string Update the default text to plugin text if the page is related to plugin.
		 */
		public function afwc_footer_text( $wp_thankyou_text ) {

			global $pagenow;

			if ( empty( $pagenow ) ) {
				return $wp_thankyou_text;
			}

			if ( ! $this->afwc_is_footer_page() ) {
				return $wp_thankyou_text;
			}

			$plugin_data = Affiliate_For_WooCommerce::get_plugin_data();

			ob_start();
			?>
			<span class="afwc-footer-left">
				<span class="afwc-footer-left-top">
					<?php
					printf(
						/* translators: 1: heart symbol 2: plugin author */
						esc_html_x( 'Affiliate For WooCommerce, made with %1$s by %2$s', 'plugin and brand text in footer', 'affiliate-for-woocommerce' ),
						'<span class="afwc-heart">&hearts;</span>',
						'<span class="afwc-brand">' . esc_html_x( 'StoreApps', 'brand name in footer', 'affiliate-for-woocommerce' ) . '</span>'
					);
					?>
				</span>
				<br />
				<span class="afwc-footer-left-bottom">
					<?php
					printf(
						/* translators: 1: pipe as text separator 2: plugin version */
						esc_html_x( 'Proudly built for WordPress & WooCommerce %1$s Version %2$s', 'text for showing plugin is for WordPress and WooCommerce in footer', 'affiliate-for-woocommerce' ),
						'<span class="afwc-separator">|</span>',
						esc_html( $plugin_data['Version'] )
					)
					?>
				</span>
			</span>
			<?php
			return ob_get_clean();
		}

		/**
		 * Method to display plugin review link and feature request link.
		 *
		 * @param  string $wp_version_text Text in footer (right).
		 * @return string Update the default text to plugin text if the page is related to plugin.
		 */
		public function afwc_update_footer_text( $wp_version_text ) {
			global $pagenow;

			if ( empty( $pagenow ) ) {
				return $wp_version_text;
			}

			if ( ! $this->afwc_is_footer_page() ) {
				return $wp_version_text;
			}

			ob_start();
			?>
			<span class="afwc-footer-right">
				<span class="afwc-footer-right-top">
					<?php
					printf(
						/* translators: 1: open the anchor tag for plugin review link 2: five star symbol 3: close the anchor tag for plugin review link */
						esc_html_x( 'If you like it, please give us %1$s %2$s rating%3$s.', 'text for plugin review in footer', 'affiliate-for-woocommerce' ),
						'<a target="_blank" href="' . esc_attr( AFWC_REVIEW_URL ) . '">',
						'<span class="afwc-star">&starf;&starf;&starf;&starf;&starf;</span>',
						'</a>'
					);
					?>
				</span>
				<br />
				<span class="afwc-footer-right-bottom">
					<?php
					printf(
						/* translators: 1: open the anchor tag for plugin feature request link 2: close the anchor tag for plugin feature request link */
						esc_html_x( 'Do you have a feature request? Tell us %1$shere%2$s.', 'text for plugin feature request in footer', 'affiliate-for-woocommerce' ),
						'<a target="_blank" href="https://woocommerce.com/feature-requests/affiliate-for-woocommerce/">',
						'</a>'
					);
					?>
				</span>
			</span>
			<?php
			return ob_get_clean();
		}

		/**
		 * Method to register the admin notice of commission rule calculation improvements.
		 */
		public function rule_update_admin_notices() {
			$title         = _x( '📣 Big update', 'Notice title for affiliate summary report', 'affiliate-for-woocommerce' );
			$message       = array(
				_x(
					'We have improved commission calculations in certain cases. New referral orders may have updated commissions issued (the existing referrals remain unaffected).',
					'Notice text for affiliate commission rule calculation update',
					'affiliate-for-woocommerce'
				),
				_x(
					'Please review your commission plans and place a test order to verify everything is working as expected.',
					'Notice text for affiliate commission rule calculation update',
					'affiliate-for-woocommerce'
				),
			);
			$action_url    = admin_url( 'admin.php?page=affiliate-for-woocommerce#!/plans' );
			$action_button = sprintf(
				'<a href="%1$s" class="button button-primary">%2$s</a>',
				esc_url( $action_url ),
				_x( 'Review plans', 'Link to open plan dashboard', 'affiliate-for-woocommerce' )
			);

			$this->show_notice( 'afwc-commission-rule-update', 'info', $title, $message, $action_button, true );
		}

		/**
		 * Method to register the admin notice of announcement of admin & affiliate manager's monthly summary email feature.
		 */
		public function admin_summary_email_admin_notices() {
			$title   = _x( '📣 Big update', 'Notice title to announce admin summary report', 'affiliate-for-woocommerce' );
			$message = array(
				_x( 'A monthly affiliate program summary email will now be sent to the Affiliate Manager and Store Admin.', 'Notice text for explaining admin summary email feature', 'affiliate-for-woocommerce' ),
			);
			if ( function_exists( 'afwc_is_hpos_enabled' ) && afwc_is_hpos_enabled() ) {
				if ( ! class_exists( 'AFWC_Emails' ) || ! is_callable( array( 'AFWC_Emails', 'is_afwc_mailer_enabled' ) ) || true !== AFWC_Emails::is_afwc_mailer_enabled( 'afwc_email_admin_summary_reports' ) ) {
					return;
				}
				$message[]          = _x( 'This email is automatically enabled for you based on your store settings.', 'Notice text stating admin summary email is auto-enabled', 'affiliate-for-woocommerce' );
				$action_button_text = _x( 'Review email', 'Button text to view admin summary email settings', 'affiliate-for-woocommerce' );
			} else {
				$message[]          = _x( 'This email is disabled for you as your store settings do not meet the requirements.', 'Notice text for stating admin summary email is disabled', 'affiliate-for-woocommerce' );
				$action_button_text = _x( 'Review email requirements', 'Button text to check requirements for enabling admin summary email', 'affiliate-for-woocommerce' );
			}

			// Link of 'Affiliate Manager - Summary Email' setting.
			$action_url = add_query_arg(
				array(
					'page'    => 'wc-settings',
					'tab'     => 'email',
					'section' => 'afwc_email_admin_summary_reports',
				),
				admin_url( 'admin.php' )
			);

			$action_button = sprintf( '<a href="%1$s" class="button button-primary">%2$s</a>', esc_url( $action_url ), $action_button_text );

			$this->show_notice( 'afwc_admin_summary_email_feature', 'info', $title, $message, $action_button, true );
		}

		/**
		 * Method to render admin notice
		 *
		 * @param string       $id           Notice ID.
		 * @param string       $type         Notice type.
		 * @param string       $title        Notice title.
		 * @param string|array $message      Notice message(s).
		 * @param string       $action       Notice actions.
		 * @param bool         $dismissible  Notice dismissible.
		 * @return void.
		 */
		public function show_notice( $id = '', $type = 'info', $title = '', $message = '', $action = '', $dismissible = false ) {
			if ( empty( $id ) || 'no' === get_option( $id . '_affiliate_wc', 'yes' ) ) {
				return;
			}
			$css_classes = array(
				'notice',
				'notice-' . $type,
			);
			?>
			<div class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>" id="<?php echo esc_attr( $id ); ?>">
				<div class="afwc_admin_notice_wrapper">
					<div>
						<?php
						if ( ! empty( $title ) ) {
							printf( '<p><strong>%s</strong></p>', esc_html( $title ) );
						}
						if ( ! empty( $message ) ) {
							if ( is_array( $message ) ) {
								foreach ( $message as $single_msg ) {
									printf( '<p>%s</p>', esc_html( $single_msg ) );
								}
							} else {
								printf( '<p>%s</p>', esc_html( $message ) );
							}
						}
						?>
						<p>
						<?php
						if ( ! empty( $action ) ) {
							printf( '<span style="padding-right: 1rem;" class="submit">%s</span>', wp_kses_post( $action ) );
						}
						?>
						</p>
					</div>
					<?php if ( ! empty( $dismissible ) ) { ?>
					<a class="afwc_dismiss_admin_notice" href="javascript:void(0)">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
						</svg>
					</a>
					<?php } ?>
				</div>
			</div>
			<style type="text/css">
				.afwc_admin_notice_wrapper {
					display: flex;
					justify-content: space-between;
				}
				a.afwc_dismiss_admin_notice {
					display: block;
					margin-top: 9px;
					color: #6b7280;
					width: 1.25rem;
					height: 1.25rem;
					border-radius: 50%;
				}
				a.afwc_dismiss_admin_notice:hover {
					color: #111827;
				}
				a.afwc_dismiss_admin_notice svg {
					width: 1.25rem;
					height: 1.25rem;
				}
			</style>
			<?php
			if ( ! empty( $dismissible ) ) {
				wc_enqueue_js(
					"jQuery(document).on('click', '#" . esc_attr( $id ) . " a.afwc_dismiss_admin_notice', function(e) {
						jQuery('#" . esc_attr( $id ) . "').css({ 'opacity': 0.75, 'pointer-events': 'none' });
						jQuery.ajax({
							type: 'POST',
							url: '" . admin_url( 'admin-ajax.php' ) . "',
							data: {
								action: 'dismiss_admin_notice',
								security: '" . esc_js( wp_create_nonce( 'afwc-dismiss-' . $id ) ) . "',
								notice_id: '" . esc_attr( $id ) . "'
							},
							complete: function() {
								jQuery('#" . esc_attr( $id ) . "').slideUp(); 
							}
						});
					});"
				);
			}
		}

		/**
		 * Method to handle AJAX action of admin notice dismiss
		 */
		public function dismiss_admin_notice() {
			if ( empty( $_POST['notice_id'] ) ) {
				wp_die();
			}
			$notice_id = wc_clean( wp_unslash( $_POST['notice_id'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			check_ajax_referer( 'afwc-dismiss-' . $notice_id, 'security' );

			if ( ! in_array( $notice_id, $this->notices, true ) ) {
				wp_die();
			}

			update_option( $notice_id . '_affiliate_wc', 'no', 'no' );
			wp_die();
		}

		/**
		 * Method to determine whether to show feedback notification to the admin.
		 *
		 * Logic:
		 * - First notification: Shows after 30 days from plugin activation
		 * - Subsequent notifications: Every 20 days after each close/dismiss
		 * - Stop showing once user leaves a review.
		 *
		 * Additional Conditions:
		 * - Only show if there are more than 3 active affiliates, OR more than 10 referrals.
		 *
		 * @return bool True if feedback notification should be shown, false otherwise.
		 */
		public static function show_feedback() {
			// Stop immediately if user has already left a review.
			if ( get_option( 'afwc_feedback_option_review', false ) ) {
				return false;
			}

			$current_date        = gmdate( 'Y-m-d', Affiliate_For_WooCommerce::get_offset_timestamp() );
			$feedback_start_date = get_option( 'afwc_feedback_start_date', false );
			$feedback_close_date = get_option( 'afwc_feedback_close_date', false );

			// If no close date and plugin was activated, it's a candidate for the first notification.
			if ( empty( $feedback_close_date ) && ! empty( $feedback_start_date ) ) {
				$days_since_activation = ceil( abs( strtotime( $current_date ) - strtotime( $feedback_start_date ) ) / DAY_IN_SECONDS );
				// Only show after 30 days of plugin activation.
				if ( $days_since_activation < 30 ) {
					return false;
				}

				global $wpdb;

				// Check if there are more than 3 affiliates.
				$affiliate_id = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->prepare(
						"SELECT user_id
						FROM {$wpdb->usermeta}
						WHERE meta_key = %s AND meta_value = %s
						LIMIT 3,1",
						'afwc_is_affiliate',
						'yes'
					)
				);
				if ( null !== $affiliate_id ) {
					return true;
				}

				// If not enough affiliates, check further if more than 10 referrals exist.
				$referral_id = $wpdb->get_var( "SELECT referral_id FROM {$wpdb->prefix}afwc_referrals LIMIT 10,1" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				return ( null !== $referral_id );
			}

			// If previously dismissed, show again after 20 days.
			if ( ! empty( $feedback_close_date ) ) {
				$days_since_close = ceil( abs( strtotime( $current_date ) - strtotime( $feedback_close_date ) ) / DAY_IN_SECONDS );
				return ( $days_since_close >= 20 );
			}

			return false;
		}

		/**
		 * Method to update feedback-related options based on user action.
		 */
		public function update_feedback() {
			check_admin_referer( 'afwc-admin-update-feedback', 'security' );

			$update_action = ! empty( $_POST['update_action'] ) ? sanitize_text_field( $_POST['update_action'] ) : ''; // phpcs:ignore
			if ( empty( $update_action ) ) {
				wp_send_json_error( _x( 'Parameter is missing.', 'error message for missing update action', 'affiliate-for-woocommerce' ) );
			}

			if ( in_array( $update_action, array( 'positive', 'negative' ), true ) ) {
				$feedback_responses = get_option(
					'afwc_feedback_responses',
					array(
						'positive' => array(),
						'negative' => array(),
					)
				);
				// Append current timestamp to appropriate response type.
				$feedback_responses[ $update_action ][] = gmdate( 'U' );
				update_option( 'afwc_feedback_responses', $feedback_responses, 'no' );
			} if ( 'close' === $update_action ) {
				$current_date = gmdate( 'Y-m-d', Affiliate_For_WooCommerce::get_offset_timestamp() );
				update_option( 'afwc_feedback_close_date', $current_date, 'no' );
			} elseif ( 'review' === $update_action ) {
				update_option( 'afwc_feedback_option_review', true, 'no' );
			}

			wp_send_json(
				array(
					'ACK' => 'Success',
					'msg' => _x( 'Feedback option updated', 'success message on feedback option update', 'affiliate-for-woocommerce' ),
				)
			);
		}

	}

}

AFWC_Admin_Notifications::get_instance();
