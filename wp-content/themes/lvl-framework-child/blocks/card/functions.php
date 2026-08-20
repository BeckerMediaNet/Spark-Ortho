<?php if( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'lvl_register_card_block_styles' );
function lvl_register_card_block_styles() {

	register_block_style( 'lvl/card', array(
		'name'         => 'striped',
		'label'        => __( 'Striped', 'theme' ),
		// 'default'      => true
	));
}