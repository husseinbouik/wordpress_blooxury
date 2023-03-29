<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'vitrine' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',         ',=_c6gSidjzwEj[Z,*K8a_j!A*yhy+Vs?ERZ-IpXC 4: poxY|#L:/L19F*Z%]GJ' );
define( 'SECURE_AUTH_KEY',  '?]Dn=z?Vl=>L8>Zox1SNfO|dU[<>l)*wlxvrVNkxzP;!IAJ`o1l8PkVjM|.BE79d' );
define( 'LOGGED_IN_KEY',    'eZA] XfT5=2_N2,z ?0o%V0Cqf~F#kEh5xI%dz{k]ph:2ZeLrxH?/F#jl^%W.y`}' );
define( 'NONCE_KEY',        '>!<>eaQFix.d&}@{<?H<Yxtupxdh;_PC{<ZbtAHDw8e=M@[Gdf3z!5UJW/W2Jx@I' );
define( 'AUTH_SALT',        '%_+NnF~>.)^$v.5_le|!cTI_ L2?us@+bR0p+M`Y:NSxho6T#A>M?2.7]Btvp*3E' );
define( 'SECURE_AUTH_SALT', 'i[uSU>1_s@Uv[/MqR2L yitu)!Eeut}t1;U#mti,GG$T8[QlB$C *^6K7D<Y+*6l' );
define( 'LOGGED_IN_SALT',   '6!5I~JhTve-{YGub$3q,0?^&CQ{b#a+dJ[[p.2+ES~~8+OT-R(qV2A5(VbHXWeW|' );
define( 'NONCE_SALT',       ':L}7.6|2r2NZprL!BJEQ)rLK4|IfdIDFM<E0G% -wpR +HlFF4XQWLQHaMn3E#lP' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
