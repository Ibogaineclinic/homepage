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
define('DB_NAME', 'admin_42hackme42');

/** MySQL database username */
define('DB_USER', 'admin_mehack42');

/** MySQL database password */
define('DB_PASSWORD', 'UkgxsQ^oH4O%');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'H[SSfKm=fuwS1Mgg5fdIAjw,,%]6k8IYCVDw)>l5<OH(ixFdxg{2dN/BWdJq(1+n');
define('SECURE_AUTH_KEY',  '7aq@CFn?S#!KQ:pIT+wAwV7KBE~ cuU )kdp}np|A2_AA3f)4dQaQtee.#]WL#V/');
define('LOGGED_IN_KEY',    'E>I+yZd8tu:m{?36E=!GZWG3]}Y+;H8U6v4k dQF[&A-!e+Zj`J0my^7W= ijH+@');
define('NONCE_KEY',        'T~`,&Zd!H9aBxt;*3YCln/0N,xWr(3R?@@Tq4O39hTng(dya/BWf!eb$=jomJA-T');
define('AUTH_SALT',        '<Z_zZM*Pp4und[&2@}yzjz5`Buw@ygF,|Hch0IH*G9H,60usOm?VKdOw?_NOiOd-');
define('SECURE_AUTH_SALT', '[6r?&v)~DvHIIjTlW@$7bH,5%L+MnV]/9NGuPSR_/H`u[Jf0>PQFx`$@{ofSW9Z;');
define('LOGGED_IN_SALT',   '82wM,k7(B6?a~?dpOps~KPR!IRzq/bEI@*VpS, t2FHK7jGv)-MB&x*?lkMbE#N0');
define('NONCE_SALT',       'JuY/76_7R7E;7t]K(qD=WI9XN-;@_fmz/RyRkpWXZ;4CE[tp3Y7etkCw3Ntbt a3');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
