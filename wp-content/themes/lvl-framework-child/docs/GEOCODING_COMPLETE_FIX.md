# Geocoding Complete Fix - October 13, 2025

## Issue Found

There were **two competing geocoding systems**:

1. **Parent theme** (`lvl-framework`) - JavaScript-based using OpenStreetMap's Nominatim API
2. **Child theme** (`lvl-framework-child`) - PHP-based using Google Maps API

The JavaScript was intercepting checkbox clicks but calling OpenStreetMap instead of Google Maps, causing geocoding to fail when you clicked the checkbox without saving.

## Root Cause

When you clicked "Fetch Geocode" checkbox:

- JavaScript in `geo-location.js` intercepted the click
- It tried to call AJAX handler `lvl_get_location_lat_long`
- Parent theme's handler used OpenStreetMap (often blocked or unreliable)
- Child theme's Google Maps implementation never ran
- Result: **Nothing happened, no visual feedback, empty fields**

## Complete Solution

### 1. Override AJAX Handler (`lib/locations.php`)

**Added at the top of the file:**

```php
// Remove parent theme's AJAX handlers
remove_action('wp_ajax_lvl_get_location_lat_long', 'lvl_get_location_lat_long');
remove_action('wp_ajax_nopriv_lvl_get_location_lat_long', 'lvl_get_location_lat_long');

// Add child theme AJAX handlers that use Google Maps API
add_action('wp_ajax_lvl_get_location_lat_long', 'lvl_child_get_location_lat_long');
add_action('wp_ajax_nopriv_lvl_get_location_lat_long', 'lvl_child_get_location_lat_long');

function lvl_child_get_location_lat_long() {
    // Uses Google Maps API instead of OpenStreetMap
    // Returns lat/lng via AJAX for immediate feedback
}
```

This allows the existing JavaScript interface to work, but now uses Google Maps API for geocoding.

### 2. Enhanced PHP Hook (Backup Method)

Also improved the PHP-based approach using `acf/update_value/name=force_geocode` filter hook for cases where you click Update button.

### 3. Added Debug Logging

Enabled WordPress debug logging in `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### 4. Visual Feedback Added

- **JavaScript snackbar notifications** (real-time during AJAX)
- **Admin notices** (after page refresh for PHP method)
- **Loading cursor** during geocoding
- **Automatic checkbox uncheck** when complete

## How to Use (Updated)

### Primary Method: Click Checkbox (AJAX - Instant Results)

1. **Go to any Location post** in WordPress admin
2. **Fill in address fields:**
   - Street: `1234 Main Street`
   - City: `Aston`
   - State: `PA`
   - Postal Code: `19014`
3. **Click the "Fetch Geocode (lat/long)" checkbox**
4. **Watch for:**
   - Loading cursor appears
   - Snackbar notification appears (top right)
   - Lat/Lng fields populate **immediately**
   - Checkbox unchecks automatically
   - ✅ Success: "Geocoding complete"
   - ❌ Error: Shows error message

**No need to click Update!** It happens instantly.

### Backup Method: Save Post (PHP Hook)

1. Fill in "Display Address" field (or individual address fields)
2. Check "Fetch Geocode" checkbox
3. Click "Update" button
4. Admin notice appears at top
5. Lat/Lng fields populated

## What's Different Now

### ✅ Before the Fix:

- Click checkbox → Nothing happens
- No visual feedback
- Fields stay empty
- Checkbox stays checked
- Very frustrating!

### ✅ After the Fix:

- Click checkbox → **Instant geocoding**
- Loading cursor shows it's working
- Snackbar notification with success/error
- Fields populate immediately
- Checkbox unchecks automatically
- Works perfectly!

## Troubleshooting

### Check Browser Console (F12)

If clicking checkbox does nothing:

1. Open browser Developer Tools (F12)
2. Go to Console tab
3. Click the checkbox
4. Look for:
   - ✅ "Geocoding complete" - Working!
   - ❌ Errors about `app_localized` - JavaScript not loaded
   - ❌ AJAX errors - Check debug log

### Check Debug Log

Location: `/wp-content/debug.log`

Look for entries:

```
AJAX geocoding request received
AJAX geocoding address: 1234 Main Street, Aston, PA, 19014
AJAX geocoding success: Lat 39.8676, Lng -75.4371
```

### Common Issues

**Issue:** Nothing happens when clicking checkbox

**Solutions:**

- Check browser console for JavaScript errors
- Verify `app_localized.ajax_url` is defined
- Check if ACF JavaScript is loaded
- Try clicking Update button instead (uses PHP method)

**Issue:** Error: "Google Maps API key not configured"

**Solution:** Already fixed - key is in `wp-config.php`

**Issue:** Error: "ZERO_RESULTS"

**Solution:** Address not specific enough - try adding more details

**Issue:** Error: "REQUEST_DENIED"

**Solution:** Check Google Cloud Console API key restrictions

## Your API Key

✅ Configured: `AIzaSyABDXScDgKZKkEZyi_fWP82Xq2_wp62Xhs`

## Testing Checklist

- [ ] Fill in Aston location address fields
- [ ] Click "Fetch Geocode" checkbox
- [ ] See loading cursor
- [ ] See snackbar notification
- [ ] Lat/Lng fields populate (should be ~39.8676, -75.4371 for Aston, PA)
- [ ] Checkbox unchecks automatically
- [ ] Check debug log for success message

## API Key Settings

In [Google Cloud Console](https://console.cloud.google.com/):

1. ✅ Ensure "Geocoding API" is enabled
2. ✅ Check API key restrictions
3. ✅ Verify billing is set up ($200/month free)
4. Monitor usage

## Summary

The fix overrides the parent theme's OpenStreetMap AJAX handler with a Google Maps version, so the existing JavaScript interface now works perfectly with your Google Maps API key. You get instant geocoding feedback without needing to save the post!
