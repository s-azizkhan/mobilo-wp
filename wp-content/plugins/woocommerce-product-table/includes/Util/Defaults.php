<?php

namespace Barn2\Plugin\WC_Product_Table\Util;

class Defaults {

	private static $table_defaults = [
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
		'quantities'                   => true,
		'variations'                   => 'dropdown',
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

	public static function add_selected_to_cart_default_text() {
		return __( 'Add to cart', 'woocommerce-product-table' );
	}

	public static function get_design_defaults() {
		return [ 'use_theme' => 'theme' ];
	}

	public static function get_misc_defaults() {
		return [
			'cache_expiry'          => 6,
			'add_selected_text'     => self::add_selected_to_cart_default_text(),
			'quick_view_links'      => false,
			'addons_layout'         => 'block',
			'addons_option_layout'  => 'block',
			'shop_override'         => false,
			'search_override'       => false,
			'archive_override'      => false,
			'product_tag_override'  => false,
			'attribute_override'    => false,
			'include_hidden'        => false,
			'variation_name_format' => 'full'
		];
	}

	public static function get_table_defaults() {
		return apply_filters( 'wc_product_table_default_args', self::$table_defaults );
	}

}
