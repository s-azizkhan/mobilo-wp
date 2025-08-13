<?php
/**
 * The main plugin file for WooCommerce Product Table.
 *
 * This file is included during the WordPress bootstrap process if the plugin is active.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 *
 * @wordpress-plugin
 * Plugin Name:     WooCommerce Product Table
 * Plugin URI:      https://barn2.com/wordpress-plugins/woocommerce-product-table/
 * Update URI:      https://barn2.com/wordpress-plugins/woocommerce-product-table/
 * Description:     Display and purchase WooCommerce products from a searchable and sortable table. Filter by anything.
 * Version:         3.0.6
 * Author:          Barn2 Plugins
 * Author URI:      https://barn2.com
 * Text Domain:     woocommerce-product-table
 * Domain Path:     /languages
 *
 * WC requires at least: 5.9.0
 * WC tested up to: 7.5.1
 *
 * Copyright:       Barn2 Media Ltd
 * License:         GNU General Public License v3.0
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Barn2\Plugin\WC_Product_Table;

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PLUGIN_FILE    = __FILE__;
const PLUGIN_VERSION = '3.0.6';

// Include autoloader.
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Helper function to access the shared plugin instance.
 *
 * @return Plugin The plugin instance.
 */
function wpt() {
	return Plugin_Factory::create( PLUGIN_FILE, PLUGIN_VERSION );
}

// Load the plugin.
wpt()->register();
