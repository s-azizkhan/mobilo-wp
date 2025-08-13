<?php

/**
 * Mobilo functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Mobilo
 */

if (!defined('MOBILO_VERSION')) {
    // Replace the version number of the theme on each release
    define('MOBILO_VERSION', '0.4.5');
}

if (!defined('LWMC_VERSION')) {
    // Replace the version number of the theme on each release.
    define('LWMC_VERSION', '0.1.0');
}

// defining the theme prefix
if (!defined('MOBILO_PREFIX')) {
    define('MOBILO_PREFIX', 'lwmc');
}

// Define theme url
if (!defined('MOBILO_THEME_URL')) {
    define('MOBILO_THEME_URL', get_stylesheet_directory_uri());
}

// Define theme url
if (!defined('MOBILO_THEME_PATH')) {
    define('MOBILO_THEME_PATH', get_stylesheet_directory());
}

// TODO: change this based on env
// Define apk api gateway address
if (!defined('MOBILO_APK_GATEWAY')) {
    define('MOBILO_APK_GATEWAY', 'https://apk-gtw-dev.mobilocard.com');
}

// TODO: change this based on env
// Define api gateway address
if (!defined('MOBILO_API_GATEWAY')) {
    define('MOBILO_API_GATEWAY', 'https://api-gtw-dev.mobilocard.com');
}

// TODO: change this based on env
// Define api key to access api gateway
if (!defined('MOBILO_X_API_KEY')) {
    define('MOBILO_X_API_KEY', "AIzaSyAiPOPN07KhNxIBVeH2JvYNxKaWV8u7rXA");
}

// TODO: change this based on env
// Define secret key
if (!defined('MOBILO_WP_SECRET_KEY')) {
    // Replace the version number of the theme on each release
    define('MOBILO_WP_SECRET_KEY', 'VC2hnfJbkEddEkbJfnh2CV');
}

if (!function_exists('lwmc_setup')):
    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     */
    function lwmc_setup()
{
        /*
         * Make theme available for translation.
         * Translations can be filed in the /languages/ directory.
         * If you're building a theme based on Mobilo, use a find and replace
         * to change 'mobilo' to the name of your theme in all the template files.
         */
        load_theme_textdomain('mobilo', get_stylesheet_directory() . '/languages');

        // Add default posts and comments RSS feed links to head.
        add_theme_support('automatic-feed-links');

        /*
         * Let WordPress manage the document title.
         * By adding theme support, we declare that this theme does not use a
         * hard-coded <title> tag in the document head, and expect WordPress to
         * provide it for us.
         */
        add_theme_support('title-tag');

        /*
         * Enable support for Post Thumbnails on posts and pages.
         *
         * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
         */
        add_theme_support('post-thumbnails');

        // This theme uses wp_nav_menu() in two locations.
        register_nav_menus(
            array(
                'menu-1' => __('Primary', 'mobilo'),
                'menu-2' => __('Footer Menu', 'mobilo'),
            )
        );

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support(
            'html5',
            array(
                'search-form',
                'comment-form',
                'comment-list',
                'gallery',
                'caption',
                'style',
                'script',
            )
        );

        // Add theme support for selective refresh for widgets.
        add_theme_support('customize-selective-refresh-widgets');

        // Add support for editor styles.
        add_theme_support('editor-styles');

        // Enqueue editor styles.
        add_editor_style('style-editor.css');

        // Add support for responsive embedded content.
        add_theme_support('responsive-embeds');

        // Remove support for block templates.
        remove_theme_support('block-templates');
        //add_theme_support( 'block-templates' );
        //add_theme_support( 'align-wide' );

        add_theme_support('woocommerce');
    }
