<?php
/**
 * Custom dynamic Gutenberg blocks — the free, native replacement for ACF
 * Flexible Content. Editors compose pages by inserting/reordering these
 * blocks; each one renders live data via its PHP render_callback.
 *
 * No JS build step: the editor-side registration (assets/blocks.js) is
 * plain wp.element/ServerSideRender code, enqueued as-is.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'wagerwise_register_blocks' );

function wagerwise_register_blocks(): void {
	$blocks = array(
		'top-casinos'             => array(
			'render_callback' => 'wagerwise_render_block_top_casinos',
			'attributes'      => array(
				'number'       => array( 'type' => 'number', 'default' => 5 ),
				'categoryId'   => array( 'type' => 'number', 'default' => 0 ),
				'featuredOnly' => array( 'type' => 'boolean', 'default' => false ),
				'layout'       => array( 'type' => 'string', 'default' => 'list' ),
			),
		),
		'casino-comparison-table' => array(
			'render_callback' => 'wagerwise_render_block_comparison_table',
			'attributes'      => array(
				'number'     => array( 'type' => 'number', 'default' => 5 ),
				'categoryId' => array( 'type' => 'number', 'default' => 0 ),
			),
		),
		'bonus-grid'              => array(
			'render_callback' => 'wagerwise_render_block_bonus_grid',
			'attributes'      => array(
				'number'      => array( 'type' => 'number', 'default' => 6 ),
				'bonusTypeId' => array( 'type' => 'number', 'default' => 0 ),
			),
		),
		'pros-cons'               => array(
			'render_callback' => 'wagerwise_render_block_pros_cons',
			'attributes'      => array(),
		),
		'casino-hero'             => array(
			'render_callback' => 'wagerwise_render_block_casino_hero',
			'attributes'      => array(),
		),
		'bonus-hero'              => array(
			'render_callback' => 'wagerwise_render_block_bonus_hero',
			'attributes'      => array(),
		),
		'cta-button'              => array(
			'render_callback' => 'wagerwise_render_block_cta_button',
			'attributes'      => array(
				'url'   => array( 'type' => 'string', 'default' => '' ),
				'label' => array( 'type' => 'string', 'default' => 'Play Now' ),
			),
		),
		'blog-grid'               => array(
			'render_callback' => 'wagerwise_render_block_blog_grid',
			'attributes'      => array(
				'number'     => array( 'type' => 'number', 'default' => 3 ),
				'categoryId' => array( 'type' => 'number', 'default' => 0 ),
			),
		),
		'hero-search'             => array(
			'render_callback' => 'wagerwise_render_block_hero_search',
			'attributes'      => array(
				'heading'    => array( 'type' => 'string', 'default' => '' ),
				'subheading' => array( 'type' => 'string', 'default' => '' ),
			),
		),
		'stats-strip'             => array(
			'render_callback' => 'wagerwise_render_block_stats_strip',
			'attributes'      => array(),
		),
		'provider-strip'          => array(
			'render_callback' => 'wagerwise_render_block_provider_strip',
			'attributes'      => array(
				'number' => array( 'type' => 'number', 'default' => 8 ),
			),
		),
		'category-strip'          => array(
			'render_callback' => 'wagerwise_render_block_category_strip',
			'attributes'      => array(
				'taxonomy' => array( 'type' => 'string', 'default' => 'casino_category' ),
				'style'    => array( 'type' => 'string', 'default' => 'chip' ),
			),
		),
		'game-grid'               => array(
			'render_callback' => 'wagerwise_render_block_game_grid',
			'attributes'      => array(
				'number'     => array( 'type' => 'number', 'default' => 8 ),
				'categoryId' => array( 'type' => 'number', 'default' => 0 ),
				'providerId' => array( 'type' => 'number', 'default' => 0 ),
			),
		),
		'game-hero'               => array(
			'render_callback' => 'wagerwise_render_block_game_hero',
			'attributes'      => array(),
		),
		'taxonomy-results'        => array(
			'render_callback' => 'wagerwise_render_block_taxonomy_results',
			'attributes'      => array(),
		),
		'site-nav'                => array(
			'render_callback' => 'wagerwise_render_block_site_nav',
			'attributes'      => array(),
		),
		'list-meta'               => array(
			'render_callback' => 'wagerwise_render_block_list_meta',
			'attributes'      => array(
				'postType' => array( 'type' => 'string', 'default' => 'casino' ),
				'sortLabel' => array( 'type' => 'string', 'default' => 'Rating (high to low)' ),
			),
		),
		'footer-links'            => array(
			'render_callback' => 'wagerwise_render_block_footer_links',
			'attributes'      => array(),
		),
		'tournament-grid'         => array(
			'render_callback' => 'wagerwise_render_block_tournament_grid',
			'attributes'      => array(
				'number' => array( 'type' => 'number', 'default' => 3 ),
			),
		),
		'tournament-leaderboard'  => array(
			'render_callback' => 'wagerwise_render_block_tournament_leaderboard',
			'attributes'      => array(),
		),
		'news-featured'           => array(
			'render_callback' => 'wagerwise_render_block_news_featured',
			'attributes'      => array(
				'category' => array( 'type' => 'string', 'default' => '' ),
			),
		),
		'news-grid'               => array(
			'render_callback' => 'wagerwise_render_block_news_grid',
			'attributes'      => array(
				'number'   => array( 'type' => 'number', 'default' => 6 ),
				'category' => array( 'type' => 'string', 'default' => '' ),
				'skipFirst' => array( 'type' => 'boolean', 'default' => false ),
			),
		),
		'guide-featured'          => array(
			'render_callback' => 'wagerwise_render_block_guide_featured',
			'attributes'      => array(),
		),
		'guide-list'              => array(
			'render_callback' => 'wagerwise_render_block_guide_list',
			'attributes'      => array(
				'number' => array( 'type' => 'number', 'default' => 8 ),
			),
		),
		'reviews-list'            => array(
			'render_callback' => 'wagerwise_render_block_reviews_list',
			'attributes'      => array(
				'number' => array( 'type' => 'number', 'default' => 12 ),
			),
		),
		'review-grid'             => array(
			'render_callback' => 'wagerwise_render_block_review_grid',
			'attributes'      => array(
				'number' => array( 'type' => 'number', 'default' => 6 ),
			),
		),
		'complaints-stats'        => array(
			'render_callback' => 'wagerwise_render_block_complaints_stats',
			'attributes'      => array(),
		),
		'complaints-list'         => array(
			'render_callback' => 'wagerwise_render_block_complaints_list',
			'attributes'      => array(
				'number' => array( 'type' => 'number', 'default' => 6 ),
			),
		),
		'complaint-form'          => array(
			'render_callback' => 'wagerwise_render_block_complaint_form',
			'attributes'      => array(),
		),
		// Thin wrappers so theme-owned presentational functions (which echo
		// directly) can be placed inside FSE .html template parts, which can
		// only contain block markup, never raw PHP.
		'language-switcher'       => array(
			'render_callback' => fn() => wagerwise_capture( 'wagerwise_language_switcher' ),
			'attributes'      => array(),
		),
		'disclaimer-bar'          => array(
			'render_callback' => fn() => wagerwise_capture( 'wagerwise_disclaimer_bar' ),
			'attributes'      => array(),
		),
		'footer-social-links'     => array(
			'render_callback' => fn() => wagerwise_capture( 'wagerwise_footer_social_links' ),
			'attributes'      => array(),
		),
		'review-meta'             => array(
			'render_callback' => fn() => wagerwise_capture( 'wagerwise_review_meta' ),
			'attributes'      => array(),
		),
		'join-free-button'        => array(
			'render_callback' => fn() => wagerwise_capture( 'wagerwise_join_free_button' ),
			'attributes'      => array(),
		),
		'footer-copyright'        => array(
			'render_callback' => fn() => wagerwise_capture( 'wagerwise_footer_copyright' ),
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

add_action( 'enqueue_block_editor_assets', 'wagerwise_enqueue_block_editor_assets' );

function wagerwise_enqueue_block_editor_assets(): void {
	wp_enqueue_script(
		'wagerwise-blocks-editor',
		plugins_url( 'assets/blocks.js', WAGERWISE_CORE_FILE ),
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-server-side-render', 'wp-components', 'wp-i18n' ),
		WAGERWISE_CORE_VERSION,
		true
	);
}

function wagerwise_capture( callable $fn ): string {
	if ( ! is_callable( $fn ) ) {
		return '';
	}
	ob_start();
	$fn();
	return (string) ob_get_clean();
}

// --- Render callbacks ---------------------------------------------------------

/**
 * On a `casino_category` archive, honor the term being browsed unless the
 * block explicitly set a categoryId in the editor — lets archive-casino.html
 * reuse a single block for both the main "/casinos/" archive and any
 * "/casino-category/<slug>/" term archive.
 */
