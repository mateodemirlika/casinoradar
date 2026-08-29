<?php
/**
 * Sports Betting: a self-contained addition mirroring the casino/bonus
 * architecture (custom post types + the shared meta-schema/meta-box system
 * + dynamic blocks), kept in its own file so the existing casino/bonus/game
 * code in post-types.php/blocks.php never needs to be restructured.
 *
 * `sportsbook` is deliberately ONE combined entity (operator profile +
 * long-form review in a single post) rather than mirroring casino+review as
 * two linked CPTs — every field the "Sportsbook Reviews" content type needs
 * (logo, rating, bonus, pros/cons, license, payment methods, sports,
 * affiliate/terms URLs, review content) lives on one post, so "Visit Site"
 * uses its affiliate link and "Read Review" is just its own permalink.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'wagerwise_sb_register_post_types' );

function wagerwise_sb_register_post_types(): void {

	register_post_type(
		'sportsbook',
		array(
			'labels'       => array(
				'name'          => __( 'Sportsbooks', 'wagerwise' ),
				'singular_name' => __( 'Sportsbook', 'wagerwise' ),
				'add_new_item'  => __( 'Add New Sportsbook', 'wagerwise' ),
				'edit_item'     => __( 'Edit Sportsbook', 'wagerwise' ),
				'all_items'     => __( 'All Sportsbooks', 'wagerwise' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-flag',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			// Nested rewrite base (not just 'sportsbook') so both the archive
			// AND single permalinks land under /sports-betting/reviews/ as
			// requested — e.g. /sports-betting/reviews/bet365/ — without a
			// separate "Reviews" Page needing to claim that same path.
			'rewrite'      => array( 'slug' => 'sports-betting/reviews' ),
		)
	);

	register_post_type(
		'sportsbook_bonus',
		array(
			'labels'       => array(
				'name'          => __( 'Sportsbook Bonuses', 'wagerwise' ),
				'singular_name' => __( 'Sportsbook Bonus', 'wagerwise' ),
				'add_new_item'  => __( 'Add New Sportsbook Bonus', 'wagerwise' ),
				'edit_item'     => __( 'Edit Sportsbook Bonus', 'wagerwise' ),
				'all_items'     => __( 'All Sportsbook Bonuses', 'wagerwise' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-tickets-alt',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'revisions' ),
			'rewrite'      => array( 'slug' => 'sports-betting/bonuses' ),
		)
	);
}

/**
 * Adds this section's fields to the shared meta schema (post-types.php's
 * wagerwise_meta_schema() is filterable specifically for this) — the
 * existing generic meta-box renderer/saver and REST meta registration then
 * pick these up automatically, no admin UI code duplicated here.
 */
add_filter( 'wagerwise_meta_schema', 'wagerwise_sb_meta_schema' );

function wagerwise_sb_meta_schema( array $schema ): array {
	$schema['sportsbook'] = array(
		'ww_rating'             => array( 'type' => 'number', 'label' => __( 'Rating (0–5)', 'wagerwise' ), 'field' => 'number', 'step' => '0.1', 'min' => 0, 'max' => 5 ),
		'ww_short_description'  => array( 'type' => 'string', 'label' => __( 'Short Description (for cards)', 'wagerwise' ), 'field' => 'textarea' ),
		'ww_bonus_value'        => array( 'type' => 'string', 'label' => __( 'Welcome Bonus', 'wagerwise' ), 'field' => 'text' ),
		'ww_pros'               => array( 'type' => 'array', 'label' => __( 'Pros', 'wagerwise' ), 'field' => 'repeater' ),
		'ww_cons'               => array( 'type' => 'array', 'label' => __( 'Cons', 'wagerwise' ), 'field' => 'repeater' ),
		'ww_payment_methods'    => array( 'type' => 'array', 'label' => __( 'Payment Methods', 'wagerwise' ), 'field' => 'repeater' ),
		'ww_sports_available'   => array( 'type' => 'array', 'label' => __( 'Sports Available', 'wagerwise' ), 'field' => 'repeater' ),
		'ww_license'            => array( 'type' => 'string', 'label' => __( 'License', 'wagerwise' ), 'field' => 'text' ),
		'ww_affiliate_link'     => array( 'type' => 'string', 'label' => __( 'Affiliate Link', 'wagerwise' ), 'field' => 'url' ),
		'ww_terms_url'          => array( 'type' => 'string', 'label' => __( 'Terms URL', 'wagerwise' ), 'field' => 'url' ),
		'ww_cta_label'          => array( 'type' => 'string', 'label' => __( 'CTA Button Label', 'wagerwise' ), 'field' => 'text', 'default' => __( 'Visit Site', 'wagerwise' ) ),
	);

	$schema['sportsbook_bonus'] = array(
		'ww_related_sportsbook' => array( 'type' => 'integer', 'label' => __( 'Sportsbook', 'wagerwise' ), 'field' => 'post_select', 'post_type' => 'sportsbook' ),
		'ww_bonus_value'        => array( 'type' => 'string', 'label' => __( 'Bonus Value', 'wagerwise' ), 'field' => 'text' ),
		'ww_promo_code'         => array( 'type' => 'string', 'label' => __( 'Promo Code', 'wagerwise' ), 'field' => 'text' ),
		'ww_terms_summary'      => array( 'type' => 'string', 'label' => __( 'Terms Summary', 'wagerwise' ), 'field' => 'textarea' ),
		'ww_expiry_date'        => array( 'type' => 'string', 'label' => __( 'Expiry Date', 'wagerwise' ), 'field' => 'date' ),
		'ww_affiliate_link'     => array( 'type' => 'string', 'label' => __( 'Affiliate Link', 'wagerwise' ), 'field' => 'url' ),
		'ww_cta_label'          => array( 'type' => 'string', 'label' => __( 'CTA Button Label', 'wagerwise' ), 'field' => 'text', 'default' => __( 'Claim Bonus', 'wagerwise' ) ),
	);

	return $schema;
}

