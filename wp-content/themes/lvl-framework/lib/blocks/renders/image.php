<?php

/**
 * @param $block_content
 * @param $block
 *
 * @return false|mixed|string
 */
function lvl_render_block_filter__images( $block_content, $block ): mixed {

	$block['blockName'] = ( $block['blockName'] ?? 'noname' );

// Overriding the output of the Image core block
	if ( 'core/image' === $block['blockName'] ) {

//		if ( $block['attrs']['accent'] ?? false ) {
//			$block_content = preg_replace( '/<figure class="/', '<figure class="accent-drop-shadow ', $block_content, 1 );
//		}

		//if is svg image then add width and height attributes
		if ( strpos( $block_content, '.svg' ) !== false ) {
			try {
				$image      = wp_get_attachment_image_src( $block['attrs']['id'], 'full' );
				$src_width  = ( ( $image[1] ?? 1 ) ?: 1 );
				$src_height = ( ( $image[2] ?? 1 ) ?: 1 );

				if ( $block['attrs']['width'] ?? false ) {
					$width = $block['attrs']['width'];
				}

				if ( $block['attrs']['height'] ?? false ) {
					$height = $block['attrs']['height'];
				}

				if ( ! isset( $width ) && ! isset( $height ) ) {
					$width  = $src_width;
					$height = $src_height;
				} elseif ( ! isset( $width ) ) {
					// remove units
					$height = preg_replace( '/[^0-9]/', '', $height );
					$width  = $height * ( $src_width / $src_height );
				} elseif ( ! isset( $height ) ) {
					//remove units
					$width  = preg_replace( '/[^0-9]/', '', $width );
					$height = $width * ( $src_height / $src_width );
				}

				$block_content = preg_replace( '/<img /', '<img width="' . $width . '" height="' . $height . '" ', $block_content, 1 );
			} catch ( Exception $e ) {
				// do nothing
			}

		}


		//DEFAULT
		//<figure class="wp-block-image"><img src="/wp-content/themes/lvl-prime/dist/img/placeholder.webp" alt=""/></figure>
//        var_dump($block);

//        if ($block['attrs']['style'] ?? false) :

//        $style = ($block['attrs']['style'] ?? 'icon');
//
//        $class = '';
//
//        switch ($block['attrs']['style']) {
//            case 'background':
//                $class = 'img-style-background position-absolute h-100 w-100 top-0 left-0 z-n1';
//                $block_content = preg_replace('/<img /', '<img class="h-100 w-100 object-fit--cover" ', $block_content, 1);
//                break;
//            case 'icon':
//                $class = 'img-style-icon mx-auto mb-3 w-auto';
//                break;
//            case 'banner':
//                $class = 'img-style-banner mx-n3 mt-n3 mb-3';
//                break;
//        }
//
//        $block_content = preg_replace('/<figure class="/', '<figure class="' . $class . ' ', $block_content, 1);

//        endif;

	}

	return $block_content;
}
add_filter( 'render_block', 'lvl_render_block_filter__images', 10, 2 );