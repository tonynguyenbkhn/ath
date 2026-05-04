<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', '2026_ath' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost:6606' );

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
define( 'AUTH_KEY',         'e4Gns~}5uZ,7OvHa?pjk,#<<b+*bwH<<E:~1Ha9P[b?pWMMBMsF7^l]%ro7%A]JT' );
define( 'SECURE_AUTH_KEY',  ',#rXi-]%e[+Rdmja^MmjvdZOR! eKH@sa+Rac}k#ulICe-D7yLdQ)_PKl[jV9?qG' );
define( 'LOGGED_IN_KEY',    ',O9[](P23,s.^tK2GP48kqiH<G8^C`@vhc0|-~CN8{!D:Ae8+4S@!_r[}LFV>*O=' );
define( 'NONCE_KEY',        'TBtWyx6-CzNxWUQ`LHAnIK,L,ZtGSeKI+k b&F}n ]8e<5w{yw#B&).bB^-M^/ W' );
define( 'AUTH_SALT',        'mhOVnR?uP{=,kjuI1=t9*2?@9*YEx`(FM5T~ROq]oVgauQlEo.nNRuLlzTUW1i8C' );
define( 'SECURE_AUTH_SALT', 'MEbdc1a~c;{N{vJUll^d&.5>tr6#U[$cF V>xlRwPlYtg_8I6A9,3Aux;!9iz]mo' );
define( 'LOGGED_IN_SALT',   '+~uV?lC{lHC)j(Og:?GFgx2@R}Iz/ak*,H@ADN:Nl%JDe/L$3sU!52>#5R)6cfij' );
define( 'NONCE_SALT',       'VpK&+R}MFE0B6{-@9-s{j^7YbkP!izv 2]!1Vc,(tx-@/)~rAN$]p+g$9p!LfcvG' );

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
$table_prefix = 'ath_nvv_';

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
define( 'WP_DEBUG', false );
// define( 'WP_DEBUG_LOG', true );
/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