/**
 * New CPT rewrite rules need one flush to take effect. Doing it here (rather
 * than editing wagerwise-core.php's activation hook) keeps this addition
 * fully self-contained; guarded by an option so it only runs once.
 */
add_action( 'init', 'wagerwise_sb_maybe_flush_rewrites', 20 );

function wagerwise_sb_maybe_flush_rewrites(): void {
	if ( '1' !== get_option( 'wagerwise_sb_rewrites_flushed' ) ) {
		flush_rewrite_rules();
		update_option( 'wagerwise_sb_rewrites_flushed', '1' );
	}
}

// --- Dynamic blocks ---------------------------------------------------------

add_action( 'init', 'wagerwise_sb_register_blocks' );

function wagerwise_sb_register_blocks(): void {
	$blocks = array(
		'sportsbook-grid'        => array(
			'render_callback' => 'wagerwise_sb_render_block_sportsbook_grid',
			'attributes'      => array(
				'number' => array( 'type' => 'number', 'default' => 10 ),
			),
		),
		'sportsbook-review-grid' => array(
			'render_callback' => 'wagerwise_sb_render_block_review_grid',
			'attributes'      => array(
				'number' => array( 'type' => 'number', 'default' => 12 ),
			),
		),
		'sportsbook-hero'        => array(
			'render_callback' => 'wagerwise_sb_render_block_hero',
			'attributes'      => array(),
		),
		'sportsbook-bonus-grid'  => array(
			'render_callback' => 'wagerwise_sb_render_block_bonus_grid',
			'attributes'      => array(
				'number' => array( 'type' => 'number', 'default' => 12 ),
			),
		),
		'sportsbook-bonus-hero'  => array(
			'render_callback' => 'wagerwise_sb_render_block_bonus_hero',
			'attributes'      => array(),
		),
	);

	foreach ( $blocks as $name => $config ) {
		register_block_type(
			'wagerwise/' . $name,
			array(
				'attributes'      => $config['attributes'],
				'render_callback' => $config['render_callback'],
			)
		);
	}
}

/**
 * "Best Sports Betting Sites" cards for the /sports-betting/ landing page —
 * reuses the exact same card classes/helpers as wagerwise_render_casino_cards()
 * (blocks.php) so it needs zero new CSS: logo fallback badge, star rating,
 * pros/cons, and CTA button are all the shared helpers from helpers.php.
 */
