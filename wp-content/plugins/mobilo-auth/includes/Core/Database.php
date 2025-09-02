<?php

namespace MobiloAuth\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database Management Class
 * 
 * @since 1.0.0
 */
class Database
{
    /**
     * Create database tables
     */
    public static function create_tables()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Firebase users table
        $table_firebase_users = $wpdb->prefix . 'mobilo_auth_firebase_users';
        $sql_firebase_users = "CREATE TABLE $table_firebase_users (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            firebase_uid varchar(128) NOT NULL,
            wordpress_user_id bigint(20) NOT NULL,
            email varchar(255) NOT NULL,
            display_name varchar(255),
            phone_number varchar(50),
            photo_url text,
            custom_claims longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_sign_in datetime,
            is_active tinyint(1) DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE KEY firebase_uid (firebase_uid),
            UNIQUE KEY wordpress_user_id (wordpress_user_id),
            KEY email (email),
            KEY is_active (is_active)
        ) $charset_collate;";

        // Firebase sessions table
        $table_firebase_sessions = $wpdb->prefix . 'mobilo_auth_firebase_sessions';
        $sql_firebase_sessions = "CREATE TABLE $table_firebase_sessions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(255) NOT NULL,
            firebase_uid varchar(128) NOT NULL,
            wordpress_user_id bigint(20) NOT NULL,
            id_token text,
            refresh_token text,
            access_token text,
            expires_at datetime,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_active tinyint(1) DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE KEY session_id (session_id),
            KEY firebase_uid (firebase_uid),
            KEY wordpress_user_id (wordpress_user_id),
            KEY expires_at (expires_at),
            KEY is_active (is_active)
        ) $charset_collate;";

        // Firebase auth logs table
        $table_firebase_logs = $wpdb->prefix . 'mobilo_auth_firebase_logs';
        $sql_firebase_logs = "CREATE TABLE $table_firebase_logs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            firebase_uid varchar(128),
            wordpress_user_id bigint(20),
            action varchar(50) NOT NULL,
            status varchar(20) NOT NULL,
            ip_address varchar(45),
            user_agent text,
            details longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY firebase_uid (firebase_uid),
            KEY wordpress_user_id (wordpress_user_id),
            KEY action (action),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Firebase regions table
        $table_firebase_regions = $wpdb->prefix . 'mobilo_auth_firebase_regions';
        $sql_firebase_regions = "CREATE TABLE $table_firebase_regions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            region_code varchar(10) NOT NULL,
            region_name varchar(100) NOT NULL,
            project_id varchar(255) NOT NULL,
            sdk_file_path varchar(500),
            api_key varchar(255),
            auth_domain varchar(255),
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY region_code (region_code),
            KEY is_active (is_active)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Create tables
        dbDelta($sql_firebase_users);
        dbDelta($sql_firebase_sessions);
        dbDelta($sql_firebase_logs);
        dbDelta($sql_firebase_regions);

        // Insert default regions
        self::insert_default_regions();

        // Update database version
        update_option('mobilo_auth_db_version', '1.0.0');
    }

    /**
     * Drop database tables
     */
    public static function drop_tables()
    {
        global $wpdb;

        $tables = [
            $wpdb->prefix . 'mobilo_auth_firebase_users',
            $wpdb->prefix . 'mobilo_auth_firebase_sessions',
            $wpdb->prefix . 'mobilo_auth_firebase_logs',
            $wpdb->prefix . 'mobilo_auth_firebase_regions',
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }

        // Delete options
        delete_option('mobilo_auth_db_version');
    }

    /**
     * Insert default regions
     */
    private static function insert_default_regions()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'mobilo_auth_firebase_regions';

        $default_regions = [
            [
                'region_code' => 'us',
                'region_name' => 'United States',
                'project_id' => '',
                'sdk_file_path' => '',
                'api_key' => '',
                'auth_domain' => '',
                'is_active' => 1
            ],
            [
                'region_code' => 'eu',
                'region_name' => 'Europe',
                'project_id' => '',
                'sdk_file_path' => '',
                'api_key' => '',
                'auth_domain' => '',
                'is_active' => 1
            ]
        ];

        foreach ($default_regions as $region) {
            $wpdb->replace($table, $region);
        }
    }

    /**
     * Get Firebase user by UID
     */
    public static function get_firebase_user($firebase_uid)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'mobilo_auth_firebase_users';

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE firebase_uid = %s AND is_active = 1",
                $firebase_uid
            )
        );
    }

    /**
     * Get Firebase user by WordPress user ID
     */
    public static function get_firebase_user_by_wp_id($wordpress_user_id)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'mobilo_auth_firebase_users';

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE wordpress_user_id = %d AND is_active = 1",
                $wordpress_user_id
            )
        );
    }

    /**
     * Insert or update Firebase user
     */
    public static function upsert_firebase_user($data)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'mobilo_auth_firebase_users';

        $result = $wpdb->replace($table, $data);

        if ($result !== false) {
            return $wpdb->insert_id ?: $data['firebase_uid'];
        }

        return false;
    }

    /**
     * Delete Firebase user
     */
    public static function delete_firebase_user($firebase_uid)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'mobilo_auth_firebase_users';

        return $wpdb->update(
            $table,
            ['is_active' => 0],
            ['firebase_uid' => $firebase_uid],
            ['%d'],
            ['%s']
        );
    }

    /**
     * Get active Firebase session
     */
    public static function get_firebase_session($session_id)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'mobilo_auth_firebase_sessions';

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE session_id = %s AND is_active = 1 AND expires_at > NOW()",
                $session_id
            )
        );
    }

    /**
     * Insert or update Firebase session
     */
    public static function upsert_firebase_session($data)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'mobilo_auth_firebase_sessions';

        $result = $wpdb->replace($table, $data);

        if ($result !== false) {
            return $wpdb->insert_id ?: $data['session_id'];
        }

        return false;
    }

    /**
     * Delete expired Firebase sessions
     */
    public static function cleanup_expired_sessions()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'mobilo_auth_firebase_sessions';

        return $wpdb->query(
            "UPDATE $table SET is_active = 0 WHERE expires_at < NOW() AND is_active = 1"
        );
    }

    /**
     * Log Firebase authentication action
     */
    public static function log_firebase_action($data)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'mobilo_auth_firebase_logs';

        $defaults = [
            'firebase_uid' => '',
            'wordpress_user_id' => 0,
            'action' => '',
            'status' => 'success',
            'ip_address' => self::get_client_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'details' => '',
            'created_at' => current_time('mysql')
        ];

        $log_data = wp_parse_args($data, $defaults);

        return $wpdb->insert($table, $log_data);
    }

    /**
     * Get Firebase region configuration
     */
    public static function get_firebase_region($region_code)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'mobilo_auth_firebase_regions';

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE region_code = %s AND is_active = 1",
                $region_code
            )
        );
    }

    /**
     * Update Firebase region configuration
     */
    public static function update_firebase_region($region_code, $data)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'mobilo_auth_firebase_regions';

        $data['updated_at'] = current_time('mysql');

        return $wpdb->update(
            $table,
            $data,
            ['region_code' => $region_code],
            null,
            ['%s']
        );
    }

    /**
     * Get all active Firebase regions
     */
    public static function get_active_firebase_regions()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'mobilo_auth_firebase_regions';

        return $wpdb->get_results(
            "SELECT * FROM $table WHERE is_active = 1 ORDER BY region_name ASC"
        );
    }

    /**
     * Get authentication statistics
     */
    public static function get_auth_statistics($days = 30)
    {
        global $wpdb;

        $table_logs = $wpdb->prefix . 'mobilo_auth_firebase_logs';
        $table_users = $wpdb->prefix . 'mobilo_auth_firebase_users';

        $stats = [];

        // Total Firebase users
        $stats['total_firebase_users'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_users WHERE is_active = 1"
        );

        // Total active sessions
        $table_sessions = $wpdb->prefix . 'mobilo_auth_firebase_sessions';
        $stats['active_sessions'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_sessions WHERE is_active = 1 AND expires_at > NOW()"
        );

        // Recent authentication attempts
        $stats['recent_auth_attempts'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
                $days
            )
        );

        // Successful logins today
        $stats['logins_today'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_logs WHERE action = 'login' AND status = 'success' AND DATE(created_at) = CURDATE()"
        );

        // Failed logins today
        $stats['failed_logins_today'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_logs WHERE action = 'login' AND status = 'failed' AND DATE(created_at) = CURDATE()"
        );

        return $stats;
    }

    /**
     * Get client IP address
     */
    private static function get_client_ip()
    {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];

        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
    }

    /**
     * Check if database tables exist
     */
    public static function tables_exist()
    {
        global $wpdb;

        $tables = [
            $wpdb->prefix . 'mobilo_auth_firebase_users',
            $wpdb->prefix . 'mobilo_auth_firebase_sessions',
            $wpdb->prefix . 'mobilo_auth_firebase_logs',
            $wpdb->prefix . 'mobilo_auth_firebase_regions',
        ];

        foreach ($tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get database version
     */
    public static function get_db_version()
    {
        return get_option('mobilo_auth_db_version', '0.0.0');
    }

    /**
     * Check if database needs upgrade
     */
    public static function needs_upgrade()
    {
        $current_version = self::get_db_version();
        $required_version = '1.0.0';

        return version_compare($current_version, $required_version, '<');
    }

    /**
     * Upgrade database if needed
     */
    public static function maybe_upgrade()
    {
        if (self::needs_upgrade()) {
            self::create_tables();
        }
    }
}
