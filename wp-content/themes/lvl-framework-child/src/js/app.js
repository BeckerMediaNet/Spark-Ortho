window.onscroll = function () {
  progressBar();
};

window.onload = function () {
  progressBar();
};

const progressBar = function () {
  let progressBar = document.querySelector("#progressBar");

  if (!progressBar) {
    return;
  }

  let winScroll = document.body.scrollTop || document.documentElement.scrollTop;
  let height =
    document.documentElement.scrollHeight -
    document.documentElement.clientHeight;
  let scrolled = (winScroll / height) * 100;
  progressBar.style.width = scrolled + "%";
};

window.onload = function () {
  main_nav_hover();
};

function main_nav_hover() {
  const dropdowns = document.querySelectorAll(".main-nav .dropdown");

  dropdowns.forEach((dropdown) => {
    dropdown.addEventListener("mouseenter", function () {
      if (!dropdown.classList.contains("show")) {
        dropdown.classList.add("show");
        document.querySelector("body").classList.add("nav-open");
      }
    });

    dropdown.addEventListener("mouseleave", function () {
      if (dropdown.classList.contains("show")) {
        dropdown.classList.remove("show");
        document.querySelector("body").classList.remove("nav-open");
      }
    });
  });
}

// Helper functions for filter management
function clearZipSearch() {
  var $zipInput = $("#zipInput");
  if ($zipInput.length) {
    $zipInput.val("");
  }

  // Clear URL zip parameter
  var url = new URL(window.location);
  url.searchParams.delete("zip");
  window.history.replaceState(null, "", url);

  // Hide search results info
  var $searchResultsInfo = $("#searchResultsInfo");
  if ($searchResultsInfo.length) {
    $searchResultsInfo.hide();
  }
}

// Clear service filter checkboxes and reload all locations
function clearServiceFilter() {
  var $serviceForm = $(".service-filter-form");
  $serviceForm.find('input[type="checkbox"]').prop("checked", false);

  // Also clear ZIP search when clearing services
  clearZipSearch();

  // Fetch all locations without filters
  var $listings = $(".locations-listings .locations-grid");
  $.ajax({
    url: ajax_params.ajax_url,
    type: "POST",
    data: {
      action: "filter_locations",
      service: [],
      zip: "",
      paged: 1,
      nonce: ajax_params.nonce,
    },
  })
    .done(function(response) {
      var data = response.data ? response.data : response;
      
      // Update results
      $listings.html(data.html);
      
      // Update result count
      var $resultCount = $(".locations-count .count");
      if ($resultCount.length) {
        $resultCount.text(data.total_found + ' locations found');
      }
      
      // Hide proximity search results info when clearing all filters
      if (window.locationSearch && window.locationSearch.searchResultsInfo) {
        window.locationSearch.searchResultsInfo.style.display = "none";
      }
    })
    .fail(function() {
      console.log("Error clearing filters");
    });
}
window.clearServiceFilter = clearServiceFilter;

// Update filter indicators based on search results
function updateFilterIndicators(data) {
  var $filterStatus = $('.filter-status');
  
  // Create filter status element if it doesn't exist
  if ($filterStatus.length === 0) {
    $filterStatus = $('<div class="filter-status mb-3"></div>');
    $('.locations-count').after($filterStatus);
  }
  
  var indicators = [];
  
  // ZIP code indicator
  if (data.zip_code && data.zip_code.trim()) {
    indicators.push('<span class="filter-indicator zip-filter">📍 Near ZIP: ' + data.zip_code + '</span>');
  }
  
  // Service filter indicators
  if (data.service_filters && data.service_filters.length > 0) {
    var serviceCount = data.service_filters.length;
    var serviceText = serviceCount === 1 ? '1 service' : serviceCount + ' services';
    indicators.push('<span class="filter-indicator service-filter">🔍 Filtered by ' + serviceText + '</span>');
  }
  
  // Combined filter indicator
  if (data.has_combined_filters) {
    indicators.push('<span class="filter-indicator combined-filter">⚡ Combined search active</span>');
  }
  
  if (indicators.length > 0) {
    $filterStatus.html('<div class="active-filters">' + indicators.join(' ') + '</div>').show();
  } else {
    $filterStatus.hide();
  }
}

