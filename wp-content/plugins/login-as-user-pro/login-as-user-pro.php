<?php
/* ======================================================
 # Login as User for WordPress - v1.4.4 (pro version)
 # -------------------------------------------------------
 # For WordPress
 # Author: Web357
 # Copyright @ 2014-2022 Web357. All rights reserved.
 # License: GNU/GPLv3, http://www.gnu.org/licenses/gpl-3.0.html
 # Website: https:/www.web357.com
 # Demo: https://demo.web357.com/wordpress/login-as-user/wp-admin/
 # Support: support@web357.com
 # Last modified: Tuesday 14 June 2022, 06:08:05 PM
 ========================================================= */
 
/**
 * Plugin Name:       Login as User (PRO version)
 * Plugin URI:        https://www.web357.com/product/login-as-user-wordpress-plugin
 * Description:       Login as User is a free WordPress plugin that helps admins switch user accounts instantly to check data.
 * Version:           1.4.4
 * Author:            Web357
 * Author URI:        https://www.web357.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       login-as-user-pro
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
if ( !defined( 'LOGINASUSER_VERSION' ) ) {
	define( 'LOGINASUSER_VERSION', '1.4.4' );
}

/** 
 * Update Checker
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/plugin-update-checker/plugin-update-checker.php';
$web357UpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://wp-updates.web357.com/?action=get_metadata&slug=login-as-user-pro-wp-pro&item=login-as-user-pro&release_type=&license_type=pro&cms=wp',
	__FILE__,
	'login-as-user-pro'
);
$web357UpdateChecker->addQueryArgFilter('wsh_filter_update_checks_web357_login_as_user');
function wsh_filter_update_checks_web357_login_as_user($queryArgs) 
{
	$options = get_option('login_as_user_options');
	$w357_license_key = $options['license_key'];
	if (!empty($w357_license_key)) 
    {
        $queryArgs['license_key'] = urlencode($w357_license_key);
	}
	else
	{
        $queryArgs['license_key'] = '';
	}

    $queryArgs['item'] = 'login-as-user-pro';
    $queryArgs['release_type'] = '';
    $queryArgs['license_type'] = 'pro';
	$queryArgs['cms'] = 'wp';
	$queryArgs['domain'] = $_SERVER['SERVER_NAME'];

	return $queryArgs;
}

/**
 * Displays a notification about the empty license key in updates manager
 */
if( is_admin()) 
{
	add_action('in_plugin_update_message-' . plugin_basename(__FILE__), 'w357_modify_plugin_update_message', 10, 2 );	
	function w357_modify_plugin_update_message( $plugin_data, $response ) 
	{
		$options = get_option('login_as_user_options');
		$w357_license_key = $options['license_key'];
		if (empty($w357_license_key)) 
		{
			echo '<br />' . sprintf( __('To enable updates, please enter your license key on the <a href="%s">Login as User</a> page. If you don\'t have a licence key, please see <a href="%s" target="_blank">Plans & Pricing</a>.', 'login-as-user-pro'), admin_url('options-general.php?page=login-as-user-pro'), 'https://www.web357.com/pricing' );
		}
	}
}


/**
 * The code that runs during plugin activation.
 */
function activate_LoginAsUserPro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-activator.php';
	LoginAsUser_ActivatorPro::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_LoginAsUserPro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-deactivator.php';
	LoginAsUser_DeactivatorPro::deactivate();
}

register_activation_hook( __FILE__, 'activate_LoginAsUserPro' );
register_deactivation_hook( __FILE__, 'deactivate_LoginAsUserPro' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-main.php';

/**
 * Begins execution of the plugin.
 */
function run_LoginAsUserPro() 
{
	$plugin = new LoginAsUserPro();
	$plugin->run();
}
run_LoginAsUserPro();


/**
 * Deactivate the FREE version after installing the PRO.
 *
 * @return void
 */
function w357_pro_deactivate_free_version_notice_login_as_user_pro() 
{
?>
<div class="notice notice-error is-dismissible">
	<p><?php echo sprintf( __( 'You need to deactivate and delete the old <b>Login as User (Free) version of plugin</b> on the plugins page. %sClick here to Deactivate it%s.', 'login-as-user-pro' ), '<a href="' . (na_action_link_login_as_user_pro( 'login-as-user/login-as-user.php', 'deactivate' )) . '">', '</a>' ); ?></p>
</div>
<?php
}

add_action('plugins_loaded', 'w357_pro_add_core_login_as_user_pro');
function w357_pro_add_core_login_as_user_pro() 
{
	if (class_exists( 'LoginAsUser_Admin'))
	{
		add_action('admin_notices', 'w357_pro_deactivate_free_version_notice_login_as_user_pro');
		return;
	}
}

function na_action_link_login_as_user_pro( $plugin, $action = 'activate' ) 
{
	if (strpos( $plugin, '/' )) 
	{
		$plugin = str_replace( '\/', '%2F', $plugin );
	}
	$url = sprintf( admin_url( 'plugins.php?action=' . $action . '&plugin=%s&plugin_status=all&paged=1&s' ), $plugin );
	$_REQUEST['plugin'] = $plugin;
	$url = wp_nonce_url( $url, $action . '-plugin_' . $plugin );

	return $url;
}


// Load the main functionality of plugin
require_once (plugin_dir_path( __FILE__ ) . 'includes/class-w357-login-as-user.php');