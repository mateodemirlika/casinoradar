<?php
/**
 * Plugin Name:       WagerWise Core
 * Description:       Custom post types, taxonomies, meta fields, dynamic blocks, and the global settings page that power the WagerWise casino affiliate site. No third-party field builder (ACF) dependency — everything uses native WordPress APIs.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            WagerWise
 * Text Domain:       wagerwise
 */

defined( 'ABSPATH' ) || exit;

define( 'WAGERWISE_CORE_FILE', __FILE__ );
define( 'WAGERWISE_CORE_VERSION', '1.0.0' );
define( 'WAGERWISE_CORE_DIR', __DIR__ );

require_once WAGERWISE_CORE_DIR . '/includes/post-types.php';
require_once WAGERWISE_CORE_DIR . '/includes/taxonomies.php';
require_once WAGERWISE_CORE_DIR . '/includes/meta-boxes.php';
require_once WAGERWISE_CORE_DIR . '/includes/settings-page.php';
require_once WAGERWISE_CORE_DIR . '/includes/helpers.php';
require_once WAGERWISE_CORE_DIR . '/includes/blocks.php';
require_once WAGERWISE_CORE_DIR . '/includes/polylang.php';
require_once WAGERWISE_CORE_DIR . '/includes/forms.php';
require_once WAGERWISE_CORE_DIR . '/includes/reviews.php';
require_once WAGERWISE_CORE_DIR . '/includes/sports-betting.php';

register_activation_hook( __FILE__, 'wagerwise_core_activate' );

function wagerwise_core_activate(): void {
	wagerwise_register_post_types();
	wagerwise_register_taxonomies();
	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, function () {
	flush_rewrite_rules();
} );
