<?php
/**
 * Custom post types: casino, bonus, game.
 * Blog content intentionally uses the native `post` type.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'wagerwise_register_post_types' );

function wagerwise_register_post_types(): void {

	register_post_type(
		'casino',
		array(
			'labels'       => array(
				'name'          => __( 'Casinos', 'wagerwise' ),
				'singular_name' => __( 'Casino', 'wagerwise' ),
				'add_new_item'  => __( 'Add New Casino', 'wagerwise' ),
				'edit_item'     => __( 'Edit Casino', 'wagerwise' ),
				'all_items'     => __( 'All Casinos', 'wagerwise' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-money-alt',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			'rewrite'      => array( 'slug' => 'casinos' ),
		)
	);

	register_post_type(
		'bonus',
		array(
			'labels'       => array(
				'name'          => __( 'Bonuses', 'wagerwise' ),
				'singular_name' => __( 'Bonus', 'wagerwise' ),
				'add_new_item'  => __( 'Add New Bonus', 'wagerwise' ),
				'edit_item'     => __( 'Edit Bonus', 'wagerwise' ),
				'all_items'     => __( 'All Bonuses', 'wagerwise' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-tickets-alt',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'revisions' ),
			'rewrite'      => array( 'slug' => 'bonuses' ),
		)
	);

	register_post_type(
		'game',
		array(
			'labels'       => array(
				'name'          => __( 'Games', 'wagerwise' ),
				'singular_name' => __( 'Game', 'wagerwise' ),
				'add_new_item'  => __( 'Add New Game', 'wagerwise' ),
				'edit_item'     => __( 'Edit Game', 'wagerwise' ),
				'all_items'     => __( 'All Games', 'wagerwise' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-games',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'revisions' ),
			'rewrite'      => array( 'slug' => 'games' ),
		)
	);

	register_post_type(
		'tournament',
		array(
			'labels'       => array(
				'name'          => __( 'Tournaments', 'wagerwise' ),
				'singular_name' => __( 'Tournament', 'wagerwise' ),
				'add_new_item'  => __( 'Add New Tournament', 'wagerwise' ),
				'edit_item'     => __( 'Edit Tournament', 'wagerwise' ),
				'all_items'     => __( 'All Tournaments', 'wagerwise' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-awards',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'revisions' ),
			'rewrite'      => array( 'slug' => 'tournaments' ),
		)
	);

	register_post_type(
		'complaint',
		array(
			'labels'       => array(
				'name'          => __( 'Complaints', 'wagerwise' ),
				'singular_name' => __( 'Complaint', 'wagerwise' ),
				'add_new_item'  => __( 'Add New Complaint', 'wagerwise' ),
				'edit_item'     => __( 'Edit Complaint', 'wagerwise' ),
				'all_items'     => __( 'All Complaints', 'wagerwise' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-shield',
			'supports'     => array( 'title', 'revisions' ),
			'rewrite'      => array( 'slug' => 'complaint' ),
		)
	);

	/**
	 * Guide / Review / News — editorial content, each surfaced through its
	 * existing composed Page (/guide/, /reviews/, /news/) rather than a CPT
	 * archive, matching how Complaint already works. "Review" here is a
	 * staff-written long-form casino review; the star-rating comment system
	 * on the casino CPT (see includes/reviews.php) is a separate, unrelated
	 * "Player Reviews" feature and is unaffected by this CPT.
	 */
	register_post_type(
		'guide',
		array(
			'labels'       => array(
				'name'          => __( 'Guides', 'wagerwise' ),
				'singular_name' => __( 'Guide', 'wagerwise' ),
				'add_new_item'  => __( 'Add New Guide', 'wagerwise' ),
				'edit_item'     => __( 'Edit Guide', 'wagerwise' ),
				'all_items'     => __( 'All Guides', 'wagerwise' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-book-alt',
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
			'rewrite'      => array( 'slug' => 'guide' ),
		)
	);

	register_post_type(
		'review',
		array(
			'labels'       => array(
				'name'          => __( 'Reviews', 'wagerwise' ),
				'singular_name' => __( 'Review', 'wagerwise' ),
				'add_new_item'  => __( 'Add New Review', 'wagerwise' ),
				'edit_item'     => __( 'Edit Review', 'wagerwise' ),
				'all_items'     => __( 'All Reviews', 'wagerwise' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-star-half',
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
			'rewrite'      => array( 'slug' => 'review' ),
		)
	);

	register_post_type(
		'news',
		array(
			'labels'       => array(
				'name'          => __( 'News', 'wagerwise' ),
				'singular_name' => __( 'News Item', 'wagerwise' ),
				'add_new_item'  => __( 'Add New News Item', 'wagerwise' ),
				'edit_item'     => __( 'Edit News Item', 'wagerwise' ),
				'all_items'     => __( 'All News', 'wagerwise' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-megaphone',
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
			'rewrite'      => array( 'slug' => 'news' ),
		)
	);
}

/**
 * Meta fields, keyed by post type. Drives both register_post_meta() and the
 * meta box renderer in meta-boxes.php — single source of truth so the two
 * never drift apart.
 */
function wagerwise_meta_schema(): array {
	return array(
		'casino' => array(
			'ww_rating'            => array( 'type' => 'number', 'label' => __( 'Rating (0–5)', 'wagerwise' ), 'field' => 'number', 'step' => '0.1', 'min' => 0, 'max' => 5 ),
			'ww_year_established'  => array( 'type' => 'integer', 'label' => __( 'Year Established', 'wagerwise' ), 'field' => 'number' ),
			'ww_min_deposit'       => array( 'type' => 'string', 'label' => __( 'Minimum Deposit', 'wagerwise' ), 'field' => 'text' ),
			'ww_wagering_requirement' => array( 'type' => 'string', 'label' => __( 'Wagering Requirement', 'wagerwise' ), 'field' => 'text' ),
			'ww_payout_speed'      => array( 'type' => 'string', 'label' => __( 'Payout Speed', 'wagerwise' ), 'field' => 'text' ),
			'ww_affiliate_link'    => array( 'type' => 'string', 'label' => __( 'Affiliate Link', 'wagerwise' ), 'field' => 'url' ),
			'ww_cta_label'         => array( 'type' => 'string', 'label' => __( 'CTA Button Label', 'wagerwise' ), 'field' => 'text', 'default' => __( 'Play Now', 'wagerwise' ) ),
			'ww_featured'          => array( 'type' => 'boolean', 'label' => __( 'Featured / Top Pick', 'wagerwise' ), 'field' => 'checkbox' ),
			'ww_pros'              => array( 'type' => 'array', 'label' => __( 'Pros', 'wagerwise' ), 'field' => 'repeater' ),
			'ww_cons'              => array( 'type' => 'array', 'label' => __( 'Cons', 'wagerwise' ), 'field' => 'repeater' ),
		),
		'bonus'  => array(
			'ww_related_casino'  => array( 'type' => 'integer', 'label' => __( 'Casino', 'wagerwise' ), 'field' => 'post_select', 'post_type' => 'casino' ),
			'ww_bonus_value'     => array( 'type' => 'string', 'label' => __( 'Bonus Value', 'wagerwise' ), 'field' => 'text' ),
			'ww_promo_code'      => array( 'type' => 'string', 'label' => __( 'Promo Code', 'wagerwise' ), 'field' => 'text' ),
			'ww_terms_summary'   => array( 'type' => 'string', 'label' => __( 'Terms Summary', 'wagerwise' ), 'field' => 'textarea' ),
			'ww_expiry_date'     => array( 'type' => 'string', 'label' => __( 'Expiry Date', 'wagerwise' ), 'field' => 'date' ),
			'ww_affiliate_link'  => array( 'type' => 'string', 'label' => __( 'Affiliate Link', 'wagerwise' ), 'field' => 'url' ),
			'ww_cta_label'       => array( 'type' => 'string', 'label' => __( 'CTA Button Label', 'wagerwise' ), 'field' => 'text', 'default' => __( 'Claim Bonus', 'wagerwise' ) ),
		),
		'game'   => array(
			'ww_rtp'            => array( 'type' => 'string', 'label' => __( 'RTP %', 'wagerwise' ), 'field' => 'text' ),
			'ww_min_bet'        => array( 'type' => 'string', 'label' => __( 'Min Bet', 'wagerwise' ), 'field' => 'text' ),
			'ww_max_win'        => array( 'type' => 'string', 'label' => __( 'Max Win', 'wagerwise' ), 'field' => 'text' ),
			'ww_related_casino' => array( 'type' => 'integer', 'label' => __( 'Play At Casino', 'wagerwise' ), 'field' => 'post_select', 'post_type' => 'casino' ),
			'ww_demo_link'      => array( 'type' => 'string', 'label' => __( 'Play Link', 'wagerwise' ), 'field' => 'url' ),
		),
		'tournament' => array(
			'ww_related_casino' => array( 'type' => 'integer', 'label' => __( 'Casino', 'wagerwise' ), 'field' => 'post_select', 'post_type' => 'casino' ),
			'ww_prize_pool'     => array( 'type' => 'string', 'label' => __( 'Prize Pool', 'wagerwise' ), 'field' => 'text' ),
			'ww_entries'        => array( 'type' => 'string', 'label' => __( 'Entries', 'wagerwise' ), 'field' => 'text' ),
			'ww_status_label'   => array( 'type' => 'string', 'label' => __( 'Status (e.g. "Ends in 2d 14h")', 'wagerwise' ), 'field' => 'text' ),
			'ww_leaderboard'    => array( 'type' => 'array', 'label' => __( 'Leaderboard (one row per line: Rank, Player, Points, Prize)', 'wagerwise' ), 'field' => 'repeater' ),
			'ww_affiliate_link' => array( 'type' => 'string', 'label' => __( 'Affiliate Link', 'wagerwise' ), 'field' => 'url' ),
			'ww_cta_label'      => array( 'type' => 'string', 'label' => __( 'CTA Button Label', 'wagerwise' ), 'field' => 'text', 'default' => __( 'Join Tournament', 'wagerwise' ) ),
		),
		'complaint' => array(
			'ww_case_number'      => array( 'type' => 'string', 'label' => __( 'Case Number', 'wagerwise' ), 'field' => 'text' ),
			'ww_related_casino'   => array( 'type' => 'integer', 'label' => __( 'Casino', 'wagerwise' ), 'field' => 'post_select', 'post_type' => 'casino' ),
			'ww_issue_type'       => array( 'type' => 'string', 'label' => __( 'Issue Type', 'wagerwise' ), 'field' => 'text' ),
			'ww_disputed_amount'  => array( 'type' => 'string', 'label' => __( 'Amount in Dispute', 'wagerwise' ), 'field' => 'text' ),
			'ww_amount_recovered' => array( 'type' => 'number', 'label' => __( 'Amount Recovered (USD, numeric)', 'wagerwise' ), 'field' => 'number' ),
			'ww_filed_date'       => array( 'type' => 'string', 'label' => __( 'Filed Date', 'wagerwise' ), 'field' => 'date' ),
			'ww_resolved_date'    => array( 'type' => 'string', 'label' => __( 'Resolved Date', 'wagerwise' ), 'field' => 'date' ),
			'ww_summary'          => array( 'type' => 'string', 'label' => __( 'Summary', 'wagerwise' ), 'field' => 'textarea' ),
			'ww_outcome'          => array( 'type' => 'string', 'label' => __( 'Outcome (e.g. "Full refund")', 'wagerwise' ), 'field' => 'text' ),
		),
		'review' => array(
			'ww_related_casino' => array( 'type' => 'integer', 'label' => __( 'Casino', 'wagerwise' ), 'field' => 'post_select', 'post_type' => 'casino' ),
			'ww_rating'         => array( 'type' => 'number', 'label' => __( 'Rating (0–5)', 'wagerwise' ), 'field' => 'number', 'step' => '0.1', 'min' => 0, 'max' => 5 ),
			'ww_verdict'        => array( 'type' => 'string', 'label' => __( 'Verdict (one line)', 'wagerwise' ), 'field' => 'text' ),
			'ww_pros'           => array( 'type' => 'array', 'label' => __( 'Pros', 'wagerwise' ), 'field' => 'repeater' ),
			'ww_cons'           => array( 'type' => 'array', 'label' => __( 'Cons', 'wagerwise' ), 'field' => 'repeater' ),
		),
	);
}

add_action( 'init', 'wagerwise_register_post_meta' );

function wagerwise_register_post_meta(): void {
	foreach ( wagerwise_meta_schema() as $post_type => $fields ) {
		foreach ( $fields as $key => $spec ) {
			register_post_meta(
				$post_type,
				$key,
				array(
					'type'          => $spec['type'],
					'single'        => true,
					'show_in_rest'  => 'array' === $spec['type']
						? array( 'schema' => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ) )
						: true,
					'auth_callback' => function() {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}
}
