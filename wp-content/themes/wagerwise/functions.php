<?php
/**
 * WagerWise theme bootstrap.
 */

defined( 'ABSPATH' ) || exit;

define( 'WAGERWISE_THEME_VERSION', '1.0.0' );
define( 'WAGERWISE_THEME_DIR', get_template_directory() );
define( 'WAGERWISE_THEME_URI', get_template_directory_uri() );

require_once WAGERWISE_THEME_DIR . '/inc/setup.php';
require_once WAGERWISE_THEME_DIR . '/inc/enqueue.php';
require_once WAGERWISE_THEME_DIR . '/inc/template-tags.php';
require_once WAGERWISE_THEME_DIR . '/inc/patterns.php';
