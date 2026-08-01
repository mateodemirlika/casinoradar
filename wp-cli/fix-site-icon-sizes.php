<?php
/**
 * Idempotent: the site icon was set via the `site_icon` option directly rather
 * than through the Customizer's crop UI, so the dedicated "site_icon-{32,192,
 * 180,270}" intermediate sizes core's wp_site_icon() expects were never
 * generated. wp_site_icon() still hardcodes sizes="32x32" / sizes="192x192" on
 * the emitted <link> tags regardless, so it was serving the nearest available
 * theme-sized image (120x120, 300x300) mislabeled as 32x32/192x192 — a
 * mismatch that made Google (and some browsers) reject the favicon. Run via
 * `wp eval-file wp-cli/fix-site-icon-sizes.php`.
 */

defined( 'ABSPATH' ) || exit;

$site_icon_id = (int) get_option( 'site_icon' );
if ( ! $site_icon_id ) {
	WP_CLI::log( 'No site icon set, nothing to do.' );
	return;
}

$file = get_attached_file( $site_icon_id );
if ( ! $file || ! file_exists( $file ) ) {
	WP_CLI::warning( "Site icon attachment {$site_icon_id} has no file on disk." );
	return;
}

$metadata = wp_get_attachment_metadata( $site_icon_id );
$sizes    = array( 270, 192, 180, 32 );
$missing  = array();
foreach ( $sizes as $size ) {
	if ( empty( $metadata['sizes']['site_icon-' . $size] ) ) {
		$missing[] = $size;
	}
}

if ( empty( $missing ) ) {
	WP_CLI::success( 'All site icon sizes already present.' );
	return;
}

foreach ( $missing as $size ) {
	add_image_size( 'site_icon-' . $size, $size, $size, true );
}

require_once ABSPATH . 'wp-admin/includes/image.php';
$new_metadata = wp_generate_attachment_metadata( $site_icon_id, $file );

foreach ( $missing as $size ) {
	remove_image_size( 'site_icon-' . $size );
}

if ( is_wp_error( $new_metadata ) || empty( $new_metadata ) ) {
	WP_CLI::error( 'Failed to regenerate attachment metadata for the site icon.' );
}
wp_update_attachment_metadata( $site_icon_id, $new_metadata );

WP_CLI::success( 'Regenerated site icon sizes: ' . implode( ', ', $missing ) );
