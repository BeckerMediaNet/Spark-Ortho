<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/*
 * Functions to help migrate bulk imported content into the WP Block system
 */

/**
 * @param $content int | array - the post ID or array or attachment IDs to convert to a gallery
 *
 * @return string - parsed blocks as HTML
 */
function lvl_attachments_to_gallery_slideshow( $content ): array {
	if ( is_int( $content ) ) {
		$content = get_posts( [
			'post_type'      => 'attachment',
			'posts_per_page' => - 1,
			'post_parent'    => $content,
		] );
	} elseif ( is_array( $content ) ) {
		$content = array_filter( $content, 'is_int' );
	}

	if ( ! $content ) {
		return [
			'template' => '',
			'content'  => '',
		];
	}

	// template
	$gallery_template = '<!-- wp:lvl/section-wrapper {"name":"lvl/section-wrapper","data":{"id":"6626c5bd05034"},"mode":"preview","anchor":"lvl-section-wrapper-mmomtgp","verticalPadding":{"individual":false,"top":5,"bottom":5}} -->
	<!-- wp:lvl/gallery-slideshow {"name":"lvl/gallery-slideshow","data":{"slider_options_speed":"5","_slider_options_speed":"gallery_slideshow_field_657c987b8d86e","slider_options_autoplay":"0","_slider_options_autoplay":"gallery_slideshow_field_657c9cdfa181a","slider_options_navigation":"1","_slider_options_navigation":"gallery_slideshow_field_657c9cfca181d","slider_options_pagination":"1","_slider_options_pagination":"gallery_slideshow_field_657c9cfca181c","slider_options_stretch_width":"0","_slider_options_stretch_width":"gallery_slideshow_field_657c9f5e2dbee","slider_options_loop":"1","_slider_options_loop":"gallery_slideshow_field_658041f3cf592","slider_options":"","_slider_options":"gallery_slideshow_field_657c9d3f053d7","id":"664e58032e745"},"mode":"preview","anchor":"lvl-gallery-slideshow-bad6rmp"} -->';

	if ( $content ) {
		// add a new gallery-card to template per attachment
		foreach ( $content as $index => $attachment ) {
			$gallery_template .= '<!-- wp:lvl/gallery-card {"name":"lvl/gallery-card","data":{"link":"","_link":"key_gallery_card_field_link","id":"664e58032e7d7_' . $index . '"},"mode":"preview","anchor":"lvl-gallery-card-' . $attachment->ID . '"} -->
					<!-- wp:group {"style":{"spacing":{"padding":{"top":"0","bottom":"0"}}},"backgroundColor":"\u002d\u002dbs-gray-200","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-bs-gray-200-background-color has-background" style="padding-top:0;padding-bottom:0">
<!-- wp:image {"id":' . $attachment->ID . ',"sizeSlug":"full","linkDestination":"none","align":"center"} -->
<figure class="wp-block-image size-full aligncenter"><img src="' . wp_get_attachment_image_url( $attachment->ID, 'full' ) . '" alt="" class="wp-image-' . $attachment->ID . '"/></figure>
<!-- /wp:image -->
</div>
<!-- /wp:group -->
<!-- /wp:lvl/gallery-card -->';
		}
	}

	$gallery_template .= '<!-- /wp:lvl/gallery-slideshow -->
	<!-- /wp:lvl/section-wrapper -->';

	$parsed_blocks = parse_blocks( $gallery_template );

	$content = '';
	foreach ( $parsed_blocks as $block ) {
		$content .= render_block( $block );
	}

	return [
		'template' => $gallery_template,
		'content'  => $content,
	];
}

function lvl_vc_remove_shortcodes( $content ) {
	$pattern       = '/\[\/?vc(.*?)\]/';
	$replacement   = '';
	$clean_content = preg_replace( $pattern, $replacement, $content );

	return $clean_content;
}

function lvl_vc_shortcodes_get_image_ids( $content ) {
	$pattern = '/\[vc_single_image image="(\d+)".*?\]/';
	preg_match_all( $pattern, $content, $matches );

	$image_ids = $matches[1];

	$clean_content = lvl_vc_remove_shortcodes( $content );

	return array( 'clean_content' => $clean_content, 'image_ids' => $image_ids );
}

function lvl_vc_process_content( $content ) {
	$cleaned_content = lvl_vc_shortcodes_get_image_ids( $content );
	$content         = $cleaned_content['clean_content'];
	$image_ids       = [];//$cleaned_content['image_ids'];

	//									}
	return $content;
}


class LVLMigration_deprecated {
	private $uniq_id;

	public function __construct() {
		$this->uniq_id = uniqid( 'lvl_migrated_' );
	}

	public function build_banner(): string {
		$banner = array(
			'lvl/banner',
			array(
				'name'            => 'lvl/banner',
				'data'            => array(
					'background_image_alignment'             => 'center',
					'_background_image_alignment'            => 'field_65b945d12d27d',
					'background_image_fit'                   => 'cover',
					'_background_image_fit'                  => 'field_65b946022d27e',
					'background_image_tablet_inherit_image'  => '1',
					'_background_image_tablet_inherit_image' => 'field_65b94c4f5123a',
					'background_image_mobile_inherit_image'  => '1',
					'_background_image_mobile_inherit_image' => 'field_65b94caa5123b',
					'background_image'                       => '',
					'_background_image'                      => 'field_65808a34b1252',
					'schedule_start'                         => '',
					'_schedule_start'                        => 'field_65808aa8b1257',
					'schedule_end'                           => '',
					'_schedule_end'                          => 'field_65808b59b1258',
					'schedule'                               => '',
					'_schedule'                              => 'field_65808a97b1256',
					'overlay'                                => '1',
					'_overlay'                               => 'field_hero_657c9d3f053d6',
					'min_height'                             => '337',
					'_min_height'                            => 'field_65aff5ef887d0',
					'max_width'                              => '100',
					'_max_width'                             => 'field_65808bc2b1259',
					'alignment'                              => 'end',
					'_alignment'                             => 'field_6580b4e2a1968',
					'is_padding_disabled'                    => '0',
					'_is_padding_disabled'                   => 'field_6580b4e2a1968_padding',
					'full_banner_link'                       => '',
					'_full_banner_link'                      => 'field_65ef3e1418dc8',
					'id'                                     => $this->uniq_id . '_banner',
				),
				'mode'            => 'preview',
				'backgroundColor' => '--bs-dark',
			),
			array(
				array(
					'lvl/breadcrumb',
					array(
						'name' => 'lvl/breadcrumb',
						'data' => array(
							'id' => $this->uniq_id . '_breadcrumb',
						),
						'mode' => 'preview',
					),
					array(),
				),
				array(
					'core/heading',
					array(
						'fontSize' => 'h1-inner',
					),
					array(),
					'content' => '<h2 class="wp-block-heading has-h-1-inner-font-size">' . get_the_title() . '</h2>',
				),
			),
		);


		$banner_html = $this->process_block( $banner );

		return $this->render_block( $banner_html );
	}

	public function build_gallery( $ref ): string {
		$images = [];
		if ( is_int( $ref ) ) {
			$images = get_posts( [
				'post_type'      => 'attachment',
				'posts_per_page' => - 1,
				'post_parent'    => $ref,
			] );
		} elseif ( is_array( $ref ) ) {
			$images = array_filter( $ref, 'is_int' );
		}

		if ( ! $ref ) {
			return '';
		}


		$gallery_showcase = array(
			'lvl/section-wrapper',
			array(
				'name'            => 'lvl/section-wrapper',
				'data'            => array(
					'id' => '6647ad05ccd3c',
				),
				'mode'            => 'preview',
				'anchor'          => 'lvl-section-wrapper-7ihywvu',
				'verticalPadding' => array(
					'individual' => false,
					'top'        => 5,
					'bottom'     => 5,
				),
			),
			array(
				array(
					'lvl/gallery-slideshow',
					array(
						'name'   => 'lvl/gallery-slideshow',
						'data'   => array(
							'slider_options_speed'          => '5',
							'_slider_options_speed'         => 'gallery_slideshow_field_657c987b8d86e',
							'slider_options_autoplay'       => '0',
							'_slider_options_autoplay'      => 'gallery_slideshow_field_657c9cdfa181a',
							'slider_options_navigation'     => '1',
							'_slider_options_navigation'    => 'gallery_slideshow_field_657c9cfca181d',
							'slider_options_pagination'     => '1',
							'_slider_options_pagination'    => 'gallery_slideshow_field_657c9cfca181c',
							'slider_options_stretch_width'  => '0',
							'_slider_options_stretch_width' => 'gallery_slideshow_field_657c9f5e2dbee',
							'slider_options_loop'           => '1',
							'_slider_options_loop'          => 'gallery_slideshow_field_658041f3cf592',
							'slider_options'                => '',
							'_slider_options'               => 'gallery_slideshow_field_657c9d3f053d7',
							'id'                            => '664e58032e745',
						),
						'mode'   => 'preview',
						'anchor' => 'lvl-gallery-slideshow-bad6rmp',
					),
					'gallery_cards' => array(
						array(
							'lvl/gallery-card',
							array(
								'name'   => 'lvl/gallery-card',
								'data'   => array(
									'link'  => '',
									'_link' => 'key_gallery_card_field_link',
									'id'    => '664e58032e7a9',
								),
								'mode'   => 'preview',
								'anchor' => 'lvl-gallery-card-5jiu08q',
							),
							array(
								array(
									'core/group',
									array(
										'style'           => array(
											'spacing' => array(
												'padding' => array(
													'top'    => '0',
													'bottom' => '0',
												),
											),
										),
										'backgroundColor' => '--bs-gray-200',
										'layout'          => array(
											'type' => 'constrained',
										),
									),
									array(
										array(
											'core/image',
											array(
												'id'              => 17090,
												'scale'           => 'cover',
												'sizeSlug'        => 'full',
												'linkDestination' => 'none',
												'align'           => 'center',
											),
											array(),
											'content' => '<figure class="wp-block-image aligncenter size-full"><img src="https://structtech.wpengine.com/wp-content/uploads/2024/05/CHsq3.jpg" alt="" class="wp-image-17086"/></figure>',
										),

									),
								),
							),
						),
					),
				),

			),
		);

		foreach ( $images as $image ) {
			$gallery_showcase[2][0][2][] = array(
				'lvl/gallery-card',
				array(
					'name'   => 'lvl/gallery-card',
					'data'   => array(
						'link'  => '',
						'_link' => 'key_gallery_card_field_link',
						'id'    => '664e58032e7a9',
					),
					'mode'   => 'preview',
					'anchor' => 'lvl-gallery-card-5jiu08q',
				),
				array(
					array(
						'core/group',
						array(
							'style'           => array(
								'spacing' => array(
									'padding' => array(
										'top'    => '0',
										'bottom' => '0',
									),
								),
							),
							'backgroundColor' => '--bs-gray-200',
							'layout'          => array(
								'type' => 'constrained',
							),
						),
						array(
							array(
								'core/image',
								array(
									'id'              => $image->ID,
									'scale'           => 'cover',
									'sizeSlug'        => 'full',
									'linkDestination' => 'none',
									'align'           => 'center',
								),
								array(),
								'content' => '<figure class="wp-block-image aligncenter size-full"><img src="' . wp_get_attachment_image_url( $image->ID ) . '" alt="" class="wp-image-' . $image->ID . '"/></figure>',
							),

						),
					),
				),
			);
		}

		$gallery_html = $this->process_block( $gallery_showcase );

		return $this->render_block( $gallery_html );
	}

	private function render_block( string $html ): string {
		$parsed  = parse_blocks( $html );
		$content = '';
		foreach ( $parsed as $block ) {
			$content .= render_block( $block );
		}

		return $content;
	}


	private function process_block( array $block, $options = [] ): string {
		$defaults = [
			'is_inner' => false,
			'content'  => '',
		];

		$options = array_merge( $defaults, $options );

		$block_name         = $block[0];
		$block_data         = $block[1];
		$block_inner_blocks = $block[2];

		$block_data = json_encode( $block_data );

		$inner_blocks = '';
		foreach ( $block_inner_blocks as $inner_block ) {
			$inner_blocks .= $this->process_block( $inner_block, ( $inner_block['content'] ?? false ? [ 'content' => $inner_block['content'] ] : [] ) );
		}

		if ( $options['content'] ) {
			$inner_blocks = $options['content'];
		}

		$block_html = '<!-- wp:' . $block_name . ' ' . $block_data . ' -->' . $inner_blocks . '<!-- /wp:' . $block_name . ' -->';

		return $block_html;
	}

	public function get_page_banner() {
		?>

		<?php
	}
}