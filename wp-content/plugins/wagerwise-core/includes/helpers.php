<?php
/**
 * Shared render helpers used by both the custom blocks (this plugin) and the
 * theme templates. Kept dependency-free (no ACF) — everything reads plain
 * post meta registered in post-types.php.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Language-aware URL helpers. Plain home_url('/casinos/') etc. always
 * resolves to the DEFAULT language's URL (no /de/ or /zh/ prefix) no matter
 * what language the visitor is currently browsing — every nav link, the
 * hero CTAs, and the header button need these instead so navigating around
 * a translated page doesn't silently drop the visitor back into English.
 */

/** Home URL for the current language. */
function wagerwise_lang_home_url(): string {
	return function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
}

/** CPT archive URL, auto-localized to the current language by Polylang. */
function wagerwise_lang_archive_url( string $post_type ): string {
	$url = get_post_type_archive_link( $post_type );
	return $url ?: wagerwise_lang_home_url();
}

/**
 * Permalink for a composed Page (Guide/Complaints/Reviews/News/etc.) in the
 * CURRENT language, resolved via Polylang from the page's canonical English
 * slug. Falls back to home_url()/$en_slug if Polylang isn't active or the
 * page has no translation in this language.
 */
function wagerwise_lang_page_url( string $en_slug ): string {
	static $cache = array();
	if ( isset( $cache[ $en_slug ] ) ) {
		return $cache[ $en_slug ];
	}
	$page = get_page_by_path( $en_slug );
	$id   = $page ? $page->ID : 0;
	if ( $id && function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $id );
		if ( $translated ) {
			$id = $translated;
		}
	}
	$url               = $id ? get_permalink( $id ) : home_url( '/' . $en_slug . '/' );
	$cache[ $en_slug ] = $url;
	return $url;
}

/**
 * True if the current request is viewing the given composed Page (by its
 * canonical English slug) in ANY language — plain is_page('guide') only
 * matches the English page itself, missing 'guide-de'/'guide-zh'.
 */
function wagerwise_is_lang_page( string $en_slug ): bool {
	$page = get_page_by_path( $en_slug );
	if ( ! $page ) {
		return false;
	}
	$ids = array( $page->ID );
	if ( function_exists( 'pll_get_post_translations' ) ) {
		$ids = array_values( pll_get_post_translations( $page->ID ) );
	}
	return is_page( $ids );
}

/**
 * Translates a static frontend UI string (nav labels, buttons, age-gate
 * copy, etc.) via Polylang's string translation feature — NOT core gettext.
 * Unlike __('...', 'wagerwise'), which needs a compiled .mo file per locale,
 * Polylang string translations are registered (pll_register_string(), see
 * wp-cli/seed.php) and edited from wp-admin under Languages → Translations,
 * so an admin can update them without touching code. Falls back to the
 * original English string if Polylang isn't active or the string was never
 * registered/translated.
 */
function wagerwise_pll__( string $string ): string {
	return function_exists( 'pll__' ) ? pll__( $string ) : $string;
}

/**
 * Rank Math's own breadcrumb trail only inserts a post-type-archive crumb
 * for CPTs registered with has_archive — guide/review/news/complaint
 * deliberately don't have one (post-types.php: each is surfaced through a
 * composed Page instead, e.g. /guide/), so on their own it jumps straight
 * from Home to the post itself, skipping the listing page entirely. Fills
 * in that missing middle crumb, reusing the exact same nav-label strings
 * (already translated in all 16 languages) rather than introducing new
 * untranslated ones.
 */
