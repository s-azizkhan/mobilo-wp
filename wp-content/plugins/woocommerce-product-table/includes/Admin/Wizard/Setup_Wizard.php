<?php

namespace Barn2\Plugin\WC_Product_Table\Admin\Wizard;

use Barn2\Plugin\WC_Product_Table\Admin\Wizard\Steps\Cart;
use Barn2\Plugin\WC_Product_Table\Admin\Wizard\Steps\Completed;
use Barn2\Plugin\WC_Product_Table\Admin\Wizard\Steps\Display;
use Barn2\Plugin\WC_Product_Table\Admin\Wizard\Steps\Filters;
use Barn2\Plugin\WC_Product_Table\Admin\Wizard\Steps\License_Verification;
use Barn2\Plugin\WC_Product_Table\Admin\Wizard\Steps\Table_Content;
use Barn2\Plugin\WC_Product_Table\Admin\Wizard\Steps\Upsell;
use Barn2\Plugin\WC_Product_Table\Dependencies\Barn2\Setup_Wizard\Setup_Wizard as Wizard;
use Barn2\WPT_Lib\Plugin\License\EDD_Licensing;
use Barn2\WPT_Lib\Plugin\License\Plugin_License;
use Barn2\WPT_Lib\Plugin\Licensed_Plugin;
use Barn2\WPT_Lib\Registerable;

/**
 * Handles general admin functions, such as adding links to our settings page in the Plugins menu.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Setup_Wizard implements Registerable {

	/**
	 * Plugin instance
	 *
	 * @var Licensed_Plugin
	 */
	private $plugin;

	/**
	 * Wizard instance
	 *
	 * @var Wizard
	 */
	private $wizard;

	/**
	 * Setup the setup wizard. Pun intended.
	 *
	 * @param Licensed_Plugin $plugin
	 */
	public function __construct( Licensed_Plugin $plugin ) {

		$this->plugin = $plugin;

		$steps = [
			new License_Verification(),
			new Display(),
			new Table_Content(),
			new Cart(),
			new Filters(),
			new Upsell(),
			new Completed(),
		];

		$wizard = new Wizard( $this->plugin, $steps );

		$wizard->configure(
			[
				'skip_url'        => admin_url( 'admin.php?page=wc-settings&tab=products&section=product-table' ),
				'license_tooltip' => esc_html__( 'The licence key is contained in your order confirmation email.', 'woocommerce-product-table' ),
				'utm_id'          => 'wpt',
			]
		);

		$wizard->add_edd_api( EDD_Licensing::class );
		$wizard->add_license_class( Plugin_License::class );
		$wizard->add_restart_link( 'product-table', 'product_table_settings_license' );

		$this->wizard = $wizard;
	}

	/**
	 * Boot the wizard.
	 *
	 * @return void
	 */
	public function register() {
		$this->wizard->boot();
	}

}