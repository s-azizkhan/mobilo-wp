<?php

namespace Barn2\Plugin\WC_Product_Table;

use Barn2\Plugin\WC_Product_Table\Util\Util;
use WC_Product;
use WC_Query;
use WP_Scoped_Hooks;

/**
 * Responsible for managing the actions and filter hooks for an individual product table.
 *
 * Hooks are registered in a temporary hook environment (@see class WP_Scoped_Hooks), and only
 * apply while the data is loaded into the table.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Hook_Manager extends WP_Scoped_Hooks {

	public $args;

	private $template_loader;

	public function __construct( Table_Args $args ) {
		parent::__construct();
		$this->args            = $args;
		$this->template_loader = Template_Loader_Factory::create();
	}

	public function register() {
		// Maybe add target="_blank" for add to cart buttons
		$this->add_filter( 'woocommerce_loop_add_to_cart_link', [ Util::class, 'format_loop_add_to_cart_link' ] );

		// Adjust class for button when using loop add to cart template
		$this->add_filter( 'woocommerce_loop_add_to_cart_args', [ $this, 'loop_add_to_cart_args' ] );

		// Remove srcset and sizes for images in table as they don't apply (to reduce bandwidth)
		$this->add_filter( 'wp_get_attachment_image_attributes', [ $this, 'remove_image_srcset' ] );

		// Filter stock HTML
		$this->add_filter( 'woocommerce_get_stock_html', [ $this, 'get_stock_html' ], 10, 2 );

		// Wrap quantity and add to cart button with extra div
		$this->add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'before_add_to_cart_button' ], 30 );
		$this->add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'after_add_to_cart_button' ] );

		// Override the 'add to cart' form action for each product.
		$this->add_filter( 'woocommerce_add_to_cart_form_action', [ $this, 'add_to_cart_form_action' ] );

		if ( 'dropdown' === $this->args->variations ) {
			// Move variation description, price & stock below the add to cart button and variations.
			$this->remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation', 10 );
			$this->add_action( 'woocommerce_after_variations_form', 'woocommerce_single_variation' );

			// Use custom template for the add to cart area for variable products.
			$this->remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
			$this->add_action( 'woocommerce_variable_add_to_cart', [ $this, 'woocommerce_variable_add_to_cart' ], 30 );

			// Set image variation props
			$this->add_filter( 'woocommerce_available_variation', [ $this, 'variations_dropdown_set_variation_image_props' ], 10, 3 );
		} elseif ( 'separate' === $this->args->variations ) {
			// Custom add to cart for separate variations.
			$this->add_action( 'woocommerce_variation_add_to_cart', [ $this, 'woocommerce_variation_add_to_cart' ], 30 );
			$this->add_action( 'woocommerce_get_children', [ $this, 'variations_separate_remove_filtered' ], 10, 3 );
		}

		if ( $this->args->shortcodes ) {
			$this->add_filter( 'wc_product_table_data_custom_field', 'do_shortcode' );
		} else {
			$this->remove_filter( 'woocommerce_short_description', 'do_shortcode', 11 );
		}

		// Replace WP audio/video/playlist classes with custom versions, to prevent wpmediaelement & wpplaylist
		// scripts running on first page load. We control the loading of the media elements ourselves in the onDraw event.
		$this->add_filter( 'wc_product_table_data_summary', [ $this, 'set_custom_video_playlist_class' ] );
		$this->add_filter( 'wc_product_table_data_description', [ $this, 'set_custom_video_playlist_class' ] );
		$this->add_filter( 'wc_product_table_data_custom_field', [ $this, 'set_custom_video_playlist_class' ] );
		$this->add_filter( 'wp_video_shortcode_class', [ $this, 'set_custom_video_shortcode_class' ] );
		$this->add_filter( 'wp_audio_shortcode_class', [ $this, 'set_custom_audio_shortcode_class' ] );

		do_action( 'wc_product_table_hooks_before_register', $this );

		parent::register();

		do_action( 'wc_product_table_hooks_after_register', $this );
	}

	public function get_stock_html( $html, $product = false ) {
		if ( ! $product ) {
			return $html;
		}

		$types_to_check = ( 'dropdown' === $this->args->variations ) ? [ 'variable', 'variation' ] : [ 'variable' ];

		// Hide stock text in add to cart column, unless it's out of stock or a variable product
		if ( ! in_array( $product->get_type(), $types_to_check ) && $product->is_in_stock() ) {
			$html = '';
		}

		return apply_filters( 'wc_product_table_stock_html', $html, $product );
	}

	public function loop_add_to_cart_args( $args ) {
		if ( isset( $args['class'] ) ) {
			if ( false === strpos( $args['class'], 'alt' ) ) {
				$args['class'] = $args['class'] . ' alt';
			}
			if ( ! $this->args->ajax_cart ) {
				$args['class'] = str_replace( ' ajax_add_to_cart', '', $args['class'] );
			}
		}
		return $args;
	}

	/**
	 * Return a blank action for add to cart forms in the product table. This allows any non-AJAX actions to return back to the current page.
	 *
	 * @param string $url
	 * @return string The URL
	 */
	public function add_to_cart_form_action( $url ) {
		return '';
	}

	public function remove_image_srcset( $attr ) {
		unset( $attr['srcset'] );
		unset( $attr['sizes'] );
		return $attr;
	}

	public function before_add_to_cart_button() {
		echo '<div class="add-to-cart-button">';
	}

	public function after_add_to_cart_button() {
		echo '</div>';
	}

	public function set_custom_video_playlist_class( $data ) {
		if ( false !== strpos( $data, '<div class="wp-playlist ' ) ) {
			$data = str_replace( '<div class="wp-playlist ', '<div class="wcpt-playlist ', $data );
		}

		return $data;
	}

	public function set_custom_video_shortcode_class( $class ) {
		return str_replace( 'wp-video-shortcode', 'wcpt-video-shortcode', $class );
	}

	public function set_custom_audio_shortcode_class( $class ) {
		return str_replace( 'wp-audio-shortcode', 'wcpt-audio-shortcode', $class );
	}

	/**
	 * The add to cart template for variable products (when using dropdowns for variations).
	 *
	 * @global WC_Product $product
	 */
	public function woocommerce_variable_add_to_cart() {
		$this->template_loader->load_template( 'add-to-cart/variable.php' );
	}

	/**
	 * The add to cart template for variation products (when listing one variation per row).
	 *
	 * @global WC_Product $product
	 */
	public function woocommerce_variation_add_to_cart() {
		$this->template_loader->load_template( 'add-to-cart/variation.php' );
	}

	public function variations_dropdown_set_variation_image_props( $variation_data, $product, $variation ) {
		if ( empty( $variation_data['image'] ) || ! is_array( $variation_data['image'] ) ) {
			return $variation_data;
		}

		// Replace thumb with correct size needed for table
		if ( ! empty( $variation_data['image']['thumb_src'] ) ) {
			$thumb = wp_get_attachment_image_src( $variation->get_image_id(), $this->args->image_size );

			if ( is_array( $thumb ) && $thumb ) {
				$variation_data['image']['thumb_src']   = $thumb[0];
				$variation_data['image']['thumb_src_w'] = $thumb[1];
				$variation_data['image']['thumb_src_h'] = $thumb[2];
			}
		}

		// Caption fallback
		if ( empty( $variation_data['image']['caption'] ) ) {
			$variation_data['image']['caption'] = trim( strip_tags( Util::get_product_name( $product ) ) );
		}

		return $variation_data;
	}

	/**
	 * When using separate variation rows with the layered nav widgets, we need to filter out variations which don't match the current search criteria.
	 *
	 * @param array      $child_ids
	 * @param WC_Product $product
	 * @param boolean    $visible_only
	 * @return array
	 */
	public function variations_separate_remove_filtered( $child_ids, $product = false, $visible_only = false ) {
		if ( ! $child_ids || ! is_array( $child_ids ) ) {
			return $child_ids;
		}

		$child_products = array_filter( array_map( 'wc_get_product', $child_ids ) );

		if ( empty( $child_products ) ) {
			return $child_ids;
		}

		$hide_out_of_stock = 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' );
		$min_price         = filter_input( INPUT_GET, 'min_price', FILTER_VALIDATE_FLOAT );
		$max_price         = filter_input( INPUT_GET, 'max_price', FILTER_VALIDATE_FLOAT );
		$chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();

		if ( ! $hide_out_of_stock && ! is_float( $min_price ) && ! is_float( $max_price ) && ! $chosen_attributes ) {
			return $child_ids;
		}

		foreach ( $child_products as $key => $child_product ) {
			$child_attributes = $child_product->get_attributes();

			if ( $hide_out_of_stock && ! $child_product->is_in_stock() ) {
				unset( $child_ids[ $key ] );
				continue;
			}

			if ( $chosen_attributes ) {
				foreach ( $chosen_attributes as $attribute => $chosen_attribute ) {
					if ( isset( $child_attributes[ $attribute ] ) && ! empty( $chosen_attribute['terms'] ) ) {
						if ( ! in_array( $child_attributes[ $attribute ], $chosen_attribute['terms'] ) ) {
							unset( $child_ids[ $key ] );
							continue 2;
						}
					}
				}
			}

			if ( is_float( $min_price ) || is_float( $max_price ) ) {
				$price = (float) $child_product->get_price();

				if ( ( is_float( $min_price ) && $price < $min_price ) || ( is_float( $max_price ) && $price > $max_price ) ) {
					unset( $child_ids[ $key ] );
					continue;
				}
			}
		} // foreach product

		return array_values( $child_ids );
	}

}
