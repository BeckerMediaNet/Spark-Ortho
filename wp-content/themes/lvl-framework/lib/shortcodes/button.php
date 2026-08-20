<?php if( ! defined('ABSPATH') ) exit;
// [btn text="Contact Us" url="/contact-us/" color="primary" size="" ghost="false" block="false" xclass=""]
/**
 * @param $atts
 * @param $content
 * @return string
 *
 * @example [btn text="Contact Us" url="/contact-us/" color="primary" size="" ghost="false" block="false" xclass=""]
 * @note This shortcode is used to display a button. Sizes are lg, sm, and default. Colors are primary, secondary, info, light, and dark.
 */
function lvl_btn_shortcode_handler($atts, $content = null ) {

    extract(shortcode_atts([
        'text' 		=> 'Contact Us',
        'url' 		=> '/contact-us/',
        'color' 	=> 'primary',
        'modal' 	=> '',
        'size' 		=> '',
        'ghost' 	=> 'false',
        'block' 	=> 'false',
        'xclass' 	=> ''
    ], $atts));

    switch( $size ){
        case 'lg':
        case 'large':
            $size = 'btn-lg ';
            break;
        case 'sm':
        case 'small':
            $size = 'btn-sm ';
            break;
        default:
            $size = '';
            break;
    }

    switch( $color ){
        case 'primary':
        case 'blue':
            $style = ( 'true' === $ghost ) ? 'btn-primary-outline ' : 'btn-primary ';
            break;
        case 'secondary':
        case 'gray':
		case 'grey':
				$style = ( 'true' === $ghost ) ? 'btn-outline-secondary ' : 'btn-secondary ';
            break;
        case 'info':
			$style = ( 'true' === $ghost ) ? 'btn-outline-info ' : 'btn-info ';
            break;
        case 'light':
        case 'white':
			$style = ( 'true' === $ghost ) ? 'btn-outline-light ' : 'btn-light ';
            break;
        case 'black':
        case 'dark':
			$style = ( 'true' === $ghost ) ? 'btn-outline-dark ' : 'btn-dark ';
            break;
        default:
			$style = ( 'true' === $ghost ) ? 'btn-primary-outline ' : 'btn-primary ';
            break;
    }

    return '<a ' . ( $modal ? 'data-bs-toggle="modal" data-bs-target="#' . $modal . '"' : 'href="' . esc_url( $url ) . '" ' ) . ' class="btn ' . $size . $style . ( 'true' === $block ? 'btn-block ' : '' ) . ( '' != $xclass ? ' ' . $xclass : '' ) . '">' . __( $text ) . '</a>';

}
add_shortcode( 'btn', 'lvl_btn_shortcode_handler' );