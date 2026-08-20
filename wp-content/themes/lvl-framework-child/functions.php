<?php if (!defined('ABSPATH')) {
    exit;
}
/**
 * Main functions file.
 * This file contains all functions to add theme support and remove default WordPress functionality.
 */

if (!defined('LVL_THEME_URI_CHILD'))
    define("LVL_THEME_URI_CHILD", get_stylesheet_directory_uri());


add_action('after_setup_theme', function () {
    // add_image_size( 'medium-large', 512, 512 );
});

// Google Search Console site verification (needed to submit the sitemap)
add_action('wp_head', function () {
    echo '<meta name="google-site-verification" content="3TMDZSAmidf-GLZfVu8BJmKgqPYeu-PpFy8GoRr2PU0" />' . "\n";
}, 1);

// Bing Webmaster Tools site verification
add_action('wp_head', function () {
    echo '<meta name="msvalidate.01" content="235A563E1FE025708A52BA6429B96FD0" />' . "\n";
}, 1);

// Defer the WMX Modals plugin's JS — it's a modal popup, not needed for first paint,
// but ships as a synchronous <head> script that was blocking render for ~900ms.
add_filter('script_loader_tag', function ($tag, $handle) {
    if ($handle === 'wmx-modals') {
        return str_replace(' src', ' defer src', $tag);
    }
    return $tag;
}, 10, 2);

// Drop the parent theme's AOS (Animate On Scroll) stylesheet — the code that would ever add
// a data-aos attribute to a block is commented out (lvl-framework/lib/classes/Block.php),
// and no template outputs one, so this ~170ms render-blocking request styles nothing.
add_action('wp_enqueue_scripts', function () {
    wp_dequeue_style('lvl-frontend');
}, 100);

add_action('wp_enqueue_scripts', 'lvl_child_load_scripts', 10);
function lvl_child_load_scripts(): void
{
    wp_register_script('lvl-bootstrap', LVL_THEME_URI_CHILD . lvl_cache_bust('/dist/js/bootstrap.min.js'), [], null, true);
    wp_enqueue_script('lvl-child-scripts', LVL_THEME_URI_CHILD . lvl_cache_bust('/dist/js/app.min.js'), ['lvl-main-scripts'], null, true);
}

add_action('wp_enqueue_scripts', 'lvl_child_load_styles', 99);
function lvl_child_load_styles(): void
{
    wp_register_style('lvl-bootstrap', LVL_THEME_URI_CHILD . lvl_cache_bust('/dist/css/bootstrap.min.css'), [], null, 'all');
    wp_enqueue_style('lvl-child-styles', LVL_THEME_URI_CHILD . lvl_cache_bust('/dist/css/app.min.css'), ['lvl-base-styles', 'lvl-bootstrap'], null, 'all');
    wp_enqueue_style('lvl-brand-color-overrides', LVL_THEME_URI_CHILD . lvl_cache_bust('/dist/css/brand-color-overrides.css'), ['lvl-child-styles'], null, 'all');
}

// Preconnect to font providers to reduce TBT/latency
add_filter('wp_resource_hints', 'lvl_child_font_preconnect', 10, 2);
function lvl_child_font_preconnect(array $urls, string $relation_type): array
{
    if ('preconnect' === $relation_type) {
        $urls[] = ['href' => 'https://fonts.googleapis.com'];
        $urls[] = ['href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous'];
    }
    return $urls;
}

// LiveHelpNow chat widget
add_action('wp_head', 'lvl_child_livehelpnow_widget');
function lvl_child_livehelpnow_widget(): void
{
?>
<script type="text/javascript">
window.lhnJsSdkInit = function () {
  lhnJsSdk.setup = {
    application_id: "25cb2181-be71-4c5a-81d9-bdc274e3befd",
    application_secret: "<?php echo esc_js(defined('LVL_LHN_APP_SECRET') ? LVL_LHN_APP_SECRET : ''); ?>"
  };
  lhnJsSdk.controls = [{
    type: "hoc",
    id: "3504867f-8ec2-4e78-b999-afc41b57c2f9"
  }];
};

(function (d, s) {
  var newjs, lhnjs = d.getElementsByTagName(s)[0];
  newjs = d.createElement(s);
  newjs.src = "https://developer.livehelpnow.net/js/sdk/lhn-jssdk-current.min.js";
  lhnjs.parentNode.insertBefore(newjs, lhnjs);
}(document, "script"));
</script>
<?php
}

