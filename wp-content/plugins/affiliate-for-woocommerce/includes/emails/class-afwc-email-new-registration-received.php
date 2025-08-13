<?php
/**
 * Main class for New  registration to admin Email
 *
 * @package     affiliate-for-woocommerce/includes/emails/
 * @since       2.4.0
 * @version     1.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AFWC_Email_New_Registration_Received' ) ) {

	/**
	 * The Affiliate New Registration to admin
	 *
	 * @extends \WC_Email
	 */
	class AFWC_Email_New_Registration_Received extends WC_Email {

		/**
		 * Email parameters
		 *
		 * @var array $email_args
		 */
		public $email_args = array();

		/**
		 * Set email defaults
		 */
		public function __construct() {

			// Set ID, this simply needs to be a unique name.
			$this->id = 'afwc_new_registration';

			// This is the title in WooCommerce email settings.
			$this->title = _x( 'Affiliate Manager - New Registration Received', 'title for Affiliate Manager - New Registration Received', 'affiliate-for-woocommerce' );

			// This is the description in WooCommerce email settings.
			$this->description = _x( 'This email will be sent to an affiliate manager when a new affiliate registration request is received or when an affiliate joins automatically.', 'Description for Affiliate Manager - New Registration Received', 'affiliate-for-woocommerce' );

			// These are the default heading and subject lines that can be overridden using the settings.
			$this->subject = _x( '{site_title} - New affiliate user registration', 'subject for Affiliate Manager - New Registration Received', 'affiliate-for-woocommerce' );
			$this->heading = _x( 'New affiliate registration', 'heading for Affiliate Manager - New Registration Received', 'affiliate-for-woocommerce' );

			// Email template location.
			$this->template_html  = 'afwc-new-registration-received.php';
			$this->template_plain = 'plain/afwc-new-registration-received.php';

			// Use our plugin templates directory as the template base.
			$this->template_base = AFWC_PLUGIN_DIRPATH . '/templates/';

			$this->placeholders = array();

			// Trigger this email when a new affiliate registration is received.
			add_action( 'afwc_email_new_registration_received', array( $this, 'trigger' ), 10, 1 );

			// Filter to modify the email object before rendering the preview (WC 9.6.0 introduced email preview).
			add_filter( 'woocommerce_prepare_email_for_preview', array( $this, 'prepare_email_for_preview' ) );

			// Call parent constructor to load any other defaults not explicity defined here.
			parent::__construct();

			// Send the email to affiliate manager if available, else to store admin.
			$this->recipient = get_option( 'afwc_contact_admin_email_address', '' );
			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'admin_email' );
			}
		}

		/**
		 * Determine if the email should actually be sent and setup email merge variables
		 *
		 * @param array $args Email arguments.
		 */
		public function trigger( $args = array() ) {
			if ( empty( $args ) ) {
				return;
			}

			$this->email_args = array();
			$this->email_args = wp_parse_args( $args, $this->email_args );

			$admin = get_user_by( 'email', $this->recipient );

			$this->email_args['admin_name']                   = ! empty( $admin->user_login ) ? $admin->user_login : _x( 'there', 'Greeting for admin', 'affiliate-for-woocommerce' );
			$this->email_args['manage_url']                   = ! empty( $this->email_args['user_id'] ) ? add_query_arg( array( 'page' => 'affiliate-for-woocommerce' ), admin_url( 'admin.php' ) ) . '#!/dashboard/' . $this->email_args['user_id'] : add_query_arg( array( 'page' => 'affiliate-for-woocommerce' ), admin_url( 'admin.php' ) );
			$this->email_args['user_name']                    = ! empty( $this->email_args['userdata']['afwc_first_name'] ) ? $this->email_args['userdata']['afwc_first_name'] . ' ' . ( ! empty( $this->email_args['userdata']['afwc_last_name'] ) ? $this->email_args['userdata']['afwc_last_name'] : '' ) : ( ! empty( $this->email_args['userdata']['user_login'] ) ? $this->email_args['userdata']['user_login'] : '' );
			$this->email_args['user_email']                   = ! empty( $this->email_args['userdata']['afwc_email'] ) ? $this->email_args['userdata']['afwc_email'] : '';
			$this->email_args['additional_information']       = ! empty( $this->email_args['userdata']['afwc_additional_fields'] ) ? $this->email_args['userdata']['afwc_additional_fields'] : '';
			$this->email_args['additional_information_label'] = esc_html_x( 'Additional information', 'Label for additional information', 'affiliate-for-woocommerce' );
			$this->email_args['user_url']                     = ! empty( $this->email_args['userdata']['afwc_website'] ) ? $this->email_args['userdata']['afwc_website'] : '';
			$this->email_args['is_auto_approved']             = ! empty( $this->email_args['is_auto_approved'] ) ? $this->email_args['is_auto_approved'] : get_option( 'afwc_auto_add_affiliate', 'no' );

			// Set the locale to the store locale for customer emails to make sure emails are in the store language.
			$this->setup_locale();

			// For any email placeholders.
			$this->set_placeholders();

			$email_content = $this->get_content();
			// Replace placeholders with values in the email content.
			$email_content = ( is_callable( array( $this, 'format_string' ) ) ) ? $this->format_string( $email_content ) : $email_content;

			// Send email.
			if ( ! empty( $email_content ) && $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $email_content, $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		}

		/**
		 * Function to set placeholder variables used in email.
		 */
		public function set_placeholders() {
			// For any email placeholders.
			$this->placeholders = array(
				'{site_title}' => $this->get_blogname(),
			);
		}

		/**
		 * Function to load email html content
		 *
		 * @return string Email content html
		 */
		public function get_content_html() {
			global $affiliate_for_woocommerce;

			$email_arguments = $this->get_template_args();

			if ( ! empty( $email_arguments ) ) {
				ob_start();

				wc_get_template(
					$this->template_html,
					$email_arguments,
					is_callable( array( $affiliate_for_woocommerce, 'get_template_base_dir' ) ) ? $affiliate_for_woocommerce->get_template_base_dir( $this->template_html ) : '',
					$this->template_base
				);

				return ob_get_clean();
			}

			return '';
		}

		/**
		 * Function to load email plain content
		 *
		 * @return string Email plain content
		 */
		public function get_content_plain() {
			global $affiliate_for_woocommerce;

			$email_arguments = $this->get_template_args();

			if ( ! empty( $email_arguments ) ) {
				ob_start();

				wc_get_template(
					$this->template_plain,
					$email_arguments,
					is_callable( array( $affiliate_for_woocommerce, 'get_template_base_dir' ) ) ? $affiliate_for_woocommerce->get_template_base_dir( $this->template_plain ) : '',
					$this->template_base
				);

				return ob_get_clean();
			}

			return '';
		}

		/**
		 * Function to return the required email arguments for this email template.
		 *
		 * @return array Email arguments.
		 */
		public function get_template_args() {
			return array(
				'email'                        => $this,
				'email_heading'                => is_callable( array( $this, 'get_heading' ) ) ? $this->get_heading() : '',
				'additional_content'           => is_callable( array( $this, 'get_additional_content' ) ) ? $this->get_additional_content() : '',
				'dashboard_url'                => add_query_arg( array( 'page' => 'affiliate-for-woocommerce' ), admin_url( 'admin.php' ) ),
				'user_website_label'           => esc_html_x( 'Website', 'Label for affiliate user website link', 'affiliate-for-woocommerce' ),
				'admin_name'                   => $this->email_args['admin_name'],
				'user_email'                   => $this->email_args['user_email'],
				'user_name'                    => $this->email_args['user_name'],
				'additional_information'       => $this->email_args['additional_information'],
				'additional_information_label' => $this->email_args['additional_information_label'],
				'is_auto_approved'             => $this->email_args['is_auto_approved'],
				'manage_url'                   => esc_url( $this->email_args['manage_url'] ),
				'user_url'                     => esc_url( $this->email_args['user_url'] ),
			);
		}

		/**
		 * Initialize Settings Form Fields
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'            => array(
					'title'   => __( 'Enable/Disable', 'affiliate-for-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'affiliate-for-woocommerce' ),
					'default' => 'yes',
				),
				'subject'            => array(
					'title'       => __( 'Subject', 'affiliate-for-woocommerce' ),
					'type'        => 'text',
					/* translators: %s Email subject. */
					'description' => sprintf( _x( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'Description for the email subject for the new registration received email', 'affiliate-for-woocommerce' ), $this->subject ),
					'placeholder' => $this->subject,
					'default'     => '',
				),
				'heading'            => array(
					'title'       => __( 'Email Heading', 'affiliate-for-woocommerce' ),
					'type'        => 'text',
					/* translators: %s Email heading. */
					'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'affiliate-for-woocommerce' ), $this->heading ),
					'placeholder' => $this->heading,
					'default'     => '',
				),
				'additional_content' => array(
					'title'       => __( 'Additional content', 'affiliate-for-woocommerce' ),
					'description' => __( 'Text to appear below the main email content.', 'affiliate-for-woocommerce' ),
					'css'         => 'width:400px; height: 75px;',
					'placeholder' => __( 'N/A', 'affiliate-for-woocommerce' ),
					'type'        => 'textarea',
					'default'     => is_callable( array( $this, 'get_additional_content' ) ) ? $this->get_additional_content() : '', // WC 3.7 introduced an additional content field for all emails.
				),
				'email_type'         => array(
					'title'       => __( 'Email type', 'affiliate-for-woocommerce' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.', 'affiliate-for-woocommerce' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
				),
			);
		}

		/**
		 * Prepare email dummy data for preview.
		 *
		 * @param WC_Email $email The email object.
		 *
		 * @return WC_Email
		 */
		public function prepare_email_for_preview( $email = null ) {
			if ( empty( $email ) || ! $email instanceof WC_Email ) {
				return $email;
			}
			if ( ! empty( $email->id ) && $email->id !== $this->id ) {
				return $email;
			}
			if ( empty( $email->email_args ) ) {
				$email->email_args = array();
			}

			$email->email_args['user_url']                     = '';
			$email->email_args['additional_information_label'] = '';
			$email->email_args['additional_information']       = array();
			// For preview, link will take user to affiliates dashboard.
			$email->email_args['manage_url'] = add_query_arg( array( 'page' => 'affiliate-for-woocommerce' ), admin_url( 'admin.php' ) );

			return $email;
		}

	}

}
