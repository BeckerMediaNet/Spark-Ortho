<?php

add_shortcode('current_date', 'lvl_display_date');
function lvl_display_date($atts)
{

	$atts = shortcode_atts(array(
		'format' => 'm-d-Y'
	), $atts);

	return date($atts['format']);
}

// Shortcode [location] to display location name with paramter state (long||short||only) to display state name or abbreviation
add_shortcode('location', 'display_location');
function display_location($atts)
{
	global $post;

	$atts = shortcode_atts(array(
		'state' => false
	), $atts);

	$parent = lvl_get_top_most_parent($post->ID);

	$location = $post->post_title;

	if ($atts['state'] && $parent !== $post->ID) {

		if ($parent) {
			$state = get_the_title($parent);
		}

		if ($atts['state'] === 'only') {
			return get_the_title($parent);
		}

		if ($state && $atts['state'] === 'short') {

			$state = lvl_state_to_abbv($state);
		} else if ($state && $atts['state'] === 'long') {
			$state = $state;
		}

		return $location . ', ' . $state;
	}

	return $location;
}

// Shortcode [location_details display_address=true, phone=true, fax=true, location_times=true] to display various location ACF fields with customizable attributes
add_shortcode('location_details', 'lvl_display_location_details');
function lvl_display_location_details($atts)
{
	global $post;

	// Return early if not on a location post
	if (get_post_type($post) !== 'location') {
		if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) {
			return '<div style="background: #f0f0f1; border: 1px solid #c3c4c7; padding: 10px; margin: 10px 0; border-radius: 4px; color: #646970; font-size: 13px;">
				<strong>Location Details Shortcode:</strong> This shortcode only displays content on Location post types. Current post type: <code>' . get_post_type($post) . '</code>
			</div>';
		}
		return '';
	}

	// Default attributes
	$atts = shortcode_atts(array(
		'display_address' => false,
		'phone' => false,
		'location_times' => false,
		'fax' => false
	), $atts);

	// Get ACF fields
	$fields = get_fields($post->ID);

	ob_start();
?>

	<div class="single-location-details">
		<?php if ($atts['display_address'] && !empty($fields['display_address'])): ?>
			<div class="location-detail-item display-address">
				<span class="icon icon-address" aria-hidden="true">
					<svg fill="none" height="20" viewBox="0 0 16 20" width="16" xmlns="http://www.w3.org/2000/svg">
						<path d="m8 10c.55 0 1.021-.196 1.413-.588.39133-.39133.587-.862.587-1.412s-.19567-1.021-.587-1.413c-.392-.39133-.863-.587-1.413-.587s-1.02067.19567-1.412.587c-.392.392-.588.863-.588 1.413s.196 1.02067.588 1.412c.39133.392.862.588 1.412.588zm0 9.625c-.13333 0-.26667-.025-.4-.075s-.25-.1167-.35-.2c-2.43333-2.15-4.25-4.1457-5.45-5.987-1.2-1.842-1.8-3.563-1.8-5.163 0-2.5.804333-4.49167 2.413-5.975 1.608-1.483333 3.47033-2.225 5.587-2.225 2.1167 0 3.979.741667 5.587 2.225 1.6087 1.48333 2.413 3.475 2.413 5.975 0 1.6-.6 3.321-1.8 5.163-1.2 1.8413-3.0167 3.837-5.45 5.987-.1.0833-.21667.15-.35.2s-.26667.075-.4.075z" fill="#00436c" />
					</svg>
				</span>

				<?php echo nl2br(esc_html(wp_strip_all_tags($fields['display_address']))); ?>

			</div>
		<?php endif; ?>

		<?php if ($atts['phone'] && !empty($fields['phone']) && !empty($fields['phone']['url'])): ?>
			<div class="location-detail-item location-phone">
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


		<?php if ($atts['fax'] && !empty($fields['fax'])): ?>
			<div class="location-detail-item location-fax">
				<span class="icon icon-fax" aria-hidden="true">
					<svg fill="none" height="18" viewBox="0 0 20 18" width="20" xmlns="http://www.w3.org/2000/svg">
						<path d="m16 4h-1v-4h-10v4h-1c-2.21 0-4 1.79-4 4v6h4v4h12v-4h4v-6c0-2.21-1.79-4-4-4zm-9-2h6v2h-6zm6 14h-6v-4h6zm3-4h-1v2h-10v-2h-1v-4c0-1.1.9-2 2-2h9c1.1 0 2 .9 2 2zm1-6c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z" fill="#00436c" />
					</svg>
				</span>
				<span><?php echo esc_html($fields['fax']); ?></span>
			</div>
		<?php endif; ?>

		<?php if ($atts['location_times'] && !empty($fields['location_times'])): ?>
			<div class="location-detail-item location-hours location-times">
				<span class="icon icon-hours" aria-hidden="true">
					<svg fill="none" height="22" viewBox="0 0 20 22" width="20" xmlns="http://www.w3.org/2000/svg">
						<path d="m18 2h-1v-2h-2v2h-10v-2h-2v2h-1c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2v-16c0-1.1-.9-2-2-2zm0 18h-16v-13h16z" fill="#00436c" />
					</svg>
				</span>
				<div class="time-content d-flex flex-column">
					<?php foreach ($fields['location_times'] as $time_row): ?>
						<div class="time-row d-flex flex-wrap flex-sm-nowrap column-gap-3 row-gap-1 justify-content-between mb-2 mb-sm-0">
							<?php if (!empty($time_row['location_days'])): ?>
								<span class="location-days">
									<?php echo nl2br(esc_html(wp_strip_all_tags($time_row['location_days']))); ?>
								</span>
							<?php endif; ?>
							<?php if (!empty($time_row['location_days']) && !empty($time_row['location_hours'])): ?><br><?php endif; ?>
							<?php if (!empty($time_row['location_hours'])): ?>
								<span class="location-hours-text">
									<?php echo nl2br(esc_html(wp_strip_all_tags($time_row['location_hours']))); ?>
								</span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>


	</div>

<?php
	return ob_get_clean();
}
