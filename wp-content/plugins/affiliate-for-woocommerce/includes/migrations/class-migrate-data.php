<?php
/**
 * Class for handling data migration from multiple plugins.
 *
 * @package     affiliate-for-woocommerce/includes/migration
 * @since       8.34.0
 * @version     1.1.0
 */

namespace AFWC\Migrations;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( Migrate_Data::class ) ) {

	/**
	 * Handles migration from supported affiliate plugins.
	 */
	class Migrate_Data {

		/**
		 * Holds the source plugins to migrate data.
		 *
		 * @var array
		 */
		public $source_plugins = array();

		/**
		 * Instance of this class
		 *
		 * @var self
		 */
		private static $instance = null;

		/**
		 * Get the single instance of this class.
		 *
		 * @return self
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {
			$this->load_sources();
			// Disable the affiliate user role setting.
			add_filter( 'afwc_general_section_admin_settings', array( $this, 'disable_affiliate_users_roles_setting' ), 20 );
		}

		/**
		 * Load available migration sources.
		 */
		public function load_sources() {

			if ( ! class_exists( Indeed_Affiliate_Pro::class ) ) {
				require_once AFWC_PLUGIN_DIRPATH . '/includes/migrations/class-indeed-affiliate-pro.php';
			}

			$this->source_plugins['indeed-affiliate-pro'] = array(
				'plugin_file' => 'indeed-affiliate-pro/indeed-affiliate-pro.php',
				'instance'    => Indeed_Affiliate_Pro::get_instance(),
				'is_active'   => afwc_is_plugin_active( 'indeed-affiliate-pro/indeed-affiliate-pro.php' ),
			);
		}

		/**
		 * Import data from the specified plugin.
		 *
		 * @param string $plugin Plugin slug.
		 *
		 * @return void
		 */
		public function import_data( $plugin = '' ) {

			// If no plugin is specified, run import for all available source plugins.
			if ( empty( $plugin ) && ! empty( $this->source_plugins ) && is_array( $this->source_plugins ) ) {
				foreach ( $this->source_plugins as $plugin_slug => $plugin_data ) {
					$this->import_data( $plugin_slug );
				}
				return;
			}

			if ( empty( $this->source_plugins[ $plugin ]['is_active'] )
				|| empty( $this->source_plugins[ $plugin ]['instance'] )
				|| ! $this->source_plugins[ $plugin ]['instance'] instanceof \AFWC_Migration
				|| ! is_callable( array( $this->source_plugins[ $plugin ]['instance'], 'init' ) )
			) {
				return;
			}

			$instance = $this->source_plugins[ $plugin ]['instance'];

			if ( is_callable( array( $instance, 'is_completed' ) ) && is_callable( array( $instance, 'delete_action' ) ) ) {
				if ( $instance->is_completed() ) {
					// Forcefully delete the action to re-initiate the process.
					$instance->delete_action();
				}
			}

			if ( is_callable( array( $instance, 'setup_action' ) ) ) {
				$instance->setup_action();
			}

			$instance->init();
			update_option( 'afwc_is_migration_process_running', true, 'no' );
		}

