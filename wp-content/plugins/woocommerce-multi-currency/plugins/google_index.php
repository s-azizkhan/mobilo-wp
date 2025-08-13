<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 30-09-19
 * Time: 8:18 AM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//$_SERVER['HTTP_USER_AGENT']='/google.com';

class WOOMULTI_CURRENCY_Plugin_Google_Index {
	protected $settings;

	public function __construct() {
		$this->settings = WOOMULTI_CURRENCY_Data::get_ins();
		if ( $this->settings->get_enable() ) {
			add_action( 'init', array( $this, 'set_currency_for_bot' ), 999 );
		}
	}

	public function set_currency_for_bot() {
		$bot_currency = false;
		if ( $this->is_google_bot() ) {
			$bot_currency = apply_filters( 'wmc_set_currency_for_google_bot_index', $this->get_bot_currency() );
		} elseif ( $this->isBot() ) {
			$bot_currency = apply_filters( 'wmc_set_currency_for_bot_index', $this->get_bot_currency() );
		}
		if ( $bot_currency !== false ) {
			if ( $bot_currency ) {
				$this->settings->set_current_currency( $bot_currency );
			} else {
				$this->settings->set_fallback_currency();
			}
		}
	}

	private function get_bot_currency() {
		$bot_currency    = $this->settings->get_params( 'bot_currency' );
		$list_currencies = $this->settings->get_list_currencies();
		if ( $bot_currency === 'default_currency' ) {
			$bot_currency = $this->settings->get_default_currency();
		} elseif ( $bot_currency ) {
			if ( empty( $list_currencies[ $bot_currency ] ) ) {
				$bot_currency = '';
			}
		}
		$passed_currency = '';
		if ( ! empty( $_GET['wmc-currency'] ) ) {
			$passed_currency = sanitize_text_field( $_GET['wmc-currency'] );
		} elseif ( ! empty( $_GET['currency'] ) ) {
			$passed_currency = sanitize_text_field( $_GET['currency'] );
		}
		if ( $passed_currency ) {
			if ( ! empty( $list_currencies[ $passed_currency ] ) ) {
				$bot_currency = $passed_currency;
			}
		}

		return $bot_currency;
	}

	public function is_google_bot() {
		$google_bots = apply_filters( 'wmc_google_bots_list', array(
			'googlebot',
			'google-sitemaps',
			'appEngine-google',
			'feedfetcher-google',
			'googlealert.com',
			'AdsBot-Google',
			'google'
		) );
		foreach ( $google_bots as $bot ) {
			if ( self::check_bot( $bot ) ) {
				return true;
			}
		}

		return false;
	}

	public function isBot() {
		$bots = apply_filters( 'wmc_other_bots_list', array(
//			'pixel',//confused with google pixel device
			'facebook',
			'rambler',
			'aport',
			'yahoo',
			'msnbot',
			'turtle',
			'mail.ru',
			'omsktele',
			'yetibot',
			'picsearch',
			'sape.bot',
			'sape_context',
			'gigabot',
			'snapbot',
			'alexa.com',
			'megadownload.net',
			'askpeter.info',
			'igde.ru',
			'ask.com',
			'qwartabot',
			'yanga.co.uk',
			'scoutjet',
			'similarpages',
			'oozbot',
			'shrinktheweb.com',
			'aboutusbot',
			'followsite.com',
			'dataparksearch',
			'liveinternet.ru',
			'xml-sitemaps.com',
			'agama',
			'metadatalabs.com',
			'h1.hrn.ru',
			'seo-rus.com',
			'yaDirectBot',
			'yandeG',
			'yandex',
			'yandexSomething',
			'Copyscape.com',
			'domaintools.com',
			'Nigma.ru',
			'bing.com',
			'dotnetdotcom',
		) );
		foreach ( $bots as $bot ) {
			if ( self::check_bot( $bot ) ) {
				return true;
			}
		}

		return false;
	}

	private static function check_bot( $bot ) {
		return isset( $_SERVER['HTTP_USER_AGENT'] ) && ( stripos( $_SERVER['HTTP_USER_AGENT'], $bot ) !== false || preg_match( '/bot|crawl|slurp|spider|mediapartners/i', $_SERVER['HTTP_USER_AGENT'] ) );
	}
}