<?php
/**
 * Page-body markup for the site's composed pages (Home, Guide, News,
 * Reviews, Complaints) — defined once here and used by wp-cli/seed.php so
 * the demo pages start out already composed. Also registered as block
 * patterns so an editor can re-insert a page's default layout later.
 *
 * Each function takes a $lang ('en'/'de'/'zh'/'es') because these are baked once
 * into a language-specific Page's post_content at seed time — unlike the
 * header/footer nav chrome (translated at render time via Polylang string
 * translations), this static heading/paragraph text needs to be correct
 * per-language *when the page is created*.
 */

defined( 'ABSPATH' ) || exit;

/** Look up one UI string for $lang, falling back to the English original. */
function wagerwise_pattern_string( array $strings, string $lang ): string {
	return $strings[ $lang ] ?? $strings['en'];
}

function wagerwise_homepage_pattern_markup( string $lang = 'en' ): string {
	$s = fn( array $strings ) => esc_html( wagerwise_pattern_string( $strings, $lang ) );

	$top_picks   = $s( array( 'en' => 'Top Picks This Week', 'de' => 'Top-Empfehlungen der Woche', 'zh' => '本周精选推荐', 'es' => 'Mejores Opciones de la Semana' ) );
	$live_tourn  = $s( array( 'en' => 'Live Tournaments', 'de' => 'Laufende Turniere', 'zh' => '正在进行的锦标赛', 'es' => 'Torneos en Vivo' ) );
	$why_trust   = $s( array( 'en' => 'Why Trust CasinoRadar', 'de' => 'Warum CasinoRadar Vertrauen', 'zh' => '为何信赖CasinoRadar', 'es' => 'Por Qué Confiar en CasinoRadar' ) );
	$trust_1     = $s( array( 'en' => '✓ Every casino is manually reviewed against our published methodology', 'de' => '✓ Jedes Casino wird manuell nach unserer veröffentlichten Methodik geprüft', 'zh' => '✓ 每家赌场均按我们公开的方法论人工审核', 'es' => '✓ Cada casino es revisado manualmente según nuestra metodología publicada' ) );
	$trust_2     = $s( array( 'en' => '✓ Ratings weigh licensing, payout speed, and bonus fairness', 'de' => '✓ Bewertungen berücksichtigen Lizenzierung, Auszahlungsgeschwindigkeit und faire Boni', 'zh' => '✓ 评级综合考量牌照、提款速度与红利公平性', 'es' => '✓ Las calificaciones consideran la licencia, la velocidad de pago y la equidad de los bonos' ) );
	$trust_3     = $s( array( 'en' => '✓ Licences are cross-checked against MGA, UKGC, and Curaçao registries', 'de' => '✓ Lizenzen werden mit MGA-, UKGC- und Curaçao-Registern abgeglichen', 'zh' => '✓ 牌照信息与MGA、UKGC及库拉索登记系统交叉核实', 'es' => '✓ Las licencias se verifican con los registros de MGA, UKGC y Curazao' ) );
	$trust_4     = $s( array( 'en' => '✓ Reviews are revisited as operators change their terms', 'de' => '✓ Testberichte werden aktualisiert, sobald Anbieter ihre Bedingungen ändern', 'zh' => '✓ 运营商条款变更时，评测将同步更新', 'es' => '✓ Las reseñas se actualizan cuando los operadores cambian sus condiciones' ) );
	$latest_news = $s( array( 'en' => 'Latest News', 'de' => 'Aktuelle Neuigkeiten', 'zh' => '最新资讯', 'es' => 'Últimas Noticias' ) );

	return <<<HTML
<!-- wp:wagerwise/hero-search /-->

<!-- wp:group {"className":"ww-section ww-section--tight","layout":{"type":"constrained"}} -->
<div class="wp-block-group ww-section ww-section--tight">
	<!-- wp:wagerwise/category-strip {"style":"chip"} /-->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"ww-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group ww-section">
	<!-- wp:heading {"level":2} -->
	<h2>{$top_picks}</h2>
	<!-- /wp:heading -->
	<!-- wp:wagerwise/top-casinos {"number":3,"featuredOnly":true,"layout":"grid"} /-->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"ww-section ww-section--alt","layout":{"type":"constrained"}} -->
