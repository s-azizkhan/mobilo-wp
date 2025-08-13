<?php

namespace Barn2\Plugin\WC_Product_Table;

use Barn2\Plugin\WC_Product_Table\Dependencies\Barn2\Setup_Wizard\Starter;
use Barn2\WPT_Lib\Plugin\Licensed_Plugin;
use Barn2\WPT_Lib\Plugin\Plugin_Activation_Listener;
use Barn2\WPT_Lib\Registerable;

/**
 * Handles the setup of the plugin setup wizard.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Plugin_Setup implements Plugin_Activation_Listener, Registerable {

	/**
	 * Plugin's entry file.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * Wizard starter.
	 *
	 * @var Starter
	 */
	private $starter;

	/**
	 * Plugin object.
	 *
	 * @var Licensed_Plugin
	 */
	private $plugin;

	/**
	 * Constructor.
	 *
	 * @param Licensed_Plugin $plugin The plugin object.
	 */
	public function __construct( Licensed_Plugin $plugin ) {
		$this->plugin  = $plugin;
		$this->file    = $plugin->get_file();
		$this->starter = new Starter( $this->plugin );
	}

	/**
	 * Register the service
	 *
	 * @return void
	 */
	public function register() {
		register_activation_hook( $this->file, [ $this, 'on_activate' ] );
		add_action( 'admin_init', [ $this, 'after_plugin_activation' ] );
	}

	/**
	 * On plugin activation determine if the setup wizard should run.
	 *
	 * @return void
	 */
	public function on_activate() {
		$this->setup();
	}

	/**
	 * Do nothing on deactivation.
	 *
	 * @return void
	 */
	public function on_deactivate() {}

	/**
	 * Maybe create the transient for the setup wizard.
	 */
	public function setup() {
		if ( $this->starter->should_start() ) {
			$this->starter->create_transient();
		}
	}

	/**
	 * Detect the transient and redirect to wizard.
	 *
	 * @return void
	 */
	public function after_plugin_activation() {
		if ( ! $this->starter->detected() ) {
			return;
		}

		$this->starter->delete_transient();
		$this->starter->redirect();
	}
}
