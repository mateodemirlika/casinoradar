<?php
/**
 * Front-end form handlers. The complaint form on the Complaints page posts
 * here rather than storing directly as a public "complaint" post — intake
 * is private (emailed to the site admin); only complaints an editor
 * deliberately publishes as resolved show up in the public list.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_post_wagerwise_submit_complaint', 'wagerwise_handle_complaint_submission' );
add_action( 'admin_post_nopriv_wagerwise_submit_complaint', 'wagerwise_handle_complaint_submission' );

function wagerwise_handle_complaint_submission(): void {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'wagerwise_submit_complaint' ) ) {
		wp_die( esc_html__( 'Security check failed. Please go back and try again.', 'wagerwise' ) );
	}

	$name    = sanitize_text_field( wp_unslash( $_POST['ww_name'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['ww_email'] ?? '' ) );
	$casino  = sanitize_text_field( wp_unslash( $_POST['ww_casino'] ?? '' ) );
	$issue   = sanitize_text_field( wp_unslash( $_POST['ww_issue'] ?? '' ) );
	$amount  = sanitize_text_field( wp_unslash( $_POST['ww_amount'] ?? '' ) );
	$details = sanitize_textarea_field( wp_unslash( $_POST['ww_details'] ?? '' ) );

	if ( ! $name || ! is_email( $email ) || ! $details ) {
		wp_die( esc_html__( 'Please fill in your name, a valid email, and the details of your complaint.', 'wagerwise' ) );
	}

	$body = sprintf(
		"New complaint submitted via CasinoRadar:\n\nName: %s\nEmail: %s\nCasino: %s\nIssue type: %s\nAmount in dispute: %s\n\nDetails:\n%s",
		$name,
		$email,
		$casino ?: '—',
		$issue ?: '—',
		$amount ?: '—',
		$details
	);

	wp_mail(
		get_option( 'admin_email' ),
		sprintf( '[CasinoRadar] New complaint: %s', $casino ?: 'Unspecified casino' ),
		$body,
		array( 'Reply-To: ' . $name . ' <' . $email . '>' )
	);

	$redirect = wp_get_referer() ?: home_url( '/' );
	wp_safe_redirect( add_query_arg( 'complaint', 'sent', $redirect ) . '#complaint-form' );
	exit;
}
