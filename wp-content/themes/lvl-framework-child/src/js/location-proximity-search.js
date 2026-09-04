/**
 * Enhanced Location Search with IP-based auto-detection
 * Handles ZIP code proximity search with 3 nearest/500-mile business rules
 */

class LocationProximitySearch {
  constructor() {
    this.form = document.getElementById("locationSearchForm");
    this.zipInput = document.getElementById("zipInput");
    this.useLocationBtn = document.getElementById("useMyLocationBtn");
    this.searchBtn = document.getElementById("searchBtn");
    this.searchStatus = document.getElementById("searchStatus");
    this.locationsGrid = document.getElementById("locationsGrid");
    this.searchResultsInfo = document.getElementById("searchResultsInfo");
    this.loadMoreBtn = document.querySelector(".load-more-locations");

    this.init();
  }

  init() {
    if (!this.form) return;

    this.form.addEventListener("submit", (e) => this.handleFormSubmit(e));
    this.useLocationBtn.addEventListener("click", () =>
      this.useCurrentLocation()
    );

    // Enhanced Load More handling
    if (this.loadMoreBtn) {
      this.loadMoreBtn.addEventListener("click", (e) => this.handleLoadMore(e));
    }

    // Check if page loaded with ZIP parameter and show search results
    this.checkInitialZipSearch();
  }

  /**
   * Handle form submission
   */
  async handleFormSubmit(e) {
    e.preventDefault();

    const zip = this.zipInput.value.trim();

    if (!this.isValidZip(zip)) {
      this.showStatus("Please enter a valid 5-digit ZIP code.", "error");
      return;
    }

    await this.searchLocationsByZip(zip);
  }

  /**
   * Use current location (Browser geolocation first, then IP-based fallback)
   */
  async useCurrentLocation() {
    this.showStatus("Detecting your location...", "loading");
    this.useLocationBtn.disabled = true;

    // Try browser geolocation first
    if ("geolocation" in navigator) {
      console.log("🌍 Location Detection: Attempting browser geolocation");
      try {
        const position = await this.getBrowserLocation();
        console.log("✅ Browser geolocation successful:", {
          lat: position.coords.latitude,
          lng: position.coords.longitude,
          accuracy: `${position.coords.accuracy}m`,
        });
        await this.handleGeolocationSuccess(position);
        return;
      } catch (error) {
        console.warn(
          "⚠️ Browser geolocation failed, trying IP fallback:",
          error.message
        );
      }
    } else {
      console.warn("⚠️ Browser geolocation not available, using IP detection");
    }

    // Fallback to IP-based detection
    try {
      await this.useIPLocation();
    } catch (error) {
      console.error("❌ All location detection methods failed:", error.message);
      this.showStatus(
        "Could not detect your location. Please enter your ZIP code manually.",
        "error"
      );
    } finally {
      this.useLocationBtn.disabled = false;
    }
  }

