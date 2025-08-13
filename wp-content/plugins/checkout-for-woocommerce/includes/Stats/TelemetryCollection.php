<?php

namespace Objectiv\Plugins\Checkout\Stats;

use Exception;
use Mixpanel;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;
use Objectiv\Plugins\Checkout\Managers\UpdatesManager;
use Objectiv\Plugins\Checkout\SingletonAbstract;

class TelemetryCollection extends SingletonAbstract {

	const MIXPANEL_TOKEN         = '0dcd9a917fe6787d096b543c926014e0';
	const SCHEDULE_INTERVAL_DAYS = 7;

	const GROUP_KEY = 'license';

	/**
	 * @var Mixpanel|null Mixpanel HTTP Client instance.
	 */
	private $mixpanel = null;

	/**
	 * Initialize telemetry collection.
	 */
	public function init() {
		// Initialize Mixpanel and schedule updates if telemetry is enabled on load
		if ( ! $this->is_telemetry_enabled() ) {
			return;
		}

		$this->initialize_mixpanel();
		$this->schedule_telemetry_update();
		$this->init_hooks();
	}

	public function init_hooks() {
		add_action( 'cfw_updated_setting', array( $this, 'track_feature_changes' ), 10, 3 );
		add_action( 'cfw_admin_output_page', array( $this, 'track_page_view' ), 10, 3 );
		add_action( 'cfw_license_data_changed', array( $this, 'track_license_changes' ) );

		// Schedule hook callback
		add_action( 'cfw_telemetry_update', array( $this, 'run_telemetry_update' ) );
	}

	/**
	 * Check if telemetry is enabled by the user.
	 *
	 * @return bool
	 */
	private function is_telemetry_enabled(): bool {
		return ! defined( 'CFW_DISABLE_TELEMETRY_TRACKING' ) || false === CFW_DISABLE_TELEMETRY_TRACKING;
	}

	/**
	 * Initialize the MixpanelHttpClient instance.
	 */
	private function initialize_mixpanel() {
		if ( $this->mixpanel || empty( self::MIXPANEL_TOKEN ) ) {
			return;
		}

		try {
			$this->mixpanel = new Mixpanel(
				self::MIXPANEL_TOKEN,
				array(
					'consumers' => array( 'wp' => '\Objectiv\Plugins\Checkout\Stats\Consumers\WPMixPanelConsumer' ),
					'consumer'  => 'wp',
				)
			);
		} catch ( Exception $e ) {
			wc_get_logger()->error( 'Mixpanel Init Error: ' . $e->getMessage(), array( 'source' => 'checkout-wc' ) );
			$this->mixpanel = null;
		}
	}

	/**
	 * Get the unique identifier for the site group.
	 *
	 * @return string The site's home URL.
	 */
	private function get_site_identifier(): string {
		$home_url = UpdatesManager::get_home_url();

		if ( ! StatCollection::instance()->tracking_allowed() ) {
			$home_url = wp_hash( $home_url );
		}

		return $home_url;
	}

	/**
	 * Schedule the periodic telemetry update if not already scheduled.
	 */
	public function schedule_telemetry_update() {
		if ( ! as_has_scheduled_action( 'cfw_telemetry_update' ) ) {
			as_schedule_recurring_action( time(), DAY_IN_SECONDS * self::SCHEDULE_INTERVAL_DAYS, 'cfw_telemetry_update', array(), 'checkoutwc' );
		}
	}

	/**
	 * Unschedule the periodic telemetry update.
	 */
	public function unschedule_telemetry_update() {
		as_unschedule_all_actions( 'cfw_telemetry_update' );
	}

