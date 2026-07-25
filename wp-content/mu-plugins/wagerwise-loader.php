<?php
/**
 * Must-use loader: runs before regular plugins, so it's the right place for
 * one-time bootstrap logic and environment-wide behavior that shouldn't be
 * toggle-able from the plugins screen.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Seed a starter set of Polylang languages on first activation, so the site
 * is immediately multilingual-ready. Safe to run repeatedly — it only acts
 * once (guarded by an option) and never removes languages an admin later
 * changes. Additional languages remain fully configurable from
 * Languages → Languages in wp-admin.
 */
add_action( 'admin_init', 'wagerwise_bootstrap_polylang_languages' );

function wagerwise_bootstrap_polylang_languages(): void {
	if ( get_option( 'wagerwise_polylang_bootstrapped' ) ) {
		return;
	}
	if ( ! function_exists( 'PLL' ) || ! class_exists( 'PLL_Language' ) ) {
		return;
	}

	$languages = array(
		array( 'slug' => 'en', 'locale' => 'en_US', 'name' => 'English', 'flag' => 'us', 'rtl' => 0 ),
		array( 'slug' => 'de', 'locale' => 'de_DE', 'name' => 'Deutsch', 'flag' => 'de', 'rtl' => 0 ),
		array( 'slug' => 'zh', 'locale' => 'zh_CN', 'name' => '中文', 'flag' => 'cn', 'rtl' => 0 ),
	);

	if ( ! PLL()->model->get_languages_list() ) {
		foreach ( $languages as $i => $lang ) {
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
		}
		// English is the default/reference language.
		$en = PLL()->model->get_language( 'en' );
		if ( $en ) {
			update_option( 'polylang', array_merge( (array) get_option( 'polylang', array() ), array( 'default_lang' => 'en' ) ) );
		}
	}

	update_option( 'wagerwise_polylang_bootstrapped', 1 );
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
