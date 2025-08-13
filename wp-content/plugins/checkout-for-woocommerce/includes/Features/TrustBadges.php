<?php

namespace Objectiv\Plugins\Checkout\Features;

use Objectiv\Plugins\Checkout\Admin\Pages\PageAbstract;
use Objectiv\Plugins\Checkout\Interfaces\SettingsGetterInterface;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 */
class TrustBadges extends FeaturesAbstract {
	protected $trust_badges_field_name;

	/**
	 * TrustBadges constructor.
	 *
	 * @param bool                    $enabled Is feature enabled.
	 * @param bool                    $available Is feature available.
	 * @param string                  $required_plans_list The list of required plans.
	 * @param SettingsGetterInterface $settings_getter The settings getter.
	 * @param string                  $trust_badges_field_name The field name.
	 */
	public function __construct( bool $enabled, bool $available, string $required_plans_list, SettingsGetterInterface $settings_getter, string $trust_badges_field_name ) {
		$this->trust_badges_field_name = $trust_badges_field_name;

		parent::__construct( $enabled, $available, $required_plans_list, $settings_getter );
	}

	protected function run_if_cfw_is_enabled() {
		$trust_badge_items = cfw_get_trust_badges( false );

		if ( empty( $trust_badge_items ) ) {
			return;
		}

		$position = $this->settings_getter->get_setting( 'trust_badge_position' );

		$action = 'cfw_checkout_cart_summary';

		if ( 'below_checkout_form' === $position ) {
			$action = 'woocommerce_after_checkout_form';
		}

		if ( 'in_footer' === $position ) {
			$action = 'cfw_before_footer';
		}

		/**
		 * Filter the action to output the trust badges
		 *
		 * @since 9.0.0
		 * @param string $action The action to output the trust badges
		 * @param string $position The position of the trust badges
		 */
		$action = apply_filters( 'cfw_trust_badges_output_action', $action, $position );

		add_action( $action, array( $this, 'output_trust_badges' ), 71 );
	}

	public function output_trust_badges() {
		?>
		<div id="cfw_trust_badges_list" class="cfw-module cfw-trust-badges-position-<?php echo esc_attr( $this->settings_getter->get_setting( 'trust_badge_position' ) ); ?>">
			<h4 class="cfw-trust-badges-list-title"><?php echo do_shortcode( $this->settings_getter->get_setting( 'trust_badges_title' ) ); ?></h4>

			<div class="cfw-tw">
				<div id="cfw-trust-badges"></div>
			</div>
		</div>
		<?php
	}
}
