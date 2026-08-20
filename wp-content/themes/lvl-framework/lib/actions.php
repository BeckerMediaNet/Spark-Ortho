<?php if (!defined('ABSPATH')) exit;

locate_template('lib/actions/theme-activate.php', true, true);
//locate_template('lib/actions/virtual-page.php', true, true);
locate_template('lib/actions/rest-api.php', true, true); // disable user endpoint
locate_template('lib/actions/editor-sidebar.php', true, true);
locate_template('lib/actions/vendor.php', true, true);
locate_template('lib/actions/menu.php', true, true);
locate_template('lib/actions/search-block.php', true, true);
locate_template('lib/actions/location.php', true, true);
locate_template('lib/actions/unpublisher.php', true, true);
locate_template('lib/actions/disable-comments.php', true, true);

//add_action('admin_init', function () {
if(function_exists('is_plugin_active')) {
	if (is_plugin_active('wpengine-geoip/class-geoip.php')) {
		locate_template('lib/actions/location-redirect.php', true, true);
	}
}
//});

if (is_multisite())
    locate_template('lib/actions/multisite.php', true, true);


// TODO: rewrite these HS related scripts to be be loaded through an Observer to where an HS form is loaded, avoiding initial loading costs
// embed hubspot scripts only once
// add_action('wp_footer', 'lvl_hubspot_check_add_scripts', 999);
function lvl_hubspot_check_add_scripts()
{
    global $is_hubspot_script;
    if (isset($is_hubspot_script) && $is_hubspot_script) {
        ?>
        <!--[if lte IE 8]>
        <script defer charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/v2-legacy.js"></script>
        <![endif]-->
        <script defer charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/v2.js"></script>
        <?php
    }
}

// client specific hubspot tracking
// add_action('wp_footer', 'lvl_hubspot_embed_scripts', 999);
function lvl_hubspot_embed_scripts()
{
    $portal_id = get_field('hubspot_portal_id', 'options');
    if (get_field('hubspot_tracking_script', 'options') && $portal_id) {
        ?>
        <!-- Start of HubSpot Embed Code -->
        <script type="text/javascript" id="hs-script-loader" async defer src="//js.hs-scripts.com/<?php echo esc_attr($portal_id); ?>.js"></script>
        <!-- End of HubSpot Embed Code -->
        <?php
    }
}

// add help text to the menu's Help describing css classes that can alter menu items
//
//add_action('current_screen', 'lvl_add_help');
function lvl_add_help($current_screen)
{
    switch ($current_screen->id) {
        case 'nav-menus':
            $current_screen->add_help_tab(array(
                'id'      => 'lvl-help', // This should be unique for the screen.
                'title'   => 'Theme Options',
                'content' => '<p>Special CSS classes to change functionality of menus items:</p>
                                    <ul>
                                        <li>drowpdown-header -> To display the item as a column heading or subheading without a link.</li>
                                        <li>drowpdown-item-text -> To display as paragraph text without a link.</li>
                                        <li>arrow-link -> To display as a bold blue link with an arrow icon.</li>
                                    </ul>
                               <p>Special CSS classes to structure the dropdowns (should be applied to top-level menu items <strong>only</strong>):</p>
                                    <ul>
                                        <li>cols-4 -> To display that menu item\'s dropdown content in 4 columns.</li>
                                        <li>cols-3 -> To display that menu item\'s dropdown content in 3 columns.</li>
                                        <li>cols-2 -> To display that menu item\'s dropdown content in 2 columns.</li>
                                    </ul>
                                        
                                <p>Add an image to the <em>image</em> field to add an image to the left of that menu item and its children.</p>',
            ));
            break;
    }
}


// ADD A FIELD TO THE FORM WITH THE LABEL "field_summary" AND THE TYPE "hidden"
//add_action('gform_pre_submission', 'pre_submission');
function pre_submission($form)
{
    $form_field_summary_id = 0;
    $form_field_summary = '';

    foreach ($form['fields'] as $field) {
        if ($field->label == 'field_summary') {
            $form_field_summary_id = $field->id;

            continue;
        }

//        if ( $field->type != 'hidden' ) {
        $field_id = $field->id;
        $field_label = $field->label;
        $field_value = rgpost("input_{$field_id}");
        $form_field_summary .= $field_label . ': ' . $field_value . "\n";
//        }

    }

    if ($form_field_summary_id)
        $_POST['input_' . $form_field_summary_id] = $form_field_summary;
}