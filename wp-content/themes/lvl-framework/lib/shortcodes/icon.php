<?php if( ! defined('ABSPATH') ) exit;
// [icon icon="chevron-down" url="" size="md" xclass=""]
/**
 * @param $atts
 * @param $content
 * @return string
 *
 * @example [icon icon="chevron-down" url="" size="md" xclass=""]
 * @note This shortcode is used to display an icon from the SVG sprite sheet. Sizes are xs, sm, md, lg, and xl.
 */
function lvl_icon_shortcode($atts, $content = null ): string
{

    extract( shortcode_atts( [
        'icon' 		=> 'chevron-down',
        'url' 		=> '',
        'size' 		=> 'md',
        'xclass' 	=> ''
    ], $atts ) );

    $svg = '<svg preserveAspectRatio="xMidYMid slice" class="icon icon-' . $icon . ' icon-' . $size . ( $xclass ? ' ' . $xclass : '' ) . '" aria-hidden="true"><use xlink:href="#' . $icon . '"></use></svg>';


    if($url) {
        return '<a href="' . $url . '" class="icon-link">' . $svg . '</a>';
    } else {
        return $svg;
    }

//    switch ( $size ) {
//        case 'xs':
//            $size_class = 'size-xs';
//            break;
//        case 'sm':
//            $size_class = 'size-sm';
//            break;
//        case 'md':
//            $size_class = 'size-md';
//            break;
//        case 'lg':
//            $size_class = 'size-lg';
//            break;
//        case 'xl':
//            $size_class = 'size-xl';
//            break;
//    }
//
//	$lvl_fetch_icon = function ( $desired_icon ) {
//		$icons = get_field( 'icon_svgs', 'options' );
//		if( $icons ) {
//			foreach( $icons as $icon ) {
//				if( empty( $icon ) ) continue;
//				if( empty( $icon['url'] ) ) continue;
//				if( empty( $icon['title'] ) ) continue;
//				if( empty( $icon['mime_type'] ) ) continue;
//				if( 'image/svg+xml' != $icon['mime_type'] ) continue;
//				if( $desired_icon == $icon['title'] ) return $icon;
//			}
//		}
//		return null;
//	};
//
//	$icon_svg = $lvl_fetch_icon( $icon );
//
//	if( $icon_svg ) :
//
//		$icon_svg_output = file_get_contents( str_replace( 'tidalbasinstg.wpengine', 'tidalbasinstg:webmechanix@tidalbasinstg.wpengine', $icon_svg['url'] ) );
//
//		$content = ( $url ? '<a href="' . $url . '" class="shortcoded-icon-link">' : '' )
//			. '<div class="shortcoded-icon ' . ( $size_class ? ' ' . $size_class : '' ) . ( $xclass ? ' ' . $xclass : '' )  . '">'
//				. $icon_svg_output
//			. '</div>'
//		. ( $url ? '</a>' : '' );
//
//		return $content;
//
//	endif;
//
//	return $content;

}
add_shortcode( 'icon', 'lvl_icon_shortcode' );