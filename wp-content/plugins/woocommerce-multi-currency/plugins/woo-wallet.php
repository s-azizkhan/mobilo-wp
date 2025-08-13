<?php

/**
 * Class WOOMULTI_CURRENCY_Plugin_Woo_Wallet
 * Plugin: TeraWallet https://wordpress.org/plugins/woo-wallet/
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WOOMULTI_CURRENCY_Plugin_Woo_Wallet {
	protected $settings;

	public function __construct() {
		$this->settings = WOOMULTI_CURRENCY_Data::get_ins();
		add_filter( 'woo_wallet_current_balance', array( $this, 'woo_wallet_current_balance' ), 10, 2 );
		add_filter( 'woo_wallet_amount', array( $this, 'woo_wallet_amount' ), 10, 2 );
		add_filter( 'woo_wallet_rechargeable_amount', array( $this, 'woo_wallet_rechargeable_amount' ) );
		add_filter( 'woo_wallet_cashback_notice_text', array( $this, 'woo_wallet_cashback_notice_text' ), 10, 2 );
		add_filter( 'woo_wallet_new_user_registration_credit_amount', array(
			$this,
			'woo_wallet_new_user_registration_credit_amount'
		), 10, 2 );
	}

	public function woo_wallet_new_user_registration_credit_amount( $amount, $user_id ) {
		return wmc_get_price( $amount );
	}

	public function woo_wallet_current_balance( $wallet_balance, $user_id ) {
		if ( $user_id ) {
			$wallet_balance = 0;
			foreach ( $this->settings->get_list_currencies() as $currency => $currency_data ) {
				$credit_amount  = array_sum( wp_list_pluck( get_wallet_transactions( array(
					'user_id' => $user_id,
					'where'   => array(
						array(
							'key'   => 'type',
							'value' => 'credit'
						),
						array(
							'key'   => 'currency',
							'value' => $currency
						)
					)
				) ), 'amount' ) );
				$debit_amount   = array_sum( wp_list_pluck( get_wallet_transactions( array(
					'user_id' => $user_id,
					'where'   => array(
						array(
							'key'   => 'type',
							'value' => 'debit'
						),
						array(
							'key'   => 'currency',
							'value' => $currency
						)
					)
				) ), 'amount' ) );
				$balance        = $credit_amount - $debit_amount;
				$wallet_balance += ( $balance / $currency_data['rate'] );
			}
			$wallet_balance = wmc_get_price( $wallet_balance );
		}

		return $wallet_balance;
	}

	public function woo_wallet_rechargeable_amount( $amount ) {
		if ( $this->settings->get_current_currency() !== $this->settings->get_default_currency() ) {
			$amount = wmc_revert_price( $amount );
		}

		return $amount;
	}

	public function woo_wallet_amount( $amount, $currency ) {
		$default_currency = $this->settings->get_default_currency();
		if ( is_admin() && ! wp_doing_ajax() ) {
			$list_currencies = $this->settings->get_list_currencies();
			if ( ! empty( $list_currencies[ $currency ]['rate'] ) ) {
				if ( $currency !== $default_currency ) {
					$amount = $amount / $list_currencies[ $currency ]['rate'];
				}
			}
		} else {
			$wmc_current_currency = $this->settings->get_current_currency();
			if ( $currency !== $default_currency ) {
				$amount = wmc_revert_price( $amount, $currency );
			}
			if ( $wmc_current_currency !== $default_currency ) {
				$amount = wmc_get_price( $amount );
			}
		}

		return $amount;
	}

	public function woo_wallet_cashback_notice_text( $text, $cashback_amount ) {
		$cashback_amount = wmc_get_price( $cashback_amount );
		if ( is_user_logged_in() ) {
			$text = sprintf( __( 'Upon placing this order a cashback of %s will be credited to your wallet.', 'woo-wallet' ), wc_price( $cashback_amount, woo_wallet_wc_price_args() ) );
		} else {
			$text = sprintf( __( 'Please <a href="%s">log in</a> to avail %s cashback from this order.', 'woo-wallet' ), esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ), wc_price( $cashback_amount, woo_wallet_wc_price_args() ) );
		}

		return $text;
	}

}