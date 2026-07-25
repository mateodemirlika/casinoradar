<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'wagerwise_enqueue_assets' );

function wagerwise_enqueue_assets(): void {
	wp_enqueue_style(
		'wagerwise-google-fonts',
		'https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Work+Sans:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	// style.css itself stays empty (theme header only) — real styles live in
	// assets/css so they can be organized into multiple files without WP's
	// "style.css must contain the theme header" constraint getting in the way.
	wp_enqueue_style( 'wagerwise-style', get_stylesheet_uri(), array(), WAGERWISE_THEME_VERSION );

	$css_path = WAGERWISE_THEME_DIR . '/assets/css/main.css';
	wp_enqueue_style(
		'wagerwise-main',
		WAGERWISE_THEME_URI . '/assets/css/main.css',
		array( 'wagerwise-style' ),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : WAGERWISE_THEME_VERSION
	);

	$js_path = WAGERWISE_THEME_DIR . '/assets/js/main.js';
	wp_enqueue_script(
		'wagerwise-main',
		WAGERWISE_THEME_URI . '/assets/js/main.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : WAGERWISE_THEME_VERSION,
		true
	);

	if ( function_exists( 'pll_current_language' ) ) {
		wp_add_inline_script( 'wagerwise-main', 'document.documentElement.lang = ' . wp_json_encode( pll_current_language() ) . ';', 'after' );
	}
}
