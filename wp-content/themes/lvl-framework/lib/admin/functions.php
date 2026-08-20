<?php if (!defined('ABSPATH')) {
    exit;
}

// Enqueue styles for the block editor
add_action('enqueue_block_editor_assets', 'lvl_block_editor_assets');
function lvl_block_editor_assets(): void
{
    // TODO: Add as a theme option to enqueue or external css and js files
    // STYLES
    // wp_enqueue_style('lvl-block-editor-assets-styles', LVL_THEME_URI . '/dist/admin/css/editor.min.css?v=' . time(), [], null, 'all');

    // SCRIPTS
    // TODO: ENQUEUE BLOCK SCRIPTS FOR EDITOR!
    wp_enqueue_script('lvl-block-editor-assets-js', LVL_THEME_URI . lvl_cache_bust( '/dist/js/editor.min.js'), ['wp-blocks', 'wp-dom-ready', 'wp-edit-post'], null, true);

    wp_register_script('lvl-swiffy', LVL_THEME_URI . lvl_cache_bust( '/dist/js/swiffy.min.js'), [], null, true);
    wp_register_script('lvl-swiper', LVL_THEME_URI . lvl_cache_bust( '/dist/js/swiper.min.js'), [], null, true);
    wp_register_script('lvl-countup', LVL_THEME_URI . lvl_cache_bust( '/dist/js/countup.min.js'), [], null, true);
}


/**
 * Prompt WordPress user to install required plugins.
 */
add_action('admin_notices', 'lvl_install_plugins_prompt');
function lvl_install_plugins_prompt()
{
    // ACF PRO
    if (!is_plugin_active('advanced-custom-fields-pro/acf.php')) { ?>
        <div class="error notice">
            <p><?php _e('This theme requires the Advanced Custom Fields PRO plugin. <a href="mailto:dev@webmechanix.com" target="_blank" rel="noopener noreferrer">Email WebMechanix to Get It</a>'); ?></p>
        </div>
    <?php }
    // TODO: Add more plugins here if they are critical to theme or blocks ...
}


/**
 * Removes comments from the left hand navigation in the admin.
 */
// TODO: remove?
// add_action( 'admin_menu', 'lvl_remove_menus' );
function lvl_remove_menus()
{
    global $menu;
    global $submenu;
//	remove_menu_page( 'edit.php' );
    remove_menu_page('edit-comments.php');
    echo '';
}

/**
 * Disables the visual editor for specific post types.
 */
// TODO: remove?
//add_filter( 'user_can_richedit', 'lvl_disable_visual_editor' );
function lvl_disable_visual_editor($default)
{
    global $post;
    $disable_on = ['post', 'page']; // Add custom post types to disable editor on here...
    if (in_array(get_post_type($post), $disable_on)) {
        return false;
    } else {
        return $default;
    }
}

/**
 * Adds custom post type counts to the "At a Glance" widget on the WP dashboard.
 */
add_action('dashboard_glance_items', 'lvl_at_glance');
function lvl_at_glance()
{
    $args = [
        'public'   => true,
        '_builtin' => false,
    ];
    $output = 'object';
    $operator = 'and';
    $post_types = get_post_types($args, $output, $operator);
    foreach ($post_types as $post_type) {
        $num_posts = wp_count_posts($post_type->name);
        $num = number_format_i18n($num_posts->publish);
        $text = _n($post_type->labels->singular_name, $post_type->labels->name, intval($num_posts->publish));
        if ($num_posts->publish > 0) {
            if (current_user_can('edit_posts')) {
                $output = '<a href="edit.php?post_type=' . $post_type->name . '">' . $num . ' ' . $text . '</a>';
                echo '<li class="post-count ' . $post_type->name . '-count">' . $output . '</li>';
            } else {
                $output = '<span>' . $num . ' ' . $text . '</span>';
                echo '<li class="post-count ' . $post_type->name . '-count">' . $output . '</li>';
            }
        }
    }
}


/**
 * Adds icons to the At a Glance widget for custom post types.
 */
// TODO: remove?
//add_action( 'admin_head', 'lvl_at_glance_css' );
function lvl_at_glance_css()
{
    echo '<style>
			#dashboard_right_now li.post-type-slug-count a:before{
				content: "";
			}
         </style>';
}

/**
 * Adds custom admin styles to the default WordPress admin screen.
 */
add_action('admin_enqueue_scripts', 'lvl_admin_js');
function lvl_admin_js($hook)
{
	$theme_json = locate_template('/theme.json', false, false);
    $theme_json = file_exists($theme_json) ? json_decode(file_get_contents($theme_json), true) : [];

    wp_enqueue_script('lvl-acf', LVL_THEME_URI . '/dist/admin/js/acf.min.js?v=' . time(), ['acf-input']);
    wp_enqueue_script('lvl-admin', LVL_THEME_URI . '/dist/admin/js/admin.min.js?v=' . time(), ['acf-input']);

    wp_localize_script(
        'lvl-acf',
        'app_localized',
        [
            'themePath' => LVL_THEME_URI,
            'ajax_url'   => admin_url('admin-ajax.php'),
            'check'     => wp_create_nonce('lvl-ajax-nonce'),
            'namespace' => 'lvl',
            'themeJson' => $theme_json,
        ]
    );
}

/**
 * Adds admin styles.
 **/
// TODO: migrate to a file?
add_action('admin_head', 'lvl_admin_css');
function lvl_admin_css()
{
    //
    wp_enqueue_style('lvl-admin-styles', LVL_THEME_URI . '/dist/admin/css/admin.min.css?v=' . time(), [], null, 'all');
}


/**
 * Add a dashboard widget with a tutorial video for the client.
 */
// TODO: remove?
// add_action( 'wp_dashboard_setup', 'lvl_add_dashboard_widgets' );
function lvl_add_dashboard_widgets()
{
    wp_add_dashboard_widget(
        'lvl_tutorial_dash_widget',
        'How To Edit and Maintain Your Site!',
        'lvl_add_tutorial_dash_widget'
    );
}

function lvl_add_tutorial_dash_widget()
{
    echo '<iframe width="630" height="355" src="https://www.useloom.com/embed/4e098d95d1ff4672b38bd4b0f86b9f93"  style="border: 0; max-width: 100%; height: auto;" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
}