// Rank Math's Hooker::do_filter() prefixes every hook with 'rank_math/' —
// the actual fired filter is 'rank_math/frontend/breadcrumb/items', not
// the bare 'frontend/breadcrumb/items' its own get_breadcrumb() docblock
// might suggest at a glance.
add_filter( 'rank_math/frontend/breadcrumb/items', 'wagerwise_fill_composed_page_breadcrumb', 10, 1 );
function wagerwise_fill_composed_page_breadcrumb( array $crumbs ): array {
	$map = array(
		'guide'     => array( 'guide', 'Guide' ),
		'review'    => array( 'reviews', 'Reviews' ),
		'news'      => array( 'news', 'News' ),
		'complaint' => array( 'complaints', 'Complaints' ),
	);
	$post_type = get_post_type();
	if ( ! is_singular( array_keys( $map ) ) || ! isset( $map[ $post_type ] ) || count( $crumbs ) < 2 ) {
		return $crumbs;
	}
	list( $slug, $label ) = $map[ $post_type ];
	array_splice( $crumbs, 1, 0, array( array( wagerwise_pll__( $label ), wagerwise_lang_page_url( $slug ), 'hide_in_schema' => false ) ) );
	return $crumbs;
}

function wagerwise_star_rating_html( float $rating ): string {
	$rating = max( 0, min( 5, $rating ) );
	$full   = (int) floor( $rating );
	$half   = ( $rating - $full ) >= 0.5;
	$empty  = 5 - $full - ( $half ? 1 : 0 );

	$html = '<span class="ww-stars" aria-label="' . esc_attr( sprintf( '%.1f out of 5', $rating ) ) . '">';
	$html .= str_repeat( '<span class="ww-star ww-star--full">★</span>', $full );
	if ( $half ) {
		$html .= '<span class="ww-star ww-star--half">★</span>';
	}
	$html .= str_repeat( '<span class="ww-star ww-star--empty">★</span>', max( 0, $empty ) );
	$html .= '<span class="ww-rating-number">' . esc_html( number_format( $rating, 1 ) ) . '</span>';
	$html .= '</span>';

	return $html;
}

/**
 * Maps a 0-5 rating to a short trust label + badge modifier, used on casino
 * cards as a quick-scan trust signal (independent of the star rating).
 */
function wagerwise_trust_label( float $rating ): array {
	if ( $rating >= 4.5 ) {
		return array( __( 'Excellent', 'wagerwise' ), 'ww-badge' );
	}
	if ( $rating >= 4.0 ) {
		return array( __( 'Very High', 'wagerwise' ), 'ww-badge' );
	}
	if ( $rating >= 3.0 ) {
		return array( __( 'High', 'wagerwise' ), 'ww-badge ww-badge--gold' );
	}
	return array( __( 'Average', 'wagerwise' ), 'ww-badge ww-badge--gold' );
}

/**
 * Whole-minutes reading time estimate at 200wpm, used on Guide list rows.
 */
function wagerwise_reading_time( string $content ): int {
	$words = str_word_count( wp_strip_all_tags( $content ) );
	return max( 1, (int) round( $words / 200 ) );
}

function wagerwise_affiliate_link_atts(): string {
	return 'rel="sponsored nofollow noopener" target="_blank"';
}

function wagerwise_cta_button_html( string $url, string $label, string $class = 'ww-btn ww-btn--primary' ): string {
	if ( empty( $url ) ) {
		return '';
	}
	return sprintf(
		'<a class="%1$s" href="%2$s" %3$s>%4$s</a>',
		esc_attr( $class ),
		esc_url( $url ),
		wagerwise_affiliate_link_atts(),
		esc_html( $label )
	);
}

