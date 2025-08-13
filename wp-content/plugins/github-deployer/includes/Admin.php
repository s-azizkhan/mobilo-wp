<?php
namespace GithubDeployer;

use GithubDeployer\Subpages\DashboardPage\Dashboard;
use GithubDeployer\Subpages\InstallThemePage\InstallTheme;
use GithubDeployer\Subpages\InstallPluginPage\InstallPlugin;
use GithubDeployer\Subpages\LogsPage\Logs;
use GithubDeployer\Subpages\MiscellaneousPage\Miscellaneous;

/**
 * Class Admin
 *
 * @package GithubDeployer
 */
class Admin {
	/**
	 * Admin constructor.
	 */
	public function __construct() {
		$this->init_dashboard_page();
		$this->init_install_theme_page();
		// $this->init_install_plugin_page(); // TODO: Uncomment this when plugin support is added 
		$this->init_logs_page();
		$this->init_miscellaneous_page();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( is_multisite() ? 'network_admin_notices' : 'admin_notices', array( $this, 'display_alert_notification_box' ) );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer' ), 1, 2 );
		$this->init_ajax_handlers();
	}

	private function init_ajax_handlers() {
		add_action( 'wp_ajax_validate_repository', array( $this, 'ajax_validate_repository' ) );
		add_action( 'wp_ajax_fetch_repository_data', array( $this, 'ajax_fetch_repository_data' ) );
	}

	/**
	 * Initialize dashboard page
	 */
	private function init_dashboard_page() {
		new Dashboard();
	}

	/**
	 * Initialize install theme page
	 */
	public function init_install_theme_page() {
		new InstallTheme();
	}

	/**
	 * Initialize install plugin page
	 */
	private function init_install_plugin_page() {
		new InstallPlugin();
	}

	/**
	 * Initialize miscellaneous page
	 */
	private function init_miscellaneous_page() {
		new Miscellaneous();
	}

	/**
	 * Initialize logs page
	 */
	private function init_logs_page() {
		new Logs();
	}

	/**
	 * Display alert notification box
	 */
	public function display_alert_notification_box() {
		$data_manager               = new DataManager();
		$alert_notification_setting = $data_manager->get_alert_notification_setting();

		if ( $alert_notification_setting ) {
			$menu_slug     = Helper::menu_slug();
			$dashboard_url = is_multisite() ? network_admin_url( "admin.php?page={$menu_slug}" ) : menu_page_url( $menu_slug, false );

			// translators: %s = dashboard url.
			$html = wp_kses_post( __( '<strong>Warning:</strong> Please do not make any changes to the theme or plugin code directly because some themes or plugins are connected with Git.<br>Click <a href="%s">here</a> to learn more.', 'github-deployer' ) );
			$html = sprintf( $html, esc_url( $dashboard_url ) );

			$html = apply_filters( 'gd_alert_notification_message', $html );

			$html = "<div class='gd_alert_box' >{$html}</div>";

			echo wp_kses(
				$html,
				array(
					'a'      => array(
						'href'  => array(),
						'title' => array(),
					),
					'br'     => array(),
					'strong' => array(),
					'div'    => array(
						'class' => array(),
					),
				)
			);
		}
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'gd-styles', GD_URL . 'assets/css/github-deployer-main.css', array(), GD_VERSION );

		wp_enqueue_script( 'gd-script', GD_URL . 'assets/js/github-deployer-main.js', array(), GD_VERSION, true );

