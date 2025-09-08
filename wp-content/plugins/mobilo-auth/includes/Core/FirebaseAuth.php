<?php

namespace MobiloAuth\Core;

use MobiloAuth\Utils\JWTUtil;

if (!defined('ABSPATH')) {
    exit;
}

use Kreait\Firebase\Factory;
use Throwable;

/**
 * Enhanced Firebase Authentication Class
 * 
 * @since 1.0.0
 */
class FirebaseAuth
{
    /**
     * Firebase Auth instance
     */
    private $auth;

    /**
     * Error message
     */
    private $error = null;

    /**
     * Current region
     */
    private $region = null;

    /**
     * Firebase configuration
     */
    private $config = null;

    /**
     * Constructor
     *
     * @param string|null $region
     * @param array|null $config
     */
    public function __construct($region = 'us', $config = null)
    {
        $this->region = $region;
        $this->config = $config ?: $this->getDefaultConfig();

        try {
            $this->connect();
        } catch (Throwable $e) {
            $this->error = $e->getMessage();
            $this->logError($e->getMessage());

            if (is_admin()) {
                add_action('admin_notices', [$this, 'admin_notice']);
            }
        }
    }

    /**
     * Get default Firebase configuration
     */
    private function getDefaultConfig()
    {
        // $settings = get_option('mobilo_auth_settings', []);
        // return [
        //     'project_id' => isset($settings['firebase_project_id']) ? $settings['firebase_project_id'] : '',
        //     'region' => isset($settings['firebase_region']) ? $settings['firebase_region'] : 'us',
        //     'sdk_file' => isset($settings['firebase_sdk_file']) ? $settings['firebase_sdk_file'] : '',
        //     'api_key' => isset($settings['firebase_api_key']) ? $settings['firebase_api_key'] : '',
        //     'auth_domain' => isset($settings['firebase_auth_domain']) ? $settings['firebase_auth_domain'] : '',
        // ];

        return [
            'project_id' => '',
            'region' => 'us',
            'sdk_file' => 'firebase.json',
            'api_key' => '',
            'auth_domain' => '',
        ];
    }

