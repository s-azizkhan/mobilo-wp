<?php
/**
 * Some common functions for Affiliate For WooCommerce to manage WordPress compatibility
 *
 * @package     affiliate-for-woocommerce/includes/
 * @since       8.37.0
 * @version     1.1.0
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

if ( ! function_exists( 'afwc_get_default_user_search_args' ) ) {
	/**
	 * Method to get default user search arguments for get_users arguments.
	 *
	 * @param string $term Search term used to find users/affiliates.
	 *
	 * @return array Default search arguments.
	 */
	function afwc_get_default_user_search_args( $term = '' ) {
		return array(
			'search'         => '*' . $term . '*',
			'search_columns' => array( 'ID', 'user_nicename', 'user_login', 'user_email', 'display_name' ),
		);
	}
}
