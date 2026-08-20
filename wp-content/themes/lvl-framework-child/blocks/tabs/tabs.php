<?php if (!defined('ABSPATH')) exit;

$render = function ($block, $is_preview, $content) {

    $the_block = new Level\Block($block);

    $innerBlocks = [
        [
            'lvl/tab-panel',
        ]
    ];

    $allowedBlocks = ['lvl/tab-panel'];

    $inner = '<InnerBlocks template="' . esc_attr(wp_json_encode($innerBlocks)) . '" templateLock="false" allowedBlocks="' . esc_attr(wp_json_encode($allowedBlocks)) . '" />';

    ob_start(); ?>

    <div class="row g-0">
        <div class="nav-tabs-wrapper col-12 col-sm-12 col-md-3 col-lg-3">
            <ul class="nav nav-tabs"></ul>
        </div>

        <div class="tab-content-wrapper col-12 col-sm-12 col-md-9 col-lg-9">
            <div class="tab-content">
                <?php echo $inner; ?>
            </div>
        </div>
    </div>




<?php

    $output = ob_get_clean();

    echo $the_block->renderSection($output, 'basic');
};

$render($block, $is_preview, $content);
