<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

locate_template( 'lib/blocks/styles.php', true, true );
// TODO: in child, refactor to parent?
locate_template( 'lib/blocks/renders.php', true, true );


function get_block_dirs(): array|false {
	// loop through blocks directory and register each block (pointing to each block.json):
	$root       = get_template_directory() . '/blocks';
	$block_dirs = array_filter( glob( $root . '/*' ), 'is_dir' );
	// only keep the directory name
	$block_dirs = array_map( function ( $dir ) {
		return basename( $dir );
	}, $block_dirs );
//    var_dumped($block_dirs);

	if ( is_child_theme() ) {
		$root             = get_stylesheet_directory() . '/blocks';
		$child_block_dirs = array_filter( glob( $root . '/*' ), 'is_dir' );
		// only keep the directory name
		$child_block_dirs = array_map( function ( $dir ) {
			return basename( $dir );
		}, $child_block_dirs );
//        var_dumped($block_dirs);

		$block_dirs = array_merge( $block_dirs, $child_block_dirs );
		$block_dirs = array_unique( $block_dirs );
	}

	return $block_dirs;
}

// Register our custom blocks
add_action( 'init', function () {

	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	// exclude blocks not needed by this theme:
	// $exclusions = [
	// 	'cataclyst',
	// ];

	is_child_theme() ? $root = get_stylesheet_directory() . '/blocks' : $root = get_template_directory() . '/blocks';
	// get all directories from a path

	$block_dirs = get_block_dirs();

	foreach ( $block_dirs as $block_dir ) {
		$block_json = locate_template( '/blocks/' . $block_dir . '/block.json' );

		if ( $block_json ) {
			$file_variations = [
				'',
				'-alt',
				'-editor',
				'-init',
			];

			foreach ( $file_variations as $file_variation ) {
				// SCRIPTS
				$is_block_script = locate_template( '/blocks/' . $block_dir . '/dist/' . $block_dir . $file_variation . '.min.js' ); // todo: should this be limited to the block.json found?
				if ( $is_block_script ) {
					$block_script = get_theme_file_uri( '/blocks/' . $block_dir . '/dist/' . $block_dir . $file_variation . '.min.js' ); // todo: should this be limited to the block.json found?
					wp_register_script( 'lvl-block-js-' . $block_dir . $file_variation, $block_script, [], filemtime( $is_block_script )
					);
				}
				// STYLES
				$is_block_style = locate_template( '/blocks/' . $block_dir . '/dist/' . $block_dir . $file_variation . '.min.css' ); // todo: should this be limited to the block.json found?
				if ( $is_block_style ) {
					$block_style = get_theme_file_uri( '/blocks/' . $block_dir . '/dist/' . $block_dir . $file_variation . '.min.css' ); // todo: should this be limited to the block.json found?
					wp_register_style( 'lvl-block-css-' . $block_dir . $file_variation, $block_style, [], filemtime( $is_block_style )
					);
				}
				// AJAX
				$is_block_ajax = locate_template( 'blocks/' . $block_dir . '/ajax.php', true, true );
				if ( $is_block_ajax ) {
					wp_localize_script(
						'lvl-block-js-' . $block_dir,
						'lvl_block_' . str_replace( '-', '_', $block_dir ) . '_ajax',
						[
							'ajax_url' => admin_url( 'admin-ajax.php' ),
							'nonce'    => wp_create_nonce( 'lvl-block-' . $block_dir . '-nonce' ),
						]
					);
				}
				// FUNCTIONS
				locate_template( 'blocks/' . $block_dir . '/functions.php', true, true );
			}

			register_block_type( $block_json );
		}

	}

}, 3 );

// Disable some default blocks
add_filter( 'allowed_block_types_all', 'lvl_allow_block_types', 10, 2 );
function lvl_allow_block_types( $allowed_blocks, $editor_context ) {
	$allowed_blocks = [
//		'core/legacy-widget',
//		'core/widget-group',
//		'core/archives',
//		'core/avatar',
'core/block',
//		'core/calendar',
//	'core/categories',
//	'core/comment-author-name',
//	'core/comment-content',
//	'core/comment-date',
//	'core/comment-edit-link',
//	'core/comment-reply-link',
//	'core/comment-template',
//	'core/comments',
//	'core/comments-pagination',
//	'core/comments-pagination-next',
//	'core/comments-pagination-numbers',
//	'core/comments-pagination-previous',
//	'core/comments-title',
'core/cover',
'core/file',
'core/footnotes',
'core/gallery',
'core/heading',
//		'core/home-link',
'core/image',
//	'core/latest-comments',
//		'core/latest-posts',
//	'core/loginout',
//		'core/navigation',
//		'core/navigation-link',
//		'core/navigation-submenu',
//		'core/page-list',
//		'core/page-list-item',
'core/pattern',
//		'core/post-author',
//		'core/post-author-biography',
'core/post-author-name',
//	'core/post-comments-form',
//		'core/post-content',
'core/post-date',
'core/post-excerpt',
'core/post-featured-image',
//		'core/post-navigation-link',
//		'core/post-template',
//		'core/post-terms',
'core/post-title',
//		'core/query',
//		'core/query-no-results',
//		'core/query-pagination',
//		'core/query-pagination-next',
//		'core/query-pagination-numbers',
//		'core/query-pagination-previous',
//		'core/query-title',
//		'core/read-more',
'core/rss',
'core/search',
'core/shortcode',
//	'core/site-logo',
//	'core/site-tagline',
//	'core/site-title',
'core/social-link',
'core/tag-cloud',
'core/template-part',
//		'core/term-description',
'core/audio',
'core/button',
'core/buttons',
'core/code',
'core/column',
'core/columns',
'core/details',
'core/embed',
'core/freeform',
'core/group',
'core/html',
'core/list',
'core/list-item',
'core/media-text',
'core/missing',
'core/more',
//		'core/nextpage',
'core/paragraph',
'core/preformatted',
'core/pullquote',
'core/quote',
'core/separator',
'core/social-links',
'core/spacer',
'core/table',
'core/text-columns',
'core/verse',
'core/video',
	];

	$block_types = WP_Block_Type_Registry::get_instance()->get_all_registered();

	foreach ( $block_types as $block ) {
		// if starts with core skip
		if ( strpos( $block->name, 'core' ) === 0 ) {
			continue;
		}

		$allowed_blocks[] = $block->name;
	}

	return $allowed_blocks;

}

