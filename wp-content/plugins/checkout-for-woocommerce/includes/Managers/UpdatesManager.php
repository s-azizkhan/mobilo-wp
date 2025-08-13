<?php
namespace Objectiv\Plugins\Checkout\Managers;

use Objectiv\Plugins\Checkout\Managers\Helpers\EDD_SL_Plugin_Updater;
use Objectiv\Plugins\Checkout\SingletonAbstract;

/**
 * EDD Software Licensing Magic
 *
 * A drop-in class that magically manages your EDD SL plugin licensing.
 *
 * @version 0.6.1
 **/
class UpdatesManager extends SingletonAbstract {
	/**
	 * The parent menu slug to which the "License" submenu will be attached.
	 *
	 * @var string
	 */
	public $menu_slug;

	/**
	 * Prefix for internal settings.
	 *
	 * @var string
	 */
	public $prefix;

	/**
	 * Plugin host site URL for Easy Digital Downloads Software Licensing.
	 *
	 * @var string
	 */
	public $url;

	/**
	 * Plugin version for Easy Digital Downloads Software Licensing.
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Plugin name for Easy Digital Downloads Software Licensing.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * List of key statuses and corresponding messages.
	 *
	 * @var array
	 */
	public $key_statuses;

	/**
	 * List of bad key statuses and corresponding messages.
	 *
	 * @var array
	 */
	public $bad_key_statuses = array(
		'expired',
		'disabled',
		'invalid',
		'nolicensekey', // we made up this status
	);

	/**
	 * List of key statuses that indicate the license is a real license
	 *
	 * @var array
	 */
	public $good_key_statuses = array(
		'inactive',
		'site_inactive',
		'valid',
	);

	/**
	 * List of activation errors and their associated messages.
	 *
	 * @var array
	 */
	public $activate_errors;

	/**
	 * Stores the last activation error message.
	 * Required because we cannot pass variables directly to `admin_notice`.
	 *
	 * @var string|null
	 */
	public $last_activation_error;

	/**
	 * Indicates whether the license applies to a theme or a plugin.
	 *
	 * @var bool
	 */
	public $theme = false;

	/**
	 * Indicates whether the plugin is a beta version.
	 *
	 * @var bool
	 */
	public $beta = false;

	/**
	 * @var bool Indicates whether the plugin is a beta version.
	 */
	public $home_url = false;

	/**
	 * @var EDD_SL_Plugin_Updater The EDD SL Plugin Updater instance.
	 */
	public $updater;

	/**
	 * @var string The author of the plugin.
	 */
	public $author;

