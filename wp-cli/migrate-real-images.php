<?php
/**
 * One-time migration: replace the GD-generated flat-colour placeholder
 * images (from the original seed) with real, contextually-relevant photos
 * (keyword-matched via LoremFlickr) for existing casino/game/guide/news/
 * tournament posts. Run via `wp eval-file wp-cli/migrate-real-images.php`.
 *
 * Safe to re-run: only touches posts whose current thumbnail slug doesn't
 * already look like a migrated one.
 */

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

function ww_migrate_keywords( string $term ): string {
	static $map = array(
		'Crypto Casinos'       => 'bitcoin,cryptocurrency',
		'Live Dealer'          => 'casino,dealer',
		'High Roller'          => 'casino,luxury',
		'Mobile Casinos'       => 'smartphone,casino',
		'New Casinos'          => 'casino,neon',
		'Slots'                => 'slotmachine',
		'Roulette'             => 'roulette',
		'Blackjack'            => 'blackjack,cards',
		'Video Poker'          => 'poker,cards',
		'Live Games'           => 'casino,dealer',
		'Bingo'                => 'bingo',
		'Beginner Guides'      => 'education,book',
		'Bankroll Management'  => 'money,finance',
		'Game Strategy'        => 'chess,strategy',
		'Bonus Terms'          => 'contract,document',
		'Country Guides'       => 'map,travel',
		'Industry'             => 'business,office',
		'Product Launch'       => 'technology,startup',
		'Responsible Gambling' => 'support,help',
		'Regulation'           => 'law,government',
		'Slot Race'            => 'slotmachine,race',
		'Table Game Ladder'    => 'poker,cards',
		'Free Roll'            => 'casino,chips',
		'Loyalty Leaderboard'  => 'trophy,vip',
	);
	return $map[ $term ] ?? 'casino';
}

/** Downloads to a throwaway slug first so a failed fetch never destroys the existing image. */
function ww_migrate_download( string $label, string $keywords, string $lock_key ): int {
	$url = 'https://loremflickr.com/800/600/' . rawurlencode( $keywords ) . '?lock=' . abs( crc32( $lock_key ) );
	$tmp = download_url( $url, 15 );
	if ( is_wp_error( $tmp ) ) {
		WP_CLI::warning( "download failed for '{$label}': " . $tmp->get_error_message() );
		return 0;
	}
	$attach_id = media_handle_sideload( array( 'name' => sanitize_title( $label ) . '-' . uniqid() . '.jpg', 'tmp_name' => $tmp ), 0, $label );
	if ( is_wp_error( $attach_id ) ) {
		@unlink( $tmp );
		WP_CLI::warning( "sideload failed for '{$label}': " . $attach_id->get_error_message() );
		return 0;
	}
	return (int) $attach_id;
}

function ww_migrate_post_type( string $post_type, string $taxonomy, string $slug_suffix ): void {
	$posts = get_posts( array(
		'post_type'      => $post_type,
		'posts_per_page' => -1,
		'suppress_filters' => true,
	) );

	$seen = array();
	foreach ( $posts as $post ) {
		$lang = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $post->ID ) : 'en';
		if ( 'en' !== $lang ) {
			continue;
		}
		if ( isset( $seen[ $post->ID ] ) ) {
			continue;
		}
		$seen[ $post->ID ] = true;

		$old_thumb = get_post_thumbnail_id( $post->ID );
		if ( $old_thumb && get_post_meta( $old_thumb, '_ww_real_photo', true ) ) {
			continue; // already migrated
		}

		$terms     = get_the_terms( $post->ID, $taxonomy );
		$term_name = ( is_array( $terms ) && ! empty( $terms ) ) ? $terms[0]->name : '';
		$keywords  = ww_migrate_keywords( $term_name );
		// Matches the slug scheme ww_seed_contextual_image() in seed.php looks
		// for, so a future `make seed` recognizes this as already present.
		$canonical_slug = sanitize_title( $post->post_title ) . $slug_suffix;

		$new_thumb = ww_migrate_download( $post->post_title, $keywords, $canonical_slug );
		if ( ! $new_thumb ) {
			continue; // leave the old image in place; don't destroy anything on a failed download
		}

		if ( $old_thumb ) {
			wp_delete_attachment( $old_thumb, true );
		}
		wp_update_post( array( 'ID' => $new_thumb, 'post_name' => $canonical_slug ) );
		update_post_meta( $new_thumb, '_wp_attachment_image_alt', $post->post_title );
		update_post_meta( $new_thumb, '_ww_real_photo', 1 );

		$translations = function_exists( 'pll_get_post_translations' )
			? pll_get_post_translations( $post->ID )
			: array( 'en' => $post->ID );
		foreach ( $translations as $tid ) {
			if ( $tid ) {
				set_post_thumbnail( $tid, $new_thumb );
			}
		}

		WP_CLI::log( "{$post_type} #{$post->ID} \"{$post->post_title}\": new image #{$new_thumb} (keywords: {$keywords})" );
	}
}

ww_migrate_post_type( 'casino', 'casino_category', '-logo' );
ww_migrate_post_type( 'game', 'game_category', '-thumb' );
ww_migrate_post_type( 'guide', 'guide_category', '-thumb' );
ww_migrate_post_type( 'news', 'news_category', '-thumb' );
ww_migrate_post_type( 'tournament', 'tournament_type', '-thumb' );

WP_CLI::success( 'Real contextual images migrated.' );
