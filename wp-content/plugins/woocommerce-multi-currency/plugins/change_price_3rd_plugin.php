<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WOOMULTI_CURRENCY_Plugin_Change_Price_3rd_Plugin {
	protected $settings;

	public function __construct() {
		$this->settings = WOOMULTI_CURRENCY_Data::get_ins();
		if ( $this->settings->get_enable() ) {
			add_action( 'init', array( $this, 'init_hooks' ) );
			add_filter( 'woocommerce_product_addons_option_price_raw', array( $this, 'change_price' ) );
			add_filter( 'wmc_change_3rd_plugin_price', array( $this, 'change_price' ) );
			add_filter( 'wmc_change_raw_price', array( $this, 'change_price' ) );
			// TM extra product option
			add_filter( 'wc_epo_option_price_correction', array( $this, 'revert_price' ) );
			add_filter( 'wc_epo_cs_convert', array( $this, 'change_price' ) );
			add_filter( 'woocommerce_tm_epo_price_on_cart', array( $this, 'change_price' ) );
			add_filter( 'wc_epo_calculate_price', array( $this, 'wc_epo_calculate_price' ), 10, 13 );
			add_filter( 'wc_epo_cart_options_prices', array( $this, 'change_price' ), 10, 2 );
			add_filter( 'wc_epo_get_current_currency_price', array(
				$this,
				'wc_epo_get_current_currency_price'
			), 10, 2 );
//		add_filter( 'wc_epo_add_cart_item_calculated_price1', array( $this, 'revert_price' ) );
//		add_filter( 'wc_epo_add_cart_item_calculated_price2', array( $this, 'change_price' ) );

			// Advanced shipping
			add_filter( 'wcml_shipping_price_amount', array( $this, 'change_price' ), 10 );
//		add_filter( 'wcml_shipping_price_amount', array( $this, 'number_format' ), 11 );

			/*Flexible shipping*/
			add_filter( 'flexible_shipping_value_in_currency', array( $this, 'flexible_shipping_value_in_currency' ) );

			// Discussion on RnB - WooCommerce Booking & Rental Plugin
			add_filter( 'redq_pickup_locations', array( $this, 'redq_change_price' ) );
			add_filter( 'redq_dropoff_locations', array( $this, 'redq_change_price' ) );
			add_filter( 'redq_payable_resources', array( $this, 'redq_change_price' ) );
			add_filter( 'redq_payable_security_deposite', array( $this, 'redq_change_price' ) );
			add_filter( 'redq_rnb_cat_categories', array( $this, 'redq_change_price' ) );
			add_filter( 'redq_payable_person', array( $this, 'redq_person_change_price' ) );
			add_filter( 'wmc_product_get_price_condition', array( $this, 'rnb_plugin_condition' ), 10, 3 );

			/*VillaTheme discount plugin*/
			add_filter( 'viredis_change_3rd_plugin_price', array( $this, 'change_price' ) );

			/*WooCommerce Boost Sales dynamic price*/
			add_filter( 'wbs_crossell_recalculated_price_in_cart', array( $this, 'revert_price' ) );

			/*Bopo – Woo Product Bundle Builder*/
			add_filter( 'bopobb_get_original_price', array( $this, 'bopobb_get_original_price' ) );
			add_filter( 'bopobb_convert_currency_price', array( $this, 'change_price' ) );

			/*Sumo subscriptions*/
			add_filter( 'sumosubscriptions_get_line_total', array( $this, 'revert_price' ), 10 );

			// WooCommerce PDF Vouchers - WordPress Plugin
			add_filter( 'woo_vou_get_product_price', array( $this, 'woo_vou_reverse_price' ), 10, 2 );
		}
	}

	public function flexible_shipping_value_in_currency( $value ) {
		if ( $value && $value !== INF ) {
			$value = wmc_get_price( $value );
		}

		return $value;
	}

	public function init_hooks() {
		// Compatible with WC Name Your Price
		if ( is_callable( array(
				'WC_Name_Your_Price_Compatibility',
				'is_nyp_gte'
			) ) && WC_Name_Your_Price_Compatibility::is_nyp_gte( '3.0' ) ) {
			add_filter( 'wc_nyp_raw_minimum_variation_price', array( $this, 'change_price' ) );
			add_filter( 'wc_nyp_raw_minimum_price', array( $this, 'change_price' ) );
			add_filter( 'wc_nyp_raw_suggested_price', array( $this, 'change_price' ) );
			add_filter( 'wc_nyp_raw_maximum_price', array( $this, 'change_price' ) );
			add_filter( 'wc_nyp_get_posted_price', array( $this, 'wc_nyp_get_posted_price' ), 10, 3 );
			add_filter( 'wc_nyp_price_input_attributes', array( $this, 'wc_nyp_price_input_attributes' ), 10, 3 );
		} else {
			add_filter( 'woocommerce_raw_minimum_variation_price', array( $this, 'change_price' ) );
			add_filter( 'woocommerce_raw_minimum_price', array( $this, 'change_price' ) );
			add_filter( 'woocommerce_raw_suggested_price', array( $this, 'change_price' ) );
			add_filter( 'woocommerce_raw_maximum_price', array( $this, 'change_price' ) );
		}
	}

	public function wc_nyp_price_input_attributes( $args, $product, $suffix ) {
		if ( $args['input_value'] ) {
			$args['input_value'] = wmc_get_price( $args['input_value'] );
		}

		return $args;
	}

	public function wc_nyp_get_posted_price( $posted_price, $product, $suffix ) {
		$posted_price = wmc_revert_price( $posted_price );

		return $posted_price;
	}

	public function bopobb_get_original_price( $price ) {
		if ( ! empty( $price['product_price'] ) ) {
			$price['product_price'] = wmc_revert_price( $price['product_price'] );
		}

		return $price;
	}

	public function wc_epo_calculate_price( $_price, $post_data, $element, $key, $attribute, $per_product_pricing, $cpf_product_price, $variation_id, $price_default_value, $currency, $current_currency, $price_per_currencies, $_price_type ) {
		if ( in_array( $_price_type, array( 'percent', 'percentcurrenttotal' ) ) ) {
			$wmc_current_currency = $this->settings->get_current_currency();
			$default_currency     = $this->settings->get_default_currency();
			if ( $current_currency !== false ) {
				if ( $wmc_current_currency === $default_currency ) {
					$_price = wmc_revert_price( $_price, $current_currency );
				}
			}
		}

		return $_price;
	}

	public function rnb_plugin_condition( $condition, $price, $product ) {
		if ( is_a( $product, 'WC_Product_Redq_Rental' ) ) {
			$condition = false;
		}

		return $condition;
	}

	public function change_price( $price_raw ) {
		return $price_raw ? wmc_get_price( $price_raw ) : $price_raw;
	}

	public function number_format( $price ) {
		$current_currency = $this->settings->get_current_currency();
		$currencies_list  = $this->settings->get_list_currencies();

		return number_format( $price, $currencies_list[ $current_currency ]['decimals'] );
	}

	public function wc_epo_get_current_currency_price( $price, $type ) {
		if ( ! $type && ! is_product() ) {
			$price = wmc_revert_price( $price );
		}

		return $price;
	}

	public function revert_price( $price ) {
		return wmc_revert_price( $price );
	}

	public function redq_person_change_price( $data ) {
		$new_data = $data;
		if ( is_array( $data ) && count( $data ) ) {
			foreach ( $data as $key => $value ) {
				$new_data[ $key ] = $this->redq_change_price( $value );
			}
		}

		return $new_data;
	}

	public function redq_change_price( $data ) {
		$new_data = $data;

		if ( is_array( $data ) && count( $data ) ) {
			foreach ( $data as $el_key => $element ) {
				if ( is_array( $element ) && count( $element ) ) {
					foreach ( $element as $key => $value ) {
						if ( substr( $key, - 4 ) == 'cost' && is_numeric( $value ) ) {
							$new_data[ $el_key ][ $key ] = $this->change_price( $value );
						}
					}
				}
			}
		}

		return $new_data;
	}

	public function woo_vou_reverse_price( $subtotal, $order_id ) {
		$order          = wc_get_order( $order_id );
		$wmc_order_info = get_post_meta( $order_id, 'wmc_order_info', true );
		$order_currency = $order->get_currency();
		$rate           = ! empty( $wmc_order_info[ $order_currency ]['rate'] ) ? $wmc_order_info[ $order_currency ]['rate'] : '';
		$decimals       = ! empty( $wmc_order_info[ $order_currency ]['decimals'] ) ? $wmc_order_info[ $order_currency ]['decimals'] : '';

		$subtotal = $rate ? $subtotal / $rate : $subtotal;
		$subtotal = $decimals ? number_format( $subtotal, $decimals ) : $subtotal;

		return $subtotal;
	}


}
