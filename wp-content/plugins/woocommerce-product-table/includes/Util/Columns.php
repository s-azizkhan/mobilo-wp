<?php

namespace Barn2\Plugin\WC_Product_Table\Util;

/**
 * Utility functions for the product table columns.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Columns {

	/**
	 * Some column replacements used for correcting misspelled columns.
	 *
	 * @var array $column_replacements
	 */
	public static $column_replacements = [
		'ID'                => 'id',
		'SKU'               => 'sku',
		'title'             => 'name',
		'content'           => 'description',
		'excerpt'           => 'summary',
		'short-description' => 'summary', // back compat: old column name
		'category'          => 'categories',
		'rating'            => 'reviews',
		'add-to-cart'       => 'buy', // back compat: old column name
		'modified'          => 'date_modified'
	];

	/**
	 * Global column defaults.
	 *
	 * @var array
	 */
	private static $column_defaults = false;

	/**
	 * Get the default column headings and responsive priorities.
	 *
	 * @return array The column defaults
	 */
	public static function column_defaults() {

		if ( empty( self::$column_defaults ) ) {
			// Priority values are used to determine visiblity at small screen sizes (1 = highest priority).
			self::$column_defaults = apply_filters(
				'wc_product_table_column_defaults',
				[
					'id'            => [
						'heading'  => __( 'ID', 'woocommerce-product-table' ),
						'priority' => 8
					],
					'sku'           => [
						'heading'  => __( 'SKU', 'woocommerce-product-table' ),
						'priority' => 6
					],
					'name'          => [
						'heading'  => __( 'Name', 'woocommerce-product-table' ),
						'priority' => 1
					],
					'description'   => [
						'heading'  => __( 'Description', 'woocommerce-product-table' ),
						'priority' => 12
					],
					'summary'       => [
						'heading'  => __( 'Summary', 'woocommerce-product-table' ),
						'priority' => 11
					],
					'date'          => [
						'heading'  => __( 'Date', 'woocommerce-product-table' ),
						'priority' => 14
					],
					'date_modified' => [
						'heading'  => __( 'Updated', 'woocommerce-product-table' ),
						'priority' => 15
					],
					'categories'    => [
						'heading'  => __( 'Categories', 'woocommerce-product-table' ),
						'priority' => 9
					],
					'tags'          => [
						'heading'  => __( 'Tags', 'woocommerce-product-table' ),
						'priority' => 10
					],
					'image'         => [
						'heading'  => __( 'Image', 'woocommerce-product-table' ),
						'priority' => 4
					],
					'stock'         => [
						'heading'  => __( 'Stock', 'woocommerce-product-table' ),
						'priority' => 7
					],
					'reviews'       => [
						'heading'  => __( 'Reviews', 'woocommerce-product-table' ),
						'priority' => 13
					],
					'weight'        => [
						'heading'  => __( 'Weight', 'woocommerce-product-table' ),
						'priority' => 16
					],
					'dimensions'    => [
						'heading'  => __( 'Dimensions', 'woocommerce-product-table' ),
						'priority' => 17
					],
					'price'         => [
						'heading'  => __( 'Price', 'woocommerce-product-table' ),
						'priority' => 3
					],
					'buy'           => [
						'heading'  => __( 'Buy', 'woocommerce-product-table' ),
						'priority' => 2
					],
					'button'        => [
						'heading'  => __( 'Details', 'woocommerce-product-table' ),
						'priority' => 5
					]
				]
			);
		}

		return self::$column_defaults;
	}

	/**
	 * If the heading equals the keyword 'blank', returns an empty string.
	 *
	 * @param string $heading
	 * @return string
	 */
	public static function check_blank_heading( $heading ) {
		return 'blank' === $heading ? '' : $heading;
	}

	/**
	 * Get the taxonomy for the specified column name.
	 *
	 * @param string $column
	 * @return false|string
	 */
	public static function get_column_taxonomy( $column ) {
		if ( 'categories' === $column ) {
			return 'product_cat';
		} elseif ( 'tags' === $column ) {
			return 'product_tag';
		} elseif ( $att = self::get_product_attribute( $column ) ) {
			if ( taxonomy_is_product_attribute( $att ) ) {
				return $att;
			}
		} elseif ( $tax = self::get_custom_taxonomy( $column ) ) {
			return $tax;
		}
		return false;
	}

	/**
	 * Is the column a custom field?
	 *
	 * @param string $column
	 * @return bool
	 */
	public static function is_custom_field( $column ) {
		return $column && 'cf:' === substr( $column, 0, 3 ) && strlen( $column ) > 3;
	}

	/**
	 * Get the custom field from the column name - so 'cf:thing' becomes 'thing'. Returns false if not a custom field column.
	 *
	 * @param string $column
	 * @return false|string
	 */
	public static function get_custom_field( $column ) {
		if ( self::is_custom_field( $column ) ) {
			return substr( $column, 3 );
		}
		return false;
	}

	/**
	 * Is the column a custom taxonomy?
	 *
	 * @param string $column
	 * @return bool
	 */
	public static function is_custom_taxonomy( $column ) {
		$is_tax = $column && 'tax:' === substr( $column, 0, 4 ) && strlen( $column ) > 4;
		return $is_tax && taxonomy_exists( substr( $column, 4 ) );
	}

	/**
	 * Get the product attribute from the column name - so 'att:colour' becomes 'colour'. Returns false if not an attribute column.
	 *
	 * @param string $column
	 * @return false|string
	 */
	public static function get_custom_taxonomy( $column ) {
		if ( self::is_custom_taxonomy( $column ) ) {
			return substr( $column, 4 );
		}
		return false;
	}

	/**
	 * Is the column a hidden filter column?
	 *
	 * @param string $column
	 * @return bool
	 */
	public static function is_hidden_filter_column( $column ) {
		return $column && 'hf:' === substr( $column, 0, 3 ) && strlen( $column ) > 3;
	}

	/**
	 * Get the hidden filter from the column name - so 'hf:colour' becomes 'colour'. Returns false if not a hidden filter column.
	 *
	 * @param string $column
	 * @return false|string
	 */
	public static function get_hidden_filter_column( $column ) {
		if ( self::is_hidden_filter_column( $column ) ) {
			return substr( $column, 3 );
		}
		return false;
	}

	/**
	 * Is the column a product attribute?
	 *
	 * @param string $column
	 * @return bool
	 */
	public static function is_product_attribute( $column ) {
		return $column && 'att:' === substr( $column, 0, 4 );
	}

	/**
	 * Get the product attribute from the column name - so 'att:colour' becomes 'colour'. Returns false if not an attribute column.
	 *
	 * @param string $column
	 * @return false|string
	 */
	public static function get_product_attribute( $column ) {
		if ( self::is_product_attribute( $column ) ) {
			return substr( $column, 4 );
		}
		return false;
	}

	/**
	 * Parse the supplied columns into an array, whose keys are the column names, and values are the column headings (if specified).
	 *
	 * Invalid taxonomies are removed, but non-standard columns are kept as they could be custom columns. Custom field keys are not validated.
	 *
	 * E.g. parse_columns( 'name,summary,price:Cost,tax:product_region:Region,cf:my_field,buy:Order' );
	 *
	 * Returns:
	 *
	 * [ 'name' => '', 'summary' => '', 'price' => 'Cost', 'tax:product_region' => 'Region', 'cf:my_field' => '', 'buy' => 'Order' ];
	 *
	 * @param string|string[] $columns The columns to parse as a string or array of strings.
	 * @return array The parsed columns.
	 */
	public static function parse_columns( $columns ) {
		if ( ! is_array( $columns ) ) {
			$columns = Util::string_list_to_array( $columns );
		}

		$parsed = [];

		foreach ( $columns as $column ) {
			$prefix = sanitize_key( strtok( $column, ':' ) );
			$col    = false;

			if ( in_array( $prefix, [ 'cf', 'att', 'tax' ], true ) ) {
				// Custom field, product attribute or taxonomy.
				$suffix = trim( strtok( ':' ) );

				if ( ! $suffix ) {
					continue; // no custom field, attribute, or taxonomy specified
				} elseif ( 'att' === $prefix ) {
					$suffix = Util::get_attribute_name( $suffix );
				} elseif ( 'tax' === $prefix && ! taxonomy_exists( $suffix ) ) {
					continue; // invalid taxonomy
				}

				$col = $prefix . ':' . $suffix;
			} else {
				// Standard or custom column.
				$col = $prefix;

				// Check for common typos in column names.
				if ( array_key_exists( $col, self::$column_replacements ) ) {
					$col = self::$column_replacements[ $col ];
				}
			}

			// Only add column if valid and not added already.
			if ( $col && ! array_key_exists( $col, $parsed ) ) {
				$parsed[ $col ] = self::sanitize_heading( strtok( '' ) );
			}
		}

		return $parsed;
	}

	/**
	 * Parse the supplied filters into an array, whose keys are the filter names, and values are the filter headings (if specified).
	 *
	 * Invalid filter columns and taxonomies are removed. When $filters = true, the filters are based on the table contents and this
	 * is specified by passing the columns in the $table_columns arg.
	 *
	 * $filters supports the keyword 'attributes' on its own, or alongside other standard filter columns. When present 'attributes' will be
	 * replaced by all global attributes and is there a shorthand way to specify all store attributes without listing them individually.
	 *
	 * E.g. parse_filters( 'categories:Category,invalid,tags,tax:product_region:Region' );
	 *
	 * Returns:
	 *
	 * [ 'categories' => 'Category', 'tags' => '', 'tax:product_region' => 'Region' ];
	 *
	 * @param bool|string|string[] $filters       The filters to parse as a bool, string or array of strings.
	 * @param string[]             $table_columns The columns to base the filters on when $filters = true.
	 * @return array The parsed filters, or an empty array if the filters are invalid.
	 */
	public static function parse_filters( $filters, array $table_columns = [] ) {
		$parsed         = [];
		$filter_columns = Util::maybe_parse_bool( $filters );

		if ( true === $filter_columns ) {
			// If filters is true, set filters based on table columns.
			$filter_columns = $table_columns;
		} elseif ( empty( $filter_columns ) ) {
			$filter_columns = [];
		}

		if ( ! is_array( $filter_columns ) ) {
			$filter_columns = Util::string_list_to_array( $filter_columns );
		}

		// If the 'attributes' keyword is specified, replace it with all attribute taxonomies.
		if ( false !== ( $attributes_index = array_search( 'attributes', $filter_columns, true ) ) ) {
			// 'attributes' keyword found - replace with all global product attributes.
			$attribute_filters = preg_replace( '/^/', 'att:', wc_get_attribute_taxonomy_names() );
			$before            = array_slice( $filter_columns, 0, $attributes_index );
			$after             = array_slice( $filter_columns, $attributes_index + 1 );
			$filter_columns    = array_merge( $before, $attribute_filters, $after );
		}

		foreach ( $filter_columns as $filter ) {
			$f                  = false;
			$prefix             = strtok( $filter, ':' );
			$filterable_columns = apply_filters( 'wc_product_table_standard_filterable_columns', [ 'categories', 'tags' ] );

			if ( in_array( $prefix, $filterable_columns, true ) ) {
				// Categories or tags filter.
				$f = $prefix;
			} elseif ( 'tax' === $prefix ) {
				// Custom taxonomy filter.
				$taxonomy = trim( strtok( ':' ) );

				if ( taxonomy_exists( $taxonomy ) ) {
					$f = 'tax:' . $taxonomy;
				}
			} elseif ( 'att' === $prefix ) {
				// Attribute filter.
				$attribute = Util::get_attribute_name( trim( strtok( ':' ) ) );

				// Only global attributes (i.e. taxonomies) are allowed as a filter
				if ( taxonomy_is_product_attribute( $attribute ) ) {
					$f = 'att:' . $attribute;
				}
			}

			if ( $f && ! array_key_exists( $f, $parsed ) ) {
				$parsed[ $f ] = self::sanitize_heading( strtok( '' ) );
			}
		}

		return $parsed;
	}

	/**
	 * Converts an array of columns in the form [ column => heading ] to a comma-separated string.
	 *
	 * E.g.
	 * parsed_columns_to_string( [ 'name' => 'Title', 'price' => 'Cost per unit', 'stock' => '', 'buy' => '' );
	 * Returns: 'name:Title,price:Cost per unit,stock,buy'
	 *
	 * @param array $columns The columns and headings array.
	 * @return string The columns combined to a string.
	 */
	public static function parsed_columns_to_string( array $columns ) {
		if ( empty( $columns ) ) {
			return '';
		}

		$columns_combined = [];

		foreach ( $columns as $column => $heading ) {
			$columns_combined[] = $heading ? $column . ':' . $heading : $column;
		}

		return implode( ',', $columns_combined );
	}

	/**
	 * Sanitizes a column heading.
	 *
	 * @param string $heading
	 * @return string
	 */
	public static function sanitize_heading( $heading ) {
		return esc_html( $heading );
	}

	/**
	 * Unprefix a column, removing the 'cf:', 'att:', or 'tax:' prefix from the column name.
	 *
	 * @param string $column
	 * @return string
	 */
	public static function unprefix_column( $column ) {
		if ( false !== ( $str = strstr( $column, ':' ) ) ) {
			$column = substr( $str, 1 );
		}
		return $column;
	}

	/**
	 * Get the CSS class for a column - will return 'col-<column>' where <column> is the unprefixed column name.
	 *
	 * @param string $column
	 * @return string
	 */
	public static function get_column_class( $column ) {
		$column_class_suffix = self::unprefix_column( $column );

		// Certain classes are reserved for use by DataTables Responsive, so we need to strip these to prevent conflicts.
		$column_class_suffix = trim( str_replace( [ 'mobile', 'tablet', 'desktop' ], '', $column_class_suffix ), '_- ' );

		return $column_class_suffix ? sanitize_html_class( 'col-' . $column_class_suffix ) : '';
	}

	/**
	 * Get the data source value to use in the internal DataTables data.
	 *
	 * @param string $column
	 * @return string
	 */
	public static function get_column_data_source( $column ) {
		// '.' not allowed in data source
		return str_replace( '.', '', $column );
	}

	/**
	 * Get the column name to use in the 'data-name' value used by DataTables.
	 *
	 * @param string $column
	 * @return string
	 */
	public static function get_column_name( $column ) {
		// ':' not allowed in column name as not compatible with DataTables API.
		return str_replace( ':', '_', $column );
	}

}