endif;
//add_action( 'after_setup_theme', 'lwmc_setup' );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function lwmc_widgets_init()
{
    register_sidebar(
        array(
            'name' => __('Footer', 'mobilo'),
            'id' => 'sidebar-1',
            'description' => __('Add widgets here to appear in your footer.', 'mobilo'),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget' => '</section>',
            'before_title' => '<h2 class="widget-title">',
            'after_title' => '</h2>',
        )
    );
}
// add_action( 'widgets_init', 'lwmc_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function lwmc_scripts()
{
    wp_enqueue_style('mobilo-style', get_stylesheet_uri(), array(), MOBILO_VERSION);
    wp_enqueue_script('mobilo-script', get_stylesheet_directory_uri() . '/js/script.min.js', array(), MOBILO_VERSION, true);

    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
// add_action( 'wp_enqueue_scripts', 'lwmc_scripts' );

/**
 * Add the block editor class to TinyMCE.
 *
 * This allows TinyMCE to use Tailwind Typography styles.
 *
 * @param array $settings TinyMCE settings.
 * @return array
 */
function lwmc_tinymce_add_class($settings)
{
    $settings['body_class'] = 'block-editor-block-list__layout';
    return $settings;
}
//add_filter('tiny_mce_before_init', 'lwmc_tinymce_add_class');

/**
 * Custom template tags for this theme.
 */
try {
    require MOBILO_THEME_PATH . '/inc/template-tags.php';
} catch (Throwable $e) {
    error_log('Error loading template-tags.php: ' . $e->getMessage());
}

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
try {
    require_once MOBILO_THEME_PATH . '/vendor/autoload.php';
} catch (Throwable $e) {
    error_log('Error loading autoload.php: ' . $e->getMessage());
}

try {
    require_once MOBILO_THEME_PATH . '/inc/template-functions.php';
} catch (Throwable $e) {
    error_log('Error loading template-functions.php: ' . $e->getMessage());
}

try {
    require_once MOBILO_THEME_PATH . '/sources/php/init.php';
} catch (Throwable $e) {
    error_log('Error loading init.php: ' . $e->getMessage());
}

try {
    require_once MOBILO_THEME_PATH . '/app/Service/BackendSubscriptionService.php';
} catch (Throwable $e) {
    error_log('Error loading BackendSubscriptionService.php: ' . $e->getMessage());
}

use Mobilo\WpTheme\Service\BackendSubscriptionService; 

try {
    require_once MOBILO_THEME_PATH . '/app/Scripts/get-subscription-without-proration.php';
} catch (Throwable $e) {
    error_log('Error loading get-subscription-without-proration.php: ' . $e->getMessage());
}

/**
 * Overrides the tax settings for WooCommerce.
 *
 */
function lwmc_override_tax()
{
    //     update_option('woocommerce_tax_total_display', 'itemized');
    update_option('woocommerce_tax_display_shop', 'incl');
    update_option('woocommerce_tax_display_cart', 'incl');
}
add_action('init', 'lwmc_override_tax');
add_action('admin_init', 'lwmc_override_tax');

/** Always show the payment box. */
add_filter( 'woocommerce_cart_needs_payment', 'lwmc_filter_woocommerce_cart_needs_payment', 10, 2 );
add_filter( 'woocommerce_order_needs_payment', 'lwmc_filter_woocommerce_order_needs_payment', 10, 3 );
// Looks at the totals to see if payment is actually required.
function lwmc_filter_woocommerce_cart_needs_payment($needs_payment, $cart)
{
    // Set true
    $needs_payment = true;

    return $needs_payment;
}
// Checks if an order needs payment, based on status and order total.
function lwmc_filter_woocommerce_order_needs_payment($needs_payment, $order, $valid_order_statuses)
{
    // Set true
    $needs_payment = true;

    return $needs_payment;
}

/* Disable WordPress Admin Bar for all users */
add_filter('show_admin_bar', '__return_false');


/* Filters the encrypted statement for CFW transactions */
add_filter('cfw_transactions_encrypted_statement', 'lwmc_filter_cfw_transactions_encrypted_statement',);
/**
 * Filters the encrypted statement for CFW transactions.
 *
 * @param string $statement The statement to be filtered.
 * @return string The filtered statement.
 */
function lwmc_filter_cfw_transactions_encrypted_statement($statement)
{
    if (!is_wc_endpoint_url('order-pay') && WC()->cart->get_subtotal() == 0) {
        $statement = __("<strong>You will not be charged for this order.</strong> Your payment details will be stored and encrypted for future purchases.", 'mobilo');
    }
    return $statement;
}



add_action('woocommerce_before_calculate_totals', function() {
    if (isset(WC()->session)) {
        WC()->session->set('currency', get_woocommerce_currency());
    }
});

// Add Kosovo and Gibraltar to WooCommerce countries
add_filter('woocommerce_countries', 'add_custom_woocommerce_countries');
function add_custom_woocommerce_countries($countries) {
    $countries['XK'] = 'Kosovo';
    $countries['GI'] = 'Gibraltar';
    return $countries;
}

// Add Kosovo and Gibraltar to allowed selling countries
add_filter('woocommerce_allowed_countries', 'add_custom_allowed_countries');
function add_custom_allowed_countries($allowed_countries) {
    $allowed_countries['XK'] = 'Kosovo';
    $allowed_countries['GI'] = 'Gibraltar';
    return $allowed_countries;
}

// Add states for Kosovo and Gibraltar
add_filter( 'woocommerce_states', 'add_custom_states_for_kosovo_gibraltar' );
function add_custom_states_for_kosovo_gibraltar( $states ) {
    
    // Kosovo states (major cities/regions)
    $states['XK'] = array(
        'PR' => 'Pristina',
        'PE' => 'Peja',
        'GI' => 'Gjakova',
        'PRZ' => 'Prizren',
        'FE' => 'Ferizaj',
        'MIT' => 'Mitrovica',
		'GJ' => 'Gjilan'
    );

    // Gibraltar (single region)
    $states['GI'] = array(
        'GI' => 'Gibraltar',
    );

    return $states;
}

add_action( 'woocommerce_subscription_object_updated_props', function( $subscription, $updated_props ) {
    if (!$subscription instanceof WC_Subscription) {
        return false;
    }
    
    mobilo_log(__METHOD__, "woocommerce_subscription_object_updated_props #" . $subscription->get_id(), "info");
    if ( $subscription->get_status() == 'active' ) {
        $modified = get_post_meta( $subscription->get_id(), '_manual_next_payment_override', true );
        $next_payment = $subscription->get_date( 'next_payment' );

        // If admin manually changes the next payment date, store it
        if ( ! $modified || $modified !== $next_payment ) {
            update_post_meta( $subscription->get_id(), '_manual_next_payment_override', $next_payment );
        }
    }
    
}, 10, 2 );

add_action('woocommerce_process_shop_subscription_meta', 'update_subscription_details', 20, 3);

function update_subscription_details($post_id, $post) {
    mobilo_log(__METHOD__, "update_subscription_details #$post_id", 'info');
    // Ensure it's a valid subscription
    $subscription = wcs_get_subscription($post_id);
    if (!$subscription || is_wp_error($subscription)) {
        return;
    }

    // Check current billing period
    $current_period = $subscription->get_billing_period();
	
    // If not already 'year', update it
    if ($current_period !== 'year') {
		mobilo_log(__METHOD__, "Subscription period#$current_period", 'info');
        $subscription->set_billing_period('year');
        $subscription->save();
    }
    
    send_subscription_to_third_party_callback($post_id);
}

function get_subscription_trial_end_date($subscription) {
    if (!$subscription instanceof WC_Subscription) {
        return false;
    }

    foreach ($subscription->get_items() as $item) {
        $product = $item->get_product();

        if ($product && $product->is_type('subscription')) {
            $trial_length = (int) get_post_meta($product->get_id(), '_subscription_trial_length', true);
            $trial_period = get_post_meta($product->get_id(), '_subscription_trial_period', true); // e.g., day, week, month, year

            if ($trial_length > 0 && $trial_period) {
                $start_timestamp = $subscription->get_time('start');

                // Calculate trial end timestamp
                $trial_end_timestamp = strtotime("+{$trial_length} {$trial_period}", $start_timestamp);
                
                // Add 10 minutes
                $trial_end_timestamp = strtotime("+2 minutes", $trial_end_timestamp);

                return [
                    'timestamp' => $trial_end_timestamp,
                    'date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $trial_end_timestamp)
                ];
            }
        }
    }

    return false; // No trial
}

add_action('woocommerce_before_calculate_totals', function($cart) {
    foreach ($cart->get_cart() as $cart_item) {
        if (!empty($cart_item['custom_price_set'])) {
            $price = $cart_item['data']->get_price();
            $cart_item['data']->set_price($price); // Reapply fixed price
        }
    }
}, 99);

add_action( 'woocommerce_new_subscription', 'queue_send_to_third_party', 10, 1 );

function queue_send_to_third_party( $subscription ) {
	mobilo_log(__METHOD__, "Subscription created for order test1 #$subscription", 'info');
	
    // Schedule job 15 seconds later
    wp_schedule_single_event( time() + 15, 'send_subscription_to_third_party', array( $subscription ) );
}

add_action( 'send_subscription_to_third_party', 'send_subscription_to_third_party_callback' );

function send_subscription_to_third_party_callback( $subscription_id ) {
	mobilo_log(__METHOD__, "Subscription created for order test2 #$subscription_id", 'info');
	
	$service = new BackendSubscriptionService();
	$service->syncSubscription($subscription_id);
}

// Enqueue jQuery and expose ajaxurl
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('jquery');
    wp_add_inline_script('jquery', 'var ajaxurl = "' . admin_url('admin-ajax.php') . '";', 'before');
});