	/**
	 * Constructor
	 *
	 * @param string|bool $beta True if beta versions are enabled.
	 *
	 * @return void
	 */
	public function init( $beta = false ) {
		add_action( 'cfw_do_plugin_activation', array( $this, 'run_on_plugin_activation' ) );
		add_action( 'cfw_do_plugin_deactivation', array( $this, 'run_on_plugin_deactivation' ) );

		$theme          = false;
		$url            = CFW_UPDATE_URL;
		$version        = CFW_VERSION;
		$name           = CFW_NAME;
		$prefix         = '_cfw_licensing';
		$author         = 'Objectiv';
		$this->home_url = self::get_home_url();

		if ( false === $version || false === $name ) {
			return;
		}

		$this->url       = trailingslashit( $url );
		$this->version   = $version;
		$this->name      = $name;
		$this->author    = $author;
		$this->menu_slug = false;
		$this->prefix    = $prefix . '_';
		$this->theme     = $theme;
		$this->beta      = $beta;

		$this->key_statuses = array(
			'invalid'       => 'The entered license key is not valid.',
			'expired'       => 'Your key has expired and needs to be renewed.',
			'inactive'      => 'Your license key is valid, but is not active.',
			'disabled'      => 'Your license key is currently disabled. Please contact support.',
			'site_inactive' => 'Your license key is valid, but not active for this site.',
			'valid'         => 'Your license key is valid and active for this site.',
			'nolicensekey'  => 'Your license key is missing.',
		);

		$this->activate_errors = array(
			'missing'             => 'The provided license key does not seem to exist.',
			'revoked'             => 'The provided license key has been revoked. Please contact support.',
			'no_activations_left' => 'This license key has been activated the maximum number of times.',
			'expired'             => 'This license key has expired.',
			'key_mismatch'        => 'An unknown error has occurred: key_mismatch',
		);

		// Instantiate EDD_SL_Plugin_Updater
		add_action( 'admin_init', array( $this, 'updater_init' ), 0 ); // run first

		// Form Handler
		add_action( SettingsManager::instance()->prefix . '_settings_saved', array( $this, 'save_settings' ) );

		// Cron action
		add_action( $this->prefix . '_check_license', array( $this, 'check_license' ) );

		// Delayed license status update
		add_action( $this->prefix . '_edd_sl_delayed_license_status_update', array( $this, 'delayed_license_update' ) );

		add_action(
			'plugins_loaded',
			function () {
				if ( defined( 'CFW_AUTO_ACTIVATE_LICENSE' ) && CFW_AUTO_ACTIVATE_LICENSE ) {
					if ( $this->is_key_valid_but_inactive() && ! $this->get_field_value( 'auto_activate_failed' ) ) {
						$this->auto_activate_license();
					}
				}
			}
		);

		add_action( 'wp_ajax_cfw_license_save', array( $this, 'ajax_save_license' ) );
	}

	public function run_on_plugin_activation() {
		$this->set_license_check_cron();
	}

	public function run_on_plugin_deactivation() {
		$this->unset_license_check_cron();
	}

	/**
	 * Retrieves the URL for a given site where the front end is accessible.
	 *
	 * Returns the 'home' option with the appropriate protocol. The protocol will be 'https'
	 * if is_ssl() evaluates to true; otherwise, it will be the same as the 'home' option.
	 * If `$scheme` is 'http' or 'https', is_ssl() is overridden.
	 *
	 * Copied from WordPress 5.2.0
	 *
	 * @param  int|null    $blog_id Optional. Site ID. Default null (current site).
	 * @param string      $path    Optional. Path relative to the home URL. Default empty.
	 * @param string|null $scheme  Optional. Scheme to give the home URL context. Accepts
	 *                              'http', 'https', 'relative', 'rest', or null. Default null.
	 *
	 * @return string Home URL link with optional path appended.
	 * @since 3.0.0
	 *
	 * @global string $pagenow
	 */
	public static function get_home_url( ?int $blog_id = null, string $path = '', ?string $scheme = null ): string {
		global $pagenow;

		$orig_scheme = $scheme;

		if ( empty( $blog_id ) || ! is_multisite() ) {
			$url = get_option( 'home' );
		} else {
			switch_to_blog( $blog_id );
			$url = get_option( 'home' );
			restore_current_blog();
		}

		if ( ! in_array( $scheme, array( 'http', 'https', 'relative' ), true ) ) {
			if ( is_ssl() && ! is_admin() && 'wp-login.php' !== $pagenow ) {
				$scheme = 'https';
			} else {
				$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
			}
		}

		$url = set_url_scheme( $url, $scheme );

		if ( $path && is_string( $path ) ) {
			$url .= '/' . ltrim( $path, '/' );
		}

		/**
		 * Filters the home URL.
		 *
		 * @since 3.0.0
		 *
		 * @param string      $url         The complete home URL including scheme and path.
		 * @param string      $path        Path relative to the home URL. Blank string if no path is specified.
		 * @param string|null $orig_scheme Scheme to give the home URL context. Accepts 'http', 'https',
		 *                                 'relative', 'rest', or null.
		 * @param int|null    $blog_id     Site ID, or null for the current site.
		 */
		return apply_filters( 'cfw_updates_manager_home_url', $url, $path, $orig_scheme, $blog_id );
	}

