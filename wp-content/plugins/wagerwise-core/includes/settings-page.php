<?php
/**
 * Global "WagerWise Settings" admin page — the free, native replacement for
 * an ACF Options page. Built entirely on the core Settings API; every value
 * is read in the theme via get_option().
 */

defined( 'ABSPATH' ) || exit;

const WAGERWISE_OPTION_GROUP = 'wagerwise_settings';

add_action( 'admin_menu', 'wagerwise_add_settings_page' );

function wagerwise_add_settings_page(): void {
	add_menu_page(
		__( 'WagerWise Settings', 'wagerwise' ),
		__( 'WagerWise', 'wagerwise' ),
		'manage_options',
		'wagerwise-settings',
		'wagerwise_render_settings_page',
		'dashicons-money-alt',
		59
	);
}

/**
 * option_name => [ label, field type, section, default ]
 */
function wagerwise_settings_schema(): array {
	return array(
		// General
		'ww_site_disclaimer'          => array( __( 'Affiliate Disclaimer', 'wagerwise' ), 'textarea', 'general', __( 'WagerWise may earn a commission from operators listed on this site. This does not affect our reviews or rankings.', 'wagerwise' ) ),
		'ww_responsible_gambling_text' => array( __( 'Responsible Gambling Notice', 'wagerwise' ), 'textarea', 'general', __( 'Gambling can be addictive. Please play responsibly. 18+ only.', 'wagerwise' ) ),
		'ww_age_gate_enabled'         => array( __( 'Enable 18+ Age Gate', 'wagerwise' ), 'checkbox', 'general', '0' ),
		'ww_footer_text'              => array( __( 'Footer Copyright Text', 'wagerwise' ), 'text', 'general', '© ' . gmdate( 'Y' ) . ' WagerWise' ),

		// Homepage
		'ww_hero_heading'             => array( __( 'Hero Heading', 'wagerwise' ), 'text', 'homepage', __( 'Every Casino Bonus Worth Chasing', 'wagerwise' ) ),
		'ww_hero_subheading'          => array( __( 'Hero Subheading', 'wagerwise' ), 'text', 'homepage', __( 'Verified bonus codes and payout-speed rankings, built for players who actually read the terms.', 'wagerwise' ) ),

		// Social
		'ww_social_facebook'  => array( __( 'Facebook URL', 'wagerwise' ), 'url', 'social', '' ),
		'ww_social_twitter'   => array( __( 'X / Twitter URL', 'wagerwise' ), 'url', 'social', '' ),
		'ww_social_instagram' => array( __( 'Instagram URL', 'wagerwise' ), 'url', 'social', '' ),
		'ww_social_telegram'  => array( __( 'Telegram URL', 'wagerwise' ), 'url', 'social', '' ),

		// Tracking
		'ww_header_scripts' => array( __( 'Header Scripts (e.g. GA4/GTM)', 'wagerwise' ), 'textarea_code', 'tracking', '' ),
		'ww_footer_scripts' => array( __( 'Footer Scripts', 'wagerwise' ), 'textarea_code', 'tracking', '' ),
	);
}

// register_setting() must run on 'init' (fires on the front end too) so its
// 'default' is available to get_option() outside wp-admin. add_settings_section()
// / add_settings_field() only exist in wp-admin, so the UI wiring stays on
// 'admin_init'.
add_action( 'init', 'wagerwise_register_setting_options' );
add_action( 'admin_init', 'wagerwise_register_settings_ui' );

function wagerwise_register_setting_options(): void {
	foreach ( wagerwise_settings_schema() as $option => [ $label, $type, $section, $default ] ) {
		register_setting(
			WAGERWISE_OPTION_GROUP,
			$option,
			array(
				'type'              => 'checkbox' === $type ? 'boolean' : 'string',
				'sanitize_callback' => wagerwise_settings_sanitizer( $type ),
				'default'           => $default,
			)
		);
	}
}

function wagerwise_register_settings_ui(): void {
	$sections = array(
		'general'  => __( 'General', 'wagerwise' ),
		'homepage' => __( 'Homepage', 'wagerwise' ),
		'social'   => __( 'Social Links', 'wagerwise' ),
		'tracking' => __( 'Tracking & Ad Codes', 'wagerwise' ),
	);

	foreach ( $sections as $id => $label ) {
		add_settings_section( 'wagerwise_' . $id, $label, '__return_false', 'wagerwise-settings' );
	}

	foreach ( wagerwise_settings_schema() as $option => [ $label, $type, $section, $default ] ) {
		add_settings_field(
			$option,
			$label,
			'wagerwise_render_settings_field',
			'wagerwise-settings',
			'wagerwise_' . $section,
			array( 'option' => $option, 'type' => $type )
		);
	}
}

function wagerwise_settings_sanitizer( string $type ): callable {
	return match ( $type ) {
		'url'           => 'esc_url_raw',
		'checkbox'      => fn( $v ) => $v ? '1' : '0',
		'textarea'      => 'sanitize_textarea_field',
		'textarea_code' => fn( $v ) => current_user_can( 'unfiltered_html' ) ? $v : wp_kses_post( $v ),
		default         => 'sanitize_text_field',
	};
}

function wagerwise_render_settings_field( array $args ): void {
	$option = $args['option'];
	$value  = get_option( $option );

	switch ( $args['type'] ) {
		case 'textarea':
		case 'textarea_code':
			printf( '<textarea class="large-text code" rows="4" name="%1$s">%2$s</textarea>', esc_attr( $option ), esc_textarea( $value ) );
			break;
		case 'checkbox':
			printf( '<input type="checkbox" name="%1$s" value="1" %2$s />', esc_attr( $option ), checked( (bool) $value, true, false ) );
			break;
		case 'url':
			printf( '<input type="url" class="regular-text" name="%1$s" value="%2$s" placeholder="https://" />', esc_attr( $option ), esc_attr( $value ) );
			break;
		default:
			printf( '<input type="text" class="regular-text" name="%1$s" value="%2$s" />', esc_attr( $option ), esc_attr( $value ) );
	}
}

function wagerwise_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'WagerWise Settings', 'wagerwise' ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( WAGERWISE_OPTION_GROUP );
			do_settings_sections( 'wagerwise-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}