// Inject the AJAX script in the footer
/*add_action('wp_footer', function () {
    if (!is_user_logged_in()) return;
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('#getAccessTokenBtn').on('click', function(e) {
            e.preventDefault();

            const refreshToken = 'AMf-vBz09ihV9qUOu9N06fb0fWjdw497hJd2Mx2o9fxo4_okC47EK6_BIHDhgUsP4655uNcpghSPMH4h9bss4HqmMNaqEdjpAmCQKnNUDgj05hCXGFAfkgYFf8z6lNKPXtiPD027ZWz5xzJKYwvdpAjIavpXZb6oSyx5zbRjx2O8iMXbTMC_bPZifM_pMQp-wMjzOrwBlZSlrrCY05h4JEk6HK8Sdk_kdqnIEbTfsYDBCgxIff8UaLo'; // 🔁 Replace this with an actual token

            console.log('📡 Sending AJAX request...');
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'get_access_token_from_refresh_token',
                    refreshToken: refreshToken
                },
                success: function(response) {
                    console.log('✅ Success:', response);
                    alert('Access token retrieved successfully!');
                },
                error: function(xhr) {
                    console.error('❌ Error:', xhr.responseJSON || xhr.responseText);
                    alert('Failed to retrieve access token.');
                }
            });
        });
    });
    </script>
    <?php
});

// Create a shortcode to show the button
add_shortcode('access_token_button', function () {
    return '<button id="getAccessTokenBtn">Get Access Token</button>';
});

function enqueue_custom_scripts() {
    // Output inline JS in footer only on frontend (not in admin area)
    if (!is_admin()) {
        ?>
        <script type="text/javascript">
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }

        const cookieValue = getCookie('user_firebase_token_data');

        if (cookieValue) {
            try {
                const tokenData = JSON.parse(cookieValue);
                const refreshToken = tokenData.refresh_token;

                if (!refreshToken) {
                    alert('Session expired, please login again.');
                    window.location.href = 'http://buy.dev.mobilocard.com/login';
                } else {
                    jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                        action: 'get_access_token_from_refresh_token',
                        refreshToken: refreshToken
                    }, function(response) {
                        if (response.redirect) {
                            alert(response.message);
                            window.location.href = response.redirect;
                            return;
                        }

                        if (response.success) {
                            // Handle success
                            console.log('Access token refreshed:', response.data);
                        } else {
                            alert(response.message || 'An error occurred');
                        }
                    });
                }
            } catch (e) {
                console.error('Failed to parse cookie:', e);
                alert('Session expired, please login again.');
                window.location.href = 'http://buy.dev.mobilocard.com/login';
            }
        } else {
            alert('Session expired, please login again.');
            window.location.href = 'http://buy.dev.mobilocard.com/login';
        }
        </script>
        <?php
    }
}
// add_action('wp_footer', 'enqueue_custom_scripts');*/

