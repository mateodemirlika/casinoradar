<?php
/**
 * Must-use loader: runs before regular plugins, so it's the right place for
 * one-time bootstrap logic and environment-wide behavior that shouldn't be
 * toggle-able from the plugins screen.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Seed a starter set of Polylang languages, so the site is immediately
 * multilingual-ready. Safe to run repeatedly and safe to extend later —
 * each language in the list is added only if it's not already registered,
 * checked individually rather than via an all-or-nothing "already ran once"
 * flag, so appending a new language here is enough to roll it out to an
 * already-bootstrapped site too. Never removes or renames a language an
 * admin later changes. Additional languages remain fully configurable from
 * Languages → Languages in wp-admin.
 */
add_action( 'admin_init', 'wagerwise_bootstrap_polylang_languages' );

function wagerwise_bootstrap_polylang_languages(): void {
	if ( ! function_exists( 'PLL' ) || ! class_exists( 'PLL_Language' ) ) {
		return;
	}

	$languages = array(
		array( 'slug' => 'en', 'locale' => 'en_US', 'name' => 'English', 'flag' => 'us', 'rtl' => 0 ),
		array( 'slug' => 'de', 'locale' => 'de_DE', 'name' => 'Deutsch', 'flag' => 'de', 'rtl' => 0 ),
		array( 'slug' => 'zh', 'locale' => 'zh_CN', 'name' => '中文', 'flag' => 'cn', 'rtl' => 0 ),
		array( 'slug' => 'es', 'locale' => 'es_ES', 'name' => 'Español', 'flag' => 'es', 'rtl' => 0 ),
		array( 'slug' => 'pt', 'locale' => 'pt_PT', 'name' => 'Português', 'flag' => 'pt', 'rtl' => 0 ),
		array( 'slug' => 'fr', 'locale' => 'fr_FR', 'name' => 'Français', 'flag' => 'fr', 'rtl' => 0 ),
		array( 'slug' => 'it', 'locale' => 'it_IT', 'name' => 'Italiano', 'flag' => 'it', 'rtl' => 0 ),
		array( 'slug' => 'sv', 'locale' => 'sv_SE', 'name' => 'Svenska', 'flag' => 'se', 'rtl' => 0 ),
		array( 'slug' => 'nb', 'locale' => 'nb_NO', 'name' => 'Norsk', 'flag' => 'no', 'rtl' => 0 ),
		array( 'slug' => 'fi', 'locale' => 'fi', 'name' => 'Suomi', 'flag' => 'fi', 'rtl' => 0 ),
		array( 'slug' => 'nl', 'locale' => 'nl_NL', 'name' => 'Nederlands', 'flag' => 'nl', 'rtl' => 0 ),
		array( 'slug' => 'ja', 'locale' => 'ja', 'name' => '日本語', 'flag' => 'jp', 'rtl' => 0 ),
		array( 'slug' => 'pl', 'locale' => 'pl_PL', 'name' => 'Polski', 'flag' => 'pl', 'rtl' => 0 ),
		array( 'slug' => 'cs', 'locale' => 'cs_CZ', 'name' => 'Čeština', 'flag' => 'cz', 'rtl' => 0 ),
		array( 'slug' => 'ro', 'locale' => 'ro_RO', 'name' => 'Română', 'flag' => 'ro', 'rtl' => 0 ),
		array( 'slug' => 'el', 'locale' => 'el', 'name' => 'Ελληνικά', 'flag' => 'gr', 'rtl' => 0 ),
	);

	$existing_list  = PLL()->model->get_languages_list();
	$is_first_run   = empty( $existing_list );
	$existing_slugs = wp_list_pluck( $existing_list, 'slug' );
	$added_any      = false;

	foreach ( $languages as $i => $lang ) {
		if ( in_array( $lang['slug'], $existing_slugs, true ) ) {
			continue;
		}
		PLL()->model->add_language(
			array(
				'slug'       => $lang['slug'],
				'locale'     => $lang['locale'],
				'name'       => $lang['name'],
				'flag'       => $lang['flag'],
				'rtl'        => $lang['rtl'],
				'term_group' => $i,
			)
		);
		$added_any = true;
	}

	// A new language's URL prefix (e.g. /es/) only takes effect once the
	// rewrite rules are rebuilt. This covers the admin_init path (an admin
	// visiting wp-admin normally); it's a no-op in practice when this same
	// function is called directly and synchronously from seed.php — within
	// that single request/process, Polylang's rewrite-rule generation reads
	// a language list it already cached before add_language() ran here, so
	// the actual fix for that path is bootstrap.sh's separate `wp rewrite
	// flush` step, run as its own process after seeding completes.
	if ( $added_any ) {
		flush_rewrite_rules();
	}

	// English is the default/reference language — only meaningful to set on
	// the very first run; an admin may have deliberately changed it since.
	if ( $is_first_run ) {
		$en = PLL()->model->get_language( 'en' );
		if ( $en ) {
			update_option( 'polylang', array_merge( (array) get_option( 'polylang', array() ), array( 'default_lang' => 'en' ) ) );
		}
	}
}

/**
 * Local dev only: route outgoing mail to Mailhog instead of trying (and
 * failing) to hit a real SMTP server.
 */
if ( 'local' === getenv( 'WP_ENV' ) ) {
	add_action( 'phpmailer_init', function ( $phpmailer ): void {
		$phpmailer->isSMTP();
		$phpmailer->Host       = 'mailhog';
		$phpmailer->Port       = 1025;
		$phpmailer->SMTPAuth   = false;
		$phpmailer->SMTPSecure = '';
	} );
}

/**
 * Baseline hardening.
 */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'xmlrpc_enabled', '__return_false' );
