# Enhanced Location Proximity Search System

## Overview

This system provides advanced ZIP code-based location searching with your specific business rules:

- **3 Nearest Locations** when closest location is within 500 miles
- **1 Closest Location** when all locations are 500+ miles away
- **IP-Based Auto-Location Detection** with "Use My Location" button
- **Real-time AJAX Search** with enhanced user feedback

## Business Logic

### Distance Rules

```
IF closest_location_distance <= 500 miles:
    SHOW up to 3 nearest locations
ELSE:
    SHOW only 1 closest location
```

### Example Scenarios

- **User in Philadelphia, PA**: Shows 3 nearest locations (likely all within 500 miles)
- **User in rural Montana**: Shows only 1 closest location if all are 500+ miles
- **User in New York City**: Shows 3 nearest locations with distances

## Features Implemented

### 1. Enhanced Proximity Search (`lvl_get_proximity_locations`)

- Queries all locations with valid coordinates
- Calculates distances using Haversine formula
- Applies your 3-nearest/500-mile business rules
- Returns structured data with distances and rule information

### 2. IP-Based Location Detection

- **Function**: `lvl_get_user_location_from_ip()`
- **Service**: Uses ipapi.co (free tier: 1000 requests/day)
- **Caching**: 24-hour cache per IP address
- **Handles**: Proxies, load balancers, Cloudflare
- **Privacy**: Only detects ZIP code, not exact address

### 3. Enhanced Search Form

- **ZIP Input**: 5-digit validation with pattern matching
- **Use My Location Button**: One-click IP-based detection
- **Real-time Status**: Loading, success, and error messages
- **Auto-search**: Automatically searches after location detection

### 4. AJAX-Powered Interface

- **No Page Reload**: Seamless search experience
- **URL Updates**: Updates browser URL with search parameters
- **Error Handling**: Graceful failure with helpful messages
- **Loading States**: Visual feedback during API calls

## Files Added/Modified

### PHP Backend (`lib/locations.php`)

```php
// New Functions:
lvl_get_proximity_locations($zip_code, $service_filter = [])
lvl_get_user_location_from_ip()
lvl_get_user_ip()

// AJAX Handlers:
lvl_search_locations_by_zip()
lvl_get_user_location_from_ip_ajax()
```

### Frontend JavaScript (`src/js/location-proximity-search.js`)

```javascript
class LocationProximitySearch {
    handleFormSubmit()      // ZIP search
    useCurrentLocation()    // IP detection
    searchLocationsByZip()  // AJAX search
    displaySearchResults()  // Result rendering
}
```

### Enhanced Archive Template (`archive-location.php`)

- Updated search form with "Use My Location" button
- Enhanced results display with distance information
- Business rule explanations in search results
- Responsive design improvements

### Styling (`src/scss/location-proximity-search.scss`)

- Modern, accessible form design
- Loading states and visual feedback
- Mobile-responsive layout
- Distance badges and location cards

## Usage Examples

### Basic ZIP Search

```javascript
// User enters "19014" (Aston, PA)
// System shows 3 nearest locations with distances:
// 1. Aston Office - 0.5 miles
// 2. Media Office - 3.2 miles
// 3. Springfield Office - 8.7 miles
```

### IP-Based Detection

```javascript
// User clicks "Use My Location"
// System detects: "Located you in Aston, PA 19014"
// Auto-populates ZIP field and searches
// Shows same 3 nearest results
```

### 500+ Mile Rule

```javascript
// User in Montana enters "59718"
// Closest location is 750 miles away
// System shows: "Showing closest location only (750.2 miles away - all locations are 500+ miles)"
// Displays only 1 location
```

## API Integration

### Google Maps Geocoding API

- **ZIP Coordinates**: Converts ZIP codes to lat/lng
- **Caching**: 24-hour cache per ZIP code
- **Usage**: ~1 request per unique ZIP search
- **Cost**: $5 per 1,000 requests (after free tier)

### IP Geolocation Service (ipapi.co)

