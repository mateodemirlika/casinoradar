<?php
/**
 * Generic admin meta box UI driven by wagerwise_meta_schema() — this is the
 * free, native replacement for ACF's field groups. One renderer/saver works
 * for every post type; adding a field only means editing the schema array.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes', 'wagerwise_add_meta_boxes' );

function wagerwise_add_meta_boxes(): void {
	foreach ( array_keys( wagerwise_meta_schema() ) as $post_type ) {
		add_meta_box(
			'wagerwise_details',
			__( 'WagerWise Details', 'wagerwise' ),
			'wagerwise_render_meta_box',
			$post_type,
			'normal',
			'high'
		);
	}
}

function wagerwise_render_meta_box( WP_Post $post ): void {
	$fields = wagerwise_meta_schema()[ $post->post_type ] ?? array();
	wp_nonce_field( 'wagerwise_save_meta', 'wagerwise_meta_nonce' );
	echo '<table class="form-table wagerwise-meta-table"><tbody>';

	foreach ( $fields as $key => $spec ) {
		$value = get_post_meta( $post->ID, $key, true );
		echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $spec['label'] ) . '</label></th><td>';
		wagerwise_render_field( $key, $spec, $value );
		echo '</td></tr>';
	}

	echo '</tbody></table>';
	wagerwise_repeater_script();
}

function wagerwise_render_field( string $key, array $spec, $value ): void {
	switch ( $spec['field'] ) {
		case 'textarea':
			printf( '<textarea class="large-text" rows="4" name="%1$s" id="%1$s">%2$s</textarea>', esc_attr( $key ), esc_textarea( (string) $value ) );
			break;

		case 'checkbox':
			printf(
				'<label><input type="checkbox" name="%1$s" id="%1$s" value="1" %2$s /> %3$s</label>',
				esc_attr( $key ),
				checked( (bool) $value, true, false ),
				esc_html__( 'Yes', 'wagerwise' )
			);
			break;

		case 'date':
			printf( '<input type="date" name="%1$s" id="%1$s" value="%2$s" />', esc_attr( $key ), esc_attr( (string) $value ) );
			break;

		case 'number':
			printf(
				'<input type="number" step="%3$s" min="%4$s" max="%5$s" name="%1$s" id="%1$s" value="%2$s" class="small-text" />',
				esc_attr( $key ),
				esc_attr( (string) $value ),
				esc_attr( $spec['step'] ?? '1' ),
				esc_attr( (string) ( $spec['min'] ?? '' ) ),
				esc_attr( (string) ( $spec['max'] ?? '' ) )
			);
			break;

		case 'url':
			printf( '<input type="url" class="large-text" name="%1$s" id="%1$s" value="%2$s" placeholder="https://" />', esc_attr( $key ), esc_attr( (string) $value ) );
			break;

		case 'post_select':
			$posts = get_posts( array( 'post_type' => $spec['post_type'], 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
			echo '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '">';
			echo '<option value="">' . esc_html__( '— None —', 'wagerwise' ) . '</option>';
			foreach ( $posts as $p ) {
				printf( '<option value="%d" %s>%s</option>', $p->ID, selected( (int) $value, $p->ID, false ), esc_html( $p->post_title ) );
			}
			echo '</select>';
			break;

		case 'repeater':
			$rows = is_array( $value ) ? $value : array();
			if ( empty( $rows ) ) {
				$rows = array( '' );
			}
			echo '<div class="wagerwise-repeater" data-key="' . esc_attr( $key ) . '">';
			foreach ( $rows as $row ) {
				echo '<div class="wagerwise-repeater-row">';
				printf( '<input type="text" class="large-text" name="%1$s[]" value="%2$s" />', esc_attr( $key ), esc_attr( $row ) );
				echo ' <button type="button" class="button wagerwise-remove-row">&times;</button></div>';
			}
			echo '</div>';
			echo '<button type="button" class="button wagerwise-add-row" data-key="' . esc_attr( $key ) . '">' . esc_html__( '+ Add row', 'wagerwise' ) . '</button>';
			break;

		case 'text':
		default:
			printf( '<input type="text" class="large-text" name="%1$s" id="%1$s" value="%2$s" />', esc_attr( $key ), esc_attr( (string) $value ) );
			break;
	}
}

function wagerwise_repeater_script(): void {
	static $printed = false;
	if ( $printed ) {
		return;
	}
	$printed = true;
	?>
	<script>
	(function () {
		function addRow( wrap ) {
			var row = document.createElement( 'div' );
			row.className = 'wagerwise-repeater-row';
			row.innerHTML = '<input type="text" class="large-text" name="' + wrap.dataset.key + '[]" value="" /> <button type="button" class="button wagerwise-remove-row">&times;</button>';
			wrap.appendChild( row );
		}
		document.addEventListener( 'click', function ( e ) {
			if ( e.target.classList.contains( 'wagerwise-add-row' ) ) {
				var wrap = document.querySelector( '.wagerwise-repeater[data-key="' + e.target.dataset.key + '"]' );
				addRow( wrap );
			}
			if ( e.target.classList.contains( 'wagerwise-remove-row' ) ) {
				e.target.closest( '.wagerwise-repeater-row' ).remove();
			}
		} );
	})();
	</script>
	<style>
		.wagerwise-repeater-row { margin-bottom: 6px; display: flex; gap: 6px; align-items: center; }
		.wagerwise-meta-table th { width: 220px; }
	</style>
	<?php
}

add_action( 'save_post', 'wagerwise_save_meta_box' );

function wagerwise_save_meta_box( int $post_id ): void {
	if ( ! isset( $_POST['wagerwise_meta_nonce'] ) || ! wp_verify_nonce( $_POST['wagerwise_meta_nonce'], 'wagerwise_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$post_type = get_post_type( $post_id );
	$fields    = wagerwise_meta_schema()[ $post_type ] ?? array();

	foreach ( $fields as $key => $spec ) {
		if ( 'repeater' === $spec['field'] ) {
			$raw = isset( $_POST[ $key ] ) ? (array) $_POST[ $key ] : array();
			$clean = array_values( array_filter( array_map( 'sanitize_text_field', $raw ), fn( $v ) => '' !== $v ) );
			update_post_meta( $post_id, $key, $clean );
			continue;
		}

		if ( 'checkbox' === $spec['field'] ) {
			update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? 1 : 0 );
			continue;
		}

		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}

		$raw = wp_unslash( $_POST[ $key ] );

		$clean = match ( $spec['field'] ) {
			'url'      => esc_url_raw( $raw ),
			'textarea' => sanitize_textarea_field( $raw ),
			'number', 'post_select' => is_numeric( $raw ) ? $raw + 0 : '',
			default    => sanitize_text_field( $raw ),
		};

		update_post_meta( $post_id, $key, $clean );
	}
}
