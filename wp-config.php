<?php
/**
 * WagerWise wp-config.php — 12-factor style: every value comes from the
 * container environment (.env), so this single file works unmodified in
 * both local dev and production.
 */

function ww_env( string $key, $default = null ) {
	$value = getenv( $key );
	return $value === false ? $default : $value;
}

// --- Database ---------------------------------------------------------------
define( 'DB_NAME', ww_env( 'DB_NAME' ) );
define( 'DB_USER', ww_env( 'DB_USER' ) );
define( 'DB_PASSWORD', ww_env( 'DB_PASSWORD' ) );
define( 'DB_HOST', ww_env( 'DB_HOST', 'db' ) );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );
$table_prefix = ww_env( 'WP_TABLE_PREFIX', 'wp_' );

// --- Auth salts ---------------------------------------------------------------
define( 'AUTH_KEY', ww_env( 'AUTH_KEY' ) );
define( 'SECURE_AUTH_KEY', ww_env( 'SECURE_AUTH_KEY' ) );
define( 'LOGGED_IN_KEY', ww_env( 'LOGGED_IN_KEY' ) );
define( 'NONCE_KEY', ww_env( 'NONCE_KEY' ) );
define( 'AUTH_SALT', ww_env( 'AUTH_SALT' ) );
define( 'SECURE_AUTH_SALT', ww_env( 'SECURE_AUTH_SALT' ) );
define( 'LOGGED_IN_SALT', ww_env( 'LOGGED_IN_SALT' ) );
define( 'NONCE_SALT', ww_env( 'NONCE_SALT' ) );

// --- Environment --------------------------------------------------------------
define( 'WP_ENVIRONMENT_TYPE', ww_env( 'WP_ENV', 'local' ) );
$is_production = WP_ENVIRONMENT_TYPE === 'production';

define( 'WP_HOME', ww_env( 'WP_HOME' ) );
define( 'WP_SITEURL', ww_env( 'WP_SITEURL' ) );

define( 'WP_DEBUG', ! $is_production );
define( 'WP_DEBUG_LOG', ! $is_production );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', ! $is_production );

// Reverse-proxy / TLS awareness (nginx terminates TLS in front of php-fpm)
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
	$_SERVER['HTTPS'] = 'on';
}
define( 'FORCE_SSL_ADMIN', $is_production );

// --- Security hardening --------------------------------------------------------
define( 'DISALLOW_FILE_EDIT', true );
define( 'DISALLOW_FILE_MODS', false ); // allow plugin/theme installs from wp-admin
define( 'WP_AUTO_UPDATE_CORE', 'minor' );

// --- Object cache (Redis, production only — plugin no-ops if unreachable) ------
if ( $is_production ) {
	define( 'WP_REDIS_HOST', ww_env( 'REDIS_HOST', 'redis' ) );
	define( 'WP_CACHE', true );
}

// --- SEO --------------------------------------------------------------------
// Without this, Rank Math's ENTIRE frontend output (titles, meta description,
// canonical, OG/Twitter tags) silently no-ops on every request — its
// Frontend class refuses to instantiate unless the site has been "connected"
// to a Rank Math account, which a self-hosted/agency site never does.
define( 'RANK_MATH_REGISTRATION_SKIP', true );

// --- Misc -----------------------------------------------------------------------
define( 'WP_POST_REVISIONS', 10 );
define( 'AUTOSAVE_INTERVAL', 120 );
define( 'MEDIA_TRASH', true );
define( 'WP_MEMORY_LIMIT', '256M' );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