	/**
	 * Creates a nonce for the license page form.
	 *
	 * @return void
	 */
	public function the_nonce() {
		wp_nonce_field( "save_{$this->prefix}_mb_settings", "{$this->prefix}_mb_save" );
	}

	/**
	 * Generates a field name from a setting value for the license page form.
	 *
	 * @param string $setting The key for the setting you're saving.
	 * @return string The field name
	 */
	public function get_field_name( string $setting ): string {
		return "{$this->prefix}_mb_setting[$setting]";
	}

	/**
	 * Retrieves value from the database for specified setting.
	 *
	 * @param string $setting The setting key you're retrieving (default: false).
	 * @return string The field value
	 */
	public function get_field_value( $setting = false ) {
		if ( false === $setting ) {
			return false;
		}

		if ( 'license_key' === $setting && defined( 'CFW_LICENSE_KEY' ) ) {
			return CFW_LICENSE_KEY;
		}

		return get_option( $this->prefix . '_' . $setting );
	}

	/**
	 * Set value for the specified setting.
	 *
	 * @param string $setting The setting key.
	 * @param mixed  $value The value.
	 * @return bool True if the value was updated, false otherwise.
	 */
	public function set_field_value( string $setting, $value ): bool {
		if ( empty( $setting ) ) {
			return false;
		}

		return update_option( $this->prefix . '_' . $setting, $value );
	}

	/**
	 * Save license settings.  Listens for settings form submit. Also handles activation / deactivation.
	 *
	 * @return void
	 */
	public function save_settings() {
		if ( ! isset( $_REQUEST[ "{$this->prefix}_mb_setting" ] ) || ! wp_verify_nonce( wc_clean( wp_unslash( $_REQUEST[ "{$this->prefix}_mb_save" ] ?? '' ) ), "save_{$this->prefix}_mb_settings" ) ) {
			return;
		}

		$settings = wc_clean( wp_unslash( $_REQUEST[ "{$this->prefix}_mb_setting" ] ) );

		foreach ( $settings as $setting => $value ) {
			$this->set_field_value( $setting, $value );
		}

		// Handle activation if applicable
		if ( isset( $_REQUEST['activate_key'] ) || isset( $_REQUEST['deactivate_key'] ) ) {
			$this->manage_license_activation();
		} else {
			$this->check_license();
		}

		add_action( 'admin_notices', array( $this, 'notice_settings_saved_success' ) );
	}