// Locations Archive AJAX
(function ($) {
  var $form = $(".location-search-form, .service-filter-form");
  var $listings = $(".locations-listings .locations-grid");
  var $loadMore = $(".load-more-locations");

  function fetchLocations(params, append) {
    // Add loading state if not appending (fresh search)
    if (!append) {
      $listings.addClass("loading");
    }
    
    $.ajax({
      url: locations_ajax.ajax_url,
      type: "POST",
      data: $.extend(
        {
          action: "filter_locations",
        },
        params
      ),
      success: function (res) {
        // Remove loading state
        $listings.removeClass("loading");
        
        if (append) {
          $listings.append(res.html);
        } else {
          $listings.html(res.html);
        }
        if (res.has_more) {
          $loadMore.show().data("next-page", res.next_page);
        } else {
          $loadMore.hide();
        }
        
        // Update filter indicators
        updateFilterIndicators(res);
        
        // Update proximity search info if available
        if (window.locationSearch && window.locationSearch.updateSearchResultsInfo) {
          window.locationSearch.updateSearchResultsInfo(res);
        }
      },
      error: function (xhr, status, error) {
        // Remove loading state on error
        $listings.removeClass("loading");
        
        // Handle error silently or show message
        if (!append) {
          $listings.html('<p>Error loading locations. Please try again.</p>');
        }
      },
    });
  }

  $form.on("submit", function (e) {
    e.preventDefault();
    var params = {};
    var $currentForm = $(this);

    // Check if this is the service filter form
    var isServiceFilter = $currentForm.hasClass("service-filter-form");

    if (isServiceFilter) {
      // DON'T clear ZIP search - maintain both ZIP and service state
      
      // Get current ZIP value if present
      var zipValue = $("#zipInput").val();
      if (zipValue && zipValue.trim()) {
        params.zip = zipValue.trim();
      }

      // Get only checked services from this form
      $currentForm.find('input[name="service[]"]:checked').each(function () {
        if (!params.service) params.service = [];
        params.service.push($(this).val());
      });
    } else {
      // For ZIP search: maintain existing service selections
      
      // Get existing service selections
      var checkedServices = [];
      $(".service-filter-form input[name='service[]']:checked").each(function () {
        checkedServices.push($(this).val());
      });
      if (checkedServices.length > 0) {
        params.service = checkedServices;
      }

      // Get form data normally
      var formData = $currentForm.serializeArray();
      formData.forEach(function (item) {
        if (item.name === "service[]") {
          // Skip - we already have services from above
        } else if (item.value) {
          params[item.name] = item.value;
        }
      });
    }

    fetchLocations(params, false);
  });

  $loadMore.on("click", function (e) {
    e.preventDefault();

    // Check if we have the new proximity search system active
    if (window.locationSearch && window.locationSearch.handleLoadMore) {
      window.locationSearch.handleLoadMore(e);

      // If it was a ZIP search, the new system handles it completely
      const currentUrl = new URL(window.location);
      if (currentUrl.searchParams.has("zip")) {
        return;
      }
    }

    // Original Load More behavior for regular pagination
    var params = collectFormData(null);
    params.paged = $(this).data("next-page");

    fetchLocations(params, true);
  });

  // Helper function to collect form data
  function collectFormData($form) {
    var params = {};
    
    // Collect service checkboxes
    var checkedServices = [];
    $(".service-filter-form input[name='service[]']:checked").each(function () {
      checkedServices.push($(this).val());
    });
    
    if (checkedServices.length > 0) {
      params.service = checkedServices;
    }
    
    // Get ZIP if present
    var zipValue = $("#zipInput").val();
    if (zipValue && zipValue.trim()) {
      params.zip = zipValue.trim();
    }
    
    return params;
  }

  // Service filter form submission (no auto-submit on checkbox change)
  $(".service-filter-form").on("submit", function (e) {
    e.preventDefault();
    var $form = $(this);
    
    // Reset pagination when filtering
    currentPage = 1;
    
    // Clear existing results
    $("#locationsGrid").empty();
    
    // Collect form data and submit
    var formData = collectFormData($form);
    formData.paged = 1;
    
    fetchLocations(formData, false);
  });
})(jQuery);

