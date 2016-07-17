<?php
/** Enable W3 Total Cache */
define('WP_CACHE', true); // Added by W3 Total Cache

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'db215537_1clk_wordpress_eCGru7hHX8MH1G1y');

/** MySQL database username */
define('DB_USER', 'wordpress_x7K5Hq');

/** MySQL database password */
define('DB_PASSWORD', 'YFo88CT2');

/** MySQL hostname */
define('DB_HOST', 'internal-db.s215537.gridserver.com');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '9hAeVEyVlxjPqCPecOoXUslfx8atZeAEANKR8cR18Ed1DmnJsbRIFFc87uz3KP5f');
define('SECURE_AUTH_KEY',  '4TXBnhh9moJP8mB1pMTiGYLv7dni0KicPv7w3vcFAECv192gN9kDbotzcUAkaawS');
define('LOGGED_IN_KEY',    'REg9P87LCjRxPHbqajLgwgPWmWnVpvrigceM1FDT6UFUbTW1Dp8ai262w5PJlvxC');
define('NONCE_KEY',        'TsTPHqGyLlW96yMQRXjezkcL0gVZpmo7JjYL4hkgJ8HP45IafRFcyU8dQnHdnBTs');
define('AUTH_SALT',        'tjoxHzBGODDofJ7SK9aW3vK5XSOHm5xJhhnaihiGuvH4DbPvT2cjAU4qrdET1hZg');
define('SECURE_AUTH_SALT', 'IlumQ7XgzLoUI0F2Lpbx6fxD0CaoBuOAMlAG2fwyr7lX4pTHNEp8i6t9AlPcS9vY');
define('LOGGED_IN_SALT',   'sTzULOdcOkccxipEXlJKlt7B3Nu7vI1Yk4rFWsRqLsShCUIjOaOIC3vCsQ4NGOSO');
define('NONCE_SALT',       'zjhcD900F6XNdU2fx5XKPA8y3PmJOL08GIEFjOB9WNEjXnKIVSYYYjRmP6fbJzAg');

/**
 * Other customizations.
 */
define('FS_METHOD','direct');define('FS_CHMOD_DIR',0755);define('FS_CHMOD_FILE',0644);
define('WP_TEMP_DIR',dirname(__FILE__).'/wp-content/uploads');

/**
 * Turn off automatic updates since these are managed upstream.
 */
define('AUTOMATIC_UPDATER_DISABLED', true);


/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 's8wi_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
