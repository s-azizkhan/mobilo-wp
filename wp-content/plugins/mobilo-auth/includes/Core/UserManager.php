<?php

namespace MobiloAuth\Core;

use Throwable;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * User Management Class
 * 
 * @since 1.0.0
 */
class UserManager
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('wp_login', [$this, 'on_user_login'], 10, 2);
        add_action('wp_logout', [$this, 'on_user_logout']);
        add_action('user_register', [$this, 'on_user_register'], 10, 1);
        add_action('profile_update', [$this, 'on_user_update'], 10, 2);
        add_action('delete_user', [$this, 'on_user_delete'], 10, 1);

        // Add custom user meta fields
        add_action('show_user_profile', [$this, 'add_custom_user_fields']);
        add_action('edit_user_profile', [$this, 'add_custom_user_fields']);
        add_action('personal_options_update', [$this, 'save_custom_user_fields']);
        add_action('edit_user_profile_update', [$this, 'save_custom_user_fields']);

        // Add user columns
        add_filter('manage_users_columns', [$this, 'add_user_columns']);
        add_filter('manage_users_custom_column', [$this, 'show_user_column_data'], 10, 3);
    }

    /**
     * Create WordPress user from Firebase user
     */
    public function create_wordpress_user($firebase_user, $password = null)
    {
        try {
            if (!$firebase_user) {
                return false;
            }

            $email = $firebase_user->email;
            $uid = $firebase_user->uid;

            // Check if user already exists
            $existing_user = get_user_by('email', $email);
            if ($existing_user) {
                // Update Firebase ID if not set
                if (!get_user_meta($existing_user->ID, 'firebase_uid', true)) {
                    update_user_meta($existing_user->ID, 'firebase_uid', $uid);
                }
                return $existing_user;
            }

            // Generate password if not provided
            if (!$password) {
                $password = wp_generate_password(12, false);
            }

            // Prepare user data
            $user_data = [
                'user_login' => $email,
                'user_email' => $email,
                'user_pass' => $password,
                'display_name' => $firebase_user->displayName ?: $email,
                'first_name' => $firebase_user->displayName ?: '',
                'role' => 'subscriber',
            ];

            // Create WordPress user
            $user_id = wp_insert_user($user_data);

            if (is_wp_error($user_id)) {
                $this->log_error('Failed to create WordPress user: ' . $user_id->get_error_message());
                return false;
            }

            // Store Firebase user data
            $this->store_firebase_user_data($user_id, $firebase_user);

            // Send welcome email if enabled
            // if (get_option('mobilo_auth_send_welcome_email', true)) {
            //     $this->send_welcome_email($user_id, $password);
            // }

            return get_user_by('id', $user_id);

        } catch (Throwable $e) {
            $this->log_error('Error creating WordPress user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Store Firebase user data in WordPress
     */
    private function store_firebase_user_data($user_id, $firebase_user)
    {
        // Store Firebase UID
        update_user_meta($user_id, 'firebase_uid', $firebase_user->uid);

        // Store additional Firebase user data
        // if ($firebase_user->displayName) {
        //     update_user_meta($user_id, 'firebase_display_name', $firebase_user->displayName);
        // }

        // if ($firebase_user->phoneNumber) {
        //     update_user_meta($user_id, 'firebase_phone', $firebase_user->phoneNumber);
        // }

        // if ($firebase_user->photoUrl) {
        //     update_user_meta($user_id, 'firebase_photo_url', $firebase_user->photoUrl);
        // }

        // // Store Firebase user metadata
        // if ($firebase_user->metadata) {
        //     update_user_meta($user_id, 'firebase_created_at', $firebase_user->metadata->createdAt);
        //     update_user_meta($user_id, 'firebase_last_sign_in', $firebase_user->metadata->lastSignInAt);
        // }

        // Store custom claims if available
        if (method_exists($firebase_user, 'customClaims') && $firebase_user->customClaims) {
            update_user_meta($user_id, 'firebase_custom_claims', json_encode($firebase_user->customClaims));
        }
    }

    /**
     * Update WordPress user from Firebase
     */
    public function update_wordpress_user($firebase_user)
    {
        try {
            if (!$firebase_user) {
                return false;
            }

            $uid = $firebase_user->uid;
            $user_id = self::get_wordpress_user_by_firebase_uid($uid);

            if (!$user_id) {
                return false;
            }

            $user_data = ['ID' => $user_id];

            // Update display name if changed
            if ($firebase_user->displayName) {
                $user_data['display_name'] = $firebase_user->displayName;
                $user_data['first_name'] = $firebase_user->displayName;
            }

            // Update email if changed
            if ($firebase_user->email) {
                $user_data['user_email'] = $firebase_user->email;
            }

            // Update WordPress user
            $result = wp_update_user($user_data);

            if (is_wp_error($result)) {
                $this->log_error('Failed to update WordPress user: ' . $result->get_error_message());
                return false;
            }

            // Update Firebase user data
            $this->store_firebase_user_data($user_id, $firebase_user);

            return true;

        } catch (Throwable $e) {
            $this->log_error('Error updating WordPress user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get WordPress user by Firebase UID
     */
    public static function get_wordpress_user_by_firebase_uid($firebase_uid)
    {
        $users = get_users([
            'meta_key' => 'firebase_uid',
            'meta_value' => $firebase_uid,
            'number' => 1,
        ]);

        return !empty($users) ? $users[0]->ID : false;
    }

    public static function get_firebase_uid_by_wordpress_id($wordpress_id)
    {
        $res = get_user_meta($wordpress_id, 'firebase_uid', true);
        return $res;
    }

    /**
     * Sync user data between Firebase and WordPress
     */
    public function sync_user_data($user_id)
    {
        try {
            $firebase_uid = get_user_meta($user_id, 'firebase_uid', true);
            if (!$firebase_uid) {
                return false;
            }

            $firebase_auth = new FirebaseAuth();
            $firebase_user = $firebase_auth->getUserByUid($firebase_uid);

            if (!$firebase_user) {
                return false;
            }

            return $this->update_wordpress_user($firebase_user);

        } catch (Throwable $e) {
            $this->log_error('Error syncing user data: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle user login
     */
    public function on_user_login($user_login, $user)
    {
        try {
            // Update last login time
            update_user_meta($user->ID, 'last_login', current_time('mysql'));

            // Sync Firebase data if user has Firebase UID
            if (get_user_meta($user->ID, 'firebase_uid', true)) {
                $this->sync_user_data($user->ID);
            }

        } catch (Throwable $e) {
            $this->log_error('Error on user login: ' . $e->getMessage());
        }
    }

    /**
     * Handle user logout
     */
    public function on_user_logout()
    {
        try {
            // Clear Firebase tokens
            delete_transient('mobilo_auth_user_token_' . get_current_user_id());

            // Clear cookie
            setcookie('mobilo_auth_token', '', time() - 3600, '/', '', is_ssl(), true);

        } catch (Throwable $e) {
            $this->log_error('Error on user logout: ' . $e->getMessage());
        }
    }

    /**
     * Handle user registration
     */
    public function on_user_register($user_id)
    {
        try {
            // Set default user meta
            // update_user_meta($user_id, 'registration_date', current_time('mysql'));
            // update_user_meta($user_id, 'user_source', 'wordpress');
        } catch (Throwable $e) {
            $this->log_error('Error on user registration: ' . $e->getMessage());
        }
    }

    /**
     * Handle user update
     */
    public function on_user_update($user_id, $old_user_data)
    {
        try {
            // Update last modified time
            update_user_meta($user_id, 'last_modified', current_time('mysql'));

            // Sync to Firebase if user has Firebase UID
            $firebase_uid = get_user_meta($user_id, 'firebase_uid', true);
            if (!$firebase_uid) {
                $this->sync_to_firebase($user_id);
            }

        } catch (Throwable $e) {
            $this->log_error('Error on user update: ' . $e->getMessage());
        }
    }

    /**
     * Handle user deletion
     */
    public function on_user_delete($user_id)
    {
        try {
            // Clean up user meta
            delete_user_meta($user_id, 'firebase_uid');
            // delete_user_meta($user_id, 'firebase_display_name');
            // delete_user_meta($user_id, 'firebase_phone');
            // delete_user_meta($user_id, 'firebase_photo_url');
            // delete_user_meta($user_id, 'firebase_created_at');
            // delete_user_meta($user_id, 'firebase_last_sign_in');
            delete_user_meta($user_id, 'firebase_custom_claims');

        } catch (Throwable $e) {
            $this->log_error('Error on user deletion: ' . $e->getMessage());
        }
    }

    /**
     * Sync WordPress user data to Firebase
     */
    private function sync_to_firebase($user_id)
    {
        try {
            $firebase_uid = get_user_meta($user_id, 'firebase_uid', true);
            if (!$firebase_uid) {
                return false;
            }

            $user = get_user_by('id', $user_id);
            if (!$user) {
                return false;
            }

            $firebase_auth = new FirebaseAuth();

            $user_data = [];
            if ($user->display_name) {
                $user_data['display_name'] = $user->display_name;
            }
            if ($user->user_email) {
                $user_data['email'] = $user->user_email;
            }

            if (!empty($user_data)) {
                $firebase_auth->updateUser($firebase_uid, $user_data);
            }

            return true;

        } catch (Throwable $e) {
            $this->log_error('Error syncing to Firebase: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Add custom user fields to profile
     */
    public function add_custom_user_fields($user)
    {
        $firebase_uid = get_user_meta($user->ID, 'firebase_uid', true);
        // $firebase_display_name = get_user_meta($user->ID, 'firebase_display_name', true);
        // $firebase_phone = get_user_meta($user->ID, 'firebase_phone', true);
        // $firebase_photo_url = get_user_meta($user->ID, 'firebase_photo_url', true);
        ?>
        <h3><?php _e('Firebase Authentication', 'mobilo-auth'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="firebase_uid"><?php _e('Firebase UID', 'mobilo-auth'); ?></label></th>
                <td>
                    <input type="text" name="firebase_uid" id="firebase_uid" value="<?php echo esc_attr($firebase_uid); ?>"
                        class="regular-text" readonly />
                    <p class="description"><?php _e('Firebase user ID (read-only)', 'mobilo-auth'); ?></p>
                </td>
            </tr>
            <!-- <tr>
                <th><label for="firebase_display_name"><?php _e('Firebase Display Name', 'mobilo-auth'); ?></label></th>
                <td>
                    <input type="text" name="firebase_display_name" id="firebase_display_name"
                        value="<?php echo esc_attr($firebase_display_name); ?>" class="regular-text" readonly />
                    <p class="description"><?php _e('Firebase display name (read-only)', 'mobilo-auth'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="firebase_phone"><?php _e('Firebase Phone', 'mobilo-auth'); ?></label></th>
                <td>
                    <input type="text" name="firebase_phone" id="firebase_phone"
                        value="<?php echo esc_attr($firebase_phone); ?>" class="regular-text" readonly />
                    <p class="description"><?php _e('Firebase phone number (read-only)', 'mobilo-auth'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="firebase_photo_url"><?php _e('Firebase Photo URL', 'mobilo-auth'); ?></label></th>
                <td>
                    <input type="text" name="firebase_photo_url" id="firebase_photo_url"
                        value="<?php echo esc_attr($firebase_photo_url); ?>" class="regular-text" readonly />
                    <p class="description"><?php _e('Firebase profile photo URL (read-only)', 'mobilo-auth'); ?></p>
                </td>
            </tr> -->
        </table>
        <?php
    }

    /**
     * Save custom user fields
     */
    public function save_custom_user_fields($user_id)
    {
        // These fields are read-only, so we don't save them
        // They are managed by Firebase integration
    }

    /**
     * Add user columns
     */
    public function add_user_columns($columns)
    {
        $columns['firebase_uid'] = __('Firebase UID', 'mobilo-auth');
        $columns['firebase_status'] = __('Firebase Status', 'mobilo-auth');
        $columns['last_login'] = __('Last Login', 'mobilo-auth');
        return $columns;
    }

    /**
     * Show user column data
     */
    public function show_user_column_data($value, $column_name, $user_id)
    {
        switch ($column_name) {
            case 'firebase_uid':
                $firebase_uid = get_user_meta($user_id, 'firebase_uid', true);
                return $firebase_uid ? $firebase_uid : '—';

            case 'firebase_status':
                $firebase_uid = get_user_meta($user_id, 'firebase_uid', true);
                if ($firebase_uid) {
                    return '<span style="color: green;">✓ ' . __('Connected', 'mobilo-auth') . '</span>';
                } else {
                    return '<span style="color: red;">✗ ' . __('Not Connected', 'mobilo-auth') . '</span>';
                }

            case 'last_login':
                $last_login = get_user_meta($user_id, 'last_login', true);
                return $last_login ? date_i18n(get_option('date_format'), strtotime($last_login)) : '—';

            default:
                return $value;
        }
    }

    /**
     * Send welcome email
     */
    private function send_welcome_email($user_id, $password)
    {
        try {
            $user = get_user_by('id', $user_id);
            if (!$user) {
                return false;
            }

            $to = $user->user_email;
            $subject = sprintf(__('Welcome to %s', 'mobilo-auth'), get_bloginfo('name'));

            $message = sprintf(
                __('Hello %s,

Welcome to %s! Your account has been created successfully.

Your login credentials:
Username: %s
Password: %s

You can log in at: %s

Best regards,
%s Team', 'mobilo-auth'),
                $user->display_name,
                get_bloginfo('name'),
                $user->user_login,
                $password,
                wp_login_url(),
                get_bloginfo('name')
            );

            $headers = ['Content-Type: text/html; charset=UTF-8'];

            return wp_mail($to, $subject, $message, $headers);

        } catch (Throwable $e) {
            $this->log_error('Error sending welcome email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log error message
     */
    private function log_error($message, $level = 'error')
    {
        if (function_exists('mobilo_log')) {
            mobilo_log(__METHOD__, $message, $level);
        } else {
            error_log("Mobilo Auth UserManager: $message");
        }
    }
}
