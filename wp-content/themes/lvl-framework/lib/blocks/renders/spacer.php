<?php

/**
 * @param $block_content
 * @param $block
 *
 * @return false|mixed|string
 */
function lvl_render_block_filter__spacer( $block_content, $block ): mixed {

	$block['blockName'] = ( $block['blockName'] ?? 'noname' );

	if ( 'core/spacer' === $block['blockName'] ) {
		$block_content = str_replace( 'height:', 'min-height:', $block_content );
	}

	return $block_content;
}
add_filter( 'render_block', 'lvl_render_block_filter__spacer', 10, 2 );