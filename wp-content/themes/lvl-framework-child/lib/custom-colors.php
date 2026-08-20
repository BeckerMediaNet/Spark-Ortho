<?php
require_once __DIR__ . '/../vendor/autoload.php';

use ScssPhp\ScssPhp\Compiler;


//button-colors($text-color, $bg-color) {
//  $hover-border-color: lighten(#{$bg-color}, 10%);
//  $focus-shadow-rgb: red(#{$hover-border-color}), green(#{$hover-border-color}), blue(#{$hover-border-color});
//  --bs-btn-color: #{$text-color};
//  --bs-btn-bg: #{$bg-color};
//  --bs-btn-border-color: #{$bg-color};
//  --bs-btn-hover-color: #{$bg-color};
//  --bs-btn-hover-bg: #{$text-color};
//  --bs-btn-hover-border-color: #{$text-color};
//  --bs-btn-focus-shadow-rgb: #{$focus-shadow-rgb};
//  --bs-btn-active-color: #{$bg-color};
//  --bs-btn-active-bg: #{$text-color};
//  --bs-btn-active-border-color: #{$text-color};
//  --bs-btn-active-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
//  --bs-btn-disabled-color: #{$text-color};
//  --bs-btn-disabled-bg: #{$bg-color};
//  --bs-btn-disabled-border-color: #{$bg-color};
//}
function lighten($color, $amount)
{
    $color = str_replace('#', '', $color);
    if (strlen($color) == 6) {
        list($r, $g, $b) = str_split($color, 2);
    } elseif (strlen($color) == 3) {
        list($r, $g, $b) = str_split($color, 1);
        $r .= $r;
        $g .= $g;
        $b .= $b;
    } else {
        return false;
    }
    $r = dechex(max(0, min(255, hexdec($r) + 255 * $amount)));
    $g = dechex(max(0, min(255, hexdec($g) + 255 * $amount)));
    $b = dechex(max(0, min(255, hexdec($b) + 255 * $amount)));
    return "#$r$g$b";
}

function rgb($color)
{
    $color = str_replace('#', '', $color);
    if (strlen($color) == 6) {
        list($r, $g, $b) = str_split($color, 2);
    } elseif (strlen($color) == 3) {
        list($r, $g, $b) = str_split($color, 1);
        $r .= $r;
        $g .= $g;
        $b .= $b;
    } else {
        return false;
    }
    return "$r, $g, $b";
}

function set_btn_colors($text_color, $bg_color, $text_color_hover, $bg_color_hover)
{
    $border_color = ($bg_color == 'transparent' ? $text_color : $bg_color);
    $hover_border_color = lighten($border_color, .1);
    $focus_shadow_rgb = rgb($hover_border_color);


    return "--bs-btn-color: $text_color;
    --bs-btn-bg: $bg_color;
    --bs-btn-border-color: $bg_color;
    --bs-btn-hover-color: $text_color_hover;
    --bs-btn-hover-bg: $bg_color_hover;
    --bs-btn-hover-border-color: $text_color_hover;
    --bs-btn-focus-shadow-rgb: $focus_shadow_rgb;
    --bs-btn-active-color: $bg_color_hover;
    --bs-btn-active-bg: $text_color_hover;
    --bs-btn-active-border-color: $text_color_hover;
    --bs-btn-active-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
    --bs-btn-disabled-color: $text_color;
    --bs-btn-disabled-bg: $bg_color;
    --bs-btn-disabled-border-color: $bg_color;
    --bs-btn-border-width: 1px;
    --bs-btn-border-color: $border_color;";
}


function compile_custom_scss_and_create_variation()
{
    // Get color options from WordPress
    $primary_color = get_option('theme_primary_color', '#ffffff');
    $primary_color_bg = get_option('theme_primary_color_bg', '#0d6efd');

    $btn_color_labels = [
        'primary'   => 'Primary',
        'secondary' => 'Secondary',
        'success'   => 'Success',
        'info'      => 'Info',
        'warning'   => 'Warning',
        'danger'    => 'Danger',
    ];

    $colors = [];

    foreach ($btn_color_labels as $label => $name) {
        $color = get_option("theme_{$label}_color", '#ffffff');
        $bg = get_option("theme_{$label}_color_bg", '#0d6efd');
        $color_hover = get_option("theme_{$label}_color_hover", '#ffffff');
        $bg_hover = get_option("theme_{$label}_color_bg_hover", '#0d6efd');
        $colors[$label] = [
            "color"       => $color,
            "bg"          => $bg,
            "color_hover" => $color_hover,
            "bg_hover"    => $bg_hover,
        ];
    }

    $scss_content = '';

   foreach ($colors as $label => $color) {
    $scss_content .= ".btn.btn-$label {
        " . set_btn_colors($color['color'], $color['bg'], $color['color_hover'], $color['bg_hover']) . "
    }
    .btn.btn-outline-$label {
        " . set_btn_colors($color['color'], 'transparent', $color['color_hover'], $color['bg_hover']) . "
    }";
}

    $scss_content_admin = '.appearance_page_theme-color-options{
        ' . $scss_content . '
    }';


//    // Set up SCSS compiler
    $compiler = new Compiler();