function wagerwise_pros_cons_html( array $pros, array $cons ): string {
	if ( empty( $pros ) && empty( $cons ) ) {
		return '';
	}
	ob_start();
	?>
	<div class="ww-pros-cons">
		<?php if ( ! empty( $pros ) ) : ?>
		<div class="ww-pros">
			<h4><?php esc_html_e( 'Pros', 'wagerwise' ); ?></h4>
			<ul>
				<?php foreach ( $pros as $pro ) : ?>
					<li>✓ <?php echo esc_html( $pro ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>
		<?php if ( ! empty( $cons ) ) : ?>
		<div class="ww-cons">
			<h4><?php esc_html_e( 'Cons', 'wagerwise' ); ?></h4>
			<ul>
				<?php foreach ( $cons as $con ) : ?>
					<li>✗ <?php echo esc_html( $con ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Generic ranked-casino query filtered by any single taxonomy term — powers
 * the taxonomy-results block for payment_method/licence/country archives,
 * which (unlike casino_category) don't have their own dedicated block.
 */
function wagerwise_get_casinos_by_taxonomy( string $taxonomy, int $term_id, int $number = 24 ): array {
	return get_posts( array(
		'post_type'      => 'casino',
		'posts_per_page' => $number,
		'meta_key'       => 'ww_rating',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
		'tax_query'      => array(
			array( 'taxonomy' => $taxonomy, 'field' => 'term_id', 'terms' => $term_id ),
		),
	) );
}

/**
 * The casino's best-value bonus (its most recently published linked bonus),
 * used to show a bonus-highlight line on casino cards — real linked data,
 * not a fabricated headline.
 */
function wagerwise_get_first_bonus_for_casino( int $casino_id ): ?WP_Post {
	$bonuses = get_posts( array(
		'post_type'      => 'bonus',
		'posts_per_page' => 1,
		'meta_key'       => 'ww_related_casino',
		'meta_value'     => $casino_id,
	) );
	return $bonuses[0] ?? null;
}

/**
 * Real casino logo if one's been uploaded as the featured image, otherwise a
 * deterministic colored initial-letter badge (same idea as Gmail/Slack's
 * fallback avatars) instead of the plain unstyled empty space that used to
 * show through the card's placeholder-texture background. None of the ~50
 * real casino reviews have a logo uploaded yet — this is a stand-in until
 * they do, not a replacement for real artwork.
 *
 * The color is keyed off the casino's ENGLISH translation ID rather than
 * $casino_id itself, so the same casino gets the same color on every
 * language's page rather than a different color per language (each
 * translation is a separate post with a different ID).
 */
function wagerwise_casino_logo_html( int $casino_id, string $image_size = 'medium' ): string {
	$thumbnail = get_the_post_thumbnail( $casino_id, $image_size );
	if ( $thumbnail ) {
		return $thumbnail;
	}

	$title = get_the_title( $casino_id );
	if ( ! $title ) {
		return '';
	}

	$color_seed_id = $casino_id;
	if ( function_exists( 'pll_get_post' ) ) {
		$en_id = pll_get_post( $casino_id, 'en' );
		if ( $en_id ) {
			$color_seed_id = $en_id;
		}
	}

	$palette = array( '#22C567', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316' );
	$color   = $palette[ $color_seed_id % count( $palette ) ];
	$letter  = mb_strtoupper( mb_substr( trim( $title ), 0, 1 ) );

	return '<span class="ww-casino-logo-fallback" style="background:' . esc_attr( $color ) . ';" aria-hidden="true">' . esc_html( $letter ) . '</span>';
}

/**
 * Ranked casino query, reused by the top-casinos block and archive templates.
 *
 * Passes 'lang' explicitly rather than relying on Polylang's automatic
 * per-request query filtering: that filtering keys off Polylang's detected
 * "current language" for the in-flight request, which is only reliably set
 * during a real frontend HTTP request. In other contexts (WP-CLI, cron, a
 * block-editor preview render) it can be empty, and the query silently
 * returns a mix of every language's posts instead of just one — confirmed
 * directly: an unfiltered call returned Czech- and Spanish-language posts
 * for what should have been an English-only result.
 */
function wagerwise_get_top_casinos( int $number = 10, ?int $category_term_id = null, bool $featured_only = false ): array {
	$args = array(
		'post_type'      => 'casino',
		'posts_per_page' => $number,
		'meta_key'       => 'ww_rating',
		'orderby'        => array( 'meta_value_num' => 'DESC', 'title' => 'ASC' ),
	);

	if ( $featured_only ) {
		$args['meta_query'] = array(
			array( 'key' => 'ww_featured', 'value' => '1' ),
		);
	}

	if ( $category_term_id ) {
		$args['tax_query'] = array(
			array( 'taxonomy' => 'casino_category', 'field' => 'term_id', 'terms' => $category_term_id ),
		);
	}

	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language();
		if ( $lang ) {
			$args['lang'] = $lang;
		}
	}

	return get_posts( $args );
}

/**
 * Same ranking/filtering as wagerwise_get_top_casinos(), but paginated and
 * returning the pagination metadata (found_posts/max_num_pages) needed to
 * render real page links — used by the casino archive's top-casinos block
 * instance. Explicitly passes 'lang' rather than relying on Polylang's
 * implicit per-request query filtering, so found_posts/max_num_pages are
 * guaranteed correct for the current language even in contexts where
 * Polylang's current-language detection may not have run yet (e.g. block
 * editor preview, REST render).
 */
function wagerwise_get_top_casinos_paged( int $number, int $paged, ?int $category_term_id = null ): array {
	$args = array(
		'post_type'      => 'casino',
		'posts_per_page' => $number,
		'paged'          => max( 1, $paged ),
		'meta_key'       => 'ww_rating',
		// Tied ratings are common (many casinos share the same value), and
		// MySQL doesn't guarantee a stable order for ties across separate
		// LIMIT/OFFSET queries — without a deterministic tiebreaker, the
		// same casino could appear on two different pages (or get skipped
		// entirely) depending on how ties happened to be ordered on each
		// request. title ASC as a secondary key makes page boundaries
		// consistent, confirmed by comparing page 1 vs page 2 results
		// (previously "BingoPlus" appeared as both the last card on page 1
		// and the first on page 2).
		'orderby'        => array( 'meta_value_num' => 'DESC', 'title' => 'ASC' ),
	);

	if ( $category_term_id ) {
		$args['tax_query'] = array(
			array( 'taxonomy' => 'casino_category', 'field' => 'term_id', 'terms' => $category_term_id ),
		);
	}

	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language();
		if ( $lang ) {
			$args['lang'] = $lang;
		}
	}

	$query = new WP_Query( $args );

	return array(
		'items'         => $query->posts,
		'total'         => (int) $query->found_posts,
		'max_num_pages' => (int) $query->max_num_pages,
	);
}

/**
 * Polylang-aware published-post count for a translated CPT/post type.
 * wp_count_posts() runs a raw SQL query grouped only by post_status — it
 * never goes through WP_Query, so it never picks up Polylang's automatic
 * per-language query filtering (hooked on 'parse_query'), and always counts
 * every language's posts combined. This runs a real (cheap, ids-only)
 * WP_Query instead, so "X casinos reviewed"-style stats match whichever
 * language is currently being viewed.
 */
function wagerwise_count_published_posts( string $post_type ): int {
	$args = array(
		'post_type'      => $post_type,
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	);

	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language();
		if ( $lang ) {
			$args['lang'] = $lang;
		}
	}

	return (int) ( new WP_Query( $args ) )->found_posts;
}

/**
 * Editorial (staff-written) casino reviews — the `review` CPT. Distinct from
 * the star-rating player comments on the casino CPT (includes/reviews.php);
 * this is long-form content with its own permalink.
 */
function wagerwise_get_reviews( int $number = 6, ?int $casino_id = null ): array {
	$args = array(
		'post_type'      => 'review',
		'posts_per_page' => $number,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	if ( $casino_id ) {
		$args['meta_key']   = 'ww_related_casino';
		$args['meta_value'] = $casino_id;
	}

	// See wagerwise_get_top_casinos() for why 'lang' is passed explicitly
	// rather than relying on Polylang's implicit per-request filtering.
	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language();
		if ( $lang ) {
			$args['lang'] = $lang;
		}
	}

	return get_posts( $args );
}
