<?php

namespace GithubDeployer;

if ( ! class_exists( 'Theme_Upgrader' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-theme-upgrader.php';
}

/**
 * Authenticated Theme Upgrader for private repositories
 *
 * @package GithubDeployer
 */
class AuthenticatedThemeUpgrader extends \Theme_Upgrader {

	/**
	 * Authentication options
	 *
	 * @var array
	 */
	private $auth_options;

	/**
	 * Constructor
	 *
	 * @param \WP_Upgrader_Skin $skin Upgrader skin.
	 * @param array             $auth_options Authentication options.
	 */
	public function __construct( $skin = null, $auth_options = array() ) {
		parent::__construct( $skin );
		$this->auth_options = $auth_options;
	}

	/**
	 * Download a package
	 *
	 * @param string $package Package URL.
	 * @param bool   $check_signatures Whether to check signatures.
	 * @param array  $hook_extra Extra arguments to pass to the filter hooks.
	 * @return string|WP_Error The package file path or a WP_Error object.
	 */
	public function download_package( $package, $check_signatures = false, $hook_extra = array() ) {
		// Add authentication headers if this is a private repository
		if ( ! empty( $this->auth_options ) && isset( $this->auth_options['is_private_repository'] ) && $this->auth_options['is_private_repository'] ) {
			add_filter( 'http_request_args', array( $this, 'add_auth_headers' ), 10, 2 );
		}

		$result = parent::download_package( $package, $check_signatures, $hook_extra );

		// Remove the filter after download
		if ( ! empty( $this->auth_options ) && isset( $this->auth_options['is_private_repository'] ) && $this->auth_options['is_private_repository'] ) {
			remove_filter( 'http_request_args', array( $this, 'add_auth_headers' ) );
		}

		return $result;
	}

	/**
	 * Add authentication headers to HTTP requests
	 *
	 * @param array  $args HTTP request arguments.
	 * @param string $url  Request URL.
	 * @return array Modified HTTP request arguments.
	 */
	public function add_auth_headers( $args, $url ) {
		// Only add headers for GitHub URLs
		if ( strpos( $url, 'github.com' ) === false ) {
			return $args;
		}

		if ( empty( $args['headers'] ) ) {
			$args['headers'] = array();
		}

		// Add access token if available
		if ( ! empty( $this->auth_options['access_token'] ) ) {
			$args['headers']['Authorization'] = 'token ' . $this->auth_options['access_token'];
		}

		// Add basic auth if username and password are available
		if ( ! empty( $this->auth_options['username'] ) && ! empty( $this->auth_options['password'] ) ) {
			$auth_string = base64_encode( $this->auth_options['username'] . ':' . $this->auth_options['password'] );
			$args['headers']['Authorization'] = 'Basic ' . $auth_string;
		}

		// Add User-Agent header
		$args['headers']['User-Agent'] = 'WordPress/GithubDeployer';

		return $args;
	}
} 