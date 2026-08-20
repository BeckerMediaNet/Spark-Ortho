<?php if (!defined('ABSPATH')) exit;

//add_filter( 'gform_disable_form_theme_css', '__return_true' );

locate_template('lib/filters/mime.php', true, true);
locate_template('lib/filters/webp.php', true, true);
locate_template('lib/filters/style.php', true, true);
locate_template('lib/filters/duplicate-post.php', true, true);
locate_template('lib/filters/blocks.php', true, true);
locate_template('lib/filters/users.php', true, true);


// include_once 'filters/filter-term-clauses.php';

// removes some gutenberg inline styles that get auto generated
// remove_filter('render_block', 'wp_render_layout_support_flag', 10, 2);
remove_filter('render_block', 'gutenberg_render_layout_support_flag', 10, 2);
remove_filter('render_block', 'wp_render_elements_support', 10, 2);
remove_filter('render_block', 'gutenberg_render_elements_support', 10, 2);
// remove wp global styles if not being used
remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
remove_action('enqueue_block_editor_assets', 'wp_enqueue_global_styles_css_custom_properties');
remove_action('in_admin_header', 'wp_global_styles_render_svg_filters');

//add_action(
//    'wp_default_styles',
//    function( $styles ) {
//
//        /* Create an array with the two handles wp-block-library and
//         * wp-block-library-theme.
//         */
//        $handles = [ 'wp-block-library', 'wp-block-library-theme' ];
//
//        foreach ( $handles as $handle ) {
//            // Search and compare with the list of registered style handles:
//            $style = $styles->query( $handle, 'registered' );
//            if ( ! $style ) {
//                continue;
//            }
//            // Remove the style
//            $styles->remove( $handle );
//            // Remove path and dependencies
//            $styles->add( $handle, false, [] );
//        }
//    },
//    PHP_INT_MAX
//);


// DEFAULT IMAGES to LAZYLOAD
apply_filters('wp_lazy_loading_enabled', true, ['img', 'iframe'], 'wp_lazy_loading_enabled');

// TODO: add this as theme option
// disable WP guessing the url if the entered doesn't resolve to a page
// add_filter( 'redirect_canonical', 'remove_redirect_guess_404_permalink' );
/**
 * @param $redirect_url
 *
 * @return false|mixed
 */
function remove_redirect_guess_404_permalink($redirect_url): mixed
{
    if (is_404()) {
        return false;
    }//end if
    return $redirect_url;
}

// TODO: add this as theme option
/**
 * Enables the built-in WordPress link manager.
 *
 * https://codex.wordpress.org/Links_Manager
 *
 * @access public
 * @return void
 */
// add_filter( 'pre_option_link_manager_enabled', '__return_true' );


/**
 * lvl_custom_query_filter function.
 *
 * Used to manipulate the $query object before making a database request.
 *
 * @access public
 *
 * @param mixed $query
 *
 * @return object
 */
//if (!is_admin()) {
//    add_filter('pre_get_posts', 'lvl_custom_query_filter');
//}
function lvl_custom_query_filter($query)
{

    global $post;

    // if( is_tax( 'resource_type' ) ) $query->set_404();

    // if ($query->is_main_query() && is_post_type_archive()) {

    // 	switch ($query->query_vars['post_type']) {

    // 	}

    // }
    // TODO: add this as theme option
//    if ($query->is_main_query() && $query->is_search) {
//
//        $query->set('post_type', ['post', 'page', 'drt', 'event', 'case-study', 'resource-hub', 'brochure', 'podcast', 'person']);
//
//    }

    return $query;

}


/**
 * lvl_custom_content_output function.
 *
 * Used to append extra html markup to the output of the_content()
 *
 * @access public
 *
 * @param mixed $content
 *
 * @return string
 */
// add_filter( 'the_content', 'lvl_custom_content_output' );
function lvl_custom_content_output(mixed $content): string
{

    global $post;

    $post_type_obj = get_post_type_object($post->post_type);

    switch ($post->post_type) {


    }

    return $content;

}


