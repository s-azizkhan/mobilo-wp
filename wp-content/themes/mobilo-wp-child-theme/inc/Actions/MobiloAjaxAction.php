<?php

namespace Mobilo\WpTheme\Actions;

use WP_Error;
use WP_Http;

defined('ABSPATH') || exit;

/**
 * Class MobiloAjaxAction
 *
 * @version 1.1.1
 * @since 3.6.0
 */
abstract class MobiloAjaxAction
{
    /**
     * @since 1.0.0
     * @access protected
     * @var string $id
     */
    protected $id = '';

    /**
     * @access protected
     * @var string $key
     */
    protected $key = '';

    /**
     * @since 1.0.0
     * @access protected
     * @var string $prefix
     */
    protected $prefix = MOBILO_PREFIX . '_';

    /**
     * @since 1.0.0
     * @access protected
     * @var string $authProtected
     */
    protected $authProtected = false;

    /**
     * @since 1.0.0
     * @access protected
     * @var string $emptyExistingAction
     */
    protected $emptyExistingAction = true;

    /**
     * @since 1.1.0
     * @access protected
     * @var string $authProtected
     */
    public $wcAjaxId = '';

    /**
     * @since 1.1.0
     * @access protected
     * @var string $isDeprecated
     */
    public $isDeprecated = false;

    /**
     * @since 1.1.0
     * @access protected
     * @var string $isPrivate
     */
    public $isPrivate = false;

    public $nonceVerify = false;

    /**
     * Constructor for the MobiloAjaxAction class.
     *
     * @param string $id The ID of the action.
     * @param bool $protected (optional) Whether the action is protected. Defaults to false.
     * @param bool $emptyExistingAction (optional) Whether to empty existing actions. Defaults to true.
     */
    public function __construct(string $id, bool $protected = false, bool $emptyExistingAction = true)
    {
        $this->id = "{$this->prefix}{$id}";
        $this->key = $id;
        $this->authProtected = $protected;
        $this->emptyExistingAction = $emptyExistingAction;

        $this->wcAjaxId = "wc_ajax_{$this->get_id()}";

        add_filter('mobilo_react_silent_routes', [$this, 'add_to_silent_routes']);
    }

    /**
     * Adds the current action to the silent routes array if it is not private or deprecated.
     *
     * @param array $routes The array of silent routes.
     * @return array The updated array of silent routes.
     * @since 1.1.0
     * @version 1.0.0
     */
    public function add_to_silent_routes(array $routes): array
    {
        if ($this->isPrivate || $this->isDeprecated) {
            return $routes;
        }

        $routes[$this->key] = "/?wc-ajax={$this->get_id()}";
        return $routes;
    }

    /**
     * @since 1.0.0
     * @access public
     * @return string
     */
    public function get_id(): string
    {
        return $this->id;
    }


    /**
     * checks the authentication required or not
     * @since 1.0.0
     * @access public
     * @return boolean
     */
    public function is_protected(): bool
    {
        return $this->authProtected;
    }

    /**
     * Empty the all existing actions or not
     * @since 1.0.0
     * @access public
     * @return boolean
     */
    public function empty_all_actions(): bool
    {
        return $this->is_protected();
    }

    /**
     * @since 1.0.0
     * @access public
     */
    public function load(): void
    {
        if ($this->empty_all_actions()) {
            remove_all_actions($this->wcAjaxId);
        }
        add_action($this->wcAjaxId, array($this, 'execute'));

        /**
         * These legacy handlers are here because Woo adds them and 3rd party plugins
         * sometimes expect them. This is particularly important for WooCommerce Memberships
         * which uses these handlers to detect valid WC ajax requests when the home page is
         * restricted
         */
        $wp_ajax_id = "wp_ajax_{$this->get_id()}";
        if ($this->empty_all_actions()) {
            remove_all_actions($wp_ajax_id);
        }
        add_action($wp_ajax_id, array($this, 'execute'));

        if (!$this->is_protected()) {
            if ($this->empty_all_actions()) {
                remove_all_actions("wp_ajax_nopriv_{$this->get_id()}");
            }
            add_action("wp_ajax_nopriv_{$this->get_id()}", array($this, 'execute'));
        }
    }

    abstract public function action();

    public function execute()
    {
        /**
         * PHP Warning / Notice Suppression
         */
        if (!defined('WP_DEV_MODE')) {
            ini_set('display_errors', 'Off');
        }

        if ($this->is_protected()) {
            if (!is_user_logged_in()) {
                $error = new \WP_Error('unauthorized', __('You are not authorized to perform this action.', 'mobilo'));
                return self::out($error, WP_Http::UNAUTHORIZED);
            }
        }

        // Check if WooCommerce is active
        if (!class_exists('woocommerce')) {
            $result = new WP_Error('plugin_not_active', __('Oops, WooCommerce plugin was not activated.'));
            return self::out(['result' => $result], WP_Http::BAD_REQUEST);
        }

        // Check if user is not logged-in and nonce is valid
        if (!is_user_logged_in() && $this->nonceVerify) {
            $nonce = isset($_REQUEST['_ajaxNonce']) ? $_REQUEST['_ajaxNonce'] : null;
            if (!wp_verify_nonce($nonce, 'mobilo_react_ajax_nonce')) {
                $error = new \WP_Error('invalid_request', __('Not a valid request.', 'mobilo'));
                return self::out($error, WP_Http::BAD_REQUEST);
            }
        }

        return $this->action();
    }

    /**
     * @param $out
     * @since 1.0.0
     * @access protected
     */
    protected static function out($out, int $status_code = WP_Http::OK): void
    {
        ini_set('display_errors', 'Off');

        // TODO: Execute and out (in Action) should be final and not overrideable. Action needs to NOT force JSON as an object. Could use a parameter to flip JSON to object
        if (!defined('CFW_ACTION_NO_ERROR_SUPPRESSION_BUFFER')) {
            @ob_end_clean(); // @phpcs:ignore
        }

        if (is_array($out) && !isset($out['notices'])) {
            $notices = wc_get_notices();
            $out['notices'] = $notices;
            wc_clear_notices();
        }

        wp_send_json($out, $status_code);
    }

    /**
     * Send Error Response
     * @param string $messageKey
     * @param string $message
     * @param mixed $data
     * @param int $status_code
     * @return void
     */
    public function errorResponse(string $messageKey, string $message, $data, ?int $status_code = WP_Http::BAD_REQUEST): void
    {
        // Plan not found, return error response
        $response = new WP_Error($messageKey, $message, $data);
        self::out($response, $status_code);
        return;
    }
}
