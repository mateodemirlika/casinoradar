<?php
/**
 * Registers our custom post types/taxonomies as translatable with Polylang.
 * Uses Polylang's documented filters so this works automatically without an
 * admin needing to tick boxes in Languages → Settings.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'pll_get_post_types', function ( array $post_types, bool $is_settings ): array {
	if ( ! $is_settings ) {
		$post_types['casino']           = 'casino';
		$post_types['bonus']            = 'bonus';
		$post_types['game']             = 'game';
		$post_types['tournament']       = 'tournament';
		$post_types['complaint']        = 'complaint';
		$post_types['guide']            = 'guide';
		$post_types['review']           = 'review';
		$post_types['news']             = 'news';
		$post_types['sportsbook']       = 'sportsbook';
		$post_types['sportsbook_bonus'] = 'sportsbook_bonus';
	}
	return $post_types;
}, 10, 2 );

add_filter( 'pll_get_taxonomies', function ( array $taxonomies, bool $is_settings ): array {
	if ( ! $is_settings ) {
		foreach ( array( 'casino_category', 'software_provider', 'payment_method', 'bonus_type', 'licence', 'game_category', 'country', 'guide_category', 'news_category', 'review_category', 'tournament_type', 'complaint_issue_type' ) as $tax ) {
			$taxonomies[ $tax ] = $tax;
		}
	}
	return $taxonomies;
}, 10, 2 );
