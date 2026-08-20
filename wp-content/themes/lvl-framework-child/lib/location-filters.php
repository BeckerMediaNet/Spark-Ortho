<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Function to replace placeholders in content
function lvl_replace_location_placeholders($content)
{
    global $post;

    // Only process on location post type
    if (!$post || get_post_type($post) !== 'location') {
        return $content;
    }

    // Get location fields for the current post
    $fields = get_fields($post->ID);

    if (!$fields) {
        return $content;
    }

    $street = $fields['street'] ?? '';
    $city = $fields['city'] ?? '';
    $state = $fields['state'] ?? '';
    $postalcode = $fields['postalcode'] ?? '';
    $country = $fields['country'] ?? '';
    $phone = $fields['phone'] ?? '';

    // Create replacement array for shortcode-style placeholders
    $replacements = [
        '[street]' => apply_filters('lvl_location_street', $street),
        '[city]' => apply_filters('lvl_location_city', $city),
        '[state]' => apply_filters('lvl_location_state', $state),
        '[postalcode]' => apply_filters('lvl_location_postalcode', $postalcode),
        '[country]' => apply_filters('lvl_location_country', $country),
        '[phone]' => apply_filters('lvl_location_phone', $phone),
    ];

    // Replace all placeholders with actual values
    return str_replace(array_keys($replacements), array_values($replacements), $content);
}

// Hook into content filters to replace placeholders
add_filter('the_content', 'lvl_replace_location_placeholders');
add_filter('the_excerpt', 'lvl_replace_location_placeholders');
