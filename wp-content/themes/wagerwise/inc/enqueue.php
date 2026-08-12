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

	wp_add_inline_script(
		'wagerwise-main',
		'var wagerwiseI18n = ' . wp_json_encode( array(
			'filterEmpty' => wagerwise_pll__( 'No featured picks in this category yet.' ),
		) ) . ';',
		'before'
	);
}

/**
 * Extra favicon/PWA tags WordPress's own wp_site_icon() doesn't cover: a
 * 48x16 and 96x96 icon (core only emits 32x32 + 192x192), a scalable SVG
 * icon, a web app manifest (192x192 + 512x512 icons, required for Android
 * "add to home screen"), and the iOS home-screen label. The actual
 * /favicon.ico file is served as a static asset directly by nginx (see
 * nginx/prod/templates/wagerwise.conf.template) rather than through PHP —
 * it's still explicitly <link>ed below, though, since that file only
 * existing at the implicit /favicon.ico convention (no <link> pointing to
 * it anywhere in <head>) is exactly what search engines/crawlers checking
 * the page source for a declared icon won't find.
 *
 * The small icon is 48x48, not the more common 16x16 — a favicon checker
 * flagged a genuine 16x16 file as "under recommended 48x48" (some contexts,
 * e.g. Windows taskbar pins, request that size hint at higher DPI than its
 * label implies), so this serves a real 48x48 image under that sizes
 * attribute rather than a literal 16-pixel one.
 */
add_action( 'wp_head', 'wagerwise_extra_favicon_tags', 5 );
function wagerwise_extra_favicon_tags(): void {
	$images_uri = WAGERWISE_THEME_URI . '/assets/images';
	printf( '<link rel="icon" href="%s" sizes="32x32" />' . "\n", esc_url( $images_uri . '/favicon.ico' ) );
	printf( '<link rel="icon" href="%s/favicon.svg" type="image/svg+xml" />' . "\n", esc_url( $images_uri ) );
	printf( '<link rel="icon" href="%s/favicon-48x48.png" sizes="48x48" type="image/png" />' . "\n", esc_url( $images_uri ) );
	printf( '<link rel="icon" href="%s/favicon-96x96.png" sizes="96x96" type="image/png" />' . "\n", esc_url( $images_uri ) );
	printf( '<link rel="manifest" href="%s/site.webmanifest" />' . "\n", esc_url( $images_uri ) );
	echo '<meta name="theme-color" content="#12121e" />' . "\n";
	echo '<meta name="apple-mobile-web-app-title" content="CasinoRadar" />' . "\n";
}

/**
 * Core's own wp_site_icon() (hooked on wp_head independently of the above)
 * always emits a 32x32 <link rel="icon">, generated on the fly from the
 * uploaded site icon — the same "under recommended 48x48" flag as above,
 * but this one can't be fixed by swapping a file: get_site_icon_url(32)
 * ties the requested crop size to the sizes attribute it prints, so there's
 * no way to serve a larger image under that tag without core lying about
 * its own size. Our 48x48 (above) and core's own 192x192 already cover that
 * range, so this just drops the redundant undersized one.
 */
add_filter( 'site_icon_meta_tags', 'wagerwise_remove_undersized_site_icon_tag' );
function wagerwise_remove_undersized_site_icon_tag( array $meta_tags ): array {
	return array_filter(
		$meta_tags,
		static fn( string $tag ): bool => ! str_contains( $tag, 'sizes="32x32"' )
	);
}