// Platform-specific Map Links
document.addEventListener("DOMContentLoaded", function () {
  function initMapLinks() {
    const mapLinks = document.querySelectorAll(".map-link");

    mapLinks.forEach(function (link) {
      const address = link.getAttribute("data-address");
      const encodedAddress = encodeURIComponent(address);

      // Detect platform
      const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
      const isAndroid = /Android/.test(navigator.userAgent);

      let mapUrl;
      if (isIOS) {
        // iOS: Use Apple Maps
        mapUrl = `https://maps.apple.com/?q=${encodedAddress}`;
      } else if (isAndroid) {
        // Android: Use Google Maps
        mapUrl = `https://maps.google.com/?q=${encodedAddress}`;
      } else {
        // Desktop/Other: Use Google Maps
        mapUrl = `https://maps.google.com/?q=${encodedAddress}`;
      }

      link.href = mapUrl;
    });
  }

  initMapLinks();

  // Re-initialize after AJAX content loads (for filtered results)
  $(document).on("ajaxComplete", function () {
    setTimeout(initMapLinks, 100);
  });
});

// Custom Pay my Bill Gform Submit Handler (opens value in a new tab format: https://bill.care/practice/xxxx)
jQuery(document).ready(function ($) {
  $("#gform_2").on("submit", function (e) {
    e.preventDefault();

    var $form = $(this);
    var selectedUrl = $("#input_2_1").val();

    if (selectedUrl && selectedUrl !== "") {
      window.open(selectedUrl, "_blank");

      // Properly reset the form state to stop spinner
      $form.find('.gform_footer input[type="submit"]').prop("disabled", false);
      $form.find(".gform-loader").remove();

      if (typeof gformInitSpinner !== "undefined") {
        gformInitSpinner(2, false);
      }

      $form.trigger("gform_post_render", [2, 1]);
    }

    return false;
  });
});

document.addEventListener('DOMContentLoaded', function() {
  const tables = document.querySelectorAll('.wp-block-table');
  
  tables.forEach(table => {
    const tableElement = table.querySelector('table');
    
    // Check if table needs scrolling
    function checkScrollable() {
      const isScrollable = table.scrollWidth > table.clientWidth;
      table.setAttribute('data-scrollable', isScrollable);
      
      // Add ARIA label for screen readers
      if (isScrollable) {
        table.setAttribute('aria-label', 'Scrollable table. Use arrow keys or swipe to see more content.');
        table.setAttribute('tabindex', '0');
        table.setAttribute('role', 'region');
      }
    }
    
    // Hide scroll indicator after user interacts
    function hideScrollIndicator() {
      table.classList.add('scrolled');
    }
    
    // Event listeners
    table.addEventListener('scroll', hideScrollIndicator);
    table.addEventListener('touchstart', hideScrollIndicator);
    table.addEventListener('mousedown', hideScrollIndicator);
    
    // Keyboard navigation
    table.addEventListener('keydown', function(e) {
      if (table.getAttribute('data-scrollable') === 'true') {
        switch(e.key) {
          case 'ArrowLeft':
            e.preventDefault();
            table.scrollLeft -= 50;
            hideScrollIndicator();
            break;
          case 'ArrowRight':
            e.preventDefault();
            table.scrollLeft += 50;
            hideScrollIndicator();
            break;
        }
      }
    });
    
    // Check on load and resize
    checkScrollable();
    window.addEventListener('resize', checkScrollable);
    
    // Make table cells focusable for better accessibility
    const cells = tableElement.querySelectorAll('th, td');
    cells.forEach(cell => {
      cell.setAttribute('tabindex', '0');
    });
  });
});
