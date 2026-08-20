<?php if (!defined('ABSPATH')) exit;

$render = function ($block, $is_preview, $content) {

	$the_block = new Level\Block($block);
	$title = $the_block->getField('tab_title');
	$desc = $the_block->getField('tab_desc');
	$icon = $the_block->getField('tab_icon');

	$innerBlocks = [
		[
			'core/heading',
			[
				'level'   => 4,
				'content' => 'Tempore incidunt omnis quidem eius',
			],
		],
		[
			'core/paragraph',
			[
				'content' => 'Quo similique voluptates illo. Ut cumque quibusdam quia. Quibusdam et officiis dolorem dignissimos alias. Iusto ut voluptates sint animi deleniti sequi voluptatem corporis. Quia voluptatum fuga. Occaecati est nam ab in aspernatur molestias vero earum.',
			],
		],
	];

	// $allowedBlocks = ['core/heading', 'core/paragraph', 'core/button', 'core/spacer', 'core/separator', 'wmx/accordion'];

	$inner = '<InnerBlocks template="' . esc_attr(wp_json_encode($innerBlocks)) . '" templateLock="false" />';

	$the_block->addAttribute(['data-bs-theme' => 'light']);

	ob_start(); ?>

		<div class="block--tab-panel tab-pane fade" data-title="<?php echo $title; ?>" data-desc="<?php echo $desc; ?>" data-icon="<?php echo $icon; ?>" role="tabpanel">
			<div class="accordion-header">
				<button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="" aria-expanded="false" aria-controls="">
						<h5><?php echo $title; ?></h5>
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
