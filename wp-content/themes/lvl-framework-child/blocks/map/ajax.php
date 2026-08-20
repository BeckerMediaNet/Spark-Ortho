<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'wp_ajax_block_map_locations_get', 'block_map_locations_get' );
add_action( 'wp_ajax_nopriv_block_map_locations_get', 'block_map_locations_get' );
function block_map_locations_get() {
	$preset_locations = $_POST['preset_locations'] ?: null;

	$args = [
		'post_type'      => 'location',
		'posts_per_page' => - 1,
		'post_status'    => 'publish',
		'fields'         => 'ids',
	];
	if ( $preset_locations ) {
		$args['post__in'] = explode( ',', $preset_locations );
	}

	$location_query = new WP_Query( $args );
	$locations      = $location_query->posts;
	$locations      = array_map( function ( $location ) {
		$lat  = get_field( 'lat', $location );
		$long = get_field( 'long', $location );

		$title       = ( get_field( 'display_title' ) ?: get_the_title( $location ) );

		$street	 = get_field( 'street', $location );
		$city	 = get_field( 'city', $location );
		$state	 = get_field( 'state', $location );
		$postalcode = get_field( 'postalcode', $location );
		$country	= get_field( 'country', $location );
		$get_directions = get_field( 'get_directions_link', $location );

//		$addressParts = [];
//
//		if ( ! empty( $street ) ) {
//			$addressParts[] = $street;
//		}
//		if ( ! empty( $city ) ) {
//			$addressParts[] = $city;
//		}
//		if ( ! empty( $state ) ) {
//			$addressParts[] = $state;
//		}
//		if ( ! empty( $postalcode ) ) {
//			$addressParts[] = $postalcode;
//		}
//		if ( ! empty( $country ) ) {
//			$addressParts[] = $country;
//		}
//
//		$address = implode( ', ', $addressParts );

		$display_address     = get_field( 'display_address', $location ) ?? '';
		$phone       = get_field( 'phone', $location );
//		$map_address = strip_tags( $address );
//		$map_address = str_replace( [ "\r\n", "\r", "\n" ], ' ', $map_address );
//		$map_address = preg_replace( '/\s+/', ' ', $map_address );

		return [
			'location' => [
				'lat'        => $lat,
				'lng'        => $long,
				'title'      => $title,
				'phone'      => $phone,
				'address'    => $display_address,
//				'mapAddress' => $map_address,
				'getDirections' => $get_directions,
				'linkedin'   => get_field( 'linkedin', $location ) ?: '',
			],
		];
	}, $locations );

	echo json_encode( $locations );

	die();
}