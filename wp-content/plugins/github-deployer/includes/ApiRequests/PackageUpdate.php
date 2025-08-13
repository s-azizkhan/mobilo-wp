<?php

namespace GithubDeployer\ApiRequests;

use GithubDeployer\DataManager;
use GithubDeployer\Helper;
use GithubDeployer\Logger;
use GithubDeployer\Subpages\InstallPackage;

/**
 * PackageUpdate class
 * Used to handle the package installation or update
 */
class PackageUpdate {

	/**
	 * Constructor
	 * Register the REST API route
	 */
	public function __construct() {
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'gd/v1',
					'/package_update/',
					array(
						array(
							'methods'             => 'GET',
							'callback'            => array( $this, 'update_package_callback' ),
							'permission_callback' => '__return_true',
						),
						array(
							'methods'             => 'POST',
							'callback'            => array( $this, 'update_package_callback' ),
							'permission_callback' => '__return_true',
						),
					)
				);
			}
		);

		// Add AJAX handlers for enhanced update functionality
		add_action( 'wp_ajax_fetch_update_repository_data', array( $this, 'ajax_fetch_update_repository_data' ) );
		add_action( 'wp_ajax_update_package_with_ref', array( $this, 'ajax_update_package_with_ref' ) );
	}

	/**
	 * AJAX handler for fetching repository data during updates
	 */
	public function ajax_fetch_update_repository_data() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'gd_update_repo_data' ) ) {
			wp_die( 'Invalid nonce' );
		}

		$package_slug = sanitize_text_field( $_POST['package_slug'] );
		$package_type = sanitize_text_field( $_POST['package_type'] );

		if ( empty( $package_slug ) || empty( $package_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Package slug and type are required', 'github-deployer' ) ) );
		}

		$data_manager = new DataManager();
		
		if ( 'theme' === $package_type ) {
			$package_details = $data_manager->get_theme( $package_slug );
		} else {
			$package_details = $data_manager->get_plugin( $package_slug );
		}

		if ( false === $package_details ) {
			wp_send_json_error( array( 'message' => __( 'Package not found', 'github-deployer' ) ) );
		}

		try {
			$provider_class_name = Helper::get_provider_class( $package_details['provider'] );
			$provider = new $provider_class_name( $package_details['repo_url'] );
			
			$repo_data = $this->fetch_repository_data_for_update( $provider, $package_details );
			
			wp_send_json_success( $repo_data );
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * AJAX handler for updating package with specific ref (branch or commit)
	 */
	public function ajax_update_package_with_ref() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'gd_update_package' ) ) {
			wp_die( 'Invalid nonce' );
		}

		$package_slug = sanitize_text_field( $_POST['package_slug'] );
		$package_type = sanitize_text_field( $_POST['package_type'] );
		$selected_ref = sanitize_text_field( $_POST['selected_ref'] );

		if ( empty( $package_slug ) || empty( $package_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Package slug and type are required', 'github-deployer' ) ) );
		}

		$data_manager = new DataManager();
		
		if ( 'theme' === $package_type ) {
			$package_details = $data_manager->get_theme( $package_slug );
		} else {
			$package_details = $data_manager->get_plugin( $package_slug );
		}

		if ( false === $package_details ) {
			wp_send_json_error( array( 'message' => __( 'Package not found', 'github-deployer' ) ) );
		}

		try {
			$provider_class_name = Helper::get_provider_class( $package_details['provider'] );
			$provider = new $provider_class_name( $package_details['repo_url'] );
			
			$package_slug = $provider->get_package_slug();
			
			// Use selected ref if provided, otherwise use stored branch
			$deployment_ref = ! empty( $selected_ref ) ? $selected_ref : $package_details['branch'];
			
			$package_zip_url = $provider->get_zip_repo_url( $deployment_ref );

			// If the provider is GitHub, we need to do some additional checks.
			if ( $provider instanceof \GithubDeployer\Providers\GithubProvider ) {
				$github_token = isset( $package_details['options']['access_token'] ) ? $package_details['options']['access_token'] : '';
				$package_zip_url = $provider->get_zip_repo_url( $deployment_ref, $github_token );
			}

			$package_install_options = array();

			if ( array_key_exists( 'is_private_repository', $package_details ) && $package_details['is_private_repository'] === true ) {
				$package_install_options = array(
					'is_private_repository' => true,
					'username'              => $package_details['options']['username'],
					'password'              => $package_details['options']['password'],
					'access_token'          => $package_details['options']['access_token'],
				);
			}

			$package_install_options['wp_json_request'] = true;

			$install_result = InstallPackage::install_package_from_zip_url( $package_zip_url, $package_slug, $package_type, $package_details['provider'], $package_install_options );

			$success = ( is_wp_error( $install_result ) ? false : true );
			$message = '';

			$logger = new Logger();
			if ( $success ) {
				$logger->log( "Package ({$package_type}) \"{$package_slug}\" successfully updated via AJAX with ref: {$deployment_ref}" );
				$message = esc_attr__( 'Package updated successfully.', 'github-deployer' );

				$flush_cache_setting_enabled = $data_manager->get_flush_cache_setting();
				if ( $flush_cache_setting_enabled ) {
					$logger->log( "Flushing cache after package ({$package_type}) \"{$package_slug}\" update via AJAX" );
					$message .= ' ' . esc_attr__( 'Cache flushed.', 'github-deployer' );
					Helper::trigger_cache_flush();
				}
			} elseif ( is_wp_error( $install_result ) ) {
				$error_message = $install_result->get_error_message();
				$logger->log( "Error occurred while updating a package ({$package_type}) \"{$package_slug}\" via AJAX \"{$error_message}\"" );
				$message = $error_message;
			}

			wp_send_json_success( array(
				'success' => $success,
				'message' => $message,
				'package_type' => $package_type,
				'package_slug' => $package_slug,
			) );

		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * Fetch repository data for updates
	 */
	private function fetch_repository_data_for_update( $provider, $package_details ) {
		$repo_handle = $this->get_repo_handle_from_url( $provider->get_repo_url() );
		$provider_type = $package_details['provider'];
		$is_private = isset( $package_details['is_private_repository'] ) && $package_details['is_private_repository'];
		$access_token = isset( $package_details['options']['access_token'] ) ? $package_details['options']['access_token'] : '';
		
		$api_url = '';
		$headers = array();

		// Set up API URL and headers based on provider
		switch ( $provider_type ) {
			case 'github':
				$api_url = "https://api.github.com/repos/{$repo_handle}";
				if ( $is_private && ! empty( $access_token ) ) {
					$headers['Authorization'] = 'token ' . $access_token;
				}
				break;
			case 'gitlab':
				$api_url = "https://gitlab.com/api/v4/projects/" . urlencode( $repo_handle );
				if ( $is_private && ! empty( $access_token ) ) {
					$headers['Authorization'] = 'Bearer ' . $access_token;
				}
				break;
			case 'bitbucket':
				$api_url = "https://api.bitbucket.org/2.0/repositories/{$repo_handle}";
				if ( $is_private && ! empty( $access_token ) ) {
					$headers['Authorization'] = 'Bearer ' . $access_token;
				}
				break;
			case 'gitea':
				// Gitea doesn't have a standard API endpoint, so we'll return basic data
				return array(
					'branches' => array( 'master', 'main', 'develop' ),
					'commits' => array(),
					'default_branch' => 'master',
					'current_branch' => $package_details['branch']
				);
		}

		if ( empty( $api_url ) ) {
			return array(
				'branches' => array( 'master', 'main' ),
				'commits' => array(),
				'default_branch' => 'master',
				'current_branch' => $package_details['branch']
			);
		}

		// Fetch repository data
		$response = wp_remote_get( $api_url, array(
			'headers' => $headers,
			'timeout' => 30
		) );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( __( 'Failed to fetch repository data', 'github-deployer' ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! $data ) {
			throw new \Exception( __( 'Invalid repository data received', 'github-deployer' ) );
		}

		// Extract branches and commits based on provider
		$branches = array();
		$commits = array();
		$default_branch = 'master';

		switch ( $provider_type ) {
			case 'github':
				$default_branch = $data['default_branch'] ?? 'master';
				$branches = $this->fetch_github_branches( $repo_handle, $is_private, $access_token );
				$commits = $this->fetch_github_commits( $repo_handle, $package_details['branch'], $is_private, $access_token );
				break;
			case 'gitlab':
				$default_branch = $data['default_branch'] ?? 'master';
				$branches = $this->fetch_gitlab_branches( $repo_handle, $is_private, $access_token );
				$commits = $this->fetch_gitlab_commits( $repo_handle, $package_details['branch'], $is_private, $access_token );
				break;
			case 'bitbucket':
				$default_branch = $data['mainbranch']['name'] ?? 'master';
				$branches = $this->fetch_bitbucket_branches( $repo_handle, $is_private, $access_token );
				$commits = $this->fetch_bitbucket_commits( $repo_handle, $package_details['branch'], $is_private, $access_token );
				break;
		}

		return array(
			'branches' => $branches,
			'commits' => $commits,
			'default_branch' => $default_branch,
			'current_branch' => $package_details['branch']
		);
	}

	/**
	 * Fetch GitHub branches
	 */
	private function fetch_github_branches( $repo_handle, $is_private, $access_token ) {
		$api_url = "https://api.github.com/repos/{$repo_handle}/branches";
		$headers = array();
		
		if ( $is_private && ! empty( $access_token ) ) {
			$headers['Authorization'] = 'token ' . $access_token;
		}

		$response = wp_remote_get( $api_url, array(
			'headers' => $headers,
			'timeout' => 30
		) );

		if ( is_wp_error( $response ) ) {
			return array( 'master', 'main' );
		}

		$branches_data = json_decode( wp_remote_retrieve_body( $response ), true );
		$branches = array();

		if ( is_array( $branches_data ) ) {
			foreach ( $branches_data as $branch ) {
				$branches[] = $branch['name'];
			}
		}

		return empty( $branches ) ? array( 'master', 'main' ) : $branches;
	}

	/**
	 * Fetch GitHub commits
	 */
	private function fetch_github_commits( $repo_handle, $branch, $is_private, $access_token ) {
		$api_url = "https://api.github.com/repos/{$repo_handle}/commits?sha={$branch}&per_page=10";
		$headers = array();
		
		if ( $is_private && ! empty( $access_token ) ) {
			$headers['Authorization'] = 'token ' . $access_token;
		}

		$response = wp_remote_get( $api_url, array(
			'headers' => $headers,
			'timeout' => 30
		) );

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$commits_data = json_decode( wp_remote_retrieve_body( $response ), true );
		$commits = array();

		if ( is_array( $commits_data ) ) {
			foreach ( $commits_data as $commit ) {
				$commits[] = array(
					'sha' => $commit['sha'],
					'message' => $commit['commit']['message'],
					'date' => $commit['commit']['author']['date']
				);
			}
		}

		return $commits;
	}

	/**
	 * Fetch GitLab branches
	 */
	private function fetch_gitlab_branches( $repo_handle, $is_private, $access_token ) {
		$api_url = "https://gitlab.com/api/v4/projects/" . urlencode( $repo_handle ) . "/repository/branches";
		$headers = array();
		
		if ( $is_private && ! empty( $access_token ) ) {
			$headers['Authorization'] = 'Bearer ' . $access_token;
		}

		$response = wp_remote_get( $api_url, array(
			'headers' => $headers,
			'timeout' => 30
		) );

		if ( is_wp_error( $response ) ) {
			return array( 'master', 'main' );
		}

		$branches_data = json_decode( wp_remote_retrieve_body( $response ), true );
		$branches = array();

		if ( is_array( $branches_data ) ) {
			foreach ( $branches_data as $branch ) {
				$branches[] = $branch['name'];
			}
		}

		return empty( $branches ) ? array( 'master', 'main' ) : $branches;
	}

	/**
	 * Fetch GitLab commits
	 */
	private function fetch_gitlab_commits( $repo_handle, $branch, $is_private, $access_token ) {
		$api_url = "https://gitlab.com/api/v4/projects/" . urlencode( $repo_handle ) . "/repository/commits?ref_name={$branch}&per_page=10";
		$headers = array();
		
		if ( $is_private && ! empty( $access_token ) ) {
			$headers['Authorization'] = 'Bearer ' . $access_token;
		}

		$response = wp_remote_get( $api_url, array(
			'headers' => $headers,
			'timeout' => 30
		) );

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$commits_data = json_decode( wp_remote_retrieve_body( $response ), true );
		$commits = array();

		if ( is_array( $commits_data ) ) {
			foreach ( $commits_data as $commit ) {
				$commits[] = array(
					'sha' => $commit['id'],
					'message' => $commit['message'],
					'date' => $commit['created_at']
				);
			}
		}

		return $commits;
	}

	/**
	 * Fetch Bitbucket branches
	 */
	private function fetch_bitbucket_branches( $repo_handle, $is_private, $access_token ) {
		$api_url = "https://api.bitbucket.org/2.0/repositories/{$repo_handle}/refs/branches";
		$headers = array();
		
		if ( $is_private && ! empty( $access_token ) ) {
			$headers['Authorization'] = 'Bearer ' . $access_token;
		}

		$response = wp_remote_get( $api_url, array(
			'headers' => $headers,
			'timeout' => 30
		) );

		if ( is_wp_error( $response ) ) {
			return array( 'master', 'main' );
		}

		$branches_data = json_decode( wp_remote_retrieve_body( $response ), true );
		$branches = array();

		if ( isset( $branches_data['values'] ) && is_array( $branches_data['values'] ) ) {
			foreach ( $branches_data['values'] as $branch ) {
				$branches[] = $branch['name'];
			}
		}

		return empty( $branches ) ? array( 'master', 'main' ) : $branches;
	}

	/**
	 * Fetch Bitbucket commits
	 */
	private function fetch_bitbucket_commits( $repo_handle, $branch, $is_private, $access_token ) {
		$api_url = "https://api.bitbucket.org/2.0/repositories/{$repo_handle}/commits/{$branch}?pagelen=10";
		$headers = array();
		
		if ( $is_private && ! empty( $access_token ) ) {
			$headers['Authorization'] = 'Bearer ' . $access_token;
		}

		$response = wp_remote_get( $api_url, array(
			'headers' => $headers,
			'timeout' => 30
		) );

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$commits_data = json_decode( wp_remote_retrieve_body( $response ), true );
		$commits = array();

		if ( isset( $commits_data['values'] ) && is_array( $commits_data['values'] ) ) {
			foreach ( $commits_data['values'] as $commit ) {
				$commits[] = array(
					'sha' => $commit['hash'],
					'message' => $commit['message'],
					'date' => $commit['date']
				);
			}
		}

		return $commits;
	}

	/**
	 * Get repository handle from URL
	 */
	private function get_repo_handle_from_url( $repo_url ) {
		$parsed_url = wp_parse_url( $repo_url );
		$parts = explode( '/', $parsed_url['path'] );
		$handle = '';
		
		foreach ( $parts as $index => $part ) {
			if ( $index === 0 ) {
				continue;
			}
			$handle .= $part . '/';

			// if end of the path, remove the trailing slash.
			if ( $index === count( $parts ) - 1 ) {
				$handle = rtrim( $handle, '/' );
			}
		}

		return $handle;
	}

	/**
	 * Get the package update URL
	 *
	 * @param string $package_slug slug of the package.
	 * @param string $type (theme|plugin).
	 * @return string $url
	 */
	public static function package_update_url( $package_slug = '', $type = 'theme' ) {
		$type = ( 'theme' === $type ) ? 'theme' : 'plugin';
		$url  = sprintf( '%s/wp-json/gd/v1/package_update?secret=%s&type=%s&package=%s', site_url(), Helper::get_api_secret(), $type, $package_slug );

		return $url;
	}

	/**
	 * Handle the package update request
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return array $response
	 */
	public function update_package_callback( $request ) {
		// Handle the request and return data as JSON.
		$secret       = $request->get_param( 'secret' );
		$package_slug = $request->get_param( 'package' );
		$package_type = $request->get_param( 'type' ) === 'plugin' ? 'plugin' : 'theme';

		do_action( 'gd_before_api_package_update', $package_slug, $package_type );

		if ( null === $secret || Helper::get_api_secret() !== $secret ) {
			// set status code to 401.
			status_header( 401 );

			return array(
				'success' => false,
				'message' => 'Invalid secret',
			);
		}

		$data_manager = new DataManager();

		if ( 'theme' === $package_type ) {
			$package_details = $data_manager->get_theme( $package_slug );
		} else {
			$package_details = $data_manager->get_plugin( $package_slug );
		}

		if ( false === $package_details ) {
			status_header( 401 );

			return array(
				'success' => false,
				'message' => 'Invalid package',
			);
		}

		try {
			$provider_class_name = Helper::get_provider_class( $package_details['provider'] );

			$provider = new $provider_class_name( $package_details['repo_url'] );

			$package_slug    = $provider->get_package_slug();
			$package_zip_url = $provider->get_zip_repo_url( $package_details['branch'] );

			// If the provider is GitHub, we need to do some additional checks.
			if ( $provider instanceof \GithubDeployer\Providers\GithubProvider ) {
				$github_token    = isset( $package_details['options']['access_token'] ) ? $package_details['options']['access_token'] : '';
				$package_zip_url = $provider->get_zip_repo_url( $package_details['branch'], $github_token );
			}

			$package_install_options = array();

			if ( array_key_exists( 'is_private_repository', $package_details ) && $package_details['is_private_repository'] === true ) {
				$package_install_options = array(
					'is_private_repository' => true,
					'username'              => $package_details['options']['username'],
					'password'              => $package_details['options']['password'],
					'access_token'          => $package_details['options']['access_token'],
				);
			}

			$package_install_options['wp_json_request'] = true;

			$install_result = InstallPackage::install_package_from_zip_url( $package_zip_url, $package_slug, $package_type, $package_details['provider'], $package_install_options );
		} catch ( \Exception $e ) {
			$install_result = new \WP_Error( 'invalid', $e->getMessage() );
		}

		$success = ( is_wp_error( $install_result ) ? false : true );
		$message = '';

		$logger = new Logger();
		if ( $success ) {
			$logger->log( "Package ({$package_type}) \"{$package_slug}\" successfully updated via wp-json" );
			$message = esc_attr__( 'Package updated successfully.', 'github-deployer' );

			$flush_cache_setting_enabled = $data_manager->get_flush_cache_setting();
			if ( $flush_cache_setting_enabled ) {
				$logger->log( "Flushing cache after package ({$package_type}) \"{$package_slug}\" update via wp-json" );
				$message .= ' ' . esc_attr__( 'Cache flushed.', 'github-deployer' );
				Helper::trigger_cache_flush();
			}
		} elseif ( is_wp_error( $install_result ) ) {
			$error_message = $install_result->get_error_message();

			$logger->log(
				sprintf(
					'Error occurred while updating a package (%1$s) "%2$s" via wp-json "%3$s"',
					$package_type,
					$package_slug,
					$error_message
				)
			);

			$message = $error_message;
		}

		do_action( 'gd_after_api_package_update', $success, $package_slug, $package_type );

		return array(
			'success'      => $success,
			'message'      => $message,
			'package_type' => $package_type,
			'package_slug' => $package_slug,
		);
	}
}
