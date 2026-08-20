<?php

/*
TO PLACE WIDGETS IN TEMPLATES:
	if( is_active_sidebar( 'lvl-footer-col1' ) ) dynamic_sidebar( 'lvl-footer-col1' );
*/

// add_action( 'widgets_init', 'lvl_widgets_init' );
function lvl_widgets_init(): void {

//	register_sidebar([
//		'name'          => __( 'Footer Sub Logo' ),
//		'id'            => 'lvl-footer-sub-logo',
//		'before_widget' => '<div id="%1$s" class="widget %2$s">',
//		'after_widget'  => '</div>',
//		'description'   => __( 'Widgets in this area will be shown in the footer on all pages.' ),
//		'before_title'  => '<h5>',
//		'after_title'   => '</h5>'
//	]);

	register_sidebar([
		'name'          => __( 'Footer' ),
		'id'            => 'lvl-footer-col1',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'description'   => __( 'Widgets in this area will be shown in the footer on all pages.' ),
		'before_title'  => '<h5>',
		'after_title'   => '</h5>'
	]);
	
    // single-footer
	register_sidebar([
        'name'          => __( 'Single Footer - Posts' ),

        'id'            => 'lvl-single-footer-posts',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
        'description'   => __( 'Widgets in this area will be shown in the footer on Posts.' ),
        'before_title'  => '<h3>',
        'after_title'   => '</h3>',
	]);
	
//	register_sidebar([
//		'name'          => __( 'Footer Column 2' ),
//		'id'            => 'lvl-footer-col2',
//		'before_widget' => '<div id="%1$s" class="widget %2$s">',
//		'after_widget'  => '</div>',
//		'description'   => __( 'Widgets in this area will be shown in the footer on all pages.' ),
//		'before_title'  => '<h5>',
//		'after_title'   => '</h5>'
//	]);
//
//	register_sidebar([
//		'name'          => __( 'Colophon' ),
//		'id'            => 'lvl-colophon',
//		'before_widget' => '<div id="%1$s" class="widget %2$s">',
//		'after_widget'  => '</div>',
//		'description'   => __( 'Widgets in this area will be shown in the footer on all pages.' ),
//		'before_title'  => '<h5>',
//		'after_title'   => '</h5>'
//	]);

}


// TODO: add boilerplate hook for block allowance within widgets