// Register theme blocks category
add_filter( 'block_categories_all', 'lvl_block_categories', 9, 2 );
function lvl_block_categories( $categories, $post ) {
	return array_merge(
		array(
			array(
				'icon'  => 'superhero',
				//<svg fill="none" height="121" viewBox="0 0 141 121" width="141" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><linearGradient id="logomarkgradient_a" gradientUnits="userSpaceOnUse" x1="0" x2="118.875" y1="0" y2="139.253"><stop offset=".178333" stop-color="#009fd4"/><stop offset="1" stop-color="#46b756"/></linearGradient><path d="m135.094 8.20192c-1.371-1.91733-3.48-3.40859-5.8-4.0477l.316.10652c-7.171-2.55644-27.314-3.941185-47.1403-4.26074h-26.5759c-18.9828.532592-37.6492 1.81081-44.6096 4.26074l.3164-.10652c-2.32012.63911-4.42932 2.13037-5.8003 4.0477-5.37846 7.45628-5.8003 40.69008-5.8003 50.70278v2.4499c0 10.1193.42184 43.3534 5.90576 50.7024 1.37098 1.918 3.48018 3.409 5.80034 4.048l-.3164-.106c8.6477 2.982 35.8564 4.367 58.9521 4.367h.3164c23.0957 0 50.3048-1.385 58.9518-4.367l-.316.106c2.32-.639 4.429-2.13 5.8-4.048 5.484-7.349 5.906-40.6896 5.906-50.7024v-2.4499c0-10.0127-.422-43.2465-5.906-50.70278zm-5.378 53.15268c0 24.3928-2.004 39.9444-3.48 43.3534l-.106.106c-5.273 1.811-26.4703 3.835-55.3663 3.835h-.4219c-8.7532 0-16.7681-.213-23.9394-.533-1.0546-1.171-2.1092-2.343-3.1638-3.302.4218-1.278.8437-2.556 1.1601-3.834.3163-1.2787.5273-2.6635.5273-3.9417 2.1092-.5326 4.3238-1.2782 6.433-2.1304-.1054-1.2782-.1054-2.5564-.3164-3.8346-2.2146-.6391-4.4293-1.0652-6.7494-1.3848-.3164-1.2782-.7382-2.5564-1.1601-3.8346-.5273-1.1717-1.0546-2.4499-1.7928-3.5151 1.4765-1.8108 2.8474-3.6217 4.0075-5.6455-.7382-1.0652-1.5819-2.0239-2.4256-2.876-2.2146.7456-4.3238 1.8108-6.2221 2.9825-.9492-.8521-2.0038-1.7043-3.1638-2.3434-1.0546-.7456-2.3201-1.2782-3.4802-1.8108.2109-2.2369.2109-4.5803 0-6.9237-1.1601-.3196-2.3201-.6391-3.5856-.8522-1.2656 2.0239-2.4256 4.0477-3.3748 6.1781-1.2655-.1065-2.531-.1065-3.902 0-1.2655.213-2.531.3196-3.7965.7456-1.1601-2.0238-2.5311-3.9411-3.9021-5.6454-.1054 0-.2109.1065-.3163.1065 0-1.5978 0-3.3021 0-4.8999v-2.4499c0-24.3927 2.0037-39.9444 3.4801-43.353h.1055c5.273-1.8108 26.4705-3.8347 55.4719-3.8347h.211.2109c29.0015 0 50.1988 1.9174 55.3668 3.8347h.105c1.477 3.4086 3.48 18.9603 3.48 43.353v2.4499z" fill="url(#logomarkgradient_a)"/></svg>
				'slug'  => 'lvl-blocks',
				'title' => __( 'Theme Blocks', 'lvl-blocks' ),
			),
		),
		$categories
	);
}

// RENDERING PROCESSING
locate_template( '/lib/blocks/render_block.php', true, true );