function wagerwise_resolve_category_id( array $attrs ): ?int {
	if ( ! empty( $attrs['categoryId'] ) ) {
		return (int) $attrs['categoryId'];
	}
	if ( is_tax( 'casino_category' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			return $term->term_id;
		}
	}
	return null;
}

function wagerwise_render_casino_cards( array $casinos, string $layout = 'grid' ): string {
	if ( empty( $casinos ) ) {
		return '';
	}
	ob_start();
	?>
	<div class="ww-top-casinos ww-layout-<?php echo esc_attr( $layout ); ?>">
		<?php foreach ( $casinos as $i => $casino ) :
			$rating    = (float) get_post_meta( $casino->ID, 'ww_rating', true );
			$link      = get_post_meta( $casino->ID, 'ww_affiliate_link', true );
			$cta       = get_post_meta( $casino->ID, 'ww_cta_label', true ) ?: __( 'Visit Casino', 'wagerwise' );
			$cat_terms = get_the_terms( $casino, 'casino_category' );
			$cat_name  = ( is_array( $cat_terms ) && ! empty( $cat_terms ) ) ? $cat_terms[0]->name : '';
			$cat_slugs = ( is_array( $cat_terms ) && ! empty( $cat_terms ) ) ? implode( ' ', wp_list_pluck( $cat_terms, 'slug' ) ) : '';
			$bonus     = wagerwise_get_first_bonus_for_casino( $casino->ID );
			?>
			<div class="ww-casino-card" data-casino-categories="<?php echo esc_attr( $cat_slugs ); ?>">
				<span class="ww-rank">#<?php echo (int) $i + 1; ?></span>
				<a class="ww-casino-card__logo" href="<?php echo esc_url( get_permalink( $casino ) ); ?>">
					<?php echo get_the_post_thumbnail( $casino, 'medium' ); ?>
				</a>
				<div class="ww-casino-card__body">
					<a class="ww-casino-card__name" href="<?php echo esc_url( get_permalink( $casino ) ); ?>"><?php echo esc_html( get_the_title( $casino ) ); ?></a>
					<p class="ww-casino-card__meta">★ <?php echo esc_html( number_format( $rating, 1 ) ); ?><?php echo $cat_name ? ' · ' . esc_html( $cat_name ) : ''; ?></p>
					<?php if ( $bonus ) : ?>
						<p class="ww-casino-card__bonus"><?php echo esc_html( get_post_meta( $bonus->ID, 'ww_bonus_value', true ) ); ?></p>
					<?php endif; ?>
				</div>
				<?php echo wagerwise_cta_button_html( $link, $cta ); ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

function wagerwise_render_block_top_casinos( array $attrs ): string {
	$casinos = wagerwise_get_top_casinos( $attrs['number'] ?? 5, wagerwise_resolve_category_id( $attrs ), ! empty( $attrs['featuredOnly'] ) );
	return wagerwise_render_casino_cards( $casinos, $attrs['layout'] ?? 'list' );
}

function wagerwise_render_block_comparison_table( array $attrs ): string {
	$casinos = wagerwise_get_top_casinos( $attrs['number'] ?? 5, wagerwise_resolve_category_id( $attrs ) );
	if ( empty( $casinos ) ) {
		return '';
	}
	ob_start();
	?>
	<table class="ww-comparison-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Casino', 'wagerwise' ); ?></th>
				<th><?php esc_html_e( 'Rating', 'wagerwise' ); ?></th>
				<th><?php esc_html_e( 'Payout Speed', 'wagerwise' ); ?></th>
				<th><?php esc_html_e( 'Min. Deposit', 'wagerwise' ); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $casinos as $casino ) :
			$rating = (float) get_post_meta( $casino->ID, 'ww_rating', true );
			$link   = get_post_meta( $casino->ID, 'ww_affiliate_link', true );
			$cta    = get_post_meta( $casino->ID, 'ww_cta_label', true ) ?: __( 'Play Now', 'wagerwise' );
			?>
			<tr>
				<td><a href="<?php echo esc_url( get_permalink( $casino ) ); ?>"><?php echo esc_html( get_the_title( $casino ) ); ?></a></td>
				<td><?php echo wagerwise_star_rating_html( $rating ); ?></td>
				<td><?php echo esc_html( get_post_meta( $casino->ID, 'ww_payout_speed', true ) ); ?></td>
				<td><?php echo esc_html( get_post_meta( $casino->ID, 'ww_min_deposit', true ) ); ?></td>
				<td><?php echo wagerwise_cta_button_html( $link, $cta, 'ww-btn ww-btn--small' ); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php
	return (string) ob_get_clean();
}

