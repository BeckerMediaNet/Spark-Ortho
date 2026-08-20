<?php
/**
 * lvl_register_all_post_types function.
 * Registers all custom post types for the theme. Hooks into init.
 * @access public
 * @return void
 */
// add_action( 'init', 'lvl_register_all_post_types' );
function lvl_register_all_post_types(): void {
	
}

// function to create a setting for a custom post type archive page like setting the page for posts
function lvl_cpt_archive_page_setting() {
	$public_post_types = get_post_types( [
		'public'      => true,
		'has_archive' => true,
	], 'names', 'and' );
	// exclude system post types
	$exclude           = [ 'post', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'acf-field-group', 'acf-field' ];
	$public_post_types = array_diff( $public_post_types, $exclude );

	foreach ( $public_post_types as $post_type ) {
		register_setting( 'reading', $post_type . '_archive_page' );
		add_settings_field( $post_type . '_archive_page', ucfirst( $post_type ) . ' Archive Page', 'lvl_cpt_archive_page_setting_field', 'reading', 'default', [ 'post_type' => $post_type ] );
	}

	// add page listing post-state
	add_filter( 'display_post_states', 'lvl_cpt_archive_page_post_state', 10, 2 );
}



// add the setting to the reading settings page // TODO: review usefulness
add_action( 'admin_init', 'lvl_cpt_archive_page_setting' );

function lvl_cpt_archive_page_post_state( $post_states, $post ) {
	$public_post_types = get_post_types( [
		'public'      => true,
		'has_archive' => true,
	], 'names', 'and' );
	// exclude system post types
	$exclude           = [ 'post', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'acf-field-group', 'acf-field' ];
	$public_post_types = array_diff( $public_post_types, $exclude );

	foreach ( $public_post_types as $post_type ) {
		$archive_page = get_option( $post_type . '_archive_page' );
		if ( $archive_page == $post->ID ) {
			$post_states[] = ucfirst( $post_type ) . ' Archive Page';
		}
	}

	return $post_states;
}

function lvl_cpt_archive_page_setting_field( $args ) {
	$pages = get_pages();

	$selected = get_option( $args['post_type'] . '_archive_page' );
	?>
    <select name="<?php echo $args['post_type']; ?>_archive_page" id="<?php echo $args['post_type']; ?>_archive_page">
        <option value="">Select a page</option>
		<?php foreach ( $pages as $page ) : ?>
			<?php $selected_page = $selected == $page->ID ? 'selected' : ''; ?>
            <option value="<?php echo $page->ID; ?>" <?php echo $selected_page; ?>><?php echo $page->post_title; ?></option>
		<?php endforeach; ?>
    </select>
	<?php
}
