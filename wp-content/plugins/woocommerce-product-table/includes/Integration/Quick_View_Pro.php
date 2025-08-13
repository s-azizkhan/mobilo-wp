<?php

namespace Barn2\Plugin\WC_Product_Table\Integration;

use Barn2\Plugin\WC_Product_Table\Util\Settings;
use Barn2\WPT_Lib\Registerable;
use Barn2\WPT_Lib\Service;
use Barn2\WPT_Lib\Util as Lib_Util;

/**
 * Handles the WooCommerce Quick View Pro integration.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Quick_View_Pro implements Service, Registerable {

	/**
	 * Register the integrations for Quick View Pro.
	 */
	public function register() {
		if ( ! Lib_Util::is_barn2_plugin_active( '\Barn2\Plugin\WC_Quick_View_Pro\wqv' ) ) {
			return;
		}

		// Plugin settings.
		add_filter( 'wc_product_table_plugin_settings_before_advanced', [ $this, 'add_plugin_settings' ], 50 );
	}

	/**
	 * Open product table links with Quick View Pro?
	 *
	 * @return bool true to open with QVP.
	 */
	public static function open_links_in_quick_view() {
		if ( Lib_Util::is_barn2_plugin_active( '\Barn2\Plugin\WC_Quick_View_Pro\wqv' ) ) {
			$misc_settings = Settings::get_setting_misc();

			return ! empty( $misc_settings['quick_view_links'] );
		}

		return false;
	}

	/**
	 * Add the Quick View Pro plugin settings.
	 *
	 * @param array $settings The list of settings.
	 * @return array The list of settings.
	 */
	public function add_plugin_settings( $settings ) {
		return array_merge(
			$settings,
			[
				[
					'title' => __( 'Quick View Pro', 'woocommerce-product-table' ),
					'type'  => 'title',
					'desc'  => __( 'These opitons control the Quick View Pro extension.', 'woocommerce-product-table' ),
					'id'    => 'product_table_settings_quick_view'
				],
				[
					'title'    => __( 'Product links', 'woocommerce-product-table' ),
					'type'     => 'checkbox',
					'id'       => Settings::OPTION_MISC . '[quick_view_links]',
					'desc'     => __( 'Replace links to the product page with a Quick View', 'woocommerce-product-table' ),
					'desc_tip' => sprintf(
					/* translators: 1: help link start, 2: help link end */
						__( '%1$sLearn how%2$s to correctly configure this option.', 'woocommerce-product-table' ),
						Lib_Util::format_barn2_link_open( 'kb/product-table-quick-view/', true ),
						'</a>'
					),
					'default'  => 'no'
				],
				[
					'type' => 'sectionend',
					'id'   => 'product_table_settings_quick_view'
				]
			]
		);
	}

}
