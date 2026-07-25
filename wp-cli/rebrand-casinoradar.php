<?php
/**
 * One-time migration: rebrand existing DB content from "WagerWise" to
 * "CasinoRadar" (the visible brand name only — theme/plugin folder names,
 * function prefixes, and text domains are untouched on purpose).
 * Run via `wp eval-file wp-cli/rebrand-casinoradar.php`.
 */

defined( 'ABSPATH' ) || exit;

function ww_rebrand_replace( string $text ): string {
	return str_replace( 'WagerWise', 'CasinoRadar', $text );
}

// --- Options -----------------------------------------------------------
update_option( 'blogname', ww_rebrand_replace( get_option( 'blogname' ) ) );
$disclaimer = get_option( 'ww_site_disclaimer' );
if ( $disclaimer ) {
	update_option( 'ww_site_disclaimer', ww_rebrand_replace( $disclaimer ) );
}
WP_CLI::log( 'blogname -> ' . get_option( 'blogname' ) );

// --- All post content (title/content/excerpt) across every post type ---
$post_types = get_post_types( array( 'public' => true ), 'names' );
$updated    = 0;
foreach ( $post_types as $post_type ) {
	$posts = get_posts( array(
		'post_type'      => $post_type,
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'suppress_filters' => true,
	) );
	foreach ( $posts as $post ) {
		$new_title   = ww_rebrand_replace( $post->post_title );
		$new_content = ww_rebrand_replace( $post->post_content );
		$new_excerpt = ww_rebrand_replace( $post->post_excerpt );
		if ( $new_title !== $post->post_title || $new_content !== $post->post_content || $new_excerpt !== $post->post_excerpt ) {
			wp_update_post( array(
				'ID'           => $post->ID,
				'post_title'   => $new_title,
				'post_content' => $new_content,
				'post_excerpt' => $new_excerpt,
			) );
			$updated++;
			WP_CLI::log( "updated {$post_type} #{$post->ID}: {$new_title}" );
		}
	}
}
WP_CLI::log( "Rebranded content in {$updated} post(s)." );

// --- Polylang string translation for the footer copyright line ---------
if ( class_exists( 'PLL_MO' ) && function_exists( 'pll_register_string' ) ) {
	$footer_strings = array(
		'© 2026 CasinoRadar. 18+. Play responsibly.' => array(
			'de' => '© 2026 CasinoRadar. 18+. Bitte verantwortungsvoll spielen.',
			'zh' => '© 2026 CasinoRadar。18岁以上。请理性博彩。',
		),
	);
	foreach ( $footer_strings as $en_string => $translations ) {
		pll_register_string( 'wagerwise-' . sanitize_title( $en_string ), $en_string, 'WagerWise', false );
	}
	foreach ( array( 'de', 'zh' ) as $lang_slug ) {
		$language = PLL()->model->get_language( $lang_slug );
		if ( ! $language ) {
			continue;
		}
		$mo = new PLL_MO();
		$mo->import_from_db( $language );
		foreach ( $footer_strings as $en_string => $translations ) {
			if ( ! empty( $translations[ $lang_slug ] ) ) {
				$mo->add_entry( $mo->make_entry( $en_string, $translations[ $lang_slug ] ) );
			}
		}
		$mo->export_to_db( $language );
	}
	WP_CLI::log( 'Footer copyright string translation updated for de/zh.' );
}

WP_CLI::success( 'Rebrand to CasinoRadar complete.' );
