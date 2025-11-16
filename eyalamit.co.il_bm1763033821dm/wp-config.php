<?php
define('WP_CACHE', true); // Added by WP Rocket
define("WP_DEBUG", false);
define( 'DISALLOW_FILE_EDIT', true );
define('WP_MEMORY_LIMIT', '512M');
define('WP_MAX_MEMORY_LIMIT', '512M');

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
/** Helper to prefer environment variables when זמינים */
function eyal_env_or_default($key, $default) {
	if (function_exists('getenv')) {
		$value = getenv($key);
		if ($value !== false && $value !== '') {
			return $value;
		}
	}
	return $default;
}

/** The name of the database for WordPress */
define('DB_NAME', eyal_env_or_default('DB_NAME', 'deveyala_uprdb'));

/** MySQL database username */
define('DB_USER', eyal_env_or_default('DB_USER', 'deveyala_uprdb'));

/** MySQL database password */
define('DB_PASSWORD', eyal_env_or_default('DB_PASSWORD', 'h9@p7%rDy2ghCf0G'));

/** MySQL hostname */
define('DB_HOST', eyal_env_or_default('DB_HOST', 'localhost'));

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
define('AUTH_KEY',         '0ssZO[z~bYti^BKjo0)ClE `|>gFM$|1=7(nPj}XVAKusV:g:LVE@XDMzEy/}?I]');
define('SECURE_AUTH_KEY',  '3A%}vwV$t.rLy&l0tAo^=RJ_z4{wRe.{1Gh*VwZ&TT}|J3r<iTze.z8u1XO)cPbc');
define('LOGGED_IN_KEY',    'GIEZM694-Y>8FMUk4O0s)Ud ;?DxdE:z=l.?%xCl$O!)6bKE2`!)RxLCkB ;=Oup');
define('NONCE_KEY',        '(z1.k:7M4y<2m4t?/v<]Zl}9 MENQpK}`HpzQA|i{!j{F{Q0lJ/*a=6wK=W_@]8}');
define('AUTH_SALT',        'Qj(a_ivvj`3w<aSpnW#T`>*bu/^V+e4o%;D2:4ldF_+)#e`d/4/*[W.|,nQO5J*^');
define('SECURE_AUTH_SALT', 'j/^[e[g_6biHUN@pEw%A/y,97F^.jm,msFUqA<(j0nm,ftWKg0yt/buHi_)iu,z^');
define('LOGGED_IN_SALT',   '7|N)6X_*7o[OAQDEnKlp5e6%+r*b,PI60TlP26y]!pMPSbLlmV,?uXA*c6mWUsuH');
define('NONCE_SALT',       'yt_WaN3+1TSr><F==ZR_R.rq>lkxMHb0+dB|CDiCFg YLs]{Kp:H}wm@F*_F}GZ)');

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
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */

/* That's all, stop editing! Happy blogging. */
/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
