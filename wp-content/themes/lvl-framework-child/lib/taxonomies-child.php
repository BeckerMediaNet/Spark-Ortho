<?php

/**
 * lvl_child_register_all_taxonomies function.
 *
 * Registers all custom taxomonies used by the theme.
 *
 * @access public
 * @return void
 */
add_action('init', 'lvl_child_register_all_taxonomies');
function lvl_child_register_all_taxonomies(): void
{

	// Example
	(new Level\Taxonomies)->registerTax(
		'location_service',
		['location'],
		'Location Service',
		'Location Services',
		'public',
		[
			'hierarchical' => true,
		]
	);

	(new Level\Taxonomies)->registerTax(
		'location_language',
		['location'],
		'Language',
		'Languages',
		'public',
		[
			'hierarchical' => true,
		]
	);


	(new Level\Taxonomies)->registerTax(
		'topics',
		['faq'],
		'Topics',
		'Topics',
		'public',
		[
			'hierarchical' => true,
		]
	);

	(new Level\Taxonomies)->registerTax(
		'team_category',
		['team'],
		'Team Category',
		'Team Categories',
		'public',
		[
			'hierarchical' => true,
		]
	);


	(new Level\Taxonomies)->registerTax(
		'specialty',
		['team'],
		'Specialty',
		'Specialties',
		'public',
		[
			'hierarchical' => true,
		]
	);
}
