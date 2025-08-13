<?php
namespace GithubDeployer\Subpages\LogsPage;

use GithubDeployer\DataManager;
use GithubDeployer\Logger;

/**
 * Logs page
 */
class Logs {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', array( $this, 'init_menu' ) );
	}

	/**
	 * Initialize menu
	 *
	 * @return void
	 */
	public function init_menu() {
		$menu_slug  = \GithubDeployer\Helper::menu_slug();
		$capability = is_multisite() ? 'manage_network_options' : 'manage_options';

		add_submenu_page(
			$menu_slug,
			esc_attr__( 'Logs', 'github-deployer' ),
			esc_attr__( 'Logs', 'github-deployer' ),
			$capability,
			"{$menu_slug}-logs",
			array( $this, 'init_page' )
		);
	}

	/**
	 * Initialize page
	 *
	 * @return void
	 */
	public function init_page() {
		$data_manager = new DataManager();
		$this->handle_clear_log_form();

		include_once __DIR__ . '/template.php';
	}

	/**
	 * Handle clear log form
	 *
	 * @return void
	 */
	public function handle_clear_log_form() {
		$form_submitted = false;

		if ( isset( $_POST[ GD_SLUG . '_nonce' ] ) && wp_verify_nonce( ( sanitize_text_field( wp_unslash( $_POST[ GD_SLUG . '_nonce' ] ) ) ), GD_SLUG . '_clear_log_file' ) ) {
			$logger           = new Logger();
			$clear_log_result = $logger->clear_log_file();
			$form_submitted   = true;
		}
	}
}
