<?php if (!defined('ABSPATH')) exit;

$render = function ($block, $is_preview, $content) {

    $the_block = new Level\Block($block);

	$image = $the_block->getProp('style:background:backgroundImage:id');

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

    $allowedBlocks = ['core/group', 'core/heading', 'core/paragraph', 'core/list', 'core/buttons', 'core/spacer', 'core/separator'];

    $inner = '<InnerBlocks template="' . esc_attr(wp_json_encode($innerBlocks)) . '" templateLock="false" allowedBlocks="' . esc_attr(wp_json_encode($allowedBlocks)) . '" />';

    ob_start(); ?>

	<div class="container">

		<div class="wrapper">

			<div class="content is-layout-flow">

				<?php echo $inner; ?>
			
			</div>

			<div class="image">

				<?php echo wp_get_attachment_image($image, 'large', false, ['class' => 'img-fluid', 'loading' => 'eager']); ?>

			</div>
		</div>
	</div>

    <?php

    $output = ob_get_clean();

    echo $the_block->renderSection($output, 'basic');

};

$render($block, $is_preview, $content);