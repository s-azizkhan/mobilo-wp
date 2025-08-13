<?php

namespace Barn2\Plugin\WC_Product_Table;

use Barn2\Plugin\WC_Product_Table\Util\Columns;
use Barn2\Plugin\WC_Product_Table\Util\Settings;
use Barn2\WPT_Lib\Util as Lib_Util;
use WC_Product;
use WC_Query;
use WC_Tax;
use WP_Query;
use WP_Scoped_Hooks;

/**
 * Responsible for managing the product table query, retrieving the list of products (as an array of WP_Post objects), and finding the product totals.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Table_Query {

	public $args;

	private $products                = null;
	private $total_products          = null;
	private $total_filtered_products = null;

	/**
	 * Table_Query constructor.
	 *
	 * @param Table_Args $args The table args.
	 */
	public function __construct( Table_Args $args ) {
		$this->args = $args;
	}

	/**
	 * Get the list of products for this table query.
	 *
	 * @return WC_Product[] An array of WC_Product objects.
	 */
	public function get_products() {
		if ( is_array( $this->products ) ) {
			return $this->products;
		}

		// Build query args and retrieve the products for our table.
		$query = $this->run_product_query( $this->build_product_query() );

		// Convert posts to products and store the results.
		$products = ! empty( $query->posts ) ? array_filter( array_map( 'wc_get_product', $query->posts ) ) : [];
		$this->set_products( $products );

		return $this->products;
	}

	/**
	 * Set the list of products for this table query.
	 *
	 * @param WC_Product[] An array of WC_Product objects.
	 */
	public function set_products( $products ) {
		if ( is_object( $products ) && isset( $products['products'] ) ) {
			// Support for wc_get_products function
			$products = $products['products'];
		} elseif ( ! is_array( $products ) ) {
			$products = null;
		}
		$this->products = $products;
	}

	/**
	 * Get the number of filtered products in this query.
	 *
	 * Will be the same as the overall total (get_total_products) if there is no filtering applied.
	 *
	 * @return int The filtered total.
	 */
	public function get_total_filtered_products() {
		if ( is_numeric( $this->total_filtered_products ) ) {
			// If we've already calculated the filtered total.
			return $this->total_filtered_products;
		}

		if ( is_array( $this->products ) ) {
			// If we already have products, then this must be the filtered list so return count of this array.
			$filtered_total = count( $this->products );
		} else {
			// Otherwise, calculate total by running a new query.
			$filtered_total_args  = $this->add_user_search_args( $this->build_product_totals_query() );
			$filtered_total_query = $this->run_product_query( $filtered_total_args );

			$filtered_total = $filtered_total_query->post_count;
		}

		$this->total_filtered_products = $this->check_within_product_limit( $filtered_total );

		return $this->total_filtered_products;
	}

	/**
	 * Set the filtered product total.
	 *
	 * @param int The filtered product total.
	 */
	public function set_total_filtered_products( $total_filtered_products ) {
		$this->total_filtered_products = $total_filtered_products;
	}

	/**
	 * Get the number of products in this query.
	 *
	 * @return int The product total.
	 */
	public function get_total_products() {
		if ( is_numeric( $this->total_products ) ) {
			return $this->total_products;
		}

		if ( $this->args->search_term && $this->args->user_search_term ) {
			// If we have search term 'on load' and a user applied search, we set the total to match the filtered total to avoid a mismatch.
			$total = $this->get_total_filtered_products();
		} elseif ( -1 === $this->args->rows_per_page && is_array( $this->products ) ) {
			// If showing all products on a single page, the total is the count of products array.
			$total = count( $this->products );
		} else {
			$total_query = $this->run_product_query( $this->build_product_totals_query() );
			$total       = $total_query->post_count;
		}

		$this->total_products = $this->check_within_product_limit( $total );

		return $this->total_products;
	}

	/**
	 * Set the product total.
	 *
	 * @param int The product total.
	 */
	public function set_total_products( $total_products ) {
		$this->total_products = $total_products;
	}

	/**
	 * Run the product query with the specified query args.
	 *
	 * @param array $query_args The query args to pass to WP_Query.
	 * @return WP_Query The query object.
	 */
	private function run_product_query( $query_args ) {
		$query_hooks = $this->get_query_hooks();
		$query_hooks->register();

		do_action( 'wc_product_table_before_product_query', $this );

		// Run the product query.
		// At some point switch this to wc_get_products(). We can't do this yet as the price filter widget and other meta queries are not passed through.
		$query = new WP_Query( $query_args );

		$query_hooks->reset();

		do_action( 'wc_product_table_after_product_query', $this );
		return $query;
	}

	private function build_product_query() {
		$query_args = $this->add_user_search_args( $this->build_base_product_query() );

		if ( $this->args->lazy_load ) {
			$query_args['posts_per_page'] = $this->check_within_product_limit( $this->args->rows_per_page );
			$query_args['offset']         = $this->args->offset;
		} else {
			$query_args['posts_per_page'] = $this->args->product_limit;
		}

		return apply_filters( 'wc_product_table_query_args', $query_args, $this );
	}

	private function build_product_totals_query() {
		$query_args                   = $this->build_base_product_query();
		$query_args['offset']         = 0;
		$query_args['posts_per_page'] = -1;
		$query_args['fields']         = 'ids';

		return apply_filters( 'wc_product_table_query_args', $query_args, $this );
	}

	private function build_base_product_query() {
		$query_args = [
			'post_type'        => 'product',
			'post_status'      => $this->args->status,
			'tax_query'        => $this->build_tax_query(),
			'meta_query'       => $this->build_meta_query(),
			'year'             => $this->args->year,
			'monthnum'         => $this->args->month,
			'day'              => $this->args->day,
			'no_found_rows'    => true,
			'suppress_filters' => false // Ensure WC post filters run on this query
		];

		if ( $this->args->include ) {
			$query_args['post__in']            = $this->args->include;
			$query_args['ignore_sticky_posts'] = true;
		} elseif ( $this->args->exclude ) {
			$query_args['post__not_in'] = $this->args->exclude;
		}

		// We only need to apply the search term for lazy load. For standard, the table will handle the search on load.
		if ( ! empty( $this->args->search_term ) && $this->args->lazy_load ) {
			$query_args['s'] = $this->args->search_term;
		}

		if ( $this->args->user_products ) {
			$query_args['post__in'] = $this->get_user_products();
		}

		return $this->add_ordering_args( $query_args );
	}

	private function build_tax_query() {
		// First we build a custom version of the WooCommerce tax query.
		$tax_query = $this->get_woocommerce_tax_query();

		// Category handling.
		if ( $this->args->category ) {
			$tax_query[] = $this->tax_query_item( $this->args->category, 'product_cat' );
		}

		// Category excludes.
		if ( $this->args->exclude_category ) {
			$tax_query[] = $this->tax_query_item( $this->args->exclude_category, 'product_cat', 'NOT IN' );
		}

		// Tag handling.
		if ( $this->args->tag ) {
			$tax_query[] = $this->tax_query_item( $this->args->tag, 'product_tag' );
		}

		// Custom taxonomy/term handling.
		if ( $this->args->term ) {
			$term_query    = [];
			$relation      = 'OR';
			$term_taxonomy = false;

			if ( false !== strpos( $this->args->term, '+' ) ) {
				$term_array = explode( '+', $this->args->term );
				$relation   = 'AND';
			} else {
				$term_array = explode( ',', $this->args->term );
			}

			// Custom terms are in format <taxonomy>:<term slug or id> or a list using just one taxonomy, e.g. product_cat:term1,term2.
			foreach ( $term_array as $term ) {
				if ( '' === $term ) {
					continue;
				}
				// Split term around the colon and check valid
				$term_split = explode( ':', $term, 2 );

				if ( 1 === count( $term_split ) ) {
					if ( ! $term_taxonomy ) {
						continue;
					}
					$term = $term_split[0];
				} elseif ( 2 === count( $term_split ) ) {
					$term          = $term_split[1];
					$term_taxonomy = $term_split[0];
				}
				$term_query[] = $this->tax_query_item( $term, $term_taxonomy );
			}

			$term_query = $this->maybe_add_relation( $term_query, $relation );

			// If no tax query, set the whole tax query to the custom terms query, otherwise append terms as inner query.
			if ( empty( $tax_query ) ) {
				$tax_query = $term_query;
			} else {
				$tax_query[] = $term_query;
			}
		}

		return apply_filters( 'wc_product_table_tax_query', $this->maybe_add_relation( $tax_query ), $this );
	}

	/**
	 * Get the WooCommerce tax query. This is similar to WC_Query::get_tax_query but with a few alterations needed
	 * for our product tables.
	 *
	 * @return array The WooCommerce tax query.
	 */
	private function get_woocommerce_tax_query() {
		$tax_query                = [];
		$product_visibility_terms = wc_get_product_visibility_term_ids();

		$options_misc = Settings::get_setting_misc();

		// If we're hiding hidden products, exclude the relevant product visibility terms.
		if ( ! $options_misc['include_hidden'] ) {
			// TODO: is_search() and other conditionals don't work for AJAX requests (i.e. lazy load) so we need to fix this.
			$product_visibility_not_in = [ is_search() ? $product_visibility_terms['exclude-from-search'] : $product_visibility_terms['exclude-from-catalog'] ];
		}

		// Hide out of stock products based on WooCommerce setting.
		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$product_visibility_not_in[] = $product_visibility_terms['outofstock'];
		}

		// Handle 'filter by attribute' widgets.
		foreach ( WC_Query::get_layered_nav_chosen_attributes() as $taxonomy => $data ) {
			$tax_query[] = [
				'taxonomy'         => $taxonomy,
				'field'            => 'slug',
				'terms'            => $data['terms'],
				'operator'         => 'and' === $data['query_type'] ? 'AND' : 'IN',
				'include_children' => false,
			];
		}

		// Handle 'filter by rating' widget.
		// We need to access $_GET directly here as filter_input doesn't work when using lazy load.
		$rating_filter = isset( $_GET['rating_filter'] ) && '' !== $_GET['rating_filter'] ? $_GET['rating_filter'] : '';

		if ( $rating_filter ) {
			$rating_filter = array_filter( array_map( 'absint', explode( ',', wp_unslash( $rating_filter ) ) ) );
			$rating_terms  = [];

			for ( $i = 1; $i <= 5; $i++ ) {
				if ( in_array( $i, $rating_filter, true ) && isset( $product_visibility_terms[ 'rated-' . $i ] ) ) {
					$rating_terms[] = $product_visibility_terms[ 'rated-' . $i ];
				}
			}
			if ( ! empty( $rating_terms ) ) {
				$tax_query[] = [
					'taxonomy'      => 'product_visibility',
					'field'         => 'term_taxonomy_id',
					'terms'         => $rating_terms,
					'operator'      => 'IN',
					'rating_filter' => true,
				];
			}
		}

		// Exclude any hidden visibility terms (e.g. out of stock).
		if ( ! empty( $product_visibility_not_in ) ) {
			$tax_query[] = [
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => $product_visibility_not_in,
				'operator' => 'NOT IN',
			];
		}

		// Run through the same filter as in WC_Query::get_tax_query.
		return array_filter( apply_filters( 'woocommerce_product_query_tax_query', $tax_query, WC()->query ) );
	}

	/**
	 * Generate an inner array for the 'tax_query' arg in WP_Query.
	 *
	 * @param string|array $terms    The list of terms as a string.
	 * @param string       $taxonomy The taxonomy name.
	 * @param string       $operator The SQL operator: IN, NOT IN, AND, etc.
	 * @param string       $field    Add the tax query by `term_id` or `slug`. Leave empty to auto-detect correct type.
	 * @return array The completed tax query.
	 */
	private function tax_query_item( $terms, $taxonomy, $operator = 'IN', $field = '' ) {
		$and_relation = 'AND' === $operator;

		if ( ! is_array( $terms ) ) {
			// Comma-delimited list = OR, plus-delimited list = AND
			if ( false !== strpos( $terms, '+' ) ) {
				$terms        = explode( '+', $terms );
				$and_relation = true;
			} else {
				$terms = explode( ',', $terms );
			}
		}

		// If no field provided, work out whether we have term slugs or ids.
		if ( ! $field ) {
			$using_term_ids = count( $terms ) === count( array_filter( $terms, 'is_numeric' ) );
			$field          = $using_term_ids && ! $this->args->numeric_terms ? 'term_id' : 'slug';
		}

		// There's a strange bug when using 'operator' => 'AND' for individual tax queries.
		// So we need to split these into separate 'IN' arrays joined by and outer relation => 'AND'
		if ( $and_relation && count( $terms ) > 1 ) {
			$result = [ 'relation' => 'AND' ];

			foreach ( $terms as $term ) {
				$result[] = [
					'taxonomy' => $taxonomy,
					'terms'    => $term,
					'operator' => 'IN',
					'field'    => $field
				];
			}

			return $result;
		} else {
			return [
				'taxonomy' => $taxonomy,
				'terms'    => $terms,
				'operator' => $operator,
				'field'    => $field
			];
		}
	}

	private function build_meta_query() {
		// First, build the WooCommerce meta query.
		$meta_query = WC()->query->get_meta_query();

		// Are we selecting products by custom field?
		if ( $this->args->cf ) {
			$custom_field_query = [];
			$relation           = 'OR';

			// Comma-delimited = OR, plus-delimited = AND.
			if ( false !== strpos( $this->args->cf, '+' ) ) {
				$field_array = explode( '+', $this->args->cf );
				$relation    = 'AND';
			} else {
				$field_array = explode( ',', $this->args->cf );
			}

			// Custom fields are in format <field_key>:<field_value>
			foreach ( $field_array as $field ) {
				// Split custom field around the colon and check valid
				$field_split = explode( ':', $field, 2 );

				if ( 2 === count( $field_split ) ) {
					// We have a field key and value
					$field_key   = $field_split[0];
					$field_value = $field_split[1];
					$compare     = '=';

					// If selecting based on an ACF field, the field value could be stored as an array, so we use a regex
					// comparison to check within a serialized array, in addition to a standard CF check.
					if ( Lib_Util::is_acf_active() ) {
						$compare     = 'REGEXP';
						$field_value = sprintf( '^%1$s$|s:%2$u:"%1$s";', preg_quote( $field_value, '/' ), strlen( $field_value ) );
					}

					$custom_field_query[] = [
						'key'     => $field_key,
						'value'   => $field_value,
						'compare' => $compare
					];
				} elseif ( 1 === count( $field_split ) ) {
					// Field key only, so do an 'exists' check instead
					$custom_field_query[] = [
						'key'     => $field_split[0],
						'compare' => 'EXISTS'
					];
				}
			}

			if ( 0 < count( $custom_field_query ) ) {
				// If only one CF query, we can use as a top-level meta query, otherwise we need to add a relation.
				if ( 1 === count( $custom_field_query ) ) {
					$custom_field_query = reset( $custom_field_query );
				} else {
					$custom_field_query = $this->maybe_add_relation( $custom_field_query, $relation );
				}

				$meta_query['product_table'] = $custom_field_query;
			}
		} // if $this->args->cf

		if ( Columns::is_custom_field( $this->args->sort_by ) ) {
			// Sort by custom field.
			$field = Columns::get_custom_field( $this->args->sort_by );
			$type  = in_array( 'cf:' . $field, $this->args->date_columns, true ) ? 'DATE' : 'CHAR';

			$meta_query['product_table_order_clause'] = [
				'key'  => $field,
				'type' => apply_filters( 'wc_product_table_sort_by_custom_field_type', $type, $field )
			];
		} elseif ( $this->is_lazy_load_sort_by_numeric_sku() ) {
			// Sort by numeric SKU for lazy load only.
			$meta_query['product_table_order_clause'] = [
				'key'     => '_sku',
				'type'    => 'NUMERIC',
				'value'   => 0,
				'compare' => '>='
			];
		}

		return apply_filters( 'wc_product_table_meta_query', $this->maybe_add_relation( $meta_query ), $this );
	}

	private function maybe_add_relation( $query, $relation = 'AND' ) {
		if ( count( $query ) > 1 && empty( $query['relation'] ) ) {
			$query['relation'] = $relation;
		}

		return $query;
	}

	private function get_user_products() {
		// Retrieve the current user's orders
		$order_args = [
			'customer_id' => get_current_user_id(),
			'limit'       => 500,
		];

		$orders = wc_get_orders( apply_filters( 'wc_product_table_user_products_query_args', $order_args ) );

		// Loop through the orders and retrieve the product IDs
		$product_ids = [];

		foreach ( $orders as $order ) {
			$products = $order->get_items();

			foreach ( $products as $product ) {
				$product_id    = $product->get_product_id();
				$product_ids[] = $product_id;
			}

			$product_ids = array_unique( $product_ids );

			// Quit checking orders if the product limit is reached
			if ( $this->args->product_limit > 0 && count( $product_ids ) >= $this->args->product_limit ) {
				break;
			}
		}

		// Prevent all products from being displayed if no user products
		if ( empty( $product_ids ) ) {
			$product_ids = [ 0 ];
		}

		return $product_ids;
	}

	/**
	 * Add the ordering args for our product query.
	 *
	 * For standard loading, DataTables will re-sort the results if the sort column is present in table.
	 *
	 * @param array $query_args The query args.
	 * @return array The updated query args.
	 */
	private function add_ordering_args( $query_args ) {
		$order   = strtoupper( $this->args->sort_order );
		$orderby = $this->args->sort_by;

		if ( ! empty( $query_args['meta_query']['product_table_order_clause'] ) ) {
			// Use named order clause if we have one.
			$query_args['orderby'] = 'product_table_order_clause';
			$query_args['order']   = $order;
		} else {
			// Replace column name with correct sort_by item used by WP_Query.
			if ( in_array( $orderby, [ 'name', 'reviews', 'date_modified' ], true ) ) {
				$orderby = str_replace( [ 'name', 'reviews', 'date_modified' ], [ 'title', 'rating', 'modified' ], $orderby );
			}

			// Bail if we don't have a valid WC orderby arg.
			// Note: custom field and SKU sorting are handled separately.
			if ( ! in_array( $orderby, [ 'id', 'title', 'menu_order', 'rand', 'relevance', 'price', 'popularity', 'rating', 'date', 'modified' ], true ) ) {
				return $query_args;
			}

			// Use WC to get standard ordering args and add extra query filters.
			$wc_ordering = WC()->query->get_catalog_ordering_args( $orderby, $order );

			add_action(
				'wc_product_table_after_product_query',
				function () {
					// The call to WC()->query->get_catalogue_ordering_args() adds various filters to WP_Query.
					// These can interfere with any subsequent queries while building table data, so we need to remove them.
					WC()->query->remove_ordering_args();
				}
			);

			if ( empty( $wc_ordering['meta_key'] ) ) {
				unset( $wc_ordering['meta_key'] );
			}

			// Additional orderby options.
			if ( 'modified' === $orderby ) {
				$wc_ordering['orderby'] = 'modified ID';
			}

			$query_args = array_merge( $query_args, $wc_ordering );
		}

		return $query_args;
	}

	private function add_user_search_args( array $query_args ) {
		if ( ! empty( $this->args->search_filters ) ) {
			$tax_query            = $query_args['tax_query'];
			$search_filters_query = [];

			// Add tax queries for search filter drop-downs.
			foreach ( $this->args->search_filters as $taxonomy => $term ) {
				// Search filters always use term IDs
				$search_filters_query[] = $this->tax_query_item( $term, $taxonomy, 'IN', 'term_id' );
			}

			$search_filters_query = $this->maybe_add_relation( $search_filters_query );

			// If no tax query, set the whole tax query to the filters query, otherwise append filters as inner query
			if ( empty( $tax_query ) ) {
				// If no tax query, set the whole tax query to the filters query.
				$tax_query = $search_filters_query;
			} elseif ( isset( $tax_query['relation'] ) && 'OR' === $tax_query['relation'] ) {
				// If tax query is an OR, nest it with the search filters query and join with AND.
				$tax_query = [
					$tax_query,
					$search_filters_query,
					'relation' => 'AND'
				];
			} else {
				// Otherwise append search filters and ensure it's AND.
				$tax_query[]           = $search_filters_query;
				$tax_query['relation'] = 'AND';
			}

			$query_args['tax_query'] = $tax_query;
		}

		if ( ! empty( $this->args->user_search_term ) ) {
			$query_args['s'] = $this->args->user_search_term;
		}

		return $query_args;
	}

	private function check_within_product_limit( $count ) {
		return is_int( $this->args->product_limit ) && $this->args->product_limit > 0 ? min( $this->args->product_limit, $count ) : $count;
	}

	private function get_query_hooks() {
		$hooks = new WP_Scoped_Hooks();

		// WP_Query optimisation.
		if ( apply_filters( 'wc_product_table_optimize_table_query', true, $this->args ) ) {
			$hooks->add_filter( 'posts_fields', [ $this, 'filter_wp_posts_selected_columns' ] );
		}

		// Search by SKU for lazy load.
		if ( $this->is_lazy_load_search_by_sku() ) {
			$hooks->add_filter( 'posts_search', [ $this, 'search_by_sku_post_search' ] );
			$hooks->add_filter( 'posts_clauses', [ $this, 'search_by_sku_post_clauses' ] );
		}

		// Sort by SKU. For lazy load numeric sort by SKU (eek!) we use a custom meta query - see build_meta_query.
		if ( 'sku' === $this->args->sort_by && ! $this->is_lazy_load_sort_by_numeric_sku() ) {
			if ( 'desc' === $this->args->sort_order ) {
				$hooks->add_filter( 'posts_clauses', [ $this, 'order_by_sku_desc_post_clauses' ] );
			} else {
				$hooks->add_filter( 'posts_clauses', [ $this, 'order_by_sku_asc_post_clauses' ] );
			}
		}

		// Price filter widget.
		$hooks->add_filter( 'posts_clauses', [ $this, 'filter_by_price_post_clauses' ] );

		return $hooks;
	}

	/**
	 * Removes unnecessary columns from the table query if we're not displaying the product description or short description.
	 *
	 * @param string $fields The posts table column selection.
	 * @return string The updated column selection.
	 */
	public function filter_wp_posts_selected_columns( $fields ) {
		global $wpdb;

		if ( "{$wpdb->posts}.*" !== $fields ) {
			return $fields;
		}

		if ( array_diff( [ 'description', 'summary' ], $this->args->columns ) ) {
			$posts_columns = [
				'ID',
				'post_author',
				'post_date',
				'post_date_gmt',
				'post_title',
				'post_status',
				'comment_status',
				'ping_status',
				'post_password',
				'post_name',
				'to_ping',
				'pinged',
				'post_modified',
				'post_modified_gmt',
				'post_content_filtered',
				'post_parent',
				'guid',
				'menu_order',
				'post_type',
				'post_mime_type',
				'comment_count'
			];

			if ( in_array( 'description', $this->args->columns ) ) {
				$posts_columns[] = 'post_content';
			}
			if ( in_array( 'summary', $this->args->columns ) ) {
				$posts_columns[] = 'post_excerpt';
				// We need the content as well, in case we need to auto-generate the excerpt from the content
				$posts_columns[] = 'post_content';
			}

			$fields = sprintf( implode( ', ', array_map( [ $this, 'array_map_prefix_column' ], $posts_columns ) ), $wpdb->posts );
		}

		return $fields;
	}

	/**
	 * Custom query to order by SKU (asc). Attached to 'posts_clauses' hook.
	 *
	 * Based on WC_Admin_List_Table_Products::order_by_sku_asc_post_clauses.
	 *
	 * @param array $args The post clauses args.
	 * @return array The filtered args.
	 */
	public function order_by_sku_asc_post_clauses( $args ) {
		$args['join']    = $this->append_product_sorting_table_join( $args['join'] );
		$args['orderby'] = ' wc_product_meta_lookup.sku ASC, wc_product_meta_lookup.product_id ASC ';
		return $args;
	}

	/**
	 * Custom query to order by SKU (desc). Attached to 'posts_clauses' hook.
	 *
	 * Based on WC_Admin_List_Table_Products::order_by_sku_desc_post_clauses.
	 *
	 * @param array $args The post clauses args.
	 * @return array The filtered args.
	 */
	public function order_by_sku_desc_post_clauses( $args ) {
		$args['join']    = $this->append_product_sorting_table_join( $args['join'] );
		$args['orderby'] = ' wc_product_meta_lookup.sku DESC, wc_product_meta_lookup.product_id DESC ';
		return $args;
	}

	/**
	 * Custom query to filter by price when using the 'Filter by price' widget. Attached to 'posts_clauses' hook.
	 *
	 * This is basically a copy of WC_Query::price_filter_post_clauses but without the check for is_main_query.
	 *
	 * @param array $args The post clauses args.
	 * @return array The filtered args.
	 */
	public function filter_by_price_post_clauses( $args ) {
		global $wpdb;

		if ( ! isset( $_GET['max_price'] ) && ! isset( $_GET['min_price'] ) ) {
			return $args;
		}

		$current_min_price = isset( $_GET['min_price'] ) ? floatval( wp_unslash( $_GET['min_price'] ) ) : 0; // WPCS: input var ok, CSRF ok.
		$current_max_price = isset( $_GET['max_price'] ) ? floatval( wp_unslash( $_GET['max_price'] ) ) : PHP_INT_MAX; // WPCS: input var ok, CSRF ok.

		/**
		 * Adjust if the store taxes are not displayed how they are stored.
		 * Kicks in when prices excluding tax are displayed including tax.
		 */
		if ( wc_tax_enabled() && 'incl' === get_option( 'woocommerce_tax_display_shop' ) && ! wc_prices_include_tax() ) {
			$tax_class = apply_filters( 'woocommerce_price_filter_widget_tax_class', '' ); // Uses standard tax class.
			$tax_rates = WC_Tax::get_rates( $tax_class );

			if ( $tax_rates ) {
				$current_min_price -= WC_Tax::get_tax_total( WC_Tax::calc_inclusive_tax( $current_min_price, $tax_rates ) );
				$current_max_price -= WC_Tax::get_tax_total( WC_Tax::calc_inclusive_tax( $current_max_price, $tax_rates ) );
			}
		}

		$args['join']  = $this->append_product_sorting_table_join( $args['join'] );
		$args['where'] .= $wpdb->prepare(
			' AND wc_product_meta_lookup.min_price >= %f AND wc_product_meta_lookup.max_price <= %f ',
			$current_min_price,
			$current_max_price
		);

		return $args;
	}

	/**
	 * Custom query to search by SKU. Attached to 'posts_search' hook.
	 *
	 * @param string $search The posts search SQL query.
	 * @return string The filtered SQL.
	 */
	public function search_by_sku_post_search( $search ) {
		global $wpdb;

		if ( empty( $this->args->search_term ) && empty( $this->args->user_search_term ) ) {
			return $search;
		}

		// A user search (i.e. via the search box) takes precedence over search term used on load.
		$search_term = ! empty( $this->args->user_search_term ) ? $this->args->user_search_term : $this->args->search_term;

		// Build SKU where clause.
		$sku_like  = '%' . $wpdb->esc_like( $search_term ) . '%';
		$sku_like  = $wpdb->prepare( '%s', $sku_like );
		$sku_where = "( wpt1.meta_key = '_sku' AND wpt1.meta_value LIKE $sku_like )";

		// Perform a match on the search SQL so we can inject our SKU meta query into it.
		$matches = [];

		if ( preg_match( "/^ AND \((.+)\) ( AND \({$wpdb->posts}.post_password = ''\) )?$/U", $search, $matches ) ) {
			$search = ' AND (' . $sku_where . ' OR (' . $matches[1] . ')) ';

			// Add the post_password = '' clause if found.
			if ( isset( $matches[2] ) ) {
				$search .= $matches[2];
			}
		}

		return $search;
	}

	/**
	 * Custom query to search by SKU. Attached to 'posts_clauses' hook.
	 *
	 * @param array $args The post clauses args.
	 * @return array The filtered args.
	 */
	public function search_by_sku_post_clauses( $args ) {
		global $wpdb;

		// Add the meta query groupby clause.
		if ( empty( $args['groupby'] ) ) {
			$args['groupby'] = "{$wpdb->posts}.ID";
		}

		// Add our meta query join. We always need to do a separate join as other post meta joins may be present.
		$args['join'] .= " INNER JOIN {$wpdb->postmeta} AS wpt1 ON ( {$wpdb->posts}.ID = wpt1.post_id )";
		return $args;
	}

	/**
	 * Join wc_product_meta_lookup to posts table if not already joined.
	 *
	 * @param string $sql SQL join.
	 * @return string The updated SQL join.
	 */
	private function append_product_sorting_table_join( $sql ) {
		global $wpdb;

		if ( ! strstr( $sql, 'wc_product_meta_lookup' ) ) {
			$sql .= " LEFT JOIN {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup ON $wpdb->posts.ID = wc_product_meta_lookup.product_id ";
		}
		return $sql;
	}

	/**
	 * Are we searching by SKU in a lazy loaded table?
	 *
	 * @return bool true if SKU search + lazy load.
	 */
	private function is_lazy_load_search_by_sku() {
		return apply_filters( 'wc_product_table_enable_lazy_load_sku_search', true ) && $this->args->lazy_load && ( $this->args->search_term || $this->args->user_search_term );
	}

	/**
	 * Are we sorting by numeric SKU in a lazy loaded table?
	 *
	 * @return bool true if SKU sort + SKUs are numeric + lazy load.
	 */
	private function is_lazy_load_sort_by_numeric_sku() {
		return apply_filters( 'wc_product_table_use_numeric_skus', false ) && $this->args->lazy_load && ( 'sku' === $this->args->sort_by );
	}

	private function array_map_prefix_column( $n ) {
		return '%1$s.' . $n;
	}

}
