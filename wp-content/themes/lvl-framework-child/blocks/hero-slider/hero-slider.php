<?php if (!defined('ABSPATH')) exit;

$render = function ($block, $is_preview, $content) {
    if (!$block) return;

    $the_block = new Level\Block($block);

    if ($is_preview) {
        $the_block->addClass('--preview');
    }

    $innerBlocks = [
        [
            'lvl/hero-slide',
        ],
    ];

    $allowedBlocks = ['lvl/hero-slide'];

    $inner = '<InnerBlocks template="' . esc_attr(wp_json_encode($innerBlocks)) . '" templateLock="false" allowedBlocks="' . esc_attr(wp_json_encode($allowedBlocks)) . '" />';

    ob_start(); ?>

    <div class="slides">
        
        <?php echo $inner; ?>

    </div>

    <?php

    $output = ob_get_clean();

    echo $the_block->renderSection($output, 'basic');

};

$render($block ?? false, $is_preview ?? false, $content ?? false);