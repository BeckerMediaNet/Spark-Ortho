<?php if (!defined('ABSPATH')) {
	exit;
}

/**
 * Override parent theme's AJAX handler to use Google Maps API instead of OpenStreetMap
 */
// Remove parent theme's AJAX handlers
remove_action('wp_ajax_lvl_get_location_lat_long', 'lvl_get_location_lat_long');
remove_action('wp_ajax_nopriv_lvl_get_location_lat_long', 'lvl_get_location_lat_long');

// Add our own AJAX handlers that use Google Maps API
add_action('wp_ajax_lvl_get_location_lat_long', 'lvl_child_get_location_lat_long');
add_action('wp_ajax_nopriv_lvl_get_location_lat_long', 'lvl_child_get_location_lat_long');

function lvl_child_get_location_lat_long()
{
	error_log("AJAX geocoding request received");

	$street = $_POST['street'] ?? '';
	$city = $_POST['city'] ?? '';
	$state = $_POST['state'] ?? '';
	$postalcode = $_POST['postalcode'] ?? '';
	$country = $_POST['country'] ?? '';

	// Build address from components
	$addressParts = array_filter([
		$street,
		$city,
		$state,
		$postalcode,
		$country
	]);

	if (empty($addressParts)) {
		error_log("AJAX geocoding: No address components provided");
		wp_send_json([
			'lat' => '',
			'long' => '',
			'status' => [
				'status' => 'error',
				'message' => 'No address provided',
			],
		]);
	}

	$address = implode(', ', $addressParts);
	$api_key = defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : '';

	if (empty($api_key)) {
		error_log("AJAX geocoding: Google Maps API key not defined");
		wp_send_json([
			'lat' => '',
			'long' => '',
			'status' => [
				'status' => 'error',
				'message' => 'Google Maps API key not configured',
			],
		]);
	}

	$encoded_address = urlencode($address);
	$url = "https://maps.googleapis.com/maps/api/geocode/json?address={$encoded_address}&key={$api_key}";

	error_log("AJAX geocoding address: $address");

	$response = wp_remote_get($url, [
		'timeout' => 30,
	]);

	if (is_wp_error($response)) {
		error_log("AJAX geocoding: API request failed - " . $response->get_error_message());
		wp_send_json([
			'lat' => '',
			'long' => '',
			'status' => [
				'status' => 'error',
				'message' => $response->get_error_message(),
			],
		]);
	}

	$body = wp_remote_retrieve_body($response);
	$data = json_decode($body, true);

	if ($data['status'] === 'OK' && !empty($data['results'])) {
		$lat = $data['results'][0]['geometry']['location']['lat'];
		$lng = $data['results'][0]['geometry']['location']['lng'];

		error_log("AJAX geocoding success: Lat $lat, Lng $lng");

		wp_send_json([
			'lat' => $lat,
			'long' => $lng,
			'status' => [
				'status' => 'success',
				'message' => 'Location found',
			],
		]);
	} else {
		$error_message = isset($data['error_message']) ? $data['error_message'] : (isset($data['status']) ? $data['status'] : 'Unknown error');
		error_log("AJAX geocoding failed: Status {$data['status']}, Error: $error_message");

		wp_send_json([
			'lat' => '',
			'long' => '',
			'status' => [
				'status' => 'error',
				'message' => $error_message,
			],
		]);
	}
}

/**
 * @param string $path
 *
 * Check to see if a location exists.
 *
 * @return boolean
 */
function is_valid_location($path)
{
	$location = get_page_by_path($path, OBJECT, 'location');
	return $location !== null;
}

/**
 * @param string $path
 *
 * Check to see if a location service exists.
 *
 * @return boolean
 */
function is_valid_location_service($slug)
{
	$valid_services = get_posts([
		'post_type' => 'location_service',
		'post_status' => 'publish',
		'posts_per_page' => -1
	]);

	$valid_services = array_map(function ($service) {
		return $service->post_name;
	}, $valid_services);

	return in_array($slug, $valid_services);
}

// Parse the request to determine if the URL is a location or location service
add_action('parse_request', 'handle_custom_location_url');
function handle_custom_location_url($wp)
{

	if (is_admin()) {
		return;
	}

	if (!empty($wp->query_vars['post_type']) && $wp->query_vars['post_type'] === 'location') {
		$path_parts = explode('/', trim($wp->request, '/'));
		$last_part = end($path_parts);

		$valid_location = is_valid_location(str_replace('locations/', '', $wp->request));
		$valid_service = is_valid_location_service($last_part);

		if (!$valid_location && !$valid_service) {
			$wp->set_query_var('location', false);
		} else if (!$valid_location && is_valid_location_service($last_part)) {
			$location = implode('/', array_slice($path_parts, 1, -1));
			$wp->set_query_var('location', $location);
			$wp->set_query_var('location_service', $last_part);

			add_filter('wpseo_canonical', function ($canonical) use ($wp) {
				return home_url() . '/' . $wp->request . '/';
			}, 10, 1);
		} else {
			$wp->set_query_var('location', implode('/', array_slice($path_parts, 1)));
		}
	}
}

