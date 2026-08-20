<?php if (! defined('ABSPATH')) {
	exit;
}

// on location post type save lookup the address and save the lat and long
//add_action( 'save_post', 'save_location_lat_long', 10, 3 );

function save_location_lat_long($post_id, $post, $update)
{


	if ($post->post_status === 'auto-draft') {
		return;
	}

	if ($post->post_type !== 'location') {
		return;
	}

	if (! get_field('street', $post_id) && get_field('force_geocode')) {
		update_field('lat', '', $post_id);
		update_field('long', '', $post_id);

		return;
	}

	$street     = get_field('street', $post_id);
	$city       = get_field('city', $post_id);
	$state      = get_field('state', $post_id);
	$postalcode = get_field('postalcode', $post_id);
	$country    = get_field('country', $post_id);

	$addressParts = [];

	if (! empty($street)) {
		$addressParts[] = $street;
	}
	if (! empty($city)) {
		$addressParts[] = $city;
	}
	if (! empty($state)) {
		$addressParts[] = $state;
	}
	if (! empty($postalcode)) {
		$addressParts[] = $postalcode;
	}
	if (! empty($country)) {
		$addressParts[] = $country;
	}

	$address = urlencode(implode(', ', $addressParts));

	$url = 'https://nominatim.openstreetmap.org/search?q=' . $address . '&format=geocodejson';

	$transient_key = 'location_' . esc_sql($address);
	delete_transient($transient_key);

	$location = get_transient($transient_key);

	if (! $location || get_field('force_geocode')) {
		delete_transient($transient_key);
		update_field('force_geocode', false, $post_id);

		$response = wp_remote_get($url);
		if (is_wp_error($response)) {
			$error = $response->get_error_message();

			return;
		}

		$body     = wp_remote_retrieve_body($response);
		$location = json_decode($body);

		set_transient($transient_key, $location, 60 * 60 * 24 * 30);

		if (empty($location)) {
			return;
		}
	}
	$lat  = $location->features[0]->geometry->coordinates[1];
	$long = $location->features[0]->geometry->coordinates[0];

	update_field('lat', $lat, $post_id);
	update_field('long', $long, $post_id);
}
