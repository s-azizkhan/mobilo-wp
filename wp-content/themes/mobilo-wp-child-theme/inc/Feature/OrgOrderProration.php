<?php

namespace Mobilo\WpTheme\Feature;

// use Mobilo\WpTheme\Actions\AddOrgProrationAction;
// use Mobilo\WpTheme\Actions\DeleteOrgProrationAction;
// use Mobilo\WpTheme\RestApi\Controllers\Version1\Proration\ProrationApiController;
use InvalidArgumentException;
use MobiloAuth\Core\UserManager;
use RuntimeException;
use Throwable;

defined('ABSPATH') || exit;

/**
 * Class OrgOrderProration
 *
 * @link logicwind.com
 * @since 0.4.2
 * @version 1.0.1
 * @package Mobilo\WpTheme\Feature
 * @author Aziz Khan <aziz@logicwind.com>
 */
class OrgOrderProration extends BaseFeature
{
    private static $featureKey = 'new_plans';
    private static $dbTableName = 'lwmc_org_prorations';
    private static $isAdminMetaKey = 'lwmc_is_org_admin';

    public function __construct()
    {
        parent::__construct(self::$featureKey);


    }

    public function actionInit()
    {
        // $this->registerAjax();
        // $this->registerRestApis();
    }

    public function adminInit()
    {
        add_action('after_switch_theme', [$this, 'createOrgProrationTable']);
        // Enqueue scripts on the admin side
        add_action('admin_enqueue_scripts', [$this, 'enqueueStyle']);
        add_action('admin_menu', [$this, 'addOrgProrationSubmenu']);
    }

    public function registerAjax()
    {
        // try {
        //     (new AddOrgProrationAction())->load();
        // } catch (Throwable $th) {
        //     mobilo_log(__METHOD__, $th->getMessage());
        // }

        // try {
        //     (new DeleteOrgProrationAction())->load();
        // } catch (Throwable $th) {
        //     mobilo_log(__METHOD__, $th->getMessage());
        // }
    }

    private function registerRestApis()
    {
        // try {
        //     ProrationApiController::run();
        // } catch (Throwable $th) {
        //     mobilo_log(__METHOD__, $th->getMessage());
        // }
    }