// Handle pagination for locations archive
add_action('pre_get_posts', 'handle_locations_archive_pagination');
function handle_locations_archive_pagination($query)
{
	if (! is_admin() && $query->is_main_query() && is_post_type_archive('location')) {

		// Check if there's a ZIP search - if so, disable pagination for proximity sorting
		$zip = isset($_GET['zip']) ? sanitize_text_field($_GET['zip']) : '';

		if (!empty($zip)) {
			if ($query->is_paged()) {
				wp_redirect(get_post_type_archive_link('location'), 301);
				exit;
			}
			$query->set('posts_per_page', -1);
			$query->set('paged', 1);
		} else {
			$query->set('posts_per_page', 12);
		}
	}
}

// Prevent WordPress from redirecting to the location post type
add_filter('redirect_canonical', 'prevent_location_redirect', 10, 2);
function prevent_location_redirect($redirect_url, $requested_url)
{
	if (get_query_var('post_type') === 'location') {
		return $requested_url;
	}
	return $redirect_url;
}

// Load the single-location.php template if query is a location
add_filter('template_include', 'load_single_location_template');
function load_single_location_template($template)
{

	if (get_query_var('post_type') === 'location' && get_query_var('location')) {

		$is_location = get_page_by_path(get_query_var('location'), OBJECT, 'location');

		if ($is_location) {
			$location_template = locate_template('single-location.php');

			if (!empty($location_template)) {
				return $location_template;
			}
		}
	}

	return $template;
}

// Prevent WordPress from returning a 404 for location pages
add_action('template_redirect', 'lvl_prevent_location_404', 10);
function lvl_prevent_location_404()
{
	global $wp_query;

	if (is_admin()) {
		return;
	}

	if (get_query_var('post_type') === 'location' && get_query_var('location')) {

		$location = get_page_by_path(get_query_var('location'), OBJECT, 'location');

		if ($location) {
			$wp_query->is_404 = false;
			status_header(200);
		}
	}
}

/**
 * Get coordinates for a zip code using Google Geocoding API
 * @param string $zip_code
 * @return array|false Array with 'lat' and 'lng' keys or false on failure
 */
function lvl_get_zip_coordinates($zip_code)
{
	$api_key = defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : '';

	if (empty($api_key)) {
		error_log("ZIP Coordinates: Google Maps API key not defined for ZIP: $zip_code");
		return false;
	}

	$cache_key = 'zip_coords_' . $zip_code;
	$cached_coords = get_transient($cache_key);

	if ($cached_coords !== false) {
		error_log("ZIP Coordinates: Using cached coordinates for ZIP $zip_code - Lat: {$cached_coords['lat']}, Lng: {$cached_coords['lng']}");
		return $cached_coords;
	}

	error_log("ZIP Coordinates: Making API request for ZIP: $zip_code");

	// Make API request
	$url = "https://maps.googleapis.com/maps/api/geocode/json?address={$zip_code}&key={$api_key}";
	$response = wp_remote_get($url, [
		'timeout' => 30
	]);

	if (is_wp_error($response)) {
		error_log("ZIP Coordinates: API request failed for ZIP $zip_code - " . $response->get_error_message());
		return false;
	}

	$data = json_decode(wp_remote_retrieve_body($response), true);
	error_log("ZIP Coordinates: API response for ZIP $zip_code - Status: {$data['status']}");

	if ($data['status'] === 'OK' && !empty($data['results'])) {
		$coords = [
			'lat' => $data['results'][0]['geometry']['location']['lat'],
			'lng' => $data['results'][0]['geometry']['location']['lng']
		];

		// Cache for 24 hours
		set_transient($cache_key, $coords, 24 * HOUR_IN_SECONDS);
		error_log("ZIP Coordinates: Successfully geocoded ZIP $zip_code - Lat: {$coords['lat']}, Lng: {$coords['lng']}");

		return $coords;
	}

	$error_message = isset($data['error_message']) ? $data['error_message'] : (isset($data['status']) ? $data['status'] : 'Unknown error');
	error_log("ZIP Coordinates: Failed to geocode ZIP $zip_code - Status: {$data['status']}, Error: $error_message");

	return false;
}

/**
 * Calculate distance between two coordinates using Haversine formula
 * @param float $lat1 Latitude of first point
 * @param float $lng1 Longitude of first point  
 * @param float $lat2 Latitude of second point
 * @param float $lng2 Longitude of second point
 * @return float Distance in miles
 */