add_action('init', function () {
    $action = new \Mobilo\WpTheme\Actions\GetAccessTokenFromRefreshTokenAction();

    add_action('wp_ajax_get_access_token_from_refresh_token', [$action, 'action']);
    add_action('wp_ajax_nopriv_get_access_token_from_refresh_token', [$action, 'action']);
});

add_action('admin_footer', 'set_default_billing_period_js');
function set_default_billing_period_js() {
    global $pagenow;
    // mobilo_log(__METHOD__, "set_default_billing_period_js #" . ($pagenow === 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'shop_subscription'), 'info');

    // Only on post-new.php for your subscription post type
    if ($pagenow === 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'shop_subscription') {
        ?>
        <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            console.log('billing element: ', document.querySelector('[name="_billing_period"]'));
            const billingPeriodField = document.querySelector('[name="_billing_period"]');
            billingPeriodField.value = 'year';
        });
        </script>
        <?php
    }
}

add_filter( 'woocommerce_cart_item_name', 'custom_checkout_item_name_with_attributes', 10, 3 );
function custom_checkout_item_name_with_attributes( $product_name, $cart_item, $cart_item_key ) {
    if ( is_checkout() && isset( $cart_item['variation'] ) && ! empty( $cart_item['variation'] ) ) {
        $product = $cart_item['data'];
        $variation_name = $product->get_name(); // This includes variation title
        
        // Get product URL
        $product_url = get_permalink( $product->get_id() );
        
        // Add the link to the product title
        return '<a href="' . esc_url( $product_url ) . '" target="_blank">' . $variation_name . '</a>';
    }

    return $product_name;
}




