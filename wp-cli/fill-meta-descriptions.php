<?php
/**
 * One-time: fills Rank Math's `rank_math_description` meta for every Page
 * and every taxonomy term, in EN/DE/ZH. Run via
 * `wp eval-file wp-cli/fill-meta-descriptions.php`.
 *
 * Rank Math's own %excerpt%-based per-post-type templates cover the CPT
 * singles (casino/bonus/game/etc.) reasonably, but Pages have no excerpt
 * mechanism and taxonomy terms have no description set at all — both were
 * rendering with an empty meta description. Safe to re-run: only fills
 * meta that's currently empty, never overwrites a manually-edited one.
 */

defined( 'ABSPATH' ) || exit;

function ww_meta_desc_set_post( int $post_id, string $desc ): void {
	if ( get_post_meta( $post_id, 'rank_math_description', true ) ) {
		return; // don't clobber a manual edit
	}
	update_post_meta( $post_id, 'rank_math_description', $desc );
}

function ww_meta_desc_set_term( int $term_id, string $desc ): void {
	if ( get_term_meta( $term_id, 'rank_math_description', true ) ) {
		return;
	}
	update_term_meta( $term_id, 'rank_math_description', $desc );
}

/** Strips the stray zero-width characters wp_insert_term() appended to some translated term names to dodge slug collisions. */
function ww_clean_term_name( string $name ): string {
	return trim( preg_replace( '/[\x{200B}\x{FEFF}]/u', '', $name ) );
}

// -----------------------------------------------------------------------------
// Pages — hand-written per page, per language.
// -----------------------------------------------------------------------------

$page_descriptions = array(
	'home'                     => array(
		'en' => 'Verified casino bonuses, payout-speed rankings, and honest reviews — CasinoRadar helps players find licensed online casinos worth their time.',
		'de' => 'Geprüfte Casino-Boni, Auszahlungsgeschwindigkeit im Ranking und ehrliche Testberichte — CasinoRadar hilft Spielern, lizenzierte Online-Casinos zu finden.',
		'zh' => '经核实的赌场红利、提款速度排名和真实评测——CasinoRadar帮助玩家找到值得信赖的合法在线赌场。',
	),
	'guide'                    => array(
		'en' => 'Straightforward, jargon-free guides on bankroll management, game strategy, bonus terms, and choosing a safe online casino.',
		'de' => 'Klare, verständliche Ratgeber zu Bankroll-Management, Spielstrategie, Bonusbedingungen und der Wahl eines sicheren Online-Casinos.',
		'zh' => '简单明了、没有行话的指南，涵盖资金管理、游戏策略、红利条款以及如何选择安全的在线赌场。',
	),
	'news'                     => array(
		'en' => 'The latest online casino industry news — regulation changes, new licenses, product launches, and responsible gambling updates.',
		'de' => 'Die neuesten Nachrichten aus der Online-Casino-Branche — Regulierungsänderungen, neue Lizenzen, Produkteinführungen und Updates zum verantwortungsvollen Spielen.',
		'zh' => '最新的在线赌场行业资讯——监管变化、新牌照、产品发布及负责任博彩动态。',
	),
	'reviews'                  => array(
		'en' => 'Independent editorial casino reviews plus real player reviews, published after moderation — see what CasinoRadar and real players think.',
		'de' => 'Unabhängige redaktionelle Casino-Testberichte sowie echte Spielerbewertungen nach Moderation — erfahren Sie, was CasinoRadar und echte Spieler denken.',
		'zh' => '独立编辑赌场评测，加上经审核发布的真实玩家评价——了解CasinoRadar和真实玩家的看法。',
	),
	'complaints'               => array(
		'en' => 'Free mediation between players and licensed casinos — file a complaint or browse cases CasinoRadar has already helped resolve.',
		'de' => 'Kostenlose Vermittlung zwischen Spielern und lizenzierten Casinos — reichen Sie eine Beschwerde ein oder sehen Sie bereits gelöste Fälle.',
		'zh' => '为玩家与持牌赌场提供免费调解服务——提交投诉，或查看CasinoRadar已协助解决的案例。',
	),
	'about-us'                 => array(
		'en' => "CasinoRadar is an independent casino review site helping players find trustworthy, well-regulated online casinos through honest research.",
		'de' => 'CasinoRadar ist eine unabhängige Casino-Bewertungsseite, die Spielern hilft, vertrauenswürdige, gut regulierte Online-Casinos zu finden.',
		'zh' => 'CasinoRadar是一家独立的赌场评测网站，通过诚实调研帮助玩家找到值得信赖、监管完善的在线赌场。',
	),
	'how-we-review-casinos'    => array(
		'en' => "CasinoRadar's review methodology: licensing, withdrawal speed, bonus fairness, game library breadth, and customer support quality.",
		'de' => 'Die Bewertungsmethodik von CasinoRadar: Lizenzierung, Auszahlungsgeschwindigkeit, faire Boni, Spielauswahl und Qualität des Kundensupports.',
		'zh' => 'CasinoRadar的评测方法：牌照资质、提款速度、红利公平性、游戏库丰富程度以及客服质量。',
	),
	'responsible-gambling'     => array(
		'en' => 'Gambling should be fun, not a way to make money. Resources and guidance from CasinoRadar for players who need support. Must be 18+.',
		'de' => 'Glücksspiel sollte Spaß machen, kein Weg, um Geld zu verdienen. Hilfe und Ressourcen von CasinoRadar für Spieler, die Unterstützung brauchen. Nur ab 18.',
		'zh' => '博彩应以娱乐为目的，而非赚钱手段。CasinoRadar为需要帮助的玩家提供资源与指引。仅限18岁以上人士。',
	),
	'privacy-policy'           => array(
		'en' => "CasinoRadar's privacy policy — how we collect, use, and protect your information when you use this site.",
		'de' => 'Die Datenschutzerklärung von CasinoRadar — wie wir Ihre Informationen erheben, verwenden und schützen.',
		'zh' => 'CasinoRadar的隐私政策——我们如何收集、使用和保护您的信息。',
	),
	'terms-affiliate-disclosure' => array(
		'en' => "CasinoRadar's terms of use and affiliate disclosure — how our commission-based links work and why they never affect our reviews.",
		'de' => 'Nutzungsbedingungen und Partnerprogramm-Offenlegung von CasinoRadar — wie unsere provisionsbasierten Links funktionieren.',
		'zh' => 'CasinoRadar的使用条款与联盟披露——我们的佣金链接如何运作，以及为何这从不影响我们的评测。',
	),
	'contact'                  => array(
		'en' => "Have a question or found an error in a review? Reach CasinoRadar's editorial team — we read every message.",
		'de' => 'Haben Sie eine Frage oder einen Fehler gefunden? Kontaktieren Sie das Redaktionsteam von CasinoRadar — wir lesen jede Nachricht.',
		'zh' => '有任何疑问，或发现评测中的错误？请联系CasinoRadar编辑团队——我们会阅读每一条留言。',
	),
);