function lvl_calculate_distance($lat1, $lng1, $lat2, $lng2)
{
	$earth_radius = 3959; // Earth's radius in miles

	$lat1_rad = deg2rad($lat1);
	$lng1_rad = deg2rad($lng1);
	$lat2_rad = deg2rad($lat2);
	$lng2_rad = deg2rad($lng2);

	$delta_lat = $lat2_rad - $lat1_rad;
	$delta_lng = $lng2_rad - $lng1_rad;

	$a = sin($delta_lat / 2) * sin($delta_lat / 2) +
		cos($lat1_rad) * cos($lat2_rad) *
		sin($delta_lng / 2) * sin($delta_lng / 2);

	$c = 2 * atan2(sqrt($a), sqrt(1 - $a));

	return $earth_radius * $c;
}

/**
 * Geocode and store coordinates for a location
 * Helper function to populate lat/lng for existing locations
 * @param int $post_id Location post ID
 */
function lvl_geocode_location($post_id)
{
	// Try multiple address sources in order of preference
	$address_sources = [
		'display_address',
		// Fallback: construct address from components
		'constructed'
	];

	$address = '';

	foreach ($address_sources as $source) {
		if ($source === 'display_address') {
			$address = get_field('display_address', $post_id);
			if (!empty($address)) {
				break;
			}
		} elseif ($source === 'constructed') {
			// Construct address from individual fields
			$street = get_field('street', $post_id);
			$city = get_field('city', $post_id);
			$state = get_field('state', $post_id);
			$postalcode = get_field('postalcode', $post_id);

			if (!empty($street) && !empty($city)) {
				$address_parts = array_filter([$street, $city, $state, $postalcode]);
				$address = implode(', ', $address_parts);
				break;
			}
		}
	}

	if (empty($address)) {
		error_log("No address found for location ID: $post_id");
		return false;
	}

	$api_key = defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : '';

	if (empty($api_key)) {
		error_log("Google Maps API key not defined. Please add GOOGLE_MAPS_API_KEY to wp-config.php");
		return false;
	}

	// Clean the address
	$clean_address = wp_strip_all_tags($address);
	$encoded_address = urlencode($clean_address);
	$url = "https://maps.googleapis.com/maps/api/geocode/json?address={$encoded_address}&key={$api_key}";

	error_log("Geocoding address for location ID $post_id: $clean_address");

	$response = wp_remote_get($url, [
		'timeout' => 30,
		'headers' => [
			'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url()
		]
	]);

	if (is_wp_error($response)) {
		error_log("Geocoding API request failed: " . $response->get_error_message());
		return false;
	}

	$body = wp_remote_retrieve_body($response);
	$data = json_decode($body, true);

	if ($data['status'] === 'OK' && !empty($data['results'])) {
		$lat = $data['results'][0]['geometry']['location']['lat'];
		$lng = $data['results'][0]['geometry']['location']['lng'];

		error_log("Geocoding successful - About to update ACF fields for location ID $post_id");
		error_log("Lat value: $lat, Lng value: $lng");

		// Update the ACF fields
		$lat_updated = update_field('lat', $lat, $post_id);
		$lng_updated = update_field('long', $lng, $post_id);

		error_log("ACF update results - lat: " . ($lat_updated ? 'success' : 'failed') . ", lng: " . ($lng_updated ? 'success' : 'failed'));

		// Verify the values were saved
		$saved_lat = get_field('lat', $post_id);
		$saved_lng = get_field('long', $post_id);
		error_log("Verification - Saved lat: $saved_lat, Saved lng: $saved_lng");

		return ['lat' => $lat, 'lng' => $lng];
	} else {
		$error_message = isset($data['error_message']) ? $data['error_message'] : (isset($data['status']) ? $data['status'] : 'Unknown error');
		error_log("Geocoding failed for location ID $post_id - Status: {$data['status']}, Error: $error_message");
		error_log("Full API response: " . print_r($data, true));
		return false;
	}
}

/**
 * Render a location card with ACF details and icons
 * @param int $post_id
 * @param float $distance Optional distance in miles to display
 */
