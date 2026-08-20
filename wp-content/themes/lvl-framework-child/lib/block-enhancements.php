<?php

/**
 * Global option: automatically inject fetchpriority="high" on the first <img> inside #main.
 * Controlled via Theme Options > Miscellaneous > Auto Fetch Priority: High on First Hero Image.
 * Uses output buffering with strpos — no regex, no extra DB queries beyond the single ACF option read.
 */
add_action( 'template_redirect', function () {
	if ( is_admin() || ! get_field( 'hero_img_fetch_priority', 'option' ) ) {
		return;
	}
	ob_start( 'lvl_child_inject_first_img_fetch_priority' );
} );

function lvl_child_inject_first_img_fetch_priority( string $buffer ): string {

	// Find id="main" or id='main'
	$main_pos = strpos( $buffer, 'id="main"' );
	if ( $main_pos === false ) {
		$main_pos = strpos( $buffer, "id='main'" );
	}
	if ( $main_pos === false ) {
		return $buffer;
	}

	// Find the first <img after #main
	$img_pos = strpos( $buffer, '<img ', $main_pos );
	if ( $img_pos === false ) {
		return $buffer;
	}

	// Read the full img tag to check if fetchpriority is already present
	$tag_end = strpos( $buffer, '>', $img_pos );
	if ( $tag_end === false ) {
		return $buffer;
	}
	$img_tag = substr( $buffer, $img_pos, $tag_end - $img_pos + 1 );
	if ( str_contains( $img_tag, 'fetchpriority=' ) ) {
		return $buffer;
	}

	// Inject fetchpriority="high" immediately after "<img "
	return substr_replace( $buffer, '<img fetchpriority="high" ', $img_pos, 5 );
}

/**
 * Add fetchpriority="high" to core/image blocks when the fetchPriorityHigh attribute is enabled.
 * Toggle is found in the Image block's Advanced tab in the editor.
 * Recommended for above-the-fold images, especially hero images.
 */
add_filter( 'render_block', 'lvl_child_render_image_fetch_priority', 10, 2 );
function lvl_child_render_image_fetch_priority( $block_content, $block ): string {

	if ( 'core/image' !== ( $block['blockName'] ?? '' ) ) {
		return $block_content;
	}

	if ( empty( $block['attrs']['fetchPriorityHigh'] ) ) {
		return $block_content;
	}

	// Only inject if fetchpriority is not already present
	if ( str_contains( $block_content, 'fetchpriority=' ) ) {
		return $block_content;
	}

	$block_content = preg_replace( '/<img /', '<img fetchpriority="high" ', $block_content, 1 );

	return $block_content;
}
