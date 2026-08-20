<?php
if( !defined('ABSPATH') ) { exit; }

/**
 * Add other post types to the list of enabled post types for the Duplicate Post plugin.
 * @param $enabled_post_types
 * @return mixed
 */
function lvl_custom_enabled_post_types($enabled_post_types ) {
	$enabled_post_types[] = 'wp_block';
	return $enabled_post_types;
}
add_filter('duplicate_post_enabled_post_types', 'lvl_custom_enabled_post_types');