    public function createOrgProrationTable()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$dbTableName;
        $charset_collate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $sql = "CREATE TABLE $table_name (
            id bigint(9) NOT NULL AUTO_INCREMENT,
            org_id varchar(191) NOT NULL,
            admin_user_ids JSON,
            expiry_date datetime,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }
    }

    /**
     * Handle profile update.
     */
    public static function handleProfileUpdate(\WP_REST_Request $request, string $firebaseUserId)
    {
        try {
            // Validate and sanitize input parameters
            $orgId = sanitize_text_field($request->get_param('orgId'));
            $isOrgAdmin = filter_var($request->get_param('isOrgAdmin'), FILTER_VALIDATE_BOOLEAN);
            $isDeleted = filter_var($request->get_param('isDeleted'), FILTER_VALIDATE_BOOLEAN);

            // if account is deleted, remove user from proration
            if ($isDeleted) {
                self::removeUserFromOrgProration($orgId, $firebaseUserId);
                return;
            }

            if (!$orgId) {
                //  if no org id provided then do nothing
                return;
            }

            // Fetch proration details for the user
            $orgProration = self::getProrationByAdminId(null, $firebaseUserId);

            // Handle proration logic based on admin status
            if (!$orgProration && $isOrgAdmin) {
                // Find proration by orgId
                $orgProration = self::getProrationByOrgId($orgId);
                if ($orgProration) {
                    self::addUserToOrgProration($orgId, $firebaseUserId);
                } else {
                    $expiryDate = self::getSubscriptionExpiryDateByFirebaseId($firebaseUserId);
                    self::createOrgProration($orgId, $firebaseUserId, $expiryDate);
                }
                // Log successful profile update
                mobilo_log(
                    __METHOD__,
                    "Proration updated successfully for user: $firebaseUserId in org: $orgId, orgProrationData: " . json_encode($orgProration),
                    'info'
                );
                return;
            } elseif ($orgProration && !$isOrgAdmin) {
                self::removeUserFromOrgProration($orgId, $firebaseUserId);
            }

            return;
        } catch (InvalidArgumentException $e) {
            // Log input validation errors
            mobilo_log(__METHOD__, "Input validation error: " . $e->getMessage());
        } catch (RuntimeException $e) {
            // Log runtime errors
            mobilo_log(__METHOD__, "Runtime error: " . $e->getMessage());
        } catch (Throwable $th) {
            // Log unexpected errors
            mobilo_log(__METHOD__, "Unexpected error: " . $th->getMessage());
        }
    }

    /**
     * Get the expiry date of the last active subscription for the user.
     * @param string $uid
     * @return null|string
     */
    public static function getSubscriptionExpiryDateByFirebaseId(string $uid)
    {
        try {
            $wpUserId = UserManager::get_wordpress_user_by_firebase_uid($uid);
            if (!$wpUserId) {
                return null;
            }

            // get last active subscription for user
            $lastActiveSubscription = self::get_latest_active_subscription($wpUserId);
            if (!$lastActiveSubscription) {
                return null;
            }

            return $lastActiveSubscription->next_payment_date;
        } catch (Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
            return null;
        }
    }

    /**
     * Get the latest active subscription for a user by user ID.
     *
     * @param int $customer_user_id The user ID.
     * @return array|object|null The latest active subscription object or null if none found.
     */
    public static function get_latest_active_subscription($customer_user_id)
    {
        global $wpdb;

        // Prepare the SQL query
        $query = $wpdb->prepare("
            SELECT sub.ID as id, sub.post_status as status, sd.meta_value as start_date, npd.meta_value as next_payment_date
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} sub ON ( pm.post_id = sub.ID )
            INNER JOIN {$wpdb->postmeta} sd ON ( pm.post_id = sd.post_id )
            INNER JOIN {$wpdb->postmeta} npd ON ( pm.post_id = npd.post_id )
            WHERE pm.meta_key = '_customer_user'
            AND pm.meta_value = %d
            AND sub.post_status = 'wc-active'
            AND sd.meta_key = '_schedule_start'
            AND npd.meta_key = '_schedule_next_payment'
            ORDER BY start_date DESC
            LIMIT 1;
        ", $customer_user_id);

        // Execute the query
        $result = $wpdb->get_row($query);

        // Return the result
        return $result;
    }

    public static function getProrationByAdminId(?int $user_id = null, ?string $admin_id = null)
    {
        try {
            global $wpdb;
            if ($admin_id) {
                $admin_id = '"' . $admin_id . '"';
                $table_name = $wpdb->prefix . self::$dbTableName;
                $query = $wpdb->prepare("SELECT * FROM $table_name WHERE JSON_CONTAINS(admin_user_ids, %s)", $admin_id);
            } else {
                if (!$user_id) {
                    $user_id = get_current_user_id();
                }
                if (!$user_id) {
                    return null;
                }
                // get the firebase user id
                $admin_id = UserManager::get_firebase_uid_by_wordpress_id($user_id);

                if (!$admin_id) {
                    return null;
                }

                $table_name = $wpdb->prefix . self::$dbTableName;
                $admin_id = '"' . $admin_id . '"';
                $query = $wpdb->prepare("SELECT * FROM $table_name WHERE JSON_CONTAINS(admin_user_ids, %s)", $admin_id);
            }

            if (!isset($query)) {
                return null;
            }

            //current timestamp
            $result = $wpdb->get_row($query);
            if ($result && (!$result->expiry_date || strtotime($result->expiry_date) < time())) {
                $currentTimestamp = date('Y-m-d H:i:s');
                $result->expiry_date = $currentTimestamp;
            }

            return $result;
        } catch (Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
            return null;
        }
    }

    public static function getProrationByOrgId(string $org_id)
    {
        try {
            if (!$org_id) {
                return null;
            }

            global $wpdb;
            $table_name = $wpdb->prefix . self::$dbTableName;

            // prepare query
            $query = $wpdb->prepare("SELECT * FROM $table_name WHERE org_id = %s", $org_id);
            $result = $wpdb->get_row($query);
            return $result;
        } catch (Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
            return null;
        }
    }

    public static function deleteOrgProrationById($id)
    {
        try {
            if (!$id) {
                return false;
            }

            global $wpdb;
            $table_name = $wpdb->prefix . self::$dbTableName;

            $result = $wpdb->delete($table_name, array('id' => $id));
            return $result;
        } catch (Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
            return false;
        }
    }

    public static function deleteOrgProration($org_id)
    {
        try {
            if (!$org_id) {
                return false;
            }

            global $wpdb;
            $table_name = $wpdb->prefix . self::$dbTableName;

            $result = $wpdb->delete($table_name, array('org_id' => $org_id));

            mobilo_log(__METHOD__, "Org proration deleted for orgId: {$org_id}", 'info');
            return $result;
        } catch (Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
            return false;
        }
    }

    public static function removeUserFromOrgProration($org_id, $user_id)
    {
        try {
            if (!$org_id || !$user_id) {
                return false;
            }

            global $wpdb;
            $table_name = $wpdb->prefix . self::$dbTableName;
            $existingAdminUsers = json_decode($wpdb->get_var($wpdb->prepare("SELECT admin_user_ids FROM $table_name WHERE org_id = %s", $org_id)), true) ?? [];

            if (!$existingAdminUsers || empty($existingAdminUsers) || !in_array($user_id, $existingAdminUsers)) {
                return false;
            }

            $newAdminUsers = array_diff($existingAdminUsers, [$user_id]);

            $result = $wpdb->update(
                $table_name,
                array(
                    'admin_user_ids' => json_encode($newAdminUsers),
                ),
                array(
                    'org_id' => $org_id,
                )
            );

            mobilo_log(__METHOD__, "Org:{$org_id} admin: <{$user_id}> removed.", 'info', 'info');
            return $result;
        } catch (Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
            return false;
        }
    }

    public static function addUserToOrgProration($org_id, $user_id)
    {
        try {
            if (!$org_id || !$user_id) {
                return false;
            }

            global $wpdb;
            $table_name = $wpdb->prefix . self::$dbTableName;
            $existingAdminUsers = json_decode($wpdb->get_var($wpdb->prepare("SELECT admin_user_ids FROM $table_name WHERE org_id = %s", $org_id)), true) ?? [];

            if (in_array($user_id, $existingAdminUsers)) {
                return false;
            }

            $newAdminUsers = array_merge($existingAdminUsers, [$user_id]);

            $result = $wpdb->update(
                $table_name,
                array(
                    'admin_user_ids' => json_encode($newAdminUsers),
                ),
                array(
                    'org_id' => $org_id,
                )
            );

            mobilo_log(__METHOD__, "Org:{$org_id} admin: <{$user_id}> added.", 'info');
            return $result;
        } catch (Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
            return false;
        }
    }

    public static function updateOrgProrationDate($org_id, $expiry_date)
    {
        try {
            if (!$org_id || !$expiry_date) {
                return false;
            }
            global $wpdb;
            $table_name = $wpdb->prefix . self::$dbTableName;
            $result = $wpdb->update(
                $table_name,
                array(
                    'expiry_date' => $expiry_date,
                ),
                array(
                    'org_id' => $org_id,
                )
            );

            mobilo_log(__METHOD__, "Org:{$org_id} proration: <{$expiry_date}> updated.", 'info');
            return $result;
        } catch (Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
        }
    }

    /**
     * Replace all admin IDs with provided IDs for org Proration
     *
     * @since 1.0.1
     */
    public static function replaceProrationAdminIdsByOrgId(string $org_id, array $admin_ids)
    {
        try {
            if (!$org_id || !$admin_ids || !is_array($admin_ids)) {
                return false;
            }

            global $wpdb;
            $table_name = $wpdb->prefix . self::$dbTableName;

            $result = $wpdb->update(
                $table_name,
                array(
                    'admin_user_ids' => json_encode($admin_ids),
                ),
                array(
                    'org_id' => $org_id,
                )
            );

            mobilo_log(__METHOD__, "Admin IDS replaced for Org:{$org_id}", 'info');
            return $result;
        } catch (Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
            return false;
        }
    }

    public static function createOrgProration($org_id, $admin_ids, $expiry_date = null)
    {
        try {

            if (!$org_id || !$admin_ids) {
                return false;
            }

            if (!is_array($admin_ids)) {
                $admin_ids = [$admin_ids];
            }

            global $wpdb;
            $table_name = $wpdb->prefix . self::$dbTableName;

            // Check if the org_id already exists then ignore
            $existing = self::getProrationByOrgId($org_id);
            if ($existing) {
                mobilo_log(__METHOD__, "Org:{$org_id} proration already exists fo ignored creation.", 'info');
                return false;
            }

            $result = $wpdb->insert(
                $table_name,
                [
                    'org_id' => $org_id,
                    'expiry_date' => $expiry_date,
                    'admin_user_ids' => json_encode($admin_ids),
                ]
            );
            mobilo_log(__METHOD__, "Org:{$org_id} proration: <{$expiry_date}> created.", 'info');

            return $result;
        } catch (Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
            return false;
        }
    }

    public static function getAllOrgProration()
    {
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . self::$dbTableName;
            $query = "SELECT * FROM $table_name";
            $result = $wpdb->get_results($query);
            return $result;
        } catch (Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
            return null;
        }
    }

    public function enqueueStyle()
    {
        // get the current screen
        $screen = get_current_screen();
        // add the style only on the 'users_page_lwmc-org-proration' screen
        if (!$screen || $screen->id !== 'users_page_lwmc-org-proration') {
            return;
        }
        wp_enqueue_style('lwmc-org-proration', MOBILO_THEME_URL . '/css/admin/org-proration.css', [], time());

        wp_enqueue_style('lwmc-jquery.bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
        wp_enqueue_style('lwmc-jquery.dataTables', 'https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css');
        wp_enqueue_style('lwmc-jquery.dataTables-responsive', 'https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css');

        wp_enqueue_script('lwmc-jquery.bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js');
        wp_enqueue_script('lwmc-jquery.dataTables', 'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js');
        wp_enqueue_script('lwmc-jquery.dataTables-responsive', 'https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js');
    }

    public function addOrgProrationSubmenu()
    {
        add_users_page(
            __('Org Proration', 'mobilo'), // Page title
            __('Org Proration', 'mobilo'), // Menu title
            'manage_options', // Capability
            'lwmc-org-proration', // Menu slug
            array($this, 'renderOrgProrationPage') // Callback function
        );
    }

    public function renderOrgProrationPage()
    {
        $data = self::getAllOrgProration();
        include_once MOBILO_THEME_PATH . '/views/org-proration-view.php';
    }

    public static function isOrgAdmin($user_id = null)
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        if (!$user_id) {
            return false;
        }
        return get_user_meta($user_id, self::$isAdminMetaKey, true);
    }
}
