<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require __DIR__ . '/../../../vendor/autoload.php';

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

if ( !class_exists( 'WWOF_API_Request' ) ) {

    class WWOF_API_Request {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */
        
        private static $_instance;

        private $api_keys = array();

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        public function __construct() { 

            $this->api_keys = $this->wwof_get_wc_api_keys();

        }
        
        public static function instance( $dependencies = null ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
	     * WC API Keys.
	     *
         * @since 1.15
	     * @return array
	     */
        public function wwof_get_wc_api_keys() {
            
            return array(
                        'consumer_key'      => get_option( 'wwof_order_form_v2_consumer_key' ),
                        'consumer_secret'   => get_option( 'wwof_order_form_v2_consumer_secret' )
                    );

        }
        
        /**
	     * Check if user is a wholesale customer
	     *
         * @since 1.15
	     * @return bool
	     */
        public function is_wholesale_customer() {

            if( is_plugin_active( 'woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php' ) ) {
                
                global $wc_wholesale_prices;
                $wholesale_role = $wc_wholesale_prices->wwp_wholesale_roles->getUserWholesaleRole();
                
                return isset( $wholesale_role[ 0 ] ) ? $wholesale_role[ 0 ] : '';

            }

            return false;

        }

        /**
	     * Get products. If user is wholesale customer then use wwpp api else use custom wwof api endpoint.
	     *
         * @since 1.15
	     * @return array
	     */
        public function get_products() {

            $wholesale_role = $this->is_wholesale_customer();

            if( empty( $wholesale_role ) && isset( $_POST[ 'wholesale_role' ] ) )
                $wholesale_role = $_POST[ 'wholesale_role' ];
                
            $wwpp_data = Order_Form_Helpers::get_wwpp_data();
            $wwpp_min_version = Order_Form_Requirements::MIN_WWPP_VERSION;
            
            // WWPP Compat
            if( !empty( $wholesale_role ) && 
                $wwpp_data && 
                version_compare( $wwpp_data[ 'Version'] , $wwpp_min_version , '>=' ) &&
                WWOF_Functions::is_wwpp_active()
            )
                $this->get_wholesale_products( $wholesale_role );
            else
                $this->get_regular_products();
                
        }

        /**
	     * Get regular products using WWOF API custom endpoint.
	     *
         * @since 1.15
	     * @return array
	     */
        public function get_regular_products() {
            
            try {

                $api_keys = $this->api_keys;
            
                $woocommerce = new Client(
                    site_url(), 
                    $api_keys[ 'consumer_key' ],
                    $api_keys[ 'consumer_secret' ],
                    [
                        'version' => 'wc/v3',
                    ]
                );
                
                $args = array(
                    'per_page'      => isset( $_POST[ 'per_page' ] ) ? $_POST[ 'per_page' ] : 12,
                    'search'        => isset( $_POST[ 'search' ] ) ? $_POST[ 'search' ] : '',
                    'category'      => isset( $_POST[ 'category' ] ) && $_POST[ 'category' ] != 0 ? $_POST[ 'category' ] : '',
                    'page'          => isset( $_POST[ 'page' ] ) ? $_POST[ 'page' ] : 1,
                    'order'         => isset( $_POST[ 'sort_order' ] ) && !empty( $_POST[ 'sort_order' ] ) ? $_POST[ 'sort_order' ] : 'desc',
                    'orderby'       => isset( $_POST[ 'sort_by' ] ) && !empty( $_POST[ 'sort_by' ] ) ? $_POST[ 'sort_by' ] : 'date',
                    'categories'    => isset( $_POST[ 'categories' ] ) ? $_POST[ 'categories' ] : '',
                    'status'        => 'publish'
                );
                
                if( !empty( $_POST[ 'products' ] ) ) {
                    if( !empty( $args[ 'include' ] ) )
                        $args[ 'include' ] = array_merge( $args[ 'include' ] , $_POST[ 'products' ] );
                    else
                        $args[ 'include' ] = explode( ',', $_POST[ 'products' ] );
                }
                
                if( get_option( 'wwof_filters_product_category_filter' ) ) {
                    $products_to_include = $this->include_products_from_category();
                    if( !empty( $args[ 'include' ] ) )
                        $args[ 'include' ] = array_merge( $args[ 'include' ] , $products_to_include );
                    else
                        $args[ 'include' ] = $products_to_include;
                } else if( get_option( 'wwof_filters_exclude_product_filter' ) )
                    $args[ 'exclude' ] = get_option( 'wwof_filters_exclude_product_filter' );

                if( isset( $_POST[ 'searching' ] ) && $_POST[ 'searching' ] === 'no' && get_option( 'wwof_general_default_product_category_search_filter' ) ) {
                    $category = get_term_by( 'slug', get_option( 'wwof_general_default_product_category_search_filter' ) , 'product_cat' );
                    if( $category && filter_var( $_POST[ 'show_all' ], FILTER_VALIDATE_BOOLEAN ) !== true )
                        $args[ 'category' ] = $category->term_id;
                }
                
                $results = $woocommerce->get( 'products' , $args );

                $response       = $woocommerce->http->getResponse();
                $headers        = $response->getHeaders();
                $tota_pages     = $headers[ 'X-WP-TotalPages' ];
                $total_products = $headers[ 'X-WP-Total' ];
                
                wp_send_json(
                    array(
                        'status'            => 'success',
                        'products'          => $results,
                        'variations'        => $this->get_variations( $results, true ),
                        'settings'          => array(),
                        'total_page'        => $tota_pages,
                        'total_products'    => $total_products,
                        'cart_subtotal'     => $this->get_cart_subtotal(),
                        'cart_url'          => wc_get_cart_url()
                    )
                );
                
            } catch ( HttpClientException $e ) {

                wp_send_json(
                    array(
                        'status'    => 'error',
                        'message'   => $e->getMessage() // error
                    )
                );

            }
            
        }

        /**
	     * Get wholesale products using WWPP API custom endpoint.
         * Note: not yet used will use this in the next phase.
	     *
         * @since 1.15
	     * @return array
	     */
        public function get_wholesale_products( $wholesale_role ) {
            
            try {

                $api_keys = $this->api_keys;
            
                $woocommerce = new Client(
                    site_url(), 
                    $api_keys[ 'consumer_key' ],
                    $api_keys[ 'consumer_secret' ],
                    [
                        'version' => 'wwpp/v1',
                    ]
                );
                
                $args = array(
                    'wholesale_role'    => $wholesale_role,
                    'per_page'          => isset( $_POST[ 'per_page' ] ) ? $_POST[ 'per_page' ] : 12,
                    'search'            => isset( $_POST[ 'search' ] ) ? $_POST[ 'search' ] : '',
                    'category'          => isset( $_POST[ 'category' ] ) && $_POST[ 'category' ] != 0 ? $_POST[ 'category' ] : '',
                    'page'              => isset( $_POST[ 'page' ] ) ? $_POST[ 'page' ] : 1,
                    'order'             => isset( $_POST[ 'sort_order' ] ) && !empty( $_POST[ 'sort_order' ] ) ? $_POST[ 'sort_order' ] : 'desc',
                    'orderby'           => isset( $_POST[ 'sort_by' ] ) && !empty( $_POST[ 'sort_by' ] ) ? $_POST[ 'sort_by' ] : 'date',
                    'status'            => 'publish'
                );

                $results = $woocommerce->get( 'wholesale/products' , $args );
                
                $response       = $woocommerce->http->getResponse();
                $headers        = $response->getHeaders();
                $total_pages    = $headers[ 'X-WP-TotalPages' ];
                $total_products = $headers[ 'X-WP-Total' ];
                
                wp_send_json(
                    array(
                        'status'            => 'success',
                        'products'          => $results,
                        'variations'        => $this->get_wholesale_variations( $results, $wholesale_role, true ),
                        'settings'          => array(),
                        'total_page'        => $total_pages,
                        'total_products'    => $total_products,
                        'cart_subtotal'     => $this->get_cart_subtotal(),
                        'cart_url'          => wc_get_cart_url()
                    )
                );
                
            } catch ( HttpClientException $e ) {

                wp_send_json( array(
                        'status'    => 'error',
                        'message'   => $e
                    ) );

            }

        }

        /**
	     * Get categories via WC API.
	     *
         * @since 1.15
	     * @return array
	     */
        public function get_categories() {
            
            try {

                $api_keys = $this->api_keys;
            
                $woocommerce = new Client(
                    site_url(), 
                    $api_keys[ 'consumer_key' ],
                    $api_keys[ 'consumer_secret' ],
                    [
                        'version' => 'wc/v3',
                    ]
                );

                $args = array( 
                    'hide_empty'    => true,
                    'per_page'      => 100
                );

                // WWOF Product Category Filter Option
                $categories = get_option( 'wwof_filters_product_category_filter' );
                $cat_ids    = array();
                if( $categories ) {
                    foreach( $categories as $slug ) {
                        $category = get_term_by( 'slug', $slug, 'product_cat' );
                        if( $category )
                            $cat_ids[] = $category->term_id;
                    }
                    if( $cat_ids )
                        $args[ 'include' ] = $cat_ids;
                    
                }

                // WWOF Product Categories Shortcode Attribute
                if( !empty( $_POST[ 'categories' ] ) ) {
                    if( !empty( $args[ 'include' ] ) )
                        $args[ 'include' ] = array_merge( $args[ 'include' ] , explode( ',', $_POST[ 'categories' ] ) );
                    else
                        $args[ 'include' ] = explode( ',', $_POST[ 'categories' ] );
                }
                
                $results = $woocommerce->get( 'products/categories' , $args );
                
                $category_hierarchy = array();
                $this->assign_category_children( $results , $category_hierarchy );
                
                wp_send_json(
                    array(
                        'status'        => 'success',
                        'categories'    => $category_hierarchy
                    )
                );
                
            } catch ( HttpClientException $e ) {

                wp_send_json(
                    array(
                        'status'        => 'error',
                        'message'    => $e->getMessage()
                    )
                );

            }

        }

        /**
	     * Group category children ito their own parents.
	     *
         * @since 1.15.2
         * @param array $cats       List of categories
         * @param array $into       New sorted children
         * @param array $parent_id  The parent ID. 0 is for grand parent.
	     * @return array
	     */
        public function assign_category_children( Array &$cats, Array &$into, $parent_id = 0 ) {

            foreach ( $cats as $i => $cat ) {

                if ( $cat->parent == $parent_id ) {

                    $into[] = $cat;
                    unset( $cats[ $i ] );

                }

            }
        
            foreach ( $into as $top_cat ) {

                $top_cat->children = array();
                $this->assign_category_children( $cats , $top_cat->children , $top_cat->id );

            }
            
        }

        /**
	     * Get product variations via WC API endpoint.
	     *
         * @since 1.15
         * @param array $products
	     * @return array
	     */
        public function get_variations( $products , $get_all_variations = false ) {

            $variations = array();
            $api_keys = $this->api_keys;
            
            $woocommerce = new Client(
                site_url(), 
                $api_keys[ 'consumer_key' ],
                $api_keys[ 'consumer_secret' ],
                [
                    'version' => 'wc/v3',
                ]
            );

            if( $get_all_variations === true && $products ) {

                // Fetch all variations per variable product
                foreach( $products as $product ) {
                    
                    if( $product->type === 'variable' ) {
                        
                        try {

                            $results = $woocommerce->get( 'products/' . $product->id . '/variations' );

                            if( $results ) {
                                
                                foreach( $results as $index => $variation ){
                                    $variation_obj = wc_get_product( $variation->id );
                                    $results[ $index ]->price = $variation_obj->get_price_html();
                                }
                                    
                                
                                $variations[ $product->id ] = $results;

                            }
                                
                            
                        } catch ( HttpClientException $e ) {
            
                            error_log(print_r($e,true));
            
                        }

                    }
                    
                }

            } else if( isset( $_POST[ 'product_id' ] ) ) {

                // Lazy loading on scroll in variation dropdown

                try {
                        
                    $args = array(
                        'status'    => 'publish',
                        'page'      => isset( $_POST[ 'page' ] ) ? $_POST[ 'page' ] : '',
                        'search'    => isset( $_POST[ 'search' ] ) ? $_POST[ 'search' ] : ''
                    );

                    $results = $woocommerce->get( 'products/' . $_POST[ 'product_id' ] . '/variations' , $args );
                    
                    $response           = $woocommerce->http->getResponse();
                    $headers            = $response->getHeaders();
                    $tota_pages         = $headers[ 'X-WP-TotalPages' ];
                    $total_variations   = $headers[ 'X-WP-Total' ];

                    if( $results )
                        $variations[ $_POST[ 'product_id' ] ] = $results;
                    
                } catch ( HttpClientException $e ) {
    
                    error_log(print_r($e,true));
    
                }

            }

            if ( $get_all_variations === false && defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                wp_send_json(
                    array(
                        'status'            => 'success',
                        'variations'        => $variations,
                        'total_pages'       => $tota_pages,
                        'total_variations'  => $total_variations
                    )
                );

            } else return $variations;
            
        }

        /**
	     * Get Wholesale Variations.
	     *
         * @since 1.16
	     * @return array
	     */
        public function get_wholesale_variations( $products , $wholesale_role, $get_all_variations = false ) {

            $variations = array();
            $api_keys = $this->api_keys;
            
            $woocommerce = new Client(
                site_url(), 
                $api_keys[ 'consumer_key' ],
                $api_keys[ 'consumer_secret' ],
                [
                    'version' => 'wwpp/v1',
                ]
            );
            
            // Fetch all variations per variable product
            foreach( $products as $product ) {
                
                if( $product->type === 'variable' ) {
                    
                    try {

                        $args = array(
                            'wholesale_role' => $wholesale_role
                        );

                        $results = $woocommerce->get( 'wholesale/products/' . $product->id . '/variations' , $args );

                        if( $results ) {
                            $variations[ $product->id  ] = $results;
                        }
                        
                    } catch ( HttpClientException $e ) {
        
                        error_log(print_r($e,true));
        
                    }

                }
                
            }
            
            return $variations;
            
        }

        /**
	     * Product Category Filter and Exclude Product Filter option.
	     *
         * @since 1.15
	     * @return array
	     */
        public function include_products_from_category() {

            $categories = get_option( 'wwof_filters_product_category_filter' );
            $args = array(
                'category'  => $categories,
                'return'    => 'ids',
                'paginate'  => false,
                'exclude'   => get_option( 'wwof_filters_exclude_product_filter' )
            );

            $products = wc_get_products( $args );
            
            return $products;
        }
        
        /**
	     * Get cart subtotal.
	     *
         * @since 1.16
	     */
        public function get_cart_subtotal() {

            ob_start(); ?>

            <div class="order_form_cart_sub_total"><?php
                if ( WC()->cart->get_cart_contents_count() ) {
                    if ( isset( $_POST[ 'cart_subtotal_tax' ] ) && $_POST[ 'cart_subtotal_tax' ] == 'excl' ) { ?>
                        <span class="sub_total excluding_tax"><?php
                            _e( 'Subtotal: ' , 'woocommerce-wholesale-order-form' );
                            echo wc_price( WC()->cart->cart_contents_total ) . ' <small>' . WC()->countries->ex_tax_or_vat() . '</small>'; ?>
                        </span><?php
                    } else { ?>
                        <span class="sub_total including_tax"><?php
                            _e( 'Subtotal: ' , 'woocommerce-wholesale-order-form' );
                            echo wc_price( WC()->cart->cart_contents_total + WC()->cart->tax_total ) . ' <small>' . WC()->countries->inc_tax_or_vat() . '</small>';
                            ?>
                        </span><?php
                    }
                } else { ?>
                    <span class="empty_cart"><?php _e( 'Cart Empty' , 'woocommerce-wholesale-order-form' ); ?></span><?php
                } ?>

            </div><?php

            return ob_get_clean();

        }
        /**
         * Execute model.
         *
         * @since 1.15
         * @access public
         */
        public function run() {
            
            add_action( 'wp_ajax_nopriv_wwof_api_get_products'      , array( $this , 'get_products' ) );
            add_action( 'wp_ajax_wwof_api_get_products'             , array( $this , 'get_products' ) );

            add_action( 'wp_ajax_nopriv_wwof_api_get_categories'    , array( $this , 'get_categories' ) );
            add_action( 'wp_ajax_wwof_api_get_categories'           , array( $this , 'get_categories' ) );

            add_action( 'wp_ajax_nopriv_wwof_api_get_variations'    , array( $this , 'get_variations' ) );
            add_action( 'wp_ajax_wwof_api_get_variations'           , array( $this , 'get_variations' ) );
            
        }
        
    }

}
