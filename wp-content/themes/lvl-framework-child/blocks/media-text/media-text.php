<?php if (!defined('ABSPATH')) exit;

$render = function ($block, $is_preview, $content) {

	$the_block = new Level\Block($block);
	$image = $the_block->getField('image') ?? false;
	$video = $the_block->getField('video_url') ?? '';

	$innerBlocks = [
		[
			'core/paragraph',
			[
					'content' => 'Quo similique voluptates illo. Ut cumque quibusdam quia. Quibusdam et officiis dolorem dignissimos alias. Iusto ut voluptates sint animi deleniti sequi voluptatem corporis. Quia voluptatum fuga. Occaecati est nam ab in aspernatur molestias vero earum.',
			],
		],
	];

	$inner = '<InnerBlocks template="' . esc_attr(wp_json_encode($innerBlocks)) . '" templateLock="false" />';

	ob_start(); ?>

		<div class="background<?php echo ($the_block->getProp('backgroundColor'))? ' has-background has-' .$the_block->getProp('backgroundColor'). '-background-color' : ''; ?>"></div>

		<div class="wrapper">

			<div class="inner-wrapper is-layout-flow">
			
				<?php echo $inner; ?>
				
			</div>

			<div class="media <?php echo ($video)? 'has-media-video' : '';?>" data-media-src="<?php echo $video; ?>">
				<?php echo ($image)? wp_get_attachment_image($image, 'large', false, ['loading' => 'lazy']) : ''; ?>
			</div>
			
		</div>

	<?php

	$output = ob_get_clean();

	echo $the_block->renderSection($output, 'basic');
};

$render($block, $is_preview, $content);
