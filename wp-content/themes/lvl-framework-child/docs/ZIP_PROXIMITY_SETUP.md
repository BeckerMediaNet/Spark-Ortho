# ZIP Code Proximity Location Search Setup

## Overview

This feature allows users to search for locations by ZIP code and displays results sorted by distance from the user's location.

## Option 1: Google Maps API (Recommended - Implemented)

### 1. Get Google Maps API Key

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable the following APIs:
   - **Geocoding API** (for converting addresses/ZIP codes to coordinates)
   - **Places API** (optional, for enhanced location data)
4. Create API credentials (API Key)
5. Restrict the API key to your domain for security

### 2. Add API Key to WordPress

Add this line to your `wp-config.php` file:

```php
define('GOOGLE_MAPS_API_KEY', 'your_api_key_here');
```

### 3. Add Required ACF Fields

Add these fields to your Location post type:

- **Field Name:** `latitude`

  - **Field Type:** Number
  - **Field Label:** Latitude
  - **Instructions:** Auto-populated by geocoding

- **Field Name:** `longitude`
  - **Field Type:** Number
  - **Field Label:** Longitude
  - **Instructions:** Auto-populated by geocoding

### 4. Populate Existing Location Coordinates

Run this one-time script to geocode all existing locations:

```php
// Add this to functions.php temporarily or run via WP-CLI
function populate_location_coordinates() {
    $locations = get_posts([
        'post_type' => 'location',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ]);

    foreach ($locations as $location) {
        $lat = get_field('latitude', $location->ID);

        // Only geocode if coordinates don't exist
        if (empty($lat)) {
            echo "Geocoding: " . $location->post_title . "\n";
            $coords = lvl_geocode_location($location->ID);

            if ($coords) {
                echo "Success: {$coords['lat']}, {$coords['lng']}\n";
            } else {
                echo "Failed to geocode\n";
            }

            // Respect API rate limits
            sleep(1);
        }
    }
}

// Uncomment to run once:
// populate_location_coordinates();
```

### 5. How It Works

1. **User Input:** User enters ZIP code and clicks "Search"
2. **Geocoding:** ZIP code is converted to latitude/longitude using Google API
3. **Distance Calculation:** Haversine formula calculates distance to all locations
4. **Results:** Locations are sorted by proximity and displayed with distance

### 6. Features

- **Caching:** ZIP coordinates are cached for 24 hours to reduce API calls
- **Distance Display:** Shows distance in miles next to location name
- **Pagination:** Works with existing load more functionality
- **AJAX Compatible:** Works with existing filtering system

## Option 2: ZIP Code Database (Alternative)

If you prefer not to use Google's API:

### Pros:

- No API costs or rate limits
- Works offline
- Faster response times

### Cons:

- Requires ZIP code database file (~50MB)
- Less accurate than Google's geocoding
- Additional maintenance for database updates

### Implementation:

1. Purchase/download ZIP code database with coordinates
2. Import into WordPress database or use CSV file
3. Modify functions to lookup coordinates from local database instead of API

## Cost Considerations

### Google Maps API Pricing:

- **Geocoding API:** $5 per 1,000 requests
- **Free Tier:** $200 monthly credit (≈40,000 requests)
- **Caching:** Reduces repeat requests significantly

### Optimization Tips:

- Cache coordinates for ZIP codes (implemented)
- Geocode location addresses once and store (implemented)
- Consider ZIP+4 for more precise results
- Implement rate limiting if needed

## Testing

1. Add API key to wp-config.php
2. Add latitude/longitude fields to Location ACF group
3. Run the geocoding script for existing locations
4. Test ZIP code search on frontend
5. Verify distance calculations and sorting

## Troubleshooting

- **"No locations found":** Check if locations have coordinates stored
- **API errors:** Verify API key and enabled services
- **Distance incorrect:** Confirm coordinate accuracy in database
- **Performance issues:** Check for proper caching implementation
