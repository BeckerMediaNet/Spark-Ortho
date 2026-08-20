<?php if (!defined('ABSPATH')) exit;

$render = function ($block, $is_preview, $content) {

    $the_block = new Level\Block($block);
	$the_block->addClass('accordion');

	$the_block->getField('expanded')? $the_block->addAttribute(['data-expanded' => 'true'], 0) : '';

    $innerBlocks = [
        [
            'lvl/accordion-panel',
        ]
    ];

    $allowedBlocks = ['lvl/accordion-panel'];

    $inner = '<InnerBlocks template="' . esc_attr(wp_json_encode($innerBlocks)) . '" templateLock="false" allowedBlocks="' . esc_attr(wp_json_encode($allowedBlocks)) . '" />';

    ob_start(); ?>

			<?php echo $inner; ?>
		
    <?php

    $output = ob_get_clean();

    echo $the_block->renderSection($output, 'basic');

};

$render($block, $is_preview, $content);