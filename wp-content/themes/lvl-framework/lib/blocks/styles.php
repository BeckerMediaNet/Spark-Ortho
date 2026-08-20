<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//add_action( 'init', 'lvl_register_image_block_styles' );
//
//function lvl_register_image_block_styles() {
//	register_block_style( 'core/image', array(
//		'name'         => 'accent-drop-shadow',
//		'label'        => __( 'Drop Shadow', 'theme' ),
//	) );
//
//	// column
//	register_block_style( 'core/column', array(
//		'name'         => 'accent-drop-shadow',
//		'label'        => __( 'Drop Shadow', 'theme' ),
//		'inline_style' => '.wp-block-column.accent-drop-shadow { box-shadow: var(--bs-box-shadow-sm); }',
//	) );
//}

// read theme.json and look at the color palatter to register bootstrap colors as button styles

add_action( 'init', 'lvl_register_button_block_styles' );

function lvl_register_button_block_styles() {
	// get theme colors
	$theme = wp_get_theme();
	$theme_json = get_stylesheet_directory() . '/theme.json';
	$theme_json = file_get_contents( $theme_json );
	$theme_json = json_decode( $theme_json, true );

	$colors = $theme_json['settings']['color']['palette']??[];

	$btn_styles = [
		'bs-primary'   => 'btn-primary',
		'bs-secondary' => 'btn-secondary',
		'bs-success'   => 'btn-success',
		'bs-danger'    => 'btn-danger',
		'bs-warning'   => 'btn-warning',
		'bs-info'      => 'btn-info',
		'bs-light'     => 'btn-light',
		'bs-dark'      => 'btn-dark',
		'bs-link'      => 'btn-link',
	];

	foreach ( $colors as $color ) {
		if ( ! str_starts_with( $color['slug'], 'bs-' ) ) {
			continue;
		}

		if( ! isset( $btn_styles[ $color['slug'] ] ) ) {
			continue;
		}

		// fill
		register_block_style( 'core/button', array(
			'name'         => $color['slug'],
			'label'        => $color['name'],
			'inline_style' => '.wp-block-button.' . $color['slug'] . ' { background-color: ' . $color['color'] . '; }',
		) );

		// outline
		$outline_slug = str_replace('bs-','bs-outline-', $color['slug']);
		register_block_style( 'core/button', array(
			'name'         => $outline_slug,
			'label'        => $color['name'] . ' Outline',
			'inline_style' => '.wp-block-button.' . $color['slug'] . ' { background-color: transparent; border-color: ' . $color['color'] . '; color: ' . $color['color'] . '; }',
		) );
	}
}