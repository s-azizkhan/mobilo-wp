<?php

namespace Barn2\Plugin\WC_Product_Table;

use Barn2\WPT_Lib\Conditional;
use Barn2\WPT_Lib\Registerable;
use Barn2\WPT_Lib\Service;
use Barn2\WPT_Lib\Util as Lib_Util;

/**
 * This class handles our product table shortcode.
 *
 * Example:
 * [product_table columns="name,description,price,buy" category="shirts" tag="on-sale"]
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Table_Shortcode implements Service, Registerable, Conditional {

	const SHORTCODE = 'product_table';

	public function is_required() {
		return Lib_Util::is_front_end();
	}

	public function register() {
		self::register_shortcode();
	}

	public static function register_shortcode() {
		add_shortcode( self::SHORTCODE, [ __CLASS__, 'do_shortcode' ] );
	}

	/**
	 * Handles our product table shortcode.
	 *
	 * @param array  $atts    The attributes passed in to the shortcode
	 * @param string $content The content passed to the shortcode (not used)
	 * @return string The shortcode output
	 */
	public static function do_shortcode( $atts, $content = '' ) {
		if ( ! self::can_do_shortocde() ) {
			return '';
		}

		// Run the shortcode args through shortcode_atts.
		$atts = shortcode_atts( Table_Args::get_defaults(), Table_Args::back_compat_args( (array) $atts ), self::SHORTCODE );

		// Return the table as HTML.
		return apply_filters( 'wc_product_table_shortcode_output', wc_get_product_table( $atts ) );
	}

	private static function can_do_shortocde() {
		// Don't process shortcodes in post content in search results.
		if ( is_search() && in_the_loop() && ! apply_filters( 'wc_product_table_run_in_search', false ) ) {
			return false;
		}

		return true;
	}

}
