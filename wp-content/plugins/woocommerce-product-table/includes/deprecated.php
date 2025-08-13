<?php
/**
 * Provides backwards compatibility for deprecated code.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */

// phpcs:disable Generic.Files.OneObjectStructurePerFile.MultipleFound, PSR1.Classes.ClassDeclaration.MultipleClasses, PSR1.Classes.ClassDeclaration.MissingNamespace, Generic.Commenting.DocComment.MissingShort, Squiz.Commenting.FunctionComment.Missing

namespace {

	use Barn2\Plugin\WC_Product_Table\Util\Settings;
	use Barn2\Plugin\WC_Product_Table\Util\Util;
	use Barn2\WPT_Lib\Table\Table_Data_Interface;

	// Prevent direct file access
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'WCPT_Util' ) ) {

		/**
		 * @deprecated 2.8 Replaced by Barn2\Plugin\WC_Product_Table\Util\Util
		 */
		final class WCPT_Util {

			public static function __callStatic( $name, $args ) {
				if ( method_exists( Util::class, $name ) ) {
					_deprecated_function( __METHOD__, '2.8', Util::class . "::$name" );
					return call_user_func_array( [ Util::class, $name ], $args );
				}

				return null;
			}

		}

	}

	if ( ! class_exists( 'WCPT_Settings' ) ) {

		/**
		 * @deprecated 2.8 Replaced by Barn2\Plugin\WC_Product_Table\Util\Settings
		 */
		final class WCPT_Settings {

			/**
			 * @deprecated 2.8 Replaced by constants in Barn2\Plugin\WC_Product_Table\Util\Settings
			 */
			const SECTION_SLUG = Settings::SECTION_SLUG;

			public static function __callStatic( $name, $args ) {
				if ( method_exists( Settings::class, $name ) ) {
					_deprecated_function( __METHOD__, '2.8', Settings::class . "::$name" );
					return call_user_func_array( [ Settings::class, $name ], $args );
				}

				return null;
			}

			public static function get_setting_table_defaults() {
				_deprecated_function( __METHOD__, '2.8', 'Settings::get_setting_table_defaults()' );
				return Settings::get_setting_table_defaults();
			}

			public static function get_setting_misc() {
				_deprecated_function( __METHOD__, '2.8', 'Settings::get_setting_misc()' );
				return Settings::get_setting_misc();
			}

		}

	}

	if ( ! interface_exists( 'Product_Table_Data' ) ) {

		/**
		 * @deprecated 2.8 Replaced by Barn2\WPT_Lib\Table\Table_Data_Interface.
		 */
		interface Product_Table_Data extends Table_Data_Interface {

		}

	}
}

namespace Barn2\Plugin\WC_Product_Table\Util {

	/**
	 * Column utility functions.
	 *
	 * @deprecated 3.0.3 Replaced by Barn2\Plugin\WC_Product_Table\Util\Columns
	 */
	class Columns_Util {

		public static function __callStatic( $name, $args ) {
			if ( method_exists( Columns::class, $name ) ) {
				return call_user_func_array( [ Columns::class, $name ], $args );
			}

			return null;
		}

	}

}