$pages = get_posts( array( 'post_type' => 'page', 'posts_per_page' => -1, 'suppress_filters' => true ) );
$filled_pages = 0;
foreach ( $pages as $page ) {
	// Match this page back to its canonical English slug (translations have
	// their own slugs, e.g. 'ratgeber' for 'guide', so resolve via Polylang).
	$en_id = $page->ID;
	if ( function_exists( 'pll_get_post' ) ) {
		$maybe_en = pll_get_post( $page->ID, 'en' );
		if ( $maybe_en ) {
			$en_id = $maybe_en;
		}
	}
	$en_slug = get_post_field( 'post_name', $en_id );
	$lang    = function_exists( 'pll_get_post_language' ) ? ( pll_get_post_language( $page->ID ) ?: 'en' ) : 'en';

	if ( isset( $page_descriptions[ $en_slug ][ $lang ] ) ) {
		ww_meta_desc_set_post( $page->ID, $page_descriptions[ $en_slug ][ $lang ] );
		$filled_pages++;
	}
}
WP_CLI::log( "Pages: filled {$filled_pages} descriptions." );

// -----------------------------------------------------------------------------
// Taxonomy terms — per-taxonomy template with the term's own name substituted.
// -----------------------------------------------------------------------------

$templates = array(
	'casino_category' => array(
		'en' => "%s: compare CasinoRadar's verified picks — licensing, payout speed, and bonus terms reviewed side by side.",
		'de' => '%s: Vergleichen Sie CasinoRadars geprüfte Auswahl — Lizenzierung, Auszahlungsgeschwindigkeit und Bonusbedingungen im direkten Vergleich.',
		'zh' => '%s：比较CasinoRadar核实过的推荐赌场——牌照、提款速度和红利条款一目了然。',
	),
	'software_provider' => array(
		'en' => 'Browse casinos and games powered by %s, reviewed and ranked by CasinoRadar.',
		'de' => 'Entdecken Sie Casinos und Spiele mit %s-Software, bewertet und eingestuft von CasinoRadar.',
		'zh' => '浏览由%s提供技术支持的赌场和游戏，由CasinoRadar评测和排名。',
	),
	'payment_method' => array(
		'en' => 'Find online casinos that accept %s, with verified deposit and withdrawal times.',
		'de' => 'Finden Sie Online-Casinos, die %s akzeptieren, mit geprüften Ein- und Auszahlungszeiten.',
		'zh' => '查找接受%s的在线赌场，附经核实的存取款时间。',
	),
	'bonus_type' => array(
		'en' => 'Every %s offer on CasinoRadar, verified and terms-checked before it\'s listed.',
		'de' => 'Jedes %s-Angebot auf CasinoRadar, geprüft und mit verifizierten Bedingungen gelistet.',
		'zh' => 'CasinoRadar上的每一项%s优惠，均经过核实和条款审查后才会上架。',
	),
	'licence' => array(
		'en' => 'Online casinos licensed by %s, reviewed for trustworthiness and player protection.',
		'de' => 'Online-Casinos mit einer Lizenz von %s, geprüft auf Vertrauenswürdigkeit und Spielerschutz.',
		'zh' => '持有%s牌照的在线赌场，经审核确认其可信度和玩家保护措施。',
	),
	'game_category' => array(
		'en' => 'Play %s for free or for real money — RTP, rules, and top casinos to play at, reviewed by CasinoRadar.',
		'de' => 'Spielen Sie %s kostenlos oder um echtes Geld — RTP, Regeln und die besten Casinos, bewertet von CasinoRadar.',
		'zh' => '免费或真钱试玩%s——RTP、规则以及CasinoRadar评测的顶级赌场推荐。',
	),
	'country' => array(
		'en' => 'Online casinos and bonuses available to players in %s, reviewed for local licensing and payment options.',
		'de' => 'Online-Casinos und Boni für Spieler in %s, geprüft auf lokale Lizenzierung und Zahlungsoptionen.',
		'zh' => '面向%s玩家的在线赌场和红利，已审核当地牌照和支付方式。',
	),
	'guide_category' => array(
		'en' => '%s articles from CasinoRadar\'s editorial team — practical, no-nonsense explainers for players.',
		'de' => '%s-Artikel vom Redaktionsteam von CasinoRadar — praktische, unkomplizierte Erklärungen für Spieler.',
		'zh' => 'CasinoRadar编辑团队撰写的%s文章——为玩家提供实用、简明的说明。',
	),
	'news_category' => array(
		'en' => 'The latest %s news in online gambling, curated by CasinoRadar.',
		'de' => 'Die neuesten %s-Nachrichten aus der Online-Glücksspielbranche, kuratiert von CasinoRadar.',
		'zh' => '由CasinoRadar精心整理的最新%s资讯。',
	),
	'review_category' => array(
		'en' => 'CasinoRadar\'s independent %s — tested and rated by our editorial team.',
		'de' => 'Die unabhängigen %s von CasinoRadar — getestet und bewertet von unserem Redaktionsteam.',
		'zh' => 'CasinoRadar独立%s——由我们的编辑团队测试和评分。',
	),
	'tournament_type' => array(
		'en' => 'Live %s tournaments at licensed casinos, with prize pools and leaderboards tracked by CasinoRadar.',
		'de' => 'Laufende %s-Turniere bei lizenzierten Casinos, mit Preispools und Ranglisten von CasinoRadar.',
		'zh' => '持牌赌场正在进行的%s锦标赛，奖池和排行榜由CasinoRadar实时追踪。',
	),
	'complaint_issue_type' => array(
		'en' => 'Player complaints about %s, mediated and resolved by CasinoRadar\'s complaint resolution team.',
		'de' => 'Spielerbeschwerden zu %s, vermittelt und gelöst vom Beschwerde-Team von CasinoRadar.',
		'zh' => '关于%s的玩家投诉，由CasinoRadar投诉解决团队进行调解和处理。',
	),
);

$filled_terms = 0;
foreach ( $templates as $taxonomy => $lang_templates ) {
	$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) ) {
		continue;
	}
	foreach ( $terms as $term ) {
		$lang = function_exists( 'pll_get_term_language' ) ? ( pll_get_term_language( $term->term_id ) ?: 'en' ) : 'en';
		$tpl  = $lang_templates[ $lang ] ?? $lang_templates['en'];
		$name = ww_clean_term_name( $term->name );
		$desc = sprintf( $tpl, $name );
		ww_meta_desc_set_term( $term->term_id, $desc );
		$filled_terms++;
	}
}
WP_CLI::log( "Taxonomy terms: filled {$filled_terms} descriptions." );

WP_CLI::success( 'Meta descriptions filled for all pages and taxonomy terms.' );