add_action('admin_menu', function () {
    add_management_page(
        'User Meta Diagnostics',
        'User Meta Diagnostics',
        'manage_options',
        'user-meta-diagnostics',
        'render_user_meta_diagnostics_page'
    );
});

function render_user_meta_diagnostics_page() {
    echo '<div class="wrap"><h1>User Meta Diagnostics</h1>';
    echo '<p>This report summarizes user meta completion status.</p>';
    
    // Total users
    $total_users = count_users()['total_users'];

    // Missing org and firebase_id
    $org_firebase_missing = new WP_User_Query([
        'meta_query' => [
            'relation' => 'AND',
            [
                'relation' => 'OR',
                [
                    'key' => 'lwmc_user_org',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key' => 'lwmc_user_org',
                    'value' => '',
                    'compare' => '=',
                ],
            ],
            [
                'relation' => 'OR',
                [
                    'key' => 'user_firebase_id',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key' => 'user_firebase_id',
                    'value' => '',
                    'compare' => '=',
                ],
            ],
        ],
        'fields' => 'ID',
        'number' => 1,
        'count_total' => true,
    ]);
    $missing_org_and_firebase = $org_firebase_missing->get_total();

    // Missing chosen plan sku
    $missing_sku = new WP_User_Query([
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => 'lwmc_chosen_plan_sku',
                'compare' => 'NOT EXISTS',
            ],
            [
                'key' => 'lwmc_chosen_plan_sku',
                'value' => '',
                'compare' => '=',
            ],
        ],
        'fields' => 'ID',
        'number' => 1,
        'count_total' => true,
    ]);
    $missing_sku_count = $missing_sku->get_total();

    // Output table
    echo '<table class="widefat striped">';
    echo '<thead><tr><th>Metric</th><th>Value</th></tr></thead><tbody>';
    echo "<tr><td>Total Users</td><td>{$total_users}</td></tr>";
    echo "<tr><td>Users missing <code>lwmc_user_org</code> AND <code>user_firebase_id</code></td><td>{$missing_org_and_firebase}</td></tr>";
    echo "<tr><td>Users missing <code>lwmc_chosen_plan_sku</code></td><td>{$missing_sku_count}</td></tr>";
    echo '</tbody></table>';

    echo '</div>';
}
add_filter( 'woocommerce_checkout_update_user_meta', '__return_false' );

add_action('woocommerce_payment_complete', 'update_order_status_on_payment_success');
function update_order_status_on_payment_success($order_id)
{
    if (!$order_id) return;

    $order = wc_get_order($order_id);

    if ($order->get_status() === 'processing') {
        $order->update_status('completed');
    }
}