function wagerwise_render_block_bonus_grid( array $attrs ): string {
	$args = array(
		'post_type'      => 'bonus',
		'posts_per_page' => $attrs['number'] ?? 6,
	);

	$bonus_type_id = $attrs['bonusTypeId'] ?? 0;
	if ( empty( $bonus_type_id ) && is_tax( 'bonus_type' ) ) {
		$term          = get_queried_object();
		$bonus_type_id = $term instanceof WP_Term ? $term->term_id : 0;
	}
	if ( ! empty( $bonus_type_id ) ) {
		$args['tax_query'] = array( array( 'taxonomy' => 'bonus_type', 'field' => 'term_id', 'terms' => $bonus_type_id ) );
	}
	$bonuses = get_posts( $args );
	if ( empty( $bonuses ) ) {
		return '';
	}
	ob_start();
	?>
	<div class="ww-bonus-grid">
		<?php foreach ( $bonuses as $bonus ) :
			$casino_id  = (int) get_post_meta( $bonus->ID, 'ww_related_casino', true );
			$value      = get_post_meta( $bonus->ID, 'ww_bonus_value', true );
			$code       = get_post_meta( $bonus->ID, 'ww_promo_code', true );
			$link       = get_post_meta( $bonus->ID, 'ww_affiliate_link', true );
			$cta        = get_post_meta( $bonus->ID, 'ww_cta_label', true ) ?: __( 'Claim Bonus', 'wagerwise' );
			$type_terms = get_the_terms( $bonus, 'bonus_type' );
			$type_label = ( is_array( $type_terms ) && ! empty( $type_terms ) ) ? $type_terms[0]->name : '';
			$type_slugs = ( is_array( $type_terms ) && ! empty( $type_terms ) ) ? implode( ' ', wp_list_pluck( $type_terms, 'slug' ) ) : '';
			?>
			<div class="ww-bonus-card" data-bonus-types="<?php echo esc_attr( $type_slugs ); ?>">
				<?php if ( $type_label ) : ?><span class="ww-bonus-card__type"><?php echo esc_html( $type_label ); ?></span><?php endif; ?>
				<p class="ww-bonus-card__value"><?php echo esc_html( $value ); ?></p>
				<h4><?php echo esc_html( get_the_title( $bonus ) ); ?></h4>
				<?php if ( $casino_id ) : ?>
					<p class="ww-bonus-card__casino"><?php echo esc_html( get_the_title( $casino_id ) ); ?></p>
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

function wagerwise_render_block_casino_hero(): string {
	$post_id = get_the_ID();
	if ( ! $post_id || 'casino' !== get_post_type( $post_id ) ) {
		return '';
	}

	$rating      = (float) get_post_meta( $post_id, 'ww_rating', true );
	$established = get_post_meta( $post_id, 'ww_year_established', true );
	$min_deposit = get_post_meta( $post_id, 'ww_min_deposit', true );
	$wagering    = get_post_meta( $post_id, 'ww_wagering_requirement', true );
	$payout      = get_post_meta( $post_id, 'ww_payout_speed', true );
	$link        = get_post_meta( $post_id, 'ww_affiliate_link', true );
	$cta         = get_post_meta( $post_id, 'ww_cta_label', true ) ?: __( 'Play Now', 'wagerwise' );

	ob_start();
	?>
	<div class="ww-casino-hero">
		<div class="ww-casino-hero__logo"><?php echo get_the_post_thumbnail( $post_id, 'casino-logo' ); ?></div>
		<div class="ww-casino-hero__body">
			<h1><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
			<?php echo wagerwise_star_rating_html( $rating ); ?>
			<ul class="ww-casino-hero__facts">
				<?php if ( $established ) : ?><li><?php esc_html_e( 'Established', 'wagerwise' ); ?>: <?php echo esc_html( $established ); ?></li><?php endif; ?>
				<?php if ( $min_deposit ) : ?><li><?php esc_html_e( 'Min. Deposit', 'wagerwise' ); ?>: <?php echo esc_html( $min_deposit ); ?></li><?php endif; ?>
				<?php if ( $wagering ) : ?><li><?php esc_html_e( 'Wagering', 'wagerwise' ); ?>: <?php echo esc_html( $wagering ); ?></li><?php endif; ?>
				<?php if ( $payout ) : ?><li><?php esc_html_e( 'Payout Speed', 'wagerwise' ); ?>: <?php echo esc_html( $payout ); ?></li><?php endif; ?>
			</ul>
		</div>
		<div class="ww-casino-hero__cta"><?php echo wagerwise_cta_button_html( $link, $cta, 'ww-btn ww-btn--primary ww-btn--large' ); ?></div>
	</div>
	<?php
	return (string) ob_get_clean();
}

function wagerwise_render_block_bonus_hero(): string {
	$post_id = get_the_ID();
	if ( ! $post_id || 'bonus' !== get_post_type( $post_id ) ) {
		return '';
	}

	$casino_id = (int) get_post_meta( $post_id, 'ww_related_casino', true );
	$value     = get_post_meta( $post_id, 'ww_bonus_value', true );
	$code      = get_post_meta( $post_id, 'ww_promo_code', true );
	$terms     = get_post_meta( $post_id, 'ww_terms_summary', true );
	$expiry    = get_post_meta( $post_id, 'ww_expiry_date', true );
	$link      = get_post_meta( $post_id, 'ww_affiliate_link', true );
	$cta       = get_post_meta( $post_id, 'ww_cta_label', true ) ?: __( 'Claim Bonus', 'wagerwise' );

	ob_start();
	?>
	<div class="ww-bonus-hero">
		<?php if ( $casino_id ) : ?>
			<div class="ww-bonus-hero__logo"><?php echo get_the_post_thumbnail( $casino_id, 'casino-logo' ); ?></div>
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

function wagerwise_render_block_pros_cons(): string {
	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return '';
	}
	$pros = get_post_meta( $post_id, 'ww_pros', true ) ?: array();
	$cons = get_post_meta( $post_id, 'ww_cons', true ) ?: array();
	return wagerwise_pros_cons_html( (array) $pros, (array) $cons );
}

function wagerwise_render_block_cta_button( array $attrs ): string {
	$url   = $attrs['url'] ?? '';
	$label = $attrs['label'] ?? __( 'Play Now', 'wagerwise' );

	if ( empty( $url ) && get_post_type() === 'casino' ) {
		$url = get_post_meta( get_the_ID(), 'ww_affiliate_link', true );
	}

	return wagerwise_cta_button_html( $url, $label );
}

function wagerwise_render_block_blog_grid( array $attrs ): string {
	$args = array(
		'post_type'      => 'post',
		'posts_per_page' => $attrs['number'] ?? 3,
	);
	if ( ! empty( $attrs['categoryId'] ) ) {
		$args['cat'] = $attrs['categoryId'];
	}
	$posts = get_posts( $args );
	if ( empty( $posts ) ) {
		return '';
	}
	ob_start();
	?>
	<div class="ww-blog-grid">
		<?php foreach ( $posts as $p ) : ?>
			<a class="ww-blog-card" href="<?php echo esc_url( get_permalink( $p ) ); ?>">
				<?php echo get_the_post_thumbnail( $p, 'medium_large' ); ?>
				<h4><?php echo esc_html( get_the_title( $p ) ); ?></h4>
				<p><?php echo esc_html( wp_trim_words( $p->post_excerpt ?: $p->post_content, 18 ) ); ?></p>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

function wagerwise_render_block_hero_search( array $attrs ): string {
	// wagerwise_pll__() wraps the STOCK default text (registered + translated
	// via Polylang in seed.php), so it resolves per-language automatically.
	// If an admin has customized the option away from that default in
	// WagerWise Settings, there's no registered translation for whatever
	// they typed, so it falls back to showing their custom text as-is in
	// every language — a deliberate, graceful degrade rather than silently
	// discarding an intentional admin override.
	$heading    = $attrs['heading'] ?: wagerwise_pll__( get_option( 'ww_hero_heading' ) );
	$subheading = $attrs['subheading'] ?: wagerwise_pll__( get_option( 'ww_hero_subheading' ) );
	$casinos    = (int) wp_count_posts( 'casino' )->publish;
	ob_start();
	?>
	<div class="ww-hero">
		<?php if ( $casinos ) : ?>
			<span class="ww-hero__badge"><?php echo esc_html( sprintf( wagerwise_pll__( 'Updated daily · %d+ casinos reviewed' ), $casinos ) ); ?></span>
		<?php endif; ?>
		<h1><?php echo esc_html( $heading ); ?></h1>
		<p><?php echo esc_html( $subheading ); ?></p>
		<div class="ww-hero__ctas">
			<a class="ww-btn ww-btn--primary ww-btn--large" href="<?php echo esc_url( wagerwise_lang_archive_url( 'casino' ) ); ?>"><?php echo esc_html( wagerwise_pll__( 'Explore Casinos' ) ); ?></a>
			<a class="ww-btn ww-btn--ghost ww-btn--large" href="<?php echo esc_url( wagerwise_lang_archive_url( 'tournament' ) ); ?>"><?php echo esc_html( wagerwise_pll__( "This Week's Tournaments" ) ); ?></a>
		</div>
		<form class="ww-hero-search" action="<?php echo esc_url( wagerwise_lang_home_url() ); ?>" method="get">
			<input type="search" name="s" placeholder="<?php echo esc_attr( wagerwise_pll__( 'Search casinos, bonuses, guides…' ) ); ?>" />
			<button type="submit"><?php echo esc_html( wagerwise_pll__( 'Search' ) ); ?></button>
		</form>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Quick-scan numbers row (casinos reviewed, active bonuses, free games,
 * published guides) — mirrors the "at a glance" stat strip pattern common on
 * casino review sites, computed live from actual post counts (no fake data).
 */
function wagerwise_render_block_stats_strip(): string {
	$stats = array(
		array( 'label' => __( 'Casinos Reviewed', 'wagerwise' ), 'count' => (int) wp_count_posts( 'casino' )->publish ),
		array( 'label' => __( 'Active Bonuses', 'wagerwise' ), 'count' => (int) wp_count_posts( 'bonus' )->publish ),
		array( 'label' => __( 'Free Games', 'wagerwise' ), 'count' => (int) wp_count_posts( 'game' )->publish ),
		array( 'label' => __( 'Guides & Articles', 'wagerwise' ), 'count' => (int) wp_count_posts( 'post' )->publish ),
	);
	if ( array_sum( wp_list_pluck( $stats, 'count' ) ) === 0 ) {
		return '';
	}
	ob_start();
	?>
	<div class="ww-stats-strip">
		<?php foreach ( $stats as $stat ) : ?>
			<div class="ww-stat">
				<span class="ww-stat__number"><?php echo esc_html( number_format_i18n( $stat['count'] ) ); ?>+</span>
				<span class="ww-stat__label"><?php echo esc_html( $stat['label'] ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Software provider "wordmark" tiles — a text-only logo grid (no external
 * brand assets needed) linking to each provider's archive.
 */
function wagerwise_render_block_provider_strip( array $attrs ): string {
	$terms = get_terms( array(
		'taxonomy'   => 'software_provider',
		'number'     => $attrs['number'] ?? 8,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'hide_empty' => true,
	) );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}
	ob_start();
	?>
	<div class="ww-provider-strip">
		<?php foreach ( $terms as $term ) : ?>
			<a class="ww-provider-tile" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Casino-category quick-filter pills, similar in spirit to the "browse by
 * type" row on casino review sites — links straight into the relevant
 * archive-casino.html term view.
 */
function wagerwise_taxonomy_archive_url( string $taxonomy ): string {
	return match ( $taxonomy ) {
		'casino_category' => wagerwise_lang_archive_url( 'casino' ),
		'bonus_type'       => wagerwise_lang_archive_url( 'bonus' ),
		'game_category'    => wagerwise_lang_archive_url( 'game' ),
		default            => wagerwise_lang_home_url(),
	};
}

function wagerwise_render_block_category_strip( array $attrs ): string {
	$taxonomy = $attrs['taxonomy'] ?? 'casino_category';
	$style    = $attrs['style'] ?? 'chip';
	$terms    = get_terms( array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => true,
	) );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}
	$is_current_archive = ! is_tax( $taxonomy );
	ob_start();
	?>
	<div class="ww-category-strip ww-category-strip--<?php echo esc_attr( $style ); ?>" data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>">
		<?php if ( 'chip' === $style ) : ?>
			<a class="ww-category-pill<?php echo $is_current_archive ? ' is-active' : ''; ?>" href="<?php echo esc_url( wagerwise_taxonomy_archive_url( $taxonomy ) ); ?>" data-term-slug=""><?php esc_html_e( 'All', 'wagerwise' ); ?></a>
		<?php endif; ?>
		<?php foreach ( $terms as $term ) :
			$active = is_tax( $taxonomy ) && (int) get_queried_object_id() === $term->term_id;
			?>
			<a class="<?php echo 'tile' === $style ? 'ww-category-tile' : 'ww-category-pill' . ( $active ? ' is-active' : '' ); ?>" href="<?php echo esc_url( get_term_link( $term ) ); ?>" data-term-slug="<?php echo esc_attr( $term->slug ); ?>">
				<?php if ( 'tile' === $style ) : ?>
					<span class="ww-category-tile__icon" aria-hidden="true"></span>
					<span class="ww-category-tile__name"><?php echo esc_html( $term->name ); ?></span>
					<span class="ww-category-tile__count"><?php echo esc_html( sprintf( _n( '%d listed', '%d listed', $term->count, 'wagerwise' ), $term->count ) ); ?></span>
				<?php else : ?>
					<?php echo esc_html( $term->name ); ?>
				<?php endif; ?>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

function wagerwise_render_block_game_grid( array $attrs ): string {
	$args = array(
		'post_type'      => 'game',
		'posts_per_page' => $attrs['number'] ?? 8,
	);
	$tax_query = array();
	if ( ! empty( $attrs['categoryId'] ) ) {
		$tax_query[] = array( 'taxonomy' => 'game_category', 'field' => 'term_id', 'terms' => $attrs['categoryId'] );
	}
	if ( ! empty( $attrs['providerId'] ) ) {
		$tax_query[] = array( 'taxonomy' => 'software_provider', 'field' => 'term_id', 'terms' => $attrs['providerId'] );
	}
	if ( ! empty( $tax_query ) ) {
		$args['tax_query'] = count( $tax_query ) > 1 ? array_merge( array( 'relation' => 'AND' ), $tax_query ) : $tax_query;
	}
	$games = get_posts( $args );
	if ( empty( $games ) ) {
		return '';
	}
	ob_start();
	?>
	<div class="ww-game-grid">
		<?php foreach ( $games as $game ) :
			$rtp        = get_post_meta( $game->ID, 'ww_rtp', true );
			$demo_link  = get_post_meta( $game->ID, 'ww_demo_link', true );
			$providers  = get_the_terms( $game, 'software_provider' );
			$provider   = ( is_array( $providers ) && ! empty( $providers ) ) ? $providers[0]->name : '';
			?>
			<div class="ww-game-card">
				<a class="ww-game-card__thumb" href="<?php echo esc_url( get_permalink( $game ) ); ?>">
					<?php echo get_the_post_thumbnail( $game, 'medium' ); ?>
					<?php if ( $rtp ) : ?><span class="ww-badge ww-game-card__rtp">RTP <?php echo esc_html( $rtp ); ?></span><?php endif; ?>
				</a>
				<div class="ww-game-card__body">
					<a class="ww-game-card__name" href="<?php echo esc_url( get_permalink( $game ) ); ?>"><?php echo esc_html( get_the_title( $game ) ); ?></a>
					<?php if ( $provider ) : ?><span class="ww-game-card__provider"><?php echo esc_html( $provider ); ?></span><?php endif; ?>
				</div>
				<?php if ( $demo_link ) : ?>
					<a class="ww-btn ww-btn--ghost ww-btn--small" href="<?php echo esc_url( $demo_link ); ?>" rel="noopener" target="_blank"><?php esc_html_e( 'Play Demo', 'wagerwise' ); ?></a>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

function wagerwise_render_block_game_hero(): string {
	$post_id = get_the_ID();
	if ( ! $post_id || 'game' !== get_post_type( $post_id ) ) {
		return '';
	}

	$rtp       = get_post_meta( $post_id, 'ww_rtp', true );
	$min_bet   = get_post_meta( $post_id, 'ww_min_bet', true );
	$max_win   = get_post_meta( $post_id, 'ww_max_win', true );
	$demo_link = get_post_meta( $post_id, 'ww_demo_link', true );
	$providers = get_the_terms( $post_id, 'software_provider' );
	$provider  = ( is_array( $providers ) && ! empty( $providers ) ) ? $providers[0]->name : '';

	ob_start();
	?>
	<div class="ww-casino-hero ww-game-hero">
		<div class="ww-casino-hero__logo"><?php echo get_the_post_thumbnail( $post_id, 'casino-logo' ); ?></div>
		<div class="ww-casino-hero__body">
			<h1><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
			<?php if ( $provider ) : ?><p class="ww-game-hero__provider"><?php esc_html_e( 'By', 'wagerwise' ); ?> <?php echo esc_html( $provider ); ?></p><?php endif; ?>
			<ul class="ww-casino-hero__facts">
				<?php if ( $rtp ) : ?><li><?php esc_html_e( 'RTP', 'wagerwise' ); ?>: <?php echo esc_html( $rtp ); ?></li><?php endif; ?>
				<?php if ( $min_bet ) : ?><li><?php esc_html_e( 'Min. Bet', 'wagerwise' ); ?>: <?php echo esc_html( $min_bet ); ?></li><?php endif; ?>
				<?php if ( $max_win ) : ?><li><?php esc_html_e( 'Max Win', 'wagerwise' ); ?>: <?php echo esc_html( $max_win ); ?></li><?php endif; ?>
			</ul>
		</div>
		<?php if ( $demo_link ) : ?>
			<div class="ww-casino-hero__cta">
				<a class="ww-btn ww-btn--primary ww-btn--large" href="<?php echo esc_url( $demo_link ); ?>" rel="noopener" target="_blank"><?php esc_html_e( 'Play Demo', 'wagerwise' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Primary nav with active-page highlighting — plain wp:navigation-link
 * markup can't know "am I on this section" per-request since template parts
 * are shared across every page, so this renders server-side instead.
 */
function wagerwise_render_block_site_nav(): string {
	$items = array(
		array( 'label' => wagerwise_pll__( 'Home' ), 'url' => wagerwise_lang_home_url(), 'active' => is_front_page() ),
		array( 'label' => wagerwise_pll__( 'Online Casinos' ), 'url' => wagerwise_lang_archive_url( 'casino' ), 'active' => is_post_type_archive( 'casino' ) || is_singular( 'casino' ) || is_tax( 'casino_category' ) ),
		array( 'label' => wagerwise_pll__( 'Games' ), 'url' => wagerwise_lang_archive_url( 'game' ), 'active' => is_post_type_archive( 'game' ) || is_singular( 'game' ) || is_tax( 'game_category' ) ),
		array( 'label' => wagerwise_pll__( 'Bonuses' ), 'url' => wagerwise_lang_archive_url( 'bonus' ), 'active' => is_post_type_archive( 'bonus' ) || is_singular( 'bonus' ) || is_tax( 'bonus_type' ) ),
		array( 'label' => wagerwise_pll__( 'Guide' ), 'url' => wagerwise_lang_page_url( 'guide' ), 'active' => wagerwise_is_lang_page( 'guide' ) || is_singular( 'guide' ) || is_tax( 'guide_category' ) ),
		array( 'label' => wagerwise_pll__( 'Complaints' ), 'url' => wagerwise_lang_page_url( 'complaints' ), 'active' => wagerwise_is_lang_page( 'complaints' ) || is_singular( 'complaint' ) || is_tax( 'complaint_issue_type' ) ),
		array( 'label' => wagerwise_pll__( 'Reviews' ), 'url' => wagerwise_lang_page_url( 'reviews' ), 'active' => wagerwise_is_lang_page( 'reviews' ) || is_singular( 'review' ) || is_tax( 'review_category' ) ),
		array( 'label' => wagerwise_pll__( 'Tournaments' ), 'url' => wagerwise_lang_archive_url( 'tournament' ), 'active' => is_post_type_archive( 'tournament' ) || is_singular( 'tournament' ) || is_tax( 'tournament_type' ) ),
		array( 'label' => wagerwise_pll__( 'News' ), 'url' => wagerwise_lang_page_url( 'news' ), 'active' => wagerwise_is_lang_page( 'news' ) || is_singular( 'news' ) || is_tax( 'news_category' ) ),
	);
	ob_start();
	?>
	<div class="ww-nav-wrap">
		<button type="button" class="ww-nav-toggle" aria-expanded="false" aria-controls="ww-primary-nav" aria-label="<?php echo esc_attr( wagerwise_pll__( 'Menu' ) ); ?>">
			<span class="ww-nav-toggle__bar"></span>
			<span class="ww-nav-toggle__bar"></span>
			<span class="ww-nav-toggle__bar"></span>
		</button>
		<nav class="ww-header__nav" id="ww-primary-nav">
			<?php foreach ( $items as $item ) : ?>
				<a href="<?php echo esc_url( $item['url'] ); ?>" class="<?php echo $item['active'] ? 'is-active' : ''; ?>"><?php echo esc_html( $item['label'] ); ?></a>
			<?php endforeach; ?>
		</nav>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * "Showing X of Y / Sorted by …" meta row above an archive listing.
 */
function wagerwise_render_block_list_meta( array $attrs ): string {
	$post_type   = $attrs['postType'] ?? 'casino';
	$post_type_obj = get_post_type_object( $post_type );
	if ( ! $post_type_obj ) {
		return '';
	}
	$total     = (int) wp_count_posts( $post_type )->publish;
	$sort_label = $attrs['sortLabel'] ?? __( 'Rating (high to low)', 'wagerwise' );
	ob_start();
	?>
	<div class="ww-list-meta">
		<span><?php echo esc_html( sprintf( __( 'Showing %1$d of %1$d %2$s', 'wagerwise' ), $total, strtolower( $post_type_obj->labels->name ) ) ); ?></span>
		<span><?php esc_html_e( 'Sort:', 'wagerwise' ); ?> <strong><?php echo esc_html( $sort_label ); ?></strong></span>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Fallback content for taxonomy.html — WordPress's generic archive template
 * for ANY taxonomy that doesn't have a more specific taxonomy-{tax}.html
 * file. Without this, browsing e.g. /provider/netent/ or /country/canada/
 * would silently render the blog index instead of filtered results.
 */
function wagerwise_render_block_taxonomy_results(): string {
	$term = get_queried_object();
	if ( ! ( $term instanceof WP_Term ) ) {
		return '';
	}

	switch ( $term->taxonomy ) {
		case 'bonus_type':
			return wagerwise_render_block_bonus_grid( array( 'number' => 24, 'bonusTypeId' => $term->term_id ) );

		case 'game_category':
			return wagerwise_render_block_game_grid( array( 'number' => 24, 'categoryId' => $term->term_id ) );

		case 'casino_category':
			return wagerwise_render_casino_cards( wagerwise_get_top_casinos( 24, $term->term_id ), 'list' );

		case 'payment_method':
		case 'licence':
		case 'country':
			return wagerwise_render_casino_cards( wagerwise_get_casinos_by_taxonomy( $term->taxonomy, $term->term_id, 24 ), 'list' );

		case 'software_provider':
			ob_start();
			$casinos = wagerwise_get_casinos_by_taxonomy( 'software_provider', $term->term_id, 12 );
			if ( ! empty( $casinos ) ) {
				printf( '<h3>%s</h3>', esc_html( sprintf( __( 'Casinos featuring %s', 'wagerwise' ), $term->name ) ) );
				echo wagerwise_render_casino_cards( $casinos, 'grid' );
			}
			$games = wagerwise_render_block_game_grid( array( 'number' => 12, 'providerId' => $term->term_id ) );
			if ( $games ) {
				printf( '<h3>%s</h3>', esc_html( sprintf( __( 'Games by %s', 'wagerwise' ), $term->name ) ) );
				echo $games;
			}
			return (string) ob_get_clean();

		default:
			return '';
	}
}

// --- Tournaments ----------------------------------------------------------

function wagerwise_render_block_tournament_grid( array $attrs ): string {
	$tournaments = get_posts( array( 'post_type' => 'tournament', 'posts_per_page' => $attrs['number'] ?? 3 ) );
	if ( empty( $tournaments ) ) {
		return '';
	}
	ob_start();
	?>
	<div class="ww-tournament-grid">
		<?php foreach ( $tournaments as $t ) :
			$status    = get_post_meta( $t->ID, 'ww_status_label', true );
			$entries   = get_post_meta( $t->ID, 'ww_entries', true );
			$casino_id = (int) get_post_meta( $t->ID, 'ww_related_casino', true );
			?>
			<a class="ww-tournament-card" href="<?php echo esc_url( get_permalink( $t ) ); ?>">
				<?php if ( $status ) : ?><span class="ww-tournament-card__status"><?php echo esc_html( $status ); ?></span><?php endif; ?>
				<span class="ww-tournament-card__name"><?php echo esc_html( get_the_title( $t ) ); ?></span>
				<span class="ww-tournament-card__meta">
					<?php echo $casino_id ? esc_html( get_the_title( $casino_id ) ) : ''; ?>
					<?php echo ( $casino_id && $entries ) ? ' · ' : ''; ?>
					<?php echo $entries ? esc_html( sprintf( __( '%s entries', 'wagerwise' ), $entries ) ) : ''; ?>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

function wagerwise_render_block_tournament_leaderboard(): string {
	$post_id = get_the_ID();
	if ( ! $post_id || 'tournament' !== get_post_type( $post_id ) ) {
		return '';
	}
	$rows = get_post_meta( $post_id, 'ww_leaderboard', true );
	if ( empty( $rows ) || ! is_array( $rows ) ) {
		return '';
	}
	ob_start();
	?>
	<div class="ww-leaderboard">
		<div class="ww-leaderboard__row ww-leaderboard__row--head">
			<span><?php esc_html_e( 'Rank', 'wagerwise' ); ?></span>
			<span><?php esc_html_e( 'Player', 'wagerwise' ); ?></span>
			<span><?php esc_html_e( 'Points', 'wagerwise' ); ?></span>
			<span><?php esc_html_e( 'Prize', 'wagerwise' ); ?></span>
		</div>
		<?php foreach ( $rows as $i => $row ) :
			$parts  = array_map( 'trim', explode( '|', $row ) );
			$player = $parts[0] ?? '';
			$points = $parts[1] ?? '';
			$prize  = $parts[2] ?? '';
			?>
			<div class="ww-leaderboard__row">
				<span class="<?php echo 0 === $i ? 'ww-leaderboard__rank--first' : ''; ?>"><?php echo (int) $i + 1; ?></span>
				<span><?php echo esc_html( $player ); ?></span>
				<span><?php echo esc_html( $points ); ?></span>
				<span><?php echo esc_html( $prize ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

// --- Guide / News (dedicated CPTs, filed under their own taxonomies) ------

function wagerwise_render_block_guide_featured(): string {
	$posts = get_posts( array( 'post_type' => 'guide', 'posts_per_page' => 1 ) );
	if ( empty( $posts ) ) {
		return '';
	}
	$post  = $posts[0];
	$terms = get_the_terms( $post, 'guide_category' );
	$cat   = ( is_array( $terms ) && ! empty( $terms ) ) ? $terms[0]->name : '';
	ob_start();
	?>
	<a class="ww-featured-guide" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
		<div class="ww-featured-guide__body">
			<span class="ww-featured-guide__tag"><?php echo esc_html( sprintf( __( 'Featured · %s', 'wagerwise' ), $cat ) ); ?></span>
			<h3><?php echo esc_html( get_the_title( $post ) ); ?></h3>
			<p><?php echo esc_html( wp_trim_words( $post->post_excerpt ?: $post->post_content, 26 ) ); ?></p>
			<span class="ww-featured-guide__meta"><?php echo esc_html( sprintf( __( '%d min read · Updated %s', 'wagerwise' ), wagerwise_reading_time( $post->post_content ), get_the_date( 'F Y', $post ) ) ); ?></span>
		</div>
		<div class="ww-featured-guide__art"><?php echo get_the_post_thumbnail( $post, 'medium_large' ); ?></div>
	</a>
	<?php
	return (string) ob_get_clean();
}

function wagerwise_render_block_guide_list( array $attrs ): string {
	$featured = get_posts( array( 'post_type' => 'guide', 'posts_per_page' => 1, 'fields' => 'ids' ) );
	$posts    = get_posts( array(
		'post_type'      => 'guide',
		'posts_per_page' => $attrs['number'] ?? 8,
		'post__not_in'   => $featured,
	) );
	if ( empty( $posts ) ) {
		return '';
	}
	ob_start();
	?>
	<div class="ww-guide-list">
		<?php foreach ( $posts as $post ) :
			$terms = get_the_terms( $post, 'guide_category' );
			$cat   = ( is_array( $terms ) && ! empty( $terms ) ) ? $terms[0]->name : '';
			?>
			<a class="ww-guide-row" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
				<span class="ww-guide-row__thumb"><?php echo get_the_post_thumbnail( $post, 'thumbnail' ); ?></span>
				<span class="ww-guide-row__body">
					<span class="ww-guide-row__title"><?php echo esc_html( get_the_title( $post ) ); ?></span>
					<span class="ww-guide-row__meta"><?php echo esc_html( $cat ); ?> · <?php echo (int) wagerwise_reading_time( $post->post_content ); ?> <?php esc_html_e( 'min read', 'wagerwise' ); ?></span>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

function wagerwise_render_block_news_featured( array $attrs ): string {
	$posts = get_posts( array( 'post_type' => 'news', 'posts_per_page' => 1 ) );
	if ( empty( $posts ) ) {
		return '';
	}
	$post  = $posts[0];
	$terms = get_the_terms( $post, 'news_category' );
	$cat   = ( is_array( $terms ) && ! empty( $terms ) ) ? $terms[0]->name : '';
	ob_start();
	?>
	<a class="ww-featured-guide" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
		<div class="ww-featured-guide__body">
			<span class="ww-featured-guide__tag"><?php echo esc_html( $cat ); ?></span>
			<h3><?php echo esc_html( get_the_title( $post ) ); ?></h3>
			<p><?php echo esc_html( wp_trim_words( $post->post_excerpt ?: $post->post_content, 26 ) ); ?></p>
			<span class="ww-featured-guide__meta"><?php echo esc_html( get_the_date( '', $post ) ); ?></span>
		</div>
		<div class="ww-featured-guide__art"><?php echo get_the_post_thumbnail( $post, 'medium_large' ); ?></div>
	</a>
	<?php
	return (string) ob_get_clean();
}

function wagerwise_render_block_news_grid( array $attrs ): string {
	$exclude = array();
	if ( ! empty( $attrs['skipFirst'] ) ) {
		$exclude = get_posts( array( 'post_type' => 'news', 'posts_per_page' => 1, 'fields' => 'ids' ) );
	}
	$posts = get_posts( array(
		'post_type'      => 'news',
		'posts_per_page' => $attrs['number'] ?? 6,
		'post__not_in'   => $exclude,
	) );
	if ( empty( $posts ) ) {
		return '';
	}
	ob_start();
	?>
	<div class="ww-news-grid">
		<?php foreach ( $posts as $post ) :
			$terms = get_the_terms( $post, 'news_category' );
			$cat   = ( is_array( $terms ) && ! empty( $terms ) ) ? $terms[0]->name : '';
			?>
			<a class="ww-news-card" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
				<span class="ww-news-card__tag"><?php echo esc_html( $cat ); ?></span>
				<span class="ww-news-card__title"><?php echo esc_html( get_the_title( $post ) ); ?></span>
				<span class="ww-news-card__date"><?php echo esc_html( get_the_date( '', $post ) ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

// --- Editorial reviews (the `review` CPT — see wagerwise_render_block_reviews_list() below for the separate, comment-based "Player Reviews") ---

function wagerwise_render_block_review_grid( array $attrs ): string {
	$reviews = wagerwise_get_reviews( $attrs['number'] ?? 6 );
	if ( empty( $reviews ) ) {
		return '';
	}
	ob_start();
	?>
	<div class="ww-review-grid">
		<?php foreach ( $reviews as $post ) :
			$casino_id = (int) get_post_meta( $post->ID, 'ww_related_casino', true );
			$rating    = (float) get_post_meta( $post->ID, 'ww_rating', true );
			$verdict   = get_post_meta( $post->ID, 'ww_verdict', true );
			?>
			<a class="ww-review-grid-card" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
				<?php echo get_the_post_thumbnail( $post, 'medium_large' ); ?>
				<div class="ww-review-grid-card__body">
					<?php if ( $casino_id ) : ?><span class="ww-review-grid-card__casino"><?php echo esc_html( get_the_title( $casino_id ) ); ?></span><?php endif; ?>
					<h3><?php echo esc_html( get_the_title( $post ) ); ?></h3>
					<?php if ( $rating ) : ?><?php echo wagerwise_star_rating_html( $rating ); ?><?php endif; ?>
					<?php if ( $verdict ) : ?><p class="ww-review-grid-card__verdict"><?php echo esc_html( $verdict ); ?></p><?php endif; ?>
				</div>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

// --- Reviews (native comments on the casino post type) --------------------

function wagerwise_render_block_reviews_list( array $attrs ): string {
	$comments = get_comments( array(
		'post_type' => 'casino',
		'status'    => 'approve',
		'number'    => $attrs['number'] ?? 12,
	) );
	if ( empty( $comments ) ) {
		return '';
	}
	ob_start();
	?>
	<div class="ww-reviews-list">
		<?php foreach ( $comments as $c ) :
			$rating = (float) get_comment_meta( $c->comment_ID, 'ww_review_rating', true );
			?>
			<div class="ww-review-card">
				<?php echo get_avatar( $c->comment_author_email, 48 ); ?>
				<div class="ww-review-card__body">
					<div class="ww-review-card__head">
						<span class="ww-review-card__author"><?php echo esc_html( $c->comment_author ); ?> <span class="ww-review-card__on"><?php echo esc_html( sprintf( __( 'on %s', 'wagerwise' ), get_the_title( $c->comment_post_ID ) ) ); ?></span></span>
						<?php if ( $rating ) : ?><span class="ww-review-card__rating"><?php echo wp_kses_post( str_repeat( '★', (int) round( $rating ) ) . str_repeat( '☆', 5 - (int) round( $rating ) ) ); ?> <?php echo esc_html( number_format( $rating, 1 ) ); ?></span><?php endif; ?>
					</div>
					<p class="ww-review-card__text"><?php echo esc_html( $c->comment_content ); ?></p>
					<span class="ww-review-card__date"><?php echo esc_html( human_time_diff( strtotime( $c->comment_date ) ) ); ?> <?php esc_html_e( 'ago', 'wagerwise' ); ?></span>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

// --- Complaints -------------------------------------------------------------

function wagerwise_render_block_complaints_stats(): string {
	$complaints = get_posts( array( 'post_type' => 'complaint', 'posts_per_page' => -1 ) );
	$resolved   = array_filter( $complaints, fn( $c ) => (bool) get_post_meta( $c->ID, 'ww_resolved_date', true ) );
	$recovered  = array_sum( array_map( fn( $c ) => (float) get_post_meta( $c->ID, 'ww_amount_recovered', true ), $resolved ) );

	$days = array();
	foreach ( $resolved as $c ) {
		$filed    = get_post_meta( $c->ID, 'ww_filed_date', true );
		$resolved_on = get_post_meta( $c->ID, 'ww_resolved_date', true );
		if ( $filed && $resolved_on ) {
			$diff = ( strtotime( $resolved_on ) - strtotime( $filed ) ) / DAY_IN_SECONDS;
			if ( $diff >= 0 ) {
				$days[] = $diff;
			}
		}
	}
	$avg_days = $days ? array_sum( $days ) / count( $days ) : 0;

	ob_start();
	?>
	<div class="ww-stats-strip">
		<div class="ww-stat">
			<span class="ww-stat__number"><?php echo (int) count( $resolved ); ?></span>
			<span class="ww-stat__label"><?php esc_html_e( 'Cases resolved', 'wagerwise' ); ?></span>
		</div>
		<div class="ww-stat">
			<span class="ww-stat__number">$<?php echo esc_html( number_format( $recovered, 0 ) ); ?></span>
			<span class="ww-stat__label"><?php esc_html_e( 'Recovered for players', 'wagerwise' ); ?></span>
		</div>
		<div class="ww-stat">
			<span class="ww-stat__number"><?php echo esc_html( number_format( $avg_days, 1 ) ); ?> <?php esc_html_e( 'days', 'wagerwise' ); ?></span>
			<span class="ww-stat__label"><?php esc_html_e( 'Avg. resolution time', 'wagerwise' ); ?></span>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

function wagerwise_render_block_complaints_list( array $attrs ): string {
	$complaints = get_posts( array( 'post_type' => 'complaint', 'posts_per_page' => $attrs['number'] ?? 6, 'orderby' => 'date', 'order' => 'DESC' ) );
	if ( empty( $complaints ) ) {
		return '';
	}
	ob_start();
	?>
	<div class="ww-complaints-list">
		<?php foreach ( $complaints as $c ) :
			$case_no   = get_post_meta( $c->ID, 'ww_case_number', true );
			$issue     = get_post_meta( $c->ID, 'ww_issue_type', true );
			$casino_id = (int) get_post_meta( $c->ID, 'ww_related_casino', true );
			$summary   = get_post_meta( $c->ID, 'ww_summary', true );
			$outcome   = get_post_meta( $c->ID, 'ww_outcome', true );
			?>
			<div class="ww-complaint-card">
				<p class="ww-complaint-card__meta"><?php echo esc_html( sprintf( __( 'Case #%1$s · %2$s · %3$s', 'wagerwise' ), $case_no, $issue, $casino_id ? get_the_title( $casino_id ) : '' ) ); ?></p>
				<p class="ww-complaint-card__summary"><?php echo esc_html( $summary ); ?></p>
				<?php if ( $outcome ) : ?><p class="ww-complaint-card__outcome"><?php echo esc_html( sprintf( __( 'Resolved · %s', 'wagerwise' ), $outcome ) ); ?></p><?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

function wagerwise_render_block_complaint_form(): string {
	$casinos = get_posts( array( 'post_type' => 'casino', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
	$notice  = '';
	if ( isset( $_GET['complaint'] ) && 'sent' === $_GET['complaint'] ) {
		$notice = __( 'Thanks — we\'ve received your complaint and will follow up by email.', 'wagerwise' );
	}
	ob_start();
	?>
	<form class="ww-complaint-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php if ( $notice ) : ?><p class="ww-complaint-form__notice"><?php echo esc_html( $notice ); ?></p><?php endif; ?>
		<input type="hidden" name="action" value="wagerwise_submit_complaint" />
		<?php wp_nonce_field( 'wagerwise_submit_complaint' ); ?>
		<label><?php esc_html_e( 'Your Name', 'wagerwise' ); ?><input type="text" name="ww_name" required /></label>
		<label><?php esc_html_e( 'Your Email', 'wagerwise' ); ?><input type="email" name="ww_email" required /></label>
		<label><?php esc_html_e( 'Casino Name', 'wagerwise' ); ?>
			<select name="ww_casino">
				<option value=""><?php esc_html_e( '— Select casino —', 'wagerwise' ); ?></option>
				<?php foreach ( $casinos as $c ) : ?><option value="<?php echo esc_attr( $c->post_title ); ?>"><?php echo esc_html( $c->post_title ); ?></option><?php endforeach; ?>
			</select>
		</label>
		<label><?php esc_html_e( 'Issue Type', 'wagerwise' ); ?>
			<select name="ww_issue">
				<option><?php esc_html_e( 'Withdrawal delay', 'wagerwise' ); ?></option>
				<option><?php esc_html_e( 'Bonus dispute', 'wagerwise' ); ?></option>
				<option><?php esc_html_e( 'Account closure', 'wagerwise' ); ?></option>
				<option><?php esc_html_e( 'Other', 'wagerwise' ); ?></option>
			</select>
		</label>
		<label><?php esc_html_e( 'Amount in Dispute', 'wagerwise' ); ?><input type="text" name="ww_amount" placeholder="$0.00" /></label>
		<label><?php esc_html_e( 'Details', 'wagerwise' ); ?><textarea name="ww_details" rows="4" required></textarea></label>
		<button type="submit" class="ww-btn ww-btn--primary"><?php esc_html_e( 'Submit Complaint', 'wagerwise' ); ?></button>
	</form>
	<?php
	return (string) ob_get_clean();
}

/** Curated footer link row — About / Responsible Gambling / Contact / Privacy. */
function wagerwise_render_block_footer_links(): string {
	$slugs = array( 'about-us', 'responsible-gambling', 'contact', 'privacy-policy', 'terms-of-use', 'cookies-policy', 'imprint' );
	ob_start();
	?>
	<nav class="ww-footer__nav">
		<?php foreach ( $slugs as $slug ) :
			$page = get_page_by_path( $slug );
			if ( ! $page ) {
				continue;
			}
			$target_id = $page->ID;
			if ( function_exists( 'pll_get_post' ) ) {
				$translated = pll_get_post( $page->ID );
				if ( $translated ) {
					$target_id = $translated;
				}
			}
			?>
			<a href="<?php echo esc_url( wagerwise_lang_page_url( $slug ) ); ?>"><?php echo esc_html( get_the_title( $target_id ) ); ?></a>
		<?php endforeach; ?>
	</nav>
	<?php
	return (string) ob_get_clean();
}
