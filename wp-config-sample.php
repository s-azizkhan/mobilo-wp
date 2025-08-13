<?php
// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'database_name_here');

/** Database username */
define('DB_USER', 'username_here');

/** Database password */
define('DB_PASSWORD', 'password_here');

/** Database hostname */
define('DB_HOST', 'localhost');

/** Database charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The database collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '?9]7bOuvgtOjlG>if(X!$$fA/ZD%#uL{Lt-L).6u6h9|:p+pnx0LqI1S+Gfot@it');
define('SECURE_AUTH_KEY',  'u>5#`GIpB7nJ{Lwy.zBgW<T~.`+P[4Mh5~&YY_`L0qn=!6O VG>dXAn;pP>].Wt}');
define('LOGGED_IN_KEY',    '`UT]1c|{!gt9|:[bX]ichL!URG]4uIkFu.:+z5SQW$WSXMVPEF3v/iKK06=mmloP');
define('NONCE_KEY',        ';?n(/mdgNmJ|$tYh,teZi%Byrb=SJY:3]FCQ)UGcS(`Ko=<4F5LA*Z6hOXT%pEHU');
define('AUTH_SALT',        'bFFj,KnAB!A.q6<:!;RGQ66/7X/:tG{/t7B29N}W{E}o9:?7.{g#~1JZk[:dWDCV');
define('SECURE_AUTH_SALT', 'G2/1[HB3QS~lUZ^nQ$f_8})4`uQht`Jo=A,x[kb>62!GET3zT925>zb,-TEHq%y/');
define('LOGGED_IN_SALT',   ':EvaFDr=#LZ%~<N#=T!A;Jl#SkEpkZpT!EC^,NY_CHxebI$pr~H{z?vwbY<e+XdV');
define('NONCE_SALT',       '|pit3#(K^J f=Cq.;#q}9o0[AnQz$[TwjrZKUk@iPa0<}Avn7B!sU5Xxp`b: 78r');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
// Caching & Object Cache
define('WP_CACHE', true);

// memory limit
define('WP_MEMORY_LIMIT', '256M');

// Environment
define('WP_ENV', 'development');


/* Add any custom values between this line and the "stop editing" line. */

define('DISABLE_WP_CRON', false);
define('FS_METHOD', 'direct');

// Security Headers
// define( 'FORCE_SSL_ADMIN', true );
define('COOKIE_DOMAIN', '.mobilo.dev');

// Redis Object Cache (if available)
if (class_exists('Redis')) {
	define('WP_REDIS_HOST', '127.0.0.1');
	define('WP_REDIS_PORT', 6379);
	define('WP_REDIS_TIMEOUT', 1);
	define('WP_REDIS_READ_TIMEOUT', 1);
	define('WP_REDIS_DATABASE', 0);
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (! defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