  /**
   * Get browser geolocation
   */
  getBrowserLocation() {
    return new Promise((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: false,
        timeout: 10000,
        maximumAge: 300000,
      });
    });
  }

  /**
   * Handle successful geolocation
   */
  async handleGeolocationSuccess(position) {
    const { latitude, longitude } = position.coords;
    console.log("🔍 Geocoding coordinates to ZIP code...");

    // Use Google Maps Geocoding API to convert coordinates to ZIP code
    try {
      const response = await fetch(locationsAjax.ajaxUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "geocode_coordinates",
          lat: latitude,
          lng: longitude,
          nonce: locationsAjax.nonce,
        }),
      });

      const data = await response.json();

      if (data.success && data.data.zip) {
        console.log("✅ Geocoding successful:", {
          zip: data.data.zip,
          city: data.data.city,
          state: data.data.state,
        });
        this.zipInput.value = data.data.zip;
        this.showStatus(
          `Located you in ${data.data.city}, ${data.data.state} ${data.data.zip}`,
          "success"
        );

        // Auto-search with detected ZIP
        setTimeout(() => {
          this.searchLocationsByZip(data.data.zip);
        }, 1000);
      } else {
        throw new Error(data.data || "Geocoding failed");
      }
    } catch (error) {
      console.error("❌ Geocoding failed:", error.message);
      this.showStatus(
        "Could not determine your ZIP code from location. Please enter it manually.",
        "error"
      );
    }
  }

  /**
   * IP-based location detection (fallback)
   */
  async useIPLocation() {
    console.log("🌐 Attempting IP-based location detection (fallback)");

    try {
      const response = await fetch(locationsAjax.ajaxUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "get_user_location_from_ip",
          nonce: locationsAjax.nonce,
        }),
      });

      const data = await response.json();

      if (data.success && data.data.zip) {
        console.log("✅ IP location successful:", {
          zip: data.data.zip,
          city: data.data.city,
          state: data.data.state,
        });
        this.zipInput.value = data.data.zip;
        this.showStatus(
          `Located you in ${data.data.city}, ${data.data.state} ${data.data.zip}`,
          "success"
        );

        // Auto-search with detected ZIP
        setTimeout(() => {
          this.searchLocationsByZip(data.data.zip);
        }, 1000);
      } else {
        console.warn("⚠️ IP location failed:", data.data || "Unknown error");
        throw new Error(data.data || "IP location detection failed");
      }
    } catch (error) {
      console.error("❌ IP location error:", error.message);
      throw error; // Re-throw to be caught by the main useCurrentLocation method
    }
  }

  /**
   * Search locations by ZIP code using enhanced proximity search
   */
  async searchLocationsByZip(zip) {
    this.showStatus("Searching for nearby locations...", "loading");
    this.searchBtn.disabled = true;

    try {
      const response = await fetch(locationsAjax.ajaxUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "search_locations_by_zip",
          zip: zip,
          nonce: locationsAjax.nonce,
        }),
      });

      const data = await response.json();

      if (data.success) {
        this.displaySearchResults(data.data);
        this.showStatus("", "hidden");

        // Update URL without page reload
        const url = new URL(window.location);
        url.searchParams.set("zip", zip);
        window.history.replaceState(null, "", url);
      } else {
        this.showStatus(
          data.data || "No locations found for that ZIP code.",
          "error"
        );
      }
    } catch (error) {
      console.error("Search error:", error);
      this.showStatus("Search failed. Please try again.", "error");
    } finally {
      this.searchBtn.disabled = false;
    }
  }

  /**
   * Display search results with enhanced information
   */
  displaySearchResults(results) {
    this.locationsGrid.innerHTML = results.locations_html;

    // Show and update search results info
    this.searchResultsInfo.style.display = "flex";

    const ruleExplanation =
      results.rule_applied === "3_nearest"
        ? `Showing ${results.locations_shown} nearest locations`
        : `Showing closest location only (${results.closest_distance} miles away - all locations are 500+ miles)`;

    this.searchResultsInfo.innerHTML = `
            <div class="search-results-summary mb-4">
                <h3>Locations near ${results.user_zip}</h3>
                <p>${ruleExplanation}. Closest location is ${results.closest_distance} miles away.</p>
                <p><button class="btn btn-primary-outline btn-sm mt-2" onclick="window.locationSearch.loadAllLocations()">View All Locations</button></p>
            </div>
        `;

    // Hide load more button during ZIP search (proximity search has its own logic)
    if (this.loadMoreBtn) {
      this.loadMoreBtn.style.display = "none";
    }

    // Scroll to results
    this.locationsGrid.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }

  /**
   * Handle Load More button click
   */
  handleLoadMore(e) {
    e.preventDefault();

    const currentUrl = new URL(window.location);
    const hasZipSearch = currentUrl.searchParams.has("zip");

    if (hasZipSearch) {
      this.loadAllLocations();
    } else {
      return;
    }
  }

  /**
   * Load all locations (clear ZIP search)
   */
  async loadAllLocations() {
    this.zipInput.value = "";

    // Hide search results info
    if (this.searchResultsInfo) {
      this.searchResultsInfo.style.display = "none";
    }

    // Update URL to remove ZIP parameter
    const url = new URL(window.location);
    url.searchParams.delete("zip");
    window.history.replaceState(null, "", url);

    // Show loading state
    this.showStatus("Loading all locations...", "loading");

    try {
      // Use the existing filter_locations AJAX endpoint to get all locations
      const response = await fetch(locationsAjax.ajaxUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "filter_locations",
          paged: 1,
          nonce: locationsAjax.nonce,
        }),
      });

      const data = await response.json();

      if (data.html) {
        this.locationsGrid.innerHTML = data.html;

        // Show/hide load more button based on whether there are more pages
        if (this.loadMoreBtn) {
          if (data.has_more) {
            this.loadMoreBtn.style.display = "flex";
            this.loadMoreBtn.setAttribute("data-next-page", data.next_page);
          } else {
            this.loadMoreBtn.style.display = "none";
          }
        }

        this.showStatus("", "hidden");
      } else {
        this.showStatus("Failed to load locations.", "error");
      }
    } catch (error) {
      console.error("Load all locations error:", error);
      this.showStatus("Failed to load locations.", "error");
    }
  }

  /**
   * Check if page loaded with ZIP parameter and show search results
   */
  checkInitialZipSearch() {
    const urlParams = new URLSearchParams(window.location.search);
    const zip = urlParams.get("zip");

    if (zip && this.isValidZip(zip)) {
      // Page loaded with ZIP search - fetch results and show summary
      this.displayInitialZipResults(zip);
    }
  }

  /**
   * Display search results summary for initial page load with ZIP
   */
  async displayInitialZipResults(zip) {
    try {
      const response = await fetch(locationsAjax.ajaxUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "search_locations_by_zip",
          zip: zip,
          nonce: locationsAjax.nonce,
        }),
      });

      const data = await response.json();

      if (data.success) {
        // Just show the search results summary
        this.searchResultsInfo.style.display = "flex";

        const ruleExplanation =
          data.data.rule_applied === "3_nearest"
            ? `Showing ${data.data.locations_shown} nearest locations`
            : `Showing closest location only (${data.data.closest_distance} miles away - all locations are 500+ miles)`;

        this.searchResultsInfo.innerHTML = `
          <div class="search-results-summary mb-4">
            <h3>Locations near ${data.data.user_zip}</h3>
            <p>${ruleExplanation}. Closest location is ${data.data.closest_distance} miles away.</p>
            <p><button class="btn btn-primary-outline btn-sm mt-2" onclick="window.locationSearch.loadAllLocations()">View All Locations</button></p>
          </div>
        `;

        // Hide load more button during ZIP search
        if (this.loadMoreBtn) {
          this.loadMoreBtn.style.display = "none";
        }
      }
    } catch (error) {
      console.error("Error loading initial ZIP results:", error);
    }
  }

  /**
   * Update search results info with new data (called from service filtering)
   */
  updateSearchResultsInfo(data) {
    if (!this.searchResultsInfo || !data.zip_code) return;

    // Only update if we have proximity search data
    if (data.search_type === 'proximity' || data.has_combined_filters) {
      this.searchResultsInfo.style.display = "flex";

      // Handle case where no results are found after filtering
      if (data.locations_shown === 0 || !data.closest_distance) {
        this.searchResultsInfo.innerHTML = `
          <div class="search-results-summary mb-4">
            <h3>Locations near ${data.zip_code}</h3>
            <p>No locations found matching your criteria.</p>
            <p><button class="btn btn-primary-outline btn-sm mt-2" onclick="window.locationSearch.loadAllLocations()">View All Locations</button></p>
          </div>
        `;
        return;
      }

      const ruleExplanation =
        data.rule_applied === "3_nearest"
          ? `Showing ${data.locations_shown} nearest locations`
          : `Showing closest location only (${data.closest_distance} miles away - all locations are 500+ miles)`;

      // Use the actual closest distance from the filtered results
      const closestDistance = Math.round(data.closest_distance * 10) / 10; // Round to 1 decimal
      
      this.searchResultsInfo.innerHTML = `
        <div class="search-results-summary mb-4">
          <h3>Locations near ${data.zip_code}</h3>
          <p>${ruleExplanation}. Closest location is ${closestDistance} miles away.</p>
          <p><button class="btn btn-primary-outline btn-sm mt-2" onclick="window.locationSearch.loadAllLocations()">View All Locations</button></p>
        </div>
      `;
    }
  }

  /**
   * Show status message
   */
  showStatus(message, type = "info") {
    this.searchStatus.textContent = message;
    this.searchStatus.className = `search-status ${type}`;

    if (type === "hidden") {
      this.searchStatus.style.display = "none";
    } else {
      this.searchStatus.style.display = "flex";
    }

    // Auto-hide success messages
    if (type === "success") {
      setTimeout(() => {
        this.showStatus("", "hidden");
      }, 3000);
    }
  }

  /**
   * Validate ZIP code format
   */
  isValidZip(zip) {
    return /^\d{5}$/.test(zip);
  }
}

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  // Check if we're on the locations archive page
  if (document.getElementById("locationSearchForm")) {
    // Make instance globally accessible for Load More integration
    window.locationSearch = new LocationProximitySearch();
  }
});