		wp_localize_script(
			'gd-script',
			'gd',
			array(
				'copy_url_label'         => __( 'Copy URL', 'github-deployer' ),
				'copied_url_label'       => __( 'Copied!', 'github-deployer' ),
				'updating_now_label'     => __( 'Updating now...', 'github-deployer' ),
				'error_label'            => __( 'Something went wrong', 'github-deployer' ),
				'update_completed_label' => __( 'Updated!', 'github-deployer' ),
				'update_theme_label'     => __( 'Update Theme', 'github-deployer' ),
				'update_plugin_label'    => __( 'Update Plugin', 'github-deployer' ),
			)
		);
	}

	/**
	 * When user is on a plugin related admin page, display footer text.
	 *
	 * @param string $text Footer text.
	 *
	 * @return string
	 */
	public function admin_footer( $text ) {

		global $current_screen;

		if ( ! empty( $current_screen->id ) && strpos( $current_screen->id, 'github-deployer' ) !== false ) {
			$url  = 'https://wordpress.org/support/plugin/github-deployer/reviews/?filter=5#new-post';
			$text = sprintf(
				wp_kses( /* translators: $1$s - Github Deployer plugin name; $2$s - WP.org review link; $3$s - WP.org review link. */
					__( 'Please rate %1$s <a href="%2$s" target="_blank" rel="noopener noreferrer">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%3$s" target="_blank" rel="noopener">WordPress.org</a> to support and promote our solution. Thank you very much!', 'github-deployer' ),
					array(
						'a' => array(
							'href'   => array(),
							'target' => array(),
							'rel'    => array(),
						),
					)
				),
				'<strong>Github Deployer</strong>',
				$url,
				$url
			);
		}

		return $text;
	}

	/**
	 * AJAX handler for repository validation
	 */
	public function ajax_validate_repository() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'gd_repo_validation' ) ) {
			wp_die( 'Invalid nonce' );
		}

		$repo_url = sanitize_text_field( $_POST['repo_url'] );
		$provider_type = sanitize_text_field( $_POST['provider_type'] );

		if ( empty( $repo_url ) || empty( $provider_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Repository URL and provider type are required', 'github-deployer' ) ) );
		}

		try {
			$provider_class_name = Helper::get_provider_class( $provider_type );
			$provider = new $provider_class_name( $repo_url );
			
			wp_send_json_success( array(
				'message' => __( 'Repository URL is valid', 'github-deployer' ),
				'repo_slug' => $provider->get_package_slug(),
				'repo_handle' => $this->get_repo_handle_from_url( $repo_url )
			) );
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * AJAX handler for fetching repository data (branches, commits)
	 */
	public function ajax_fetch_repository_data() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'gd_repo_data' ) ) {
			wp_die( 'Invalid nonce' );
		}

		$repo_url = sanitize_text_field( $_POST['repo_url'] );
		$provider_type = sanitize_text_field( $_POST['provider_type'] );
		$is_private = isset( $_POST['is_private'] ) ? (bool) $_POST['is_private'] : false;
		$access_token = sanitize_text_field( $_POST['access_token'] ?? '' );

		if ( empty( $repo_url ) || empty( $provider_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Repository URL and provider type are required', 'github-deployer' ) ) );
		}

		try {
			$provider_class_name = Helper::get_provider_class( $provider_type );
			$provider = new $provider_class_name( $repo_url );
			
			$repo_data = $this->fetch_repository_data( $provider, $provider_type, $is_private, $access_token );
			
			wp_send_json_success( $repo_data );
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * Fetch repository data (branches, commits) from the provider
	 */
	private function fetch_repository_data( $provider, $provider_type, $is_private, $access_token ) {
		$repo_handle = $this->get_repo_handle_from_url( $provider->get_repo_url() );
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
					'default_branch' => 'master'
				);
		}

		if ( empty( $api_url ) ) {
			return array(
				'branches' => array( 'master', 'main' ),
				'commits' => array(),
				'default_branch' => 'master'
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
				$commits = $this->fetch_github_commits( $repo_handle, $default_branch, $is_private, $access_token );
				break;
			case 'gitlab':
				$default_branch = $data['default_branch'] ?? 'master';
				$branches = $this->fetch_gitlab_branches( $repo_handle, $is_private, $access_token );
				$commits = $this->fetch_gitlab_commits( $repo_handle, $default_branch, $is_private, $access_token );
				break;
			case 'bitbucket':
				$default_branch = $data['mainbranch']['name'] ?? 'master';
				$branches = $this->fetch_bitbucket_branches( $repo_handle, $is_private, $access_token );
				$commits = $this->fetch_bitbucket_commits( $repo_handle, $default_branch, $is_private, $access_token );
				break;
		}

		return array(
			'branches' => $branches,
			'commits' => $commits,
			'default_branch' => $default_branch
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
}
