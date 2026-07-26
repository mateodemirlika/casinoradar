<?php
/**
 * Demo content seeder — run via `wp eval-file wp-cli/seed.php`.
 * Idempotent: every insert is guarded by a "does this already exist" check,
 * so re-running (e.g. after `wp db reset`) is safe.
 *
 * Seeds English, German, and Chinese content and links each language's post/
 * term as a Polylang translation of the others.
 */

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'wagerwise_bootstrap_polylang_languages' ) ) {
	wagerwise_bootstrap_polylang_languages();
}

function ww_seed_lang( int $object_id, string $type = 'post', string $lang = 'en' ): void {
	if ( 'post' === $type && function_exists( 'pll_set_post_language' ) ) {
		pll_set_post_language( $object_id, $lang );
	} elseif ( 'term' === $type && function_exists( 'pll_set_term_language' ) ) {
		pll_set_term_language( $object_id, $lang );
	}
}

function ww_link_post_translations( array $ids ): void {
	if ( function_exists( 'pll_save_post_translations' ) && count( $ids ) > 1 ) {
		pll_save_post_translations( $ids );
	}
}

function ww_link_term_translations( array $ids ): void {
	if ( function_exists( 'pll_save_term_translations' ) && count( $ids ) > 1 ) {
		pll_save_term_translations( $ids );
	}
}

function ww_seed_term( string $taxonomy, string $name, string $lang = 'en' ): int {
	// Matched by name AND language, not name alone — several term names are
	// deliberately identical across languages (shared loanwords like "High
	// Roller", or brand names like "PayPal"), so a name-only match would
	// collapse all three languages onto the same term row instead of giving
	// each language its own row for Polylang to tag and link.
	$candidates = get_terms( array( 'taxonomy' => $taxonomy, 'name' => $name, 'hide_empty' => false ) );
	if ( ! is_wp_error( $candidates ) ) {
		foreach ( $candidates as $candidate ) {
			if ( ! function_exists( 'pll_get_term_language' ) || pll_get_term_language( $candidate->term_id ) === $lang ) {
				return (int) $candidate->term_id;
			}
		}
	}
	// WordPress core refuses two sibling terms with the exact same name in a
	// hierarchical taxonomy (and, for flat taxonomies, anywhere in it) —
	// which collides with the common Polylang scenario of a translated term
	// intentionally sharing text with another language (a loanword like
	// "High Roller", or an unlocalized brand name). Retry with a trailing
	// zero-width space appended (invisible in rendered output) until the
	// name is unique enough for WP to accept; the term's translation link
	// is what actually matters for display, not this internal disambiguator.
	$attempt   = $name;
	$max_tries = 5;
	for ( $try = 0; $try < $max_tries; $try++ ) {
		$result = wp_insert_term( $attempt, $taxonomy );
		if ( ! is_wp_error( $result ) ) {
			$id = (int) $result['term_id'];
			ww_seed_lang( $id, 'term', $lang );
			return $id;
		}
		if ( 'term_exists' !== $result->get_error_code() && ! str_contains( $result->get_error_message(), 'already exists' ) ) {
			WP_CLI::warning( "Failed to create term '{$name}' in '{$taxonomy}': " . $result->get_error_message() );
			return 0;
		}
		$attempt = $name . str_repeat( "\xE2\x80\x8B", $try + 1 ); // U+200B zero-width space
	}
	WP_CLI::warning( "Failed to create term '{$name}' in '{$taxonomy}' after {$max_tries} tries: " . $result->get_error_message() );
	return 0;
}

/**
 * Seeds one term per language from a {lang => name} map, links them as
 * Polylang translations of each other, and returns the map of term IDs.
 */
function ww_seed_term_i18n( string $taxonomy, array $names ): array {
	$ids = array();
	foreach ( $names as $lang => $name ) {
		$id = ww_seed_term( $taxonomy, $name, $lang );
		if ( $id ) {
			$ids[ $lang ] = $id;
		}
	}
	ww_link_term_translations( $ids );
	return $ids;
}

/**
 * Generates a simple branded placeholder logo (GD, no external assets
 * needed) and returns its attachment ID, or 0 on failure.
 */
function ww_seed_placeholder_image( string $label, string $slug, string $hex, string $alt = '' ): int {
	$existing = get_page_by_path( $slug, OBJECT, 'attachment' );
	if ( $existing ) {
		return (int) $existing->ID;
	}
	if ( ! function_exists( 'imagecreatetruecolor' ) ) {
		return 0;
	}

	$w = 400; $h = 240;
	$im = imagecreatetruecolor( $w, $h );
	list( $r, $g, $b ) = sscanf( $hex, '#%02x%02x%02x' );
	$bg   = imagecolorallocate( $im, (int) $r, (int) $g, (int) $b );
	$white = imagecolorallocate( $im, 255, 255, 255 );
	imagefilledrectangle( $im, 0, 0, $w, $h, $bg );

	$font_size = 5;
	$text      = $label;
	$text_w    = imagefontwidth( $font_size ) * strlen( $text );
	$text_h    = imagefontheight( $font_size );
	imagestring( $im, $font_size, (int) ( ( $w - $text_w ) / 2 ), (int) ( ( $h - $text_h ) / 2 ), $text, $white );

	$upload_dir = wp_upload_dir();
	$filename   = $slug . '.png';
	$filepath   = trailingslashit( $upload_dir['path'] ) . $filename;
	imagepng( $im, $filepath );
	imagedestroy( $im );

	$filetype   = wp_check_filetype( $filename, null );
	$attachment = array(
		'post_mime_type' => $filetype['type'],
		'post_title'     => $label,
		'post_name'      => $slug,
		'post_content'   => '',
		'post_status'    => 'inherit',
	);
	$attach_id = wp_insert_attachment( $attachment, $filepath );
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$attach_data = wp_generate_attachment_metadata( $attach_id, $filepath );
	wp_update_attachment_metadata( $attach_id, $attach_data );
	if ( $alt ) {
		update_post_meta( $attach_id, '_wp_attachment_image_alt', $alt );
	}

	return (int) $attach_id;
}

/**
 * Maps a taxonomy term name to search keywords for a contextual stock photo.
 * Falls back to a generic "casino" keyword for anything unmapped.
 */
function ww_image_keywords( string $term ): string {
	static $map = array(
		// Casino categories
		'Crypto Casinos'       => 'bitcoin,cryptocurrency',
		'Live Dealer'          => 'casino,dealer',
		'High Roller'          => 'casino,luxury',
		'Mobile Casinos'       => 'smartphone,casino',
		'New Casinos'          => 'casino,neon',
		// Game categories
		'Slots'                => 'slotmachine',
		'Roulette'             => 'roulette',
		'Blackjack'            => 'blackjack,cards',
		'Video Poker'          => 'poker,cards',
		'Live Games'           => 'casino,dealer',
		'Bingo'                => 'bingo',
		// Guide categories
		'Beginner Guides'      => 'education,book',
		'Bankroll Management'  => 'money,finance',
		'Game Strategy'        => 'chess,strategy',
		'Bonus Terms'          => 'contract,document',
		'Country Guides'       => 'map,travel',
		// News categories
		'Industry'             => 'business,office',
		'Product Launch'       => 'technology,startup',
		'Responsible Gambling' => 'support,help',
		'Regulation'           => 'law,government',
		// Tournament types
		'Slot Race'            => 'slotmachine,race',
		'Table Game Ladder'    => 'poker,cards',
		'Free Roll'            => 'casino,chips',
		'Loyalty Leaderboard'  => 'trophy,vip',
	);
	return $map[ $term ] ?? 'casino';
}

/**
 * Downloads a real, contextually-relevant photo (keyword-matched via
 * LoremFlickr's free keyword search, no API key required) and imports it
 * into the media library as a proper attachment. `lock` pins the same
 * keyword+slug combination to the same photo on re-runs. Falls back to the
 * plain generated-colour placeholder if the network/download fails, so
 * seeding never hard-fails on a flaky connection.
 */
