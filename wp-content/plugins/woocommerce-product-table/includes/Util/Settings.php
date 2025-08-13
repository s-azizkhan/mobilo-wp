<?php

namespace Barn2\Plugin\WC_Product_Table\Util;

use Barn2\Plugin\WC_Product_Table\Admin\Settings_List;
use Barn2\Plugin\WC_Product_Table\Integration\Quick_View_Pro;
use Barn2\WPT_Lib\Admin\Settings_Util;
use Barn2\WPT_Lib\Plugin\Licensed_Plugin;
use WC_Admin_Settings;

/**
 * Utility functions for the product table settings.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Settings {

	/**
	 * Option names for our plugin settings (i.e. the option keys used in wp_options).
	 */
	const OPTION_TABLE_STYLING  = 'wcpt_table_styling';
	const OPTION_TABLE_DEFAULTS = 'wcpt_shortcode_defaults';
	const OPTION_MISC           = 'wcpt_misc_settings';

	/**
	 * The section name within the main WooCommerce Settings.
	 */
	const SECTION_SLUG = 'product-table';

	public static function get_setting_misc() {
		return self::get_setting( self::OPTION_MISC, Defaults::get_misc_defaults() );
	}

	public static function get_setting_table_defaults() {
		return self::get_setting( self::OPTION_TABLE_DEFAULTS, Defaults::get_table_defaults() );
	}

	public static function get_setting_table_styling() {
		return self::get_setting( self::OPTION_TABLE_STYLING, Defaults::get_design_defaults() );
	}

	public static function update_setting_misc( array $settings ) {
		$settings = array_intersect_key( $settings, Defaults::get_misc_defaults() );

		foreach ( $settings as $setting => $setting_value ) {
			$settings[ $setting ] = self::sanitize_setting( $setting_value, $setting );
		}

		update_option( self::OPTION_MISC, self::to_woocommerce_settings( $settings ) );
	}

	public static function update_setting_table_defaults( array $settings ) {
		$all_settings = array_merge( Defaults::get_table_defaults(), array_fill_keys( [ 'filters_custom', 'sort_by_custom' ], '' ) );
		$settings     = array_intersect_key( $settings, $all_settings );

		foreach ( $settings as $setting => $setting_value ) {
			$settings[ $setting ] = self::sanitize_setting( $setting_value, $setting );
		}

		update_option( self::OPTION_TABLE_DEFAULTS, self::to_woocommerce_settings( $settings ) );
	}

	public static function sanitize_option_misc( $value, $option, $raw_value ) {
		$setting = self::get_setting_name( $option, self::OPTION_MISC );

		if ( ! $setting ) {
			return $value;

		}
		return self::sanitize_setting( $value, $setting );
	}

	public static function sanitize_option_table_styling( $value, $option, $raw_value ) {

		if ( 'color_size' === $option['type'] && ! empty( $value['color'] ) ) {
			$value['color'] = sanitize_hex_color( $value['color'] );
		} elseif ( 'color' === $option['type'] && ! empty( $value ) ) {
			$value = sanitize_hex_color( $value );
		}

		return $value;
	}

	public static function sanitize_option_table_defaults( $value, $option, $raw_value ) {
		$setting = self::get_setting_name( $option, self::OPTION_TABLE_DEFAULTS );

		if ( ! $setting ) {
			return $value;
		}

		$value = self::sanitize_setting( $value, $setting );

		if ( 'columns' === $setting && empty( $value ) ) {
			WC_Admin_Settings::add_error( __( 'The columns option is invalid. Please check you have entered valid column names.', 'woocommerce-product-table' ) );
		}

		return $value;
	}

	public static function sanitize_setting( $value, $setting ) {
		$defaults = Defaults::get_table_defaults();

		// Check for empty settings.
		if ( '' === $value && in_array( $setting, [ 'columns', 'image_size', 'links' ], true ) ) {
			$value = $defaults[ $setting ];
		}

		switch ( $setting ) {
			case 'columns':
				$value = Columns::parsed_columns_to_string( Columns::parse_columns( $value ) );
				break;
			case 'links':
				if ( false === $value ) {
					$value = 'none';
				} elseif ( 'true' === $value || true === $value ) {
					$value = 'all';
				}
				break;
			case 'filters':
				if ( false === $value ) {
					$value = 'false';
				} elseif ( true === $value ) {
					$value = 'true';
				}
				break;
			case 'filters_custom':
				$value = Columns::parsed_columns_to_string( Columns::parse_filters( $value ) );
				break;
			case 'lazy_load':
			case 'quantities':
			case 'reset_button':
			case 'cache':
			case 'lightbox':
			case 'shortcodes':
			case 'ajax_cart':
			case 'quick_view_links':
			case 'shop_override':
			case 'search_override':
			case 'archive_override':
			case 'product_tag_override':
			case 'attribute_override':
			case 'include_hidden':
				$value = Settings_Util::bool_to_checkbox_setting( Util::maybe_parse_bool( $value ) );
				break;
			case 'image_size':
				$value = Util::sanitize_image_size( $value );
				break;
			case 'rows_per_page':
			case 'description_length':
			case 'product_limit':
				// Check integer settings.
				if ( 0 === (int) $value ) {
					$value = -1;
				}
				if ( ! is_numeric( $value ) || (int) $value < -1 ) {
					$value = $defaults[ $setting ];
				}
				break;
			case 'cache_expiry':
				$value = absint( $value );
				break;
			case 'add_selected_text':
				if ( '' === $value ) {
					$value = Defaults::add_selected_to_cart_default_text();
				}
				break;
		}

		return $value;
	}

	public static function settings_to_table_defaults( $settings ) {
		if ( empty( $settings ) || ! is_array( $settings ) ) {
			return $settings;
		}

		$defaults = Defaults::get_table_defaults();

		// Custom filter option
		if ( isset( $settings['filters'] ) ) {
			$settings['filters'] = Util::maybe_parse_bool( $settings['filters'] );

			if ( 'custom' === $settings['filters'] ) {
				if ( empty( $settings['filters_custom'] ) ) {
					// Custom filters selected in settings but none defined, so reset to default.
					$settings['filters'] = $defaults['filters'];
				} else {
					$settings['filters'] = $settings['filters_custom'];
				}
			}
		}

		// Custom sort by option
		if ( isset( $settings['sort_by'] ) && 'custom' === $settings['sort_by'] ) {
			if ( empty( $settings['sort_by_custom'] ) ) {
				// Custom order by selected in settings but no order defined, so reset to default.
				$settings['sort_by'] = $defaults['sort_by'];
			} else {
				$settings['sort_by'] = $settings['sort_by_custom'];
			}
		}

		// Unset settings that don't map to args.
		unset( $settings['filters_custom'] );
		unset( $settings['sort_by_custom'] );

		if ( isset( $settings['links'] ) ) {
			$settings['links'] = Util::maybe_parse_bool( $settings['links'] );
		}

		return $settings;
	}

	public static function to_woocommerce_settings( $args ) {
		if ( empty( $args ) || ! is_array( $args ) ) {
			return $args;
		}

		foreach ( $args as $key => $value ) {
			if ( is_bool( $value ) ) {
				$args[ $key ] = Settings_Util::bool_to_checkbox_setting( $value );
			}
		}

		return $args;
	}

	private static function get_setting( $option_name, $default = [] ) {
		$option_value = get_option( $option_name, $default );

		if ( is_array( $option_value ) ) {
			// Merge with defaults.
			if ( is_array( $default ) ) {
				$option_value = wp_parse_args( $option_value, $default );
			}

			// Convert 'yes'/'no' options to booleans.
			$option_value = array_map( [ self::class, 'maybe_checkbox_setting_to_bool' ], $option_value );
		}

		return $option_value;
	}

	private static function get_setting_name( $option, $option_name ) {
		$option_name_array = [];
		parse_str( $option['id'], $option_name_array );

		return isset( $option_name_array[ $option_name ] ) ? key( $option_name_array[ $option_name ] ) : false;
	}

	/**
	 * Maybe convert a 'yes' or 'no' string to a bool.
	 *
	 * @param mixed $val The option value.
	 * @return mixed|bool bool if 'yes' or 'no', otherwise the original value.
	 */
	private static function maybe_checkbox_setting_to_bool( $val ) {
		if ( in_array( $val, [ 'yes', 'no' ], true ) ) {
			return Settings_Util::checkbox_setting_to_bool( $val );
		}

		return $val;
	}

	/**
	 * Get the default 'Add Selected To Cart' text.
	 *
	 * @return string
	 * @deprecated 3.0.2 Replaced by Defaults::add_selected_to_cart_default_text
	 */
	public static function add_selected_to_cart_default_text() {
		_deprecated_function( __METHOD__, '3.0.2', 'Defaults::add_selected_to_cart_default_text' );
		return Defaults::add_selected_to_cart_default_text();
	}

	/**
	 * Convert boolean values to strings such as "yes" or "no".
	 *
	 * @param mixed $val
	 * @return string 'yes' if true, 'no' otherwise
	 * @deprecated 3.0.2 Replaced by Settings_Util::bool_to_checkbox_setting
	 */
	public static function bool_to_yes_no( $val ) {
		_deprecated_function( __METHOD__, '3.0.2', 'Settings_Util::bool_to_checkbox_setting' );
		if ( $val === true || $val === 'true' ) {
			return 'yes';
		}

		return 'no';
	}

	/**
	 * @param Licensed_Plugin $plugin
	 * @return array
	 * @deprecated 3.0.2 Replaced by Settings_List::get_all_settings
	 */
	public static function get_settings_list( Licensed_Plugin $plugin ) {
		_deprecated_function( __METHOD__, '3.0.2', 'Settings_List::get_all_settings' );
		return Settings_List::get_all_settings( $plugin );
	}

	/**
	 * @param $val
	 * @return bool|mixed
	 * @deprecated 3.0.2 Replaced by maybe_checkbox_setting_to_bool
	 */
	public static function yes_no_to_boolean( $val ) {
		_deprecated_function( __METHOD__, '3.0.2', 'maybe_checkbox_setting_to_bool' );
		if ( 'yes' === $val ) {
			return true;
		} elseif ( 'no' === $val ) {
			return false;
		}

		return $val;
	}

	/**
	 * Should we open table links with QVP.
	 *
	 * @return bool
	 * @deprecated 2.9.6 Replaced by Quick_View_Pro::open_links_in_quick_view
	 */
	public static function open_links_in_quick_view_pro() {
		_deprecated_function( __METHOD__, '2.9.6', 'Barn2\Plugin\WC_Product_Table\Integration\Quick_View_Pro::open_links_in_quick_view' );
		return Quick_View_Pro::open_links_in_quick_view();
	}

}
