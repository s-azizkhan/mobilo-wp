<?php

namespace Barn2\Plugin\WC_Product_Table;

use Barn2\Plugin\WC_Product_Table\Admin\Admin_Controller;
use Barn2\Plugin\WC_Product_Table\Admin\Wizard\Setup_Wizard;
use Barn2\Plugin\WC_Product_Table\Util\Settings;
use Barn2\Plugin\WC_Product_Table\Widgets\Active_Filters_Widget;
use Barn2\Plugin\WC_Product_Table\Widgets\Attribute_Filter_Widget;
use Barn2\Plugin\WC_Product_Table\Widgets\Price_Filter_Widget;
use Barn2\Plugin\WC_Product_Table\Widgets\Rating_Filter_Widget;
use Barn2\WPT_Lib\Plugin\Licensed_Plugin;
use Barn2\WPT_Lib\Plugin\Premium_Plugin;
use Barn2\WPT_Lib\Registerable;
use Barn2\WPT_Lib\Service;
use Barn2\WPT_Lib\Service_Container;
use Barn2\WPT_Lib\Service_Provider;
use Barn2\WPT_Lib\Translatable;
use Barn2\WPT_Lib\Util;

/**
 * The main plugin class. Responsible for setting up the core plugin services.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Plugin extends Premium_Plugin implements Licensed_Plugin, Registerable, Translatable, Service_Provider {

	const NAME    = 'WooCommerce Product Table';
	const ITEM_ID = 12913;

	use Service_Container;

	/**
	 * Constructor.
	 *
	 * @param string $file    The main plugin file (__FILE__). This is the file WordPress loads in the plugin root folder.
	 * @param string $version The plugin version string, e.g. '1.2.1'
	 */
	public function __construct( $file, $version = '1.0' ) {
		parent::__construct(
			[
				'name'               => self::NAME,
				'item_id'            => self::ITEM_ID,
				'version'            => $version,
				'file'               => $file,
				'is_woocommerce'     => true,
				'settings_path'      => 'admin.php?page=wc-settings&tab=products&section=' . Settings::SECTION_SLUG,
				'documentation_path' => 'kb-categories/woocommerce-product-table-kb',
				'legacy_db_prefix'   => 'wcpt'
			]
		);
	}

	/**
	 * Registers the plugin hooks (add_action/add_filter).
	 *
	 * @return void
	 */
	public function register() {
		parent::register();

		// We create Plugin_Setup here so the plugin activation hook will run.
		$plugin_setup = new Plugin_Setup( $this );
		$plugin_setup->register();

		add_action( 'plugins_loaded', [ $this, 'maybe_load_plugin' ] );
	}

	/**
	 * Load the plugin if WooCommerce is installed and active. This will hook the main plugin services, such as
	 * loading the text domain, registering widgets, and service classes.
	 *
	 * @hook plugins_loaded
	 * @return void
	 */
	public function maybe_load_plugin() {
		// Bail if WooCommerce not installed & active.
		if ( ! Util::is_woocommerce_active() ) {
			return;
		}

		add_action( 'init', [ $this, 'load_textdomain' ], 5 );
		add_action( 'init', [ $this, 'register_services' ] );
		add_action( 'init', [ $this, 'load_template_functions' ] );
		add_action( 'widgets_init', [ $this, 'register_widgets' ] );
	}

	/**
	 * Get the list of services that the plugin requires.
	 *
	 * @return Service[] The list of services.
	 */
	public function get_services() {
		$services = [
			'admin'  => new Admin_Controller( $this ),
			'wizard' => new Setup_Wizard( $this ),
		];

		if ( ! $this->has_valid_license() ) {
			return $services;
		}

		return array_merge(
			$services,
			[
				'shortcode'          => new Table_Shortcode(),
				'scripts'            => new Frontend_Scripts( $this->get_version() ),
				'cart_handler'       => new Cart_Handler(),
				'ajax_handler'       => new Ajax_Handler(),
				'template_handler'   => new Template_Handler(),
				'theme_compat'       => new Integration\Theme_Integration(),
				'searchwp'           => new Integration\SearchWP(),
				'product_addons'     => new Integration\Product_Addons(),
				'quick_view_pro'     => new Integration\Quick_View_Pro(),
				'variation_swatches' => new Integration\Variation_Swatches(),
				'yith_request_quote' => new Integration\YITH_Request_Quote()
			]
		);
	}

	/**
	 * Load the plugin template functions file.
	 *
	 * @return void
	 */
	public function load_template_functions() {
		require_once $this->get_dir_path() . 'includes/template-functions.php';
	}

	/**
	 * Load the plugin's language files by calling load_plugin_textdomain.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'woocommerce-product-table', false, $this->get_slug() . '/languages' );
	}

	/**
	 * Register the plugin's widgets.
	 *
	 * @return void
	 */
	public function register_widgets() {
		if ( ! $this->get_license()->is_valid() ) {
			return;
		}

		$widget_classes = [
			Active_Filters_Widget::class,
			Attribute_Filter_Widget::class,
			Price_Filter_Widget::class,
			Rating_Filter_Widget::class
		];

		// Register the product table widgets
		array_map( 'register_widget', array_filter( $widget_classes, 'class_exists' ) );
	}

}
