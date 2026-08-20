<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom body classes
 * @throws Exception
 */
add_filter( 'body_class', function ( $classes ) {

	$is_transparent = false;
	if ( is_single() || is_page() ) {
		$is_transparent = get_field( 'transparent_navigation' );
	}


	if ( $is_transparent ) {
		$classes[] = 'transparent-navigation';
	}

	return $classes;
} );