//    $compiler->setImportPaths(get_stylesheet_directory() . '/assets/css');
//    $compiler->setImportPaths('../dist/css');

    try {
        // Compile SCSS to CSS
        $css = $compiler->compileString($scss_content)->getCss();
        $css_admin = $compiler->compileString($scss_content_admin)->getCss();

        // Save compiled CSS
        $css_file_path = get_stylesheet_directory() . '/assets/css/custom-style.css';
        file_put_contents($css_file_path, $css);

        $css_file_path_admin = get_stylesheet_directory() . '/assets/css/custom-style-admin.css';
        file_put_contents($css_file_path_admin, $css_admin);

        // Create style variation JSON
//        $variation = [
//            'version'  => 2,
//            'title'    => 'Custom Style',
//            'settings' => [
//                'color' => [
//                    'palette' => [
//                        [
//                            'slug'  => 'primary',
//                            'color' => $primary_color,
//                            'name'  => 'Primary',
//                        ],
//                    ],
//                ],
//            ],
//            'styles'   => [
//                'css' => file_get_contents($css_file_path),
//            ],
//        ];
//
//        // Save style variation JSON
//        $json_file_path = get_stylesheet_directory() . '/styles/custom.json';
//        file_put_contents($json_file_path, json_encode($variation, JSON_PRETTY_PRINT));

        return true;
    } catch (Exception $e) {
        echo '...';
        var_dumped($e->getMessage());
        error_log('SCSS compilation failed: ' . $e->getMessage());
        return false;
    }
}

// Hook to save options
function save_theme_options()
{
    $btn_color_labels = [
        'primary'   => 'Primary',
        'secondary' => 'Secondary',
        'success'   => 'Success',
        'info'      => 'Info',
        'warning'   => 'Warning',
        'danger'    => 'Danger',
    ];

    // Save color options
    foreach ($btn_color_labels as $label => $name) {
        if (isset($_POST["theme_{$label}_color"])) {
            update_option("theme_{$label}_color", sanitize_hex_color($_POST["theme_{$label}_color"]));
        }
        if (isset($_POST["theme_{$label}_color_bg"])) {
            update_option("theme_{$label}_color_bg", sanitize_hex_color($_POST["theme_{$label}_color_bg"]));
        }
        if (isset($_POST["theme_{$label}_color_hover"])) {
            update_option("theme_{$label}_color_hover", sanitize_hex_color($_POST["theme_{$label}_color_hover"]));
        }
        if (isset($_POST["theme_{$label}_color_bg_hover"])) {
            update_option("theme_{$label}_color_bg_hover", sanitize_hex_color($_POST["theme_{$label}_color_bg_hover"]));
        }
    }


    // Compile SCSS and create variation
    if (compile_custom_scss_and_create_variation()) {
        add_settings_error('theme_options', 'theme_options_updated', __('Theme options updated and styles compiled successfully.', 'your-text-domain'), 'updated');
    } else {
        add_settings_error('theme_options', 'theme_compile_error', __('Error compiling styles. Check error log for details.', 'your-text-domain'), 'error');
    }

    // Redirect back to the options page
    wp_redirect(admin_url('themes.php?page=theme-color-options'));

    exit;
}

add_action('admin_post_save_theme_options', 'save_theme_options');

// Add this to your theme options page
function theme_options_page()
{
    $btn_color_labels = [
        'primary'   => 'Primary',
        'secondary' => 'Secondary',
        'success'   => 'Success',
        'info'      => 'Info',
        'warning'   => 'Warning',
        'danger'    => 'Danger',
    ];

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?= esc_html(get_admin_page_title()); ?></h1>
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="save_theme_options">
            <?php
            // Output security fields
            wp_nonce_field('theme_options_action', 'theme_options_nonce');
            // Get the value of the setting we've registered with register_setting()
            ?>
            <style>
                .color-settings-picker {
                    text-align: center;
                }

                .color-settings-picker tbody tr:nth-child(odd) {
                    background-color: #f9f9f9;
                }

                .color-settings-picker th {
                    padding: 1em;
                }

                .color-settings-picker td {
                    padding: 1em;
                }

                .color-settings-picker tr:first-child th:nth-child(3),
                .color-settings-picker tr:nth-child(2) th:nth-child(4),
                .color-settings-picker tr:nth-child(2) th:nth-child(5),
                .color-settings-picker tr td:nth-child(4),
                .color-settings-picker tr td:nth-child(5) {
                    background: #ccc;
                }
            </style>
            <table class="color-settings-picker table table-borderless table-striped">
                <thead>
                <tr>
                    <th></th>
                    <th colspan="2"></th>
                    <th colspan="2">HOVER</th>
                    <th></th>
                </tr>
                <tr>
                    <th>Button</th>
                    <th>Text</th>
                    <th>Background</th>
                    <th>Text</th>
                    <th>Background</th>
                    <th>Example</th>
                    <th>Example Outline</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($btn_color_labels as $label => $name) {
                    $color = get_option("theme_{$label}_color", '#ffffff');
                    $bg = get_option("theme_{$label}_color_bg", '#0d6efd');
                    $color_hover = get_option("theme_{$label}_color_hover", '#ffffff');
                    $bg_hover = get_option("theme_{$label}_color_bg_hover", '#0d6efd');
                    ?>
                    <tr>
                        <td><?= $name ?></td>
                        <td><input type="color" name="theme_<?= $label ?>_color" value="<?= $color ?>" class="color-field"></td>
                        <td><input type="color" name="theme_<?= $label ?>_color_bg" value="<?= $bg ?>" class="color-field"></td>
                        <td><input type="color" name="theme_<?= $label ?>_color_hover" value="<?= $color_hover ?>" class="color-field"></td>
                        <td><input type="color" name="theme_<?= $label ?>_color_bg_hover" value="<?= $bg_hover ?>" class="color-field"></td>
                        <td>
                            <button class="btn btn-<?= $label ?>">Button</button>
                        </td>
                        <td>
                            <button class="btn btn-outline-<?= $label ?>">Button</button>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <?php submit_button('Save Changes'); ?>
        </form>
    </div>
    <?php
}

// Add menu item
function add_theme_options_page()
{
    add_theme_page('Theme Color Options', 'Theme Colors', 'manage_options', 'theme-color-options', 'theme_options_page');
}

add_action('admin_menu', 'add_theme_options_page');