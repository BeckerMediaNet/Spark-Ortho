<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

add_action( 'init', 'setup_post_unpublisher' );

function setup_post_unpublisher() {
	// Add meta box to post editor
	add_action( 'add_meta_boxes', 'add_unpublish_date_meta_box' );
	// Save meta box data
	add_action( 'save_post', 'save_unpublish_date' );
	// Check for posts to unpublish
	add_action( 'wp', 'check_and_unpublish_posts' );
}

function add_unpublish_date_meta_box() {
	add_meta_box(
		'unpublish_date_meta_box',
		'Unpublish Date',
		'display_unpublish_date_meta_box',
		'post',
		'side',
		'high'
	);
}

function display_unpublish_date_meta_box( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'unpublish_date_nonce' );
	$unpublish_date = get_post_meta( $post->ID, '_unpublish_date', true );
	?>
    <p>
        <label for="unpublish_date">Set date to unpublish:</label>
        <input type="datetime-local" id="unpublish_date" name="unpublish_date" value="<?php echo esc_attr( $unpublish_date ); ?>">
    </p>
    <p>Leave blank to keep the post published indefinitely.</p>
	<?php
}

function save_unpublish_date( $post_id ) {
	if ( ! isset( $_POST['unpublish_date_nonce'] ) || ! wp_verify_nonce( $_POST['unpublish_date_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	$unpublish_date = isset( $_POST['unpublish_date'] ) ? sanitize_text_field( $_POST['unpublish_date'] ) : '';
	update_post_meta( $post_id, '_unpublish_date', $unpublish_date );
}

function check_and_unpublish_posts() {
	$args = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => - 1,
		'meta_query'     => array(
			array(
				'key'     => '_unpublish_date',
				'value'   => '',
				'compare' => '!=',
			),
		),
	);

	$query = new WP_Query( $args );

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id        = get_the_ID();
			$unpublish_date = get_post_meta( $post_id, '_unpublish_date', true );

			if ( ! empty( $unpublish_date ) && current_time( 'mysql' ) >= $unpublish_date ) {
				wp_update_post( array(
					'ID'          => $post_id,
					'post_status' => 'draft',
				) );
			}
		}
	}

	wp_reset_postdata();
}