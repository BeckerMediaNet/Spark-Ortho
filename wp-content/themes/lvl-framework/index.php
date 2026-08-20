<?php get_header();

$archive_page = false;

if ( is_home() ) {

	$archive_page = get_option( 'page_for_posts' );

	$page = get_post( $archive_page );
	echo apply_filters( 'the_content', $page->post_content );
} elseif ( have_posts() ) {
    $post_type = get_post_type();
    $archive_page = get_option($post_type . '_archive_page');
}


if ($archive_page) {
    $args = array(
        'post_type' => 'page',
        'p'         => $archive_page,
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            the_content();
        }
    }
} else {
	?>
    <div class="container">
        <h1><?php post_type_archive_title(); ?></h1>
        <div class="row">
			<?php
			while ( have_posts() ) {
				the_post();
				?>
                <div class="col-12 col-md-6 col-lg-4">
					<?php
					the_title( '<h2>', '</h2>' );
					the_excerpt();
					the_permalink();
					?>
                </div>
				<?php
			}
			?>
        </div>
    </div>
	<?php
}

get_footer();