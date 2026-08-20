<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'lvl_register_accordion_block_styles' );

function lvl_register_accordion_block_styles() {
	register_block_style( 'lvl/accordion', array(
			'name'  => 'inline-block',
			'label' => __( 'Inline Block', 'theme' ),
	) );

	register_block_style( 'lvl/accordion', array(
			'name'  => 'boxed',
			'label' => __( 'Boxed', 'theme' ),
	) );

}