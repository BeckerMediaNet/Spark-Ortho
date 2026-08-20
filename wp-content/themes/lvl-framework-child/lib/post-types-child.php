<?php

/**
 * lvl_child_register_all_post_types function.
 * Registers all custom post types for the theme. Hooks into init.
 * @access public
 * @return void
 */
add_action('init', 'lvl_child_register_all_post_types');
function lvl_child_register_all_post_types(): void
{

	(new Level\PostTypes)->registerCPT(
		'landing-page',
		'Landing Page',
		'Landing Pages',
		[],
		'public',
		[
			'capability_type' => 'post',
			'has_archive'	  => false,
			'exclude_from_search' => true,
			'rewrite'         => array('slug' => 'lp', 'with_front' => false),
			'menu_icon'       => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M120-120v-80h720v80H120Zm622-202L120-499v-291l96 27 48 139 138 39-35-343 115 34 128 369 172 49q25 8 41.5 29t16.5 48q0 35-28.5 61.5T742-322Z"/></svg>'),
			'supports'        => array('editor', 'title', 'custom-fields', 'page-attributes'),
		]
	);

	(new Level\PostTypes)->registerCPT(
		'location',
		'Location',
		'Locations',
		[],
		'public',
		[
			'capability_type' => 'page',
			'hierarchical'    => true,
			'has_archive' => true,
			'rewrite'         => array('slug' => 'locations', 'with_front' => false),
			'menu_icon'       => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M480-480q33 0 56.5-23.5T560-560q0-33-23.5-56.5T480-640q-33 0-56.5 23.5T400-560q0 33 23.5 56.5T480-480Zm0 294q122-112 181-203.5T720-552q0-109-69.5-178.5T480-800q-101 0-170.5 69.5T240-552q0 71 59 162.5T480-186Zm0 106Q319-217 239.5-334.5T160-552q0-150 96.5-239T480-880q127 0 223.5 89T800-552q0 100-79.5 217.5T480-80Zm0-480Z"/></svg>'),
			'supports'        => array('title', 'editor', 'custom-fields', 'thumbnail', 'page-attributes', 'excerpt', 'revisions'),
			'show_in_rest'    => true,
			'query_var'       => 'location_page',
		]
	);

	(new Level\PostTypes)->registerCPT(
		'testimonial',
		'Testimonial',
		'Testimonials',
		[],
		'private',
		[
			'capability_type' => 'post',
			'rewrite'         => array('slug' => 'testimonial', 'with_front' => false),
			'menu_icon'       => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="m363-390 117-71 117 71-31-133 104-90-137-11-53-126-53 126-137 11 104 90-31 133ZM80-80v-720q0-33 23.5-56.5T160-880h640q33 0 56.5 23.5T880-800v480q0 33-23.5 56.5T800-240H240L80-80Zm126-240h594v-480H160v525l46-45Zm-46 0v-480 480Z"/></svg>'),
			'supports'        => array('title', 'custom-fields', 'thumbnail'),
		]
	);

	(new Level\PostTypes)->registerCPT(
		'team',
		'Team Member',
		'Team',
		[],
		'public',
		[
			'capability_type' => 'page',
			'hierarchical'    => true,
			'rewrite'         => array('slug' => 'doctors', 'with_front' => false),
			'menu_icon'       => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q66 0 130 15.5T616-378q29 15 46.5 43.5T680-272v112H40Zm720 0v-120q0-44-24.5-84.5T666-434q51 6 96 20.5t84 35.5q36 20 55 44.5t19 53.5v120H760ZM360-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm400-160q0 66-47 113t-113 47q-11 0-28-2.5t-28-5.5q27-32 41.5-71t14.5-81q0-42-14.5-81T544-792q14-5 28-6.5t28-1.5q66 0 113 47t47 113ZM120-240h480v-32q0-11-5.5-20T580-306q-54-27-109-40.5T360-360q-56 0-111 13.5T140-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T440-640q0-33-23.5-56.5T360-720q-33 0-56.5 23.5T280-640q0 33 23.5 56.5T360-560Zm0 320Zm0-400Z"/></svg>'),
			'supports'        => array('title', 'editor', 'custom-fields', 'thumbnail', 'page-attributes', 'excerpt', 'revisions'),
		]
	);

	(new Level\PostTypes)->registerCPT(
		'faq',
		'FAQ',
		'FAQs',
		[],
		'private',
		[
			'capability_type' => 'post',
			'has_archive'	  => false,
			'exclude_from_search' => true,
			'rewrite'         => array('slug' => 'faq', 'with_front' => false),
			'menu_icon'       => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M120-120v-720h720v720H120Zm360-120q25 0 42.5-17.5T540-300q0-25-17.5-42.5T480-360q-25 0-42.5 17.5T420-300q0 25 17.5 42.5T480-240Z"/></svg>'),
			'supports'        => array('title', 'editor', 'custom-fields', 'page-attributes'),
		]
	);
}

// Disable pagination for default post type archive
add_action('pre_get_posts', 'disable_post_archive_pagination');
function disable_post_archive_pagination($query)
{

	if (! is_admin() && $query->is_main_query() && $query->is_home()) {
		$query->set('posts_per_page', -1);
		$query->set('paged', 1);
	}
}
