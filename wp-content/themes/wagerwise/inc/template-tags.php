<?php
defined( 'ABSPATH' ) || exit;

/**
 * Polylang language switcher, gracefully no-ops if Polylang isn't active
 * (e.g. before the first admin visit runs the mu-plugin bootstrap).
 */
function wagerwise_language_switcher(): void {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return;
	}
	$languages = pll_the_languages( array( 'raw' => 1 ) );
	if ( empty( $languages ) ) {
		return;
	}
	echo '<nav class="ww-lang-switcher" aria-label="' . esc_attr__( 'Language switcher', 'wagerwise' ) . '"><ul>';
	foreach ( $languages as $lang ) {
		printf(
			'<li class="%s"><a href="%s">%s</a></li>',
			$lang['current_lang'] ? 'is-active' : '',
			esc_url( $lang['url'] ),
			esc_html( strtoupper( $lang['slug'] ) )
		);
	}
	echo '</ul></nav>';
}

/**
 * Sitewide affiliate/responsible-gambling notice, driven by WagerWise Settings.
 */
function wagerwise_disclaimer_bar(): void {
	$text = get_option( 'ww_site_disclaimer' );
	if ( ! $text ) {
		return;
	}
	echo '<div class="ww-disclaimer-bar"><p>' . esc_html( $text ) . '</p></div>';
}

function wagerwise_footer_social_links(): void {
	$links = array(
		'facebook'  => get_option( 'ww_social_facebook' ),
		'twitter'   => get_option( 'ww_social_twitter' ),
		'instagram' => get_option( 'ww_social_instagram' ),
		'telegram'  => get_option( 'ww_social_telegram' ),
	);
	$links = array_filter( $links );
	if ( empty( $links ) ) {
		return;
	}
	echo '<ul class="ww-social-links">';
	foreach ( $links as $network => $url ) {
		printf( '<li><a href="%s" rel="noopener" target="_blank">%s</a></li>', esc_url( $url ), esc_html( ucfirst( $network ) ) );
	}
	echo '</ul>';
}

/**
 * Optional 18+ age gate, toggled from WagerWise Settings → General.
 * main.js reveals/hides it based on a localStorage flag so it never blocks
 * search-engine crawlers and doesn't require a server round-trip.
 */
add_action( 'wp_body_open', 'wagerwise_age_gate_markup' );

function wagerwise_age_gate_markup(): void {
	if ( ! get_option( 'ww_age_gate_enabled' ) ) {
		return;
	}
	$text = get_option( 'ww_responsible_gambling_text' );
	?>
	<div id="ww-age-gate" class="ww-age-gate" hidden>
		<div class="ww-age-gate__box">
			<h2><?php echo esc_html( wagerwise_pll__( 'Are you 18 or older?' ) ); ?></h2>
			<p><?php echo esc_html( $text ); ?></p>
			<div class="ww-age-gate__actions">
				<button type="button" class="ww-btn ww-btn--primary" data-ww-age-gate="yes"><?php echo esc_html( wagerwise_pll__( "Yes, I'm 18+" ) ); ?></button>
				<a class="ww-btn ww-btn--ghost" href="https://www.google.com"><?php echo esc_html( wagerwise_pll__( 'Leave Site' ) ); ?></a>
			</div>
		</div>
	</div>
	<?php
}

add_action( 'wp_head', 'wagerwise_output_header_scripts' );
function wagerwise_output_header_scripts(): void {
	$scripts = get_option( 'ww_header_scripts' );
	if ( $scripts ) {
		echo $scripts; // phpcs:ignore -- sanitized on save (wp_kses_post / unfiltered_html gate) in settings-page.php
	}
}

add_action( 'wp_footer', 'wagerwise_output_footer_scripts' );
function wagerwise_output_footer_scripts(): void {
	$scripts = get_option( 'ww_footer_scripts' );
	if ( $scripts ) {
		echo $scripts; // phpcs:ignore -- sanitized on save (wp_kses_post / unfiltered_html gate) in settings-page.php
	}
}

/** Header "Join Free" CTA — translated via Polylang string translations. */
function wagerwise_join_free_button(): void {
	printf(
		'<a class="ww-btn ww-btn--primary" href="%s">%s</a>',
		esc_url( wagerwise_lang_archive_url( 'casino' ) ),
		esc_html( wagerwise_pll__( 'Join Free' ) )
	);
}

/** Footer copyright/responsible-gambling line — translated via Polylang string translations. */
function wagerwise_footer_copyright(): void {
	echo '<p class="ww-footer__copyright">' . esc_html( wagerwise_pll__( '© 2026 CasinoRadar. 18+. Play responsibly.' ) ) . '</p>';
}

/**
 * Casino name + star rating row for single-review.html — an editorial
 * `review` post's own rating, independent of the linked casino's aggregate
 * `ww_rating`.
 */
function wagerwise_review_meta(): void {
	$post_id = get_the_ID();
	if ( ! $post_id || 'review' !== get_post_type( $post_id ) ) {
		return;
	}
	$casino_id = (int) get_post_meta( $post_id, 'ww_related_casino', true );
	$rating    = (float) get_post_meta( $post_id, 'ww_rating', true );
	$verdict   = get_post_meta( $post_id, 'ww_verdict', true );
	?>
	<div class="ww-review-meta">
		<?php if ( $casino_id ) : ?>
			<a class="ww-review-meta__casino" href="<?php echo esc_url( get_permalink( $casino_id ) ); ?>"><?php echo esc_html( get_the_title( $casino_id ) ); ?></a>
		<?php endif; ?>
		<?php if ( $rating && function_exists( 'wagerwise_star_rating_html' ) ) : ?>
			<?php echo wagerwise_star_rating_html( $rating ); ?>
		<?php endif; ?>
		<?php if ( $verdict ) : ?>
			<p class="ww-review-meta__verdict"><?php echo esc_html( $verdict ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}
