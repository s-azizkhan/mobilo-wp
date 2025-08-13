<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'Order_Form_Scripts' ) ) {

    class Order_Form_Scripts {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of Order_Form_Scripts.
         *
         * @since 1.6.6
         * @access private
         * @var Order_Form_Scripts
         */
        private static $_instance;

        /**
         * Current WWOF version.
         *
         * @since 1.6.6
         * @access private
         * @var int
         */
        private $_wwof_current_version;

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * Order_Form_Scripts constructor.
         *
         * @param array $dependencies Array of instance objects of all dependencies of Order_Form_Scripts model.
         *
         * @access public
         * @since 1.6.6
         */
        public function __construct( $dependencies ) {

            $this->_wwof_current_version = $dependencies[ 'WWOF_CURRENT_VERSION' ];

        }

        /**
         * Ensure that only one instance of Order_Form_Scripts is loaded or can be loaded (Singleton Pattern).
         *
         * @param array $dependencies Array of instance objects of all dependencies of Order_Form_Scripts model.
         *
         * @return Order_Form_Scripts
         * @since 1.6.6
         */
        public static function instance( $dependencies = null ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Load Admin or Backend Related Styles and Scripts.
         *
         * @since 1.0.0
         * @since 1.6.6 Refactor codebase and move to its proper model
         */
        public function load_back_end_styles_and_scripts( $handle ) {

            if( $handle === 'woocommerce_page_order-forms' ) {
                
                // Important: Must enqueue this script in order to use WP REST API via JS
                wp_enqueue_script( 'wp-api' );
                wp_localize_script( 'wp-api', 'wpApiSettings', array(
                    'root' => esc_url_raw( rest_url() ),
                    'nonce' => wp_create_nonce( 'wp_rest' )
                ) );
                
                global $wc_wholesale_prices;
                
                wp_localize_script( 'wp-api', 'Options',
                                    array(
                                        'site_url'          => site_url(),
                                        'ajax'              => admin_url( 'admin-ajax.php' ),
                                        'wholesale_role'    => !empty( $wc_wholesale_prices ) ? $wc_wholesale_prices->wwp_wholesale_roles->getUserWholesaleRole() : ''
                                    )
                                );

                // React Order Form Scripts
                $paths = array(
                    'beta'      => get_option( 'wwof_order_form_v2_enable_order_form' ) == 'yes' ? true : false,
                    'app_name'  => 'order-form-cpt',
                );

                $this->load_react_order_form_scripts( $paths );

            }
            
        }

        /**
         * Load Frontend Related Styles and Scripts.
         *
         * @since 1.0.0
         * @since 1.6.6 Refactor codebase and move to its proper model
         */
        public function load_front_end_styles_and_scripts() {

            global $post;
            
            $force_load_scripts_styles  = apply_filters( 'wwof_force_load_scripts_styles' , false );

            if ( ( isset( $post->post_content ) && has_shortcode( $post->post_content , 'wwof_product_listing' ) ) || $force_load_scripts_styles === true ) {

                $pattern    = get_shortcode_regex();
                $beta       = false;

                // Check if shortcode 'wwof_product_listing' has beta attribute and if set
                if ( preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches ) )  {
                    foreach( $matches as $match) {
                        if( !empty($match[0]) ){
                            preg_match('/beta="(.*)"/', trim( $match[0] ) , $attr_val);
                            if( isset( $attr_val[ 1 ] ) )
                                $beta = $attr_val[ 1 ];
                        }
                    }
                }

                // Important: Must enqueue this script in order to use WP REST API via JS
                wp_enqueue_script( 'wp-api' );
                wp_localize_script( 'wp-api', 'wpApiSettings', array(
                    'root' => esc_url_raw( rest_url() ),
                    'nonce' => wp_create_nonce( 'wp_rest' )
                ) );
                
                // React Order Form Scripts
                $paths = array(
                    'beta'      => $beta && get_option( 'wwof_order_form_v2_enable_order_form' ) == 'yes' ? true : false,
                    'app_name'  => 'order-form'
                );

                $this->load_react_order_form_scripts( $paths );
                
            }

        }

        /**
         * Load React Order Scripts.
         *
         * @since 1.15
         * @since 1.15.1 Check if the feature is turned on and if the "beta" attribute is true.
         * @access public
         */
        public function load_react_order_form_scripts( $args ) {

            if( $args[ 'beta' ] == true ) {

                // JS Files
                $js_path = WWOF_JS_ROOT_DIR . 'app/' . $args[ 'app_name' ] . '/build/static/js';

                if( file_exists( $js_path )  ) {
                    
                    $js_files = scandir( $js_path );

                    if( $js_files ) {
                        foreach( $js_files as $key => $js_file ) {
                            
                            // Get the extension using pathinfo
                            $extension = pathinfo( $js_file, PATHINFO_EXTENSION );

                            if ( $extension === 'js' )
                                wp_enqueue_script( 'wwof_react_order_form_'. $key , WWOF_JS_ROOT_URL . 'app/' . $args[ 'app_name' ] . '/build/static/js/' . $js_file , array( 'jquery' ) , $this->_wwof_current_version , true );
                        
                        }

                    }

                }

                // CSS Files
                $css_path = WWOF_JS_ROOT_DIR . 'app/' . $args[ 'app_name' ] . '/build/static/css';

                if( file_exists( $css_path ) ) {

                    $css_files = scandir( $css_path );

                    if( $css_files ) {

                        foreach( $css_files as $key => $css_file ) {

                            // Get the extension using pathinfo
                            $extension = pathinfo( $css_file, PATHINFO_EXTENSION );

                            if ( $extension === 'css' )
                                wp_enqueue_style( 'wwof_react_order_form_css_' . $key , WWOF_JS_ROOT_URL . 'app/' . $args[ 'app_name' ] . '/build/static/css/' . $css_file , array() , $this->_wwof_current_version , 'all' );  

                        }

                    }

                }

            }

        }

        /**
         * Execute model.
         *
         * @since 1.6.6
         * @access public
         */
        public function run() {

            // Load Backend CSS and JS
            add_action( 'admin_enqueue_scripts' , array( $this , 'load_back_end_styles_and_scripts' ) );

            // Load Frontend CSS and JS
            add_action( 'wp_enqueue_scripts' , array( $this , 'load_front_end_styles_and_scripts' ) , 100 );
            
        }

    }

}