<div class="wp-block-group ww-section ww-section--alt">
	<!-- wp:heading {"level":2} -->
	<h2>{$live_tourn}</h2>
	<!-- /wp:heading -->
	<!-- wp:wagerwise/tournament-grid {"number":3} /-->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"ww-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group ww-section">
	<!-- wp:columns -->
	<div class="wp-block-columns">
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":2} -->
			<h2>{$why_trust}</h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p>{$trust_1}</p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph -->
			<p>{$trust_2}</p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph -->
			<p>{$trust_3}</p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph -->
			<p>{$trust_4}</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":2} -->
			<h2>{$latest_news}</h2>
			<!-- /wp:heading -->
			<!-- wp:wagerwise/news-grid {"number":2} /-->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
HTML;
}

function wagerwise_guide_page_markup( string $lang = 'en' ): string {
	$s = fn( array $strings ) => esc_html( wagerwise_pattern_string( $strings, $lang ) );

	$h1      = $s( array( 'en' => 'Player Guide', 'de' => 'Spieler-Ratgeber', 'zh' => '玩家指南', 'es' => 'Guía del Jugador' ) );
	$subtitle = $s( array( 'en' => 'Straightforward explainers, no jargon, no upsell.', 'de' => 'Klare Erklärungen, ohne Fachjargon, ohne Verkaufsdruck.', 'zh' => '简单明了的说明，没有行话，没有推销。', 'es' => 'Explicaciones claras, sin jerga, sin ventas encubiertas.' ) );

	return <<<HTML
<!-- wp:group {"className":"ww-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group ww-section">
	<!-- wp:heading {"level":1} -->
	<h1>{$h1}</h1>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"className":"ww-page-subtitle"} -->
	<p class="ww-page-subtitle">{$subtitle}</p>
	<!-- /wp:paragraph -->
	<!-- wp:wagerwise/guide-featured /-->
	<!-- wp:wagerwise/guide-list {"number":8} /-->
</div>
<!-- /wp:group -->
HTML;
}