- **Free Tier**: 1,000 requests/day
- **Accuracy**: City/ZIP level (good enough for our needs)
- **Privacy**: No personal data stored
- **Fallback**: Manual ZIP entry if detection fails

## Configuration

### Required Setup

1. ✅ **Google Maps API Key** - Already configured in `wp-config.php`
2. ✅ **Location Coordinates** - Your geocoding system populates these
3. ✅ **JavaScript Enqueuing** - Added to `functions.php`

### Optional Customizations

```php
// In functions.php - customize distance threshold
add_filter('lvl_proximity_distance_threshold', function() {
    return 300; // Change from 500 to 300 miles
});

// Customize max locations shown
add_filter('lvl_proximity_max_locations', function() {
    return 5; // Show up to 5 instead of 3
});
```

## Testing Scenarios

### Test Case 1: Urban Area (Should show 3 locations)

- **ZIP**: 19014 (Aston, PA)
- **Expected**: 3 nearest locations within reasonable distance
- **Rule Applied**: `3_nearest`

### Test Case 2: Rural Area (May show 1 location)

- **ZIP**: 59718 (Bozeman, MT)
- **Expected**: 1 closest location if all are 500+ miles
- **Rule Applied**: `1_closest`

### Test Case 3: IP Detection

- **Action**: Click "Use My Location"
- **Expected**: ZIP field populates, auto-searches
- **Fallback**: Manual entry if detection fails

## Performance Optimizations

### Caching Strategy

- **ZIP Coordinates**: 24-hour cache (`zip_coords_12345`)
- **IP Locations**: 24-hour cache (`ip_location_[hash]`)
- **Database**: Only queries locations with valid coordinates

### API Usage Minimization

- **Google Maps**: Cached ZIP lookups reduce API calls
- **IP Service**: Cached per IP, handles development IPs gracefully
- **Location Data**: Pre-geocoded, no repeated location API calls

## Error Handling

### User-Friendly Messages

- **Invalid ZIP**: "Please enter a valid 5-digit ZIP code."
- **No Results**: "No locations found for ZIP code 12345."
- **API Failure**: "Search failed. Please try again."
- **Location Detection**: "Could not detect your location. Please enter your ZIP code manually."

### Graceful Degradation

- **No JavaScript**: Form still works with page reload
- **API Failure**: Falls back to manual ZIP entry
- **No IP Detection**: Button hides if service unavailable

## Future Enhancements

### Potential Additions

1. **Radius Filter**: Let users choose 25/50/100+ mile radius
2. **Service Integration**: Filter by services during proximity search
3. **Map View**: Visual map showing locations and distances
4. **Driving Directions**: Integration with Google Maps for directions
5. **Geolocation API**: Browser-based GPS location (requires HTTPS + user permission)

### Analytics Tracking

```javascript
// Track search patterns
gtag("event", "location_search", {
  search_zip: zipCode,
  results_count: resultsCount,
  closest_distance: closestDistance,
});
```

## Troubleshooting

### Common Issues

1. **No Results Found**

   - Check if locations have lat/lng coordinates
   - Verify Google Maps API key
   - Test with known ZIP codes

2. **IP Detection Not Working**

   - Check browser console for errors
   - Verify AJAX URL and nonce
   - Test from non-local IP

3. **JavaScript Errors**
   - Ensure script is enqueued on location archive
   - Check for conflicting JavaScript
   - Verify AJAX localization

### Debug Commands

```javascript
// Check if system is loaded
console.log(window.locationsAjax);

// Test IP detection manually
fetch("/wp-admin/admin-ajax.php", {
  method: "POST",
  body: "action=get_user_location_from_ip",
})
  .then((r) => r.json())
  .then(console.log);
```

## Conclusion

This enhanced proximity search system provides a modern, user-friendly way for visitors to find nearby locations with your specific business rules. The combination of IP-based auto-detection and smart distance logic creates an excellent user experience while maintaining reasonable API usage costs.

The system is built with progressive enhancement - it works without JavaScript but provides a much better experience with it enabled.
