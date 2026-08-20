<?php
// display notice on admin edit screen if current saved post content does not contain an H1 tag

//add_action('save_post', 'lvl_admin_notice_no_h1');
function lvl_admin_notice_no_h1( $post_id ) {
//    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
//    if (!current_user_can('edit_post', $post_id)) return;
//
//    $post = get_post($post_id);
//    $post_content = $post->post_content;
//
//    if (strpos($post_content, '<h1') === false) {
//    }
}

//add_action('admin_notices', 'lvl_admin_notice_no_h1_display');

function lvl_admin_notice_no_h1_display() {
	?>
    <div class="notice notice-warning">
        <p><?php _e( 'This post does not contain an H1 tag.', 'theme' ); ?></p>
    </div>
	<?php
}


// TODO: TBD
function lvl_multisite_register_post_meta() {
	if ( ! is_multisite() ) {
		return;
	}

	register_meta(
		'post',
		'_lvl_multisite_post_clone_to_process',
		[
			'type'              => 'array',
			'default'           => [],
			'description'       => 'Whether or not to clone this post to another site.',
			'single'            => true,
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array(
						'type' => 'integer',
					),
				),
			),
			'auth_callback'     => function ( $allowed, $meta_key, $object_id, $user_id, $cap, $caps ) {
				return current_user_can( 'edit_resume', $object_id );
			},
			'sanitize_callback' => function ( $meta_value, $meta_key, $object_type ) {
				return $meta_value;
			},
		]
	);

	// meta field to say this post was cloned from this other post storing json array of blog_id and post_id
	// [[blog_id, post_id]]
	register_meta(
		'post',
		'_lvl_multisite_post_cloned_to',
		[
			'type'              => 'array',
			'default'           => [],
			'description'       => 'Cloned to blog_id and post_id.',
			'single'            => true,
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array(
						'type' => 'integer',
					),
				),
			),
			'auth_callback'     => function ( $allowed, $meta_key, $object_id, $user_id, $cap, $caps ) {
				return current_user_can( 'edit_resume', $object_id );
			},
			'sanitize_callback' => function ( $meta_value, $meta_key, $object_type ) {
				return $meta_value;
			},
		]
	);

}

//add_action('init', 'lvl_multisite_register_post_meta');


// add a checkbox field to the side of the editor
function lvl_multisite_add_checkbox() {
	if ( ! is_multisite() ) {
		return;
	}
	$post_types = get_post_types( [ 'public' => true ], 'names' );
	foreach ( $post_types as $post_type ) {
		add_meta_box(
			'lvl_multisite_checkbox',
			'Clone to other sites',
			'lvl_multisite_checkbox_html',
			$post_type,
			'side',
			'high'
		);
	}
}

//add_action('add_meta_boxes', 'lvl_multisite_add_checkbox');

// display the checkbox field
function lvl_multisite_checkbox_html( $post ) {
	if ( ! is_multisite() ) {
		return;
	}

	$sites     = get_sites();
	$post_meta = get_post_meta( $post->ID, '_lvl_multisite_post_clone_to_process', true );
	$post_meta = is_array( $post_meta ) ? $post_meta : [];
	?>
    <div>
        <p clas="small" style="margin: .5em 0;line-height:1.1;"><?php _e( 'Select all sites to clone to.', 'theme' ); ?></p>
		<?php foreach ( $sites as $site ) :
			if ( $site->blog_id == get_current_blog_id() ) {
				continue;
			}
			?>
            <label>
                <input type="checkbox" name="lvl_multisite_post_clone[]"
                       value="<?php echo $site->blog_id; ?>" <?php checked( in_array( $site->blog_id, $post_meta ) ); ?>>
				<?php echo $site->blogname; ?>
            </label>
            <br>
		<?php endforeach; ?>
    </div>
	<?php
	// option for cloned post status
	?>
    <hr>
    <div>
        <h3><?php _e( 'Cloned status', 'theme' ); ?></h3>
        <label>
            <input type="checkbox" name="lvl_multisite_post_clone_status" checked value="draft">
			<?php _e( 'Draft', 'theme' ); ?>
        </label>
        <p clas="small" style="margin: .5em;color:var(--bs-danger,darkred);line-height:1.1;"><?php _e( 'If unchecked, the post will be published when cloned to other sites.', 'theme' ); ?></p>
    </div>
	<?php

	return;

	// TODO: FINISH RESYNCING POSTS ON OTHER SITES WHEN POST IS UPDATED
	// display option to sync post to other sites if it was cloned to another site
	// _lvl_multisite_post_cloned_to is stored as json array of blog_id and post_id
	$post_meta_cloned_to = get_post_meta( $post->ID, '_lvl_multisite_post_cloned_to', true );
	$cloned_to           = ( is_serialized( $post_meta_cloned_to, true ) ? unserialize( $post_meta_cloned_to ) : ( $post_meta_cloned_to ?: [] ) );

	if ( ! empty( $cloned_to ) && is_array( $cloned_to ) && count( $cloned_to ) > 0 ) {
		?>
        <div>
            <label>
                <input type="checkbox" name="lvl_multisite_post_clone_sync" value="1">
                Sync to other sites
            </label>
            <hr>
            <h3><?php _e( 'Cloned to', 'theme' ); ?></h3>
			<?php foreach ( $cloned_to as $cloned ) : ?>
                <p><?php echo $cloned['blog_id'] . ' - ' . $cloned['post_id']; ?></p>
			<?php endforeach; ?>
        </div>
		<?php
	}
}