/**
 * lvl_responsive_embed function.
 *
 * Filters the_content() and looks for iframes containing video players. We then add the responsive embed markup for bootstrap 3 around the iframe.
 *
 * @access public
 *
 * @param string $content
 *
 * @return string
 */
add_filter('the_content', 'lvl_responsive_embed', 98, 1);
function lvl_responsive_embed($content)
{

    $videopattern = '/<iframe.*src="(http)?s?:?\/\/(.*\.?vimeo.com.*|.*\.?youtube.com.*)".*<\/iframe>|<embed.*src="(http)?s?:?\/\/(.*\.?vimeo.com.*|.*\.?youtube.com.*)".*<\/embed>/';

    preg_match_all($videopattern, $content, $matches);

    foreach ($matches[0] as $match) {

        $flex_video_classes = '';

        if (preg_match('/<iframe/', $match)) {
            $flex_video_classes = ' iframe';
            $attr = ' loading="lazy"';
            str_replace('<iframe', '<iframe' . $attr, $match);
        }
        if (preg_match('/<embed/', $match)) {
            $flex_video_classes = ' embed';
        }

        $wrapped_frame = '<div class="embed-responsive embed-responsive-16by9' . $flex_video_classes . '">' . '<div class="embed-responsive-item ratio ratio-16x9">' . $match . '</div>' . '</div>';

        $content = str_replace($match, $wrapped_frame, $content);

    }

    return $content;

}

// Add responsiveness to oEmbed videos
add_filter('oembed_data_parse', 'lvl_wrap_oembed_data_parse', 99, 4);
/**
 * @param $return
 * @param $data
 * @param $url
 *
 * @return string
 */
function lvl_wrap_oembed_data_parse($return, $data, $url): string
{

    $attr = ' loading="lazy"';
    $return = str_replace('iframe', 'iframe' . $attr, $return);

    $class = 'ratio';

    // 4:3 video
    if (($data->type == 'video') && (isset($data->width)) && (isset($data->height)) && (round($data->height / $data->width, 2) == round(3 / 4, 2))) {
        $class = 'ratio-4x3';
    }

    // 16:9 video
    if (($data->type == 'video') && (isset($data->width)) && (isset($data->height)) && (round($data->height / $data->width, 2) == round(9 / 16, 2))) {
        $class = 'ratio-16x9';
    }

    if ($data->provider_name === 'YouTube') {
        $url_parse = parse_url($url);
        parse_str($url_parse['query'], $params);

        if (isset($params['v'])) {
            $return = '<div class="embed-youtube ratio ratio-16x9" data-video-id="' . ($params['v'] ?? '') . '"><div class="embed-youtube-play"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--! Font Awesome Pro 6.1.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path fill="currentColor" d="M361 215C375.3 223.8 384 239.3 384 256C384 272.7 375.3 288.2 361 296.1L73.03 472.1C58.21 482 39.66 482.4 24.52 473.9C9.377 465.4 0 449.4 0 432V80C0 62.64 9.377 46.63 24.52 38.13C39.66 29.64 58.21 29.99 73.03 39.04L361 215z"/></svg></div></div>';
        }

        // $return = str_replace('iframe', 'iframe class="embed-youtube', $return);
    }

    if ($data->type != 'video') {
        return '<div>' . $return . '</div>';

    } else {
        return '<div class="embed-responsive ' . $class . '">' . $return . '</div>';

    }

    //return '<div class="embed-responsive ' . $class . '">' . $return . '</div>';
}


// dynamically populate MENU field menus:
add_filter('acf/load_field/key=field_642473fce6892', 'lvl_acf_load_menu_items_into_field');
function lvl_acf_load_menu_items_into_field($field)
{
    $field['choices'] = [];
    $menus = get_terms('nav_menu');
    if (!empty($menus)) {
        $menus = array_combine(wp_list_pluck($menus, 'term_id'), wp_list_pluck($menus, 'name'));
        foreach ($menus as $menuId => $menu) {
            if ('Main Menu' == $menu) {
                continue;
            }
            $field['choices'][$menuId] = $menu;
        }
    }
    return $field;
}