	/**
	 * Runs the periodic telemetry update.
	 * Collects specified settings, associates the user with the site group,
	 * and sends settings as group properties to Mixpanel using MixpanelHttpClient.
	 */
	public function run_telemetry_update() {
		if ( ! $this->is_telemetry_enabled() ) {
			return;
		}

		if ( ! $this->mixpanel ) {
			return;
		}

		$license_id         = wp_hash( UpdatesManager::instance()->get_license_key() );
		$site_id            = $this->get_site_identifier();
		$properties_to_send = $this->collect_properties();
		$license_data       = UpdatesManager::instance()->get_license_data();

		if ( empty( $properties_to_send ) ) {
			return;
		}

		// Identify the user
		$this->mixpanel->identify( $site_id );

		$properties_to_send[ self::GROUP_KEY ] = $license_id;

		$this->mixpanel->people->set( $site_id, $properties_to_send );

		// Set properties on the group profile itself
		$this->mixpanel->group->set(
			self::GROUP_KEY,
			$license_id,
			array(
				'price_id'         => UpdatesManager::instance()->get_license_price_id(),
				'plan_name'        => UpdatesManager::instance()->get_plan_name(),
				'status'           => UpdatesManager::instance()->get_field_value( 'key_status' ),
				'license_limit'    => $license_data->license_limit ?? 0,
				'site_count'       => $license_data->site_count ?? 0,
				'activations_left' => $license_data->activations_left ?? 0,
			)
		);
	}

	/**
	 * Collects and prepares settings values for Mixpanel group properties.
	 *
	 * @return array
	 */
	private function collect_properties(): array {
		$settings_manager     = SettingsManager::instance();
		$settings_to_track    = $this->get_settings_to_track();
		$properties           = array();
		$active_template_slug = cfw_get_active_template()->get_slug();

		foreach ( $settings_to_track as $setting_key ) {
			$keys = array();
			// Handle template-specific settings
			if ( in_array( $setting_key, array( 'custom_css', 'body_font', 'heading_font', 'label_style', 'footer_text', 'logo_attachment_id' ), true ) ) {
				$keys[] = $active_template_slug;
			}

			$value                      = $settings_manager->get_setting( $setting_key, $keys );
			$properties[ $setting_key ] = $this->prepare_setting_value( $setting_key, $value );
		}

		// Add environment data as group properties
		$properties['cfw_version']       = CFW_VERSION;
		$properties['php_version']       = PHP_VERSION;
		$properties['wc_version']        = function_exists( 'WC' ) ? WC()->version : 'N/A';
		$properties['wp_version']        = get_bloginfo( 'version' );
		$properties['is_multisite']      = is_multisite();
		$properties['is_staging_or_dev'] = $this->is_staging_or_dev_site();

		return $properties;
	}

