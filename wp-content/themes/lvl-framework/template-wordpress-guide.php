<?php
/*
*
* Template Name: Login Required (no index)
* Template Post Type: page
*
*/

// redirect to login if not logged in
if (!is_user_logged_in()) {
    auth_redirect();
}

add_filter('wpseo_robots', function ($robots) {
    $robots = str_replace('index', 'noindex', $robots);
    return $robots;

}, 10, 1);

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();
        the_content();
    endwhile;
endif;

get_footer();