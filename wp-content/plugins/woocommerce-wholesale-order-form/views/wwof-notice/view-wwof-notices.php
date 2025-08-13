<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

    if( !function_exists( 'is_plugin_active' ) )
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    $wwp_active     = is_plugin_active( 'woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php' );
    $wwp_notice     = get_option( 'wwp_admin_notice_getting_started_show' );

    $wwpp_active    = is_plugin_active( 'woocommerce-wholesale-prices-premium/woocommerce-wholesale-prices-premium.bootstrap.php' );
    $wwpp_notice    = get_option( 'wwp_admin_notice_getting_started_show' );

    $active_counter = 1;
    
    if( ( $wwp_active && $wwp_notice === 'yes' ) || ( $wwpp_active && $wwpp_notice === 'yes' ) ) $active_counter++;
    
    $wwp_getting_started_link   = 'https://wholesalesuiteplugin.com/kb/woocommerce-wholesale-prices-free-plugin-getting-started-guide/?utm_source=freeplugin&utm_medium=kb&utm_campaign=wwpgettingstarted';
    $wwpp_getting_started_link  = 'https://wholesalesuiteplugin.com/kb/woocommerce-wholesale-prices-premium-getting-started-guide/?utm_source=wwpp&utm_medium=kb&utm_campaign=wwppgettingstarted';
    $wwof_getting_started_link  = 'https://wholesalesuiteplugin.com/kb/woocommerce-wholesale-order-form-getting-started-guide/?utm_source=wwof&utm_medium=kb&utm_campaign=wwofgettingstarted';

    // Check if current user is admin or shop manager
    // Check if getting started option is 'yes'
    if( ( current_user_can( 'administrator' ) || current_user_can( 'shop_manager' ) ) && ( get_option( 'wwof_admin_notice_getting_started_show' ) === 'yes' || get_option( 'wwof_admin_notice_getting_started_show' ) === false ) ) { 

        $screen = get_current_screen(); 

        // Check if WWS license page
        // Check if products pages
        // Check if woocommerce pages ( wc, products, analytics )
        // Check if plugins page
        if( $screen->id === 'settings_page_wwc_license_settings' || $screen->post_type === 'product' || in_array( $screen->parent_base , array( 'woocommerce' , 'plugins' ) ) ) {

            if( $active_counter > 1 ) { ?>

                <div class="updated notice wwof-getting-started">
                    <p><img src="<?php echo WWOF_IMAGES_ROOT_URL; ?>wholesale-suite-activation-notice-logo.png" alt=""/></p>
                    <p><?php _e( 'Thank you for choosing Wholesale Suite – the most complete wholesale solution for building wholesale sales into your existing WooCommerce driven store.' , 'woocommercew-wholesale-order-form' ); ?></p>
                    <p><?php _e( 'To help you get up and running as quickly and as smoothly as possible, we\'ve published a number of getting started guides for our tools. You\'ll find links to these at any time inside the Help section in the settings for each plugin, but here are the links below so you can read them now.' , 'woocommercew-wholesale-order-form' ); ?></p>
                    <p><?php 
                    
                        if( $wwpp_active && $wwpp_notice === 'yes' ) { ?>
                            <a href="<?php echo $wwpp_getting_started_link; ?>" target="_blank">
                                <?php _e( 'Wholesale Prices Premium Guide' , 'woocommerce-wholesale-order-form' ); ?>
                                <span class="dashicons dashicons-arrow-right-alt" style="margin-top: 5px"></span>
                            </a><?php 
                        } else if( $wwp_active && $wwp_notice === 'yes' ) { ?>
                            <a href="<?php echo $wwp_getting_started_link; ?>" target="_blank">
                                <?php _e( 'Wholesale Prices Guide' , 'woocommerce-wholesale-order-form' ); ?>
                                <span class="dashicons dashicons-arrow-right-alt" style="margin-top: 5px"></span>
                            </a><?php 
                        } ?>

                        <a href="<?php echo $wwof_getting_started_link; ?>" target="_blank">
                            <?php _e( 'Wholesale Order Form Guide' , 'woocommerce-wholesale-order-form' ); ?>
                            <span class="dashicons dashicons-arrow-right-alt" style="margin-top: 5px"></span>
                        </a>

                    </p>
                    <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.' , 'woocommerce-wholesale-order-form' ); ?></span></button>
                </div><?php

            } else { ?>

                <div class="updated notice wwof-getting-started">
                    <p><img src="<?php echo WWOF_IMAGES_ROOT_URL; ?>wholesale-suite-activation-notice-logo.png" alt=""/></p>
                    <p><?php _e( 'Thank you for choosing Order Form to provide an efficient, optimized ordering experience for you wholeale customers. We know they\'re going to love it!' , 'woocommerce-wholesale-order-form' ); ?>
                    <p><?php _e( 'The plugin has already created an order form page which you\'ll find under the Pages menu. We highly recommend reading the getting started guide to help you get up to speed on customizing order form experience.' , 'woocommerce-wholesale-order-form' ); ?>
                    <p><a href="<?php echo $wwof_getting_started_link; ?>" target="_blank">
                        <?php _e( 'Read the Getting Started guide' , 'woocommerce-wholesale-order-form' ); ?>
                        <span class="dashicons dashicons-arrow-right-alt" style="margin-top: 5px"></span>
                    </a></p>
                    <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.' , 'woocommerce-wholesale-order-form' ); ?></span></button>
                </div><?php
                
            }

        }

    }
?>