/*Force Save CC Details + Hide Checkbox*/
add_action( 'wp_footer', function() {
    ?>
    <script>
    (function($){
        function checkStripeSaveCard() {
            const $checkbox = $('#stripe_cc_save_source_key');
            if ($checkbox.length && !$checkbox.prop('checked')) {
                $checkbox.prop('checked', true).trigger('change');
            }
        }

        $(document).ready(checkStripeSaveCard);

        // CheckoutWC updates DOM dynamically (multi-step checkout), so observe DOM changes
        const observer = new MutationObserver(() => {
            checkStripeSaveCard();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Re-check before order is placed
        $(document.body).on('checkout_place_order', checkStripeSaveCard);
    })(jQuery);
    </script>
    <?php
}, 100 );


// Hide the Save Card checkbox visually
add_action( 'wp_head', function() {
    echo '<style>
        .wc-stripe-save-source {
            display: none !important;
        }
    </style>';
});

add_filter( 'woocommerce_cart_needs_shipping_address', 'force_shipping_address_for_specific_sku' );
function force_shipping_address_for_specific_sku( $needs_shipping_address ) {
    $has_mc_digital = false;
    $has_variable   = false;
    $has_subscription = false;
    foreach ( WC()->cart->get_cart() as $item ) {
        $product = wc_get_product( $item['product_id'] );
        $type = $product->get_type();
        if ( ! $product ) continue;

        if ( $product->get_sku() === 'MC_DIGITAL' ) {
            $has_mc_digital = true;
        }

        if ( $product->is_type( 'variable' ) ) {
            $has_variable = true;
        }

        if ( in_array( $type, array( 'subscription', 'variable-subscription' ), true ) ) {
            $has_subscription = true;
        }
    }

    // Require shipping address in all three cases
    if ( $has_mc_digital ) return true;

    return $needs_shipping_address;
}

add_filter( 'woocommerce_package_rates', 'remove_shipping_charges_for_specific_sku', 10, 2 );
function remove_shipping_charges_for_specific_sku( $rates, $package ) {
    $has_mc_digital = false;
    $has_variable   = false;
    $has_subscription = false;
    
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        $product = wc_get_product( $cart_item['product_id'] );
        $type = $product->get_type();
        if ( ! $product ) continue;

        if ( $product->get_sku() === 'MC_DIGITAL' ) {
            $has_mc_digital = true;
        }

        if ( $product->is_type( 'variable' ) ) {
            $has_variable = true;
        }

        if ( in_array( $type, array( 'subscription', 'variable-subscription' ), true ) ) {
            $has_subscription = true;
        }
    }
    if ( $has_mc_digital && ! $has_variable ) {
        foreach ( $rates as $rate_id => $rate ) {
            $rates[ $rate_id ]->cost = 0;
            $rates[ $rate_id ]->taxes = array();
            // $rates[ $rate_id ]->label = 'Free Shipping';
        }
    }
    return $rates;
}


function set_query_params_as_cookies_script() {
    // Check if product_sku or sku are present in the URL
    if (isset($_GET['product_sku']) || isset($_GET['sku'])) {
    ?>
    <script>
        // Helper function to get query parameters
        function getQueryParams() {
            const params = new URLSearchParams(window.location.search);
            return {
                product_sku: params.get("product_sku"),
                sku: params.get("sku")
            };
        }

        // Helper function to set a cookie
        function setCookie(name, value, minutes = 5) {
            const expires = new Date(Date.now() + minutes * 60 * 1000).toUTCString();
            document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/`;
        }

        // Set cookies for product_sku and sku if they exist in query parameters
        const { product_sku, sku } = getQueryParams();
        if (product_sku) setCookie("product_sku", product_sku);
        if (sku) setCookie("sku", sku);

        // Delete cookies if matching API call is made
        function deleteCookies() {
            document.cookie = "product_sku=; Max-Age=0; path=/";
            document.cookie = "sku=; Max-Age=0; path=/";
        }

        // Intercept fetch and XMLHttpRequest for specific API call
        const interceptRequest = (method, url) => {
            if (url.includes("wc-ajax=lwmc_get_cart_by_sku")) {
                deleteCookies();
            }
        };

        // Intercept fetch
        (function() {
            const originalFetch = window.fetch;
            window.fetch = (...args) => {
                interceptRequest(...args);
                return originalFetch.apply(this, args);
            };
        })();

        // Intercept XMLHttpRequest
        (function() {
            const open = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function (method, url, ...args) {
                interceptRequest(method, url);
                open.call(this, method, url, ...args);
            };
        })();
    </script>
    <?php
    }
}
add_action('wp_footer', 'set_query_params_as_cookies_script');


add_action('wp_footer', 'fix_pay_button_final_for_all_errors', 20);
function fix_pay_button_final_for_all_errors() {
    if (is_checkout() && !is_order_received_page()) :
    ?>
    <script type="text/javascript">
    jQuery(function($) {
        const $button = $('#place_order');
        if (!$button.length) return;

        const isInput = $button.is('input');
        const defaultText = isInput ? $button.val() : $button.text();
        const processingText = 'Processing...';
        const delayBeforeDisable = 300;

        let formSubmitted = false;

        // Intercept form submission
        const $form = $('form.woocommerce-checkout, form#order_review');

        // Hook into actual form submit
        $form.on('submit', function() {
            formSubmitted = true;
        });

        // On button click
        $button.on('click', function(e) {
            if ($button.prop('disabled')) {
                e.preventDefault();
                return;
            }

            // Update text to "Processing..."
            if (isInput) {
                $button.val(processingText);
            } else {
                $button.text(processingText);
            }

            // Delay disable to let Stripe/others hook in
            setTimeout(() => {
                $button.prop('disabled', true);
            }, delayBeforeDisable);

            // Reset the button after a short delay if form doesn't actually submit
            setTimeout(() => {
                if (!formSubmitted) {
                    resetButton();
                }
            }, 1500); // Give Woo time to run validation
        });

        function resetButton() {
            $button.prop('disabled', false);
            if (isInput) {
                $button.val(defaultText);
            } else {
                $button.text(defaultText);
            }
        }
    });
    </script>
    <?php
    endif;
}


// Force login before showing the Customer Payment Link
/*add_action( 'template_redirect', function() {

    if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-pay' ) ) {
        
        // If user not logged in
        if ( ! is_user_logged_in() ) {
            
            // Save current URL to redirect back after login
            wc_add_notice( __( 'Please log in to pay for your order so we can save your payment method.' ), 'notice' );
            
            $redirect_url = esc_url( wc_get_checkout_url() ); // fallback
            if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
                $redirect_url = esc_url( home_url( '/login/' ) ) . '?redirect_to=' . urlencode( home_url( $_SERVER['REQUEST_URI'] ) );
                console.log('main url' . $redirect_url);
            }
            
            wp_safe_redirect( $redirect_url );
            exit;
        }
    }

}, 5 );*/


// Auto-login customer on order-pay page if email matches existing account
/*add_action( 'template_redirect', function() {

    if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-pay' ) ) {

        // Already logged in? Do nothing.
        if ( is_user_logged_in() ) {
            return;
        }

        // Get the order ID from the URL
        $order_id = get_query_var( 'order-pay' );
        if ( ! $order_id ) {
            return;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        // Get billing email from order
        $billing_email = $order->get_billing_email();
        if ( ! $billing_email ) {
            return;
        }

        // Find a WP user with that email
        $user = get_user_by( 'email', $billing_email );
        if ( $user && ! is_user_logged_in() ) {

            // Log them in
            wp_set_current_user( $user->ID );
            wp_set_auth_cookie( $user->ID );

            // Redirect back to same URL without extra query junk
            wp_safe_redirect( $order->get_checkout_payment_url() );
            exit;
        }

        // No matching account — redirect to login page
        wc_add_notice( __( 'Please log in to pay for your order so we can save your payment method.' ), 'notice' );
        $redirect_url = esc_url( home_url( '/login/' ) ) . '?redirect_to=' . urlencode( home_url( $_SERVER['REQUEST_URI'] ) );
        wp_safe_redirect( $redirect_url );
        exit;
    }

}, 5 );*/

/**
 * Auto login on order-pay and auto logout after successful payment
 */

add_action( 'template_redirect', function() {

    // ----- Auto Login on order-pay -----
    if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-pay' ) && ! is_user_logged_in() ) {

        $order_id = absint( get_query_var( 'order-pay' ) );
        if ( $order_id && ( $order = wc_get_order( $order_id ) ) ) {

            $billing_email = $order->get_billing_email();
            if ( $billing_email && ( $user = get_user_by( 'email', $billing_email ) ) ) {

                // Log them in
                wp_set_current_user( $user->ID );
                wp_set_auth_cookie( $user->ID );

                // Store flag in WC session for logout after payment
                if ( function_exists( 'WC' ) && WC()->session ) {
                    WC()->session->set( 'logout_after_payment_order_id', $order_id );
                }

                // Refresh page as logged-in user
                wp_safe_redirect( $order->get_checkout_payment_url() );
                exit;
            }
        }
    }

    // ----- Auto Logout on order-received -----
    if ( function_exists( 'is_order_received_page' ) && is_order_received_page() && is_user_logged_in() ) {

        $order_id = absint( get_query_var( 'order-received' ) );
        if ( $order_id && ( $order = wc_get_order( $order_id ) ) && $order->is_paid() ) {

            if ( function_exists( 'WC' ) && WC()->session ) {
                $flag_order_id = WC()->session->get( 'logout_after_payment_order_id' );

                if ( $flag_order_id && intval( $flag_order_id ) === $order_id ) {

                    // Clear the flag to avoid affecting future orders
                    WC()->session->__unset( 'logout_after_payment_order_id' );

                    // Schedule logout after page render so saved payment methods are still displayed
                    add_action( 'shutdown', function() {
                        if ( function_exists( 'WC' ) && WC()->session ) {
                            WC()->session->destroy_session();
                        }
                        wp_logout();
                        wp_safe_redirect( home_url );
                    });
                }
            }
        }
    }

}, 5);