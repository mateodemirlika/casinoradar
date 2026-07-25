<?php
/**
 * Player reviews are native WordPress comments on the casino post type, with
 * one extra field — a 1-5 star rating — stored as comment meta. Comments
 * stay under core's normal moderation settings (Settings → Discussion), so
 * public submissions never publish unreviewed.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function (): void {
	add_post_type_support( 'casino', 'comments' );
} );

add_filter( 'comment_form_fields', function ( array $fields ): array {
	if ( 'casino' !== get_post_type() ) {
		return $fields;
	}
	$rating_field = '<p class="comment-form-rating"><label for="ww_review_rating">' . esc_html__( 'Your Rating', 'wagerwise' ) . '</label> ' .
		'<select name="ww_review_rating" id="ww_review_rating" required>' .
		'<option value="">' . esc_html__( '— Select —', 'wagerwise' ) . '</option>';
	for ( $i = 5; $i >= 1; $i-- ) {
		$rating_field .= sprintf( '<option value="%1$d">%1$d / 5</option>', $i );
	}
	$rating_field .= '</select></p>';

	$comment_field = $fields['comment'] ?? '';
	unset( $fields['comment'] );
	$fields['rating']  = $rating_field;
	$fields['comment'] = $comment_field;
	return $fields;
} );

add_action( 'comment_post', function ( int $comment_id, $approved ): void {
	if ( isset( $_POST['ww_review_rating'] ) ) {
		$rating = (float) $_POST['ww_review_rating'];
		if ( $rating >= 1 && $rating <= 5 ) {
			update_comment_meta( $comment_id, 'ww_review_rating', $rating );
		}
	}
}, 10, 2 );
