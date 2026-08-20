<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

locate_template( 'lib/blocks/renders/buttons.php', true, true );
locate_template( 'lib/blocks/renders/image.php', true, true );
locate_template( 'lib/blocks/renders/search.php', true, true );
locate_template( 'lib/blocks/renders/spacer.php', true, true );

// ADD hasParent hasChild attributes to blocks to use in rendering
// https://wordpress.org/support/topic/gutemberg-how-to-know-if-a-block-has-a-parent/
/**
 * @param $parsed_block
 * @param $source_block
 * @param $parent_block
 *
 * @return array
 */
function lvl_block_data_pre_render( $parsed_block, $source_block, $parent_block ): array {

	$core_blocks = [
		'core/group',
		'core/column',
		'core/columns',
		'lvl/section-wrapper',
	];

	if (
		in_array( $source_block['blockName'], $core_blocks, true ) &&
		! is_admin() &&
		! wp_is_json_request()
	) {
		$parsed_block['attrs']['hasChild'] = 1;
		array_walk( $parsed_block['innerBlocks'], 'lvl_inner_block_looper' );
	} else {
		if ( ! isset( $parsed_block['attrs']['hasParent'] ) ) {
			$parsed_block['attrs']['hasParent'] = 0;
		}
	}

	// LIST ITEM
//    if ('core/list' === $source_block['blockName']) {
//        $parsed_block['attrs']['className'] = 'list-TEST';
//    }


	return $parsed_block;

}

add_filter( 'render_block_data', 'lvl_block_data_pre_render', 10, 3 );

/**
 * @param $itm
 * @param $key
 *
 * @return void
 */
function lvl_inner_block_looper( &$itm, $key ): void {

	if ( $key === 'attrs' ) {
		$itm['hasParent']  = 1;
		$itm['bs-wrapper'] = 0;
	}

	if ( is_array( $itm ) ) {
		array_walk( $itm, 'lvl_inner_block_looper' );
	}

}


/**
 * @param $block_content
 * @param $block
 *
 * @return false|mixed|string
 */
function lvl_render_block_filter( $block_content, $block ): mixed {

	$block['blockName'] = ( $block['blockName'] ?? 'noname' );

	return $block_content;
}

//add_filter( 'render_block', 'lvl_render_block_filter', 10, 2 );

/**
 * @param $block_content `
 * @param $block
 *
 * @return array|mixed|string|string[]|null
 */