// save the checkbox field
function lvl_multisite_save_checkbox( $post_id ) {
	if ( ! is_multisite() ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$post_meta = $_POST['lvl_multisite_post_clone'] ?? [];
	update_post_meta( $post_id, '_lvl_multisite_post_clone_to_process', $post_meta );

	$post_meta_clone_status = $_POST['lvl_multisite_post_clone_status'] ?? 'publish';
	update_post_meta( $post_id, '_lvl_multisite_post_clone_status', $post_meta_clone_status );

	$post_meta_clone_sync = $_POST['lvl_multisite_post_clone_sync'] ?? false;
	update_post_meta( $post_id, '_lvl_multisite_post_clone_sync', $post_meta_clone_sync );
}

//add_action('save_post', 'lvl_multisite_save_checkbox');

// clone the post to other sites
function lvl_multisite_clone_post( $post_id, $post ) {
	if ( ! is_multisite() ) {
		return;
	}
	$all_meta_for_post = get_post_meta( $post_id );

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$post_meta_process = get_post_meta( $post_id, '_lvl_multisite_post_clone_to_process', true );
	$post_meta_process = is_array( $post_meta_process ) ? $post_meta_process : [];
	$post_meta_process = array_map( 'intval', $post_meta_process );

	$post_meta_clone_status = get_post_meta( $post_id, '_lvl_multisite_post_clone_status', true );

	// TODO: FINISH RESYNCING POSTS ON OTHER SITES WHEN POST IS UPDATED
	$post_meta_clone_sync = get_post_meta( $post_id, '_lvl_multisite_post_clone_sync', true );
	$post_meta_cloned_to  = get_post_meta( $post_id, '_lvl_multisite_post_cloned_to', true );

	// delete meta data so it doesn't get cloned
	delete_post_meta( $post_id, '_lvl_multisite_post_clone_to_process' );
	delete_post_meta( $post_id, '_lvl_multisite_post_clone_status' );
	delete_post_meta( $post_id, '_lvl_multisite_post_clone_sync' );
	delete_post_meta( $post_id, '_lvl_multisite_post_cloned_to' );

	// TODO: cloned_to data gets lost here
	$cloned_to = ( is_serialized( $post_meta_cloned_to, true ) ? unserialize( $post_meta_cloned_to ) : ( $post_meta_cloned_to ?: [] ) );

	$sites = get_sites();
	foreach ( $sites as $site ) {

		if ( $site->blog_id == get_current_blog_id() ) {
			continue;
		}

		if ( in_array( $site->blog_id, $post_meta_process ) ) {

			lvl_clone_post( $post_id, get_current_blog_id(), $site->blog_id );

//            switch_to_blog($site->blog_id);
//
//            if ($post_meta_clone_status == 'draft') {
//                $post->post_status = 'draft';
//            } else {
//                $post->post_status = 'publish';
//            }
//
//            if ($post_meta_clone_sync && !empty($post_meta_cloned_to)) {
//                foreach ($cloned_to as $cloned) {
//                    if ($cloned['blog_id'] == $site->blog_id) {
//                        $post->ID = $cloned['post_id'];
//                        $post->post_title = $post->post_title . '--SYNCED';
//                        wp_update_post($post);
//                    }
//                }
//            } else {
//                $post->ID = null;
//                $new_post_id = wp_insert_post($post);
//                $new_post_blog_id = get_current_blog_id();
//                $cloned_to[] = ['blog_id' => $new_post_blog_id, 'post_id' => $new_post_id];
//            }
//
//            restore_current_blog();
//
//            // TODO: FINISH RESYNCING POSTS ON OTHER SITES WHEN POST IS UPDATED
//            update_post_meta($post_id, '_lvl_multisite_post_cloned_to', maybe_serialize($cloned_to));
		}
	}
}

//add_action('wp_insert_post', 'lvl_multisite_clone_post', 10, 2);

// add a column to the posts list
function lvl_multisite_add_column( $columns ) {
	if ( ! is_multisite() ) {
		return;
	}
	$columns['lvl_multisite'] = 'Clone to other sites';

	return $columns;
}

//add_filter('manage_posts_columns', 'lvl_multisite_add_column');

// display the column content
function lvl_multisite_display_column( $column, $post_id ) {
	if ( ! is_multisite() ) {
		return;
	}
	if ( $column == 'lvl_multisite' ) {
		$post_meta = get_post_meta( $post_id, '_lvl_multisite_post_clone_to_process', true );
		$post_meta = is_array( $post_meta ) ? $post_meta : [];
		$post_meta = array_map( 'intval', $post_meta );
		$sites     = get_sites();
		foreach ( $sites as $site ) {
			if ( in_array( $site->blog_id, $post_meta ) ) {
				echo $site->blogname . '<br>';
			}
		}
	}
}

//add_action('manage_posts_custom_column', 'lvl_multisite_display_column', 10, 2);

// add a filter to the posts list
function lvl_multisite_add_filter() {
	if ( ! is_multisite() ) {
		return;
	}
	$post_types = get_post_types( [ 'public' => true ], 'names' );
	foreach ( $post_types as $post_type ) {
		add_filter( "manage_{$post_type}_posts_columns", 'lvl_multisite_add_column' );
	}
}

//add_action('admin_init', 'lvl_multisite_add_filter');


/**
 * @param int      $post_id Post ID
 * @param int|null $source_site_id Source site ID
 * @param int|null $destination_site_id Destination site ID
 *
 * @return int
 */
function lvl_clone_post( int $post_id, int $source_site_id = null, int $destination_site_id = null ): int {
	if ( $source_site_id == null ) {
		$source_site_id = get_current_blog_id();
	}
	if ( $destination_site_id == null ) {
		$destination_site_id = $source_site_id;
	}

	switch_to_blog( $source_site_id );

	$post = get_post( $post_id );
	if ( ! $post ) {
		restore_current_blog();

		return 0;
	}

	$post_meta        = get_post_meta( $post_id );
	$post_attachments = get_attached_media( '', $post_id );
	if ( has_post_thumbnail( $post_id ) ) {
		$post_attachments[] = get_post_thumbnail_id( $post_id );
	}
	$post_acf = get_fields( $post_id );

	// Get taxonomy terms
	$taxonomies     = get_object_taxonomies( $post );
	$taxonomy_terms = array();
	foreach ( $taxonomies as $taxonomy ) {
		$terms = wp_get_post_terms( $post_id, $taxonomy );
		foreach ( $terms as $term ) {
//            $taxonomy_terms[$taxonomy][] = $term->term_id;
			$taxonomy_terms[ $taxonomy ][] = $term->name;
		}
	}

	restore_current_blog();

	switch_to_blog( $destination_site_id );

	$new_post_args = array(
		'post_title'   => $post->post_title . ' (Clone)',
		'post_content' => $post->post_content,
		'post_status'  => 'draft',
		'post_type'    => $post->post_type,
		'post_author'  => $post->post_author,
	);

	$new_post_id = wp_insert_post( $new_post_args );

	foreach ( $post_meta as $meta_key => $meta_values ) {
		foreach ( $meta_values as $meta_value ) {
			add_post_meta( $new_post_id, $meta_key, $meta_value );
		}
	}

	foreach ( $post_attachments as $attachment ) {
		$attachment_data = array(
			'post_title'     => $attachment->post_title,
			'post_content'   => $attachment->post_content,
			'post_status'    => 'inherit',
			'post_mime_type' => $attachment->post_mime_type,
			'post_parent'    => $new_post_id,
		);

		$new_attachment_id   = wp_insert_attachment( $attachment_data, $attachment->guid );
		$attachment_metadata = wp_generate_attachment_metadata( $new_attachment_id, get_attached_file( $attachment->ID ) );
		wp_update_attachment_metadata( $new_attachment_id, $attachment_metadata );
	}

	// Copy taxonomy terms
	$new_terms = array();
	foreach ( $taxonomy_terms as $taxonomy => $terms ) {
		// add term by name and create if it doesn't exist
		foreach ( $terms as $term ) {
			$term_exists = term_exists( $term, $taxonomy );
			if ( ! $term_exists ) {
				$term_exists = wp_insert_term( $term, $taxonomy );
			}
			$new_terms[] = $term_exists['term_id'];
		}


		wp_set_post_terms( $new_post_id, $new_terms, $taxonomy );
	}

	// If using Advanced Custom Fields (ACF)
	foreach ( $post_acf as $key => $value ) {
		// check if field is a repeater
		if ( is_array( $value ) ) {
			foreach ( $value as $sub_key => $sub_value ) {
				foreach ( $sub_value as $sub_sub_key => $sub_sub_value ) {
					update_field( $sub_sub_key, $sub_sub_value, $new_post_id );
				}
			}
		}

		// check if field is file
		if ( is_numeric( $value ) ) {
			switch_to_blog( $source_site_id );

			$attachment = get_post( $value );

			switch_to_blog( $destination_site_id );

			$attachment_data     = array(
				'post_title'     => $attachment->post_title,
				'post_content'   => $attachment->post_content,
				'post_status'    => 'inherit',
				'post_mime_type' => $attachment->post_mime_type,
				'post_parent'    => $new_post_id,
			);
			$new_attachment_id   = wp_insert_attachment( $attachment_data, $attachment->guid );
			$attachment_metadata = wp_generate_attachment_metadata( $new_attachment_id, get_attached_file( $new_attachment_id ) );
			wp_update_attachment_metadata( $new_attachment_id, $attachment_metadata );
		}


		update_field( $key, $value, $new_post_id );
	}

	restore_current_blog();

	return $new_post_id;
}

//add_action('admin_action_clone_post', 'clone_post');