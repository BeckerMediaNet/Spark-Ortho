<?php get_header();

$archive_page = false;

if (is_home()) {
	
	$page = get_post(get_option('page_for_posts'));
	echo apply_filters('the_content', $page->post_content);

} elseif (have_posts()) {

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
}

get_footer();