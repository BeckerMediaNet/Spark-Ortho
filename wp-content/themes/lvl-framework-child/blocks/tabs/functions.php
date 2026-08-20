<?php if (!defined('ABSPATH')) exit;

add_action('init', 'lvl_register_tabs_block_styles');
function lvl_register_tabs_block_styles()
{
    register_block_style('lvl/tabs', array(
        'name' => 'horizontal-tabs',
        'label' => __('Horizontal Tabs', 'theme'),
    ));
}
