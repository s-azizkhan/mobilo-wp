<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'Order_Form_Helpers' ) ) {

    /**
     * Model that houses plugin helper functions.
     *
     * @since 1.16
     */
    final class Order_Form_Helpers {

        /**
         * Utility function that determines if a plugin is active or not.
         *
         * @since 1.16
         * @access public
         *
         * @param string $plugin_basename Plugin base name. Ex. woocommerce/woocommerce.php
         * @return boolean True if active, false otherwise.
         */
        public static function is_plugin_active( $plugin_basename ) {

            // Makes sure the plugin is defined before trying to use it
            if ( !function_exists( 'is_plugin_active' ) )
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

            return is_plugin_active( $plugin_basename );

        }

        /**
         * Get plugin data.
         *
         * @since 1.16
         * @access public
         *
         * @oaran string $plugin_basename Plugin basename.
         * @return array Array of data about the current woocommerce installation.
         */
        public static function get_plugin_data( $plugin_basename ) {

            if ( ! function_exists( 'get_plugin_data' ) )
                require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

            return get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_basename );

        }

        /**
         * Get data about the current woocommerce installation.
         *
         * @since 1.16
         * @access public
         *
         * @return array
         */
        public static function get_woocommerce_data() {

            return self::get_plugin_data( 'woocommerce/woocommerce.php' );

        }

        /**
         * Get data about the current WWPP installation.
         *
         * @since 1.16
         * @access public
         *
         * @return array
         */
        public static function get_wwpp_data() {

            return self::get_plugin_data( 'woocommerce-wholesale-prices-premium/woocommerce-wholesale-prices-premium.bootstrap.php' );

        }
        
    }

}
