<?php if (!defined('ABSPATH')) exit;

add_action('init', 'lvl_register_logos_block_styles');
function lvl_register_logos_block_styles()
{

    register_block_style('lvl/logos', array(
        'name'         => 'alternating-bg-colors',
        'label'        => __('Alternating Background Colors', 'theme'),
    ));
}
