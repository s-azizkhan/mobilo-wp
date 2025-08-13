<?php

namespace Barn2\Plugin\WC_Product_Table\Integration;

use Automattic\Jetpack\Constants;
use Barn2\Plugin\WC_Product_Table\Cart_Handler;
use Barn2\Plugin\WC_Product_Table\Table_Args;
use Barn2\Plugin\WC_Product_Table\Util\Settings;
use Barn2\Plugin\WC_Product_Table\Util\Util;
use Barn2\WPT_Lib\Registerable;
use Barn2\WPT_Lib\Service;
use Barn2\WPT_Lib\Util as Lib_Util;
use Exception;
use WC_Product;
use WC_Product_Addons_Helper;
use WP_Scoped_Hooks;

/**
 * Handles the WooCommerce Product Addons integration.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Product_Addons implements Service, Registerable {

	private const MULTI_FIELD_NAME_PREFIX = 'addon-';

	/**
	 * Register the integrations for Product Addons.
	 */
	public function register() {
		if ( ! Lib_Util::is_product_addons_active() ) {
			return;
		}

		// Load the Addons scripts.
		add_action( 'wc_product_table_load_table_scripts', [ $this, 'load_scripts' ] );

		// Cart classes.
		add_filter( 'wc_product_table_add_to_cart_class', [ $this, 'add_to_cart_class' ] );

		// Table hooks.
		add_action( 'wc_product_table_hooks_before_register', [ $this, 'register_table_hooks' ] );

		// Handle multi add to cart.
		add_action( 'wc_product_table_before_add_to_cart_multi', [ $this, 'before_multi_add_to_cart' ] );
		add_filter( 'wc_product_table_multi_add_to_cart_data', [ $this, 'multi_add_to_cart_data' ], 10, 2 );

		// Plugin settings.
		add_filter( 'wc_product_table_plugin_settings_before_advanced', [ $this, 'add_plugin_settings' ], 40 );

		// Workaround for bug in Addons 4.7.0 with Storefront theme (causes JS bug).
		if ( defined( 'WC_PRODUCT_ADDONS_VERSION' ) && '4.7.0' === WC_PRODUCT_ADDONS_VERSION ) {
			add_filter( 'storefront_handheld_footer_bar_links', [ $this, 'storefront_remove_handheld_footer_bar_cart_link' ] );
		}
	}

	public function load_scripts( Table_Args $args ) {
		// Addons scripts only needed if add to cart column present.
		if ( ! in_array( 'buy', $args->columns, true ) ) {
			return;
		}

		// Next check the function to register the scripts exists.
		if ( ! isset( $GLOBALS['Product_Addon_Display'] ) || ! method_exists( $GLOBALS['Product_Addon_Display'], 'addon_scripts' ) ) {
			return;
		}

		// Product Addons has a dependency on jquery tipTip which isn't passed to the deps array for the addons script, so we need to load it.
		if ( ! wp_script_is( 'jquery-tiptip', 'registered' ) ) {
			$wc_version = Constants::get_constant( 'WC_VERSION' );
			wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', [ 'jquery' ], $wc_version, true );
		}

		wp_enqueue_script( 'jquery-tiptip' );

		// Next check if the Addons script is already queued.
		if ( wp_script_is( 'woocommerce-addons', 'enqueued' ) ) {
			return;
		}

		$GLOBALS['Product_Addon_Display']->addon_scripts();
	}

	public function add_to_cart_class( $cart_class ) {
		$misc_settings = Settings::get_setting_misc();

		if ( ! empty( $misc_settings['addons_layout'] ) ) {
			$cart_class[] = esc_attr( 'addons-' . $misc_settings['addons_layout'] );
		}

		if ( ! empty( $misc_settings['addons_option_layout'] ) ) {
			$cart_class[] = esc_attr( 'addons-options-' . $misc_settings['addons_option_layout'] );
		}

		return $cart_class;
	}

	public function register_table_hooks( WP_Scoped_Hooks $hooks ) {
		// Adjust template for <select> type product addons.
		$hooks->add_filter( 'wc_get_template', [ $this, 'load_product_addons_template' ], 10, 5 );

		// Reset the product add-ons hooks after displaying add-ons for variable products.
		$hooks->add_action( 'woocommerce_after_variations_form', [ $this, 'reset_display_hooks' ] );

		if ( isset( $GLOBALS['Product_Addon_Display'] ) ) {
			// Don't show addons grand total in the cart column.
			$hooks->add_filter( 'woocommerce_product_addons_show_grand_total', '__return_false' );
		}
	}

	/**
	 * Load any custom templates for WooCommerce Product Addons. Templates are located under /templates/addons/
	 */
	public function load_product_addons_template( $located, $template_name, $args, $template_path, $default_path ) {
		if ( 'woocommerce-product-addons' === $template_path ) {
			$template = Util::get_template_path() . $template_name;

			if ( file_exists( $template ) ) {
				$located = $template;
			}
		}

		return $located;
	}

	public function reset_display_hooks() {
		// Product Addons moves addons for variable products to the 'single variation' element and removes the
		// 'woocommerce_before_add_to_cart_button hook'. We need to re-add this hook to make the addons appear in the
		// correct place in the table.
		if ( isset( $GLOBALS['Product_Addon_Display'] ) &&
			 false === has_action( 'woocommerce_before_add_to_cart_button', [ $GLOBALS['Product_Addon_Display'], 'display' ] ) ) {

			add_action( 'woocommerce_before_add_to_cart_button', [ $GLOBALS['Product_Addon_Display'], 'display' ], 10 );
		}
	}

	public function before_multi_add_to_cart( $products ) {
		// If using Product Addons, we need to remove and add some filters to process the multi cart data correctly.
		if ( isset( $GLOBALS['Product_Addon_Cart'] ) ) {
			remove_filter( 'woocommerce_add_cart_item_data', [ $GLOBALS['Product_Addon_Cart'], 'add_cart_item_data' ], 10 );
			add_filter( 'woocommerce_add_cart_item_data', [ $this, 'cart_item_data_wrapper' ], 10, 2 );

			remove_filter( 'woocommerce_add_to_cart_validation', [ $GLOBALS['Product_Addon_Cart'], 'validate_add_cart_item' ], 999 );
			add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'validate_cart_item' ], 999, 3 );
		}
	}

	public function multi_add_to_cart_data( array $data, WC_Product $product ) {
		$product_addons = $this->get_product_addons( $product->get_id() );

		if ( ! $product_addons ) {
			return $data;
		}

		foreach ( $product_addons as $addon ) {
			$key = self::MULTI_FIELD_NAME_PREFIX . $addon['field_name'];

			if ( 'checkbox' === $addon['type'] ) {
				if ( ! empty( $addon['options'] ) ) {
					foreach ( $addon['options'] as $option_key => $option ) {
						$sub_key          = $key . '[' . $option_key . ']';
						$data[ $sub_key ] = '';
					}
				}
			} else {
				$data[ $key ] = '';
			}
		}

		return $data;
	}

	public function cart_item_data_wrapper( $cart_item_data, $product_id ) {
		$cart_data = Cart_Handler::get_multi_cart_data();

		if ( isset( $cart_data[ $product_id ] ) && is_array( $cart_data[ $product_id ] ) ) {
			$post_data = $cart_data[ $product_id ];
		} else {
			return $cart_item_data;
		}

		return $this->add_cart_item_data( $cart_item_data, $product_id, $post_data );
	}

	private function add_cart_item_data( $cart_item_data, $product_id, $post_data ) {
		if ( ! defined( 'WC_PRODUCT_ADDONS_PLUGIN_PATH' ) || empty( $post_data ) ) {
			return $cart_item_data;
		}

		if ( empty( $cart_item_data['addons'] ) ) {
			$cart_item_data['addons'] = [];
		}

		$product_addons = $this->get_product_addons( $product_id );

		if ( $product_addons ) {
			include_once WC_PRODUCT_ADDONS_PLUGIN_PATH . '/includes/fields/abstract-wc-product-addons-field.php';

			foreach ( $product_addons as $addon ) {
				// If type is heading, skip.
				if ( 'heading' === $addon['type'] ) {
					continue;
				}

				$field_name = self::MULTI_FIELD_NAME_PREFIX . $addon['field_name'];
				$value      = isset( $post_data[ $field_name ] ) ? $post_data[ $field_name ] : '';

				if ( is_array( $value ) ) {
					$value = array_map( 'stripslashes', $value );
				} else {
					$value = stripslashes( $value );
				}

				switch ( $addon['type'] ) {
					case 'checkbox':
						include_once WC_PRODUCT_ADDONS_PLUGIN_PATH . '/includes/fields/class-wc-product-addons-field-list.php';
						$field = new \WC_Product_Addons_Field_List( $addon, $value );
						break;
					case 'multiple_choice':
						switch ( $addon['display'] ) {
							case 'radiobutton':
								include_once WC_PRODUCT_ADDONS_PLUGIN_PATH . '/includes/fields/class-wc-product-addons-field-list.php';
								$field = new \WC_Product_Addons_Field_List( $addon, $value );
								break;
							case 'images':
							case 'select':
								include_once WC_PRODUCT_ADDONS_PLUGIN_PATH . '/includes/fields/class-wc-product-addons-field-select.php';
								$field = new \WC_Product_Addons_Field_Select( $addon, $value );
								break;
						}
						break;
					case 'custom_text':
					case 'custom_textarea':
					case 'custom_price':
					case 'input_multiplier':
						include_once WC_PRODUCT_ADDONS_PLUGIN_PATH . '/includes/fields/class-wc-product-addons-field-custom.php';
						$field = new \WC_Product_Addons_Field_Custom( $addon, $value );
						break;
					case 'file_upload':
						include_once WC_PRODUCT_ADDONS_PLUGIN_PATH . '/includes/fields/class-wc-product-addons-field-file-upload.php';
						$field = new \WC_Product_Addons_Field_File_Upload( $addon, $value );
						break;
				}

				$data = $field->get_cart_item_data();

				if ( is_wp_error( $data ) ) {
					// Throw exception for add_to_cart to pickup.
					throw new Exception( $data->get_error_message() );
				} elseif ( $data ) {
					$cart_item_data['addons'] = array_merge( $cart_item_data['addons'], apply_filters( 'woocommerce_product_addon_cart_item_data', $data, $addon, $product_id, $post_data ) );
				}
			}
		}

		return $cart_item_data;
	}

	public function validate_cart_item( $passed, $product_id, $qty ) {
		if ( ! isset( $GLOBALS['Product_Addon_Cart'] ) ) {
			return $passed;
		}

		$cart_data = Cart_Handler::get_multi_cart_data();

		if ( isset( $cart_data[ $product_id ] ) && is_array( $cart_data[ $product_id ] ) ) {
			// Get the posted data for this product.
			$post_data = $cart_data[ $product_id ];

			// Fetch all addons for the product.
			$product_addons = $this->get_product_addons( $product_id );

			if ( $product_addons ) {
				// Product has 1 or more addons.
				foreach ( $product_addons as $addon ) {
					// Loop to find any checkbox addons.
					$post_data_addon_name = self::MULTI_FIELD_NAME_PREFIX . $addon['field_name'];

					// For checkbox addons, we remove any unselected options (value = '') from the posted data, so that the validation in WC_Product_Addons_Field_List works correctly.
					if ( 'checkbox' === $addon['type'] && isset( $post_data[ $post_data_addon_name ] ) && is_array( $post_data[ $post_data_addon_name ] ) ) {
						$post_data[ $post_data_addon_name ] = array_filter(
							$post_data[ $post_data_addon_name ],
							function ( $value ) {
								return '' !== $value;
							}
						);
					}
				}

				$passed = $GLOBALS['Product_Addon_Cart']->validate_add_cart_item( $passed, $product_id, $qty, $post_data );
			}
		}

		return $passed;
	}

	/**
	 * Add the Product Addons plugin settings.
	 *
	 * @param array $settings The list of settings.
	 * @return array The list of settings.
	 */
	public function add_plugin_settings( $settings ) {
		return array_merge(
			$settings,
			[
				[
					'title' => __( 'Product Addons', 'woocommerce-product-table' ),
					'type'  => 'title',
					'desc'  => __( 'These options control the Product Addons extension.', 'woocommerce-product-table' ),
					'id'    => 'product_table_settings_addons'
				],
				[
					'title'    => __( 'Addons layout', 'woocommerce-product-table' ),
					'type'     => 'select',
					'options'  => [
						'block'  => __( 'Vertical', 'woocommerce-product-table' ),
						'inline' => __( 'Horizontal', 'woocommerce-product-table' ),
					],
					'id'       => Settings::OPTION_MISC . '[addons_layout]',
					'desc_tip' => __( 'Should product addons display horizontally or vertically within the table?', 'woocommerce-product-table' ),
					'default'  => 'block',
					'class'    => 'wc-enhanced-select'
				],
				[
					'title'    => __( 'Addon options layout', 'woocommerce-product-table' ),
					'type'     => 'select',
					'options'  => [
						'block'  => __( 'Vertical', 'woocommerce-product-table' ),
						'inline' => __( 'Horizontal', 'woocommerce-product-table' ),
					],
					'id'       => Settings::OPTION_MISC . '[addons_option_layout]',
					'desc_tip' => __( 'Should individual options for each addon display horizontally or vertically?', 'woocommerce-product-table' ),
					'default'  => 'block',
					'class'    => 'wc-enhanced-select'
				],
				[
					'type' => 'sectionend',
					'id'   => 'product_table_settings_addons'
				]
			]
		);
	}

	/**
	 * Remove 'cart' from mobile menu as it conflicts with Product Addons 4.7.0.
	 *
	 * @param array $links The links.
	 * @return array The links.
	 */
	public function storefront_remove_handheld_footer_bar_cart_link( $links ) {
		if ( isset( $links['cart'] ) ) {
			unset( $links['cart'] );
		}
		return $links;
	}

	/**
	 * Get the addons for the specified product.
	 *
	 * @param int $product_id The product ID.
	 * @return array The product addons, or an empty array if no addons.
	 */
	private function get_product_addons( $product_id ) {
		if ( ! method_exists( 'WC_Product_Addons_Helper', 'get_product_addons' ) ) {
			return [];
		}

		return WC_Product_Addons_Helper::get_product_addons( $product_id );
	}

}
