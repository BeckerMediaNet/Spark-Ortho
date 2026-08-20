<?php
/**
 * Block Name: Map
 *
 * This is the template that displays the map block.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$render = function ( $block, $is_preview, $content ) {

	$the_block = new Level\Block( $block );

	$locations  = $the_block->getField( 'locations' ) ?: [];
	$min_height = $the_block->getField( 'min_height' ) ?: '400';

	$the_block->addStyle( '--map-min-height:' . $min_height . 'px' );
	$the_block->addAttribute( [ 'data-locations' => json_encode( $locations ) ] );

	ob_start(); ?>

    <div class="block--map-wrapper"></div>

	<?php

	$output = ob_get_clean();

	if ( ! $is_preview ) {
		echo $the_block->renderSection( $output, 'basic' );
	} else {
		$location_list = '';
		if ( $locations ) {
			$location_list = '<ul class="small column-count-3">';
			foreach ( $locations as $location ) {
				$location_list .= '<li>' . get_the_title( $location ) . '</li>';
			}
			$location_list .= '</ul>';
		}
		echo $the_block->previewNotice( 'info', 'Interactive Map: Placeholder' . $location_list );
		$screenshot = LVL_THEME_URI_CHILD . '/blocks/map/asset/screenshot.png';
		echo '<img src="' . $screenshot . '" alt="Map Block Screenshot" style="width:100%;height:auto;">';


	}

};
$render( $block, $is_preview, $content );