	/**
	 * Determines the list of settings keys to track.
	 *
	 * @return array
	 */
	private function get_settings_to_track(): array {
		// Note: This static list ensures predictable data collection.
		return array(
			'installed',
			'active_template',
			'enable',
			'allow_tracking', // Note: This is the old usage collection data
			'allow_uninstall',
			'login_style',
			'registration_style',
			'cart_item_link',
			'cart_item_link_target_new_window',
			'cart_item_data_display',
			'show_cart_item_discount',
			'show_side_cart_item_discount',
			'skip_shipping_step',
			'disable_auto_open_login_modal',
			'enable_order_notes',
			'enable_debug_log',
			'enable_highlighted_countries',
			'template_loader',
			'show_logos_mobile',
			'show_mobile_coupon_field',
			'enable_mobile_cart_summary',
			'enable_mobile_totals',
			'enable_order_pay',
			'enable_thank_you_page',
			'enable_map_embed',
			'override_view_order_template',
			'user_matching',
			'hide_optional_address_fields_behind_link',
			'use_fullname_field',
			'enable_pickup_ship_option',
			'enable_pickup_method_step',
			'enable_pickup_shipping_method_other_regex',
			'enable_coupon_code_link',
			'enable_order_bumps',
			'shake_floating_cart_button',
			'enable_side_cart_suggested_products',
			'enable_side_cart_suggested_products_random_fallback',
			'force_different_billing_address',
			'skip_cart_step',
			'hide_admin_bar_button',
			'enable_beta_version_updates',
			'show_item_remove_button',
			'enable_promo_codes_on_side_cart',
			'enable_side_cart_totals',
			'disable_domain_autocomplete',
			'auto_select_free_shipping_method',
			'hide_billing_address_for_free_orders',
			'header_scripts',
			'footer_scripts',
			'php_snippets',
			'header_scripts_checkout',
			'footer_scripts_checkout',
			'header_scripts_thank_you',
			'footer_scripts_thank_you',
			'header_scripts_order_pay',
			'footer_scripts_order_pay',
			'custom_css', // Template specific
			'body_font', // Template specific
			'heading_font', // Template specific
			'label_style', // Template specific
			'footer_text', // Template specific
			'logo_attachment_id', // Template specific
			'disable_express_checkout',
			'allow_checkout_cart_item_variation_changes',
			'allow_side_cart_item_variation_changes',
			'enable_astra_support',
			'enable_discreet_address_1_fields',
			'enable_free_shipping_progress_bar_at_checkout',
			'enable_side_cart_continue_shopping_button',
			'hide_floating_cart_button_empty_cart',
			'hide_pickup_methods',
			'show_cart_item_discounts',
			'trust_badge_position',
			'enable_smartystreets_integration',
			'enable_acr',
			'acr_simulate_only',
			'enable_fetchify_address_autocomplete',
			'enable_order_review_step',
			'enable_address_autocomplete',
			'enable_international_phone_field',
			'enable_pickup',
			'enable_trust_badges',
			'enable_cart_editing',
			'enable_side_cart',
			'enable_ajax_add_to_cart',
			'enable_free_shipping_progress_bar',
			'enable_floating_cart_button',
			'enable_order_bumps_on_side_cart',
			'enable_side_cart_payment_buttons',
			'enable_one_page_checkout',
			'enable_side_cart_coupon_code_link',
			'enable_sticky_cart_summary',
			'allow_cashier_for_woocommerce_address_modification',
			'allow_thcfe_address_modification',
			'enable_beaver_themer_support',
			'enable_elementor_pro_support',
			'allow_checkout_field_editor_address_modification',
			'enable_wp_rocket_delay_js_compatibility_mode',
			'international_phone_field_standard',
			'highlighted_countries',
			'acr_abandoned_time',
			'discreet_address_1_fields_order',
			'side_cart_icon',
		);
	}

	/**
	 * Prepares a setting value for sending to Mixpanel.
	 * Converts boolean-like strings ('yes'/'no') to actual booleans.
	 * Checks script/CSS fields for content (true if not empty).
	 * Checks specific known array settings for content (true if not empty).
	 *
	 * @param string $key   The setting key.
	 * @param mixed  $value The setting value.
	 * @return mixed Prepared value.
	 */
	private function prepare_setting_value( string $key, $value ) {
		$script_css_keys = array(
			'header_scripts',
			'footer_scripts',
			'php_snippets',
			'header_scripts_checkout',
			'footer_scripts_checkout',
			'header_scripts_thank_you',
			'footer_scripts_thank_you',
			'header_scripts_order_pay',
			'footer_scripts_order_pay',
			'custom_css',
			'footer_text',
		);

		if ( in_array( $key, $script_css_keys, true ) ) {
			return ! empty( trim( $value ) );
		}

		// Handle specific array settings explicitly
		$array_check_keys = array(
			'highlighted_countries',
			'thank_you_order_statuses',
			'enabled_billing_address_fields',
			'pickup_methods',
			'acr_excluded_roles',
			'acr_recovered_order_statuses',
			'store_policies',
			'trust_badges',
		);
		if ( in_array( $key, $array_check_keys, true ) ) {
			return is_array( $value ) && count( $value ) > 0;
		}

		if ( 'yes' === $value ) {
			return true;
		}
		if ( 'no' === $value ) {
			return false;
		}

		// Return original value for other types (strings, numbers, etc.)
		return $value;
	}