// Single combined Google Fonts URL — both families in one HTTP request, display=swap
// prevents render-blocking; preconnect hints above reduce DNS/TCP overhead.
define('LVL_GOOGLE_FONTS_URL', 'https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap');

// Enqueue fonts to front-end and editor
add_action('wp_enqueue_scripts', 'lvl_child_load_fonts', 99);
add_action('enqueue_block_editor_assets', 'lvl_child_load_fonts');
function lvl_child_load_fonts(): void
{
    wp_enqueue_style('lvl-google-fonts', LVL_GOOGLE_FONTS_URL, [], null, 'all');
}

/**
 * Adds custom login styles to the default WordPress login screen.
 * @return void
 */
add_action('login_enqueue_scripts', 'lvl_child_login_styles', 11);
function lvl_child_login_styles()
{

    wp_enqueue_global_styles();
    wp_register_style('lvl-child-login-stylesheet', LVL_THEME_URI_CHILD . lvl_cache_bust('/dist/admin/css/login.min.css'), false);
}


// Enqueue styles for the block editor
add_action('enqueue_block_editor_assets', 'lvl_child_block_editor_assets');
function lvl_child_block_editor_assets(): void
{
    wp_enqueue_style('lvl-child-block-editor-bs-styles', LVL_THEME_URI_CHILD . '/dist/admin/css/editor-bs.min.css?v=' . time(), [], null, 'all');
    wp_enqueue_style('lvl-child-block-editor-theme-styles', LVL_THEME_URI_CHILD . '/dist/admin/css/editor-theme.min.css?v=' . time(), ['lvl-child-block-editor-bs-styles'], null, 'all');

    // SCRIPTS
    wp_enqueue_script('lvl-child-block-editor-assets-js', LVL_THEME_URI_CHILD . lvl_cache_bust('/dist/js/editor.min.js'), ['lvl-block-editor-assets-js'], null, true);
}

// Enqueue styles for the Admin Settings page
add_action('admin_enqueue_scripts', 'lvl_child_admin_styles');
function lvl_child_admin_styles(): void
{
    wp_enqueue_style('lvl-child-admin-styles', LVL_THEME_URI_CHILD . lvl_cache_bust('/dist/admin/css/admin.min.css'), [], null, 'all');
    // wp_enqueue_style('lvl-child-admin-custom-colors-styles', LVL_THEME_URI_CHILD . lvl_cache_bust('/assets/css/custom-style-admin.css'), [], null, 'all');

}

add_action('admin_enqueue_scripts', 'lvl_child_load_fonts_site_editor');
function lvl_child_load_fonts_site_editor()
{
    if (!is_admin() || !function_exists('get_current_screen')) {
        return;
    }

    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'site-editor') {
        return;
    }

    add_action('admin_footer', function () {
?>
        <script>
            (function() {
                var loadFonts = function() {
                    var iframe = document.querySelector('iframe[name="editor-canvas"]');
                    if (iframe && iframe.contentDocument) {
                        var head = iframe.contentDocument.head;
                        var fonts = [
                            '<?php echo esc_js(LVL_GOOGLE_FONTS_URL); ?>'
                        ];
                        fonts.forEach(function(href) {
                            var link = iframe.contentDocument.createElement('link');
                            link.href = href;
                            link.rel = 'stylesheet';
                            head.appendChild(link);
                        });
                    }
                };

                var attemptLoadFonts = function() {
                    loadFonts();
                    // Keep trying every 1000ms until successful or max attempts reached
                    var attempts = 0;
                    var intervalId = setInterval(function() {
                        attempts++;
                        if (loadFonts() || attempts > 10) {
                            clearInterval(intervalId);
                        }
                    }, 1000);
                };

                if (document.readyState === 'complete') {
                    attemptLoadFonts();
                } else {
                    window.addEventListener('load', attemptLoadFonts);
                }

                // Also attempt to load fonts when iframe content changes
                window.addEventListener('message', function(event) {
                    if (event.data && event.data.type === 'patternRendered') {
                        attemptLoadFonts();
                    }
                });
            })();
        </script>
    <?php
    });
}

