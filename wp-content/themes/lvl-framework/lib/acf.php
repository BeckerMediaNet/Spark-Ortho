<?php
// if ACF not active, return
if (! class_exists('acf')) {
	return;
}

add_filter('acf/blocks/wrap_frontend_innerblocks', 'acf_should_wrap_innerblocks', 10, 2);
function acf_should_wrap_innerblocks($wrap, $name): bool
{
	return false;
}

add_filter('acf/blocks/no_fields_assigned_message', 'lvl_no_fields_assigned_message', 99, 1);
function lvl_no_fields_assigned_message($message): string
{
	return '';
}

// Name ACF JSON files after the title of the field group
add_filter('acf/json/save_file_name', 'lvl_acf_json_filename', 10, 3); // TODO: ACF features testing
function lvl_acf_json_filename($filename, $post, $load_path): string
{
	//    var_dumped($post);

	if (($post['location'][0][0]['param'] ?? false) === 'block') {
		$block_name = explode('/', $post['location'][0][0]['value'])[1] ?? '_default';

		//        $paths = [get_stylesheet_directory() . '/blocks/' . $dir . '/acf-json'];

		return $block_name . '.json';
	}

	//    $filename = _wp_specialchars($post['title']);
	//    $filename = str_replace(
	//        array(
	//            ' ',
	//            '_',
	//        ),
	//        array(
	//            '-',
	//            '-',
	//        ),
	//        $filename
	//    );
	//
	//    return strtolower($filename) . '.json';

	//    var_dumped($filename);

	return $filename;
}

add_filter('acf/json/save_paths', 'lvl_acf_json_save_paths', 10, 2); // TODO: ACF features testing
function lvl_acf_json_save_paths($paths, $post): array
{
	//    var_dumped($paths);

	if (($post['location'][0][0]['param'] ?? false) === 'block') {
		$dir   = explode('/', $post['location'][0][0]['value'])[1] ?? '_default';
		$paths = [get_stylesheet_directory() . '/blocks/' . $dir . '/acf-json'];
	}

	if (! file_exists($paths[0])) {
		mkdir($paths[0], 0755, true);
	}

	return $paths;
}

add_filter('acf/settings/load_json', 'lvl_acf_json_load_point'); // TODO: ACF features testing
function lvl_acf_json_load_point($paths)
{
	// Remove the original path (optional).
	// unset($paths[0]);

	if (is_child_theme()) {
		$paths[0] = get_template_directory() . '/acf-json'; // load parent theme acf-json
		$paths[1] = get_stylesheet_directory() . '/acf-json'; // load child theme acf-json, overrides parent
	}

	$block_dirs = get_block_dirs();
	foreach ($block_dirs as $block_dir) {
		$block_acf_json = locate_template('/blocks/' . $block_dir . '/acf-json');
		if ($block_acf_json) {
			$paths[] = $block_acf_json;
		}
	};

	// Append the new path and return it.
	//    $paths[] = get_stylesheet_directory() . '/my-custom-folder';

	return $paths;
}

add_filter(
	'acf/pre_save_block',
	function ($attributes) {
		if (empty($attributes['data']['id'])) {
			$attributes['data']['id'] = uniqid();
		}

		return $attributes;
	}
);