		/**
		 * Check if any migration is currently running.
		 *
		 * @return bool True if migration is in progress, otherwise false.
		 */
		public function is_running() {

			if ( empty( $this->source_plugins ) || ! is_array( $this->source_plugins ) ) {
				return false;
			}

			foreach ( $this->source_plugins as $source_migrate ) {
				if ( ! empty( $source_migrate['instance'] )
					&& is_callable( array( $source_migrate['instance'], 'is_running' ) )
					&& $source_migrate['instance']->is_running()
				) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Check if the migration is completed.
		 *
		 * @return bool True if migration is completed, otherwise false.
		 */
		public function is_completed() {

			if ( empty( $this->source_plugins ) || ! is_array( $this->source_plugins ) ) {
				return false;
			}

			foreach ( $this->source_plugins as $source_migrate ) {
				if ( ! empty( $source_migrate['instance'] )
					&& is_callable( array( $source_migrate['instance'], 'is_completed' ) )
					&& $source_migrate['instance']->is_completed()
				) {
					update_option( 'affiliate_migration_data_affiliate_wc', 'no', 'no' ); // Dismiss the notice.
					delete_option( 'afwc_is_migration_process_running' ); // Destroy the migration process.
					$this->disable_affiliate_by_user_role_feature();
					return true;
				}
			}

			return false;
		}

		/**
		 * Get the updated affiliate ID from old affiliate ID present in the source plugin.
		 *
		 * @param int $migrated_affiliate_id Migrated affiliate ID in source plugin.
		 *
		 * @throws \Exception If any error during the process.
		 * @return int New affiliate ID.
		 */
		public function get_new_affiliate_id_by_migrated_affiliate_id( $migrated_affiliate_id = 0 ) {

			if ( empty( $migrated_affiliate_id ) ) {
				return 0;
			}

			global $wpdb;

			try {
				$new_affiliate_id = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->prepare(
						"SELECT u.ID
						FROM {$wpdb->users} AS u
						INNER JOIN {$wpdb->usermeta} AS um
							ON u.ID = um.user_id
						WHERE um.meta_key = 'afwc_migrated_affiliate_id'
						  AND um.meta_value = %d",
						$migrated_affiliate_id
					)
				);
				if ( is_null( $new_affiliate_id ) && ! empty( $wpdb->last_error ) ) {
					throw new \Exception( $wpdb->last_error );
				}
			} catch ( \Exception $e ) {
				$new_affiliate_id = 0;
				\Affiliate_For_WooCommerce::log_error( __METHOD__, ( is_callable( array( $e, 'getMessage' ) ) ) ? $e->getMessage() : '' );
			}

			return ! empty( $new_affiliate_id ) ? intval( $new_affiliate_id ) : 0;
		}

		/**
		 * Disable the affiliates by user role functionality.
		 * Rename the existing option to preserve data.
		 *
		 * @throws \Exception If any error during the process.
		 * @return void
		 */
		public function disable_affiliate_by_user_role_feature() {
			global $wpdb;

			try {
				$old_option_name = 'affiliate_users_roles';
				$current_date    = gmdate( 'Y-m-d', \Affiliate_For_WooCommerce::get_offset_timestamp() );
				$new_option_name = sanitize_key( "afwc_users_roles_{$current_date}" );

				$updated = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->prepare(
						"UPDATE {$wpdb->options} SET option_name = %s WHERE option_name = %s",
						$new_option_name,
						$old_option_name
					)
				);

				if ( false === $updated ) {
					throw new \Exception( "Failed to rename option {$old_option_name}." );
				}

				// Delete the cache for the old option.
				wp_cache_delete( $old_option_name, 'options' );

			} catch ( \Exception $e ) {
				\Affiliate_For_WooCommerce::log_error( __METHOD__, ( is_callable( array( $e, 'getMessage' ) ) ) ? $e->getMessage() : '' );
			}
		}

		/**
		 * Disable the Affiliate User Roles setting in the admin UI after migration.
		 *
		 * @param array $settings The settings.
		 *
		 * @return array The modified settings array.
		 */
		public function disable_affiliate_users_roles_setting( $settings = array() ) {

			// Early exists if migration is not completed.
			if ( ! $this->is_completed() || ! is_array( $settings ) || empty( $settings ) ) {
				return $settings;
			}

			foreach ( $settings as &$setting ) {

				if ( empty( $setting['id'] ) || 'affiliate_users_roles' !== $setting['id'] ) {
					continue;
				}

				// Disable the field by adding the disabled attribute.
				if ( ! isset( $setting['custom_attributes'] ) || ! is_array( $setting['custom_attributes'] ) ) {
					$setting['custom_attributes'] = array();
				}

				$setting['custom_attributes']['disabled'] = 'disabled';

				// Update the description to indicate that it's no longer functional.
				$setting['desc'] = _x(
					'This setting is no longer functional because some affiliates are imported from other plugins.',
					'Disabled description for Affiliate users roles setting',
					'affiliate-for-woocommerce'
				);
			}
			unset( $setting ); // Prevent accidental reference usage.

			return $settings;
		}
	}
}

// Initialize the migration process.
Migrate_Data::get_instance();