	/**
	 * Sets up the EDD_SL_Plugin_Updater object.
	 *
	 * @return void
	 */
	public function updater_init() {
		// retrieve our license key from the DB
		$license_key = $this->get_license_key();

		if ( ! $license_key ) {
			return;
		}

		// setup the updater
		$this->updater = new EDD_SL_Plugin_Updater(
			$this->url,
			CFW_MAIN_FILE,
			array(
				'version'     => $this->version,  // current version number
				'license'     => $license_key,    // license key (used get_option above to retrieve from DB)
				'item_name'   => $this->name,     // name of this plugin
				'author'      => $this->author,   // author of this plugin
				'beta'        => $this->beta,     // Enable beta updates
				'wp_override' => defined( 'CFW_DEV_MODE' ) && isset( $_GET['force-check'] ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			)
		);
	}

	/**
	 * Generates license page form.
	 *
	 * @return void
	 */
	public function admin_page() {
		?>
		<div class="wrap">
			<form method="post" action="<?php echo esc_attr( wc_clean( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ) ); ?>">
				<?php $this->the_nonce(); ?>

				<table class="form-table">
					<tbody>
						<?php $this->admin_page_fields(); ?>
					</tbody>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>

		<?php
	}

	public function admin_page_fields() {
		$license_data = get_option( 'cfw_license_data', false );
		$this->the_nonce();
		?>
		<div class="cfw-admin-field-container" id="cfw-admin-license-info">
			<label for="<?php echo esc_attr( $this->get_field_name( 'license_key' ) ); ?>" class="block text-sm font-medium text-gray-700 mb-2">
				<?php _e( 'CheckoutWC License Key', 'checkout-wc' ); ?>
			</label>
			<?php if ( ! defined( 'CFW_LICENSE_KEY' ) ) : ?>
				<input type="password" autocomplete="off" name="<?php echo esc_attr( $this->get_field_name( 'license_key' ) ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'license_key' ) ); ?>" id="cfw_license_key" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-300 rounded-md" />
			<?php else : ?>
				<?php _e( 'Your license key is set with the constant <code>CFW_LICENSE_KEY</code>.', 'checkout-wc' ); ?>
			<?php endif; ?>

			<div class="mt-2 max-w-xl">
				<dl>
					<div class="py-2 grid grid-cols-3">
						<dt class="text-sm font-medium leading-6 text-gray-900">
							License Holder
						</dt>
						<dd class="text-sm leading-6 text-gray-700 col-span-2">
							<?php
							if ( $license_data->customer_name && $license_data->customer_email ) {
								echo wp_kses_post( $license_data->customer_name . ' (' . $license_data->customer_email . ')' );
							}
							?>
						</dd>
					</div>
					<div class="py-2 grid grid-cols-3">
						<dt class="text-sm font-medium leading-6 text-gray-900">
							Plan
						</dt>
						<dd class="text-sm leading-6 text-gray-700 col-span-2">
							<?php echo wp_kses_post( $this->get_plan_name() ); ?>
						</dd>
					</div>
					<div class="py-2 grid grid-cols-3">
						<dt class="text-sm font-medium leading-6 text-gray-900">
							Activation Limit
						</dt>
						<dd class="text-sm leading-6 text-gray-700 col-span-2">
							<?php echo wp_kses_post( get_option( 'cfw_license_activation_limit', 0 ) ); ?>
						</dd>
					</div>
					<div class="py-2 grid grid-cols-3">
						<dt class="text-sm font-medium leading-6 text-gray-900">
							Status
						</dt>
						<dd class="text-sm leading-6 text-gray-700 col-span-2">
							<?php self::instance()->admin_page_activation_status_button(); ?>

							<?php if ( $license_data ) : ?>
								<a href="javascript:" class="mt-2 block text-sm text-blue-600" id="cfw-admin-refresh-license">Refresh License Info</a>
							<?php endif; ?>
						</dd>
					</div>
				</dl>
			</div>

		</div>
		<?php
	}

	public function get_plan_name(): string {
		$price_id = get_option( 'cfw_license_price_id', 0 );

		$plans = array(
			2  => 'Plus',
			3  => 'Agency',
			4  => 'Agency Monthly',
			6  => 'Plus Monthly',
			7  => 'Pro',
			8  => 'Pro',
			9  => 'Plus',
			10 => 'Agency',
			12 => 'Pro',
		);

		$plan = $plans[ $price_id ] ?? 'None';

		if ( 'None' !== $plan ) {
			$plan = "CheckoutWC {$plan}";
		}

		return $plan;
	}

	public function admin_page_activation_status_button() {
		$key_status = $this->get_field_value( 'key_status' );
		$license    = $this->get_field_value( 'license_key' );
		?>
		<div id="cfw-activation-control" class="cfw-admin-field-container">
			<?php if ( empty( $license ) ) : ?>
				<p class="text-sm leading-6 text-gray-700 col-span-2">
					<?php _e( 'Please enter your license key.', 'checkout-wc' ); ?>
				</p>
			<?php elseif ( 'inactive' === $key_status || 'site_inactive' === $key_status ) : ?>
				<input type="submit" name="activate_key" class="button-secondary" value="Activate Site" />
				<p class="mt-2 text-sm leading-6 col-span-2 text-red-600"><?php echo esc_html( $this->key_statuses[ $key_status ] ); ?></p>
			<?php elseif ( 'valid' === $key_status ) : ?>
				<input type="submit" name="deactivate_key" class="button-secondary" value="Deactivate Site" />
				<p class="mt-2 text-sm leading-6 col-span-2 text-green-600" style="color:green;"><?php echo esc_html( $this->key_statuses[ $key_status ] ); ?></p>
			<?php else : ?>
				<p style="color:red;"><?php echo esc_html( $this->key_statuses[ $key_status ] ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Is the key valid but inactive?
	 *
	 * @return bool
	 */
	public function is_key_valid_but_inactive(): bool {
		$key_status = $this->get_field_value( 'key_status' );
		return ( 'inactive' === $key_status || 'site_inactive' === $key_status );
	}

	public function auto_activate_license() {
		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $this->get_field_value( 'license_key' ),
			'item_name'  => rawurlencode( $this->name ), // the name of our product in EDD
			'url'        => $this->home_url,
			'bypass'     => 'true',
		);

		// Call the custom API.
		$response = wp_remote_get(
			add_query_arg( $api_params, $this->url ),
			array(
				'timeout'   => 15,
				'sslverify' => false,
			)
		);

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			cfw_debug_log( 'License Auto Activation Error: ' . $response->get_error_message() );
			$this->set_field_value( 'auto_activate_failed', true );
			return false;
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// Front end notice only
		// $license_data->license will be either "valid" or "invalid"

		if ( isset( $license_data->error ) || 'invalid' === $license_data->license ) {
			// Don't allow auto activate to run again
			$this->set_field_value( 'auto_activate_failed', true );
		} else {
			// License is valid, so cancel the delayed update
			$this->cancel_delayed_license_update();
		}

		// Set detailed key_status
		$this->set_field_value( 'key_status', $this->get_license_status() );

		return true;
	}

	/**
	 * Handles license activation and deactivation
	 *
	 * @return void
	 */
	public function manage_license_activation() {
		if ( ! isset( $_REQUEST[ "{$this->prefix}_mb_save" ] ) || ! wp_verify_nonce( wc_clean( wp_unslash( $_REQUEST[ "{$this->prefix}_mb_save" ] ) ), "save_{$this->prefix}_mb_settings" ) ) {
			return;
		}

		$action = isset( $_REQUEST['activate_key'] ) ? 'activate_license' : 'deactivate_license';

		$api_params = array(
			'edd_action' => $action,
			'license'    => $this->get_field_value( 'license_key' ),
			'item_name'  => rawurlencode( $this->name ), // the name of our product in EDD
			'url'        => $this->home_url,
			'bypass'     => 'true',
		);

		// Call the custom API.
		$response = wp_remote_get(
			add_query_arg( $api_params, $this->url ),
			array(
				'timeout'   => 15,
				'sslverify' => false,
			)
		);

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			cfw_debug_log( 'License Activation Error (manage_license_activation): ' . $response->get_error_message() );
			return;
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( 'activate_license' === $action ) {
			// Front end notice only
			// $license_data->license will be either "valid" or "invalid"

			if ( isset( $license_data->error ) ) {
				$this->last_activation_error = $license_data->error;
				add_action( 'admin_notices', array( $this, 'notice_license_activate_error' ) );
			} elseif ( 'invalid' === $license_data->license ) {
				add_action( 'admin_notices', array( $this, 'notice_license_invalid' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'notice_license_valid' ) );
			}
		} elseif ( 'failed' === $license_data->license ) { // $license_data->license will be either "deactivated" or "failed"
			add_action( 'admin_notices', array( $this, 'notice_license_deactivate_failed' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'notice_license_deactivate_success' ) );
		}

		// Set detailed key_status
		$this->cancel_delayed_license_update();
		$this->set_field_value( 'key_status', $this->get_license_status() );

		// Clear auto activation block since a manual change was made
		$this->set_field_value( 'auto_activate_failed', false );
	}

	/**
	 * Retrieve status of license key for current site.
	 *
	 * @return string|bool The license status|false on error
	 */
	public function get_license_status() {
		$license_data = $this->get_license_data();

		if ( isset( $license_data->license ) ) {
			return $license_data->license;
		}

		wc_get_logger()->error( 'License Check Error: You should not be able to get here. I am kind of freaked out.', array( 'source' => 'checkout-wc' ) );
		wc_get_logger()->error( wc_print_r( $license_data, true ), array( 'source' => 'checkout-wc' ) );

		return false;
	}

	public function get_license_data() {
		$license = $this->get_license_key();

		if ( ! $license ) {
			update_option( 'cfw_license_activation_limit', 0 );
			update_option( 'cfw_license_price_id', 0 );
			update_option( 'cfw_license_data', null, false );
			return 'nolicensekey';
		}

		$current_data = get_option( 'cfw_license_data', null );

		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $license,
			'item_name'  => rawurlencode( $this->name ),
			'url'        => $this->home_url,
			'bypass'     => 'true',
		);

		// Call the custom API.
		$response = wp_remote_get(
			add_query_arg( $api_params, $this->url ),
			array(
				'timeout'   => 15,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			cfw_debug_log( 'License Activation Error (get_license_data): ' . $response->get_error_message() );
			return false;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		update_option( 'cfw_license_activation_limit', $license_data->license_limit ?? 0 );
		update_option( 'cfw_license_price_id', $license_data->price_id ?? 0 );
		update_option( 'cfw_license_data', $license_data, false );

		$changes = array_intersect_key(
			array_diff_assoc( (array) $current_data, (array) $license_data ),
			array_flip( array( 'price_id', 'license_limit', 'license', 'license_limit', 'site_count', 'activations_left' ) )
		);

		if ( ! empty( $changes ) ) {
			cfw_do_action( 'cfw_license_data_changed', $changes );
		}

		return $license_data;
	}

	/**
	 * License is invalid notice
	 *
	 * @return void
	 */
	public function notice_license_invalid() {
		?>
		<div class="error">
			<p><?php echo esc_html( $this->name ); ?> license activation was not successful. Please check your key status below for more information.</p>
		</div>
		<?php
	}

	/**
	 * License is valid notice
	 *
	 * @return void
	 */
	public function notice_license_valid() {
		?>
		<div class="updated">
			<p><?php echo esc_html( $this->name ); ?> license successfully activated.</p>
		</div>
		<?php
	}

	/**
	 * License deactivation failed notice
	 *
	 * @return void
	 */
	public function notice_license_deactivate_failed() {
		?>
		<div class="error">
			<p><?php echo esc_html( $this->name ); ?> license deactivation failed. Please try again, or contact support.</p>
		</div>
		<?php
	}

	/**
	 * License deactivation successful notice
	 *
	 * @return void
	 */
	public function notice_license_deactivate_success() {
		?>
		<div class="updated">
			<p><?php echo esc_html( $this->name ); ?> license deactivated successfully.</p>
		</div>
		<?php
	}

	/**
	 * Settings saved success notice
	 *
	 * @return void
	 */
	public function notice_settings_saved_success() {
		?>
		<div class="updated">
			<p><?php echo esc_html( $this->name ); ?> license settings saved successfully.</p>
		</div>
		<?php
	}

	/**
	 * License activation error notice
	 *
	 * @return void
	 */
	public function notice_license_activate_error() {
		?>
		<div class="error">
			<p><?php echo esc_html( $this->name ); ?> license activation failed: <?php echo esc_html( $this->activate_errors[ $this->last_activation_error ] ); ?></p>
		</div>
		<?php
	}

	/**
	 * Create cron for license check
	 *
	 * @return void
	 */
	public function set_license_check_cron() {
		$this->unset_license_check_cron();
		wp_schedule_event( time(), 'daily', $this->prefix . '_check_license' );
	}

	/**
	 * Clear cron for license check.
	 *
	 * @return void
	 */
	public function unset_license_check_cron() {
		wp_clear_scheduled_hook( $this->prefix . '_check_license' );
	}

	/**
	 * Retrieve license status for current site and store in key_status setting.
	 *
	 * @return void
	 */
	public function check_license() {
		$current_key_status = $this->get_field_value( 'key_status' );
		$new_status         = $this->get_license_status();

		// If doing cron and the key is currently valid and the new status is invalid, add 3 day delay to updating to prevent immediate deactivation
		if ( defined( 'DOING_CRON' ) && 'valid' === $current_key_status && ( in_array( $new_status, $this->bad_key_statuses, true ) || false === $new_status ) ) {
			$this->cancel_delayed_license_update();
			$this->schedule_delayed_license_check();
		} else {
			$this->cancel_delayed_license_update();
			$this->set_field_value( 'key_status', $new_status );
		}
	}

	/**
	 * Update the key status to the current status
	 */
	public function delayed_license_update() {
		$new_status = $this->get_license_status();

		if ( false !== $new_status ) {
			$this->set_field_value( 'key_status', $new_status );
		} else {
			// Bump check another 7 days
			$this->cancel_delayed_license_update();
			$this->schedule_delayed_license_check();
		}
	}

	public function schedule_delayed_license_check() {
		if ( ! wp_next_scheduled( $this->prefix . '_edd_sl_delayed_license_status_update' ) ) {
			wp_schedule_single_event( time() + ( DAY_IN_SECONDS * 7 ), $this->prefix . '_edd_sl_delayed_license_status_update' );
		}
	}

	public function cancel_delayed_license_update() {
		wp_clear_scheduled_hook( $this->prefix . '_edd_sl_delayed_license_status_update' );
	}

	/**
	 * Get trimmed license key
	 *
	 * @return bool|string
	 */
	public function get_license_key() {
		$license = trim( $this->get_field_value( 'license_key' ) );

		if ( ! empty( $license ) && 32 === strlen( $license ) ) {
			return $license;
		}

		return false;
	}

	/**
	 * Is license valid
	 *
	 * @return bool True if license valid, false if it is invalid
	 */
	public function is_license_valid(): bool {
		$key_status  = $this->get_field_value( 'key_status' );
		$license_key = $this->get_field_value( 'license_key' );

		$valid = true;

		// Validate Key Status
		if ( empty( $license_key ) || 'valid' !== $key_status ) {
			$valid = false;
		}

		return $valid;
	}

	/**
	 * A good license is a real one, regardless of whether it is active on the site
	 *
	 * Used to determine if admin settings should be shown.
	 *
	 * @return bool
	 */
	public function is_license_good(): bool {
		$license_key = $this->get_field_value( 'license_key' );
		$key_status  = $this->get_field_value( 'key_status' );

		return in_array( $key_status, $this->good_key_statuses, true ) && ! empty( $license_key );
	}

	public function ajax_save_license() {
		if ( ! check_ajax_referer( 'objectiv-cfw-admin-save', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid security token sent.' );
			wp_die();
		}

		$this->set_field_value( 'license_key', wc_clean( wp_unslash( $_REQUEST['key'] ?? '' ) ) );

		if ( isset( $_REQUEST['refresh_only'] ) && 'false' === $_REQUEST['refresh_only'] ) {
			$this->auto_activate_license();
		}

		$this->get_license_data(); // required to actually update info from the server

		ob_start();
		$this->admin_page_fields();

		$cfw_activation_control_content = ob_get_clean();

		wp_send_json(
			array(
				'success'   => true,
				'fragments' => array(
					'#cfw-admin-license-info' => $cfw_activation_control_content,
				),
			)
		);
	}

	public function get_license_price_id(): int {
		$price_id = get_option( 'cfw_license_price_id', false );

		if ( false === $price_id ) {
			$license_data = $this->get_license_data();
			$price_id     = $license_data->price_id ?? 0;
		}

		return intval( $price_id );
	}
}
