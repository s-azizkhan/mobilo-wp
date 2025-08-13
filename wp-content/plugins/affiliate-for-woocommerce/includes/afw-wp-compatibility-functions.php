<?php
/**
 * Some common functions for Affiliate For WooCommerce to manage WordPress compatibility
 *
 * @package     affiliate-for-woocommerce/includes/
 * @since       8.37.0
 * @version     1.0.0
 */

if ( ! function_exists( 'afwc_is_wp_doing_ajax' ) ) {
	/**
	 * Determines whether the current request is a WordPress Ajax request.
	 *
	 * @return bool True if it's a WordPress Ajax request, false otherwise.
	 */
	function afwc_is_wp_doing_ajax() {
		return function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' ) && DOING_AJAX;
	}
}