	/**
	 * Tracks changes in 'enable*' settings (excluding telemetry itself) and sends events to Mixpanel,
	 * associating the event with the site group.
	 * Runs with priority 10 on the settings saved hook.
	 *
	 * @param string $setting_key The setting key.
	 * @param mixed  $value The new value.
	 * @param mixed  $old_value The old value.
	 */
	public function track_feature_changes( $setting_key, $value, $old_value ) {
		// Nonce is verified in SettingsManagerAbstract::save_settings before this action fires.

		if ( ! $this->is_telemetry_enabled() ) {
			return; // Don't track if telemetry is currently off
		}

		if ( ! $this->mixpanel ) {
			return; // Don't track if Mixpanel isn't available
		}

		if ( $old_value === $value ) {
			return;
		}

		$setting_key = str_replace( '_cfw_', '', $setting_key );

		// Identify the user and associate them with the site group using people->set
		// Also set the $email property on the user profile
		$this->mixpanel->identify( $this->get_site_identifier() );

		$event_properties = array( 'feature' => $setting_key );

		if ( 'active_template' === $setting_key ) {
			$this->mixpanel->track( 'template_activated', array( 'template' => $value ) );
			$this->mixpanel->track( 'template_deactivated', array( 'template' => $old_value ) );
			return;
		}

		if ( 'yes' === $value ) {
			$this->mixpanel->track( 'feature_activated', $event_properties );
		} elseif ( 'no' === $value ) {
			$this->mixpanel->track( 'feature_deactivated', $event_properties );
		}
	}

	public function track_page_view( $slug ) {
		if ( empty( $slug ) ) {
			return;
		}

		$this->mixpanel->identify( $this->get_site_identifier() );
		$this->mixpanel->track( 'admin_page_view', array( $slug ) );
	}

	public function track_license_changes( $changes = array() ) {
		$this->mixpanel->identify( $this->get_site_identifier() );
		$this->mixpanel->track( 'license_data_changed', $changes );
	}

	/**
	 * Determines if the current site is a staging or development environment.
	 *
	 * @return bool True if staging/dev/test, false otherwise.
	 */
	public function is_staging_or_dev_site() {
		$host = wp_parse_url( home_url(), PHP_URL_HOST );

		if ( empty( $host ) ) {
			return false;
		}

		$host = strtolower( $host );

		// List of known dev/staging subdomains and wildcard domains.
		$subdomains_to_check = array(
			'dev.',
			'stage.',
			'staging.',
			'staging-*.',
			'*.staging.',
			'*.test.',
			'*.wpengine.com',
			'*.instawp.xyz',
			'*.cloudwaysapps.com',
			'*.flywheelsites.com',
			'*.flywheelstaging.com',
			'*.myftpupload.com',
			'*.kinsta.cloud',
			'*.sozowebdesign.co.uk',
			'*.wpdns.site',
			'*.closte.com',
			'*.wpcomstaging.com',
			'*.sg-host.com',
			'*.ddev.site',
			'*.pantheonsite.io',
			'*.wpstage.net',
			'*.templweb.com',
			'dev.nfs.health',
			'*.wordifysites.com',
			'*.aubrie-app.fndr-infra.de',
		);

		// List of TLDs that usually indicate local or dev.
		$tlds_to_check = array(
			'.dev',
			'.local',
			'.test',
		);

		// Check TLDs
		foreach ( $tlds_to_check as $tld ) {
			if ( str_ends_with( $host, $tld ) ) {
				return true;
			}
		}

		// Check subdomains/wildcards
		foreach ( $subdomains_to_check as $pattern ) {
			// Exact match or prefix match
			if ( str_starts_with( $pattern, '*' ) ) {
				$needle = ltrim( $pattern, '*.' );
				if ( str_ends_with( $host, $needle ) ) {
					return true;
				}
			} elseif ( str_ends_with( $pattern, '.' ) || str_ends_with( $pattern, '-.' ) ) {
				if ( str_starts_with( $host, rtrim( $pattern, '.' ) ) ) {
					return true;
				}
			} elseif ( $host === $pattern ) {
					return true;
			}
		}

		return false;
	}

	/**
	 * Clean up on deactivation.
	 */
	public function deactivate() {
		$this->unschedule_telemetry_update();
	}
}