function lvl_render_location_card($post_id, $distance = null)
{
	$fields = get_fields($post_id);
?>
	<div class="location-card">
		<div class="location-details">
			<h3 class="location-card-header mb-3">
				<?php echo esc_html(get_the_title($post_id)); ?>
				<?php if ($distance !== null): ?>
					<span class="location-distance"><?php echo number_format($distance, 1); ?> miles</span>
				<?php endif; ?>
			</h3>
			<?php if (!empty($fields['display_address'])): ?>
				<div class="display-address">
					<span class="icon icon-address" aria-hidden="true">
						<svg fill="none" height="20" viewBox="0 0 16 20" width="16" xmlns="http://www.w3.org/2000/svg">
							<path d="m8 10c.55 0 1.021-.196 1.413-.588.39133-.39133.587-.862.587-1.412s-.19567-1.021-.587-1.413c-.392-.39133-.863-.587-1.413-.587s-1.02067.19567-1.412.587c-.392.392-.588.863-.588 1.413s.196 1.02067.588 1.412c.39133.392.862.588 1.412.588zm0 9.625c-.13333 0-.26667-.025-.4-.075s-.25-.1167-.35-.2c-2.43333-2.15-4.25-4.1457-5.45-5.987-1.2-1.842-1.8-3.563-1.8-5.163 0-2.5.804333-4.49167 2.413-5.975 1.608-1.483333 3.47033-2.225 5.587-2.225 2.1167 0 3.979.741667 5.587 2.225 1.6087 1.48333 2.413 3.475 2.413 5.975 0 1.6-.6 3.321-1.8 5.163-1.2 1.8413-3.0167 3.837-5.45 5.987-.1.0833-.21667.15-.35.2s-.26667.075-.4.075z" fill="#00436c" />
						</svg>
					</span>
					<a href="#" class="map-link" data-address="<?php echo esc_attr(wp_strip_all_tags($fields['display_address'])); ?>" target="_blank" rel="noopener">
						<?php echo nl2br(esc_html(wp_strip_all_tags($fields['display_address']))); ?>
					</a>
				</div>
			<?php endif; ?>
			<?php if (!empty($fields['phone']) && !empty($fields['phone']['url'])): ?>
				<div class="location-phone">
					<span class="icon icon-phone" aria-hidden="true">
						<svg fill="none" height="18" viewBox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg">
							<path d="m17.01 12.38c-1.23 0-2.42-.2-3.53-.56-.35-.12-.74-.03-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99h-3.46c-.54 0-1.19.24-1.19.99 0 9.29 7.73 17.01 17.01 17.01.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z" fill="#00436c" />
						</svg>
					</span>
					<a href="<?php echo esc_url($fields['phone']['url']); ?>" <?php if (!empty($fields['phone']['target'])): ?>target="<?php echo esc_attr($fields['phone']['target']); ?>" <?php endif; ?>>
						<?php echo esc_html($fields['phone']['title'] ?: $fields['phone']['url']); ?>
					</a>
				</div>
			<?php endif; ?>
			<?php if (!empty($fields['location_times'])): ?>
				<div class="location-hours">
					<span class="icon icon-hours" aria-hidden="true">
						<svg fill="none" height="22" viewBox="0 0 20 22" width="20" xmlns="http://www.w3.org/2000/svg">
							<path d="m18 2h-1v-2h-2v2h-10v-2h-2v2h-1c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2v-16c0-1.1-.9-2-2-2zm0 18h-16v-13h16z" fill="#00436c" />
						</svg>
					</span>
					<div>
						<?php foreach ($fields['location_times'] as $time_row): ?>
							<div class="location-time-row">
								<?php if (!empty($time_row['location_days'])): ?>
									<span><?php echo nl2br(esc_html(wp_strip_all_tags($time_row['location_days']))); ?></span>
								<?php endif; ?>
								<?php if (!empty($time_row['location_days']) && !empty($time_row['location_hours'])): ?><br><?php endif; ?>
								<?php if (!empty($time_row['location_hours'])): ?>
									<span><?php echo nl2br(esc_html(wp_strip_all_tags($time_row['location_hours']))); ?></span>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
			<div class="location-services">
				<span class="services-offered-label">Services Offered:</span>
				</br>
				<?php
				$terms = get_the_terms($post_id, 'location_service');
				if ($terms && !is_wp_error($terms)) {
					foreach ($terms as $term) {
						echo '<span class="service-pill">' . esc_html($term->name) . '</span> ';
					}
				}
				?>
			</div>
			<?php
			$language_terms = get_the_terms($post_id, 'location_language');
			if ($language_terms && !is_wp_error($language_terms)) : ?>
			<div class="location-languages mt-2">
				<span class="languages-spoken-label">Languages Spoken:</span>
				</br>
				<?php foreach ($language_terms as $lang_term) {
					echo '<span class="service-pill">' . esc_html($lang_term->name) . '</span> ';
				} ?>
			</div>
			<?php endif; ?>
			<div class="location-link-wrapper d-flex justify-content-end pt-4 mt-auto w-100">
				<a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="btn btn-link-secondary">Visit Page</a>
			</div>
		</div>
	</div>
<?php
}

// Hook to trigger geocoding when force_geocode field is checked
add_filter('acf/update_value/name=force_geocode', 'lvl_trigger_geocoding_on_checkbox', 10, 3);
function lvl_trigger_geocoding_on_checkbox($value, $post_id, $field)
{
	// Only proceed if checkbox is checked (value is 1 or true)
	if ($value && get_post_type($post_id) === 'location') {
		// Schedule geocoding to run after ACF finishes saving
		add_action('acf/save_post', function () use ($post_id) {
			lvl_perform_geocoding($post_id);
		}, 20);

		// Return false to uncheck the checkbox
		return false;
	}

	return $value;
}