function ww_seed_contextual_image( string $label, string $slug, string $keywords, string $fallback_hex, string $alt = '' ): int {
	$existing = get_page_by_path( $slug, OBJECT, 'attachment' );
	if ( $existing ) {
		return (int) $existing->ID;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$url = 'https://loremflickr.com/800/600/' . rawurlencode( $keywords ) . '?lock=' . abs( crc32( $slug ) );
	$tmp = download_url( $url, 15 );

	if ( ! is_wp_error( $tmp ) ) {
		$attach_id = media_handle_sideload( array( 'name' => $slug . '.jpg', 'tmp_name' => $tmp ), 0, $label );
		if ( ! is_wp_error( $attach_id ) ) {
			wp_update_post( array( 'ID' => $attach_id, 'post_name' => $slug ) );
			update_post_meta( $attach_id, '_ww_real_photo', 1 );
			if ( $alt ) {
				update_post_meta( $attach_id, '_wp_attachment_image_alt', $alt );
			}
			return (int) $attach_id;
		}
		@unlink( $tmp );
		WP_CLI::warning( "contextual image sideload failed for '{$slug}': " . $attach_id->get_error_message() );
	} else {
		WP_CLI::warning( "contextual image download failed for '{$slug}': " . $tmp->get_error_message() . ' — using placeholder instead' );
	}

	return ww_seed_placeholder_image( $label, $slug, $fallback_hex, $alt );
}

function ww_seed_post( array $args, array $meta = array(), array $tax = array(), int $thumbnail_id = 0, string $lang = 'en' ): int {
	// Matched by slug, not title — casino/game names and complaint case
	// numbers are deliberately identical across EN/DE/ZH (proper nouns), so a
	// title-based lookup would collapse all three language posts into one.
	// post_name is unique per post regardless of language.
	$existing = get_posts( array(
		'post_type'      => $args['post_type'],
		'name'           => $args['post_name'] ?? '',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( empty( $existing ) && empty( $args['post_name'] ) ) {
		// Fall back to a title match only when no explicit slug was given
		// (e.g. WordPress's own auto-created "Privacy Policy" draft page).
		$existing = get_posts( array(
			'post_type'      => $args['post_type'],
			'title'          => $args['post_title'],
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		) );
	}
	if ( ! empty( $existing ) ) {
		$existing_id = (int) $existing[0];
		// WordPress core auto-creates a draft "Privacy Policy" page on
		// install; adopt (and publish) it rather than skipping, so the seed
		// still results in a live page instead of a 404.
		if ( 'publish' !== get_post_status( $existing_id ) && 'publish' === ( $args['post_status'] ?? '' ) ) {
			wp_update_post( array( 'ID' => $existing_id, 'post_status' => 'publish', 'post_content' => $args['post_content'] ?? '' ) );
		}
		// Backfill a missing thumbnail on re-runs (e.g. a related casino's
		// image wasn't ready yet on an earlier run) — never overrides one
		// that's already set, so an editor's deliberate change is untouched.
		if ( $thumbnail_id && ! get_post_thumbnail_id( $existing_id ) ) {
			set_post_thumbnail( $existing_id, $thumbnail_id );
		}
		return $existing_id;
	}

	$post_id = wp_insert_post( $args );
	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return 0;
	}

	// Set the post's language BEFORE assigning taxonomy terms — Polylang's
	// set_object_terms hook translates whatever term you assign into the
	// equivalent for the post's *current* language, defaulting to the site's
	// default language if the post isn't tagged yet. Tagging late silently
	// rewrote every non-English post's terms back to the English term.
	ww_seed_lang( $post_id, 'post', $lang );

	foreach ( $meta as $key => $value ) {
		update_post_meta( $post_id, $key, $value );
	}
	foreach ( $tax as $taxonomy => $terms ) {
		wp_set_object_terms( $post_id, $terms, $taxonomy );
	}
	if ( $thumbnail_id ) {
		set_post_thumbnail( $post_id, $thumbnail_id );
	}

	return $post_id;
}

/**
 * Seeds the same logical post in EN/DE/ZH from a {lang => [args, meta, tax]}
 * map, shares one thumbnail across all three, links them as Polylang
 * translations, and returns the map of post IDs keyed by language.
 */
function ww_seed_post_i18n( array $per_lang, int $thumbnail_id = 0 ): array {
	$ids        = array();
	$slugs_used = array(); // lang => post_name, for the collision check below

	foreach ( $per_lang as $lang => $spec ) {
		$args = $spec['args'];

		// Only disambiguate when this language's slug actually collides
		// with another language already processed in this same call — most
		// translated titles are already distinct, so most content keeps a
		// clean, unsuffixed slug. This still catches the real collision
		// case: two translated titles that happen to sanitize to the same
		// string (e.g. "75% Reload Bonus" vs. "75% Reload-Bonus" both
		// become "75-reload-bonus"), which would otherwise make
		// ww_seed_post() silently reuse (and relanguage) another
		// language's post instead of creating a new one.
		if ( ! empty( $args['post_name'] ) && in_array( $args['post_name'], $slugs_used, true ) && ! str_ends_with( $args['post_name'], "-{$lang}" ) ) {
			$args['post_name'] .= "-{$lang}";
		}
		$slugs_used[ $lang ] = $args['post_name'] ?? '';

		$id = ww_seed_post( $args, $spec['meta'] ?? array(), $spec['tax'] ?? array(), $thumbnail_id, $lang );
		if ( $id ) {
			$ids[ $lang ] = $id;
		}
	}
	ww_link_post_translations( $ids );
	return $ids;
}

// -----------------------------------------------------------------------------
// Taxonomy terms (EN/DE/ZH)
// -----------------------------------------------------------------------------

function ww_terms_map( string $taxonomy, array $rows ): array {
	$out = array();
	foreach ( $rows as $key => $names ) {
		$out[ $key ] = ww_seed_term_i18n( $taxonomy, $names );
	}
	return $out;
}

/** Re-index a {name => [lang => term_id]} map into a plain 0-based list of term_ids for one language. */
function ww_lang_ids( array $terms_map, string $lang ): array {
	return array_values( array_map( fn( $row ) => $row[ $lang ] ?? $row['en'], $terms_map ) );
}

$payment_methods = ww_terms_map( 'payment_method', array(
	'Visa'       => array( 'en' => 'Visa', 'de' => 'Visa', 'zh' => 'Visa' ),
	'Mastercard' => array( 'en' => 'Mastercard', 'de' => 'Mastercard', 'zh' => 'Mastercard' ),
	'Skrill'     => array( 'en' => 'Skrill', 'de' => 'Skrill', 'zh' => 'Skrill' ),
	'Neteller'   => array( 'en' => 'Neteller', 'de' => 'Neteller', 'zh' => 'Neteller' ),
	'PayPal'     => array( 'en' => 'PayPal', 'de' => 'PayPal', 'zh' => 'PayPal' ),
	'Bitcoin'    => array( 'en' => 'Bitcoin', 'de' => 'Bitcoin', 'zh' => '比特币' ),
) );

$providers = ww_terms_map( 'software_provider', array(
	'NetEnt'           => array( 'en' => 'NetEnt', 'de' => 'NetEnt', 'zh' => 'NetEnt' ),
	'Microgaming'      => array( 'en' => 'Microgaming', 'de' => 'Microgaming', 'zh' => 'Microgaming' ),
	'Pragmatic Play'   => array( 'en' => 'Pragmatic Play', 'de' => 'Pragmatic Play', 'zh' => 'Pragmatic Play' ),
	'Evolution Gaming' => array( 'en' => 'Evolution Gaming', 'de' => 'Evolution Gaming', 'zh' => 'Evolution Gaming' ),
	"Play'n GO"        => array( 'en' => "Play'n GO", 'de' => "Play'n GO", 'zh' => "Play'n GO" ),
	'Yggdrasil'        => array( 'en' => 'Yggdrasil', 'de' => 'Yggdrasil', 'zh' => 'Yggdrasil' ),
	'Novomatic'        => array( 'en' => 'Novomatic', 'de' => 'Novomatic', 'zh' => 'Novomatic' ),
	'Red Tiger'        => array( 'en' => 'Red Tiger', 'de' => 'Red Tiger', 'zh' => 'Red Tiger' ),
) );

$bonus_types = ww_terms_map( 'bonus_type', array(
	'Welcome Bonus' => array( 'en' => 'Welcome Bonus', 'de' => 'Willkommensbonus', 'zh' => '欢迎红利' ),
	'No Deposit'    => array( 'en' => 'No Deposit', 'de' => 'Ohne Einzahlung', 'zh' => '免存款红利' ),
	'Free Spins'    => array( 'en' => 'Free Spins', 'de' => 'Freispiele', 'zh' => '免费旋转' ),
	'Cashback'      => array( 'en' => 'Cashback', 'de' => 'Cashback', 'zh' => '返现' ),
	'Reload Bonus'  => array( 'en' => 'Reload Bonus', 'de' => 'Reload-Bonus', 'zh' => '充值红利' ),
	'High Roller'   => array( 'en' => 'High Roller', 'de' => 'High Roller', 'zh' => '高额投注红利' ),
) );

$licences = ww_terms_map( 'licence', array(
	'Malta Gaming Authority'  => array( 'en' => 'Malta Gaming Authority', 'de' => 'Malta Gaming Authority (MGA)', 'zh' => '马耳他博彩管理局' ),
	'UK Gambling Commission'  => array( 'en' => 'UK Gambling Commission', 'de' => 'Britische Glücksspielkommission (UKGC)', 'zh' => '英国博彩委员会' ),
	'Curaçao eGaming'         => array( 'en' => 'Curaçao eGaming', 'de' => 'Curaçao eGaming', 'zh' => '库拉索博彩牌照' ),
) );

$countries = ww_terms_map( 'country', array(
	'United Kingdom' => array( 'en' => 'United Kingdom', 'de' => 'Vereinigtes Königreich', 'zh' => '英国' ),
	'Canada'         => array( 'en' => 'Canada', 'de' => 'Kanada', 'zh' => '加拿大' ),
	'Germany'        => array( 'en' => 'Germany', 'de' => 'Deutschland', 'zh' => '德国' ),
	'Ireland'        => array( 'en' => 'Ireland', 'de' => 'Irland', 'zh' => '爱尔兰' ),
	'New Zealand'    => array( 'en' => 'New Zealand', 'de' => 'Neuseeland', 'zh' => '新西兰' ),
) );

$categories = ww_terms_map( 'casino_category', array(
	'New Casinos'    => array( 'en' => 'New Casinos', 'de' => 'Neue Casinos', 'zh' => '新赌场' ),
	'Crypto Casinos' => array( 'en' => 'Crypto Casinos', 'de' => 'Krypto-Casinos', 'zh' => '加密货币赌场' ),
	'Live Dealer'    => array( 'en' => 'Live Dealer', 'de' => 'Live-Dealer', 'zh' => '真人荷官' ),
	'High Roller'    => array( 'en' => 'High Roller', 'de' => 'High Roller', 'zh' => '高额玩家' ),
	'Mobile Casinos' => array( 'en' => 'Mobile Casinos', 'de' => 'Mobile Casinos', 'zh' => '移动赌场' ),
) );

$game_categories = ww_terms_map( 'game_category', array(
	'Slots'       => array( 'en' => 'Slots', 'de' => 'Spielautomaten', 'zh' => '老虎机' ),
	'Roulette'    => array( 'en' => 'Roulette', 'de' => 'Roulette', 'zh' => '轮盘' ),
	'Blackjack'   => array( 'en' => 'Blackjack', 'de' => 'Blackjack', 'zh' => '21点' ),
	'Video Poker' => array( 'en' => 'Video Poker', 'de' => 'Video Poker', 'zh' => '视频扑克' ),
	'Live Games'  => array( 'en' => 'Live Games', 'de' => 'Live-Spiele', 'zh' => '真人游戏' ),
	'Bingo'       => array( 'en' => 'Bingo', 'de' => 'Bingo', 'zh' => '宾果' ),
) );

$guide_categories = ww_terms_map( 'guide_category', array(
	'Beginner Guides'      => array( 'en' => 'Beginner Guides', 'de' => 'Einsteiger-Guides', 'zh' => '新手指南' ),
	'Bankroll Management'  => array( 'en' => 'Bankroll Management', 'de' => 'Bankroll-Management', 'zh' => '资金管理' ),
	'Game Strategy'        => array( 'en' => 'Game Strategy', 'de' => 'Spielstrategie', 'zh' => '游戏策略' ),
	'Bonus Terms'          => array( 'en' => 'Bonus Terms', 'de' => 'Bonusbedingungen', 'zh' => '红利条款' ),
	'Country Guides'       => array( 'en' => 'Country Guides', 'de' => 'Länder-Guides', 'zh' => '国家指南' ),
) );

$news_categories = ww_terms_map( 'news_category', array(
	'Industry'              => array( 'en' => 'Industry', 'de' => 'Branche', 'zh' => '行业' ),
	'Product Launch'        => array( 'en' => 'Product Launch', 'de' => 'Produkteinführung', 'zh' => '产品发布' ),
	'Responsible Gambling'  => array( 'en' => 'Responsible Gambling', 'de' => 'Verantwortungsvolles Spielen', 'zh' => '负责任博彩' ),
	'Regulation'            => array( 'en' => 'Regulation', 'de' => 'Regulierung', 'zh' => '监管' ),
) );

$review_categories = ww_terms_map( 'review_category', array(
	'Casino Reviews' => array( 'en' => 'Casino Reviews', 'de' => 'Casino-Testberichte', 'zh' => '赌场评测' ),
	'Game Reviews'   => array( 'en' => 'Game Reviews', 'de' => 'Spiel-Testberichte', 'zh' => '游戏评测' ),
	'Bonus Reviews'  => array( 'en' => 'Bonus Reviews', 'de' => 'Bonus-Testberichte', 'zh' => '红利评测' ),
) );

$tournament_types = ww_terms_map( 'tournament_type', array(
	'Slot Race'          => array( 'en' => 'Slot Race', 'de' => 'Slot-Race', 'zh' => '老虎机竞赛' ),
	'Table Game Ladder'  => array( 'en' => 'Table Game Ladder', 'de' => 'Tischspiel-Rangliste', 'zh' => '桌面游戏排位赛' ),
	'Free Roll'          => array( 'en' => 'Free Roll', 'de' => 'Freeroll', 'zh' => '免费锦标赛' ),
	'Loyalty Leaderboard' => array( 'en' => 'Loyalty Leaderboard', 'de' => 'Treue-Rangliste', 'zh' => '忠诚度排行榜' ),
) );

$issue_types = ww_terms_map( 'complaint_issue_type', array(
	'Withdrawal Delay' => array( 'en' => 'Withdrawal Delay', 'de' => 'Auszahlungsverzögerung', 'zh' => '提款延迟' ),
	'Bonus Dispute'    => array( 'en' => 'Bonus Dispute', 'de' => 'Bonusstreit', 'zh' => '红利争议' ),
	'Account Closure'  => array( 'en' => 'Account Closure', 'de' => 'Kontoschließung', 'zh' => '账户关闭' ),
	'KYC Delay'        => array( 'en' => 'KYC Delay', 'de' => 'KYC-Verzögerung', 'zh' => 'KYC延迟' ),
	'Unfair Terms'     => array( 'en' => 'Unfair Terms', 'de' => 'Unfaire Bedingungen', 'zh' => '不公平条款' ),
) );

// -----------------------------------------------------------------------------
// Casinos (brand names stay the same across languages; copy is translated)
// -----------------------------------------------------------------------------

$casino_defs = array(
	array( 'name' => 'Golden Reel Casino', 'rating' => 4.8, 'year' => 2018, 'colour' => '#00A86B', 'featured' => true, 'category' => 'Crypto Casinos',
		'pros' => array(
			'en' => array( 'Fast crypto withdrawals', '2,000+ slot titles', '24/7 live chat support' ),
			'de' => array( 'Schnelle Krypto-Auszahlungen', 'Über 2.000 Spielautomaten', 'Live-Chat-Support rund um die Uhr' ),
			'zh' => array( '加密货币提款速度快', '超过2000款老虎机游戏', '24/7在线客服支持' ),
		),
		'cons' => array(
			'en' => array( 'High wagering on welcome bonus', 'Limited phone support' ),
			'de' => array( 'Hohe Umsatzanforderungen beim Willkommensbonus', 'Eingeschränkter Telefonsupport' ),
			'zh' => array( '欢迎红利的流水要求较高', '电话支持有限' ),
		),
		'content' => array(
			'en' => "Golden Reel Casino is a well-rounded online casino offering a wide range of slots, table games, and live dealer options. Our team reviewed account creation, payment speed, customer support responsiveness, and bonus terms before assigning a final rating.\n\nOverall, Golden Reel Casino stands out for its game selection and player experience, though as with any operator, always confirm current terms directly with the casino before depositing.",
			'de' => 'Golden Reel Casino überzeugt mit einer großen Auswahl an Spielautomaten, Tischspielen und Live-Dealer-Optionen sowie schnellen Krypto-Auszahlungen. Prüfen Sie vor einer Einzahlung stets die aktuellen Bedingungen direkt beim Anbieter.',
			'zh' => 'Golden Reel Casino提供丰富的老虎机、桌面游戏和真人荷官选项，加密货币提款速度快。存款前请务必核实运营商的最新条款。',
		),
	),
	array( 'name' => 'Royal Spin Palace', 'rating' => 4.6, 'year' => 2015, 'colour' => '#142033', 'featured' => true, 'category' => 'Live Dealer',
		'pros' => array(
			'en' => array( 'Excellent live dealer lobby', 'Generous VIP program', 'UKGC licensed' ),
			'de' => array( 'Exzellente Live-Dealer-Lobby', 'Großzügiges VIP-Programm', 'UKGC-lizenziert' ),
			'zh' => array( '真人荷官大厅体验出色', 'VIP计划丰厚', '持有UKGC牌照' ),
		),
		'cons' => array(
			'en' => array( 'Slower verification process' ),
			'de' => array( 'Langsamerer Verifizierungsprozess' ),
			'zh' => array( '身份验证流程较慢' ),
		),
		'content' => array(
			'en' => "Royal Spin Palace is a well-rounded online casino offering a wide range of slots, table games, and live dealer options. Our team reviewed account creation, payment speed, customer support responsiveness, and bonus terms before assigning a final rating.\n\nOverall, Royal Spin Palace stands out for its game selection and player experience, though as with any operator, always confirm current terms directly with the casino before depositing.",
			'de' => 'Royal Spin Palace punktet mit einer erstklassigen Live-Dealer-Lobby und einem großzügigen VIP-Programm unter britischer Lizenz. Die Kontoverifizierung dauert allerdings etwas länger als bei anderen Anbietern.',
			'zh' => 'Royal Spin Palace拥有出色的真人荷官大厅和丰厚的VIP计划，并持有英国博彩委员会牌照。不过账户验证流程相对较慢。',
		),
	),
	array( 'name' => 'Lucky Vault', 'rating' => 4.5, 'year' => 2020, 'colour' => '#FFB800', 'featured' => true, 'category' => 'Mobile Casinos',
		'pros' => array(
			'en' => array( 'Great mobile app', 'Weekly cashback', 'Low min. deposit' ),
			'de' => array( 'Hervorragende mobile App', 'Wöchentliches Cashback', 'Niedrige Mindesteinzahlung' ),
			'zh' => array( '移动应用体验出色', '每周返现', '最低存款额度低' ),
		),
		'cons' => array(
			'en' => array( 'Smaller game library', 'No phone support' ),
			'de' => array( 'Kleinere Spielauswahl', 'Kein Telefonsupport' ),
			'zh' => array( '游戏库较小', '无电话支持' ),
		),
		'content' => array(
			'en' => "Lucky Vault is a well-rounded online casino offering a wide range of slots, table games, and live dealer options. Our team reviewed account creation, payment speed, customer support responsiveness, and bonus terms before assigning a final rating.\n\nOverall, Lucky Vault stands out for its game selection and player experience, though as with any operator, always confirm current terms directly with the casino before depositing.",
			'de' => 'Lucky Vault überzeugt mit einer hervorragenden mobilen App, wöchentlichem Cashback und einer niedrigen Mindesteinzahlung. Die Spielbibliothek ist im Vergleich etwas kleiner.',
			'zh' => 'Lucky Vault的移动应用体验出色，提供每周返现和较低的最低存款额度，但游戏库相对较小。',
		),
	),
	array( 'name' => 'Diamond Reign', 'rating' => 4.3, 'year' => 2012, 'colour' => '#5B6472', 'featured' => false, 'category' => 'High Roller',
		'pros' => array(
			'en' => array( 'High table limits', 'Dedicated VIP host' ),
			'de' => array( 'Hohe Tischlimits', 'Persönlicher VIP-Betreuer' ),
			'zh' => array( '桌面游戏限额高', '专属VIP客户经理' ),
		),
		'cons' => array(
			'en' => array( 'Fewer slot providers', 'Wagering 45x' ),
			'de' => array( 'Weniger Slot-Anbieter', '45-fache Umsatzanforderung' ),
			'zh' => array( '老虎机供应商较少', '流水要求为45倍' ),
		),
		'content' => array(
			'en' => "Diamond Reign is a well-rounded online casino offering a wide range of slots, table games, and live dealer options. Our team reviewed account creation, payment speed, customer support responsiveness, and bonus terms before assigning a final rating.\n\nOverall, Diamond Reign stands out for its game selection and player experience, though as with any operator, always confirm current terms directly with the casino before depositing.",
			'de' => 'Diamond Reign richtet sich an High Roller mit hohen Tischlimits und einem persönlichen VIP-Betreuer. Die Slot-Auswahl ist etwas kleiner und die Umsatzanforderung mit 45x höher als üblich.',
			'zh' => 'Diamond Reign面向高额玩家，提供高额桌面游戏限额和专属VIP客户经理，但老虎机供应商较少，流水要求为45倍。',
		),
	),
	array( 'name' => 'Fresh Start Casino', 'rating' => 4.1, 'year' => 2023, 'colour' => '#008151', 'featured' => false, 'category' => 'New Casinos',
		'pros' => array(
			'en' => array( 'New player promotions', 'Modern, fast site' ),
			'de' => array( 'Promotionen für neue Spieler', 'Moderne, schnelle Website' ),
			'zh' => array( '新玩家专属优惠', '网站现代化且加载快速' ),
		),
		'cons' => array(
			'en' => array( 'Still building reputation', 'Limited payment options' ),
			'de' => array( 'Noch im Aufbau befindlicher Ruf', 'Eingeschränkte Zahlungsmethoden' ),
			'zh' => array( '品牌口碑仍在建立中', '支付方式有限' ),
		),
		'content' => array(
			'en' => "Fresh Start Casino is a well-rounded online casino offering a wide range of slots, table games, and live dealer options. Our team reviewed account creation, payment speed, customer support responsiveness, and bonus terms before assigning a final rating.\n\nOverall, Fresh Start Casino stands out for its game selection and player experience, though as with any operator, always confirm current terms directly with the casino before depositing.",
			'de' => 'Fresh Start Casino ist ein neuer Anbieter mit attraktiven Promotionen für neue Spieler und einer modernen, schnellen Website. Der Ruf des Casinos befindet sich noch im Aufbau.',
			'zh' => 'Fresh Start Casino是一家新赌场，为新玩家提供优惠，网站界面现代且响应迅速，但品牌口碑仍在建立中。',
		),
	),
	array( 'name' => 'Crimson Ace Casino', 'rating' => 4.4, 'year' => 2019, 'colour' => '#B3283D', 'featured' => true, 'category' => 'Mobile Casinos',
		'pros' => array(
			'en' => array( 'Huge slot library', 'Frequent free spin drops', 'Fast KYC verification' ),
			'de' => array( 'Riesige Slot-Bibliothek', 'Häufige Freispiel-Aktionen', 'Schnelle KYC-Verifizierung' ),
			'zh' => array( '老虎机种类丰富', '免费旋转活动频繁', 'KYC验证速度快' ),
		),
		'cons' => array(
			'en' => array( 'No phone support', 'Withdrawal cap on weekends' ),
			'de' => array( 'Kein Telefonsupport', 'Auszahlungslimit am Wochenende' ),
			'zh' => array( '无电话支持', '周末提款有限额' ),
		),
		'content' => array(
			'en' => "Crimson Ace Casino is a well-rounded online casino offering a wide range of slots, table games, and live dealer options. Our team reviewed account creation, payment speed, customer support responsiveness, and bonus terms before assigning a final rating.\n\nOverall, Crimson Ace Casino stands out for its game selection and player experience, though as with any operator, always confirm current terms directly with the casino before depositing.",
			'de' => 'Crimson Ace Casino bietet eine riesige Slot-Bibliothek, häufige Freispiel-Aktionen und eine schnelle KYC-Verifizierung. Am Wochenende gilt jedoch ein Auszahlungslimit.',
			'zh' => 'Crimson Ace Casino拥有丰富的老虎机种类，免费旋转活动频繁，KYC验证速度快，但周末提款设有限额。',
		),
	),
	array( 'name' => 'Silver Bay Casino', 'rating' => 3.9, 'year' => 2021, 'colour' => '#3C6E71', 'featured' => false, 'category' => 'Live Dealer',
		'pros' => array(
			'en' => array( 'Clean, simple interface', 'Good range of table games' ),
			'de' => array( 'Übersichtliche, einfache Oberfläche', 'Gute Auswahl an Tischspielen' ),
			'zh' => array( '界面简洁清晰', '桌面游戏种类丰富' ),
		),
		'cons' => array(
			'en' => array( 'Average bonus terms', 'Fewer live dealer tables' ),
			'de' => array( 'Durchschnittliche Bonusbedingungen', 'Weniger Live-Dealer-Tische' ),
			'zh' => array( '红利条款一般', '真人荷官桌数量较少' ),
		),
		'content' => array(
			'en' => "Silver Bay Casino is a well-rounded online casino offering a wide range of slots, table games, and live dealer options. Our team reviewed account creation, payment speed, customer support responsiveness, and bonus terms before assigning a final rating.\n\nOverall, Silver Bay Casino stands out for its game selection and player experience, though as with any operator, always confirm current terms directly with the casino before depositing.",
			'de' => 'Silver Bay Casino punktet mit einer übersichtlichen Oberfläche und einer guten Auswahl an Tischspielen. Die Bonusbedingungen sind eher durchschnittlich.',
			'zh' => 'Silver Bay Casino界面简洁，桌面游戏选择丰富，但红利条款一般，真人荷官桌数量较少。',
		),
	),
	array( 'name' => 'Nova Star Casino', 'rating' => 4.7, 'year' => 2017, 'colour' => '#5B3CB3', 'featured' => true, 'category' => 'High Roller',
		'pros' => array(
			'en' => array( 'High table limits for VIPs', 'Personal account manager', 'Same-day payouts' ),
			'de' => array( 'Hohe Tischlimits für VIPs', 'Persönlicher Kundenbetreuer', 'Auszahlung am selben Tag' ),
			'zh' => array( 'VIP桌面游戏限额高', '专属客户经理', '当日到账提款' ),
		),
		'cons' => array(
			'en' => array( 'Steep wagering on reload bonuses' ),
			'de' => array( 'Hohe Umsatzanforderung bei Reload-Boni' ),
			'zh' => array( '充值红利流水要求较高' ),
		),
		'content' => array(
			'en' => "Nova Star Casino is a well-rounded online casino offering a wide range of slots, table games, and live dealer options. Our team reviewed account creation, payment speed, customer support responsiveness, and bonus terms before assigning a final rating.\n\nOverall, Nova Star Casino stands out for its game selection and player experience, though as with any operator, always confirm current terms directly with the casino before depositing.",
			'de' => 'Nova Star Casino bietet VIPs hohe Tischlimits, einen persönlichen Kundenbetreuer und Auszahlungen am selben Tag. Reload-Boni haben allerdings hohe Umsatzanforderungen.',
			'zh' => 'Nova Star Casino为VIP玩家提供高额桌面游戏限额、专属客户经理和当日到账提款，但充值红利的流水要求较高。',
		),
	),
);

$cta_labels = array( 'en' => 'Play Now', 'de' => 'Jetzt Spielen', 'zh' => '立即游玩' );

$casino_ids  = array(); // en id list, index-aligned with $casino_defs, for meta references elsewhere
$casino_ids_i18n = array(); // index => [lang => id]

foreach ( $casino_defs as $i => $c ) {
	$logo_id = ww_seed_contextual_image( $c['name'], sanitize_title( $c['name'] ) . '-logo', ww_image_keywords( $c['category'] ), $c['colour'], $c['name'] . ' logo' );

	$per_lang = array();
	foreach ( array( 'en', 'de', 'zh' ) as $lang ) {
		$payment_method_ids = ww_lang_ids( $payment_methods, $lang );
		$provider_ids       = ww_lang_ids( $providers, $lang );
		$licence_list       = array_values( $licences );
		$country_list       = array_values( $countries );

		$tax = array(
			'casino_category'   => array( $categories[ $c['category'] ][ $lang ] ),
			'software_provider' => array_slice( $provider_ids, $i, 2 ),
			'payment_method'    => array(
				$payment_method_ids[ $i % count( $payment_method_ids ) ],
				$payment_method_ids[ ( $i + 1 ) % count( $payment_method_ids ) ],
				$payment_method_ids[ ( $i + 2 ) % count( $payment_method_ids ) ],
				$payment_method_ids[ ( $i + 3 ) % count( $payment_method_ids ) ],
			),
			'licence' => array( $licence_list[ $i % count( $licence_list ) ][ $lang ] ),
			'country' => array( $country_list[ $i % count( $country_list ) ][ $lang ], $country_list[ ( $i + 1 ) % count( $country_list ) ][ $lang ] ),
		);

		$per_lang[ $lang ] = array(
			'args' => array(
				'post_type'    => 'casino',
				'post_title'   => $c['name'],
				'post_content' => '<p>' . nl2br( esc_html( $c['content'][ $lang ] ) ) . '</p>',
				'post_status'  => 'publish',
				'post_name'    => sanitize_title( $c['name'] ) . ( 'en' === $lang ? '' : '-' . $lang ),
			),
			'meta' => array(
				'ww_rating'               => $c['rating'],
				'ww_year_established'     => $c['year'],
				'ww_min_deposit'          => '$20',
				'ww_wagering_requirement' => '35x',
				'ww_payout_speed'         => '24-48 hours',
				'ww_affiliate_link'       => '#',
				'ww_cta_label'            => $cta_labels[ $lang ],
				'ww_featured'             => $c['featured'] ? 1 : 0,
				'ww_pros'                 => $c['pros'][ $lang ],
				'ww_cons'                 => $c['cons'][ $lang ],
			),
			'tax' => $tax,
		);
	}
	$ids = ww_seed_post_i18n( $per_lang, $logo_id );
	$casino_ids_i18n[ $i ] = $ids;
	$casino_ids[] = $ids['en'] ?? 0;
}

/** Fetch the translated ID of a casino (by index into $casino_defs) for a given language, falling back to English. */
function ww_casino_id( array $casino_ids_i18n, int $index, string $lang ): int {
	$row = $casino_ids_i18n[ $index ] ?? array();
	return (int) ( $row[ $lang ] ?? $row['en'] ?? 0 );
}

// -----------------------------------------------------------------------------
// Bonuses
// -----------------------------------------------------------------------------

$bonus_defs = array(
	array( 'casino' => 0, 'type' => 'Welcome Bonus', 'code' => 'WELCOME500',
		'title' => array( 'en' => '100% Welcome Bonus up to $500', 'de' => '100% Willkommensbonus bis zu 500 $', 'zh' => '100%欢迎红利，最高500美元' ),
		'value' => array( 'en' => '100% up to $500', 'de' => '100% bis 500 $', 'zh' => '100%，最高500美元' ) ),
	array( 'casino' => 1, 'type' => 'Free Spins', 'code' => 'SPIN50',
		'title' => array( 'en' => '50 Free Spins on Sign-Up', 'de' => '50 Freispiele bei Anmeldung', 'zh' => '注册即送50次免费旋转' ),
		'value' => array( 'en' => '50 Free Spins', 'de' => '50 Freispiele', 'zh' => '50次免费旋转' ) ),
	array( 'casino' => 2, 'type' => 'No Deposit', 'code' => 'FREE25',
		'title' => array( 'en' => '$25 No Deposit Bonus', 'de' => '25 $ Bonus ohne Einzahlung', 'zh' => '25美元免存款红利' ),
		'value' => array( 'en' => '$25 Free', 'de' => '25 $ gratis', 'zh' => '25美元免费' ) ),
	array( 'casino' => 3, 'type' => 'Cashback', 'code' => '',
		'title' => array( 'en' => '10% Weekly Cashback', 'de' => '10% wöchentliches Cashback', 'zh' => '每周10%返现' ),
		'value' => array( 'en' => '10% Cashback', 'de' => '10% Cashback', 'zh' => '10%返现' ) ),
	array( 'casino' => 4, 'type' => 'Reload Bonus', 'code' => 'RELOAD75',
		'title' => array( 'en' => '75% Reload Bonus', 'de' => '75% Reload-Bonus', 'zh' => '75%充值红利' ),
		'value' => array( 'en' => '75% up to $200', 'de' => '75% bis 200 $', 'zh' => '75%，最高200美元' ) ),
	array( 'casino' => 5, 'type' => 'Welcome Bonus', 'code' => 'CRYPTO200',
		'title' => array( 'en' => '200% Crypto Deposit Match', 'de' => '200% Krypto-Einzahlungsbonus', 'zh' => '200%加密货币存款红利' ),
		'value' => array( 'en' => '200% up to 1 BTC', 'de' => '200% bis 1 BTC', 'zh' => '200%，最高1个比特币' ) ),
	array( 'casino' => 6, 'type' => 'Free Spins', 'code' => 'ACE30',
		'title' => array( 'en' => '30 Free Spins on Sign-Up', 'de' => '30 Freispiele bei Anmeldung', 'zh' => '注册即送30次免费旋转' ),
		'value' => array( 'en' => '30 Free Spins', 'de' => '30 Freispiele', 'zh' => '30次免费旋转' ) ),
	array( 'casino' => 7, 'type' => 'No Deposit', 'code' => 'TRY10',
		'title' => array( 'en' => '$10 No Deposit Bonus', 'de' => '10 $ Bonus ohne Einzahlung', 'zh' => '10美元免存款红利' ),
		'value' => array( 'en' => '$10 Free', 'de' => '10 $ gratis', 'zh' => '10美元免费' ) ),
	array( 'casino' => 0, 'type' => 'Reload Bonus', 'code' => 'VIP50',
		'title' => array( 'en' => 'VIP 50% Weekly Reload', 'de' => 'VIP 50% wöchentlicher Reload', 'zh' => 'VIP每周50%充值红利' ),
		'value' => array( 'en' => '50% up to $1,000', 'de' => '50% bis 1.000 $', 'zh' => '50%，最高1000美元' ) ),
);

$bonus_terms_summary = array( 'en' => '35x wagering. Min deposit $20. 30-day expiry.', 'de' => '35-fache Umsatzanforderung. Mindesteinzahlung 20 $. Gültig für 30 Tage.', 'zh' => '35倍流水要求。最低存款20美元。30天内有效。' );
$bonus_body_tpl = array(
	'en' => fn( $title ) => $title . ' — available to new players. Standard wagering requirements and game restrictions apply; see the operator\'s terms for full details.',
	'de' => fn( $title ) => $title . ' — für neue Spieler verfügbar. Es gelten die üblichen Umsatzanforderungen und Spielbeschränkungen; Details entnehmen Sie den Bedingungen des Anbieters.',
	'zh' => fn( $title ) => $title . ' — 仅限新玩家使用。适用标准流水要求和游戏限制，详情请参阅运营商条款。',
);

foreach ( $bonus_defs as $i => $b ) {
	$per_lang = array();
	foreach ( array( 'en', 'de', 'zh' ) as $lang ) {
		$title = $b['title'][ $lang ];
		$per_lang[ $lang ] = array(
			'args' => array(
				'post_type'    => 'bonus',
				'post_title'   => $title,
				'post_content' => '<p>' . esc_html( $bonus_body_tpl[ $lang ]( $title ) ) . '</p>',
				'post_status'  => 'publish',
				'post_name'    => sanitize_title( $title ),
			),
			'meta' => array(
				'ww_related_casino' => ww_casino_id( $casino_ids_i18n, $b['casino'], $lang ),
				'ww_bonus_value'    => $b['value'][ $lang ],
				'ww_promo_code'     => $b['code'],
				'ww_terms_summary'  => $bonus_terms_summary[ $lang ],
				'ww_expiry_date'    => gmdate( 'Y-m-d', strtotime( '+60 days' ) ),
				'ww_affiliate_link' => '#',
				'ww_cta_label'      => 'en' === $lang ? 'Claim Bonus' : ( 'de' === $lang ? 'Bonus Sichern' : '领取红利' ),
			),
			'tax' => array( 'bonus_type' => array( $bonus_types[ $b['type'] ][ $lang ] ) ),
		);
	}
	ww_seed_post_i18n( $per_lang, get_post_thumbnail_id( ww_casino_id( $casino_ids_i18n, $b['casino'], 'en' ) ) );
}

// -----------------------------------------------------------------------------
// Games
// -----------------------------------------------------------------------------

$game_defs = array(
	array( 'name' => 'Mega Fortune Reels', 'colour' => '#C97A1F', 'rtp' => '96.4%', 'min_bet' => '$0.10', 'max_win' => '5,000x', 'provider' => 0, 'category' => 'Slots',
		'desc' => array( 'en' => 'Mega Fortune Reels is available to play for free in demo mode, no download or registration required.', 'de' => 'Mega Fortune Reels kann kostenlos im Demomodus gespielt werden — kein Download oder Registrierung nötig.', 'zh' => 'Mega Fortune Reels可免费试玩，无需下载或注册。' ) ),
	array( 'name' => 'Diamond Roulette', 'colour' => '#1F2C39', 'rtp' => '97.3%', 'min_bet' => '$1', 'max_win' => '35x', 'provider' => 3, 'category' => 'Roulette',
		'desc' => array( 'en' => 'Diamond Roulette is available to play for free in demo mode, no download or registration required.', 'de' => 'Diamond Roulette kann kostenlos im Demomodus gespielt werden — kein Download oder Registrierung nötig.', 'zh' => 'Diamond Roulette可免费试玩，无需下载或注册。' ) ),
	array( 'name' => 'Classic Blackjack Pro', 'colour' => '#0F5132', 'rtp' => '99.5%', 'min_bet' => '$1', 'max_win' => '3x', 'provider' => 1, 'category' => 'Blackjack',
		'desc' => array( 'en' => 'Classic Blackjack Pro is available to play for free in demo mode, no download or registration required.', 'de' => 'Classic Blackjack Pro kann kostenlos im Demomodus gespielt werden — kein Download oder Registrierung nötig.', 'zh' => 'Classic Blackjack Pro可免费试玩，无需下载或注册。' ) ),
	array( 'name' => 'Jacks or Better Video Poker', 'colour' => '#7A1FA2', 'rtp' => '99.1%', 'min_bet' => '$0.25', 'max_win' => '800x', 'provider' => 2, 'category' => 'Video Poker',
		'desc' => array( 'en' => 'Jacks or Better Video Poker is available to play for free in demo mode, no download or registration required.', 'de' => 'Jacks or Better Video Poker kann kostenlos im Demomodus gespielt werden — kein Download oder Registrierung nötig.', 'zh' => 'Jacks or Better Video Poker可免费试玩，无需下载或注册。' ) ),
	array( 'name' => 'Live Lightning Baccarat', 'colour' => '#A21F3D', 'rtp' => '98.8%', 'min_bet' => '$0.50', 'max_win' => '250x', 'provider' => 3, 'category' => 'Live Games',
		'desc' => array( 'en' => 'Live Lightning Baccarat is available to play for free in demo mode, no download or registration required.', 'de' => 'Live Lightning Baccarat kann kostenlos im Demomodus gespielt werden — kein Download oder Registrierung nötig.', 'zh' => 'Live Lightning Baccarat可免费试玩，无需下载或注册。' ) ),
	array( 'name' => 'Golden Bingo Hall', 'colour' => '#B3941F', 'rtp' => '95.0%', 'min_bet' => '$0.10', 'max_win' => '1,000x', 'provider' => 5, 'category' => 'Bingo',
		'desc' => array( 'en' => 'Golden Bingo Hall is available to play for free in demo mode, no download or registration required.', 'de' => 'Golden Bingo Hall kann kostenlos im Demomodus gespielt werden — kein Download oder Registrierung nötig.', 'zh' => 'Golden Bingo Hall可免费试玩，无需下载或注册。' ) ),
	array( 'name' => 'Wild Frontier Slots', 'colour' => '#8A4B1F', 'rtp' => '96.1%', 'min_bet' => '$0.20', 'max_win' => '10,000x', 'provider' => 6, 'category' => 'Slots',
		'desc' => array( 'en' => 'Wild Frontier Slots is available to play for free in demo mode, no download or registration required.', 'de' => 'Wild Frontier Slots kann kostenlos im Demomodus gespielt werden — kein Download oder Registrierung nötig.', 'zh' => 'Wild Frontier Slots可免费试玩，无需下载或注册。' ) ),
	array( 'name' => 'Crystal Cascade Megaways', 'colour' => '#1F7A6C', 'rtp' => '96.8%', 'min_bet' => '$0.20', 'max_win' => '20,000x', 'provider' => 7, 'category' => 'Slots',
		'desc' => array( 'en' => 'Crystal Cascade Megaways is available to play for free in demo mode, no download or registration required.', 'de' => 'Crystal Cascade Megaways kann kostenlos im Demomodus gespielt werden — kein Download oder Registrierung nötig.', 'zh' => 'Crystal Cascade Megaways可免费试玩，无需下载或注册。' ) ),
);

foreach ( $game_defs as $g ) {
	$logo_id = ww_seed_contextual_image( $g['name'], sanitize_title( $g['name'] ) . '-thumb', ww_image_keywords( $g['category'] ), $g['colour'], $g['name'] . ' thumbnail' );
	$per_lang = array();
	foreach ( array( 'en', 'de', 'zh' ) as $lang ) {
		$per_lang[ $lang ] = array(
			'args' => array(
				'post_type'    => 'game',
				'post_title'   => $g['name'],
				'post_content' => '<p>' . esc_html( $g['desc'][ $lang ] ) . '</p>',
				'post_status'  => 'publish',
				'post_name'    => sanitize_title( $g['name'] ) . ( 'en' === $lang ? '' : '-' . $lang ),
			),
			'meta' => array(
				'ww_rtp'       => $g['rtp'],
				'ww_min_bet'   => $g['min_bet'],
				'ww_max_win'   => $g['max_win'],
				'ww_demo_link' => '#',
			),
			'tax' => array(
				'software_provider' => array( ww_lang_ids( $providers, $lang )[ $g['provider'] ] ),
				'game_category'     => array( $game_categories[ $g['category'] ][ $lang ] ),
			),
		);
	}
	ww_seed_post_i18n( $per_lang, $logo_id );
}

// -----------------------------------------------------------------------------
// Guides
// -----------------------------------------------------------------------------

$guide_defs = array(
	array( 'cat' => 'Beginner Guides',
		'title' => array( 'en' => 'How to Spot a Fake Casino License in 60 Seconds', 'de' => 'Eine gefälschte Casino-Lizenz in 60 Sekunden erkennen', 'zh' => '60秒识破假冒赌场牌照' ),
		'body'  => array(
			'en' => 'A step-by-step checklist for verifying MGA, UKGC, and Curaçao registrations before you deposit a single dollar. We update this article regularly as regulations and industry practices evolve.',
			'de' => 'Eine Schritt-für-Schritt-Checkliste zur Überprüfung von MGA-, UKGC- und Curaçao-Registrierungen, bevor Sie auch nur einen Euro einzahlen. Wir aktualisieren diesen Artikel regelmäßig.',
			'zh' => '在您存入任何资金之前，逐步核实MGA、UKGC和库拉索牌照的实用清单。我们会定期更新本文内容。',
		) ),
	array( 'cat' => 'Bonus Terms',
		'title' => array( 'en' => 'Understanding Wagering Requirements, With Real Math', 'de' => 'Umsatzanforderungen verstehen — mit echten Rechenbeispielen', 'zh' => '用真实数字读懂流水要求' ),
		'body'  => array(
			'en' => 'A practical, no-nonsense look at understanding wagering requirements, with real math. We update this article regularly as regulations and industry practices evolve.',
			'de' => 'Ein praktischer, unkomplizierter Blick auf Umsatzanforderungen mit konkreten Rechenbeispielen, damit Sie genau wissen, was ein Bonus wirklich kostet.',
			'zh' => '用实际计算示例，直观地解释流水要求究竟意味着什么，帮助您理解红利背后的真实成本。',
		) ),
	array( 'cat' => 'Bankroll Management',
		'title' => array( 'en' => 'Bankroll Management for Slot Players: The 1% Rule', 'de' => 'Bankroll-Management für Slot-Spieler: Die 1%-Regel', 'zh' => '老虎机玩家的资金管理：1%法则' ),
		'body'  => array(
			'en' => 'A practical, no-nonsense look at bankroll management for slot players, the 1% rule. We update this article regularly as regulations and industry practices evolve.',
			'de' => 'Warum viele erfahrene Slot-Spieler nie mehr als 1% ihres Bankrolls auf eine einzelne Session setzen, und wie Sie diese Regel für sich anwenden.',
			'zh' => '许多资深老虎机玩家为何单次游戏投注不超过资金的1%，以及如何将这一法则应用到自己的策略中。',
		) ),
	array( 'cat' => 'Game Strategy',
		'title' => array( 'en' => 'Basic Blackjack Strategy Chart, Explained Simply', 'de' => 'Die grundlegende Blackjack-Strategietabelle, einfach erklärt', 'zh' => '基础21点策略表，简单易懂' ),
		'body'  => array(
			'en' => 'A practical, no-nonsense look at basic blackjack strategy chart, explained simply. We update this article regularly as regulations and industry practices evolve.',
			'de' => 'Die grundlegende Strategietabelle für Blackjack, Schritt für Schritt erklärt — ohne Fachjargon, direkt anwendbar am Tisch.',
			'zh' => '基础21点策略表逐条讲解，无需专业术语，即学即用。',
		) ),
	array( 'cat' => 'Country Guides',
		'title' => array( 'en' => 'Playing from Germany: Taxes, Limits and Licensed Sites', 'de' => 'Spielen aus Deutschland: Steuern, Limits und lizenzierte Anbieter', 'zh' => '德国玩家指南：税务、限额与合法平台' ),
		'body'  => array(
			'en' => 'A practical, no-nonsense look at playing from Germany, taxes, limits and licensed sites. We update this article regularly as regulations and industry practices evolve.',
			'de' => 'Was deutsche Spieler über Steuern, gesetzliche Einzahlungslimits und lizenzierte Anbieter wissen müssen, kompakt zusammengefasst.',
			'zh' => '德国玩家需要了解的税务规定、法定存款限额和合法持牌平台，简明扼要地为您梳理。',
		) ),
	array( 'cat' => 'Beginner Guides',
		'title' => array( 'en' => 'RTP Explained: How Return to Player Actually Works', 'de' => 'RTP erklärt: Wie der Auszahlungsprozentsatz wirklich funktioniert', 'zh' => 'RTP详解：返还率究竟如何运作' ),
		'body'  => array(
			'en' => 'A practical, no-nonsense look at RTP explained, how return to player actually works. We update this article regularly as regulations and industry practices evolve.',
			'de' => 'Was der RTP-Wert wirklich bedeutet, warum er eine langfristige statistische Kennzahl ist und kein Versprechen für eine einzelne Session.',
			'zh' => 'RTP数值真正的含义——它是长期统计指标，而非单次游戏的保证，帮助玩家建立正确预期。',
		) ),
	array( 'cat' => 'Game Strategy',
		'title' => array( 'en' => 'Slot Volatility: High vs. Low Explained', 'de' => 'Slot-Volatilität: Hoch vs. niedrig erklärt', 'zh' => '老虎机波动性：高波动与低波动详解' ),
		'body'  => array(
			'en' => 'A practical, no-nonsense look at slot volatility, high vs. low explained. We update this article regularly as regulations and industry practices evolve.',
			'de' => 'Der Unterschied zwischen hoher und niedriger Volatilität bei Spielautomaten, und welcher Typ zu Ihrem Spielstil passt.',
			'zh' => '高波动性与低波动性老虎机的区别，以及哪种类型更适合您的游戏风格。',
		) ),
	array( 'cat' => 'Beginner Guides',
		'title' => array( 'en' => 'How to Choose a Safe Online Casino in 2026', 'de' => 'So wählen Sie 2026 ein sicheres Online-Casino', 'zh' => '2026年如何选择安全的在线赌场' ),
		'body'  => array(
			'en' => 'A practical, no-nonsense look at how to choose a safe online casino in 2026. We update this article regularly as regulations and industry practices evolve.',
			'de' => 'Eine praktische Checkliste für die Wahl eines sicheren Online-Casinos im Jahr 2026 — Lizenzierung, Auszahlungsgeschwindigkeit und faire Bonusbedingungen.',
			'zh' => '2026年选择安全在线赌场的实用清单——牌照资质、提款速度和公平的红利条款。',
		) ),
);

foreach ( $guide_defs as $g ) {
	$thumb_id = ww_seed_contextual_image( $g['title']['en'], sanitize_title( $g['title']['en'] ) . '-thumb', ww_image_keywords( $g['cat'] ), '#2b3245', $g['title']['en'] );
	$per_lang = array();
	foreach ( array( 'en', 'de', 'zh' ) as $lang ) {
		$title = $g['title'][ $lang ];
		$per_lang[ $lang ] = array(
			'args' => array(
				'post_type'    => 'guide',
				'post_title'   => $title,
				'post_content' => '<p>' . esc_html( $g['body'][ $lang ] ) . '</p>',
				'post_excerpt' => wp_trim_words( $g['body'][ $lang ], 22 ),
				'post_status'  => 'publish',
				'post_name'    => sanitize_title( $title ),
			),
			'tax' => array( 'guide_category' => array( $guide_categories[ $g['cat'] ][ $lang ] ) ),
		);
	}
	ww_seed_post_i18n( $per_lang, $thumb_id );
}

// -----------------------------------------------------------------------------
// News
// -----------------------------------------------------------------------------

$news_defs = array(
	array( 'cat' => 'Regulation',
		'title' => array( 'en' => 'New UKGC Rules Tighten Deposit Limits Starting August', 'de' => 'Neue UKGC-Regeln verschärfen Einzahlungslimits ab August', 'zh' => '英国博彩委员会新规8月起收紧存款限额' ),
		'body'  => array(
			'en' => 'Operators licensed in the UK must implement affordability checks for deposits above £150/month under updated guidance.',
			'de' => 'Im Vereinigten Königreich lizenzierte Anbieter müssen ab August Zahlungsfähigkeitsprüfungen für Einzahlungen über 150 £ pro Monat durchführen.',
			'zh' => '根据最新指引，持有英国牌照的运营商须对每月超过150英镑的存款进行财务能力审查。',
		) ),
	array( 'cat' => 'Industry',
		'title' => array( 'en' => 'Vega Harbor Rolls Out Instant Crypto Withdrawals', 'de' => 'Vega Harbor führt sofortige Krypto-Auszahlungen ein', 'zh' => 'Vega Harbor推出即时加密货币提款' ),
		'body'  => array(
			'en' => 'Vega Harbor rolls out instant crypto withdrawals — this article walks through the key things CasinoRadar readers should know.',
			'de' => 'Vega Harbor führt ab sofort sofortige Krypto-Auszahlungen für alle verifizierten Konten ein, ein weiterer Schritt zu schnelleren Auszahlungszeiten in der Branche.',
			'zh' => 'Vega Harbor现已为所有已验证账户开放即时加密货币提款，为行业提款速度树立新标杆。',
		) ),
	array( 'cat' => 'Product Launch',
		'title' => array( 'en' => 'Nova Spins Adds 40 New Live-Dealer Tables This Month', 'de' => 'Nova Spins fügt diesen Monat 40 neue Live-Dealer-Tische hinzu', 'zh' => 'Nova Spins本月新增40张真人荷官桌' ),
		'body'  => array(
			'en' => 'Nova Spins adds 40 new live-dealer tables this month — this article walks through the key things CasinoRadar readers should know.',
			'de' => 'Nova Spins erweitert sein Live-Casino-Angebot um 40 neue Tische, mit zusätzlichen Sprachoptionen für europäische Spieler.',
			'zh' => 'Nova Spins将真人娱乐场新增40张桌台，并为欧洲玩家增加更多语言选项。',
		) ),
	array( 'cat' => 'Responsible Gambling',
		'title' => array( 'en' => 'National Self-Exclusion Registry Expands to 6 More States', 'de' => 'Nationales Selbstausschlussregister auf 6 weitere Bundesstaaten ausgeweitet', 'zh' => '全国自我排除登记系统扩展至另外6个州' ),
		'body'  => array(
			'en' => 'National self-exclusion registry expands to 6 more states — this article walks through the key things CasinoRadar readers should know.',
			'de' => 'Das nationale Selbstausschlussregister für Problemspieler steht ab sofort in sechs weiteren Bundesstaaten zur Verfügung.',
			'zh' => '面向问题博彩者的全国自我排除登记系统现已扩展至另外6个州。',
		) ),
	array( 'cat' => 'Regulation',
		'title' => array( 'en' => 'Curaçao Reforms Licensing Structure into Four Sub-Tiers', 'de' => 'Curaçao reformiert Lizenzstruktur in vier Untergruppen', 'zh' => '库拉索博彩牌照改革为四个子级别' ),
		'body'  => array(
			'en' => 'Curaçao reforms licensing structure into four sub-tiers — this article walks through the key things CasinoRadar readers should know.',
			'de' => 'Die neue Lizenzstruktur aus Curaçao soll für mehr Transparenz sorgen und unterteilt Anbieter künftig in vier Kategorien.',
			'zh' => '库拉索新牌照结构旨在提升透明度，将运营商划分为四个子级别。',
		) ),
	array( 'cat' => 'Industry',
		'title' => array( 'en' => 'Golden Reel Casino Named Payout Speed Leader for Q2', 'de' => 'Golden Reel Casino als Auszahlungsschnellster im 2. Quartal ausgezeichnet', 'zh' => 'Golden Reel Casino荣获第二季度提款速度冠军' ),
		'body'  => array(
			'en' => 'Golden Reel Casino named payout speed leader for Q2 — this article walks through the key things CasinoRadar readers should know.',
			'de' => 'Golden Reel Casino wurde in unserer vierteljährlichen Auswertung als schnellster Auszahler unter allen bewerteten Casinos ausgezeichnet.',
			'zh' => '在我们的季度评估中，Golden Reel Casino在所有评测赌场中提款速度最快。',
		) ),
	array( 'cat' => 'Product Launch',
		'title' => array( 'en' => 'Lucky Vault Opens Sportsbook Ahead of Football Season', 'de' => 'Lucky Vault eröffnet Sportwetten-Bereich vor der Fußballsaison', 'zh' => 'Lucky Vault在足球赛季前开设体育博彩' ),
		'body'  => array(
			'en' => 'Lucky Vault opens a sportsbook ahead of football season, with competitive odds across major European leagues.',
			'de' => 'Lucky Vault eröffnet rechtzeitig zur Fußballsaison einen neuen Sportwetten-Bereich mit wettbewerbsfähigen Quoten für die großen europäischen Ligen.',
			'zh' => 'Lucky Vault在足球赛季开始前推出体育博彩板块，覆盖欧洲各大联赛，赔率具有竞争力。',
		) ),
	array( 'cat' => 'Industry',
		'title' => array( 'en' => 'Nova Spins Named Best Live Dealer Experience of 2026', 'de' => 'Nova Spins zum besten Live-Dealer-Erlebnis 2026 gekürt', 'zh' => 'Nova Spins荣获2026年最佳真人荷官体验奖' ),
		'body'  => array(
			'en' => 'Nova Spins was named best live dealer experience of 2026 in an independent industry survey covering dozens of licensed operators.',
			'de' => 'Nova Spins wurde in einer unabhängigen Branchenumfrage unter Dutzenden lizenzierten Anbietern zum besten Live-Dealer-Erlebnis des Jahres 2026 gekürt.',
			'zh' => '在覆盖数十家持牌运营商的独立行业调查中，Nova Spins荣获2026年最佳真人荷官体验奖。',
		) ),
);

foreach ( $news_defs as $n ) {
	$thumb_id = ww_seed_contextual_image( $n['title']['en'], sanitize_title( $n['title']['en'] ) . '-thumb', ww_image_keywords( $n['cat'] ), '#3a2b45', $n['title']['en'] );
	$per_lang = array();
	foreach ( array( 'en', 'de', 'zh' ) as $lang ) {
		$title = $n['title'][ $lang ];
		$per_lang[ $lang ] = array(
			'args' => array(
				'post_type'    => 'news',
				'post_title'   => $title,
				'post_content' => '<p>' . esc_html( $n['body'][ $lang ] ) . '</p>',
				'post_excerpt' => wp_trim_words( $n['body'][ $lang ], 22 ),
				'post_status'  => 'publish',
				'post_name'    => sanitize_title( $title ),
			),
			'tax' => array( 'news_category' => array( $news_categories[ $n['cat'] ][ $lang ] ) ),
		);
	}
	ww_seed_post_i18n( $per_lang, $thumb_id );
}

// -----------------------------------------------------------------------------
// Editorial Reviews (the `review` CPT — separate from the comment-based
// "Player Reviews" seeded further below)
// -----------------------------------------------------------------------------

$review_defs = array(
	array( 'casino' => 0,
		'title'   => array( 'en' => 'Golden Reel Casino: Full Review', 'de' => 'Golden Reel Casino: Vollständiger Testbericht', 'zh' => 'Golden Reel Casino完整评测' ),
		'verdict' => array( 'en' => 'A strong all-rounder for crypto players who value fast payouts.', 'de' => 'Ein starker Allrounder für Krypto-Spieler, die schnelle Auszahlungen schätzen.', 'zh' => '对重视快速提款的加密货币玩家而言，是全能型的优秀选择。' ),
		'rating'  => 4.8,
		'pros'    => array( 'en' => array( 'Fast crypto withdrawals', 'Huge slot library', 'Responsive support' ), 'de' => array( 'Schnelle Krypto-Auszahlungen', 'Riesige Slot-Bibliothek', 'Reaktionsschneller Support' ), 'zh' => array( '加密货币提款速度快', '老虎机种类丰富', '客服响应迅速' ) ),
		'cons'    => array( 'en' => array( 'High wagering on welcome bonus' ), 'de' => array( 'Hohe Umsatzanforderung beim Willkommensbonus' ), 'zh' => array( '欢迎红利流水要求较高' ) ),
		'body'    => array( 'en' => 'Our editorial team tested account creation, deposits, withdrawals, and support responsiveness over several weeks before finalizing this review.', 'de' => 'Unser Redaktionsteam hat über mehrere Wochen Kontoerstellung, Ein- und Auszahlungen sowie den Support getestet, bevor dieser Testbericht finalisiert wurde.', 'zh' => '我们的编辑团队在数周内测试了账户注册、存取款和客服响应速度，才最终完成本篇评测。' ) ),
	array( 'casino' => 1,
		'title'   => array( 'en' => 'Royal Spin Palace: Full Review', 'de' => 'Royal Spin Palace: Vollständiger Testbericht', 'zh' => 'Royal Spin Palace完整评测' ),
		'verdict' => array( 'en' => 'The live dealer lobby alone makes this worth a look.', 'de' => 'Allein die Live-Dealer-Lobby macht einen Blick wert.', 'zh' => '仅凭真人荷官大厅就值得一试。' ),
		'rating'  => 4.6,
		'pros'    => array( 'en' => array( 'Excellent live dealer lobby', 'UKGC licensed', 'Generous VIP program' ), 'de' => array( 'Exzellente Live-Dealer-Lobby', 'UKGC-lizenziert', 'Großzügiges VIP-Programm' ), 'zh' => array( '真人荷官大厅体验出色', '持有UKGC牌照', 'VIP计划丰厚' ) ),
		'cons'    => array( 'en' => array( 'Slower verification process' ), 'de' => array( 'Langsamerer Verifizierungsprozess' ), 'zh' => array( '身份验证流程较慢' ) ),
		'body'    => array( 'en' => 'Our editorial team tested account creation, deposits, withdrawals, and support responsiveness over several weeks before finalizing this review.', 'de' => 'Unser Redaktionsteam hat über mehrere Wochen Kontoerstellung, Ein- und Auszahlungen sowie den Support getestet, bevor dieser Testbericht finalisiert wurde.', 'zh' => '我们的编辑团队在数周内测试了账户注册、存取款和客服响应速度，才最终完成本篇评测。' ) ),
	array( 'casino' => 2,
		'title'   => array( 'en' => 'Lucky Vault: Full Review', 'de' => 'Lucky Vault: Vollständiger Testbericht', 'zh' => 'Lucky Vault完整评测' ),
		'verdict' => array( 'en' => 'A solid mobile-first pick for casual players on a budget.', 'de' => 'Eine solide Mobile-First-Wahl für Gelegenheitsspieler mit kleinem Budget.', 'zh' => '预算有限的休闲玩家的可靠移动端优选。' ),
		'rating'  => 4.5,
		'pros'    => array( 'en' => array( 'Great mobile app', 'Weekly cashback', 'Low min. deposit' ), 'de' => array( 'Hervorragende mobile App', 'Wöchentliches Cashback', 'Niedrige Mindesteinzahlung' ), 'zh' => array( '移动应用体验出色', '每周返现', '最低存款额度低' ) ),
		'cons'    => array( 'en' => array( 'Smaller game library' ), 'de' => array( 'Kleinere Spielauswahl' ), 'zh' => array( '游戏库较小' ) ),
		'body'    => array( 'en' => 'Our editorial team tested account creation, deposits, withdrawals, and support responsiveness over several weeks before finalizing this review.', 'de' => 'Unser Redaktionsteam hat über mehrere Wochen Kontoerstellung, Ein- und Auszahlungen sowie den Support getestet, bevor dieser Testbericht finalisiert wurde.', 'zh' => '我们的编辑团队在数周内测试了账户注册、存取款和客服响应速度，才最终完成本篇评测。' ) ),
	array( 'casino' => 5,
		'title'   => array( 'en' => 'Crimson Ace Casino: Full Review', 'de' => 'Crimson Ace Casino: Vollständiger Testbericht', 'zh' => 'Crimson Ace Casino完整评测' ),
		'verdict' => array( 'en' => 'Great for slot fans who chase frequent free spin promos.', 'de' => 'Ideal für Slot-Fans, die häufige Freispiel-Aktionen suchen.', 'zh' => '适合喜欢频繁免费旋转活动的老虎机玩家。' ),
		'rating'  => 4.4,
		'pros'    => array( 'en' => array( 'Huge slot library', 'Frequent free spin drops', 'Fast KYC verification' ), 'de' => array( 'Riesige Slot-Bibliothek', 'Häufige Freispiel-Aktionen', 'Schnelle KYC-Verifizierung' ), 'zh' => array( '老虎机种类丰富', '免费旋转活动频繁', 'KYC验证速度快' ) ),
		'cons'    => array( 'en' => array( 'Withdrawal cap on weekends' ), 'de' => array( 'Auszahlungslimit am Wochenende' ), 'zh' => array( '周末提款有限额' ) ),
		'body'    => array( 'en' => 'Our editorial team tested account creation, deposits, withdrawals, and support responsiveness over several weeks before finalizing this review.', 'de' => 'Unser Redaktionsteam hat über mehrere Wochen Kontoerstellung, Ein- und Auszahlungen sowie den Support getestet, bevor dieser Testbericht finalisiert wurde.', 'zh' => '我们的编辑团队在数周内测试了账户注册、存取款和客服响应速度，才最终完成本篇评测。' ) ),
	array( 'casino' => 7,
		'title'   => array( 'en' => 'Nova Star Casino: Full Review', 'de' => 'Nova Star Casino: Vollständiger Testbericht', 'zh' => 'Nova Star Casino完整评测' ),
		'verdict' => array( 'en' => 'A high roller\'s pick, provided you can clear the reload wagering.', 'de' => 'Eine Wahl für High Roller, sofern Sie die Reload-Umsatzanforderung erfüllen können.', 'zh' => '适合高额玩家，前提是能满足充值红利的流水要求。' ),
		'rating'  => 4.7,
		'pros'    => array( 'en' => array( 'High table limits for VIPs', 'Personal account manager', 'Same-day payouts' ), 'de' => array( 'Hohe Tischlimits für VIPs', 'Persönlicher Kundenbetreuer', 'Auszahlung am selben Tag' ), 'zh' => array( 'VIP桌面游戏限额高', '专属客户经理', '当日到账提款' ) ),
		'cons'    => array( 'en' => array( 'Steep wagering on reload bonuses' ), 'de' => array( 'Hohe Umsatzanforderung bei Reload-Boni' ), 'zh' => array( '充值红利流水要求较高' ) ),
		'body'    => array( 'en' => 'Our editorial team tested account creation, deposits, withdrawals, and support responsiveness over several weeks before finalizing this review.', 'de' => 'Unser Redaktionsteam hat über mehrere Wochen Kontoerstellung, Ein- und Auszahlungen sowie den Support getestet, bevor dieser Testbericht finalisiert wurde.', 'zh' => '我们的编辑团队在数周内测试了账户注册、存取款和客服响应速度，才最终完成本篇评测。' ) ),
	array( 'casino' => 3,
		'title'   => array( 'en' => 'Diamond Reign: Full Review', 'de' => 'Diamond Reign: Vollständiger Testbericht', 'zh' => 'Diamond Reign完整评测' ),
		'verdict' => array( 'en' => 'Built for table-game high rollers, not casual slot players.', 'de' => 'Konzipiert für High Roller bei Tischspielen, weniger für gelegentliche Slot-Spieler.', 'zh' => '专为桌面游戏高额玩家打造，而非休闲老虎机玩家。' ),
		'rating'  => 4.3,
		'pros'    => array( 'en' => array( 'High table limits', 'Dedicated VIP host' ), 'de' => array( 'Hohe Tischlimits', 'Persönlicher VIP-Betreuer' ), 'zh' => array( '桌面游戏限额高', '专属VIP客户经理' ) ),
		'cons'    => array( 'en' => array( 'Fewer slot providers', 'Wagering 45x' ), 'de' => array( 'Weniger Slot-Anbieter', '45-fache Umsatzanforderung' ), 'zh' => array( '老虎机供应商较少', '流水要求为45倍' ) ),
		'body'    => array( 'en' => 'Our editorial team tested account creation, deposits, withdrawals, and support responsiveness over several weeks before finalizing this review.', 'de' => 'Unser Redaktionsteam hat über mehrere Wochen Kontoerstellung, Ein- und Auszahlungen sowie den Support getestet, bevor dieser Testbericht finalisiert wurde.', 'zh' => '我们的编辑团队在数周内测试了账户注册、存取款和客服响应速度，才最终完成本篇评测。' ) ),
);

foreach ( $review_defs as $r ) {
	$per_lang = array();
	foreach ( array( 'en', 'de', 'zh' ) as $lang ) {
		$title = $r['title'][ $lang ];
		$per_lang[ $lang ] = array(
			'args' => array(
				'post_type'    => 'review',
				'post_title'   => $title,
				'post_content' => '<p>' . esc_html( $r['body'][ $lang ] ) . '</p>',
				'post_excerpt' => $r['verdict'][ $lang ],
				'post_status'  => 'publish',
				'post_name'    => sanitize_title( $title ),
			),
			'meta' => array(
				'ww_related_casino' => ww_casino_id( $casino_ids_i18n, $r['casino'], $lang ),
				'ww_rating'         => $r['rating'],
				'ww_verdict'        => $r['verdict'][ $lang ],
				'ww_pros'           => $r['pros'][ $lang ],
				'ww_cons'           => $r['cons'][ $lang ],
			),
			'tax' => array( 'review_category' => array( $review_categories['Casino Reviews'][ $lang ] ) ),
		);
	}
	ww_seed_post_i18n( $per_lang, get_post_thumbnail_id( ww_casino_id( $casino_ids_i18n, $r['casino'], 'en' ) ) );
}

// -----------------------------------------------------------------------------
// Tournaments
// -----------------------------------------------------------------------------

$tournament_defs = array(
	array( 'casino' => 0, 'type' => 'Slot Race', 'entries' => '8,400',
		'title'  => array( 'en' => '$50,000 Slot Race', 'de' => '50.000 $ Slot-Race', 'zh' => '5万美元老虎机竞赛' ),
		'prize'  => array( 'en' => '$50,000', 'de' => '50.000 $', 'zh' => '5万美元' ),
		'status' => array( 'en' => 'Ends in 2d 14h', 'de' => 'Endet in 2T 14Std', 'zh' => '2天14小时后结束' ),
		'leaderboard' => array( 'PlayerX_92 | 184,200 | $10,000', 'SpinQueen | 171,900 | $6,000', 'ReelDeal88 | 160,440 | $4,000', 'LuckyStrike | 148,010 | $2,500', 'Vault_Runner | 139,770 | $1,500' ) ),
	array( 'casino' => 2, 'type' => 'Table Game Ladder', 'entries' => '2,100',
		'title'  => array( 'en' => 'Blackjack Ladder', 'de' => 'Blackjack-Rangliste', 'zh' => '21点排位赛' ),
		'prize'  => array( 'en' => '$12,000', 'de' => '12.000 $', 'zh' => '1.2万美元' ),
		'status' => array( 'en' => 'Ends in 5d 2h', 'de' => 'Endet in 5T 2Std', 'zh' => '5天2小时后结束' ),
		'leaderboard' => array() ),
	array( 'casino' => 1, 'type' => 'Free Roll', 'entries' => '0',
		'title'  => array( 'en' => 'Weekend Free Roll', 'de' => 'Wochenend-Freeroll', 'zh' => '周末免费锦标赛' ),
		'prize'  => array( 'en' => 'Free entry', 'de' => 'Kostenlose Teilnahme', 'zh' => '免费参赛' ),
		'status' => array( 'en' => 'Starts in 1d', 'de' => 'Startet in 1T', 'zh' => '1天后开始' ),
		'leaderboard' => array() ),
);

$tournament_body_tpl = array(
	'en' => fn( $title, $casino ) => $title . ' is a leaderboard race at ' . $casino . '. Climb the leaderboard by playing eligible games during the tournament window — prizes are paid out automatically at the end.',
	'de' => fn( $title, $casino ) => $title . ' ist ein Rangliste-Turnier bei ' . $casino . '. Klettern Sie die Rangliste hoch, indem Sie während des Turnierzeitraums teilnahmeberechtigte Spiele spielen — die Preise werden am Ende automatisch ausgezahlt.',
	'zh' => fn( $title, $casino ) => $title . '是' . $casino . '举办的排行榜竞赛。在锦标赛期间参与符合条件的游戏即可提升排名——奖金将在结束时自动发放。',
);

foreach ( $tournament_defs as $t ) {
	$thumb_id = ww_seed_contextual_image( $t['title']['en'], sanitize_title( $t['title']['en'] ) . '-thumb', ww_image_keywords( $t['type'] ), '#45332b', $t['title']['en'] );
	$per_lang = array();
	foreach ( array( 'en', 'de', 'zh' ) as $lang ) {
		$title      = $t['title'][ $lang ];
		$casino_id  = ww_casino_id( $casino_ids_i18n, $t['casino'], $lang );
		$casino_nm  = $casino_id ? get_the_title( $casino_id ) : '';
		$per_lang[ $lang ] = array(
			'args' => array(
				'post_type'    => 'tournament',
				'post_title'   => $title,
				'post_content' => '<p>' . esc_html( $tournament_body_tpl[ $lang ]( $title, $casino_nm ) ) . '</p>',
				'post_status'  => 'publish',
				'post_name'    => sanitize_title( $title ),
			),
			'meta' => array(
				'ww_related_casino' => $casino_id,
				'ww_prize_pool'     => $t['prize'][ $lang ],
				'ww_entries'        => $t['entries'],
				'ww_status_label'   => $t['status'][ $lang ],
				'ww_leaderboard'    => $t['leaderboard'],
			),
			'tax' => array( 'tournament_type' => array( $tournament_types[ $t['type'] ][ $lang ] ) ),
		);
	}
	ww_seed_post_i18n( $per_lang, $thumb_id );
}

// -----------------------------------------------------------------------------
// Complaints (resolved cases — public list; new submissions arrive by email)
// -----------------------------------------------------------------------------

$complaint_defs = array(
	array( 'case' => '4821', 'casino' => 0, 'issue' => 'Withdrawal Delay', 'amount' => '$1,400', 'recovered' => 1400, 'filed_days_ago' => 26, 'resolved_days_ago' => 7,
		'summary' => array(
			'en' => "Player's \$1,400 withdrawal, stuck for 19 days, was released after our team intervened.",
			'de' => 'Die Auszahlung eines Spielers in Höhe von 1.400 $, die 19 Tage lang feststeckte, wurde nach Eingreifen unseres Teams freigegeben.',
			'zh' => '一名玩家1400美元的提款申请被搁置19天，经我们团队介入后成功放行。',
		),
		'outcome' => array( 'en' => 'Full refund', 'de' => 'Vollständige Rückerstattung', 'zh' => '全额退还' ) ),
	array( 'case' => '4798', 'casino' => 1, 'issue' => 'Bonus Dispute', 'amount' => '$300', 'recovered' => 300, 'filed_days_ago' => 20, 'resolved_days_ago' => 14,
		'summary' => array(
			'en' => "Wagering requirement was miscalculated by the casino's system; balance corrected in the player's favor.",
			'de' => 'Die Umsatzanforderung wurde vom System des Casinos falsch berechnet; das Guthaben wurde zugunsten des Spielers korrigiert.',
			'zh' => '赌场系统误算了流水要求；余额已按有利于玩家的方向更正。',
		),
		'outcome' => array( 'en' => 'Balance corrected', 'de' => 'Guthaben korrigiert', 'zh' => '余额已更正' ) ),
	array( 'case' => '4771', 'casino' => 2, 'issue' => 'Account Closure', 'amount' => '$0', 'recovered' => 0, 'filed_days_ago' => 35, 'resolved_days_ago' => 4,
		'summary' => array(
			'en' => 'Account was frozen pending KYC for over 30 days; documents were re-reviewed and the account reinstated.',
			'de' => 'Das Konto war über 30 Tage lang wegen ausstehender KYC-Prüfung gesperrt; die Unterlagen wurden erneut geprüft und das Konto reaktiviert.',
			'zh' => '账户因KYC审核搁置超过30天；文件经重新审核后账户已恢复。',
		),
		'outcome' => array( 'en' => 'Account reinstated', 'de' => 'Konto reaktiviert', 'zh' => '账户已恢复' ) ),
);

foreach ( $complaint_defs as $c ) {
	$per_lang = array();
	foreach ( array( 'en', 'de', 'zh' ) as $lang ) {
		$casino_id = ww_casino_id( $casino_ids_i18n, $c['casino'], $lang );
		$per_lang[ $lang ] = array(
			'args' => array(
				'post_type'    => 'complaint',
				'post_title'   => 'Case #' . $c['case'],
				'post_status'  => 'publish',
				'post_name'    => 'case-' . $c['case'] . ( 'en' === $lang ? '' : '-' . $lang ),
			),
			'meta' => array(
				'ww_case_number'      => $c['case'],
				'ww_related_casino'   => $casino_id,
				'ww_issue_type'       => $issue_types[ $c['issue'] ][ $lang ],
				'ww_disputed_amount'  => $c['amount'],
				'ww_amount_recovered' => $c['recovered'],
				'ww_filed_date'       => gmdate( 'Y-m-d', strtotime( "-{$c['filed_days_ago']} days" ) ),
				'ww_resolved_date'    => gmdate( 'Y-m-d', strtotime( "-{$c['resolved_days_ago']} days" ) ),
				'ww_summary'          => $c['summary'][ $lang ],
				'ww_outcome'          => $c['outcome'][ $lang ],
			),
			'tax' => array( 'complaint_issue_type' => array( $issue_types[ $c['issue'] ][ $lang ] ) ),
		);
	}
	ww_seed_post_i18n( $per_lang );
}

// -----------------------------------------------------------------------------
// Player Reviews (native comments on casino posts, with a rating field) —
// one set of comments per language's casino translation.
// -----------------------------------------------------------------------------

$player_review_defs = array(
	array( 'casino' => 0, 'rating' => 5,
		'author' => array( 'en' => 'Marcus T.', 'de' => 'Markus T.', 'zh' => '陈先生' ),
		'text'   => array(
			'en' => 'Withdrew a large sum in under 24 hours with zero back-and-forth. Support actually answered in under 5 minutes.',
			'de' => 'Habe eine große Summe in unter 24 Stunden ohne Rückfragen ausgezahlt bekommen. Der Support hat tatsächlich in unter 5 Minuten geantwortet.',
			'zh' => '不到24小时就提现了一大笔钱，全程无需反复沟通。客服在5分钟内就回复了。',
		) ),
	array( 'casino' => 1, 'rating' => 4,
		'author' => array( 'en' => 'Priya K.', 'de' => 'Petra K.', 'zh' => '李女士' ),
		'text'   => array(
			'en' => 'Great game selection and live dealer tables. Deposit bonus terms were clear upfront, no surprise wagering traps.',
			'de' => 'Tolle Spielauswahl und Live-Dealer-Tische. Die Bonusbedingungen für die Einzahlung waren von Anfang an klar, keine versteckten Umsatzfallen.',
			'zh' => '游戏种类丰富，真人荷官桌台也不错。存款红利条款一开始就写得很清楚，没有隐藏的流水陷阱。',
		) ),
	array( 'casino' => 2, 'rating' => 3,
		'author' => array( 'en' => 'Daniel W.', 'de' => 'Daniel W.', 'zh' => '王先生' ),
		'text'   => array(
			'en' => 'KYC took longer than advertised but support was responsive once I followed up.',
			'de' => 'Die KYC-Prüfung dauerte länger als angekündigt, aber der Support war reaktionsschnell, sobald ich nachgefragt habe.',
			'zh' => 'KYC审核时间比宣传的要长，但我主动跟进后客服反应还算及时。',
		) ),
);

foreach ( $player_review_defs as $r ) {
	foreach ( array( 'en', 'de', 'zh' ) as $lang ) {
		$casino_id = ww_casino_id( $casino_ids_i18n, $r['casino'], $lang );
		if ( ! $casino_id ) {
			continue;
		}
		$author   = $r['author'][ $lang ];
		$existing = get_comments( array( 'post_id' => $casino_id, 'author' => $author, 'number' => 1 ) );
		if ( $existing ) {
			continue;
		}
		$comment_id = wp_insert_comment( array(
			'comment_post_ID'      => $casino_id,
			'comment_author'       => $author,
			'comment_author_email' => sanitize_title( $author ) . '-' . $lang . '@example.com',
			'comment_content'      => $r['text'][ $lang ],
			'comment_approved'     => 1,
		) );
		if ( $comment_id ) {
			update_comment_meta( $comment_id, 'ww_review_rating', $r['rating'] );
		}
	}
}

// -----------------------------------------------------------------------------
// Pages (Home, composed pages, legal, etc.) — EN/DE/ZH
// -----------------------------------------------------------------------------

$home_ids = ww_seed_post_i18n( array(
	'en' => array( 'args' => array( 'post_type' => 'page', 'post_title' => 'Home', 'post_content' => wagerwise_homepage_pattern_markup(), 'post_status' => 'publish', 'post_name' => 'home' ) ),
	'de' => array( 'args' => array( 'post_type' => 'page', 'post_title' => 'Startseite', 'post_content' => wagerwise_homepage_pattern_markup( 'de' ), 'post_status' => 'publish', 'post_name' => 'startseite' ) ),
	'zh' => array( 'args' => array( 'post_type' => 'page', 'post_title' => '首页', 'post_content' => wagerwise_homepage_pattern_markup( 'zh' ), 'post_status' => 'publish', 'post_name' => sanitize_title( '首页' ) ) ),
) );

ww_seed_post_i18n( array(
	'en' => array( 'args' => array( 'post_type' => 'page', 'post_title' => 'Guide', 'post_content' => wagerwise_guide_page_markup(), 'post_status' => 'publish', 'post_name' => 'guide' ) ),
	'de' => array( 'args' => array( 'post_type' => 'page', 'post_title' => 'Ratgeber', 'post_content' => wagerwise_guide_page_markup( 'de' ), 'post_status' => 'publish', 'post_name' => 'ratgeber' ) ),
	'zh' => array( 'args' => array( 'post_type' => 'page', 'post_title' => '指南', 'post_content' => wagerwise_guide_page_markup( 'zh' ), 'post_status' => 'publish', 'post_name' => sanitize_title( '指南' ) ) ),
) );

ww_seed_post_i18n( array(
	'en' => array( 'args' => array( 'post_type' => 'page', 'post_title' => 'News', 'post_content' => wagerwise_news_page_markup(), 'post_status' => 'publish', 'post_name' => 'news' ) ),
	'de' => array( 'args' => array( 'post_type' => 'page', 'post_title' => 'Neuigkeiten', 'post_content' => wagerwise_news_page_markup( 'de' ), 'post_status' => 'publish', 'post_name' => 'neuigkeiten' ) ),
	'zh' => array( 'args' => array( 'post_type' => 'page', 'post_title' => '资讯', 'post_content' => wagerwise_news_page_markup( 'zh' ), 'post_status' => 'publish', 'post_name' => sanitize_title( '资讯' ) ) ),
) );

ww_seed_post_i18n( array(
	'en' => array( 'args' => array( 'post_type' => 'page', 'post_title' => 'Reviews', 'post_content' => wagerwise_reviews_page_markup(), 'post_status' => 'publish', 'post_name' => 'reviews' ) ),
	'de' => array( 'args' => array( 'post_type' => 'page', 'post_title' => 'Testberichte', 'post_content' => wagerwise_reviews_page_markup( 'de' ), 'post_status' => 'publish', 'post_name' => 'testberichte' ) ),
	'zh' => array( 'args' => array( 'post_type' => 'page', 'post_title' => '评测', 'post_content' => wagerwise_reviews_page_markup( 'zh' ), 'post_status' => 'publish', 'post_name' => sanitize_title( '评测' ) ) ),
) );

ww_seed_post_i18n( array(
	'en' => array( 'args' => array( 'post_type' => 'page', 'post_title' => 'Complaints', 'post_content' => wagerwise_complaints_page_markup(), 'post_status' => 'publish', 'post_name' => 'complaints' ) ),
	'de' => array( 'args' => array( 'post_type' => 'page', 'post_title' => 'Beschwerden', 'post_content' => wagerwise_complaints_page_markup( 'de' ), 'post_status' => 'publish', 'post_name' => 'beschwerden' ) ),
	'zh' => array( 'args' => array( 'post_type' => 'page', 'post_title' => '投诉', 'post_content' => wagerwise_complaints_page_markup( 'zh' ), 'post_status' => 'publish', 'post_name' => sanitize_title( '投诉' ) ) ),
) );

$legal_pages = array(
	'About Us' => array(
		'en' => array( 'title' => 'About Us', 'content' => '<p>CasinoRadar is an independent casino review site. Our mission is to help players find trustworthy, well-regulated online casinos through honest, thoroughly researched reviews.</p>' ),
		'de' => array( 'title' => 'Über Uns', 'content' => '<p>CasinoRadar ist eine unabhängige Casino-Bewertungsseite. Unsere Mission ist es, Spielern zu helfen, vertrauenswürdige, gut regulierte Online-Casinos durch ehrliche, gründlich recherchierte Testberichte zu finden.</p>' ),
		'zh' => array( 'title' => '关于我们', 'content' => '<p>CasinoRadar是一家独立的赌场评测网站。我们的使命是通过诚实、深入调研的评测，帮助玩家找到值得信赖、监管完善的在线赌场。</p>' ),
	),
	'How We Review Casinos' => array(
		'en' => array( 'title' => 'How We Review Casinos', 'content' => "<p>Every casino listed on CasinoRadar goes through the same evaluation process before it earns a rating. We look at licensing and regulation, the speed and reliability of withdrawals, the fairness of bonus terms and wagering requirements, the breadth of the game library, and the responsiveness of customer support.</p><p>Our rating is a starting point for your own research, not a guarantee — always read a casino's current terms and conditions directly before depositing.</p>" ),
		'de' => array( 'title' => 'Wie Wir Casinos Bewerten', 'content' => '<p>Jedes auf CasinoRadar gelistete Casino durchläuft denselben Bewertungsprozess, bevor es eine Wertung erhält. Wir prüfen Lizenzierung und Regulierung, die Geschwindigkeit und Zuverlässigkeit von Auszahlungen, die Fairness der Bonusbedingungen und Umsatzanforderungen, die Breite der Spielbibliothek sowie die Reaktionsfähigkeit des Kundensupports.</p><p>Unsere Bewertung ist ein Ausgangspunkt für Ihre eigene Recherche, keine Garantie — lesen Sie immer die aktuellen Geschäftsbedingungen eines Casinos direkt, bevor Sie einzahlen.</p>' ),
		'zh' => array( 'title' => '我们的评测方式', 'content' => '<p>每一家在CasinoRadar上架的赌场，都需经过相同的评估流程才能获得评级。我们考察牌照与监管、提款速度与可靠性、红利条款与流水要求的公平性、游戏库的丰富程度，以及客服的响应速度。</p><p>我们的评级仅作为您自行研究的起点，而非保证——存款前请务必直接查阅赌场当前的条款与条件。</p>' ),
	),
	'Responsible Gambling' => array(
		'en' => array( 'title' => 'Responsible Gambling', 'content' => '<p>Gambling should always be fun, not a way to make money. If you feel you may have a gambling problem, resources like BeGambleAware and GamCare are available to help. Must be 18+.</p>' ),
		'de' => array( 'title' => 'Verantwortungsvolles Spielen', 'content' => '<p>Glücksspiel sollte immer Unterhaltung sein, kein Weg, um Geld zu verdienen. Wenn Sie glauben, ein Glücksspielproblem zu haben, stehen Ihnen Angebote wie die BZgA-Beratung zur Verfügung. Nur ab 18 Jahren.</p>' ),
		'zh' => array( 'title' => '负责任博彩', 'content' => '<p>博彩应始终以娱乐为目的，而非赚钱的手段。如果您认为自己可能有博彩问题，可寻求相关求助资源的帮助。仅限18岁以上人士参与。</p>' ),
	),
	'Privacy Policy' => array(
		'en' => array( 'title' => 'Privacy Policy', 'content' => '<p>This page describes how CasinoRadar collects and uses information. Replace this placeholder with your actual privacy policy before launch.</p>' ),
		'de' => array( 'title' => 'Datenschutzerklärung', 'content' => '<p>Diese Seite beschreibt, wie CasinoRadar Informationen erhebt und verwendet. Ersetzen Sie diesen Platzhalter vor dem Launch durch Ihre tatsächliche Datenschutzerklärung.</p>' ),
		'zh' => array( 'title' => '隐私政策', 'content' => '<p>本页面说明CasinoRadar如何收集和使用信息。上线前请将此占位内容替换为实际的隐私政策。</p>' ),
	),
	'Terms & Affiliate Disclosure' => array(
		'en' => array( 'title' => 'Terms & Affiliate Disclosure', 'content' => "<p>CasinoRadar may receive a commission when you sign up with an operator through links on this site. This never affects the objectivity of our reviews or rankings.</p>" ),
		'de' => array( 'title' => 'Bedingungen & Partnerprogramm-Offenlegung', 'content' => '<p>CasinoRadar erhält möglicherweise eine Provision, wenn Sie sich über Links auf dieser Seite bei einem Anbieter anmelden. Dies beeinflusst niemals die Objektivität unserer Testberichte oder Rankings.</p>' ),
		'zh' => array( 'title' => '条款与联盟披露', 'content' => '<p>当您通过本网站的链接在运营商处注册时，CasinoRadar可能会获得佣金。这绝不会影响我们评测或排名的客观性。</p>' ),
	),
	'Contact' => array(
		'en' => array( 'title' => 'Contact', 'content' => '<p>Have a question or found an error in a review? Reach out to our editorial team — we read every message.</p>' ),
		'de' => array( 'title' => 'Kontakt', 'content' => '<p>Haben Sie eine Frage oder einen Fehler in einem Testbericht gefunden? Kontaktieren Sie unser Redaktionsteam — wir lesen jede Nachricht.</p>' ),
		'zh' => array( 'title' => '联系我们', 'content' => '<p>有任何疑问，或在评测中发现错误？请联系我们的编辑团队——我们会阅读每一条留言。</p>' ),
	),
);

foreach ( $legal_pages as $key => $variants ) {
	$per_lang = array();
	foreach ( array( 'en', 'de', 'zh' ) as $lang ) {
		$v = $variants[ $lang ];
		$per_lang[ $lang ] = array(
			'args' => array(
				'post_type'    => 'page',
				'post_title'   => $v['title'],
				'post_content' => $v['content'],
				'post_status'  => 'publish',
				'post_name'    => sanitize_title( $v['title'] ),
			),
		);
	}
	ww_seed_post_i18n( $per_lang );
}

if ( ! empty( $home_ids['en'] ) ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home_ids['en'] );
}

// Sensible CasinoRadar Settings defaults if this is truly the first run.
if ( false === get_option( 'ww_site_disclaimer', false ) ) {
	add_option( 'ww_site_disclaimer', 'CasinoRadar may earn a commission from operators listed on this site. This does not affect our reviews or rankings.' );
}

// -----------------------------------------------------------------------------
// Polylang string translations for the theme/plugin's static UI chrome, so
// the header/footer/buttons are translatable, not just post content.
// -----------------------------------------------------------------------------

if ( class_exists( 'PLL_MO' ) && function_exists( 'pll_register_string' ) ) {
	$ui_strings = array(
		'Home'                     => array( 'de' => 'Startseite', 'zh' => '首页' ),
		'Online Casinos'           => array( 'de' => 'Online Casinos', 'zh' => '在线赌场' ),
		'Games'                    => array( 'de' => 'Spiele', 'zh' => '游戏' ),
		'Bonuses'                  => array( 'de' => 'Boni', 'zh' => '红利' ),
		'Guide'                    => array( 'de' => 'Ratgeber', 'zh' => '指南' ),
		'Complaints'               => array( 'de' => 'Beschwerden', 'zh' => '投诉' ),
		'Reviews'                  => array( 'de' => 'Testberichte', 'zh' => '评测' ),
		'Tournaments'              => array( 'de' => 'Turniere', 'zh' => '锦标赛' ),
		'News'                     => array( 'de' => 'Neuigkeiten', 'zh' => '资讯' ),
		'Join Free'                => array( 'de' => 'Kostenlos Beitreten', 'zh' => '免费加入' ),
		'Play Now'                 => array( 'de' => 'Jetzt Spielen', 'zh' => '立即游玩' ),
		'Claim Bonus'              => array( 'de' => 'Bonus Sichern', 'zh' => '领取红利' ),
		'Visit Casino'             => array( 'de' => 'Casino Besuchen', 'zh' => '访问赌场' ),
		'Are you 18 or older?'     => array( 'de' => 'Sind Sie 18 Jahre oder älter?', 'zh' => '您是否年满18岁？' ),
		"Yes, I'm 18+"             => array( 'de' => 'Ja, ich bin 18+', 'zh' => '是的，我已满18岁' ),
		'Leave Site'               => array( 'de' => 'Seite Verlassen', 'zh' => '离开网站' ),
		'© 2026 CasinoRadar. 18+. Play responsibly.' => array( 'de' => '© 2026 CasinoRadar. 18+. Bitte verantwortungsvoll spielen.', 'zh' => '© 2026 CasinoRadar。18岁以上。请理性博彩。' ),
		'No featured picks in this category yet.' => array( 'de' => 'In dieser Kategorie noch keine Top-Empfehlungen.', 'zh' => '该类别暂无精选推荐。' ),
	);

	foreach ( $ui_strings as $en_string => $translations ) {
		pll_register_string( 'wagerwise-' . sanitize_title( $en_string ), $en_string, 'WagerWise', false );
	}

	foreach ( array( 'de', 'zh' ) as $lang_slug ) {
		$language = PLL()->model->get_language( $lang_slug );
		if ( ! $language ) {
			continue;
		}
		$mo = new PLL_MO();
		$mo->import_from_db( $language );
		foreach ( $ui_strings as $en_string => $translations ) {
			if ( ! empty( $translations[ $lang_slug ] ) ) {
				$mo->add_entry( $mo->make_entry( $en_string, $translations[ $lang_slug ] ) );
			}
		}
		$mo->export_to_db( $language );
	}
}

WP_CLI::success( 'CasinoRadar demo content seeded (EN/DE/ZH).' );
