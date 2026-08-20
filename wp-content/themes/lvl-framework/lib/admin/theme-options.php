<?php

class LVL_Framework_Options {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_theme_options_page']);
        add_action('admin_init', [$this, 'register_theme_options']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_media_library']);
        add_action('admin_head', [$this, 'admin_page_styles']);
    }

    public function add_theme_options_page() {
        add_menu_page(
            'Framework Options',
            'Framework Options',
            'manage_options',
            'lvl_framework',
            [$this, 'sitewide_options_page'],
            'dashicons-admin-generic',
            61
        );
    }

    public function sitewide_options_page() {
        echo '<div class="wrap"><h1>Theme Options</h1>';
        settings_errors();
        echo '<form method="post" action="options.php">';
        settings_fields('lvl_framework');
        do_settings_sections('lvl_framework');
        submit_button();
        echo '</form></div>';
    }

    public function register_theme_options() {
        register_setting('lvl_framework', 'lvl_framework');

        $sections = [
            'welcome' => 'Welcome',
            'branding' => 'Branding',
            'search_quick_links' => 'Search Quick Links',
            'navigation' => 'Navigation',
        ];

        foreach ($sections as $id => $title) {
            add_settings_section("lvl_framework_{$id}", $title, [$this, "sitewide_{$id}_callback"], 'lvl_framework');
        }

        $fields = [
            'welcome_message' => ['Welcome Message', 'welcome'],
            'sitelogo' => ['Logo', 'branding'],
            'sitelogo_transparent' => ['Logo for Transparent Navigation Bar', 'branding'],
            'search_quick_links' => ['Search Quick Links', 'search_quick_links'],
            'behavior' => ['Behavior', 'navigation'],
            'is_dropdown_hover' => ['Dropdown on Hover', 'navigation'],
            'scroll_to_top' => ['Scroll to Top', 'navigation'],
        ];

        foreach ($fields as $id => [$label, $section]) {
            add_settings_field($id, $label, [$this, "sitewide_{$id}_callback"], 'lvl_framework', "lvl_framework_{$section}");
        }
    }

    public function enqueue_media_library() {
        wp_enqueue_media();
    }