// Separate function to perform the actual geocoding
function lvl_perform_geocoding($post_id)
{
	// Only proceed if this is a location post
	if (get_post_type($post_id) !== 'location') {
		return;
	}

	error_log("Starting geocoding for location ID: $post_id");

	// Attempt to geocode the location
	$result = lvl_geocode_location($post_id);

	if ($result) {
		// Success - coordinates should now be populated
		error_log("Successfully geocoded location ID: $post_id - Lat: {$result['lat']}, Lng: {$result['lng']}");

		// Set a transient to show success message
		set_transient('geocoding_success_' . $post_id, [
			'lat' => $result['lat'],
			'lng' => $result['lng']
		], 30);
	} else {
		// Failed to geocode
		error_log("Failed to geocode location ID: $post_id - Check error log for details");

		// Set a transient to show error message
		set_transient('geocoding_error_' . $post_id, true, 30);
	}
}

// Show admin notices for geocoding results
add_action('admin_notices', 'lvl_show_geocoding_result_notice');
function lvl_show_geocoding_result_notice()
{
	global $post;
	if (!is_admin() || !$post || get_post_type($post) !== 'location') {
		return;
	}

	// Check for success transient
	$success = get_transient('geocoding_success_' . $post->ID);
	if ($success) {
		echo '<div class="notice notice-success is-dismissible"><p><strong>Geocoding Success!</strong> Location coordinates updated - Lat: ' . esc_html($success['lat']) . ', Lng: ' . esc_html($success['lng']) . '</p></div>';
		delete_transient('geocoding_success_' . $post->ID);
	}

	// Check for error transient
	$error = get_transient('geocoding_error_' . $post->ID);
	if ($error) {
		echo '<div class="notice notice-error is-dismissible"><p><strong>Geocoding Failed!</strong> Could not fetch coordinates. Check the address fields and error log for details.</p></div>';
		delete_transient('geocoding_error_' . $post->ID);
	}
}

// Add admin notice if Google Maps API key is not configured
add_action('admin_notices', 'lvl_geocoding_admin_notice');
function lvl_geocoding_admin_notice()
{
	// Only show on location edit pages
	global $post;
	if (!is_admin() || !$post || get_post_type($post) !== 'location') {
		return;
	}

	$api_key = defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : '';
	if (empty($api_key)) {
		echo '<div class="notice notice-warning"><p><strong>Location Geocoding:</strong> Google Maps API key is not configured. Please add <code>define(\'GOOGLE_MAPS_API_KEY\', \'your_api_key_here\');</code> to your wp-config.php file to enable automatic geocoding.</p></div>';
	}
}

// Test geocoding function (admin only)
add_action('wp_ajax_test_geocoding', 'lvl_test_geocoding_ajax');
function lvl_test_geocoding_ajax()
{
	if (!current_user_can('manage_options')) {
		wp_die('Unauthorized');
	}

	$post_id = intval($_POST['post_id']);
	if (!$post_id || get_post_type($post_id) !== 'location') {
		wp_send_json_error('Invalid location ID');
	}

	$result = lvl_geocode_location($post_id);

	if ($result) {
		wp_send_json_success([
			'message' => "Successfully geocoded location! Lat: {$result['lat']}, Lng: {$result['lng']}",
			'lat' => $result['lat'],
			'lng' => $result['lng']
		]);
	} else {
		wp_send_json_error('Failed to geocode location. Check the error log for details.');
	}
}

/**
 * Enhanced proximity search with your business rules:
 * - Show 3 nearest locations if closest is within 500 miles
 * - Show only 1 closest location if all are 500+ miles away
 * 
 * @param string $zip_code User's input zip code
 * @param array $service_filter Optional service taxonomy filter
 * @return array Array of location data with distances
 */
