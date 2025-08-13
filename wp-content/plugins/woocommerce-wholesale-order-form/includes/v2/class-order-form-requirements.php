<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'Order_Form_Requirements' ) ) {

    class Order_Form_Requirements {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of Order_Form_Requirements.
         *
         * @since 1.16
         * @access private
         */
        private static $_instance;

        /**
         * Property that holds required minimum WWPP version.
         *
         * @since 1.16
         * @access public
         */
        const MIN_WWPP_VERSION = '1.24.4';

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * Order_Form_Requirements constructor.
         *
         * @param array $dependencies Array of instance objects of all dependencies of Order_Form_Requirements model.
         *
         * @access public
         * @since 1.16
         */
        public function __construct( $dependencies ) { }

        /**
         * Ensure that only one instance of Order_Form_Requirements is loaded or can be loaded (Singleton Pattern).
         *
         * @param array $dependencies Array of instance objects of all dependencies of Order_Form_Requirements model.
         *
         * @return Order_Form_Requirements
         * @since 1.16
         */
        public static function instance( $dependencies = null ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }
        
        /**
         * Check if WWPP minimum requirement is met else show a message in Order Forms page.
         *
         * @since 1.16
         * @access public
         */
        public static function wwpp_minimum_requirement() {

            $wwpp_data      = Order_Form_Helpers::get_wwpp_data();
            $min_version    = self::MIN_WWPP_VERSION;
            $show_notice    = get_option( 'wwof_show_min_wwpp_requirement_notice' );
            
            // WWPP is not active
            if( !WWOF_Functions::is_wwpp_active() )
                wp_send_json(
                    array(
                        'status'    => 'hidden',
                        'heading'   => '',
                        'message'   => ''
                    )
                );

            if( !empty( $show_notice ) && $show_notice === 'no' ) {

                // Hide notice
                wp_send_json(
                    array(
                        'status'    => 'hidden',
                        'heading'   => '',
                        'message'   => ''
                    )
                );

            } else {

                if( $wwpp_data && version_compare( $wwpp_data[ 'Version'] , $min_version , '>=' ) ) {

                    // Will not print this message
                    wp_send_json(
                        array(
                            'status'    => 'success',
                            'heading'   => '',
                            'message'   => sprintf( __( 'You have met the minimum WooCommerce Wholesale Prices Premium version of %1$s' , 'woocommerce-wholesale-prices-premium' ) , $min_version )
                        )
                    );

                } else {

                    // Show Notice in Order Forms Page
                    update_option( 'wwof_show_min_wwpp_requirement_notice' , 'yes' );
                    
                    $img                = "<img style='height: 40px; margin-top: -3px;' src='" . WWOF_IMAGES_ROOT_URL . "/logo.png'>";
                    $license_activated  = is_multisite() ? get_site_option( 'wwpp_license_activated' ) : get_option( 'wwpp_license_activated' );
                    $update_link        = $license_activated ? admin_url( 'update-core.php' ) : admin_url( 'options-general.php?page=wwc_license_settings&tab=wwpp' );
                    $update_now         = "<a style='font-weight: 600; font-size: 18px; background: #46bf92; color: #fff; padding: 6px 16px; display: inline-table;' href='" . $update_link . "' class='ant-btn' type='link' target='_blank'>Update Now</a>";

                    wp_send_json(
                        array(
                            'status'    => 'fail',
                            'heading'   => sprintf( __( '%1$s &nbsp; <b>NEWER VERSION OF WOOCOMMERCE WHOLESALE PRICES PREMIUM REQUIRED</b>' , 'woocommerce-wholesale-prices-premium' ), $img ),
                            'message'   => sprintf( __( '<br/><p>WooCommerce Wholesale Prices Premium needs to be on at least version %1$s to work properly with WooCommerce Wholesale Order Form.</p><p><b>Click here to update.</b></p>%2$s' , 'woocommerce-wholesale-prices-premium' ) , $min_version , $update_now )
                        )
                    );

                }

            }
            
        }

        /**
         * Remove WWPP Minimum requirement message notice.
         *
         * @since 1.16
         * @access public
         */
        public function remove_wwpp_minimum_requirement_message() {

            update_option( 'wwof_show_min_wwpp_requirement_notice' , 'no' );

            wp_send_json(
                array(
                    'status'    => 'success',
                    'message'   => "Hiding WWPP minimum requirement message."
                )
            );

        }

        /**
         * Execute model.
         *
         * @since 1.16
         * @access public
         */
        public function run() {

            // Admin only AJAX Interfaces
            add_action( 'wp_ajax_wwpp_minimum_requirement'          , array( $this , 'wwpp_minimum_requirement' ) );
            add_action( 'wp_ajax_nopriv_wwpp_minimum_requirement'   , array( $this , 'wwpp_minimum_requirement' ) );

            add_action( 'wp_ajax_remove_wwpp_minimum_requirement_message'          , array( $this , 'remove_wwpp_minimum_requirement_message' ) );
            add_action( 'wp_ajax_nopriv_remove_wwpp_minimum_requirement_message'   , array( $this , 'remove_wwpp_minimum_requirement_message' ) );
            
        }
    }
}
