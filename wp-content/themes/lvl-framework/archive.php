<?php get_header();

$post_type    = false;
$archive_page = false;
$default_page = true;

if ( is_archive() ) {
	// is post type archive
	if ( is_post_type_archive() ) {
		$post_type    = get_post_type();
		$archive_page = get_option( $post_type . '_archive_page' );
	}

	// is taxonomy archive
	if ( is_category() || is_tax() ) {
		$url_path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
		$url_path = explode( '/', $url_path );

//        $post_type = $url_path[1]??'';
//	    foreach (get_post_types([],'rewrite') as $post_type_obj) {
//            if ( ( $post_type_obj->rewrite['slug'] ?? '') === $post_type ) {
//                $post_type = $post_type_obj->name;
//                break;
//            }
//	    }
//
//        $archive_page = get_option( $post_type . '_archive_page' );

		$post_type = get_post_type(); //$url_path[1] ?? '';
		$tax_type  = $url_path[2] ?? '';
		$tax_value = $url_path[3] ?? '';
        $tax_label = get_taxonomy( $tax_type )->labels->singular_name;

        $listing = '<!-- wp:lvl/banner {"name":"lvl/banner","data":{"field_65808a34b1252":{"field_65808a6bb1254":"","field_65b945d12d27d":"center","field_65b946022d27e":"cover","field_65b94c4f5123a":"1","field_65b94caa5123b":"1"},"field_hero_657c9d3f053d6":"1","field_65aff5ef887d0":"337","field_65808bc2b1259":"100","field_6580b4e2a1968":"end","field_6580b4e2a1968_padding":"0","field_65ef3e1418dc8":{"title":"","url":"","target":""}},"mode":"preview","backgroundColor":"\u002d\u002dbs-secondary"} -->
<!-- wp:lvl/breadcrumb {"name":"lvl/breadcrumb","data":{"header_level":"h1","_header_level":"field_breadcrumb_header_level","id":"665e1e1b8a9c6"},"mode":"preview"} /-->

<!-- wp:heading {"fontSize":"h1-inner"} -->
<h2 class="wp-block-heading has-h-1-inner-font-size">'. $tax_label .'</h2>
<!-- /wp:heading -->
<!-- /wp:lvl/banner -->';



		$listing .= '<!-- wp:lvl/section-wrapper {"name":"lvl/section-wrapper","data":{"id":"6633818c7c5a7"},"mode":"preview","anchor":"lvl-section-wrapper-j6c4o0x","verticalPadding":{"individual":false,"top":9,"bottom":9}} -->
<!-- wp:lvl/post-listing {"name":"lvl/post-listing","data":{"post_types":["' . $post_type . '"],"_post_types":"field_6596f612762a3","per_page":"' . ( get_option( 'posts_per_page' ) ?? 12 ) . '","_per_page":"field_post_listing_6596f523762a4","featured_posts":"","_featured_posts":"field_66314d9ec6bd0","pagination":"load_more","_pagination":"post_listing_pagination","show_filter":"1","_show_filter":"field_6596f523762a2","taxonomies":["' . $tax_type . '"],"_taxonomies":"field_6596f63a762a4","display_options":["meta","featured_image","link"],"_display_options":"field_post_listing_display_options_key","card_layout":"column","_card_layout":"field_post_listing_card_layout","cards_per_row":"3","_cards_per_row":"field_post_listing_card_per_row","min_height":"318","_min_height":"field_post_listing_card_min_height","id":"665f01b29d3c0"},"mode":"preview","anchor":"lvl-post-listing-6y8iagr","className":"is-style-default"} /-->
<!-- /wp:lvl/section-wrapper -->';

		$listing = parse_blocks( $listing );

		foreach ( $listing as $block ) {
			echo render_block( $block );
		}

		$default_page = false;
	}

	if ( $archive_page ) {
		$page = get_post( $archive_page );
		echo apply_filters( 'the_content', $page->post_content );

		$default_page = false;
	}
}

if ( have_posts() && $default_page ) {
	?>
    <div class="container">
        <h1><?php post_type_archive_title(); ?></h1>
        <div class="row">
            <div class="col-12 py-5">
				<?php
				while ( have_posts() ) {
					the_post();
					?>
                    <div class="mb-5">
						<?php
						the_title( '<h2>', '</h2>' );
						echo '<p>' . get_the_excerpt() . '</p>';
						?>
                        <a class="btn btn-arrow" href="<?php echo get_permalink(); ?>"><?php _e( 'Learn More', 'theme' ); ?><span class="visually-hidden"> <?php _e( 'about', 'theme' ); ?><?php echo get_the_title(); ?></span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="18" viewBox="0 0 18 9" fill="none">
                                <path d="M16 4.26099L16.7071 3.55388L17.4142 4.26099L16.7071 4.96809L16 4.26099ZM1 5.26099H0V3.26099H1V5.26099ZM13.2071 0.0538796L16.7071 3.55388L15.2929 4.96809L11.7929 1.46809L13.2071 0.0538796ZM16.7071 4.96809L13.2071 8.46809L11.7929 7.05388L15.2929 3.55388L16.7071 4.96809ZM16 5.26099H1V3.26099H16V5.26099Z" fill="currentColor"/>
                            </svg>
                        </a>
						<?php
						?>
                    </div>
					<?php
				}
				?>
            </div>
            <div class="col-12 pb-5">
				<?php
				the_posts_pagination( array(
					'prev_text'          => __( 'Previous page' ),
					'next_text'          => __( 'Next page' ),
					'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page' ) . ' </span>',
				) );
				?>
            </div>
        </div>
    </div>
	<?php
}

get_footer();