function lvl_get_proximity_locations($zip_code, $service_filter = [])
{
	// Get user coordinates from zip code
	$user_coords = lvl_get_zip_coordinates($zip_code);

	if (!$user_coords) {
		return ['error' => 'Invalid zip code or geocoding failed'];
	}

	// Query all locations with coordinates
	$args = [
		'post_type' => 'location',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'meta_query' => [
			'relation' => 'AND',
			[
				'key' => 'lat',
				'compare' => 'EXISTS'
			],
			[
				'key' => 'long',
				'compare' => 'EXISTS'
			],
			[
				'key' => 'lat',
				'value' => '',
				'compare' => '!='
			],
			[
				'key' => 'long',
				'value' => '',
				'compare' => '!='
			]
		]
	];

	// Add service filter if provided
	if (!empty($service_filter)) {
		if (count($service_filter) === 1) {
			// Single service - use IN operator
			$args['tax_query'] = [
				[
					'taxonomy' => 'location_service',
					'field' => 'slug',
					'terms' => $service_filter,
					'operator' => 'IN'
				]
			];
		} else {
			// Multiple services - use AND operator (location must have ALL services)
			$args['tax_query'] = ['relation' => 'AND'];
			foreach ($service_filter as $service) {
				$args['tax_query'][] = [
					'taxonomy' => 'location_service',
					'field' => 'slug',
					'terms' => [$service],
					'operator' => 'IN'
				];
			}
		}
	}

	$locations_query = new WP_Query($args);
	$locations_with_distance = [];

	if ($locations_query->have_posts()) {
		while ($locations_query->have_posts()) {
			$locations_query->the_post();
			$location_id = get_the_ID();

			$lat = get_field('lat', $location_id);
			$lng = get_field('long', $location_id);

			if ($lat && $lng) {
				$distance = lvl_calculate_distance(
					$user_coords['lat'],
					$user_coords['lng'],
					$lat,
					$lng
				);

				$locations_with_distance[] = [
					'post_id' => $location_id,
					'post' => get_post($location_id),
					'distance' => $distance,
					'lat' => $lat,
					'lng' => $lng
				];
			}
		}
	}

	wp_reset_postdata();

	// Sort by distance (closest first)
	usort($locations_with_distance, function ($a, $b) {
		return $a['distance'] <=> $b['distance'];
	});

	// Apply your business rules
	if (empty($locations_with_distance)) {
		return ['error' => 'No locations found'];
	}

	$closest_distance = $locations_with_distance[0]['distance'];

	if ($closest_distance <= 500) {
		// Show up to 3 nearest locations if closest is within 500 miles
		$result_locations = array_slice($locations_with_distance, 0, 3);
	} else {
		// Show only the closest location if all are 500+ miles away
		$result_locations = array_slice($locations_with_distance, 0, 1);
	}

	return [
		'user_coordinates' => $user_coords,
		'total_locations_found' => count($locations_with_distance),
		'closest_distance' => $closest_distance,
		'locations' => $result_locations,
		'rule_applied' => $closest_distance <= 500 ? '3_nearest' : '1_closest'
	];
}

/**
 * Get user's approximate location from IP address
 * @return array|false Array with zip, city, state, lat, lng or false
 */
function lvl_get_user_location_from_ip()
{
	$user_ip = lvl_get_user_ip();

	if (!$user_ip || $user_ip === '127.0.0.1') {
		return false; // Local development or invalid IP
	}

	// Check cache first
	$cache_key = 'ip_location_' . md5($user_ip);
	$cached_location = get_transient($cache_key);

	if ($cached_location !== false) {
		return $cached_location;
	}

	// Use ipapi.co service (free tier: 1000 requests/day)
	$url = "https://ipapi.co/{$user_ip}/json/";

	$response = wp_remote_get($url, [
		'timeout' => 15,
		'headers' => [
			'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url()
		]
	]);

	if (is_wp_error($response)) {
		return false;
	}

	$response_code = wp_remote_retrieve_response_code($response);
	$body = wp_remote_retrieve_body($response);

	// Log rate limiting issues for debugging
	if ($response_code === 429) {
		error_log("IP Location: Rate limited by ipapi.co (429) - consider using browser geolocation instead");
	}
	$data = json_decode($body, true);

	if ($data === null) {
		return false;
	}

	if (isset($data['postal']) && isset($data['latitude']) && isset($data['longitude'])) {
		$location_data = [
			'zip' => $data['postal'],
			'city' => $data['city'] ?? '',
			'state' => $data['region_code'] ?? '',
			'lat' => $data['latitude'],
			'lng' => $data['longitude'],
			'country' => $data['country_code'] ?? ''
		];

		// Cache for 24 hours
		set_transient($cache_key, $location_data, 24 * HOUR_IN_SECONDS);

		return $location_data;
	}

	return false;
}

/**
 * Get user's real IP address (handles proxies, load balancers, etc.)
 * @return string|false
 */
function lvl_get_user_ip()
{
	// Check for various IP headers (in order of priority)
	$ip_headers = [
		'HTTP_CF_CONNECTING_IP',     // Cloudflare
		'HTTP_CLIENT_IP',            // Proxy
		'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
		'HTTP_X_FORWARDED',          // Proxy
		'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
		'HTTP_FORWARDED_FOR',        // Proxy
		'HTTP_FORWARDED',            // Proxy
		'REMOTE_ADDR'                // Standard
	];

	foreach ($ip_headers as $header) {
		if (!empty($_SERVER[$header])) {
			$ips = explode(',', $_SERVER[$header]);
			$ip = trim($ips[0]);

			if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
				return $ip;
			}
		}
	}

	return $_SERVER['REMOTE_ADDR'] ?? false;
}

