<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'Order_Form_Settings_API_Controller' ) ) {

    /**
     * Model that houses the logic of Order Form Settngs REST API.
     *
     * @since 1.16
     */
    class Order_Form_Settings_API_Controller extends WP_REST_Controller {

		/**
		 * Endpoint namespace.
		 *
		 * @var string
		 */
		protected $namespace = 'wwof/v1';

		/**
		 * Route base.
		 *
		 * @var string
		 */
        protected $rest_base = 'settings';
        
        /**
         * Order_Form_Settings_API_Controller constructor.
         *
         * @since 1.16
         * @access public
         */
        public function __construct() {
            
			// Fires when preparing to serve an API request.
            add_action( "rest_api_init" , array( $this , "register_routes" ) );

        }
        
        /**
         * Register cpt REST API routes and endpoints.
         * 
         * @since 1.16
         * @access public
         * 
         * @return void
         */
        public function register_routes() {
            
            register_rest_route( 
                $this->namespace, 
                '/' . $this->rest_base, 
                array(
                    array(
                        'methods'             => WP_REST_Server::READABLE,
                        'callback'            => array( $this, 'get_items' ),
                        'permission_callback' => array( $this, 'permissions_check' )
                    ),
                    'schema' => array( $this, 'get_public_item_schema' ),
                ) 
            );

            register_rest_route(
                $this->namespace,
                '/' . $this->rest_base . '/(?P<id>[\d]+)',
                array(
                    'args'   => array(
                        'id' => array(
                            'description' => __( 'Unique identifier for the object.' ),
                            'type'        => 'integer',
                        ),
                    ),
                    array(
                        'methods'             => WP_REST_Server::READABLE,
                        'callback'            => array( $this, 'get_settings_data' ),
                        'permission_callback' => array( $this, 'permissions_check' )
                    ),
                    array(
                        'methods'             => WP_REST_Server::EDITABLE,
                        'callback'            => array( $this, 'update_settings_data' ),
                        'permission_callback' => array( $this, 'permissions_check' )
                    )
                )
            );
            
        }
        
        /**
         * Check whether a given request has permission to edit and delete order forms.
         *
         * @param  WP_REST_Request
         * @return WP_Error|boolean
         */
        public function permissions_check( $request ) {
return true;
            if ( empty( get_current_user_id() ) ) {
                return new WP_Error( 'rest_customer_invalid', __( 'Resource does not exist.', 'woocommerce-wholesale-order-form' ), array( 'status' => 404 ) );
            }
    
            if ( ! user_can( get_current_user_id(), 'manage_options' ) ) {
                return new WP_Error( 'rest_cannot_view', __( 'Sorry, you cannot list resources.', 'woocommerce-wholesale-order-form' ), array( 'status' => rest_authorization_required_code() ) );
            }
    
            return true;

        }

        /**
         * Get WWWOF Settings.
         * 
         * @since 1.16
         * @access public
         */
        public function get_items( $request ) {
            
            $response = rest_ensure_response( $this->set_settings() );
            
            return $response;

        }

        /**
         * Set WWWOF Settings.
         * 
         * @since 1.16
         * @access public
         */
        public function set_settings() {

            return apply_filters( 'rest_api_wwof_settings' , array(
                array(
                    'title'     => 'Product Sorting',
                    'type'      => 'select',
                    'desc'      => 'Changes how products are sorted on the form.',
                    'id'        => 'sort_by',
                    'options'   => array(
                            '' => 'Default Sorting',
                            // 'menu_order' => 'Custom Ordering (menu order) + Name',
                            'title' => 'Name',
                            'date'  => 'Sort by Date',
                            // 'sku' => 'SKU'
                        )

                    ),
                    array(
                        'title'     => 'Product Sort Order',
                        'type'      => 'select',
                        'desc'      => 'Changes how products are sorted on the form.',
                        'id'        => 'sort_order',
                        'options'   => array(
                                'asc'   => 'Ascending',
                                'desc'  => 'Descending'
                            )
    
                        ),
                    array(
                        'title'     => 'Cart Subtotal Tax',
                        'type'      => 'select',
                        'desc'      => 'Choose if the cart subtotal should display including or excluding taxes. This is only used if you have the <i>Show Cart Subtotal</i> setting turned on.',
                        'id'        => 'cart_subtotal_tax',
                        'options'   => array(
                                'incl' => 'Including tax',
                                'excl' => 'Excluding tax'
                        ),
                        'default'   => 'incl'
                    )
            ) );

        }

        /**
         * Get Settings data for the specific order form.
         * 
         * @since 1.16
         * @access public
         */
        public function get_settings_data( $request ) {

            if( get_post_type( $request[ 'id' ] ) !== 'order_form' )
                return new WP_Error( 'rest_invalid_id', __( 'Invalid ID.', 'woocommerce-wholesale-order-form' ), array( 'status' => 400 ) );

            $settingsData = get_post_meta( $request[ 'id' ] ,'settings' , true );

            return rest_ensure_response( !empty( $settingsData ) ? $settingsData : array() );

        }

        /**
         * Update Settings data for the specific order form.
         * 
         * @since 1.16
         * @access public
         */
        public function update_settings_data( $request ) {

            if( get_post_type( $request[ 'id' ] ) !== 'order_form' )
                return new WP_Error( 'rest_invalid_id', __( 'Invalid ID.', 'woocommerce-wholesale-order-form' ), array( 'status' => 400 ) );

            $updated = update_post_meta( $request[ 'id' ] ,'settings' , $request[ 'data' ] );

            if( $updated === true ) {
                return rest_ensure_response( 
                    array( 
                        'status'    => 'success',
                        'message'   => __( 'Settings updated successfully.' , 'woocommerce-wholesale-order-form' ),
                        'data'      => $request[ 'data' ]
                    )
                );
            } else {
                return rest_ensure_response( 
                    array( 
                        'status'    => 'fail', 
                        'message'   => __( 'Update Fail.' , 'woocommerce-wholesale-order-form' ),
                        'data'      => $request[ 'data' ]
                    )
                );
            }
            
        }
        
    }

}

return new Order_Form_Settings_API_Controller();