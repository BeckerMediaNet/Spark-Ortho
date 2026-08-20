<?php

/**
 * @param $block_content
 * @param $block
 *
 * @return false|mixed|string
 */
function lvl_render_block_filter__search( $block_content, $block ): mixed {

	$block['blockName'] = ( $block['blockName'] ?? 'noname' );

	if ( 'core/search' === $block['blockName'] ) {
		// STYLE INNER BUTTON
		if ( strpos( $block_content, 'wp-block-search__button-inside' ) !== false ) {
			$block_content = str_replace( 'class="wp-block-search__inside-wrapper',
				'class="wp-block-search__inside-wrapper input-group p-0', $block_content );
		}
		//INPUT
		$block_content = str_replace( 'class="wp-block-search__input',
			' class="wp-block-search__input form-control', $block_content );
		//BUTTON
//		$block_content = str_replace( '<button class="wp-block-search__button',
//			'<button class="wp-block-search__button btn btn-primary', $block_content );
		// WRAPPER
		$block_content = str_replace( 'class="wp-block-search__inside-wrapper',
			'data-bs-theme="light" class="wp-block-search__inside-wrapper', $block_content );

        // SVG SWAP
//        $block_content = str_replace( '<svg class="search-icon" viewBox="0 0 24 24" width="24" height="24">',
//            '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">', $block_content );
//        $block_content = str_replace( '<path d="M13 5c-3.3 0-6 2.7-6 6 0 1.4.5 2.7 1.3 3.7l-3.8 3.8 1.1 1.1 3.8-3.8c1 .8 2.3 1.3 3.7 1.3 3.3 0 6-2.7 6-6S16.3 5 13 5zm0 10.5c-2.5 0-4.5-2-4.5-4.5s2-4.5 4.5-4.5 4.5 2 4.5 4.5-2 4.5-4.5 4.5z"></path>', '<path d="M19.7408 17.2878L15.8318 13.3854C15.6363 13.2293 15.4018 13.1122 15.1672 13.1122H14.5418C15.5972 11.7463 16.2618 10.0293 16.2618 8.11707C16.2618 3.66829 12.5872 0 8.13088 0C3.63544 0 0 3.66829 0 8.11707C0 12.6049 3.63544 16.2341 8.13088 16.2341C10.0072 16.2341 11.7272 15.6098 13.1345 14.5171V15.1805C13.1345 15.4146 13.2127 15.6488 13.4081 15.8439L17.2781 19.7073C17.669 20.0976 18.2554 20.0976 18.6072 19.7073L19.7017 18.6146C20.0926 18.2634 20.0926 17.678 19.7408 17.2878ZM8.13088 13.1122C5.35543 13.1122 3.12726 10.8878 3.12726 8.11707C3.12726 5.38537 5.35543 3.12195 8.13088 3.12195C10.8672 3.12195 13.1345 5.38537 13.1345 8.11707C13.1345 10.8878 10.8672 13.1122 8.13088 13.1122Z" fill="white"/></svg>', $block_content );
		// regex replace class="wp-block-search__button has-icon wp-element-button" with or without "has-icon"
		$block_content = preg_replace( '/class="wp-block-search__button( has-icon)? wp-element-button"/',
			'class="wp-block-search__button btn btn-primary ${1}"', $block_content );


		// BS THEME
//        $block_content = str_replace('<form', '<form data-bs-theme="light"', $block_content);
//        $block_content = str_replace('<form class="', '<form data-bs-toggle="dropdown" aria-expanded="false" class="dropdown ', $block_content);
		// RELEVANSSI LIVE SEARCH
		$block_content = str_replace( 'name="s"', 'name="s" data-rlvlive="true" data-rlvparentel="#rlvlive" data-rlvconfig="default"', $block_content );
//        $block_content = str_replace('</button>', '</button><div id="rlvlive"></div>', $block_content);
		// before and of form add <div class=quick-links>

		$quick_links = get_field( 'search_quick_links', 'options' );
		if ( $quick_links ) {
			ob_start();
			?>
			<div class="quick-links p-3 rounded-0 border border-primary bg-white dropdown-menu w-100" data-bs-theme="light">
				<h3>Quick Links</h3>
				<ul class="list-unstyled content-columns-2">
					<?php foreach ( $quick_links as $quick_link ) {
						$link = $quick_link['link'];
						if ( $link ) {
							$link_url    = $link['url'];
							$link_title  = $link['title'];
							$link_target = $link['target'] ?: '_self';
							?>
							<li>
								<a class="d-inline-block py-1 fw-bold text-decoration-none" href="<?php echo $link_url; ?>" target="<?php echo $link_target; ?>"><?php echo $link_title; ?></a>
							</li>
							<?php
						}
						?>
					<?php } ?>
				</ul>
			</div>
			<?php
			$output = ob_get_clean();

			$block_content = str_replace( '</form>', '<div class="search-quick-links">' . $output . '</div></form>', $block_content );
			$block_content = '<div aria-expanded="false" class="wp-block-search-dropdown dropdown">' . $block_content . '</div>';
		}
	}

	return $block_content;
}
add_filter( 'render_block', 'lvl_render_block_filter__search', 10, 2 );