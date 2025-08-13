<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;
use Objectiv\Plugins\Checkout\Model\AlternativePlugin;
use Objectiv\Plugins\Checkout\Model\DetectedPaymentGateway;
use Objectiv\Plugins\Checkout\Model\GatewaySupport;

class Braintree extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'WC_PAYPAL_BRAINTREE_FILE' );
	}

	public function pre_init() {
		if ( ! $this->is_available() ) {
			return;
		}

		add_filter(
			'cfw_detected_gateways',
			function ( $gateways ) {
				$gateways[] = new DetectedPaymentGateway(
					'Braintree for WooCommerce Payment Gateway',
					GatewaySupport::NOT_SUPPORTED,
					'Gateway does not support Express Checkout at checkout. Switch to <a class="text-blue-600 underline" target="_blank" href="https://wordpress.org/plugins/woo-payment-gateway/">Payment Plugins Braintree.</a>',
					new AlternativePlugin(
						'woo-payment-gateway',
						'Payment Plugins Braintree For WooCommerce'
					)
				);

				return $gateways;
			}
		);
	}

	public function run() {
		remove_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_methods', 10 );
		add_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_methods', 25 );
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$payment_gateways = WC()->payment_gateways->payment_gateways();

		$cc_gateway_available     = isset( $payment_gateways[ \WC_Braintree::CREDIT_CARD_GATEWAY_ID ] ) ? $payment_gateways[ \WC_Braintree::CREDIT_CARD_GATEWAY_ID ]->is_available() : false;
		$paypal_gateway_available = isset( $payment_gateways[ \WC_Braintree::PAYPAL_GATEWAY_ID ] ) ? $payment_gateways[ \WC_Braintree::PAYPAL_GATEWAY_ID ]->is_available() : false;

		$compatibility[] = array(
			'class'  => 'Braintree',
			'params' => array(
				'cc_gateway_available'     => $cc_gateway_available,
				'paypal_gateway_available' => $paypal_gateway_available,
			),
		);

		return $compatibility;
	}
}
