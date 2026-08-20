# Geocoding Fix - October 13, 2025

## Issue Found

The geocoding wasn't working because the ACF hook `acf/save_post` was not reliably capturing the checkbox state before ACF saved it.

## Changes Made

### 1. Updated Hook Implementation (`lib/locations.php`)

**Changed from:** `acf/save_post` action hook  
**Changed to:** `acf/update_value/name=force_geocode` filter hook

This new approach:

- Intercepts the checkbox value **before** ACF saves it
- Automatically unchecks the checkbox by returning `false`
- Schedules the geocoding to run after ACF finishes saving all fields
- More reliable timing for field updates

### 2. Added Debug Logging (`wp-config.php`)

Enabled WordPress debug logging to help troubleshoot:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Debug log location: `/wp-content/debug.log`

### 3. Enhanced Error Logging (`lib/locations.php`)

Added detailed logging throughout the geocoding process:

- When geocoding starts
- API request details
- Success/failure of ACF field updates
- Verification of saved values
- Full API response on errors

### 4. Added Admin Notices

You'll now see visual feedback in the WordPress admin:

- **Green success notice:** Shows lat/lng values when geocoding succeeds
- **Red error notice:** Alerts when geocoding fails with troubleshooting tips

## How to Test

1. **Go to any Location post** in WordPress admin
2. **Ensure the location has an address:**
   - Either fill in the "Display Address" field, OR
   - Fill in Street, City, State, Postal Code fields
3. **Check the "Fetch Geocode (lat/long)" checkbox**
4. **Click "Update"**
5. **Look for:**
   - Admin notice at the top (success or error)
   - Lat/Lng fields should populate automatically
   - Checkbox should uncheck itself

## Troubleshooting

### If it still doesn't work:

1. **Check the debug log:** `/wp-content/debug.log`

   - Look for entries starting with "Starting geocoding"
   - Look for error messages

2. **Common issues logged:**

   - "No address found" - Fill in address fields
   - "API key not defined" - Already fixed in your config
   - "ZERO_RESULTS" - Address not valid/specific enough
   - "OVER_QUERY_LIMIT" - API quota exceeded

3. **Verify API key is working:**
   - Log entry should show "Geocoding address for location ID X"
   - If you see "API request failed", check API key restrictions

### Debug Log Entries to Look For:

```
Starting geocoding for location ID: 123
Geocoding address for location ID 123: 123 Main St, City, State 12345
Geocoding successful - About to update ACF fields for location ID 123
Lat value: 40.7128, Lng value: -74.0060
ACF update results - lat: success, lng: success
Verification - Saved lat: 40.7128, Saved lng: -74.0060
Successfully geocoded location ID: 123 - Lat: 40.7128, Lng: -74.0060
```

## Your API Key

✅ Already configured in `wp-config.php`: `AIzaSyABDXScDgKZKkEZyi_fWP82Xq2_wp62Xhs`

## Next Steps

1. Test on a location with a valid address
2. Check for success/error notices
3. Review debug log if issues persist
4. Report any error messages you see

## API Key Settings to Verify

In [Google Cloud Console](https://console.cloud.google.com/):

1. Ensure "Geocoding API" is enabled
2. Check API key restrictions (should allow your domain)
3. Verify billing is set up (Google provides $200/month free)
4. Monitor usage to avoid quota issues
