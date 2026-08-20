<?php if (!defined('ABSPATH')) exit;

add_action( 'init', 'lvl_register_media_text_block_styles' );
function lvl_register_media_text_block_styles() {

	register_block_style( 'lvl/media-text', array(
		'name'         => 'pattern-1',
		'label'        => __( '3-Points', 'theme' ),
	));
	register_block_style( 'lvl/media-text', array(
		'name'         => 'pattern-8',
		'label'        => __( '3-Points Flipped', 'theme' ),
	));

	register_block_style( 'lvl/media-text', array(
		'name'         => 'pattern-2',
		'label'        => __( 'A Stacked', 'theme' ),
	));

	register_block_style( 'lvl/media-text', array(
		'name'         => 'pattern-3',
		'label'        => __( 'A Stacked/Flipped', 'theme' ),
	));

	register_block_style( 'lvl/media-text', array(
		'name'         => 'pattern-4',
		'label'        => __( 'Flipping A', 'theme' ),
	));

	register_block_style( 'lvl/media-text', array(
		'name'         => 'pattern-5',
		'label'        => __( 'Thin Thick Fade', 'theme' ),
	));
	register_block_style( 'lvl/media-text', array(
		'name'         => 'pattern-9',
		'label'        => __( 'Double Thin', 'theme' ),
	));

	register_block_style( 'lvl/media-text', array(
		'name'         => 'pattern-6',
		'label'        => __( 'Chevron', 'theme' ),
	));

	register_block_style( 'lvl/media-text', array(
		'name'         => 'pattern-7',
		'label'        => __( 'Uniform Brick', 'theme' ),
	));
}