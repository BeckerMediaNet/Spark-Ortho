<?php

add_filter( 'register_block_type_args', 'lvl_add_custom_block_type_attributes' );
function lvl_add_custom_block_type_attributes( $args ) {
	$args['attributes']['bs'] = array(
			'type'    => 'object',
			'default' => '',
	);

	return $args;
}