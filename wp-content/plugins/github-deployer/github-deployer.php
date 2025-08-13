<?php

/**
 * Github Deployer
 *
 * @link              https://hyperticsai.com
 * @since             1.0.0
 * @package           Github_Deployer
 *
 * @wordpress-plugin
 * Plugin Name:       Github Deployer
 * Plugin URI:        https://github.com/s-azizkhan/github-deployer
 * Description:       This plugin can install and automatically update themes and plugins hosted on GitHub, Bitbucket, GitLab, or Gitea.
 * Version:           1.0.1
 * Author:            Hypertics AI
 * Author URI:        https://hyperticsai.com/
 * License:           GPL-3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       github_deployer
 * Domain Path:       /languages
 * Requires PHP:      7.0
 * Requires at least: 4.4
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Load Composer autoloader
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

if (!defined('GD_FILE')) {
    define('GD_FILE', __FILE__);
}
if (!defined('GD_PATH')) {
    define('GD_PATH', plugin_dir_path(__FILE__));
}
if (!defined('GD_URL')) {
    define('GD_URL', plugin_dir_url(__FILE__));
}
if (!defined('GD_SLUG')) {
    define('GD_SLUG', 'gd');
    // = plugin slug (used for options)
}
if (!defined('GD_VERSION')) {
    define('GD_VERSION', '1.0');
}
if (!defined('ABSPATH')) {
    exit;
}

use GithubDeployer\Helper;
use GithubDeployer\Admin;
use GithubDeployer\ApiRequests\PackageUpdate;

if (!class_exists('GithubDeployerInit')) {
    /**
     * Class GithubDeployerInit
     */
    class GithubDeployerInit
    {
        /**
         * GithubDeployerInit constructor.
         */
        public function __construct()
        {
            if (is_admin()) {
                add_action('plugins_loaded', array($this, 'admin_init'));
                add_action('plugins_loaded', array($this, 'load_textdomain'));
            }
            // Init api requests.
            add_action('plugins_loaded', array($this, 'api_requests_init'));
            register_activation_hook(GD_FILE, array($this, 'plugin_activation'));
        }

        /**
         * Set default settings.
         *
         * @return void
         */
        public function plugin_activation()
        {
            if (!Helper::get_api_secret()) {
                Helper::generate_api_secret();
            }
            flush_rewrite_rules();
            // Flush rewrite rules to ensure the new endpoints are registered.
        }

        /**
         * Load and initialize wp-admin side classes
         */
        public function admin_init()
        {
            new Admin();
        }

        /**
         * Load and initialize api requests classes
         */
        public function api_requests_init()
        {
            // init theme update webhook endpoint.
            new PackageUpdate();
        }

        /**
         * Load plugin textdomain.
         *
         * @return void
         */
        public function load_textdomain()
        {
            load_plugin_textdomain('github-deployer', false, dirname(plugin_basename(__FILE__)) . '/languages');
        }
    }

    new GithubDeployerInit();
}