add_action('acf/init', function () {
	if (function_exists('acf_add_options_page')) {
		acf_add_options_page([
			'page_title' => 'Theme Options',
			'menu_title' => 'Theme Options',
			'menu_slug'  => 'lvl_sitewide',
			'icon_url'   => 'dashicons-excerpt-view',
			'position'   => 4.65,
		]);
	}

	if (function_exists('acf_add_local_field_group')) :

		$post_types = get_post_types([
			'public' => true,
		], 'objects');
		$post_types = array_filter($post_types, function ($post_type) {
			return ! in_array($post_type->name, ['attachment', 'revision', 'nav_menu_item']);
		});

		$post_type_links = [];
		foreach ($post_types as $post_type) {
			$post_type_links[$post_type->name] = get_admin_url(null, 'edit.php?post_type=' . $post_type->name);
		}

		$buttons = [];
		foreach ($post_types as $post_type) {
			$buttons[] = [
				'key'               => 'button_' . $post_type->name,
				'label'             => $post_type->label,
				'name'              => 'button_' . $post_type->name,
				'type'              => 'button',
				'button_text'       => '' . $post_type->label,
				'button_link'       => $post_type_links[$post_type->name],
				'button_link_title' => 'Edit ' . $post_type->label,
				'wrapper'           => [
					'width' => '33%',
				],
			];
		}

		$actions = '<ul>';
		foreach ($buttons as $button) {
			$actions .= '<li>';
			$actions .= '<a href="' . $button['button_link'] . '" class="button button-primary" title="' . $button['button_link_title'] . '">' . $button['button_text'] . '</a>';
			$actions .= '</li>';
		}
		$actions .= '</ul>';


		acf_add_local_field_group(array(
			'key'                   => 'theme_options_key',
			'title'                 => 'Theme Options',
			'fields'                => array(
				// TAB - Welcome
				array(
					'key'                => 'welcome_tab_key',
					'label'              => 'Welcome',
					'name'               => '',
					'aria-label'         => '',
					'type'               => 'tab',
					'instructions'       => '',
					'required'           => 0,
					'conditional_logic'  => 0,
					'wrapper'            => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'relevanssi_exclude' => 0,
					'placement'          => 'left',
					'endpoint'           => 0,
				),
				array(
					'key'     => 'welcome_message_key',
					'label'   => '',
					'name'    => 'welcome_message',
					'type'    => 'message',
					'message' => '<h1>Welcome to your custom WordPress theme built by <a href="https://www.webmechanix.com/" target="_blank">WebMechanix</a></h1>
									<p>Here you can manage your theme settings, branding, tracking, SEO, icons, and more.</p>
									<p>Click on the tabs to the left to get started.</p>
									<p>Need help? <a href="https://www.webmechanix.com/contact" target="_blank">Contact us</a>.</p>
									<p>Need more familiarity with WordPress Block Editor? <a href="https://wordpress.org/documentation/article/wordpress-block-editor/" target="_blank">Learn more</a>.</p>
									<p>Need more services, such as Digital Marketing, Creative, SEO, Data, and more? <a href="https://www.webmechanix.com/capabilities/" target="_blank">Learn about WebMechanix\'s Other Capabilities</a> </p>',
				),
				// TAB - Branding
				array(
					'key'                => 'branding_tab_key',
					'label'              => 'Branding',
					'name'               => '',
					'aria-label'         => '',
					'type'               => 'tab',
					'instructions'       => '',
					'required'           => 0,
					'conditional_logic'  => 0,
					'wrapper'            => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'relevanssi_exclude' => 0,
					'placement'          => 'left',
					'endpoint'           => 0,
				),
				array(
					'key'               => 'sitelogo_key',
					'label'             => 'Logo',
					'name'              => 'sitelogo',
					'type'              => 'image',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'default_value'     => false,
					'return_format'     => 'id',
					'preview_size'      => 'medium',
					'mime_types'        => '',
				),
				array(
					'key'               => 'sitelogo_transparent_key',
					'label'             => 'Logo for Transparent Navigation Bar',
					'name'              => 'sitelogo_transparent',
					'type'              => 'image',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'default_value'     => false,
					'return_format'     => 'id',
					'preview_size'      => 'medium',
					'mime_types'        => '',
				),
				// TAB - Search Quick Links
				array(
					'key'                => 'search_quick_links_tab_key',
					'label'              => 'Search Quick Links',
					'name'               => '',
					'aria-label'         => '',
					'type'               => 'tab',
					'instructions'       => '',
					'required'           => 0,
					'conditional_logic'  => 0,
					'wrapper'            => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'relevanssi_exclude' => 0,
					'placement'          => 'left',
					'endpoint'           => 0,
				),
				array(
					'key'               => 'search_quick_links_key',
					'label'             => 'Search Quick Links',
					'name'              => 'search_quick_links',
					'type'              => 'repeater',
					'instructions'      => 'Add quick links to the search results page.',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'collapsed'         => 'search_quick_links_title_key',
					'min'               => 0,
					'max'               => 0,
					'layout'            => 'table',
					'button_label'      => '+ Add Link',
					'sub_fields'        => array(
						array(
							'key'               => 'search_quick_links_link_key',
							'label'             => 'Link',
							'name'              => 'link',
							'type'              => 'link',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'return_format'     => 'array',
						),
					),
				),
				// TAB - Navigation
				array(
					'key'               => 'field_64dcd965e1413',
					'label'             => 'Navigation',
					'name'              => '',
					'aria-label'        => '',
					'type'              => 'tab',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'placement'         => 'left',
					'endpoint'          => 0,
				),
				array(
					'key'               => 'field_64dcd93ae1412',
					'label'             => 'Behavior',
					'name'              => 'behavior',
					'aria-label'        => '',
					'type'              => 'button_group',
					'instructions'      => 'Default = navigation remaining at top of page.<br/>Sticky = navigation sticks to the top of the page when scrolling.<br/>Peek-a-Boo = navigation hides when scrolling down and reappears when scrolling up.',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'choices'           => array(
						'default'  => 'Default',
						'sticky'   => 'Sticky',
						'peekaboo' => 'Peek-a-Boo',
					),
					'default_value'     => 'default',
					'return_format'     => 'value',
					'allow_null'        => 0,
					'layout'            => 'horizontal',
				),
				array(
					'key'               => 'field_64dcd9eae1414_dd',
					'label'             => 'Dropdown on Hover',
					'name'              => 'is_dropdown_hover',
					'aria-label'        => '',
					'type'              => 'true_false',
					'instructions'      => 'Checked = dropdowns will open on hover instead of click.<br/>This also allows for top level links to be clickable to pages.',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'message'           => '',
					'default_value'     => 0,
					'ui'                => 1,
					'ui_on_text'        => '',
					'ui_off_text'       => '',
				),
				array(
					'key'               => 'field_650464c76255f',
					'label'             => 'Language Translation Dropdown',
					'name'              => 'is_language_dropdown',
					'aria-label'        => '',
					'type'              => 'true_false',
					'instructions'      => 'Enable to display the Gtranslate language dropdown in the navigation bar.',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'message'           => '',
					'default_value'     => 0,
					'ui'                => 1,
					'ui_on_text'        => '',
					'ui_off_text'       => '',
				),
				// toggle scroll to top
				array(
					'key'               => 'scroll_to_top_key',
					'label'             => 'Scroll to Top',
					'name'              => 'scroll_to_top',
					'aria-label'        => '',
					'type'              => 'true_false',
					'instructions'      => 'Enable the scroll to top button.',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'message'           => '',
					'default_value'     => 0,
					'ui'                => 1,
					'ui_on_text'        => '',
					'ui_off_text'       => '',
				),
				array(
					'key'               => 'field_64ed3acb14939',
					'label'             => 'Miscellaneous',
					'name'              => '',
					'aria-label'        => '',
					'type'              => 'tab',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'placement'         => 'left',
					'endpoint'          => 0,
				),
				array(
					'key'               => 'field_64ed3b081493a',
					'label'             => 'Default Post Listing Thumbnail Image',
					'name'              => 'default_card_img',
					'aria-label'        => '',
					'type'              => 'image',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'return_format'     => 'id',
					'library'           => 'all',
					'min_width'         => '',
					'min_height'        => '',
					'min_size'          => '',
					'max_width'         => '',
					'max_height'        => '',
					'max_size'          => '',
					'mime_types'        => '',
					'preview_size'      => 'medium',
				),
				array(
					'key'               => 'field_hero_img_fetch_priority',
					'label'             => 'Auto Fetch Priority: High on First Hero Image',
					'name'              => 'hero_img_fetch_priority',
					'aria-label'        => '',
					'type'              => 'true_false',
					'instructions'      => 'When enabled, automatically adds <code>fetchpriority="high"</code> to the first image inside <code>#main</code> on every page. Improves LCP scores by telling the browser to load the hero image sooner. Best used for sites where the first image is always a hero.',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'message'           => '',
					'default_value'     => 0,
					'ui'                => 1,
					'ui_on_text'        => '',
					'ui_off_text'       => '',
				),
				array(
					'key'               => 'field_6504653c6256c',
					'label'             => '404 Page',
					'name'              => '',
					'aria-label'        => '',
					'type'              => 'tab',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'placement'         => 'top',
					'endpoint'          => 0,
				),
				array(
					'key'               => 'field_650465496256d',
					'label'             => 'Hero Message',
					'name'              => 'hero_message',
					'aria-label'        => '',
					'type'              => 'wysiwyg',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => 'wysiwyg-sm',
						'id'    => '',
					),
					'default_value'     => 'Oops! Looks like the data you were searching for couldn\'t be found.',
					'tabs'              => 'all',
					'toolbar'           => 'full',
					'media_upload'      => 1,
					'delay'             => 0,
				),
				array(
					'key'               => 'field_6504657c6256e',
					'label'             => 'CTA Content',
					'name'              => 'cta_404',
					'aria-label'        => '',
					'type'              => 'group',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'layout'            => 'block',
					'sub_fields'        => array(
						array(
							'key'               => 'field_6504658e6256f',
							'label'             => 'Content',
							'name'              => 'content',
							'aria-label'        => '',
							'type'              => 'wysiwyg',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => 'wysiwyg-sm',
								'id'    => '',
							),
							'default_value'     => '',
							'tabs'              => 'all',
							'toolbar'           => 'full',
							'media_upload'      => 1,
							'delay'             => 0,
						),
						array(
							'key'               => 'field_659d81d4c27c6',
							'label'             => 'Buttons',
							'name'              => 'buttons',
							'aria-label'        => '',
							'type'              => 'repeater',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'layout'            => 'table',
							'pagination'        => 0,
							'min'               => 0,
							'max'               => 0,
							'collapsed'         => '',
							'button_label'      => 'Add Button',
							'rows_per_page'     => 20,
							'sub_fields'        => array(
								array(
									'key'               => 'field_659d81f5c27c7',
									'label'             => 'Link',
									'name'              => 'link',
									'aria-label'        => '',
									'type'              => 'link',
									'instructions'      => '',
									'required'          => 0,
									'conditional_logic' => 0,
									'wrapper'           => array(
										'width' => '',
										'class' => '',
										'id'    => '',
									),
									'return_format'     => 'array',
									'parent_repeater'   => 'field_659d81d4c27c6',
								),
							),
						),
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'lvl_sitewide',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
			'show_in_rest'          => 0,
		));

	endif;
});

// Disable any ACF fields that are not needed
add_filter('acf/get_fields', 'lvl_disable_acf_fields', 20, 2);
function lvl_disable_acf_fields($fields, $parent)
{

	// Disable Marker.io fields for all users except with @webmechanix.com or lvltools.com email
	$user = wp_get_current_user();
	if (
		! in_array('administrator', $user->roles)
		&& ! preg_match('/@webmechanix.com$/', $user->user_email)
		&& ! preg_match('/@lvltools.com$/', $user->user_email)
	) {
		$disable_fields = [
			// MARKER.IO TAB & FIELDS
			'markerio_tab_key',
			'is_markerio_key',
			'markerio_project_id_key',
			'dev_domains_key',
			// FORMS TAB & FIELDS
			'forms_tab_key',
			'field_64c7d849bbc0b',
		];
		foreach ($disable_fields as $disable_field) {
			if ($key = array_search($disable_field, array_column($fields, 'key'))) {
				unset($fields[$key]);
			}
		}
	}


	return $fields;
}


// Add WordPress Menus to ACF field
add_filter('acf/load_field/name=cta_menu', 'acf_load_menu_locations');
function acf_load_menu_locations($field)
{

	// reset choices
	$field['choices'] = array();

	// get nav menus
	$menus = get_terms('nav_menu');

	// turn nav menus list into array
	foreach ($menus as $menu) {
		$choices[] = $menu->name;
	}

	// loop through array and add to field 'choices'
	if (is_array($choices)) {
		foreach ($choices as $choice) {
			$field['choices'][$choice] = $choice;
		}
	}

	// return the field
	return $field;
}

// ACF Year Picker
add_filter('acf/load_field/name=select_year', 'acf_year_select');
function acf_year_select($field)
{

	$currentYear = date('Y');

	$field['choices'] = [];

	$field['default_value'] = $currentYear;

	foreach (range($currentYear, $currentYear - 50) as $year) {
		$field['choices'][$year] = $year;
	}


	return $field;
}

add_filter('acf/load_field/name=select_categories', 'acf_categories_select');
function acf_categories_select($field)
{

	$categories = get_categories();

	foreach ($categories as $cat) {
		$field['choices'][$cat->term_id] = $cat->name;
	}


	return $field;
}

add_filter('acf/load_field/name=select_tags', 'acf_tags_select');
function acf_tags_select($field)
{

	$tags = get_tags();

	foreach ($tags as $tag) {
		$field['choices'][$tag->term_id] = $tag->name;
	}


	return $field;
}

add_filter('acf/load_field/name=select_authors', 'acf_authors_select');
function acf_authors_select($field)
{

	global $wpdb;
	$query      = "SELECT DISTINCT post_author FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish'";
	$author_ids = $wpdb->get_col($query);

	if (! empty($author_ids)) {
		foreach ($author_ids as $author_id) {
			$author                         = get_userdata($author_id);
			$field['choices'][$author_id] = $author->display_name;
		}
	}

	return $field;
}

// Add Hubspot forms to hubspot_forms ACF select field
//add_filter( 'acf/load_field/name=hubspot_forms', 'acf_hubspot_forms' );
function acf_hubspot_forms($field)
{
	//TODO secure theme option
	$access_token = 'XXXXXXXXX';

	$url = 'https://api.hubapi.com/forms/v2/forms';

	$headers = array(
		'Authorization: Bearer ' . $access_token,
	);

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$response  = curl_exec($ch);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	if ($http_code == 200) {
		$forms = json_decode($response, true);
	} else {
		echo 'Error: ' . $http_code;
	}

	curl_close($ch);

	if ($forms) {

		$field['choices'] = [];

		usort($forms, function ($f1, $f2) {
			return strcmp(trim($f1['name']), trim($f2['name']));
		});

		foreach ($forms as $form) {
			$field['choices'][$form['guid']] = trim($form['name']);
		}
	}

	return $field;
}

function add_custom_fields_support_to_wp_block($args, $post_type)
{
	if ($post_type === 'wp_block') {
		$args['supports'][] = 'custom-fields';
	}
	return $args;
}
add_filter('register_post_type_args', 'add_custom_fields_support_to_wp_block', 10, 2);

// Register ACF field for wp_block post type, "Pattern"
add_action('acf/init', 'add_acf_field_to_wp_block');
function add_acf_field_to_wp_block()
{
	if (function_exists('acf_add_local_field_group')):

		acf_add_local_field_group(array(
			'key'                   => 'group_wp_block_field',
			'title'                 => 'Pattern Details',
			'fields'                => array(
				array(
					'key'             => 'field_wp_block_field_message',
					'label'           => 'Optional Kitchen Sink Details',
					'name'            => 'pattern_message',
					'type'            => 'message',
					'message'         => 'These fields are used to display the pattern on the "Kitchen Sink" page.',
					'label_placement' => 'none',
				),
				array(
					'key'          => 'field_wp_block_field_title',
					'label'        => 'Title',
					'name'         => 'pattern_title',
					'type'         => 'text',
					'instructions' => '',
				),
				array(
					'key'          => 'field_wp_block_field_description',
					'label'        => 'Description',
					'name'         => 'pattern_description',
					'type'         => 'textarea',
					'instructions' => '',
				),
				array(
					'key'         => 'field_wp_block_field_hide',
					'label'       => 'Hide on kitchen sink?',
					'name'        => 'pattern_hide',
					'type'        => 'true_false',
					'ui'          => 1, // Enable stylized UI
					'ui_on_text'  => 'Yes', // Text for the "On" state
					'ui_off_text' => 'No', // Text for the "Off" state
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'wp_block',
					),
				),
			),
			'position'              => 'side',
			'menu_order'            => 0,
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => 1,
			'description'           => '',
		));

	endif;
}
