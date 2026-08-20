<?php if (! defined('ABSPATH')) {
	exit;
}

//include_once get_template_directory() . '/lib/blocks/styles.php'; // if you want to include the parent theme's block styles

// Button Styles
add_action('init', 'lvl_register_button_block_styles');
function lvl_register_button_block_styles()
{

	register_block_style('core/button', array(
		'name'         => 'btn-primary',
		'label'        => __('Primary', 'theme'),
		'default'      => true
	));

	register_block_style('core/button', array(
		'name'         => 'btn-secondary',
		'label'        => __('Secondary', 'theme')
	));

	register_block_style('core/button', array(
		'name'         => 'btn-primary-outline',
		'label'        => __('Primary Outline', 'theme')
	));

	register_block_style('core/button', array(
		'name'         => 'btn-secondary-outline',
		'label'        => __('Secondary Outline', 'theme')
	));

	register_block_style('core/button', array(
		'name'         => 'btn-red',
		'label'        => __('Red', 'theme')
	));

	register_block_style('core/button', array(
		'name'         => 'btn-red-outline',
		'label'        => __('Red Outline', 'theme')
	));

	register_block_style('core/button', array(
		'name'         => 'btn-light-blue',
		'label'        => __('Light Blue', 'theme')
	));

	register_block_style('core/button', array(
		'name'         => 'btn-light-blue-outline',
		'label'        => __('Light Blue Outline', 'theme')
	));

	register_block_style('core/button', array(
		'name'         => 'btn-black',
		'label'        => __('Black', 'theme')
	));

	register_block_style('core/button', array(
		'name'         => 'btn-link',
		'label'        => __('Link', 'theme')
	));

	register_block_style('core/button', array(
		'name'         => 'btn-link-secondary',
		'label'        => __('Link Secondary', 'theme')
	));
}

add_action('init', 'lvl_register_group_block_styles');
function lvl_register_group_block_styles()
{

	register_block_style('core/group', array(
		'name'         => 'skewed-left',
		'label'        => __('Skewed Left', 'theme')
	));

	register_block_style('core/group', array(
		'name'         => 'skewed-right',
		'label'        => __('Skewed Right', 'theme')
	));
}

add_action('init', 'lvl_register_list_block_styles');
function lvl_register_list_block_styles()
{

	register_block_style('core/list', array(
		'name'         => 'dot',
		'label'        => __('Dot', 'theme'),
		'default'      => true
	));

	register_block_style('core/list', array(
		'name'         => 'dash',
		'label'        => __('Dash', 'theme'),
	));

	register_block_style('core/list', array(
		'name'         => 'circle',
		'label'        => __('Circle', 'theme'),
	));

	register_block_style('core/list', array(
		'name'         => 'square',
		'label'        => __('Square', 'theme'),
	));

	register_block_style('core/list', array(
		'name'         => 'none',
		'label'        => __('None', 'theme'),
	));

	register_block_style('core/list', array(
		'name'         => 'checkmark',
		'label'        => __('Checkmark', 'theme'),
	));
}
