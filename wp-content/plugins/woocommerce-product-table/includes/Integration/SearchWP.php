<?php

namespace Barn2\Plugin\WC_Product_Table\Integration;

use Barn2\WPT_Lib\Registerable;
use Barn2\WPT_Lib\Service;

/**
 * Handles integration with SearchWP.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class SearchWP implements Registerable, Service {

	private $args;
	private $applicable = false;

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		if ( ! defined( 'SEARCHWP_PREFIX' ) || defined( 'SEARCHWP_WOOCOMMERCE_PRODUCT_TABLE_VERSION' ) ) {
			return;
		}

		add_action( 'wc_product_table_before_product_query', [ $this, 'handle_posts_query' ] );
		add_filter( 'wc_product_table_query_args', [ $this, 'handle_wpt_args' ], 99, 2 );
		add_filter( 'searchwp\native\args', [ $this, 'handle_searchwp_args' ], 100, 2 );
	}

	/**
	 * Determine if we should run the query through SearchWP
	 *
	 * @param Table_Query $query
	 */
	public function handle_posts_query( $query ) {
		$search_term = ! empty( $query->args->user_search_term ) ? $query->args->user_search_term : $query->args->search_term;
		$applicable  = apply_filters( 'searchwp\barn2\wc_product_table\applicable', ! empty( trim( $search_term ) ), $query );

		if ( $applicable ) {
			$this->applicable = true;
			add_filter( 'searchwp\native\force', '__return_true', 131 );
			add_filter( 'searchwp\native\strict', '__return_false', 131 );
			add_filter( 'searchwp\native\short_circuit', '__return_false', 999 );
		}
	}

	/**
	 * Store the args so we can pass them to the SearchWP query.
	 *
	 * @param array $query_args
	 * @param Table_Query $query
	 * @return array
	 */
	public function handle_wpt_args( $query_args, $query ) {
		$this->args = $query_args;

		return $query_args;
	}

	/**
	 * Run our query through SearchWP.
	 *
	 * @param array $args
	 * @param WP_Query $query
	 * @return array
	 */
	public function handle_searchwp_args( $args, $query ) {
		if ( $this->applicable ) {
			// Traditional pagination isn't used.
			add_filter(
				'searchwp\query\args',
				function ( $args ) {
					// There are two queries run, one for this page and one to get totals.
					// We need to customize the offset and per page for the table data
					// but set nopaging=true when trying to find the totals.
					if ( -1 != $args['per_page'] ) {
						$args['offset']   = isset( $_REQUEST['start'] ) ? absint( $_REQUEST['start'] ) : 0;
						$args['per_page'] = isset( $_REQUEST['length'] ) ? absint( $_REQUEST['length'] ) : 25;
					}

					return $args;
				},
				20
			);

			$args = apply_filters(
				'searchwp\barn2\wc_product_table\query\args',
				array_merge( $args, $this->args )
			);

			remove_filter( 'searchwp\native\force', '__return_true', 131 );
			remove_filter( 'searchwp\native\strict', '__return_false', 131 );
		}

		return $args;
	}

}
