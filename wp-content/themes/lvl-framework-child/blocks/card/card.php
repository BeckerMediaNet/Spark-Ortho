<?php if (!defined('ABSPATH')) {
	exit;
}

$render = function ($block, $is_preview, $content) {

	$the_block = new Level\Block($block, ['is_preview' => $is_preview]);

	$link = $the_block->getField('link');
	$text = ($link['title'] ?? '') ?: 'Learn more';
	$url = $link['url'] ?? '';
	$target = ($link['target'] ?? false) ? 'target="_blank"' : 'target="_self"';
	$disable_hover = $the_block->getField('disable_hover');

	if ($disable_hover)
		$the_block->addClass('linked-no-style');

	$the_block->addAttribute(['data-lvl-stretch-link' => 'true']);

	$innerBlocks = [
		//group
		[
			'core/group',
			[
				'style' => [
					'spacing' => [
						'padding' => [
							'top'    => 'var:preset|spacing|30',
							'bottom' => 'var:preset|spacing|30',
							'left'   => 'var:preset|spacing|30',
							'right'  => 'var:preset|spacing|30',
						],
					],
				],
			],
			[
				[
					'core/image',
					[
						'url' => '/wp-content/themes/lvl-framework/dist/img/placeholder.webp',
					],
				],
				[
					'core/heading',
					[
						'level'   => 3,
						'content' => '',
						'style'   => [
							'spacing' => [
								'margin' => [
									'bottom' => 'var:preset|spacing|4',
								],
							],
						],
					],
				],
				[
					'core/paragraph',
					[
						'content' => '',
					],
				],
				[
					'core/buttons',
					[
						'content' => [
							[
								'text' => 'Learn more',
								'url'  => '#',
							],
						],
					],
				],
			],
		],
	];

	$inner = '<InnerBlocks template="' . esc_attr(wp_json_encode($innerBlocks)) . '" templateLock="false" />';

	ob_start(); ?>

	<div class="card-wrapper">
		<div class="card-inner" <?php echo $the_block->renderStyle(1); ?>>
			<div class="card-body"><?php echo $inner; ?></div>

			<?php if ($link) : ?>
				<a href="<?php echo $url; ?>" class="visually-hidden-focusable" <?php echo $target ?>><span class="visually-hidden"><?php echo $text; ?></span></a>
			<?php endif; ?>
		</div>
	</div>


<?php

	$output = ob_get_clean();

	echo $the_block->renderSection($output, 'basic');
};

$render($block, $is_preview, $content);