// AJAX handlers for enhanced location search
add_action('wp_ajax_search_locations_by_zip', 'lvl_search_locations_by_zip');
add_action('wp_ajax_nopriv_search_locations_by_zip', 'lvl_search_locations_by_zip');

function lvl_search_locations_by_zip()
{
	// Verify nonce for security
	if (!wp_verify_nonce($_POST['nonce'] ?? '', 'locations_search_nonce')) {
		wp_send_json_error('Security check failed.');
	}

	$zip = isset($_POST['zip']) ? sanitize_text_field($_POST['zip']) : '';
	$service_filter = isset($_POST['service']) ? array_map('sanitize_text_field', (array) $_POST['service']) : [];

	if (empty($zip) || !preg_match('/^\d{5}$/', $zip)) {
		wp_send_json_error('Please enter a valid 5-digit ZIP code.');
	}

	error_log("AJAX ZIP Search: Starting search for ZIP: $zip");

	$results = lvl_get_proximity_locations($zip, $service_filter);

	if (isset($results['error'])) {
		error_log("AJAX ZIP Search: Error for ZIP $zip - {$results['error']}");
		wp_send_json_error($results['error']);
	}

	// Render location cards HTML
	ob_start();
	foreach ($results['locations'] as $location_data) {
		lvl_render_location_card($location_data['post_id'], $location_data['distance']);
	}
	$locations_html = ob_get_clean();

	wp_send_json_success([
		'locations_html' => $locations_html,
		'total_found' => $results['total_locations_found'],
		'locations_shown' => count($results['locations']),
		'closest_distance' => round($results['closest_distance'], 1),
		'rule_applied' => $results['rule_applied'],
		'user_zip' => $zip
	]);
}

// AJAX handler for IP-based location detection
add_action('wp_ajax_get_user_location_from_ip', 'lvl_get_user_location_from_ip_ajax');
add_action('wp_ajax_nopriv_get_user_location_from_ip', 'lvl_get_user_location_from_ip_ajax');

// AJAX handler for geocoding coordinates to ZIP code
add_action('wp_ajax_geocode_coordinates', 'lvl_geocode_coordinates_ajax');
add_action('wp_ajax_nopriv_geocode_coordinates', 'lvl_geocode_coordinates_ajax');

function lvl_get_user_location_from_ip_ajax()
{	// Verify nonce for security
	if (!wp_verify_nonce($_POST['nonce'] ?? '', 'locations_search_nonce')) {
		wp_send_json_error('Security check failed.');
		return;
	}

	$user_ip = lvl_get_user_ip();

	// Clear any cached results to force fresh API call
	$cache_key = 'ip_location_' . md5($user_ip);
	delete_transient($cache_key);

	$location = lvl_get_user_location_from_ip();
	if ($location) {
		wp_send_json_success($location);
	} else {
		// Provide more specific error message based on IP
		if (!$user_ip || $user_ip === '127.0.0.1') {
			wp_send_json_error('Cannot detect location from localhost. Please enter your ZIP code manually.');
		} else {
			wp_send_json_error('Could not determine your location from IP: ' . $user_ip . '. Please enter your ZIP code manually.');
		}
	}
}

function lvl_geocode_coordinates_ajax()
{
	// Verify nonce for security
	if (!wp_verify_nonce($_POST['nonce'] ?? '', 'locations_search_nonce')) {
		wp_send_json_error('Security check failed.');
	}

	$lat = floatval($_POST['lat'] ?? 0);
	$lng = floatval($_POST['lng'] ?? 0);

	if (!$lat || !$lng) {
		wp_send_json_error('Invalid coordinates provided.');
	}



	// Use Google Maps Geocoding API
	$api_key = defined('GOOGLE_GEOCODING_API_KEY') ? GOOGLE_GEOCODING_API_KEY : '';
	$url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$lat},{$lng}&key={$api_key}";

	$response = wp_remote_get($url, ['timeout' => 15]);

	if (is_wp_error($response)) {
		wp_send_json_error('Geocoding service unavailable.');
	}

	$body = wp_remote_retrieve_body($response);
	$data = json_decode($body, true);

	if ($data['status'] !== 'OK' || empty($data['results'])) {
		wp_send_json_error('Could not geocode the location.');
	}

	// Extract ZIP code and location info from the first result
	$result = $data['results'][0];
	$location_data = [
		'zip' => '',
		'city' => '',
		'state' => '',
		'country' => ''
	];

	foreach ($result['address_components'] as $component) {
		$types = $component['types'];

		if (in_array('postal_code', $types)) {
			$location_data['zip'] = $component['long_name'];
		}
		if (in_array('locality', $types)) {
			$location_data['city'] = $component['long_name'];
		}
		if (in_array('administrative_area_level_1', $types)) {
			$location_data['state'] = $component['short_name'];
		}
		if (in_array('country', $types)) {
			$location_data['country'] = $component['short_name'];
		}
	}

	if (empty($location_data['zip'])) {
		wp_send_json_error('Could not determine ZIP code from your location.');
	}

	wp_send_json_success($location_data);
}

