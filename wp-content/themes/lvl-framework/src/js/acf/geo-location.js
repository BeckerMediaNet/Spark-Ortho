class GeoLocation {
  constructor() {
    this.init();
  }

  init() {
    if (typeof acf !== "undefined") {
      this.setupGeocodeListener();
    } else {
      console.error("ACF is not defined");
    }
  }

  setupGeocodeListener() {
    const forceGeocode = acf.getField("field_force_geocode");
    if (forceGeocode) {
      forceGeocode.on("click", (e) => {
        if (e.target.checked) {
          this.geocodeLocation();
        }
      });
    }
  }

  geocodeLocation() {
    const street = acf.getField("field_location_street").val();
    const city = acf.getField("field_location_city").val();
    const state = acf.getField("field_location_state").val();
    const postalcode = acf.getField("field_location_postalcode").val();
    const country = acf.getField("field_location_country").val();

    const latField = acf.getField("field_6627e5261159c");
    const longField = acf.getField("field_6627e5521159d");

    latField.disable();
    longField.disable();

    document.body.classList.add("--waiting");
    document.body.style.cursor = "wait";

    const data = new FormData();
    data.append("action", "lvl_get_location_lat_long");
    data.append("street", street);
    data.append("city", city);
    data.append("state", state);
    data.append("postalcode", postalcode);
    data.append("country", country);

    if (!app_localized?.ajax_url) {
      console.error("Ajax URL not defined");
      return;
    }

    let isError = false;

    fetch(app_localized?.ajax_url, {
      method: "POST",
      credentials: "same-origin",
      body: data,
    })
      .then((response) => {
        if (!response.ok) {
          console.log("Error getting locations");
          isError = true;
          return;
        }
        return response.json();
      })
      .then((response) => {
        if (response) {
          latField.val(response?.lat);
          longField.val(response?.long);
        }
        if (response?.status?.status === "error") {
          isError = true;
        }
      })
      .catch((error) => {
        console.error("Error getting locations", error);
        isError = true;
      })
      .finally(() => {
        latField.enable();
        longField.enable();

        document.body.classList.remove("--waiting");
        document.body.style.cursor = "default";

        const forceGeocode = acf.getField("field_force_geocode");
        forceGeocode.val(0);
        forceGeocode.$el.find(".-on").removeClass("-on");

        if (wp?.data) {
          if (isError) {
            wp.data
              .dispatch("core/notices")
              .createErrorNotice(
                "Error geocoding location: " + response?.status?.message,
                {
                  type: "snackbar",
                  isDismissible: true,
                }
              );
          } else {
            wp.data
              .dispatch("core/notices")
              .createSuccessNotice("Geocoding complete", {
                type: "snackbar",
                isDismissible: true,
              });
          }
        }
      });
  }
}

export default GeoLocation;
