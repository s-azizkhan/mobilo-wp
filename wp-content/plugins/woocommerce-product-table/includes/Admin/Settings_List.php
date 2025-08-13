<?php

namespace Barn2\Plugin\WC_Product_Table\Admin;

use Barn2\Plugin\WC_Product_Table\Util\Defaults;
use Barn2\Plugin\WC_Product_Table\Util\Settings;
use Barn2\Plugin\WC_Product_Table\Util\Util;
use Barn2\WPT_Lib\Plugin\Licensed_Plugin;
use Barn2\WPT_Lib\Util as Lib_Util;

/**
 * Builds the list of settings to use on the plugin settings pages.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Settings_List {

	/**
	 * Get the list of settings of this plugin.
	 *
	 * @param Licensed_Plugin $plugin
	 * @return array
	 */
	public static function get_all_settings( Licensed_Plugin $plugin ) {
		// Settings wrapper.
		$plugin_settings = [
			[
				'id'    => 'product_table_settings_start',
				'type'  => 'settings_start',
				'class' => 'product-table-settings'
			]
		];

		// Filter before adding any settings.
		$plugin_settings = apply_filters( 'wc_product_table_plugin_settings_after_start', $plugin_settings );

		// License key setting.
		$plugin_settings = array_merge(
			$plugin_settings,
			[
				[
					'title' => __( 'Product tables', 'woocommerce-product-table' ),
					'type'  => 'title',
					'id'    => 'product_table_settings_license',
					'desc'  => sprintf(
						'<p>%s</p><p>%s | %s</p>',
						__( 'The following options control the WooCommerce Product Table extension.', 'woocommerce-product-table' ),
						Lib_Util::format_link( $plugin->get_documentation_url(), __( 'Documentation', 'woocommerce-product-table' ), true ),
						Lib_Util::format_link( $plugin->get_support_url(), __( 'Support', 'woocommerce-product-table' ), true )
					)
				],
				$plugin->get_license_setting()->get_license_key_setting(),
				$plugin->get_license_setting()->get_license_override_setting(),
				[
					'type' => 'sectionend',
					'id'   => 'product_table_settings_license'
				]
			]
		);

		// Shop integration settings.
		$plugin_settings = array_merge(
			$plugin_settings,
			array_merge(
				[
					[
						'title' => __( 'Shop integration', 'woocommerce-product-table' ),
						'type'  => 'title',
						'id'    => 'product_table_settings_selecting',
						'desc'  => apply_filters( 'wc_product_table_display_admin_description', '<p>' . __( 'Replace the following WooCommerce pages with a product table.', 'woocommerce-product-table' ) . '</p>' ),
					],
					[
						'title'         => __( 'Use table layout for', 'woocommerce-product-table' ),
						'type'          => 'checkbox',
						'id'            => Settings::OPTION_MISC . '[shop_override]',
						'desc'          => __( 'Shop page', 'woocommerce-product-table' ),
						'default'       => 'no',
						'checkboxgroup' => 'start'
					],
					[
						'type'          => 'checkbox',
						'id'            => Settings::OPTION_MISC . '[search_override]',
						'desc'          => __( 'Product search results', 'woocommerce-product-table' ),
						'default'       => 'no',
						'checkboxgroup' => ''
					],
					[
						'type'          => 'checkbox',
						'id'            => Settings::OPTION_MISC . '[archive_override]',
						'desc'          => __( 'Product categories', 'woocommerce-product-table' ),
						'default'       => 'no',
						'checkboxgroup' => ''
					],
					[
						'type'          => 'checkbox',
						'id'            => Settings::OPTION_MISC . '[product_tag_override]',
						'desc'          => __( 'Product tags', 'woocommerce-product-table' ),
						'default'       => 'no',
						'checkboxgroup' => ''
					],
					[
						'type'          => 'checkbox',
						'id'            => Settings::OPTION_MISC . '[attribute_override]',
						'desc'          => __( 'Product attributes', 'woocommerce-product-table' ),
						'default'       => 'no',
						'checkboxgroup' => ''
					],
				],
				self::get_taxonomy_settings(),
				[
					[
						'type' => 'sectionend',
						'id'   => 'product_table_settings_selecting'
					]
				]
			)
		);

		$defaults = Settings::to_woocommerce_settings( Defaults::get_table_defaults() );

		// Table content settings.
		$plugin_settings = array_merge(
			$plugin_settings,
			[
				[
					'title' => __( 'Table content', 'woocommerce-product-table' ),
					'type'  => 'title',
					'desc'  => sprintf(
					// translators: 1: help link open tag, 2: help link close tag.
						__( 'You can override any of the settings below for individual tables by %1$sadding options%2$s to the shortcode or block.', 'woocommerce-product-table' ),
						Lib_Util::format_barn2_link_open( 'kb/product-table-options', true ),
						'</a>'
					),
					'id'    => 'product_table_settings_content'
				],
				[
					'title'   => __( 'Columns', 'woocommerce-product-table' ),
					'type'    => 'text',
					'id'      => Settings::OPTION_TABLE_DEFAULTS . '[columns]',
					'desc'    => __( 'Enter the columns for your product tables.', 'woocommerce-product-table' ) . ' ' . Lib_Util::barn2_link( 'kb/product-table-columns', false, true ),
					'default' => $defaults['columns'],
					'css'     => 'width:600px;max-width:100%;'
				],
				[
					'title'             => __( 'Description length', 'woocommerce-product-table' ),
					'type'              => 'number',
					'id'                => Settings::OPTION_TABLE_DEFAULTS . '[description_length]',
					'suffix'            => __( 'words', 'woocommerce-product-table' ),
					'desc_tip'          => __( 'Enter -1 to show the full product description including formatting.', 'woocommerce-product-table' ),
					'default'           => $defaults['description_length'],
					'css'               => 'width:75px',
					'class'             => 'with-suffix',
					'custom_attributes' => [
						'min' => -1
					]
				],
				[
					'title'    => __( 'Product links', 'woocommerce-product-table' ),
					'type'     => 'text',
					'id'       => Settings::OPTION_TABLE_DEFAULTS . '[links]',
					'desc'     => __( 'Include links to the single product page and taxonomy archives.', 'woocommerce-product-table' ) . ' ' . Lib_Util::barn2_link( 'kb/product-table-links', false, true ),
					'desc_tip' => __( "Use 'all' to enable all links, 'none' to hide them, or a comma-separated list of: 'sku', 'name', 'image', 'tags', 'categories', 'terms', or 'attributes'", 'woocommerce-product-table' ),
					'default'  => $defaults['links'],
					'css'      => 'width:200px;max-width:100%;'
				],
				[
					'title'    => __( 'Image size', 'woocommerce-product-table' ),
					'type'     => 'text',
					'id'       => Settings::OPTION_TABLE_DEFAULTS . '[image_size]',
					'desc_tip' => __( "Enter a width x height in pixels, e.g. 70x50, or a standard image size such as 'thumbnail'", 'woocommerce-product-table' ),
					'default'  => $defaults['image_size'],
					'css'      => 'width:200px;max-width:100%;'
				],
				[
					'type' => 'sectionend',
					'id'   => 'product_table_settings_content'
				]
			]
		);

		// Load settings.
		$plugin_settings = array_merge(
			$plugin_settings,
			[
				[
					'title' => __( 'Loading', 'woocommerce-product-table' ),
					'type'  => 'title',
					'id'    => 'product_table_settings_loading'
				],
				[
					'title'             => __( 'Lazy load', 'woocommerce-product-table' ),
					'type'              => 'checkbox',
					'id'                => Settings::OPTION_TABLE_DEFAULTS . '[lazy_load]',
					'desc'              => __( 'Load products one page at a time', 'woocommerce-product-table' ),
					'desc_tip'          => __( 'Enable this if you have many products or experience slow page load times.', 'woocommerce-product-table' ) . '<br/>' .
										   sprintf(
										   /* translators: 1: Help link open tag, 2: help link close tag */
											   __( 'Warning: Lazy load has %1$ssome limitations%2$s &mdash; it limits the search, sorting, dropdown filters, and variations.', 'woocommerce-product-table' ),
											   Lib_Util::format_barn2_link_open( 'kb/lazy-load', true ),
											   '</a>'
										   ),
					'default'           => $defaults['lazy_load'],
					'class'             => 'toggle-parent',
					'custom_attributes' => [
						'data-child-class' => 'toggle-product-limit',
						'data-toggle-val'  => 0
					]
				],
				[
					'title'             => __( 'Product limit', 'woocommerce-product-table' ),
					'type'              => 'number',
					'id'                => Settings::OPTION_TABLE_DEFAULTS . '[product_limit]',
					'desc'              => __( 'The maximum number of products in one table.', 'woocommerce-product-table' ),
					'desc_tip'          => __( 'Enter -1 to show all products.', 'woocommerce-product-table' ),
					'default'           => $defaults['product_limit'],
					'class'             => 'toggle-product-limit',
					'custom_attributes' => [
						'min' => -1
					],
					'css'               => 'width:75px'
				],
				[
					'title'   => __( 'Show hidden products', 'woocommerce-product-table' ),
					'type'    => 'checkbox',
					'id'      => Settings::OPTION_MISC . '[include_hidden]',
					'desc'    => __( 'Include hidden products in the table', 'woocommerce-product-table' ),
					'default' => 'no',
				],
				[
					'type' => 'sectionend',
					'id'   => 'product_table_settings_loading'
				]
			]
		);

		// Sort settings.
		$plugin_settings = array_merge(
			$plugin_settings,
			[
				[
					'title' => __( 'Sorting', 'woocommerce-product-table' ),
					'type'  => 'title',
					'desc'  => __( 'How to sort products when the table is first loaded.', 'woocommerce-product-table' ),
					'id'    => 'product_table_settings_sorting'
				],
				[
					'title'             => __( 'Sort by', 'woocommerce-product-table' ),
					'type'              => 'select',
					'id'                => Settings::OPTION_TABLE_DEFAULTS . '[sort_by]',
					'options'           => [
						'menu_order' => __( 'WooCommerce default', 'woocommerce-product-table' ),
						'name'       => __( 'Name', 'woocommerce-product-table' ),
						'sku'        => __( 'SKU', 'woocommerce-product-table' ),
						'price'      => __( 'Price', 'woocommerce-product-table' ),
						'popularity' => __( 'Popularity (sales)', 'woocommerce-product-table' ),
						'reviews'    => __( 'Average rating', 'woocommerce-product-table' ),
						'date'       => __( 'Date added', 'woocommerce-product-table' ),
						'modified'   => __( 'Date modified', 'woocommerce-product-table' ),
						'id'         => __( 'Product ID', 'woocommerce-product-table' ),
						'custom'     => __( 'Other', 'woocommerce-product-table' )
					],
					'default'           => $defaults['sort_by'],
					'class'             => 'toggle-parent wc-enhanced-select',
					'custom_attributes' => [
						'data-child-class' => 'custom-sort',
						'data-toggle-val'  => 'custom'
					]
				],
				[
					'title' => __( 'Sort column', 'woocommerce-product-table' ),
					'type'  => 'text',
					'id'    => Settings::OPTION_TABLE_DEFAULTS . '[sort_by_custom]',
					'class' => 'custom-sort',
					'desc'  => __( 'Enter a column name, e.g. description, att:size. Only applied when lazy load is disabled.', 'woocommerce-product-table' ),
					'css'   => 'width:200px;max-width:100%;'
				],
				[
					'title'   => __( 'Sort direction', 'woocommerce-product-table' ),
					'type'    => 'select',
					'id'      => Settings::OPTION_TABLE_DEFAULTS . '[sort_order]',
					'options' => [
						''     => __( 'Automatic', 'woocommerce-product-table' ),
						'asc'  => __( 'Ascending (A to Z, 1 to 99)', 'woocommerce-product-table' ),
						'desc' => __( 'Descending (Z to A, 99 to 1)', 'woocommerce-product-table' )
					],
					'default' => $defaults['sort_order'],
					'class'   => 'wc-enhanced-select'
				],
				[
					'type' => 'sectionend',
					'id'   => 'product_table_settings_sorting'
				]
			]
		);

		// Search and filter settings.
		$plugin_settings = array_merge(
			$plugin_settings,
			[
				[
					'title' => __( 'Search and filter', 'woocommerce-product-table' ),
					'type'  => 'title',
					'desc'  => __( 'How customers search and filter products.', 'woocommerce-product-table' ),
					'id'    => 'product_table_settings_search'
				],
				[
					'title'             => __( 'Product filters', 'woocommerce-product-table' ),
					'type'              => 'select',
					'id'                => Settings::OPTION_TABLE_DEFAULTS . '[filters]',
					'options'           => [
						'false'  => __( 'Disabled', 'woocommerce-product-table' ),
						'true'   => __( 'Show based on table content', 'woocommerce-product-table' ),
						'custom' => __( 'Custom', 'woocommerce-product-table' )
					],
					'desc'              => __( 'Filter the table by category, tag, attribute or taxonomy.', 'woocommerce-product-table' ),
					'default'           => $defaults['filters'],
					'class'             => 'toggle-parent wc-enhanced-select',
					'custom_attributes' => [
						'data-child-class' => 'custom-search-filter',
						'data-toggle-val'  => 'custom'
					]
				],
				[
					'title'    => __( 'Custom filters', 'woocommerce-product-table' ),
					'type'     => 'text',
					'id'       => Settings::OPTION_TABLE_DEFAULTS . '[filters_custom]',
					'desc'     => __( 'Enter the filters as a comma-separated list.', 'woocommerce-product-table' ) . ' ' . Lib_Util::barn2_link( 'kb/wpt-filters/#filter-dropdowns', false, true ),
					'desc_tip' => __( 'E.g. categories,tags,att:color', 'woocommerce-product-table' ),
					'class'    => 'regular-text custom-search-filter'
				],
				[
					'title'   => __( 'Search box', 'woocommerce-product-table' ),
					'type'    => 'select',
					'id'      => Settings::OPTION_TABLE_DEFAULTS . '[search_box]',
					'options' => [
						'top'    => __( 'Above table', 'woocommerce-product-table' ),
						'bottom' => __( 'Below table', 'woocommerce-product-table' ),
						'both'   => __( 'Above and below table', 'woocommerce-product-table' ),
						'false'  => __( 'Hidden', 'woocommerce-product-table' )
					],
					'default' => $defaults['search_box'],
					'class'   => 'wc-enhanced-select'
				],
				[
					'title'   => __( 'Number of products found', 'woocommerce-product-table' ),
					'type'    => 'select',
					'id'      => Settings::OPTION_TABLE_DEFAULTS . '[totals]',
					'options' => [
						'top'    => __( 'Above table', 'woocommerce-product-table' ),
						'bottom' => __( 'Below table', 'woocommerce-product-table' ),
						'both'   => __( 'Above and below table', 'woocommerce-product-table' ),
						'false'  => __( 'Hidden', 'woocommerce-product-table' )
					],
					'default' => $defaults['totals'],
					'class'   => 'wc-enhanced-select'
				],
				[
					'title'   => __( 'Reset search', 'woocommerce-product-table' ),
					'type'    => 'checkbox',
					'id'      => Settings::OPTION_TABLE_DEFAULTS . '[reset_button]',
					'desc'    => __( 'Show a link to reset the search', 'woocommerce-product-table' ),
					'default' => $defaults['reset_button']
				],
				[
					'type' => 'sectionend',
					'id'   => 'product_table_settings_search'
				]
			]
		);

		// Add to cart settings.
		$plugin_settings = array_merge(
			$plugin_settings,
			[
				[
					'title' => __( 'Add to cart', 'woocommerce-product-table' ),
					'type'  => 'title',
					'desc'  => __( 'How customers buy products from the table.', 'woocommerce-product-table' ),
					'id'    => 'product_table_settings_cart'
				],
				[
					'title'   => __( 'Add to cart method', 'woocommerce-product-table' ),
					'type'    => 'select',
					'id'      => Settings::OPTION_TABLE_DEFAULTS . '[cart_button]',
					'options' => [
						'button'          => __( 'Cart buttons', 'woocommerce-product-table' ),
						'checkbox'        => __( 'Checkboxes', 'woocommerce-product-table' ),
						'button_checkbox' => __( 'Cart buttons and checkboxes', 'woocommerce-product-table' )
					],
					'default' => $defaults['cart_button'],
					'class'   => 'wc-enhanced-select'
				],
				[
					'title'   => __( 'Quantities', 'woocommerce-product-table' ),
					'type'    => 'checkbox',
					'id'      => Settings::OPTION_TABLE_DEFAULTS . '[quantities]',
					'desc'    => __( 'Show a quantity picker for each product', 'woocommerce-product-table' ),
					'default' => $defaults['quantities']
				],
				[
					'title'             => __( 'Variations', 'woocommerce-product-table' ),
					'type'              => 'select',
					'id'                => Settings::OPTION_TABLE_DEFAULTS . '[variations]',
					'options'           => [
						'dropdown' => __( 'Show as dropdown lists', 'woocommerce-product-table' ),
						'separate' => __( 'Show one variation per row', 'woocommerce-product-table' ),
						'false'    => __( 'Read More button linking to the product page', 'woocommerce-product-table' ),
					],
					'desc'              => __( 'How to display the options for variable products.', 'woocommerce-product-table' ) . ' ' . Lib_Util::barn2_link( 'kb/product-variations', false, true ),
					'default'           => $defaults['variations'],
					'class'             => 'toggle-parent wc-enhanced-select',
					'custom_attributes' => [
						'data-child-class' => 'variation-name-format',
						'data-toggle-val'  => 'separate'
					]
				],
				[
					'title'   => __( 'Variation name format', 'woocommerce-product-table' ),
					'type'    => 'select',
					'id'      => Settings::OPTION_MISC . '[variation_name_format]',
					'options' => [
						'full'       => __( 'Full (product name + attributes)', 'woocommerce-product-table' ),
						'attributes' => __( 'Attributes only', 'woocommerce-product-table' ),
						'parent'     => __( 'Product name only', 'woocommerce-product-table' ),
					],
					'default' => 'full',
					'class'   => 'variation-name-format'
				],
				[
					'title'   => __( 'Multi-select cart button', 'woocommerce-product-table' ),
					'type'    => 'select',
					'id'      => Settings::OPTION_TABLE_DEFAULTS . '[add_selected_button]',
					'options' => [
						'top'    => __( 'Above table', 'woocommerce-product-table' ),
						'bottom' => __( 'Below table', 'woocommerce-product-table' ),
						'both'   => __( 'Above and below table', 'woocommerce-product-table' )
					],
					'desc'    => __( 'The location of the cart button when ordering multiple products using checkboxes.', 'woocommerce-product-table' ),
					'default' => $defaults['add_selected_button'],
					'class'   => 'wc-enhanced-select'
				],
				[
					'title'   => __( 'Multi-select cart button text', 'woocommerce-product-table' ),
					'type'    => 'text',
					'id'      => Settings::OPTION_MISC . '[add_selected_text]',
					'default' => Defaults::add_selected_to_cart_default_text()
				],
				[
					'type' => 'sectionend',
					'id'   => 'product_table_settings_cart'
				]
			]
		);

		// Pagination settings.
		$plugin_settings = array_merge(
			$plugin_settings,
			[
				[
					'title' => __( 'Pagination', 'woocommerce-product-table' ),
					'type'  => 'title',
					'desc'  => __( 'How to paginate products when there are multiple pages of results.', 'woocommerce-product-table' ),
					'id'    => 'product_table_settings_pagination'
				],
				[
					'title'             => __( 'Products per page', 'woocommerce-product-table' ),
					'type'              => 'number',
					'id'                => Settings::OPTION_TABLE_DEFAULTS . '[rows_per_page]',
					'desc'              => __( 'The number of products per page of results.', 'woocommerce-product-table' ),
					'desc_tip'          => __( 'Enter -1 to show all products on a single page.', 'woocommerce-product-table' ),
					'default'           => $defaults['rows_per_page'],
					'css'               => 'width:75px',
					'custom_attributes' => [
						'min' => -1
					]
				],
				[
					'title'   => __( 'Products per page control', 'woocommerce-product-table' ),
					'type'    => 'select',
					'id'      => Settings::OPTION_TABLE_DEFAULTS . '[page_length]',
					'desc'    => __( 'Allow customers to adjust the number of products per page.', 'woocommerce-product-table' ),
					'options' => [
						'top'    => __( 'Above table', 'woocommerce-product-table' ),
						'bottom' => __( 'Below table', 'woocommerce-product-table' ),
						'both'   => __( 'Above and below table', 'woocommerce-product-table' ),
						'false'  => __( 'Hidden', 'woocommerce-product-table' )
					],
					'default' => $defaults['page_length'],
					'class'   => 'wc-enhanced-select'
				],
				[
					'title'   => __( 'Pagination buttons', 'woocommerce-product-table' ),
					'type'    => 'select',
					'id'      => Settings::OPTION_TABLE_DEFAULTS . '[pagination]',
					'options' => [
						'top'    => __( 'Above table', 'woocommerce-product-table' ),
						'bottom' => __( 'Below table', 'woocommerce-product-table' ),
						'both'   => __( 'Above and below table', 'woocommerce-product-table' ),
						'false'  => __( 'Hidden', 'woocommerce-product-table' )
					],
					'default' => $defaults['pagination'],
					'class'   => 'wc-enhanced-select'
				],
				[
					'title'   => __( 'Pagination type', 'woocommerce-product-table' ),
					'type'    => 'select',
					'id'      => Settings::OPTION_TABLE_DEFAULTS . '[paging_type]',
					'options' => [
						'numbers'        => __( 'Page numbers', 'woocommerce-product-table' ),
						'simple'         => __( 'Prev - Next', 'woocommerce-product-table' ),
						'simple_numbers' => __( 'Prev - Page numbers - Next', 'woocommerce-product-table' ),
						'full'           => __( 'First - Prev - Next - Last', 'woocommerce-product-table' ),
						'full_numbers'   => __( 'First - Prev - Page numbers - Next - Last', 'woocommerce-product-table' )
					],
					'default' => $defaults['paging_type'],
					'class'   => 'wc-enhanced-select'
				],
				[
					'type' => 'sectionend',
					'id'   => 'product_table_settings_pagination'
				]
			]
		);

		// Table design settings.
		$plugin_settings = array_merge(
			$plugin_settings,
			[
				[
					'title' => __( 'Design', 'woocommerce-product-table' ),
					'desc'  => __( 'Customize the design of the table.', 'woocommerce-product-table' ),
					'type'  => 'title',
					'id'    => 'product_table_settings_design',
				],
				[
					'title'             => __( 'Design', 'woocommerce-product-table' ),
					'type'              => 'radio',
					'id'                => Settings::OPTION_TABLE_STYLING . '[use_theme]',
					'options'           => [
						'theme'  => __( 'Default', 'woocommerce-product-table' ),
						'custom' => __( 'Custom', 'woocommerce-product-table' )
					],
					'default'           => 'theme',
					'class'             => 'toggle-parent',
					'custom_attributes' => [
						'data-child-class' => 'custom-style',
						'data-toggle-val'  => 'custom'
					]
				],
				[
					'title'       => __( 'Borders', 'woocommerce-product-table' ),
					'type'        => 'color_size',
					'id'          => Settings::OPTION_TABLE_STYLING . '[border_outer]',
					'desc'        => self::get_icon( 'external-border.svg', __( 'External border icon', 'woocommerce-product-table' ) ) . __( 'External', 'woocommerce-product-table' ),
					'desc_tip'    => __( 'The border for the outer edges of the table.', 'woocommerce-product-table' ),
					'placeholder' => __( 'Width', 'woocommerce-product-table' ),
					'class'       => 'custom-style',
				],
				[
					'type'        => 'color_size',
					'id'          => Settings::OPTION_TABLE_STYLING . '[border_header]',
					/* translators: 'Header' in this context refers to the heading row of a table. */
					'desc'        => self::get_icon( 'header-border.svg', __( 'Header border icon', 'woocommerce-product-table' ) ) . __( 'Header', 'woocommerce-product-table' ),
					'desc_tip'    => __( 'The border for the bottom of the header row.', 'woocommerce-product-table' ),
					'placeholder' => __( 'Width', 'woocommerce-product-table' ),
					'class'       => 'custom-style',
				],
				[
					'type'        => 'color_size',
					'id'          => Settings::OPTION_TABLE_STYLING . '[border_cell]',
					/* translators: 'Cell' in this context refers to a cell in a table or spreadsheet. */
					'desc'        => self::get_icon( 'cell-border.svg', __( 'Cell border icon', 'woocommerce-product-table' ) ) . __( 'Body', 'woocommerce-product-table' ),
					'desc_tip'    => __( 'The border between cells in your table.', 'woocommerce-product-table' ),
					'placeholder' => __( 'Width', 'woocommerce-product-table' ),
					'class'       => 'custom-style',
				],
				[
					'title'       => __( 'Header background color', 'woocommerce-product-table' ),
					'type'        => 'color_picker',
					'id'          => Settings::OPTION_TABLE_STYLING . '[header_bg]',
					'placeholder' => __( 'Color', 'woocommerce-product-table' ),
					'class'       => 'custom-style'
				],
				[
					'title' => __( 'Header text', 'woocommerce-product-table' ),
					'type'  => 'color_size',
					'id'    => Settings::OPTION_TABLE_STYLING . '[header_font]',
					'min'   => 1,
					'class' => 'custom-style',
				],
				[
					'title'       => __( 'Body background color', 'woocommerce-product-table' ),
					'type'        => 'color_picker',
					'id'          => Settings::OPTION_TABLE_STYLING . '[cell_bg]',
					'placeholder' => __( 'Color', 'woocommerce-product-table' ),
					'class'       => 'custom-style'
				],
				[
					'title' => __( 'Body text', 'woocommerce-product-table' ),
					'type'  => 'color_size',
					'id'    => Settings::OPTION_TABLE_STYLING . '[cell_font]',
					'min'   => 1,
					'class' => 'custom-style'
				],
				[
					'type' => 'sectionend',
					'id'   => 'product_table_settings_design'
				]
			]
		);

		// Filter before advanced settings.
		$plugin_settings = apply_filters( 'wc_product_table_plugin_settings_before_advanced', $plugin_settings );

		$plugin_settings = array_merge(
			$plugin_settings,
			[
				[
					'title' => __( 'Advanced', 'woocommerce-product-table' ),
					'type'  => 'title',
					'id'    => 'product_table_settings_advanced'
				],
				[
					'title'   => __( 'AJAX', 'woocommerce-product-table' ),
					'type'    => 'checkbox',
					'id'      => Settings::OPTION_TABLE_DEFAULTS . '[ajax_cart]',
					'desc'    => __( 'Use AJAX when adding to the cart', 'woocommerce-product-table' ),
					'default' => $defaults['ajax_cart']
				],
				[
					'title'   => __( 'Shortcodes', 'woocommerce-product-table' ),
					'type'    => 'checkbox',
					'id'      => Settings::OPTION_TABLE_DEFAULTS . '[shortcodes]',
					'desc'    => __( 'Show shortcodes, HTML and other formatting in the table', 'woocommerce-product-table' ),
					'default' => $defaults['shortcodes']
				],
				[
					'title'   => __( 'Image lightbox', 'woocommerce-product-table' ),
					'type'    => 'checkbox',
					'id'      => Settings::OPTION_TABLE_DEFAULTS . '[lightbox]',
					'desc'    => __( 'Show product images in a lightbox', 'woocommerce-product-table' ),
					'default' => $defaults['lightbox'],
				],
				[
					'title'             => __( 'Caching', 'woocommerce-product-table' ),
					'type'              => 'checkbox',
					'id'                => Settings::OPTION_TABLE_DEFAULTS . '[cache]',
					'desc'              => __( 'Cache table contents to improve load times', 'woocommerce-product-table' ),
					'default'           => $defaults['cache'],
					'class'             => 'toggle-parent',
					'custom_attributes' => [
						'data-child-class' => 'toggle-cache'
					]
				],
				[
					'title'             => __( 'Cache expiration', 'woocommerce-product-table' ),
					'type'              => 'number',
					'id'                => Settings::OPTION_MISC . '[cache_expiry]',
					'suffix'            => __( 'hours', 'woocommerce-product-table' ),
					'desc'              => __( 'Your data will be refreshed after this length of time.', 'woocommerce-product-table' ),
					'default'           => 6,
					'class'             => 'toggle-cache with-suffix',
					'css'               => 'width:75px;',
					'custom_attributes' => [
						'min' => 1,
						'max' => 9999
					]
				],
				[
					'type' => 'sectionend',
					'id'   => 'product_table_settings_advanced'
				]
			]
		);

		// Filter after all settings.
		$plugin_settings = apply_filters( 'wc_product_table_plugin_settings_before_end', $plugin_settings );

		$plugin_settings[] = [
			'id'   => 'product_table_settings_end',
			'type' => 'settings_end'
		];

		// phpcs:ignore WordPress.NamingConventions.ValidHookName
		return apply_filters( 'woocommerce_get_settings_product-table', $plugin_settings );
	}

	/**
	 * Get an icon for the plugin's settings.
	 *
	 * @param string $icon
	 * @param string $alt_text
	 * @return string
	 */
	private static function get_icon( $icon, $alt_text = '' ) {
		return sprintf(
			'<img src="%1$s" alt="%2$s" width="22" height="22" />',
			Util::get_asset_url( 'images/' . ltrim( $icon, '/' ) ),
			$alt_text
		);
	}

	/**
	 * Get taxonomy settings for the plugin options panel.
	 *
	 * @return array
	 */
	private static function get_taxonomy_settings() {
		$settings          = [];
		$public_taxonomies = wp_filter_object_list(
			get_object_taxonomies( 'product', 'objects' ),
			[ 'public' => true ]
		);

		if ( ! empty( $public_taxonomies ) ) {
			foreach ( $public_taxonomies as $public_taxonomy ) {
				if ( empty( $public_taxonomy->label ) || empty( $public_taxonomy->name ) ) {
					continue;
				}

				if ( in_array( $public_taxonomy->name, [ 'product_shipping_class', 'product_cat', 'product_tag' ], true ) ) {
					continue;
				}

				$settings[] = [
					'type'          => 'checkbox',
					'id'            => Settings::OPTION_MISC . '[' . $public_taxonomy->name . '_override]',
					'desc'          => $public_taxonomy->label . ' - <code>' . $public_taxonomy->name . '</code>',
					'default'       => 'no',
					'checkboxgroup' => ''
				];
			}

			if ( ! empty( $settings ) ) {
				$settings[ count( $settings ) - 1 ]['checkboxgroup'] = 'end';
			}
		}

		return $settings;
	}

}
