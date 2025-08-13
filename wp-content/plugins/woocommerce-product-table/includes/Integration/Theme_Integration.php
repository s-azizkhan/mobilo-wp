<?php

namespace Barn2\Plugin\WC_Product_Table\Integration;

use Barn2\Plugin\WC_Product_Table\Frontend_Scripts;
use Barn2\Plugin\WC_Product_Table\Table_Args;
use Barn2\Plugin\WC_Product_Table\Util\Util;
use Barn2\WPT_Lib\Conditional;
use Barn2\WPT_Lib\Registerable;
use Barn2\WPT_Lib\Service;
use Barn2\WPT_Lib\Util as Lib_Util;
use function Barn2\Plugin\WC_Product_Table\wpt;

/**
 * Handles compatibility and integration of WPT with specific themes.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Theme_Integration implements Service, Registerable, Conditional {

	private $theme;

	public function __construct() {
		$this->theme = strtolower( get_template() );
	}

	public function is_required() {
		return Lib_Util::is_front_end();
	}

	public function register() {
		if ( in_array( $this->theme, [ 'astra', 'avada', 'divi', 'enfold', 'flatsome', 'jupiter' ], true ) ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'add_theme_inline_script' ], 50 );
		}

		switch ( $this->theme ) {
			case 'avada':
				add_filter(
					'wc_product_table_multi_cart_class',
					function ( $class ) {
						return $class . ' button-default';
					}
				);
				add_filter(
					'wc_product_table_button_class',
					function ( $class ) {
						return $class . ' button-default fusion-button-default-size';
					}
				);
				add_filter(
					'woocommerce_loop_add_to_cart_args',
					function ( $args ) {
						if ( did_action( 'wc_product_table_before_get_data' ) ) {
							$args['class'] = $args['class'] . ' button-default fusion-button-default-size';
						}
						return $args;
					}
				);
				break;
			case 'kallyas':
				add_filter( 'add_to_cart_fragments', [ $this, 'kallyas_ensure_valid_add_to_cart_fragments' ], 20 );
				break;
			case 'porto':
				// Prevent 'View Cart' links added by Porto theme appearing in table
				remove_action( 'woocommerce_after_add_to_cart_button', 'porto_view_cart_after_add', defined( 'WC_STRIPE_PLUGIN_NAME' ) ? 8 : 35 );
				break;
			case 'uncode':
				add_filter( 'add_to_cart_class', [ $this, 'uncode_child_add_to_cart_class' ] );
				break;
			case 'woodmart':
				add_action( 'wc_product_table_load_table_scripts', [ $this, 'woodmart_load_quantity_script' ] );
				break;
		}

	}

	public function add_theme_inline_script() {
		$inline_script_file = realpath( wpt()->get_dir_path() . "assets/js/compat/theme/{$this->theme}.js" );

		if ( $inline_script_file ) {
			$inline_script_contents = file_get_contents( $inline_script_file );
			wp_add_inline_script( Frontend_Scripts::SCRIPT_HANDLE, $inline_script_contents );
		}
	}

	public function kallyas_ensure_valid_add_to_cart_fragments( $fragments ) {
		if ( ! isset( $fragments['zn_added_to_cart'] ) ) {
			$fragments['zn_added_to_cart'] = '';
		}

		return $fragments;
	}

	/**
	 * Add the standard 'single_add_to_cart_button' class as this is required for the add to cart buttons to work.
	 *
	 * @param string $class The cart button class
	 * @return string The cart button class
	 */
	public function uncode_child_add_to_cart_class( $class ) {
		return $class . ' single_add_to_cart_button';
	}

	public function woodmart_load_quantity_script( Table_Args $args ) {
		if ( $args->quantities && function_exists( 'woodmart_enqueue_js_script' ) ) {
			woodmart_enqueue_js_script( 'woocommerce-quantity' );
		}
	}

}