    /**
     * Connect to Firebase
     */
    private function connect()
    {
        try {
            $sdk_file_path = $this->getSdkFilePath();

            if (!file_exists($sdk_file_path)) {
                throw new \Exception('Firebase SDK file not found: ' . $sdk_file_path);
            }

            $factory = (new Factory())->withServiceAccount($sdk_file_path);

            // Set custom options if provided
            if (!empty($this->config['project_id'])) {
                $factory = $factory->withProjectId($this->config['project_id']);
            }

            $this->auth = $factory->createAuth();

        } catch (Throwable $e) {
            $this->error = $e->getMessage();
            $this->logError('Error connecting to Firebase: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get SDK file path
     */
    private function getSdkFilePath()
    {
        // $settings = get_option('mobilo_auth_settings', []);

        if ($this->region === 'eu' || $this->region === 'Europe') {
            $sdk_file = 'firebase_eu.json';
            // $sdk_file = isset($settings['firebase_sdk_file_eu']) ? $settings['firebase_sdk_file_eu'] : $this->config['sdk_file'];
        } else {
            // TODO: Remove this after testing
            $sdk_file = 'firebase-dev.json';
            // $sdk_file = isset($settings['firebase_sdk_file']) ? $settings['firebase_sdk_file'] : $this->config['sdk_file'];
        }

        // // Check if it's a full path or just filename
        // if (file_exists($sdk_file)) {
        //     return $sdk_file;
        // }

        // // Try to find in theme directory
        // $theme_path = get_template_directory() . '/json/' . $sdk_file;
        // if (file_exists($theme_path)) {
        //     return $theme_path;
        // }

        // Try plugin directory
        $plugin_path = MOBILO_AUTH_PLUGIN_DIR . 'config/' . $sdk_file;
        if (file_exists($plugin_path)) {
            return $plugin_path;
        }

        throw new \Exception('Firebase SDK file not found in any location');
    }

    /**
     * Show admin notice for errors
     */
    public function admin_notice()
    {
        if (!$this->error) {
            return;
        }

        $settings_url = admin_url('admin.php?page=mobilo-auth-settings');
        ?>
        <div class="notice notice-error">
            <p><strong><?php _e('Mobilo Auth - Firebase Error:', 'mobilo-auth'); ?></strong></p>
            <p><?php echo esc_html($this->error); ?></p>
            <p>
                <a href="<?php echo esc_url($settings_url); ?>" class="button button-primary">
                    <?php _e('Configure Firebase Settings', 'mobilo-auth'); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Get user by email
     * 
     * @return \Kreait\Firebase\Auth\UserRecord | null
     */
    public function getUserByEmail($email)
    {
        try {
            if (!is_email($email)) {
                return null;
            }

            return $this->auth->getUserByEmail($email);
        } catch (Throwable $e) {
            $this->logError('Error getting user by email: ' . $e->getMessage(), 'info');
            return null;
        }
    }

    /**
     * Get user by UID
     */
    public function getUserByUid($uid)
    {
        try {
            if (empty($uid)) {
                return null;
            }

            return $this->auth->getUser($uid);
        } catch (Throwable $e) {
            $this->logError('Error getting user by UID: ' . $e->getMessage(), 'info');
            return null;
        }
    }

    /**
     * Create new user
     */
    public function createUser($email, $password, $userData = [])
    {
        try {
            if (!is_email($email)) {
                return null;
            }

            $userProperties = [
                'email' => $email,
                'password' => $password,
            ];

            // Add additional user properties if provided
            if (!empty($userData['display_name'])) {
                $userProperties['displayName'] = $userData['display_name'];
            }
            if (!empty($userData['phone'])) {
                $userProperties['phoneNumber'] = $userData['phone'];
            }
            if (!empty($userData['photo_url'])) {
                $userProperties['photoUrl'] = $userData['photo_url'];
            }

            return $this->auth->createUser($userProperties);
        } catch (Throwable $e) {
            $this->error = $e->getMessage();
            $this->logError(__METHOD__, 'Error creating user: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Sign in with email and password
     */
    public function signInWithEmailAndPassword($email, $password)
    {
        try {
            if (!is_email($email)) {
                return new \WP_Error('invalid_email', __('Invalid email address', 'mobilo-auth'));
            }

            $result = $this->auth->signInWithEmailAndPassword($email, $password);

            // Store token data
            $this->storeTokenData($result);

            return $result;
        } catch (Throwable $e) {
            $this->logError(__METHOD__, "Firebase sign in failed for $email: " . $e->getMessage());
            return new \WP_Error('firebase_auth_fail', $e->getMessage());
        }
    }

    /**
     * Sign in with custom token
     */
    public function signInWithCustomToken($customToken)
    {
        try {
            $result = $this->auth->signInWithCustomToken($customToken);
            $this->storeTokenData($result);
            return $result;
        } catch (Throwable $e) {
            $this->logError(__METHOD__, 'Error signing in with custom token: ' . $e->getMessage());
            return new \WP_Error('firebase_auth_fail', $e->getMessage());
        }
    }

    /**
     * Sign in by user ID
     */
    public function signInByUserId($uid, $sendFullData = false)
    {
        try {
            $signInResult = $this->auth->signInAsUser($uid);

            if ($sendFullData) {
                return $signInResult;
            }

            $data = $signInResult->data();
            return isset($data['idToken']) ? $data['idToken'] : null;
        } catch (Throwable $e) {
            $this->logError(__METHOD__, 'Error signing in by user ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify ID token
     */
    public function verifyIdToken($token)
    {
        try {
            return $this->auth->verifyIdToken($token);
        } catch (Throwable $e) {
            $this->logError(__METHOD__, 'Error verifying ID token: ' . $e->getMessage());
            return new \WP_Error('token_verification_failed', $e->getMessage());
        }
    }

    /**
     * Create custom token
     */
    public function createCustomToken($uid, $additionalClaims = [])
    {
        try {
            return $this->auth->createCustomToken($uid, $additionalClaims);
        } catch (Throwable $e) {
            $this->logError(__METHOD__, 'Error creating custom token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetLink($email)
    {
        try {
            if (empty($email)) {
                return false;
            }

            $this->auth->sendPasswordResetLink($email);
            $this->logInfo(__METHOD__, "Password reset email sent to: $email");
            return true;
        } catch (Throwable $e) {
            $this->logError(__METHOD__, 'Error sending password reset: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Change user password
     */
    public function changeUserPassword($uid, $newPassword)
    {
        try {
            $this->logInfo(__METHOD__, "Changing password for user: $uid");
            return $this->auth->changeUserPassword($uid, $newPassword);
        } catch (Throwable $e) {
            $this->logError(__METHOD__, 'Error changing password: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update user profile
     */
    public function updateUser($uid, $userData)
    {
        try {
            $properties = [];

            if (isset($userData['display_name'])) {
                $properties['displayName'] = $userData['display_name'];
            }
            if (isset($userData['email'])) {
                $properties['email'] = $userData['email'];
            }
            if (isset($userData['phone'])) {
                $properties['phoneNumber'] = $userData['phone'];
            }
            if (isset($userData['photo_url'])) {
                $properties['photoUrl'] = $userData['photo_url'];
            }

            if (empty($properties)) {
                return null;
            }

            return $this->auth->updateUser($uid, $properties);
        } catch (Throwable $e) {
            $this->logError(__METHOD__, 'Error updating user: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($uid)
    {
        try {
            $this->auth->deleteUser($uid);
            $this->logInfo(__METHOD__, "User deleted: $uid");
            return true;
        } catch (Throwable $e) {
            $this->logError(__METHOD__, 'Error deleting user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get current user token data
     */
    public static function getCurrentUserToken()
    {
        // Access global variable containing user's Firebase token data
        global $user_firebase_token_data;
        $token_data = null;

        // Check if user Firebase token data is available
        if (!isset($user_firebase_token_data) || empty($user_firebase_token_data) || !$user_firebase_token_data) {
            $token_data = get_transient('mobilo_auth_user_token_' . get_current_user_id());

        }

        if (!$token_data) {
            // Try to get from cookie as fallback and remove any slashes
            $cookie_data = wp_unslash(mc_get_cookie('mobilo_auth_token'));

            // Decode the cookie value as JSON and return the resul
            if ($cookie_data) {
                $token_data = json_decode($cookie_data, true);
            }
        }

        return $token_data;
    }
    public static function getCurrentUser()
    {
        try {
            // Get the token data from the cookie
            $tokenData = self::getCurrentUserToken();

            $result = false;
            // Check if the token exists and is valid
            if ($tokenData && !empty($tokenData)) {
                // Verify the token and return the user data
                //$result = $this->auth->verifyIdToken($tokenData->id_token)->claims()->all();
                $decodedData = JWTUtil::decode($tokenData->access_token ?: $tokenData->id_token);

                if (isset($decodedData['payload'])) {
                    $result = $decodedData['payload'];
                }
            }

            // Return false if the token is invalid or not present
            return $result;
        } catch (Throwable $th) {
            mobilo_auth_log(__METHOD__, $th->getMessage(), 'warning');
            return false;
        }
    }
    /**
     * Store token data
     */
    private function storeTokenData($signInResult)
    {
        try {
            $token_data = $signInResult->asTokenResponse();

            // Store in transient
            set_transient('mobilo_auth_user_token_' . get_current_user_id(), $token_data, HOUR_IN_SECONDS);

            // Store in cookie as fallback
            setcookie('mobilo_auth_token', json_encode($token_data), time() + HOUR_IN_SECONDS, '/', '', is_ssl(), true);

        } catch (Throwable $e) {
            $this->logError(__METHOD__, 'Error storing token data: ' . $e->getMessage());
        }
    }

    /**
     * Refresh token
     */
    public function refreshToken($refreshToken)
    {
        try {
            $result = $this->auth->signInWithRefreshToken($refreshToken);
            $this->storeTokenData($result);
            return $result;
        } catch (Throwable $e) {
            $this->logError(__METHOD__, 'Error refreshing token: ' . $e->getMessage());
            return new \WP_Error('token_refresh_failed', $e->getMessage());
        }
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated()
    {
        $token_data = self::getCurrentUserToken();
        if (!$token_data) {
            return false;
        }

        try {
            $this->verifyIdToken($token_data['id_token']);
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Get current user from Firebase
     */
    public function getCurrentFirebaseUser()
    {
        $token_data = self::getCurrentUserToken();
        if (!$token_data || empty($token_data['user_id'])) {
            return null;
        }

        return $this->getUserByUid($token_data['user_id']);
    }

    /**
     * Log error message
     */
    private function logError($from, $message, $level = 'error')
    {
        if (function_exists('mobilo_auth_log')) {
            mobilo_auth_log($from, $message, $level);
        } else {
            error_log("Mobilo Auth: $message");
        }
    }

    /**
     * Log info message
     */
    private function logInfo($from, $message)
    {
        $this->logError($from, $message, 'info');
    }

    /**
     * Get Firebase Auth instance
     */
    public function getAuth()
    {
        return $this->auth ? $this->auth : null;
    }

    /**
     * Get current region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Get error message
     */
    public function getError()
    {
        return $this->error;
    }
}
