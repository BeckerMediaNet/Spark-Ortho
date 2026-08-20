<?php
// TODO: DEPRECATED?
/**
 * @param $block_content
 * @param $block
 *
 * @return false|mixed|string
 */
function lvl_render_block_filter__buttons( $block_content, $block ): mixed {

	$block['blockName'] = ( $block['blockName'] ?? 'noname' );

	if ( false && 'core/buttons' === $block['blockName'] ) { // TODO: REVISIT with wp_render_layout_support_flag removal
		$buttons = $block['innerBlocks'];

		$classes = [];
		if ( $block['attrs']['layout']['justifyContent'] ?? false ) {
			$classes[] = 'justify-content-' . $block['attrs']['layout']['justifyContent'];
		}
		if ( $block['attrs']['layout']['alignItems'] ?? false ) {
			$classes[] = 'align-items-' . $block['attrs']['layout']['alignItems'];
		}
		if ( $block['attrs']['className'] ?? false ) {
			$classes[] = $block['attrs']['className'];
		}

		$styles = '';
		// match on "style=""
		preg_match( '/style="(.*?)"/s', $block_content, $styles );
		if ( count( $styles ) > 0 ) {
			$styles = $styles[1];
		}

		ob_start() ?>

		<div class="wp-block-buttons<?php echo( $classes ? ' ' . implode( ' ', $classes ) : '' ); ?>"<?php echo( $styles ? 'style="' . $styles . '"' : '' ); ?>>
			<?php //foreach ( $buttons as $button ) {
				$block_content = str_replace( 'is-style-btn-primary', '', $block_content );
				$block_content = str_replace( 'wp-block-button__link', '', $block_content );
				$block_content = str_replace( 'wp-element-button', '', $block_content );
				preg_match( '/is-style-(.*?)"/s', $block_content, $style );
				$style_class         = 'btn btn-' . ( $style[1] ?? 'primary' );
				$block_content = str_replace( 'a class="', 'a class="' . $style_class, $block_content );


				if ( strpos( $block_content, 'is-style-arrow' ) !== false ) {
					$block_content = str_replace( '</a>', '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="18" viewBox="0 0 18 9" fill="none"><path d="M16 4.26099L16.7071 3.55388L17.4142 4.26099L16.7071 4.96809L16 4.26099ZM1 5.26099H0V3.26099H1V5.26099ZM13.2071 0.0538796L16.7071 3.55388L15.2929 4.96809L11.7929 1.46809L13.2071 0.0538796ZM16.7071 4.96809L13.2071 8.46809L11.7929 7.05388L15.2929 3.55388L16.7071 4.96809ZM16 5.26099H1V3.26099H16V5.26099Z" fill="currentColor"/></svg></a>', $block_content );
				}

				echo $block_content;

			//} ?>

            <?php //foreach ( $buttons as $button ) {
//				$button['innerHTML'] = str_replace( 'is-style-btn-primary', '', $button['innerHTML'] );
//				$button['innerHTML'] = str_replace( 'wp-block-button__link', '', $button['innerHTML'] );
//				$button['innerHTML'] = str_replace( 'wp-element-button', '', $button['innerHTML'] );
//				preg_match( '/is-style-(.*?)"/s', $button['innerHTML'], $style );
//				$style_class         = 'btn btn-' . ( $style[1] ?? 'primary' );
//				$button['innerHTML'] = str_replace( 'a class="', 'a class="' . $style_class, $button['innerHTML'] );
//
//
//				if ( strpos( $button['innerHTML'], 'is-style-arrow' ) !== false ) {
//					$button['innerHTML'] = str_replace( '</a>', '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="18" viewBox="0 0 18 9" fill="none"><path d="M16 4.26099L16.7071 3.55388L17.4142 4.26099L16.7071 4.96809L16 4.26099ZM1 5.26099H0V3.26099H1V5.26099ZM13.2071 0.0538796L16.7071 3.55388L15.2929 4.96809L11.7929 1.46809L13.2071 0.0538796ZM16.7071 4.96809L13.2071 8.46809L11.7929 7.05388L15.2929 3.55388L16.7071 4.96809ZM16 5.26099H1V3.26099H16V5.26099Z" fill="currentColor"/></svg></a>', $button['innerHTML'] );
//				}
//
//				echo $button['innerHTML'];
//
//			} ?>

		</div>


		<?php

		$block_content = ob_get_clean();

	}

	if ( 'core/button' === $block['blockName'] ) {
		// DEFAULT = //<div class="wp-block-button has-custom-width wp-block-button__width-100 is-style-outline-light"><a class="wp-block-button__link has-text-align-center wp-element-button" href="#test">Some Button</a></div>

		$btn_style     = preg_match( '/is-style-(.*?)"/s', $block_content, $matches );
		$btn_style     = $matches[1] ?? 'primary';
		$btn_style     = 'btn btn-' . $btn_style;
		$block_content = str_replace( 'wp-element-button', $btn_style, $block_content );
		$block_content = str_replace( 'wp-block-button__link', '', $block_content );
	}

	return $block_content;
}
// add_filter( 'render_block', 'lvl_render_block_filter__buttons', 10, 2 );