    public function admin_page_styles() {
        echo '<style>
            .wrap h1 { font-size: 2em; margin-bottom: 20px; }
            .wrap form { background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; }
            .wrap form textarea, .wrap form input[type="text"], .wrap form input[type="url"], .wrap form select {
                width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 4px; }
            .wrap form input[type="checkbox"] { margin-right: 10px; }
            .wrap form .button { background: #0073aa; border-color: #0073aa; color: #fff; text-decoration: none; text-shadow: none; box-shadow: none; padding: 10px 20px; border-radius: 4px; }
            .wrap form .button:hover { background: #005177; border-color: #005177; }
        </style>';
    }

    public function sitewide_welcome_callback() {
        echo '<p>Welcome to the Theme Options page. Here you can customize the theme settings.</p>';
    }

    public function sitewide_welcome_message_callback() {
        $options = get_option('lvl_framework');
        $welcome_message = $options['welcome_message'] ?? '';
        echo '<textarea name="lvl_framework[welcome_message]" style="width: 100%; height: 100px;">' . $welcome_message . '</textarea>';
    }

    public function sitewide_branding_callback() {
        echo '<p>Customize the branding of the site.</p>';
    }

    public function sitewide_sitelogo_callback() {
        $options = get_option('lvl_framework');
        $sitelogo = $options['sitelogo'] ?? '';
        echo '<input type="hidden" name="lvl_framework[sitelogo]" value="' . $sitelogo . '"/>
        <div id="sitelogo-preview">' . ($sitelogo ? '<img src="' . wp_get_attachment_image_url($sitelogo, 'medium') . '" style="max-width: 100%;"/>' : '') . '</div>
        <button type="button" class="button" id="sitelogo-upload">Upload/Replace Logo</button>
        <button type="button" class="button" id="sitelogo-remove">Remove Logo</button>';
        $this->media_upload_script('sitelogo');
    }

    public function sitewide_sitelogo_transparent_callback() {
        $options = get_option('lvl_framework');
        $sitelogo_transparent = $options['sitelogo_transparent'] ?? '';
        echo '<input type="hidden" name="lvl_framework[sitelogo_transparent]" value="' . $sitelogo_transparent . '"/>
        <div id="sitelogo_transparent-preview">' . ($sitelogo_transparent ? '<img src="' . wp_get_attachment_image_url($sitelogo_transparent, 'medium') . '" style="max-width: 100%;"/>' : '') . '</div>
        <button type="button" class="button" id="sitelogo_transparent-upload">Upload/Replace Logo</button>
        <button type="button" class="button" id="sitelogo_transparent-remove">Remove Logo</button>';
        $this->media_upload_script('sitelogo_transparent');
    }

    public function sitewide_search_quick_links_callback() {
        $options = get_option('lvl_framework');
        $search_quick_links = $options['search_quick_links'] ?? '';
        echo '<textarea name="lvl_framework[search_quick_links]" style="width: 100%; height: 100px;">' . $search_quick_links . '</textarea>';
    }

    public function sitewide_behavior_callback() {
        $options = get_option('lvl_framework');
        $behavior = $options['behavior'] ?? 'default';
        echo '<select name="lvl_framework[behavior]">
            <option value="default" ' . selected($behavior, 'default', false) . '>Default</option>
            <option value="sticky" ' . selected($behavior, 'sticky', false) . '>Sticky</option>
            <option value="peekaboo" ' . selected($behavior, 'peekaboo', false) . '>Peek-a-Boo</option>
        </select>';
    }

    public function sitewide_is_dropdown_hover_callback() {
        $options = get_option('lvl_framework');
        $is_dropdown_hover = $options['is_dropdown_hover'] ?? 0;
        echo '<input type="checkbox" name="lvl_framework[is_dropdown_hover]" value="1" ' . checked($is_dropdown_hover, 1, false) . ' />';
    }

    public function sitewide_scroll_to_top_callback() {
        $options = get_option('lvl_framework');
        $scroll_to_top = $options['scroll_to_top'] ?? 0;
        echo '<input type="checkbox" name="lvl_framework[scroll_to_top]" value="1" ' . checked($scroll_to_top, 1, false) . ' />';
    }



    public function sitewide_dev_domains_callback() {
        $options = get_option('lvl_framework');
        $dev_domains = $options['dev_domains'] ?? '';
        echo '<textarea name="lvl_framework[dev_domains]" style="width: 100%; height: 100px;">' . esc_textarea($dev_domains) . '</textarea>';
    }

    public function sitewide_navigation_callback() {
        echo '<p>Customize the navigation settings of the site.</p>';
    }


    private function media_upload_script($field_id) {
        echo '<script>
            jQuery(document).ready(function ($) {
                var file_frame;
                $("#' . $field_id . '-upload").on("click", function (event) {
                    event.preventDefault();
                    if (file_frame) { file_frame.open(); return; }
                    file_frame = wp.media({ title: "Select or Upload Logo", button: { text: "Use this logo" }, multiple: false });
                    file_frame.on("select", function () {
                        var attachment = file_frame.state().get("selection").first().toJSON();
                        var imageUrl = attachment.sizes.medium ? attachment.sizes.medium.url : attachment.sizes.full.url;
                        $("input[name=\'lvl_framework[' . $field_id . ']\']").val(attachment.id);
                        $("#' . $field_id . '-preview").html("<img src=\'" + imageUrl + "\' style=\'max-width: 100%;\' />");
                    });
                    file_frame.open();
                });
                $("#' . $field_id . '-remove").on("click", function (event) {
                    event.preventDefault();
                    $("input[name=\'lvl_framework[' . $field_id . ']\']").val("");
                    $("#' . $field_id . '-preview").html("");
                });
            });
        </script>';
    }
}

new LVL_Framework_Options();