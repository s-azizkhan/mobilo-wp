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
class LoginAsUser_AdminPro {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * This fields
	 *
	 * @var [class]
	 */
	public $fields;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->plugin_name_clean = 'login-as-user-pro';
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() 
	{
		wp_enqueue_style( $this->plugin_name_clean, plugin_dir_url( __FILE__ ) . 'css/admin.min.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() 
	{
		wp_enqueue_script( $this->plugin_name_clean, plugin_dir_url( __FILE__ ) . 'js/admin.min.js', array( 'jquery', ), $this->version, true );
		wp_localize_script( $this->plugin_name_clean, 'loginasuserAjax', array( 'loginasuser_ajaxurl' => admin_url( 'admin-ajax.php' )));        
	}

	
	public function web357_license_key_validation() 
	{
		if ( !wp_verify_nonce( $_REQUEST['nonce'], "web357_license_key_validation_nonce")) {
		   exit("Error (web357_license_key_validation_nonce). Please, contact us at support@web357.com");
		}   

		$get_api_key = isset($_REQUEST["key"]) ? $_REQUEST["key"] : null;
		$get_domain = isset($_REQUEST["domain"]) ? $_REQUEST["domain"] : null;

		if (empty($get_api_key))
		{
			die(esc_html__('The License Key cannot be empty.', 'login-as-user-pro'));
		}

		// Create the request Array.
		$paramArr = array(
			'domain'    => $get_domain,
		);

		// Create an Http Query.//
		$paramArr = http_build_query($paramArr);
		
		// Post
		$url = 'https://www.web357.com/wp-json/web357-api-key/v1/status/'.$get_api_key;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $paramArr);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);

		$resp = curl_exec($ch);
		curl_close($ch);

		if ($resp === FALSE || empty($resp) || $resp == '') 
		{
			$result['type'] = "success";
			$result['message'] = '<div class="w357-alert w357-alert-danger">Call with Web357\'s License Manager has been failed. <br>Please, try again later or contact us at support@web357.com.</div>';
		} 
		else 
		{
			$resp = json_decode($resp);

			if (isset($resp->req->data->status) && ($resp->req->data->status == 'ok' || $resp->req->data->status == 'ok_old_api_key'))
			{
				$result['type'] = "success";
				$result['message'] = '<div class="w357-alert w357-alert-success">Your License Key ('. $get_api_key . ') has been successfully activated.</div>';
			}
			elseif ($resp->code == 'error' && !empty($resp->message))
			{
				$result['type'] = "success";
		   		$result['message'] = '<div class="w357-alert w357-alert-danger"> ' . $resp->message . '</div>';
			}
			else
			{
				$result['type'] = "success";
				$result['message'] = '<div class="w357-alert w357-alert-danger">Call with Web357\'s License Manager has been failed. <br>Please, try again later or contact us at support@web357.com.</div>';
			}
		}

		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$result = json_encode($result);
			echo $result;
		}
		else {
			header("Location: ".$_SERVER["HTTP_REFERER"]);
		}
	  
		die();
	}
	 
	public function web357_license_key_validation_must_login() {
		echo "You must log in first.";
		die();
	}
	
}