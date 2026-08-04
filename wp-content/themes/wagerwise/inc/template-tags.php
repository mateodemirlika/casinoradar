<?php
defined( 'ABSPATH' ) || exit;

/**
 * Language switcher — a compact trigger that opens a floating dropdown on
 * desktop / a bottom sheet on mobile, listing every language Polylang has
 * configured. Entirely data-driven from pll_the_languages(): no language
 * names, codes, or flags are hardcoded here, so adding/removing a language
 * in Languages → Languages is all it takes to change what this renders.
 * Gracefully no-ops if Polylang isn't active (e.g. before the first admin
 * visit runs the mu-plugin bootstrap) or only one language is configured.
 */
function wagerwise_language_switcher(): void {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return;
	}
	$languages = pll_the_languages( array( 'raw' => 1 ) );
	if ( count( $languages ) < 2 ) {
		return;
	}

	$current = null;
	foreach ( $languages as $lang ) {
		if ( $lang['current_lang'] ) {
			$current = $lang;
			break;
		}
	}
	$current = $current ?: reset( $languages );

	// A dropdown of 16 (and growing) languages is hard to scan — a search
	// field earns its keep once there are enough entries to make scrolling
	// slower than typing; below that it'd just be a tap target no one uses.
	$show_search  = count( $languages ) > 8;
	$select_label = wagerwise_pll__( 'Select language' );
	?>
	<div class="ww-lang-switcher" data-ww-lang-switcher>
		<button
			type="button"
			class="ww-lang-switcher__trigger"
			aria-haspopup="menu"
			aria-expanded="false"
			aria-controls="ww-lang-menu"
			aria-label="<?php echo esc_attr( $select_label ); ?>"
		>
			<svg class="ww-lang-switcher__globe" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<circle cx="12" cy="12" r="9" />
				<path d="M3 12h18" />
				<path d="M12 3c2.5 2.7 3.8 6 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-6-3.8-9s1.3-6.3 3.8-9Z" />
			</svg>
			<span class="ww-lang-switcher__current"><?php echo esc_html( strtoupper( $current['slug'] ) ); ?></span>
			<svg class="ww-lang-switcher__chevron" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<path d="M6 9l6 6 6-6" />
			</svg>
		</button>

		<div class="ww-lang-switcher__backdrop" data-ww-lang-backdrop></div>

		<div class="ww-lang-switcher__panel">
			<div class="ww-lang-switcher__handle" aria-hidden="true"></div>
			<?php if ( $show_search ) : ?>
				<div class="ww-lang-switcher__search-wrap">
					<svg class="ww-lang-switcher__search-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<circle cx="11" cy="11" r="7" />
						<path d="m21 21-4.3-4.3" />
					</svg>
					<input
						type="search"
						class="ww-lang-switcher__search"
						placeholder="<?php echo esc_attr( wagerwise_pll__( 'Search languages' ) ); ?>"
						aria-label="<?php echo esc_attr( wagerwise_pll__( 'Search languages' ) ); ?>"
						autocomplete="off"
					/>
				</div>
			<?php endif; ?>
			<ul class="ww-lang-switcher__list" id="ww-lang-menu" role="menu" aria-label="<?php echo esc_attr( $select_label ); ?>">
				<?php foreach ( $languages as $lang ) : ?>
					<li
						class="ww-lang-switcher__item<?php echo $lang['current_lang'] ? ' is-active' : ''; ?>"
						role="none"
						data-search="<?php echo esc_attr( mb_strtolower( $lang['name'] . ' ' . $lang['slug'] . ' ' . $lang['locale'] ) ); ?>"
					>
						<a
							href="<?php echo esc_url( $lang['url'] ); ?>"
							role="menuitem"
							lang="<?php echo esc_attr( $lang['slug'] ); ?>"
							<?php echo $lang['current_lang'] ? 'aria-current="true"' : ''; ?>
						>
							<?php if ( ! empty( $lang['flag'] ) ) : ?>
								<img class="ww-lang-switcher__flag" src="<?php echo esc_url( $lang['flag'] ); ?>" alt="" width="20" height="15" loading="lazy" decoding="async" />
							<?php endif; ?>
							<span class="ww-lang-switcher__name"><?php echo esc_html( $lang['name'] ); ?></span>
							<?php if ( $lang['current_lang'] ) : ?>
								<svg class="ww-lang-switcher__check" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<path d="M5 13l4 4L19 7" />
								</svg>
							<?php endif; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( $show_search ) : ?>
				<p class="ww-lang-switcher__empty" hidden><?php echo esc_html( wagerwise_pll__( 'No languages found' ) ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php
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

/**
 * Floating "back to top" button. Markup is always in the DOM (so it works
 * with JS disabled via its plain #-anchor href) — main.js shows/hides and
 * smooth-scrolls it based on scroll position.
 */
add_action( 'wp_footer', 'wagerwise_back_to_top_button' );
function wagerwise_back_to_top_button(): void {
	?>
	<a href="#wp--skip-link--target" id="ww-back-to-top" class="ww-back-to-top" aria-label="<?php echo esc_attr( wagerwise_pll__( 'Back to top' ) ); ?>">
		<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
			<path d="M5 12l7-7 7 7M12 5v14" />
		</svg>
	</a>
	<?php
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
