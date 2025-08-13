<?php
namespace GithubDeployer\Subpages\DashboardPage;

use GithubDeployer\DataManager;
use GithubDeployer\Helper;

/**
 * Class Dashboard
 *
 * @package GithubDeployer\Subpages\DashboardPage
 */
class Dashboard {

	/**
	 * Dashboard constructor.
	 */
	public function __construct() {
		add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', array( $this, 'init_menu' ) );
	}

	/**
	 * Initializes the menu.
	 */
	public function init_menu() {
		$menu_slug  = Helper::menu_slug();
		$capability = is_multisite() ? 'manage_network_options' : 'manage_options';

		add_menu_page(
			esc_attr__( 'Dashboard', 'github-deployer' ),
			esc_attr__( 'Github Deployer', 'github-deployer' ),
			$capability,
			$menu_slug,
			array( $this, 'init_page' ),
			'dashicons-randomize'
		);

		add_submenu_page(
			$menu_slug,
			esc_attr__( 'Dashboard', 'github-deployer' ),
			esc_attr__( 'Dashboard', 'github-deployer' ),
			$capability,
			$menu_slug,
			array( $this, 'init_page' )
		);
	}

	/**
	 * Initializes the page.
	 */
	public function init_page() {
		$data_manager = new DataManager();

		include_once __DIR__ . '/template.php';
	}
}