// Enqueue block styles in the header
add_action('wp_enqueue_scripts', 'enqueue_block_styles_in_header', 99);
function enqueue_block_styles_in_header()
{
    global $post;

    if (is_singular() && has_blocks($post->post_content)) {
        $blocks = parse_blocks($post->post_content);
        $block_styles = array();

        function get_block_styles($blocks, &$block_styles)
        {
            foreach ($blocks as $block) {
                if (! empty($block['blockName'])) {
                    $block_type = WP_Block_Type_Registry::get_instance()->get_registered($block['blockName']);
                    if ($block_type && ! empty($block_type->style)) {
                        if (is_array($block_type->style)) {
                            $block_styles = array_merge($block_styles, $block_type->style);
                        } else {
                            $block_styles[] = $block_type->style;
                        }
                    }
                }

                // Recursively process inner blocks
                if (! empty($block['innerBlocks'])) {
                    get_block_styles($block['innerBlocks'], $block_styles);
                }
            }
        }

        get_block_styles($blocks, $block_styles);

        $block_styles = array_unique($block_styles);

        foreach ($block_styles as $style_handle) {
            wp_enqueue_style($style_handle);
        }
    }
}

add_action('widgets_init', function () {

    register_sidebar([
        'name'          => __('Footer'),
        'id'            => 'lvl-footer',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'description'   => __('Use this to build the footer'),
        'before_title'  => '<h5>',
        'after_title'   => '</h5>'
    ]);

    register_sidebar([
        'name'          => __('404 Content'),
        'id'            => 'lvl-404',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'description'   => __('Use this to build the 404 page'),
        'before_title'  => '<h5>',
        'after_title'   => '</h5>'
    ]);
});

// dynamically load option for the acf field "plan_color" with values from the theme colors where the value is the hex code and the label is the name of the color. The colors should come from the theme's color palette defined in the theme.json file.
add_filter('acf/load_field/name=plan_color', 'lvl_load_plan_color_field');
function lvl_load_plan_color_field($field)
{

    // get the color palette from the theme.json file
    $theme = json_decode(file_get_contents(get_stylesheet_directory() . '/theme.json'), true);
    $colors = $theme['settings']['color']['palette'];

    $field['choices'] = [];

    foreach ($colors as $color) {
        $field['choices'][$color['color']] = '<span class="color-picker has-' . $color['slug'] . '-background-color">' . $color['name'] . '</span>';
    }

    return $field;
}

add_filter('manage_wp_block_posts_columns', 'add_synced_column');
function add_synced_column($columns)
{

    $new_columns = array();
    $count = 0;
    $total = count($columns);

    foreach ($columns as $key => $value) {
        $count++;
        if ($count === $total - 1) {
            $new_columns['sync_status'] = 'Synced?';
        }
        $new_columns[$key] = $value;
    }

    return $new_columns;
}

add_action('manage_wp_block_posts_custom_column', 'populate_synced_column', 10, 2);
function populate_synced_column($column, $post_id)
{
    if ($column == 'sync_status') {
        $status = (get_post_meta($post_id, 'wp_pattern_sync_status', true) == 'unsynced') ? 'No' : 'Yes';
        echo $status;
    }
}