function lvl_add_data_attribute( $block_content, $block ): mixed {
	$attributes = array();
	$classes    = array();
	$styles     = array();


	// if block name
	if ( $block['blockName'] == 'lvl/banner' ) {
//		var_dumped($block['attrs']['metadata']);
	}

	//
//	if ( $block['attrs']['layout'] ?? false ) { // TODO: REVISIT with wp_render_layout_support_flag removal
//		foreach ( $block['attrs']['layout'] as $key => $layout ) {
//			switch ( $key ) {
//				case 'flexWrap':
//					$classes[] = 'flex-' . $layout;
//					break;
//				case 'type':
//					$classes[] = 'd-' . $layout;
//					$classes[] = 'gap-3';
//					break;
//				case 'justifyContent':
//					if ( $block['attrs']['layout']['orientation'] ?? '' == 'vertical' ) {
//						$classes[] = 'align-items-' . $layout;
//					} else {
//						$classes[] = 'justify-content-' . $layout;
//					}
//					break;
//				case 'alignItems':
//					$classes[] = 'align-items-' . $layout;
//					break;
//				case 'alignContent':
//					$classes[] = 'align-content-' . $layout;
//					break;
//				case'orientation':
//					if ( $layout == 'vertical' ) {
//						$classes[] = 'flex-column';
//					}
//			}
//		}
//	}

	$skip_data_component = [
		'core/buttons',
	];
	// add block name to attributes
	if ( ! in_array( $block['blockName'], $skip_data_component, true ) ) {
		$attributes['component'] = 'data-component="' . str_replace( '/', ':', $block['blockName'] ?? '' ) . '"';
		if ( $block['attrs']['metadata']['name'] ?? false ) {
			$attributes['label'] = 'data-component-label="' . esc_attr( $block['attrs']['metadata']['name'] ) . '"';
		}
	}

	// list of data attributes we are looking to add
	$data_props = array(
		'order',
		'hide',
		'justification', // TODO revisit
		'stretchedBackground',
		'bs',
		'height',
		// 'style',
		//        'lvlClassList'
	);

	// loop through the data attributes
	foreach ( $data_props as $data_prop ) {
		// check if the block has data attribute from list above
		if ( isset( $block['attrs'][ $data_prop ] ) ) {

			if ( $data_prop == 'style' ) { //TODO: Revisit
				// parse key value pairs and add to styles array
				foreach ( $block['attrs'][ $data_prop ] as $key => $value ) {
					$styles[] = $key . ':' . $value . ';';
				}
				continue;
			}

			if ( $data_prop == 'lvlClassList' ) {
				// parse key value pairs and add to classes array
				foreach ( $block['attrs'][ $data_prop ] as $key => $value ) {
					$classes[] = $value;
				}
				continue;
			}

			if ( $data_prop == 'height' && $block['attrs'][ $data_prop ] !== '' ) {
				$styles[] = '--block-height:' . $block['attrs'][ $data_prop ] . ';';
				continue;
			}

			if ( $data_prop == 'bs' ) {

				foreach ( $block['attrs'][ $data_prop ] as $key => $item ) {
					// var_dump($item);
					$attributes[] = 'data-' . $data_prop . '-' . $key . '="' . $item . '"';
				}

				continue;
			}


			// check if the data attribute is not an array
			if ( ! is_array( $block['attrs'][ $data_prop ] ) ) {
				$attributes[] = 'data-' . $data_prop . '="' . $block['attrs'][ $data_prop ] . '"';

				if ( $data_prop == 'justification' ) {
					switch ( $block['attrs'][ $data_prop ] ) {
						case 'left':
							$classes[] = 'justify-content-start';
							break;
						case 'center':
							$classes[] = 'justify-content-center';
							break;
						case 'right':
							$classes[] = 'justify-content-end';
							break;
						case 'space-between':
							$classes[] = 'justify-content-between';
							break;
						default:
							$classes[] = 'justify--' . $block['attrs'][ $data_prop ];
					}
				}
			} else {
				// loop through the data attribute array
				foreach ( $block['attrs'][ $data_prop ] as $key => $item ) {
					$attributes[] = 'data-' . $data_prop . '-' . $key . '="' . var_export( $item, true ) . '"';
				}
			}

		}
	}

	if ( ! empty( $attributes ) ) {
		$classes = array_unique( $classes );

		if ( $block['blockName'] == 'core/button' && strpos( $block_content, 'href="' ) !== false ) {
			$component = $attributes['component'];
			unset( $attributes['component'] );
			$label = ( $attributes['label'] ?? '' );
			unset( $attributes['label'] );

			$block_content = preg_replace(
				'/href="/',
				$component . ' ' . $label . ' href="',
				$block_content,
				1
			);
		}

		// Add the data attributes to the block's output
		$attributes_str = implode( ' ', $attributes );
		$classes_str    = implode( ' ', $classes );

		$block_content = preg_replace_callback(
			'/class="[^"]*"/',
			function ($matches) use ($attributes_str, $classes_str) {
				return "{$attributes_str} class=\"{$classes_str}" . (!empty($classes_str) ? ' ' : '') . substr($matches[0], 7);
			},
			$block_content,
			1
		);

		//if style is not empty add to block content
		if ( ! empty( $styles ) ) {
			// check if block content has styles
			if ( strpos( $block_content, 'style="' ) !== false ) {
				// add styles to block content
				$block_content = preg_replace(
					'/style="/',
					'style="' . implode( ' ', $styles ),
					$block_content,
					1
				);
			} else {
				// add styles to block content
				$block_content = preg_replace(
					'/class="/',
					'style="' . implode( ' ', $styles ) . '" class="',
					$block_content,
					1
				);
			}


		}

	}

	return $block_content;
}

add_filter( 'render_block', 'lvl_add_data_attribute', 11, 2 );