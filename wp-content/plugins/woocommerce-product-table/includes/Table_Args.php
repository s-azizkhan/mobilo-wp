<?php

namespace Barn2\Plugin\WC_Product_Table;

use Barn2\Plugin\WC_Product_Table\Util\Columns;
use Barn2\Plugin\WC_Product_Table\Util\Defaults;
use Barn2\Plugin\WC_Product_Table\Util\Settings;
use Barn2\Plugin\WC_Product_Table\Util\Util;

/**
 * The Table_Args class is responsible for storing and validating the product table arguments.
 * It parses an array of args into their corresponding properties.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Table_Args {

	/**
	 * The args array.
	 *
	 * @var array
	 */
	private $args = [];

	// Table params.
	public $columns;
	public $headings;
	public $widths;
	public $auto_width;
	public $priorities;
	public $column_breakpoints;
	public $responsive_control;
	public $responsive_display;
	public $wrap;
	public $show_footer;
	public $search_on_click;
	public $filters;
	public $filter_headings;
	public $quantities;
	public $variations;
	public $cart_button;
	public $ajax_cart;
	public $scroll_offset;
	public $description_length;
	public $links;
	public $lazy_load;
	public $cache;
	public $image_size;
	public $lightbox;
	public $shortcodes;
	public $button_text;
	public $date_format;
	public $date_columns;
	public $no_products_message;
	public $no_products_filtered_message;
	public $paging_type;
	public $page_length;
	public $search_box;
	public $totals;
	public $pagination;
	public $reset_button;
	public $add_selected_button;
	public $user_products;

	// Query params.
	public $rows_per_page;
	public $product_limit;
	public $sort_by;
	public $sort_order;
	public $status;
	public $category;
	public $exclude_category;
	public $tag;
	public $term;
	public $numeric_terms;
	public $cf;
	public $year;
	public $month;
	public $day;
	public $exclude;
	public $include;
	public $search_term;

	// Lazy load params.
	public $offset         = 0;
	public $user_search_term;
	public $search_filters = [];

	// Internal params.
	public $show_hidden_columns;

	/**
	 * The full list of supported table options and their default values.
	 *
	 * @var array The default args.
	 * @depreated 3.0.2 Replaced by Use Defaults::get_table_defaults(). This will be removed in a future update.
	 */
	public static $default_args = [
		'columns'                      => 'image,name,summary,price,buy',
		'widths'                       => '',
		'auto_width'                   => true,
		'priorities'                   => '',
		'column_breakpoints'           => '',
		'responsive_control'           => 'inline',
		'responsive_display'           => 'child_row',
		'wrap'                         => true,
		'show_footer'                  => false,
		'search_on_click'              => true,
		'filters'                      => true,
		'quantities'                   => false,
		'variations'                   => false,
		'cart_button'                  => 'button',
		'ajax_cart'                    => true,
		'scroll_offset'                => 15,
		'description_length'           => 15,
		'links'                        => 'all',
		'lazy_load'                    => false,
		'cache'                        => false,
		'image_size'                   => '70x70',
		'lightbox'                     => true,
		'shortcodes'                   => false,
		'button_text'                  => '',
		'date_format'                  => '',
		'date_columns'                 => '',
		'no_products_message'          => '',
		'no_products_filtered_message' => '',
		'paging_type'                  => 'numbers',
		'page_length'                  => 'bottom',
		'search_box'                   => 'top',
		'totals'                       => 'bottom',
		'pagination'                   => 'bottom',
		'reset_button'                 => true,
		'add_selected_button'          => 'top',
		'user_products'                => false,
		'rows_per_page'                => 25,
		'product_limit'                => 500,
		'sort_by'                      => 'menu_order',
		'sort_order'                   => '',
		'status'                       => 'publish',
		'category'                     => '',
		'exclude_category'             => '',
		'tag'                          => '',
		'term'                         => '',
		'numeric_terms'                => false,
		'cf'                           => '',
		'year'                         => '',
		'month'                        => '',
		'day'                          => '',
		'exclude'                      => '',
		'include'                      => '',
		'search_term'                  => '',
		'show_hidden_columns'          => false
	];

	/**
	 * The site-wide table options after merging with the plugin settings.
	 *
	 * @var array $site_defaults
	 */
	private static $site_defaults = null;

	/**
	 * Constructs a new table args object based on the supplied args. Args are validated and any missing args are
	 * merged in, and stored in the $args property.
	 *
	 * For any args that can accept a list of items (e.g. $columns, $category, etc) the list should be comma-separated.
	 *
	 * @param array          $args               {
	 * @type string|string[] $columns            The columns for the table. Any combination of: id, sku, name, description,
	 *                                           summary, date, date_modified, categories, tags, image, reviews, stock,
	 *                                           weight, dimensions, price, buy, button, as well as attributes (att:pa_size),
	 *                                           taxonomies (tax:product_vendor) or custom fields (cf:my_field).
	 * @type string          $widths             The column widths. A comma-separate list (one for each column) with an
	 *                                           integer, pixels, percentage or the word 'auto' supplied for each.
	 *                                           E.g. 20px,30,auto,20. An integer is treated as a percentage, so 30 is 30%.
	 * @type string          $responsive_control The responsive control type for child rows: inline or column.
	 * @type string          $responsive_display The responsive display type: child_row, child_row_visible, or modal.
	 * @type string          $cart_button        The cart button type: button, button_checkbox, or checkbox.
	 * @type int             $description_length The description length (the number of words).
	 * @type string          $Links              The table links: all, none, or any combination of id, sku, name, image, tags, categories, terms, attributes.
	 * @type string          $category           The category or categories to display products from. Accepts a list of slugs or IDs.
	 * @type string          $exclude_category   Exclude categories from the table. Accepts a list of slugs or IDs.
	 * @type string          $tag                The tag(s) to display products from. Accepts a list of slugs or IDs.
	 * @type string          $term               The term(s) to display products from. Accepts a list of terms in the form <taxonomy>:<term>.
	 * @type bool            $numeric_terms      Set to true if using categories, tags or terms with numeric slugs.
	 * @type string          $cf                 The custom field(s) to display products from. Accepts a list of fields in the form <field_key>:<field_value>.
	 * @type int             $year               The year to select products from. Use a 4-digit year, e.g. 2011.
	 * @type int             $month              The month to select products from. Use a 2-digit month, e.g. 12.
	 * @type int             $day                The day to select products from. Use a 2-digit day, e.g. 03.
	 * @type string          $exclude            The list of products to exclude, as a list of product IDs.
	 * @type string          $include            The list of products to include, as a list of product IDs. If specified, other selection parameters are ignored.
	 *                                           }
	 */
	public function __construct( array $args ) {
		$this->set_args( $args );
	}

	/**
	 * Magic __get method.
	 *
	 * @param string $name The property to get.
	 * @return mixed
	 */
	public function __get( $name ) {
		if ( 'show_quantity' === $name ) {
			// Back-compat: old property name.
			return $this->quantities;
		}

		return null;
	}

	/**
	 * Get the table args.
	 *
	 * @return array The table args.
	 */
	public function get_args() {
		return $this->args;
	}

	/**
	 * Is this table using the multi add to cart feature?
	 *
	 * @return bool true if multi-cart is enabled.
	 */
	public function is_multi_add_to_cart() {
		return in_array( $this->cart_button, [ 'checkbox', 'button_checkbox' ], true ) && in_array( 'buy', $this->columns, true );
	}

	/**
	 * Set the table args. Not all args need to be supplied - new args will be merged in to the existing, and only
	 * those specified in the new args will be overwritten.
	 *
	 * @param array $args The new table args.
	 * @return void
	 */
	public function set_args( array $args ) {
		// Check for old arg names.
		$args = self::back_compat_args( $args );

		// Lazy load args need to be merged in.
		$lazy_load_args = [
			'offset'           => $this->offset,
			'user_search_term' => $this->user_search_term,
			'search_filters'   => $this->search_filters
		];

		// Update by merging new args into previous args.
		$this->args = array_merge( $lazy_load_args, $this->args, $args );

		// Parse/validate args & update properties.
		$this->parse_args( $this->args );
	}

	private function array_filter_custom_field_or_taxonomy( $column ) {
		return Columns::is_custom_field( $column ) || Columns::is_custom_taxonomy( $column );
	}

	private function array_filter_validate_boolean( $var ) {
		return $var === FILTER_VALIDATE_BOOLEAN;
	}

	private function parse_args( array $args ) {
		$defaults = self::get_defaults();

		// Merge in defaults so we know all args have been set.
		$args = wp_parse_args( $args, $defaults );

		// Convert any array args to a comma-separated string prior to validation and processing, to ensure we have
		// consistent options to work with.
		foreach (
			[
				'columns',
				'widths',
				'priorities',
				'column_breakpoints',
				'filters',
				'links',
				'image_size',
				'date_columns',
				'status',
				'category',
				'exclude_category',
				'tag',
				'term',
				'cf',
				'exclude',
				'include'
			] as $arg
		) {
			if ( is_array( $args[ $arg ] ) ) {
				$args[ $arg ] = implode( ',', $args[ $arg ] );
			}
		}

		// Define custom validation callbacks.
		$sanitize_list = [
			'filter'  => FILTER_CALLBACK,
			'options' => [ Util::class, 'sanitize_list' ]
		];

		$sanitize_numeric_list = [
			'filter'  => FILTER_CALLBACK,
			'options' => [ Util::class, 'sanitize_numeric_list' ]
		];

		$sanitize_enum = [
			'filter'  => FILTER_CALLBACK,
			'options' => [ Util::class, 'sanitize_enum' ]
		];

		$sanitize_enum_or_bool = [
			'filter'  => FILTER_CALLBACK,
			'options' => [ Util::class, 'sanitize_enum_or_bool' ]
		];

		$sanitize_search_term = [
			'filter'  => FILTER_CALLBACK,
			'options' => [ Util::class, 'sanitize_search_term' ]
		];

		// Setup validation array.
		$validation = [
			'columns'                      => FILTER_DEFAULT,
			'widths'                       => $sanitize_list,
			'auto_width'                   => FILTER_VALIDATE_BOOLEAN,
			'priorities'                   => $sanitize_numeric_list,
			'column_breakpoints'           => $sanitize_list,
			'responsive_control'           => $sanitize_enum,
			'responsive_display'           => $sanitize_enum,
			'wrap'                         => FILTER_VALIDATE_BOOLEAN,
			'show_footer'                  => FILTER_VALIDATE_BOOLEAN,
			'search_on_click'              => FILTER_VALIDATE_BOOLEAN,
			'filters'                      => FILTER_DEFAULT,
			'quantities'                   => FILTER_VALIDATE_BOOLEAN,
			'variations'                   => $sanitize_enum_or_bool,
			'cart_button'                  => $sanitize_enum,
			'ajax_cart'                    => FILTER_VALIDATE_BOOLEAN,
			'scroll_offset'                => [
				'filter'  => FILTER_VALIDATE_INT,
				'options' => [
					'default' => $defaults['scroll_offset']
				]
			],
			'description_length'           => [
				'filter'  => FILTER_VALIDATE_INT,
				'options' => [
					'default'   => $defaults['description_length'],
					'min_range' => -1
				]
			],
			'links'                        => [
				'filter'  => FILTER_CALLBACK,
				'options' => [ Util::class, 'sanitize_list_or_bool' ]
			],
			'lazy_load'                    => FILTER_VALIDATE_BOOLEAN,
			'cache'                        => FILTER_VALIDATE_BOOLEAN,
			'image_size'                   => [
				'filter'  => FILTER_CALLBACK,
				'options' => [ Util::class, 'sanitize_image_size' ]
			],
			'lightbox'                     => FILTER_VALIDATE_BOOLEAN,
			'shortcodes'                   => FILTER_VALIDATE_BOOLEAN,
			'date_format'                  => FILTER_SANITIZE_SPECIAL_CHARS, // not FILTER_SANITIZE_FULL_SPECIAL_CHARS otherwise non-ASCII characters are encoded in the date.
			'date_columns'                 => $sanitize_list,
			'no_products_message'          => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			'no_products_filtered_message' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			'paging_type'                  => $sanitize_enum,
			'page_length'                  => $sanitize_enum_or_bool,
			'search_box'                   => $sanitize_enum_or_bool,
			'totals'                       => $sanitize_enum_or_bool,
			'pagination'                   => $sanitize_enum_or_bool,
			'reset_button'                 => FILTER_VALIDATE_BOOLEAN,
			'button_text'                  => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			'add_selected_button'          => $sanitize_enum,
			'user_products'                => FILTER_VALIDATE_BOOLEAN,
			'rows_per_page'                => [
				'filter'  => FILTER_VALIDATE_INT,
				'options' => [
					'default'   => $defaults['rows_per_page'],
					'min_range' => -1
				]
			],
			'product_limit'                => [
				'filter'  => FILTER_VALIDATE_INT,
				'options' => [
					'default'   => $defaults['product_limit'],
					'min_range' => -1
				]
			],
			'sort_by'                      => $sanitize_list,
			'sort_order'                   => $sanitize_enum,
			'status'                       => $sanitize_list,
			'category'                     => $sanitize_list,
			'exclude_category'             => $sanitize_list,
			'tag'                          => $sanitize_list,
			'term'                         => $sanitize_list,
			'numeric_terms'                => FILTER_VALIDATE_BOOLEAN,
			'cf'                           => $sanitize_list,
			'year'                         => [
				'filter'  => FILTER_VALIDATE_INT,
				'options' => [
					'default'   => $defaults['year'],
					'min_range' => 1
				]
			],
			'month'                        => [
				'filter'  => FILTER_VALIDATE_INT,
				'options' => [
					'default'   => $defaults['month'],
					'min_range' => 1,
					'max_range' => 12
				]
			],
			'day'                          => [
				'filter'  => FILTER_VALIDATE_INT,
				'options' => [
					'default'   => $defaults['day'],
					'min_range' => 1,
					'max_range' => 31
				]
			],
			'exclude'                      => $sanitize_numeric_list,
			'include'                      => $sanitize_numeric_list,
			'search_term'                  => $sanitize_search_term,
			// Internal params
			'show_hidden_columns'          => FILTER_VALIDATE_BOOLEAN,
			// Lazy load params
			'offset'                       => [
				'filter'  => FILTER_VALIDATE_INT,
				'options' => [
					'default'   => 0,
					'min_range' => 0,
				]
			],
			'user_search_term'             => $sanitize_search_term,
			'search_filters'               => [
				'filter' => FILTER_VALIDATE_INT,
				'flags'  => FILTER_REQUIRE_ARRAY
			]
		];

		// Sanitize/validate all args.
		$args = filter_var_array( $args, $validation );

		// Set properties from the sanitized args.
		Util::set_object_vars( $this, $args );

		// Fill in any blanks.
		foreach ( [ 'columns', 'status', 'image_size', 'sort_by' ] as $arg ) {
			if ( empty( $this->$arg ) ) {
				$this->$arg = $defaults[ $arg ];
			}
		}

		// Make sure boolean args are definitely booleans - sometimes filter_var_array doesn't convert them properly
		foreach ( array_filter( $validation, [ $this, 'array_filter_validate_boolean' ] ) as $arg => $val ) {
			$this->$arg = ( $this->$arg === true || $this->$arg === 'true' ) ? true : false;
		}

		// Convert some list args to arrays - columns, filters, links, category, tag, term, and cf are handled separately.
		foreach ( [ 'widths', 'priorities', 'column_breakpoints', 'date_columns', 'status', 'include', 'exclude', 'exclude_category' ] as $arg ) {
			$this->$arg = Util::string_list_to_array( $this->$arg );
		}

		// Columns, headings and filters.
		$this->set_columns();
		$this->set_filters();

		// Column widths
		if ( $this->widths ) {
			$this->widths = Util::array_pad_and_slice( $this->widths, count( $this->columns ), 'auto' );
		}

		// Responsive options
		if ( $this->priorities ) {
			$this->priorities = Util::array_pad_and_slice( $this->priorities, count( $this->columns ), 'default' );
		}

		if ( ! in_array( $this->responsive_control, [ 'inline', 'column' ], true ) ) {
			$this->responsive_control = $defaults['responsive_control'];
		}

		if ( ! in_array( $this->responsive_display, [ 'child_row', 'child_row_visible', 'modal' ], true ) ) {
			$this->responsive_display = $defaults['responsive_display'];
		}

		if ( $this->column_breakpoints ) {
			$this->column_breakpoints = Util::array_pad_and_slice( $this->column_breakpoints, count( $this->columns ), 'default' );
		}

		if ( ! $this->auto_width ) {
			// Must use inline responsive control if auto width disabled, otherwise the control column is always shown.
			$this->responsive_control = 'inline';
		} elseif ( ! empty( $this->column_breakpoints ) && 'mobile' === $this->column_breakpoints[0] ) {
			// If first column is mobile visibility, force column control as using inline will override the mobile visibility option.
			$this->responsive_control = 'column';
		}

		// Display options (page length, etc)
		foreach ( [ 'page_length', 'search_box', 'totals', 'pagination' ] as $display_option ) {
			if ( ! in_array( $this->$display_option, [ 'top', 'bottom', 'both', false ], true ) ) {
				$this->$display_option = $defaults[ $display_option ];
			}
		}

		// Links - controls whether certain items are links or plain text.
		$this->links = is_string( $this->links ) ? strtolower( $this->links ) : $this->links;

		if ( in_array( $this->links, [ 'all', 'true', true ], true ) ) {
			$this->links = [ 'all' ];
		} elseif ( empty( $this->links ) || 'none' === $this->links ) {
			$this->links = [];
		} else {
			$linkable_columns = apply_filters( 'wc_product_table_linkable_columns', [ 'id', 'sku', 'name', 'image', 'categories', 'tags', 'terms', 'attributes' ] );
			$this->links      = array_intersect( explode( ',', $this->links ), $linkable_columns );
		}

		// Paging type.
		if ( ! in_array( $this->paging_type, [ 'numbers', 'simple', 'simple_numbers', 'full', 'full_numbers' ], true ) ) {
			$this->paging_type = $defaults['paging_type'];
		}

		// Image size.
		$this->set_image_size();
		$this->set_image_column_width();

		// Validate date columns.
		if ( $this->date_columns ) {
			// Date columns must be present in table.
			$this->date_columns = array_intersect( (array) $this->date_columns, $this->columns );

			// Only custom fields or taxonomies allowed.
			$this->date_columns = array_filter( $this->date_columns, [ $this, 'array_filter_custom_field_or_taxonomy' ] );
		}

		// Sort by
		if ( array_key_exists( $this->sort_by, Columns::$column_replacements ) ) {
			$this->sort_by = Columns::$column_replacements[ $this->sort_by ];
		}

		// If sorting by attribute, make sure it uses the full attribute name.
		if ( $sort_att = Columns::get_product_attribute( $this->sort_by ) ) {
			$this->sort_by = 'att:' . Util::get_attribute_name( $sort_att );
		}

		// Sort order.
		$this->sort_order = strtolower( $this->sort_order );

		if ( ! in_array( $this->sort_order, [ 'asc', 'desc' ], true ) ) {
			// Default to descending if sorting by date or date modified, ascending for everything else.
			$this->sort_order = in_array( $this->sort_by, array_merge( [ 'date', 'date_modified' ], $this->date_columns ), true ) ? 'desc' : 'asc';
		}

		// Check search terms are valid.
		if ( ! Util::is_valid_search_term( $this->search_term ) ) {
			$this->search_term = '';
		}

		if ( ! Util::is_valid_search_term( $this->user_search_term ) ) {
			$this->user_search_term = '';
		}

		// Description length & rows per page - can be positive int or -1
		foreach ( [ 'description_length', 'rows_per_page', 'product_limit' ] as $arg ) {
			// Sanity check in case filter set an invalid value
			if ( ! is_int( $this->$arg ) || $this->$arg < -1 ) {
				$this->$arg = $defaults[ $arg ];
			}
			if ( 0 === $this->$arg ) {
				$this->$arg = -1;
			}
		}

		// If enabling shortcodes, display the full content
		if ( $this->shortcodes ) {
			$this->description_length = -1;
		}

		// @deprecated 3.0.1 Replaced by plugin setting.
		$this->product_limit = (int) apply_filters_deprecated( 'wc_product_table_max_product_limit', [ $this->product_limit, $this ], '3.0.1' );

		// Ignore product limit if lazy loading and the default product limit is used.
		if ( $this->lazy_load && (int) $defaults['product_limit'] === $this->product_limit ) {
			$this->product_limit = -1;
		}

		// Disable lightbox if explicitly linking from image column.
		if ( in_array( 'image', $this->links, true ) ) {
			$this->lightbox = false;
		}

		// Variations.
		if ( true === $this->variations ) {
			$this->variations = 'dropdown';
		} elseif ( ! in_array( $this->variations, [ 'dropdown', 'separate' ], true ) ) {
			$this->variations = false;
		}

		// Separate variations not currently supported for lazy load.
		if ( 'separate' === $this->variations && $this->lazy_load ) {
			$this->variations = 'dropdown';
		}

		// Cart button.
		if ( ! in_array( $this->cart_button, [ 'button', 'button_checkbox', 'checkbox' ], true ) ) {
			$this->cart_button = $defaults['cart_button'];
		}

		// Add selected button.
		if ( ! in_array( $this->add_selected_button, [ 'top', 'bottom', 'both' ], true ) ) {
			$this->add_selected_button = $defaults['add_selected_button'];
		}

		// Text for 'button' column button.
		if ( ! $this->button_text ) {
			$this->button_text = __( 'Show details', 'woocommerce-product-table' );
		}

		do_action( 'wc_product_table_parse_args', $this );
	}

	private function set_columns() {
		$columns = Columns::parse_columns( $this->columns );

		if ( empty( $columns ) ) {
			$columns = Columns::parse_columns( Defaults::get_table_defaults()['columns'] );
		}

		$this->columns  = array_keys( $columns );
		$this->headings = array_values( $columns );
	}

	private function set_filters() {
		$filters = Columns::parse_filters( $this->filters, $this->columns );

		$this->filters         = ! empty( $filters ) ? array_keys( $filters ) : false;
		$this->filter_headings = array_values( $filters );
	}

	private function set_image_column_width() {
		if ( false === ( $image_col = array_search( 'image', $this->columns, true ) ) ) {
			return;
		}

		if ( $this->widths && isset( $this->widths[ $image_col ] ) && 'auto' !== $this->widths[ $image_col ] ) {
			return;
		}

		if ( $image_col_width = Util::get_image_size_width( $this->image_size ) ) {
			if ( ! $this->widths ) {
				$this->widths = array_fill( 0, count( $this->columns ), 'auto' );
			}
			$this->widths[ $image_col ] = $image_col_width . 'px';
		}
	}

	private function set_image_size() {
		if ( empty( $this->image_size ) ) {
			return;
		}

		$size_arr           = explode( 'x', str_replace( ',', 'x', $this->image_size ) );
		$size_numeric_count = count( array_filter( $size_arr, 'is_numeric' ) );

		if ( 1 === $size_numeric_count ) {
			// One number, so use for both width and height
			$this->image_size = [ $size_arr[0], $size_arr[0] ];
		} elseif ( 2 === $size_numeric_count ) {
			// Width and height specified
			$this->image_size = $size_arr;
		} // otherwise assume it's a text-based image size, e.g. 'thumbnail'
	}

	/**
	 * Maintain support for old arg names.
	 *
	 * @param array $args The product table args to check.
	 * @return array The updated attributes with old ones replaced with their new equivalent.
	 */
	public static function back_compat_args( array $args ) {
		if ( empty( $args ) ) {
			return $args;
		}

		$compat = [
			'add_to_cart'          => 'cart_button',
			'display_page_length'  => 'page_length',
			'display_totals'       => 'totals',
			'display_pagination'   => 'pagination',
			'display_search_box'   => 'search_box',
			'display_reset_button' => 'reset_button',
			'show_quantities'      => 'quantities',
			'show_quantity'        => 'quantities'
		];

		foreach ( $compat as $old => $new ) {
			if ( isset( $args[ $old ] ) ) {
				$args[ $new ] = $args[ $old ];
				unset( $args[ $old ] );
			}
		}

		return $args;
	}

	/**
	 * Get the default table args based on the the options set in the plugin settings.
	 *
	 * @return array The site defaults.
	 */
	public static function get_defaults() {
		if ( null === self::$site_defaults ) {
			self::$site_defaults = array_merge(
				Defaults::get_table_defaults(),
				Settings::settings_to_table_defaults( self::back_compat_args( Settings::get_setting_table_defaults() ) )
			);
		}

		return self::$site_defaults;
	}

	/**
	 * Deprecated.
	 *
	 * @deprecated 3.0.2 Replaced by Defaults::get_table_defaults.
	 */
	public static function get_table_defaults() {
		_deprecated_function( __METHOD__, '3.0.2', 'Defaults::get_table_defaults' );
		return Defaults::get_table_defaults();
	}

	/**
	 * Deprecated.
	 *
	 * @deprecated 3.0.2 Renamed get_defaults().
	 */
	public static function get_user_defaults() {
		_deprecated_function( __METHOD__, '3.0.2', 'get_defaults' );
		return self::get_defaults();
	}

	/**
	 * Deprecated.
	 *
	 * @deprecated 3.0.2 Replaced by Columns::parse_columns().
	 */
	public static function parse_columns_arg( $columns ) {
		_deprecated_function( __METHOD__, '3.0.2', 'Columns::parse_columns' );
		$columns = Columns::parse_columns( $columns );

		return [
			'columns'  => array_keys( $columns ),
			'headings' => array_values( $columns )
		];
	}

	/**
	 * Deprecated.
	 *
	 * @deprecated 3.0.2 Replaced by Columns::parse_filters().
	 */
	public static function parse_filters_arg( $filters, array $columns = [], $variations = false ) {
		_deprecated_function( __METHOD__, '3.0.2', 'Columns::parse_filters' );
		$filters = Columns::parse_filters( $filters, $columns );

		return [
			'filters'  => array_keys( $filters ),
			'headings' => array_values( $filters )
		];
	}

}