add_filter('block_type_metadata_settings', 'disable_button_block_supports', 10, 2);
function disable_button_block_supports($settings, $metadata)
{
    if ($metadata['name'] === 'core/button') {
        unset($settings['supports']['spacing']);
        unset($settings['supports']['typography']);
        unset($settings['supports']['color']);
        unset($settings['supports']['border']);
        unset($settings['supports']['__experimentalBorder']);
        unset($settings['supports']['shadow']);
    }

    return $settings;
}

function get_current_post()
{
    global $post;
    return $post;
}

// Filter out empty paragraphs from imported content
add_filter('the_content', 'remove_empty_paragraphs');
function remove_empty_paragraphs($content)
{

    // Remove <p> tags that only contain &nbsp; or whitespace
    $content = preg_replace('/<p>\s*(&nbsp;|\s)+\s*<\/p>/i', '', $content);

    return $content;
}

function strToTitleCase($string)
{
    $word_splitters = array(' ', '-', "O'", "L'", "D'", 'St.', 'Mc');
    $lowercase_exceptions = array('and', 'to', 'of', 'by', 'for', 'the', 'in', 'on', 'a', 'an');
    $uppercase_exceptions = array('III', 'IV', 'VI', 'VII', 'VIII', 'IX');

    $string = strtolower($string);
    foreach ($word_splitters as $delimiter) {
        $words = explode($delimiter, $string);
        $newwords = array();
        foreach ($words as $word) {
            if (in_array(strtoupper($word), $uppercase_exceptions)) {
                $word = strtoupper($word);
            } elseif (!in_array($word, $lowercase_exceptions)) {
                $word = ucfirst($word);
            }

            $newwords[] = $word;
        }

        if (in_array(strtolower($delimiter), $lowercase_exceptions)) {
            $delimiter = strtolower($delimiter);
        }

        $string = join($delimiter, $newwords);
    }

    return $string;
}


/**
 * ACF Options Page for Locations Archive
 */
if (function_exists('acf_add_options_page')) {
    acf_add_options_sub_page([
        'page_title'  => 'Locations Archive Settings',
        'menu_title'  => 'Archive Settings',
        'parent_slug' => 'edit.php?post_type=location',
        'capability'  => 'edit_posts',
    ]);
}

/**
 * ACF Options Page for Team (Doctors) Archive
 */
if (function_exists('acf_add_options_page')) {
    acf_add_options_sub_page([
        'page_title'  => 'Team Archive Settings',
        'menu_title'  => 'Archive Settings',
        'menu_slug'   => 'acf-options-team-archive-settings',
        'parent_slug' => 'edit.php?post_type=team',
        'capability'  => 'edit_posts',
    ]);
}

/**
 * Add CPTs and Taxonomies
 * To override the default post types and taxonomies, create a new file in the lib directory and include it here.
 * */
locate_template('lib/post-types-child.php', true, true);
locate_template('lib/taxonomies-child.php', true, true);
locate_template('lib/client-helpers.php', true, true);
locate_template('lib/block-enhancements.php', true, true);
locate_template('lib/locations.php', true, true);
locate_template('lib/location-filters.php', true, true);

// Include block-specific functions
if ( file_exists( get_stylesheet_directory() . '/blocks/dentist-cards/functions.php' ) ) {
    require_once get_stylesheet_directory() . '/blocks/dentist-cards/functions.php';
}


add_action('wp_enqueue_scripts', function () {
    if (is_post_type_archive('location')) {
        // Enhanced location proximity search JavaScript
        wp_enqueue_script('location-proximity-search', get_stylesheet_directory_uri() . '/src/js/location-proximity-search.js', ['jquery'], '1.0.1', true);
        wp_localize_script('location-proximity-search', 'locationsAjax', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('locations_search_nonce')
        ]);

        // Localization for legacy Load More system (app.js compatibility)
        wp_localize_script('lvl-child-scripts', 'locations_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('locations_search_nonce')
        ]);

        // Note: Proximity search styles are now included in the main app.min.css via _archives.scss
    }
});
