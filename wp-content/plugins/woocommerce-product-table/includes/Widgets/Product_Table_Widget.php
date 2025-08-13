<?php

namespace Barn2\Plugin\WC_Product_Table\Widgets;

use Barn2\Plugin\WC_Product_Table\Util\Util;
use WC_Widget;

/**
 * Abstract widget class extended by the Product Table widgets.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
abstract class Product_Table_Widget extends WC_Widget {

	public function __construct() {
		parent::__construct();
		add_filter( 'body_class', [ self::class, 'body_class' ], 50 );
	}

	public static function body_class( $classes ) {
		// Add .woocommerce to body class if product table used on page, so filter widgets pick up correct styles in certain themes (Genesis, Total, etc).
		if ( ! in_array( 'woocommerce', $classes, true ) && Util::is_table_on_page() ) {
			$classes[] = 'woocommerce';
		}

		return $classes;
	}

	protected static function get_main_tax_query() {
		global $wp_the_query;
		return isset( $wp_the_query->tax_query, $wp_the_query->tax_query->queries ) ? $wp_the_query->tax_query->queries : [];
	}

	protected static function get_main_meta_query() {
		global $wp_the_query;
		return isset( $wp_the_query->query_vars['meta_query'] ) ? $wp_the_query->query_vars['meta_query'] : [];
	}

	/**
	 * Return the currently viewed taxonomy name.
	 *
	 * @return string
	 */
	protected function get_current_taxonomy() {
		return is_tax() ? get_queried_object()->taxonomy : '';
	}

	/**
	 * Return the currently viewed term ID.
	 *
	 * @return int
	 */
	protected function get_current_term_id() {
		return absint( is_tax() ? get_queried_object()->term_id : 0 );
	}

	/**
	 * Return the currently viewed term slug.
	 *
	 * @return int
	 */
	protected function get_current_term_slug() {
		return absint( is_tax() ? get_queried_object()->slug : 0 );
	}

}