function wagerwise_news_page_markup( string $lang = 'en' ): string {
	return <<<'HTML'
<!-- wp:group {"className":"ww-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group ww-section">
	<!-- wp:wagerwise/news-featured /-->
	<!-- wp:wagerwise/news-grid {"number":6,"skipFirst":true} /-->
</div>
<!-- /wp:group -->
HTML;
}

function wagerwise_reviews_page_markup( string $lang = 'en' ): string {
	$s = fn( array $strings ) => esc_html( wagerwise_pattern_string( $strings, $lang ) );

	$h1        = $s( array( 'en' => 'Player Reviews', 'de' => 'Spielerbewertungen', 'zh' => '玩家评测', 'es' => 'Reseñas de Jugadores' ) );
	$subtitle  = $s( array( 'en' => 'Independent editorial reviews, plus real player reviews published after moderation.', 'de' => 'Unabhängige Redaktions-Testberichte sowie echte Spielerbewertungen, veröffentlicht nach Moderation.', 'zh' => '独立编辑评测，以及经审核后发布的真实玩家评价。', 'es' => 'Reseñas editoriales independientes, además de reseñas reales de jugadores publicadas tras moderación.' ) );
	$editors   = $s( array( 'en' => "Editor's Reviews", 'de' => 'Redaktionelle Testberichte', 'zh' => '编辑评测', 'es' => 'Reseñas Editoriales' ) );
	$players   = $s( array( 'en' => 'Player Reviews', 'de' => 'Spielerbewertungen', 'zh' => '玩家评价', 'es' => 'Reseñas de Jugadores' ) );

	return <<<HTML
<!-- wp:group {"className":"ww-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group ww-section">
	<!-- wp:heading {"level":1} -->
	<h1>{$h1}</h1>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"className":"ww-page-subtitle"} -->
	<p class="ww-page-subtitle">{$subtitle}</p>
	<!-- /wp:paragraph -->
	<!-- wp:heading {"level":2} -->
	<h2>{$editors}</h2>
	<!-- /wp:heading -->
	<!-- wp:wagerwise/review-grid {"number":6} /-->
	<!-- wp:heading {"level":2} -->
	<h2>{$players}</h2>
	<!-- /wp:heading -->
	<!-- wp:wagerwise/reviews-list {"number":12} /-->
</div>
<!-- /wp:group -->
HTML;
}

function wagerwise_complaints_page_markup( string $lang = 'en' ): string {
	$s = fn( array $strings ) => esc_html( wagerwise_pattern_string( $strings, $lang ) );

	$h1        = $s( array( 'en' => 'Complaint Resolution Center', 'de' => 'Beschwerde-Schlichtungsstelle', 'zh' => '投诉解决中心', 'es' => 'Centro de Resolución de Quejas' ) );
	$subtitle  = $s( array( 'en' => 'Free mediation between players and licensed casinos.', 'de' => 'Kostenlose Vermittlung zwischen Spielern und lizenzierten Casinos.', 'zh' => '为玩家与持牌赌场提供免费调解服务。', 'es' => 'Mediación gratuita entre jugadores y casinos con licencia.' ) );
	$file_h    = $s( array( 'en' => 'File a Complaint', 'de' => 'Beschwerde Einreichen', 'zh' => '提交投诉', 'es' => 'Presentar una Queja' ) );
	$resolved_h = $s( array( 'en' => 'Recently Resolved', 'de' => 'Kürzlich Gelöst', 'zh' => '近期已解决', 'es' => 'Resueltas Recientemente' ) );

	return <<<HTML
<!-- wp:group {"className":"ww-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group ww-section">
	<!-- wp:heading {"level":1} -->
	<h1>{$h1}</h1>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"className":"ww-page-subtitle"} -->
	<p class="ww-page-subtitle">{$subtitle}</p>
	<!-- /wp:paragraph -->
	<!-- wp:wagerwise/complaints-stats /-->
	<!-- wp:columns -->
	<div class="wp-block-columns">
		<!-- wp:column {"className":"ww-complaint-form-col"} -->
		<div class="wp-block-column ww-complaint-form-col" id="complaint-form">
			<!-- wp:heading {"level":2} -->
			<h2>{$file_h}</h2>
			<!-- /wp:heading -->
			<!-- wp:wagerwise/complaint-form /-->
		</div>
		<!-- /wp:column -->
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":2} -->
			<h2>{$resolved_h}</h2>
			<!-- /wp:heading -->
			<!-- wp:wagerwise/complaints-list {"number":6} /-->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
HTML;
}

add_action( 'init', 'wagerwise_register_patterns' );

function wagerwise_register_patterns(): void {
	$lang = function_exists( 'pll_current_language' ) ? ( pll_current_language() ?: 'en' ) : 'en';
	$patterns = array(
		'homepage-sections' => array( __( 'WagerWise: Homepage Sections', 'wagerwise' ), 'wagerwise_homepage_pattern_markup' ),
		'guide-page'        => array( __( 'WagerWise: Guide Page', 'wagerwise' ), 'wagerwise_guide_page_markup' ),
		'news-page'         => array( __( 'WagerWise: News Page', 'wagerwise' ), 'wagerwise_news_page_markup' ),
		'reviews-page'      => array( __( 'WagerWise: Reviews Page', 'wagerwise' ), 'wagerwise_reviews_page_markup' ),
		'complaints-page'   => array( __( 'WagerWise: Complaints Page', 'wagerwise' ), 'wagerwise_complaints_page_markup' ),
	);
	foreach ( $patterns as $slug => [ $title, $fn ] ) {
		register_block_pattern(
			'wagerwise/' . $slug,
			array(
				'title'      => $title,
				'categories' => array( 'wagerwise' ),
				'content'    => $fn( $lang ),
			)
		);
	}
}

add_action( 'init', 'wagerwise_register_pattern_category' );

function wagerwise_register_pattern_category(): void {
	register_block_pattern_category( 'wagerwise', array( 'label' => __( 'WagerWise', 'wagerwise' ) ) );
}
