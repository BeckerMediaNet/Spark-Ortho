<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// add_filter( 'render_block', 'lvl_client_render_button_filter', 10, 2 );
function lvl_client_render_button_filter( $block_content, $block ): mixed {

	if ( 'core/button' === $block['blockName'] ) {
		if ( str_contains( $block['attrs']['className'] ?? '', 'is-style-btn-link' ) ) {

			$block_content = str_replace( '</a>', '<svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" viewBox="0 0 42 33" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M25.3431 9.92888L31.7071 16.2928C32.0976 16.6834 32.0976 17.3165 31.7071 17.7071L25.3431 24.071C24.9526 24.4615 24.3195 24.4615 23.9289 24.071C23.5384 23.6805 23.5384 23.0473 23.9289 22.6568L28.5858 17.9999H1C0.447715 17.9999 0 17.5522 0 16.9999C0 16.4477 0.447715 15.9999 1 15.9999H28.5858L23.9289 11.3431C23.5384 10.9526 23.5384 10.3194 23.9289 9.92888C24.3195 9.53836 24.9526 9.53836 25.3431 9.92888Z" fill="currentColor"></path></svg></a>', $block_content );
		}

		// if contains "is-style-bs-" then replace with "btn btn-"
		if ( preg_match( '/is-style-bs-(.*?)"/', $block_content, $style ) ) {
			$style_class   = 'wp-element-button-- btn btn-' . ( $style[1] ?? 'primary' );
			$block_content = str_replace( 'wp-element-button', $style_class, $block_content );
			// remove wp-block-button__link
			$block_content = str_replace( 'wp-block-button__link', '', $block_content );
		}
	}

	return $block_content;

}