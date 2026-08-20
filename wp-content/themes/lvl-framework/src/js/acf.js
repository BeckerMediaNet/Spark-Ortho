import AcfColorPalette from "./acf/colors";
import GeoLocation from "./acf/geo-location";


document.addEventListener('DOMContentLoaded', function () {

    // check if ACF is defined and define color palette
    if (typeof acf !== 'undefined') {

        // Color Palette
        new AcfColorPalette();

        // Location Geocoding
        new GeoLocation();

    } else {
        console.error('ACF is not defined')
    }

});