function wagerwise_sb_render_block_sportsbook_grid( array $attrs ): string {
	$args = array(
		'post_type'      => 'sportsbook',
		'posts_per_page' => $attrs['number'] ?? 10,
		'meta_key'       => 'ww_rating',
		'orderby'        => array( 'meta_value_num' => 'DESC', 'title' => 'ASC' ),
	);
	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language();
		if ( $lang ) {
			$args['lang'] = $lang;
		}
	}
	$sportsbooks = get_posts( $args );
	if ( empty( $sportsbooks ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="ww-top-casinos ww-layout-grid">
		<?php foreach ( $sportsbooks as $i => $sb ) :
			$rating      = (float) get_post_meta( $sb->ID, 'ww_rating', true );
			$description = get_post_meta( $sb->ID, 'ww_short_description', true );
			$bonus       = get_post_meta( $sb->ID, 'ww_bonus_value', true );
			$link        = get_post_meta( $sb->ID, 'ww_affiliate_link', true );
			$cta         = get_post_meta( $sb->ID, 'ww_cta_label', true ) ?: __( 'Visit Site', 'wagerwise' );
			$rank        = $i + 1;
			$rank_class  = 1 === $rank ? '' : ( 2 === $rank ? ' ww-rank--2' : ' ww-rank--other' );
			?>
			<div class="ww-casino-card">
				<span class="ww-rank<?php echo esc_attr( $rank_class ); ?>">#<?php echo (int) $rank; ?></span>
				<a class="ww-casino-card__logo" href="<?php echo esc_url( get_permalink( $sb ) ); ?>">
					<?php echo wagerwise_casino_logo_html( $sb->ID, 'casino-logo' ); ?>
				</a>
				<div class="ww-casino-card__body">
					<a class="ww-casino-card__name" href="<?php echo esc_url( get_permalink( $sb ) ); ?>"><?php echo esc_html( get_the_title( $sb ) ); ?></a>
					<?php echo wagerwise_star_rating_html( $rating ); ?>
					<?php if ( $description ) : ?>
						<p class="ww-casino-card__meta"><?php echo esc_html( $description ); ?></p>
					<?php endif; ?>
					<?php if ( $bonus ) : ?>
						<p class="ww-casino-card__bonus"><?php echo esc_html( $bonus ); ?></p>
					<?php endif; ?>
				</div>
				<div class="ww-sportsbook-card__ctas">
					<?php echo wagerwise_cta_button_html( $link, $cta ); ?>
					<a class="ww-btn ww-btn--ghost ww-btn--small" href="<?php echo esc_url( get_permalink( $sb ) ); ?>"><?php esc_html_e( 'Read Review', 'wagerwise' ); ?></a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/** Compact review list — mirrors wagerwise_render_block_review_grid() in blocks.php. */
function wagerwise_sb_render_block_review_grid( array $attrs ): string {
	$args = array(
		'post_type'      => 'sportsbook',
		'posts_per_page' => $attrs['number'] ?? 12,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);
	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language();
		if ( $lang ) {
			$args['lang'] = $lang;
		}
	}
	$sportsbooks = get_posts( $args );
	if ( empty( $sportsbooks ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="ww-review-grid">
		<?php foreach ( $sportsbooks as $sb ) :
			$rating = (float) get_post_meta( $sb->ID, 'ww_rating', true );
			$bonus  = get_post_meta( $sb->ID, 'ww_bonus_value', true );
			?>
			<a class="ww-review-grid-card" href="<?php echo esc_url( get_permalink( $sb ) ); ?>">
				<?php $thumb = get_the_post_thumbnail( $sb, 'medium_large' ); ?>
				<?php if ( $thumb ) : ?>
					<?php echo $thumb; ?>
				<?php endif; ?>
				<div class="ww-review-grid-card__body">
					<h3><?php echo esc_html( get_the_title( $sb ) ); ?></h3>
					<?php if ( $rating ) : ?><?php echo wagerwise_star_rating_html( $rating ); ?><?php endif; ?>
					<?php if ( $bonus ) : ?><p class="ww-review-grid-card__verdict"><?php echo esc_html( $bonus ); ?></p><?php endif; ?>
				</div>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/** Single-sportsbook header — mirrors wagerwise_render_block_casino_hero(). */
function wagerwise_sb_render_block_hero(): string {
	$post_id = get_the_ID();
	if ( ! $post_id || 'sportsbook' !== get_post_type( $post_id ) ) {
		return '';
	}

	$rating   = (float) get_post_meta( $post_id, 'ww_rating', true );
	$bonus    = get_post_meta( $post_id, 'ww_bonus_value', true );
	$license  = get_post_meta( $post_id, 'ww_license', true );
	$sports   = (array) get_post_meta( $post_id, 'ww_sports_available', true );
	$payments = (array) get_post_meta( $post_id, 'ww_payment_methods', true );
	$link     = get_post_meta( $post_id, 'ww_affiliate_link', true );
	$cta      = get_post_meta( $post_id, 'ww_cta_label', true ) ?: __( 'Visit Site', 'wagerwise' );

	ob_start();
	?>
	<div class="ww-casino-hero">
		<div class="ww-casino-hero__logo"><?php echo wagerwise_casino_logo_html( $post_id, 'casino-logo' ); ?></div>
		<div class="ww-casino-hero__body">
			<h1><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
			<?php echo wagerwise_star_rating_html( $rating ); ?>
			<ul class="ww-casino-hero__facts">
				<?php if ( $bonus ) : ?><li><?php esc_html_e( 'Welcome Bonus', 'wagerwise' ); ?>: <?php echo esc_html( $bonus ); ?></li><?php endif; ?>
				<?php if ( $license ) : ?><li><?php esc_html_e( 'License', 'wagerwise' ); ?>: <?php echo esc_html( $license ); ?></li><?php endif; ?>
			</ul>
			<?php if ( ! empty( $sports ) ) : ?>
				<div class="ww-sportsbook-tags">
					<?php foreach ( $sports as $sport ) : ?>
						<span class="ww-badge ww-sportsbook-tag"><?php echo esc_html( $sport ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $payments ) ) : ?>
				<p class="ww-sportsbook-payments"><?php esc_html_e( 'Payment Methods', 'wagerwise' ); ?>: <?php echo esc_html( implode( ', ', $payments ) ); ?></p>
			<?php endif; ?>
		</div>
		<div class="ww-casino-hero__cta"><?php echo wagerwise_cta_button_html( $link, $cta, 'ww-btn ww-btn--primary ww-btn--large' ); ?></div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/** Bonus grid — mirrors wagerwise_render_block_bonus_grid() in blocks.php, for sportsbook_bonus. */
function wagerwise_sb_render_block_bonus_grid( array $attrs ): string {
	$args = array(
		'post_type'      => 'sportsbook_bonus',
		'posts_per_page' => $attrs['number'] ?? 12,
	);
	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language();
		if ( $lang ) {
			$args['lang'] = $lang;
		}
	}
	$bonuses = get_posts( $args );
	if ( empty( $bonuses ) ) {
		return '';
	}
	ob_start();
	?>
	<div class="ww-bonus-grid">
		<?php foreach ( $bonuses as $bonus ) :
			$sportsbook_id = (int) get_post_meta( $bonus->ID, 'ww_related_sportsbook', true );
			$value         = get_post_meta( $bonus->ID, 'ww_bonus_value', true );
			$code          = get_post_meta( $bonus->ID, 'ww_promo_code', true );
			$link          = get_post_meta( $bonus->ID, 'ww_affiliate_link', true );
			$cta           = get_post_meta( $bonus->ID, 'ww_cta_label', true ) ?: __( 'Claim Bonus', 'wagerwise' );
			?>
			<div class="ww-bonus-card">
				<p class="ww-bonus-card__value"><?php echo esc_html( $value ); ?></p>
				<h4><?php echo esc_html( get_the_title( $bonus ) ); ?></h4>
				<?php if ( $sportsbook_id ) : ?>
					<p class="ww-bonus-card__casino"><?php echo esc_html( get_the_title( $sportsbook_id ) ); ?></p>
				<?php endif; ?>
				<?php if ( $code ) : ?>
					<p class="ww-bonus-card__code"><?php esc_html_e( 'Code:', 'wagerwise' ); ?> <code><?php echo esc_html( $code ); ?></code></p>
				<?php endif; ?>
				<?php echo wagerwise_cta_button_html( $link, $cta ); ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/** Single-bonus header — mirrors wagerwise_render_block_bonus_hero() in blocks.php. */
function wagerwise_sb_render_block_bonus_hero(): string {
	$post_id = get_the_ID();
	if ( ! $post_id || 'sportsbook_bonus' !== get_post_type( $post_id ) ) {
		return '';
	}

	$sportsbook_id = (int) get_post_meta( $post_id, 'ww_related_sportsbook', true );
	$value         = get_post_meta( $post_id, 'ww_bonus_value', true );
	$code          = get_post_meta( $post_id, 'ww_promo_code', true );
	$terms         = get_post_meta( $post_id, 'ww_terms_summary', true );
	$expiry        = get_post_meta( $post_id, 'ww_expiry_date', true );
	$link          = get_post_meta( $post_id, 'ww_affiliate_link', true );
	$cta           = get_post_meta( $post_id, 'ww_cta_label', true ) ?: __( 'Claim Bonus', 'wagerwise' );

	ob_start();
	?>
	<div class="ww-bonus-hero">
		<?php if ( $sportsbook_id ) : ?>
			<div class="ww-bonus-hero__logo"><?php echo wagerwise_casino_logo_html( $sportsbook_id, 'casino-logo' ); ?></div>
		<?php endif; ?>
		<div class="ww-bonus-hero__body">
			<h1><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
			<p class="ww-bonus-hero__value"><?php echo esc_html( $value ); ?></p>
			<?php if ( $code ) : ?><p class="ww-bonus-hero__code"><?php esc_html_e( 'Code:', 'wagerwise' ); ?> <code><?php echo esc_html( $code ); ?></code></p><?php endif; ?>
			<?php if ( $terms ) : ?><p class="ww-bonus-hero__terms"><?php echo esc_html( $terms ); ?></p><?php endif; ?>
			<?php if ( $expiry ) : ?><p class="ww-bonus-hero__expiry"><?php esc_html_e( 'Expires', 'wagerwise' ); ?>: <?php echo esc_html( $expiry ); ?></p><?php endif; ?>
		</div>
		<div class="ww-bonus-hero__cta"><?php echo wagerwise_cta_button_html( $link, $cta, 'ww-btn ww-btn--primary ww-btn--large' ); ?></div>
	</div>
	<?php
	return (string) ob_get_clean();
}
