<?php

namespace Barn2\Plugin\WC_Product_Table\Admin;

use Barn2\Plugin\WC_Product_Table\Util\Settings;
use Barn2\WPT_Lib\Plugin\Licensed_Plugin;
use Barn2\WPT_Lib\Registerable;
use Barn2\WPT_Lib\WooCommerce\Admin\Custom_Settings_Fields;
use Barn2\WPT_Lib\WooCommerce\Admin\Plugin_Promo;

/**
 * Provides functions for the plugin settings page in the WordPress admin.
 *
 * Settings can be accessed at WooCommerce -> Settings -> Products -> Product tables.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Settings_Page implements Registerable {

	private $plugin;

	public function __construct( Licensed_Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function register() {
		// Register our custom settings types.
		$extra_setting_fields = new Custom_Settings_Fields( $this->plugin );
		$extra_setting_fields->register();

		// Add sections & settings.
		add_filter( 'woocommerce_get_sections_products', [ $this, 'add_section' ] );
		add_filter( 'woocommerce_get_settings_products', [ $this, 'add_settings' ], 10, 2 );

		// Support old settings structure.
		add_action( 'woocommerce_settings_products', [ $this, 'back_compat_settings' ], 5 );

		// Sanitize settings
		$license_setting = $this->plugin->get_license_setting();
		add_filter( 'woocommerce_admin_settings_sanitize_option_' . $license_setting->get_license_setting_name(), [ $license_setting, 'save_license_key' ] );
		add_filter( 'woocommerce_admin_settings_sanitize_option_' . Settings::OPTION_TABLE_STYLING, [ Settings::class, 'sanitize_option_table_styling' ], 10, 3 );
		add_filter( 'woocommerce_admin_settings_sanitize_option_' . Settings::OPTION_TABLE_DEFAULTS, [ Settings::class, 'sanitize_option_table_defaults' ], 10, 3 );
		add_filter( 'woocommerce_admin_settings_sanitize_option_' . Settings::OPTION_MISC, [ Settings::class, 'sanitize_option_misc' ], 10, 3 );

		// Add plugin promo section.
		$plugin_promo = new Plugin_Promo( $this->plugin, 'products', Settings::SECTION_SLUG );
		$plugin_promo->register();
	}

	public function add_section( $sections ) {
		$sections[ Settings::SECTION_SLUG ] = __( 'Product tables', 'woocommerce-product-table' );

		return $sections;
	}

	public function add_settings( $settings, $current_section ) {
		// Check we're on the correct settings section
		if ( Settings::SECTION_SLUG !== $current_section ) {
			return $settings;
		}

		return Settings_List::get_all_settings( $this->plugin );
	}

	public function back_compat_settings() {
		$shortcode_defaults = get_option( Settings::OPTION_TABLE_DEFAULTS, [] );

		if ( ! empty( $shortcode_defaults['add_selected_text'] ) ) {
			$misc_settings                      = get_option( Settings::OPTION_MISC, [] );
			$misc_settings['add_selected_text'] = $shortcode_defaults['add_selected_text'];
			update_option( Settings::OPTION_MISC, $misc_settings );

			unset( $shortcode_defaults['add_selected_text'] );
			update_option( Settings::OPTION_TABLE_DEFAULTS, $shortcode_defaults );
		}

		if ( isset( $shortcode_defaults['show_quantity'] ) ) {
			$shortcode_defaults['quantities'] = $shortcode_defaults['show_quantity'];

			unset( $shortcode_defaults['show_quantity'] );
			update_option( Settings::OPTION_TABLE_DEFAULTS, $shortcode_defaults );
		}
	}

}
