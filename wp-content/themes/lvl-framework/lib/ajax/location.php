<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// ajax function priv only
add_action( 'wp_ajax_lvl_get_location_lat_long', 'lvl_get_location_lat_long' );
add_action( 'wp_ajax_nopriv_lvl_get_location_lat_long', 'lvl_get_location_lat_long' );

function lvl_get_location_lat_long() {

	$street     = $_POST['street'] ?? false;
	$city       = $_POST['city'] ?? false;
	$state      = $_POST['state'] ?? false;
	$postalcode = $_POST['postalcode'] ?? false;
	$country    = $_POST['country'] ?? false;


	if ( ! $street ) {
		echo json_encode( [
			'status' => [
				'status' => 'error',
				'error'  => 'Street is required',
			],
		] );

		die();
	}

	$addressParts = [];

	if ( ! empty( $street ) ) {
		$addressParts[] = $street;
	}
	if ( ! empty( $city ) ) {
		$addressParts[] = $city;
	}
	if ( ! empty( $state ) ) {
		$addressParts[] = $state;
	}
	if ( ! empty( $postalcode ) ) {
		$addressParts[] = $postalcode;
	}
	if ( ! empty( $country ) ) {
		$addressParts[] = $country;
	}

	$address = urlencode( implode( ', ', $addressParts ) );

	$url = 'https://nominatim.openstreetmap.org/search?q=' . $address . '&format=geocodejson';

	$transient_key = 'location_' . esc_sql( $address );

//	delete_transient( $transient_key ); // TODO: remove transients?
	$location = get_transient( $transient_key );

	if ( ! $location ) {
		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			$error = $response->get_error_message();

			echo json_encode( [
				'status' => [
					'status' => 'error',
					'error'  => $error,
				],
			] );

			die();
		}

		$body     = wp_remote_retrieve_body( $response );
		$location = json_decode( $body );

		if ( empty( $location ) || empty( $location->features )) {
			echo json_encode( [
				'url'           => $url,
				'lat'           => '',
				'long'          => '',
				'transient_key' => $transient_key,
				'status' => [
					'status' => 'error',
					'error'  => 'No location found',
				],
				'response'      => $location,
			] );

			die();
		}

		set_transient( $transient_key, $location, 60 * 60 * 24 * 30 );

	}
	$lat  = $location->features[0]->geometry->coordinates[1];
	$long = $location->features[0]->geometry->coordinates[0];

	echo json_encode( [
		'url'           => $url,
		'lat'           => $lat,
		'long'          => $long,
		'transient_key' => $transient_key,
		'status'        => [
			'status'  => 'success',
			'message' => 'Location found',
		],
		'response'      => $location,
	] );

	die();
}