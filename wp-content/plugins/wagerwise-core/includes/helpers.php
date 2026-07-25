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
 * Ranked casino query, reused by the top-casinos block and archive templates.
 */
function wagerwise_get_top_casinos( int $number = 10, ?int $category_term_id = null, bool $featured_only = false ): array {
	$args = array(
		'post_type'      => 'casino',
		'posts_per_page' => $number,
		'meta_key'       => 'ww_rating',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
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

	return get_posts( $args );
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

	return get_posts( $args );
}