add_action('wp_ajax_filter_locations', 'lvl_filter_locations');
add_action('wp_ajax_nopriv_filter_locations', 'lvl_filter_locations');
function lvl_filter_locations()
{
	// Verify nonce for security (optional for backward compatibility)
	if (isset($_POST['nonce']) && !wp_verify_nonce($_POST['nonce'], 'locations_search_nonce')) {
		wp_send_json_error('Security check failed.');
	}

	$zip = isset($_POST['zip']) ? sanitize_text_field($_POST['zip']) : '';
	$service_filter = isset($_POST['service']) ? array_map('sanitize_text_field', (array) $_POST['service']) : [];
	$paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;

	error_log("Filter Locations AJAX: ZIP: '$zip', Services: " . print_r($service_filter, true) . ", Page: $paged");

	// If ZIP code is provided, use proximity search with service filtering
	if (!empty($zip)) {
		$proximity_results = lvl_get_proximity_locations($zip, $service_filter);

		if (isset($proximity_results['error'])) {
			error_log("Proximity search error: " . $proximity_results['error']);
			wp_send_json_error($proximity_results['error']);
		}

		// Render location cards
		ob_start();
		if (!empty($proximity_results['locations'])) {
			foreach ($proximity_results['locations'] as $location_data) {
				lvl_render_location_card($location_data['post_id'], $location_data['distance']);
			}
		} else {
			// Generate helpful error message
			if (!empty($service_filter)) {
				// Get service names for user-friendly message
				$service_names = [];
				foreach ($service_filter as $service_slug) {
					$term = get_term_by('slug', $service_slug, 'location_service');
					if ($term) {
						$service_names[] = $term->name;
					}
				}
				$services_text = implode(' and ', $service_names);
				echo '<p>No locations within 500 miles offer ' . esc_html($services_text) . ' services in your area. Please try a different ZIP code or remove some service filters.</p>';
			} else {
				echo '<p>No locations found near ZIP code ' . esc_html($zip) . '. Please try a different ZIP code.</p>';
			}
		}
		$html = ob_get_clean();

		$response_data = [
			'html' => $html,
			'has_more' => false, // Proximity search shows limited results
			'next_page' => $paged + 1,
			'total_found' => $proximity_results['total_locations_found'] ?? 0,
			'locations_shown' => count($proximity_results['locations'] ?? []),
			'search_type' => 'proximity',
			'rule_applied' => $proximity_results['rule_applied'] ?? '',
			'closest_distance' => round($proximity_results['closest_distance'] ?? 0, 1),
			'zip_code' => $zip,
			'service_filters' => $service_filter,
			'has_combined_filters' => !empty($service_filter) && !empty($zip),
		];

		error_log("Proximity search results: " . print_r($response_data, true));
		wp_send_json($response_data);
		return;
	}

	// Regular filtering without ZIP code
	$args = [
		'post_type' => 'location',
		'posts_per_page' => 12,
		'paged' => $paged,
		'post_status' => 'publish',
		'orderby' => 'title',
		'order' => 'ASC',
	];

	// Add service filtering
	if (!empty($service_filter)) {
		if (count($service_filter) === 1) {
			// Single service - use IN operator
			$args['tax_query'] = [
				[
					'taxonomy' => 'location_service',
					'field' => 'slug',
					'terms' => $service_filter,
					'operator' => 'IN'
				]
			];
		} else {
			// Multiple services - use AND operator (location must have ALL services)
			$args['tax_query'] = ['relation' => 'AND'];
			foreach ($service_filter as $service) {
				$args['tax_query'][] = [
					'taxonomy' => 'location_service',
					'field' => 'slug',
					'terms' => [$service],
					'operator' => 'IN'
				];
			}
		}
	}

	$locations_query = new WP_Query($args);

	ob_start();
	if ($locations_query->have_posts()) {
		while ($locations_query->have_posts()) {
			$locations_query->the_post();
			lvl_render_location_card(get_the_ID());
		}
	} else {
		echo '<p>No locations found.</p>';
	}
	wp_reset_postdata();
	$html = ob_get_clean();

	wp_send_json([
		'html' => $html,
		'has_more' => ($locations_query->max_num_pages > $paged),
		'next_page' => $paged + 1,
		'search_type' => 'regular_filter',
		'total_found' => $locations_query->found_posts,
		'service_filters' => $service_filter,
		'has_service_filter' => !empty($service_filter),
	]);
}
