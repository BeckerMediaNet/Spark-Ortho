<?php
// TODO for testing only ---v
//add_filter('init', 'flushRules');
function flushRules()
{
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
}

// capture urls for positions
//add_action('init', 'lvl_rewrites_init');
function lvl_rewrites_init()
{
    add_rewrite_rule(
        'kitchen-sink',
        'index.php?position=$matches[2]',
        'top');
}

//add_action( 'init', function() {
//    add_rewrite_endpoint( 'testing', EP_PERMALINK );
//} );


// create fake post to use for positions
//add_action('template_redirect', 'lvl_virtual_page');
function lvl_virtual_page(): void
{
    global $wp_query, $wp;

    status_header(200);
    if ( $wp_query->query_vars['name'] !== 'kitchen-sink'  ) {
        return;
    }
    locate_template('page-kitchen-sink.php', true, true);

//    die;
    $post_id = -99; // negative ID, to avoid clash with a valid post
    $post = new stdClass();

    $post->ID = $post_id;
    $post->post_author = 1;
    $post->post_date = current_time('mysql');
    $post->post_date_gmt = current_time('mysql', 1);
    $post->post_title = sanitize_text_field('Kitchen Sink');
    $post->post_content = '';
    $post->post_status = 'publish';
    $post->comment_status = 'closed';
    $post->ping_status = 'closed';
    $post->post_name = 'kitchen-sink';
    $post->post_type = 'page';
    $post->filter = 'raw'; // important!
    $post->excerpt = '';
    $post->permalink = site_url() . '/kitchen-sink/';

// Update the main query
    $wp_query->post = $post;
    $wp_query->posts = array($post);
    $wp_query->queried_object = $post;
    $wp_query->queried_object_id = $post_id;
    $wp_query->found_posts = 1;
    $wp_query->post_count = 1;
    $wp_query->max_num_pages = 1;
    $wp_query->is_page = true;
    $wp_query->is_singular = true;
    $wp_query->is_single = true;
    $wp_query->is_attachment = false;
    $wp_query->is_archive = false;
    $wp_query->is_category = false;
    $wp_query->is_tag = false;
    $wp_query->is_tax = false;
    $wp_query->is_author = false;
    $wp_query->is_date = false;
    $wp_query->is_year = false;
    $wp_query->is_month = false;
    $wp_query->is_day = false;
    $wp_query->is_time = false;
    $wp_query->is_search = false;
    $wp_query->is_feed = false;
    $wp_query->is_comment_feed = false;
    $wp_query->is_trackback = false;
    $wp_query->is_home = false;
    $wp_query->is_embed = false;
    $wp_query->is_404 = false;
    $wp_query->is_paged = false;
    $wp_query->is_admin = false;
    $wp_query->is_preview = false;
    $wp_query->is_robots = false;
    $wp_query->is_posts_page = false;
    $wp_query->is_post_type_archive = false;

    $GLOBALS['wp_query'] = $wp_query;
    $wp->register_globals();
//
    add_filter('wpseo_opengraph_title', 'position_change_title');
    add_filter('wpseo_metadesc', 'position_change_desc');
    add_filter('wpseo_opengraph_desc', 'position_change_desc');
    add_filter('wpseo_opengraph_url', 'position_change_url');
//
    add_filter('document_title_parts', 'position_title_set');
}