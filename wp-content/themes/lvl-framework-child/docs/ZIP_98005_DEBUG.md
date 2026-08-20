# ZIP Code 98005 Debug Test

To debug the ZIP code issue, try these steps:

## 1. Manual API Test

Test the Google Maps API directly in your browser:

```
https://maps.googleapis.com/maps/api/geocode/json?address=98005&key=YOUR_API_KEY_HERE
```

Replace `YOUR_API_KEY_HERE` with: `AIzaSyABDXScDgKZKkEZyi_fWP82Xq2_wp62Xhs`

Expected result for 98005 (Bellevue, WA):

```json
{
  "results": [
    {
      "geometry": {
        "location": {
          "lat": 47.6101497,
          "lng": -122.2015159
        }
      }
    }
  ],
  "status": "OK"
}
```

## 2. Clear WordPress Cache

Add this to your functions.php temporarily:

```php
add_action('admin_init', function() {
    if (current_user_can('manage_options') && isset($_GET['clear_zip_cache'])) {
        delete_transient('zip_coords_98005');
        echo '<div class="notice notice-success"><p>Cache cleared for ZIP 98005</p></div>';
    }
});
```

Then visit: `/wp-admin/?clear_zip_cache=1`

## 3. Check Debug Log

After testing the search, check `/wp-content/debug.log` for:

```
ZIP Coordinates: Making API request for ZIP: 98005
ZIP Coordinates: API response for ZIP 98005 - Status: OK
ZIP Coordinates: Successfully geocoded ZIP 98005 - Lat: 47.6101497, Lng: -122.2015159
```

## 4. Common Issues

### API Key Issues:

- Key not enabled for Geocoding API
- Key has domain restrictions
- Billing not set up (Google requires billing even for free tier)

### WordPress Issues:

- Cache plugin interfering
- Server blocking outbound requests
- Timeout issues

## 5. Test Different ZIP Codes

Try these known working ZIP codes:

- 10001 (New York, NY)
- 90210 (Beverly Hills, CA)
- 60601 (Chicago, IL)

If those work but 98005 doesn't, it might be a regional API issue.

## 6. Check Google Cloud Console

1. Go to https://console.cloud.google.com/
2. Select your project
3. Go to APIs & Services > Credentials
4. Check your API key restrictions
5. Go to APIs & Services > Enabled APIs
6. Verify "Geocoding API" is enabled
7. Check Billing account is set up

## 7. Alternative Test

If Google Maps fails, test with this alternative (for debugging only):

```php
$url = "https://api.zippopotam.us/us/98005";
$response = wp_remote_get($url);
$data = json_decode(wp_remote_retrieve_body($response), true);
// Should return lat/lng for 98005
```