// TODO: add to theme options
add_filter('excerpt_more', function ($more) {
    return '...';
});

// TODO: add to theme options
add_filter('excerpt_length', function ($length) {
    return 20;
});


// Fixes SVG code in ACF textarea sanitization issue:
add_filter('wp_kses_allowed_html', 'lvl_acf_add_allowed_svg_tag', 10, 2);
function lvl_acf_add_allowed_svg_tag($tags, $context)
{
//    if ($context === 'acf') { // opening up to all contexts for now, specifically to avoid HTML block filtering for non admins
	$tags['svg'] = array(
		'xmlns'               => true,
		'width'               => true,
		'height'              => true,
		'preserveAspectRatio' => true,
		'fill'                => true,
		'viewbox'             => true,
		'role'                => true,
		'aria-hidden'         => true,
		'focusable'           => true,
	);
	$tags['path'] = array(
		'd'    => true,
		'fill' => true,
	);
//    }

	return $tags;
}

add_filter('wp_kses_allowed_html', 'lvl_add_allowed_iframe', 10, 1);
function lvl_add_allowed_iframe($tags)
{
	$tags['iframe'] = array(
		'align'                 => true,
		'allow'                 => true,
		'allowfullscreen'       => true,
		'class'                 => true,
		'frameborder'           => true,
		'height'                => true,
		'id'                    => true,
		'marginheight'          => true,
		'marginwidth'           => true,
		'name'                  => true,
		'scrolling'             => true,
		'src'                   => true,
		'style'                 => true,
		'width'                 => true,
		'allowFullScreen'       => true,
		'mozallowfullscreen'    => true,
		'title'                 => true,
		'webkitAllowFullScreen' => true,
	);

	return $tags;
}

add_filter('post_type_link', 'resources_permalink_structure', 10, 4);
function resources_permalink_structure($post_link, $post, $leavename, $sample)
{
    if (false !== strpos($post_link, '%resource_types%')) {
        $resource_type_term = get_the_terms($post->ID, 'resource_types');
        if (is_array($resource_type_term)) {
            $last_term = array_pop($resource_type_term);
            $post_link = str_replace('%resource_types%', $last_term->slug, $post_link);
        }

    }
    return $post_link;
}


function filter_theme_json_theme(WP_Theme_JSON_Data $theme_json)
{
    $theme_json_array = $theme_json->get_data();
    $palette = $theme_json_array['settings']['color']['palette']['theme'];

    $unique_colors = array_unique(array_column($palette, 'color'));
    $palette = array_filter($palette, function ($color) use (&$unique_colors) {
        if (in_array($color['color'], $unique_colors)) {
            unset($unique_colors[array_search($color['color'], $unique_colors)]);
            return true;
        }
        return false;
    });

    $palette = array_values($palette);
    $new_data = array(
        'version'  => 2,
        'settings' => array(
            'color' => array(
                'palette' => $palette,
            ),
        ),
    );

    return $theme_json->update_with($new_data);
}

add_filter('wp_theme_json_data_theme', 'filter_theme_json_theme');


// DNS PREFETCH FOR JS
add_filter( 'wp_resource_hints', function ( $urls, $relation_type ) {
    switch ( $relation_type ) {
        case 'dns-prefetch':
            $urls = array_merge( $urls, [
                'https://fonts.googleapis.com',
                'https://www.googletagmanager.com',
                'https://www.google-analytics.com',
                'https://connect.facebook.net',
                'https://static.hotjar.com',
                'https://script.hotjar.com',
                'https://js.hsforms.net',
                'https://forms.hsforms.net',
                'https://track.hsforms.net',
                'https://geoip.cookieyes.com',
                'https://api.formhq.net',
                'https://px.ads.linkedin.com/',
            ] );
            break;
    }

    return $urls;
}, 10, 2 );


// RELEVANSSI
add_filter( 'relevanssi_live_search_base_styles', '__return_false' );