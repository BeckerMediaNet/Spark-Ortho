<?php if (!defined('ABSPATH')) exit;

$render = function ($block, $is_preview, $content) {

    $the_block = new Level\Block($block);

    $prefix = $the_block->getField('prefix');
    $start = $the_block->getField('count_from');
    $end = $the_block->getField('count_to');
    $suffix = $the_block->getField('suffix');
    $color = $the_block->getField('color');
    $prefix_suffix_color = $the_block->getField('prefix_suffix_color');

    $the_block->addAttribute(['data-countUp' => 'true'], 0);
    $the_block->addAttribute(['data-prefix' => $prefix], 0);
    $the_block->addAttribute(['data-suffix' => $suffix], 0);
    $the_block->addAttribute(['data-start' => $start], 0);
	$the_block->addAttribute(['data-value' => $end], 0);

    if ($color) {
        $the_block->addStyle('--stat-color:' . $color, 'section');
    }
    if ($prefix_suffix_color) {
        $the_block->addStyle('--accent-color:' . $prefix_suffix_color, 'section');
    }


    ob_start(); ?>

    <?php echo $prefix . $end . $suffix; ?>

    <?php

    $output = ob_get_clean();

    echo $the_block->renderSection($output, 'full');

};

$render($block, $is_preview, $content);