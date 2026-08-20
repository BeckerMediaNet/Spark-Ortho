# Geocoding Troubleshooting Guide

## Current Setup Status

### ✅ What's Already Working:

- ACF fields are properly defined (`lat` and `long`)
- Force geocode checkbox field exists
- PHP functions are integrated with correct field names
- Auto-geocoding trigger is set up

### ❌ What Needs to be Done:

## Step 1: Add Google Maps API Key

**Add this line to your `wp-config.php` file:**

```php
define('GOOGLE_MAPS_API_KEY', 'your_actual_api_key_here');
```

**To get an API key:**

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Enable "Geocoding API"
4. Create credentials (API Key)
5. Restrict the key to your domain

## Step 2: Test the Setup

### Method 1: Use the Force Geocode Field

1. Edit any Location post in WordPress admin
2. Check the "Fetch Geocode (lat/long)" checkbox
3. Click "Update"
4. The lat/lng fields should populate automatically
5. Check the checkbox will auto-uncheck after processing

### Method 2: Check Error Logs

If geocoding fails, check your WordPress error log for details:

- Look for messages starting with "Geocoding"
- Common issues: API key missing, invalid address, API quota exceeded

### Method 3: Manual Test (for developers)

Add this temporary code to test a specific location:

```php
// Test geocoding for location ID 123
$result = lvl_geocode_location(123);
if ($result) {
    echo "Success: Lat {$result['lat']}, Lng {$result['lng']}";
} else {
    echo "Failed - check error logs";
}
```

## Common Issues & Solutions

### Issue: "No address found for location ID"

**Solution:** Ensure the location has either:

- A filled "Display Address" field, OR
- Individual address fields (Street, City, State, Postal Code)

### Issue: "Google Maps API key not defined"

**Solution:** Add the API key to wp-config.php as shown above

### Issue: "Geocoding API request failed"

**Possible causes:**

- Network connectivity issues
- API key restrictions (domain/IP restrictions)
- API not enabled in Google Cloud Console

### Issue: "Status: ZERO_RESULTS"

**Solution:**

- Check if the address is valid and specific enough
- Try with just city, state instead of full address
- Verify address format is correct

### Issue: "Status: OVER_QUERY_LIMIT"

**Solution:**

- You've exceeded your API quota
- Check Google Cloud Console for usage
- Consider upgrading your plan or enabling billing

## Testing Your Setup

### Quick Test Checklist:

1. ✅ API key added to wp-config.php
2. ✅ Location has valid address information
3. ✅ Check "Force Geocode" checkbox and save
4. ✅ Verify lat/lng fields are populated
5. ✅ Test ZIP code search on frontend

### Frontend ZIP Search Test:

1. Go to your locations archive page
2. Enter a ZIP code (e.g., "10001")
3. Click "Search"
4. Locations should display sorted by distance
5. Distance should appear next to location names

## API Key Security

### Best Practices:

- Restrict API key to your domain only
- Enable only necessary APIs (Geocoding API)
- Monitor usage in Google Cloud Console
- Set up billing alerts if using paid tier

### API Restrictions Settings:

1. In Google Cloud Console, edit your API key
2. Under "Application restrictions" → "HTTP referrers"
3. Add: `yourdomain.com/*` and `*.yourdomain.com/*`

## Cost Management

### Free Tier:

- Google provides $200/month free credit
- Geocoding API: $5 per 1,000 requests
- Free tier = ~40,000 geocoding requests/month

### Our Optimizations:

- ZIP code coordinates are cached for 24 hours
- Location coordinates stored permanently after first geocode
- Minimal API calls during normal operation

## Need Help?

### Error Log Location:

- Usually in `/wp-content/debug.log`
- Or check cPanel → Error Logs
- Look for entries containing "Geocoding" or "lvl_geocode_location"

### Debug Mode:

Add to wp-config.php to enable detailed logging:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```
