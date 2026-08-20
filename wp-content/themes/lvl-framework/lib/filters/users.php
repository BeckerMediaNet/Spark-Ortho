<?php

const LVL_AGENCY_EMAIL_DOMAIN = ['@level.agency', '@webmechanix.com'];
const LVL_AGENCY_USERNAMES = ['wmxadmin', 'lvladmin'];

if (!is_admin()) {
	/**
	 * Filter agency admin user display name to be site title
	 */
	add_filter('the_author', 'lvl_filter_author_name');
	function lvl_filter_author_name($name)
	{
		if (in_array($name, LVL_AGENCY_USERNAMES)) {
			return get_bloginfo('name');
		}

		global $authordata;

		if( !is_object( $authordata ) ) {
			return $name;
		}

		$author = $authordata->user_email;
		$domains = LVL_AGENCY_EMAIL_DOMAIN;

		foreach ($domains as $domain) {
			if (str_contains($author, $domain)) {
				return get_bloginfo('name');
			}
		}


		return $name;
	}

	/**
	 * Filter agency admin user display name to be site title
	 */
	add_filter('get_the_author_display_name', 'lvl_filter_author_display_name', 10, 2);
	function lvl_filter_author_display_name($display_name, $author_id)
	{
		$author_email = get_the_author_meta('user_email', $author_id);
		$domains = LVL_AGENCY_EMAIL_DOMAIN;

		foreach ($domains as $domain) {
			if (strpos($author_email, $domain) !== false) {
				return get_bloginfo('name');
			}
		}

		return $display_name;
	}

	/**
	 * Filter agency admin user link to be empty
	 */
	add_filter('author_link', 'lvl_filter_author_link', 10, 3);
	function lvl_filter_author_link($link, $author_id, $author_nicename)
	{
		$author_email = get_the_author_meta('user_email', $author_id);
		$domains = LVL_AGENCY_EMAIL_DOMAIN;

		foreach ($domains as $domain) {
			if (strpos($author_email, $domain) !== false) {
				return '';
			}
		}

		return $link;
	}
}