<?php
/**
 * Taxonomies shared across the casino/bonus/game post types.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'wagerwise_register_taxonomies' );

function wagerwise_register_taxonomies(): void {

	register_taxonomy(
		'casino_category',
		array( 'casino' ),
		array(
			'labels'       => array( 'name' => __( 'Casino Categories', 'wagerwise' ), 'singular_name' => __( 'Casino Category', 'wagerwise' ) ),
			'hierarchical' => true,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'casino-category' ),
		)
	);

	register_taxonomy(
		'software_provider',
		array( 'casino', 'game' ),
		array(
			'labels'       => array( 'name' => __( 'Software Providers', 'wagerwise' ), 'singular_name' => __( 'Software Provider', 'wagerwise' ) ),
			'hierarchical' => true,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'provider' ),
		)
	);

	register_taxonomy(
		'payment_method',
		array( 'casino' ),
		array(
			'labels'       => array( 'name' => __( 'Payment Methods', 'wagerwise' ), 'singular_name' => __( 'Payment Method', 'wagerwise' ) ),
			'hierarchical' => false,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'payment-method' ),
		)
	);

	register_taxonomy(
		'bonus_type',
		array( 'bonus' ),
		array(
			'labels'       => array( 'name' => __( 'Bonus Types', 'wagerwise' ), 'singular_name' => __( 'Bonus Type', 'wagerwise' ) ),
			'hierarchical' => true,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'bonus-type' ),
		)
	);

	register_taxonomy(
		'licence',
		array( 'casino' ),
		array(
			'labels'       => array( 'name' => __( 'Licences', 'wagerwise' ), 'singular_name' => __( 'Licence', 'wagerwise' ) ),
			'hierarchical' => false,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'licence' ),
		)
	);

	register_taxonomy(
		'game_category',
		array( 'game' ),
		array(
			'labels'       => array( 'name' => __( 'Game Categories', 'wagerwise' ), 'singular_name' => __( 'Game Category', 'wagerwise' ) ),
			'hierarchical' => true,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'game-category' ),
		)
	);

	register_taxonomy(
		'country',
		array( 'casino' ),
		array(
			'labels'       => array( 'name' => __( 'Countries', 'wagerwise' ), 'singular_name' => __( 'Country', 'wagerwise' ) ),
			'hierarchical' => false,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'country' ),
		)
	);

	register_taxonomy(
		'guide_category',
		array( 'guide' ),
		array(
			'labels'       => array( 'name' => __( 'Guide Categories', 'wagerwise' ), 'singular_name' => __( 'Guide Category', 'wagerwise' ) ),
			'hierarchical' => true,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'guide-category' ),
		)
	);

	register_taxonomy(
		'news_category',
		array( 'news' ),
		array(
			'labels'       => array( 'name' => __( 'News Categories', 'wagerwise' ), 'singular_name' => __( 'News Category', 'wagerwise' ) ),
			'hierarchical' => true,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'news-category' ),
		)
	);

	register_taxonomy(
		'review_category',
		array( 'review' ),
		array(
			'labels'       => array( 'name' => __( 'Review Categories', 'wagerwise' ), 'singular_name' => __( 'Review Category', 'wagerwise' ) ),
			'hierarchical' => true,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'review-category' ),
		)
	);

	register_taxonomy(
		'tournament_type',
		array( 'tournament' ),
		array(
			'labels'       => array( 'name' => __( 'Tournament Types', 'wagerwise' ), 'singular_name' => __( 'Tournament Type', 'wagerwise' ) ),
			'hierarchical' => true,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'tournament-type' ),
		)
	);

	register_taxonomy(
		'complaint_issue_type',
		array( 'complaint' ),
		array(
			'labels'       => array( 'name' => __( 'Issue Types', 'wagerwise' ), 'singular_name' => __( 'Issue Type', 'wagerwise' ) ),
			'hierarchical' => false,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'issue-type' ),
		)
	);
}
