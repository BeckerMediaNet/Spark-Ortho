<?php

/**
 * Main archive template for Locations post type
 */

get_header();

$heading = get_field('locations_archive_heading', 'option');
$description = get_field('locations_archive_description', 'option');

// Get location_service and specialty terms for sidebar filter
$location_services = get_terms([
    'taxonomy' => 'location_service',
    'hide_empty' => false,
]);

$specialties = get_terms([
    'taxonomy' => 'specialty',
    'hide_empty' => false,
]);

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$zip = isset($_GET['zip']) ? sanitize_text_field($_GET['zip']) : '';
$service_filter = isset($_GET['service']) ? array_map('sanitize_text_field', (array) $_GET['service']) : [];

$args = [
    'post_type' => 'location',
    'posts_per_page' => 12,
    'paged' => $paged,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC',
    'tax_query' => [],
];

// Only add tax_query if service_filter is not empty
if (!empty($service_filter)) {
    if (count($service_filter) === 1) {
        // Single service - use IN operator
        $args['tax_query'][] = [
            'taxonomy' => 'location_service',
            'field' => 'slug',
            'terms' => $service_filter,
            'operator' => 'IN',
        ];
    } else {
        // Multiple services - use AND operator (location must have ALL services)
        $args['tax_query']['relation'] = 'AND';
        foreach ($service_filter as $service) {
            $args['tax_query'][] = [
                'taxonomy' => 'location_service',
                'field' => 'slug',
                'terms' => [$service],
                'operator' => 'IN',
            ];
        }
    }
}
// Add proximity sorting by zip code if provided
if (!empty($zip)) {
    $user_coords = lvl_get_zip_coordinates($zip);

    if ($user_coords) {
        $args['posts_per_page'] = -1; // Get all locations for distance sorting
        $args['meta_query'][] = [
            'relation' => 'AND',
            [
                'key' => 'lat',
                'compare' => 'EXISTS'
            ],
            [
                'key' => 'long',
                'compare' => 'EXISTS'
            ]
        ];
    }
}

$locations_query = new WP_Query($args);

// If zip code provided, sort results by distance
if (!empty($zip) && isset($user_coords) && $user_coords) {
    $locations_with_distance = [];

    if ($locations_query->have_posts()) {
        while ($locations_query->have_posts()) {
            $locations_query->the_post();
            $location_id = get_the_ID();

            // Get location coordinates from ACF
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
                    'post' => get_post($location_id),
                    'distance' => $distance
                ];
            }
        }

        // Sort by distance
        usort($locations_with_distance, function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        $locations_with_distance = array_slice($locations_with_distance, 0, 12);
    }

    wp_reset_postdata();
}
?>

<section class="locations-hero py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <h1 class="text-center archive-title"><?php echo (!empty($heading) ? $heading : get_the_title()); ?></h1>
                <div class="d-flex justify-content-center align-items-center flex-column">
                    <p class="text-center"><?php echo (!empty($description) ? $description : ''); ?></p>
                    <div class="wp-block-button is-style-btn-red mb-4">
                        <a class="wp-element-button" href="/spark-smile-assessment/">Book your free Spark Smile Assessment</a>
                    </div>
                    <h2 class="h4 mb-3">Find a location</h2>
                    <form class="location-search-form" id="locationSearchForm" method="get" action="">
                        <span class="visually-hidden">Enter your ZIP code to find the nearest location</span>
                        <div class="search-input-wrapper">
                            <label for="zipInput" class="sr-only">ZIP Code</label>
                            <input type="text" name="zip" id="zipInput" pattern="\d{5}" maxlength="5" placeholder="Enter ZIP code" value="<?php echo esc_attr($zip); ?>" required>
                            <button type="submit" class="btn" id="searchBtn">Search</button>
                        </div>
                    </form>
                    <button type="button" id="useMyLocationBtn" class="mt-2 btn-use-location" title="Use my current location">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                        </svg>
                        Use My Current Location
                    </button>
                    <div class="search-status" id="searchStatus"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container pt-0 pt-md-4 pt-lg-6 pb-6">
    <div class="row">
        <div class="col-12">
            <div class="locations-content-wrapper">
                <aside class="locations-sidebar">
                    <h2 class="filter-heading text-center text-md-start">Filter by Service</h2>
                    <form class="service-filter-form justify-content-center" method="get" action="">
                        <?php foreach ($location_services as $service): ?>
                            <label>
                                <input type="checkbox" name="service[]" value="<?php echo esc_attr($service->slug); ?>" <?php echo in_array($service->slug, $service_filter) ? 'checked' : ''; ?>>
                                <?php echo esc_html($service->name); ?>
                            </label>
                        <?php endforeach; ?>
                        <div class="filter-buttons w-100 justify-content-center mt-4">
                            <button class="btn btn-secondary" type="submit">Filter Services</button>
                            <button class="btn btn-primary-outline" type="button" onclick="clearServiceFilter()">Clear Filters</button>
                        </div>
                    </form>

                </aside>

                <section class="locations-listings">
                    <div class="search-results-info" id="searchResultsInfo" style="display: none;">
                        <!-- Search results summary will be inserted here via JavaScript -->
                    </div>

                    <div class="locations-grid" id="locationsGrid">
                        <?php
                        if (!empty($zip) && isset($locations_with_distance) && !empty($locations_with_distance)):
                            // Display distance-sorted results using new system
                            $proximity_results = lvl_get_proximity_locations($zip, $service_filter);

                            if (isset($proximity_results['locations'])):
                                // Let JavaScript handle the search results summary - just render the location cards
                                foreach ($proximity_results['locations'] as $location_data):
                                    lvl_render_location_card($location_data['post_id'], $location_data['distance']);
                                endforeach;
                            else:
                                echo '<p>No locations found for ZIP code ' . esc_html($zip) . '.</p>';
                            endif;
                        elseif ($locations_query->have_posts()):
                            // Display all locations (default view)
                            while ($locations_query->have_posts()): $locations_query->the_post();
                                lvl_render_location_card(get_the_ID());
                            endwhile;
                        else:
                            echo '<p>No locations found.</p>';
                        endif;
                        ?>
                    </div>

                    <?php
                    // Show Load More button for different scenarios
                    $show_load_more = false;
                    $button_text = 'Load More';

                    if (!empty($zip)) {
                        // After ZIP search - show "View All Locations" button
                        $show_load_more = true;
                        $button_text = 'View All Locations';
                    } elseif ($locations_query->max_num_pages > 1) {
                        // Regular pagination - show "Load More" if there are more pages
                        $show_load_more = true;
                        $button_text = 'Load More';
                    }

                    if ($show_load_more): ?>
                        <div class="d-flex justify-content-center mt-4">
                            <button class="load-more-locations btn btn-primary" data-next-page="<?php echo $paged + 1; ?>"><?php echo $button_text; ?></button>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>
</div>

<?php 
// Check if there's a designated archive page for locations
$archive_page_id = get_option('location_archive_page');
if ($archive_page_id) {
    $archive_page = get_post($archive_page_id);
    if ($archive_page) {
        echo apply_filters('the_content', $archive_page->post_content);
    }
}

wp_reset_postdata();
get_footer();
