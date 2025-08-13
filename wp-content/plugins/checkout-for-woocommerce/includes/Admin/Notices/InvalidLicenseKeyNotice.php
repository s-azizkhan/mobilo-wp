<?php

namespace Objectiv\Plugins\Checkout\Admin\Notices;

use Objectiv\Plugins\Checkout\Managers\UpdatesManager;

class InvalidLicenseKeyNotice extends NoticeAbstract {
	public function maybe_add( string $id, string $title, string $message, array $options = array() ) {
		$key_status  = UpdatesManager::instance()->get_field_value( 'key_status' );
		$license_key = UpdatesManager::instance()->get_field_value( 'license_key' );

		if ( 'expired' === $key_status ) {
			$message = sprintf(
				__( 'Your license key appears to have expired. Please <a href="%s" target="_blank">%s</a> to restore functionality. If you believe this is in error, <a href="%s">%s</a>.', 'checkout-wc' ),
				'https://www.checkoutwc.com/pricing',
				__( 'purchase a new license', 'checkout-wc' ),
				'mailto:support@checkoutwc.com?subject=Problem%20With%20License%20Expiration&body=License%20Key%3A%20' . $license_key,
				__( 'please contact support', 'checkout-wc' )
			);
		}

		parent::maybe_add( $id, $title, $message, $options );
	}

	protected function should_add(): bool {
		$key_status  = UpdatesManager::instance()->get_field_value( 'key_status' );
		$license_key = UpdatesManager::instance()->get_field_value( 'license_key' );

		if ( ! empty( $_GET['page'] ) && 'cfw-settings' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}

		if ( ! empty( $license_key ) && 'valid' === $key_status ) {
			return false;
		}

		if ( ! empty( $license_key ) && 'site_inactive' === $key_status ) {
			return false;
		}

		return true;
	}
}
