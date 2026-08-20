<?php if (!defined('ABSPATH')) exit;

$render = function ($block, $is_preview, $content) {

	$the_block = new Level\Block($block);
	$title = $the_block->getField('accordion_title') ?? '';
	$is_open = $the_block->getField('is_open') ?? false;

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

		<div class="block--accordion-panel accordion-item">
			
			<div class="accordion-header">
				<button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="" aria-expanded="false" aria-controls="">
						<h4 class="mb-0"><?php echo $title; ?></h4>
				</button>
			</div>
			<div class="accordion-collapse collapse" aria-labelledby="" data-bs-parent="">
				<div class="accordion-body">
					<?php echo $inner; ?>
				</div>
			</div>
		</div>

	<?php

	$output = ob_get_clean();

	echo $the_block->renderSection($output, false);
};

$render($block, $is_preview, $content);
