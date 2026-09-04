(function ($) {
  "use strict";

  class DentistFilter {
    constructor($block) {
      this.$block = $block;
      this.$grid = $block.find(".dentist-cards-flex");
      this.$loadMoreBtn = $block.find(".load-more-dentists");
      this.$resetBtn = $block.find(".filter-reset");
      this.$spinner = $block.find(".dentist-spinner");
      this.$filteredDiv = $block.find(".filtered");
      this.perPage = parseInt($block.data("per-page")) || 12;
      this.imageFit = $block.data("image-fit") || "cover";
      this.currentPage = 1;
      this.isLoading = false;
      this.currentFilters = {};

      this.init();
    }

    init() {
      this.loadDentists(false);

      // Filter submit button
      this.$block.find(".filter-submit").on("click", (e) => {
        e.preventDefault();

        // Handle keyword input
        const keywordInput = this.$block.find('input[name="keyword"]');
        const keywordValue = keywordInput.val().trim();

        if (keywordValue) {
          const existingKeywordFilter = this.$filteredDiv.find(
            '.btn-filter[data-filter-type="keyword"]'
          );
          if (existingKeywordFilter.length === 0) {
            this.addFilter(
              "keyword",
              keywordValue,
              `Keyword: "${keywordValue}"`
            );
          }
        }

        this.currentPage = 1;
        this.loadDentists(false);
      });

      this.$resetBtn.on("click", (e) => {
        e.preventDefault();
        this.resetFilters();
      });

      this.$loadMoreBtn.on("click", (e) => {
        e.preventDefault();
        this.loadMore();
      });

      // Dropdown filter items
      this.$block.find(".dropdown-item").on("click", (e) => {
        e.preventDefault();
        const $item = $(e.target);
        const filterType = $item.data("filter-type");
        const filterValue = $item.data("filter-value");
        const filterText = $item.text().trim();

        this.addFilter(filterType, filterValue, filterText);
      });

      // Keyword input
      this.$block.find('input[name="keyword"]').on("keypress", (e) => {
        if (e.key === "Enter") {
          e.preventDefault();
          const keyword = $(e.target).val().trim();
          if (keyword) {
            this.addFilter("keyword", keyword, keyword);
          }
        }
      });

      // Remove filter buttons (delegated event)
      this.$filteredDiv.on("click", ".btn-filter", (e) => {
        e.preventDefault();
        this.removeFilter($(e.target));
      });
    }

    addFilter(type, value, text) {
      // Check if filter already exists
      const existingFilter = this.$filteredDiv.find(
        `[data-filter-type="${type}"][data-filter-value="${value}"]`
      );
      if (existingFilter.length === 0) {
        const filterBtn = $(
          `<button class="btn-filter" data-filter-type="${type}" data-filter-value="${value}">${text}</button>`
        );
        this.$filteredDiv.append(filterBtn);
      }

      // Show reset button
      this.$resetBtn.removeClass("d-none");

      this.currentPage = 1;
      this.loadDentists(false);
    }

    removeFilter($filterBtn) {
      const filterType = $filterBtn.data("filter-type");

      $filterBtn.remove();

      // Hide reset button if no filters remain
      if (this.$filteredDiv.find(".btn-filter").length === 0) {
        this.$resetBtn.addClass("d-none");
      }

      // Clear keyword input if it was a keyword filter
      if (filterType === "keyword") {
        this.$block.find('input[name="keyword"]').val("");
      }

      this.currentPage = 1;
      this.loadDentists(false);
    }

    resetFilters() {
      this.$filteredDiv.empty();

      this.$block.find('input[name="keyword"]').val("");

      this.$resetBtn.addClass("d-none");

      this.currentPage = 1;
      this.loadDentists(false);
    }

    displayNoResultsMessage(filtersApplied) {
      let message =
        '<div class="col-12"><div class="no-results-message text-center py-5">';

      if (
        filtersApplied &&
        filtersApplied.has_keyword &&
        filtersApplied.keyword
      ) {
        // Specific message for keyword searches
        message += `<h4>No dentists found</h4>`;
        message += `<p class="mb-3">No dentists match the keyword "<strong>${filtersApplied.keyword}</strong>"`;

        // Add additional filter context if other filters are applied
        if (filtersApplied.specialty && filtersApplied.specialty.length > 0) {
          message += ` with the selected specialties`;
        }
        if (filtersApplied.location && filtersApplied.location.length > 0) {
          message += ` at the selected locations`;
        }
      } else if (filtersApplied && filtersApplied.has_filters) {
        message += `<h4>No dentists found</h4>`;
        message += `<p class="mb-3">No dentists match your current filter selection.</p>`;
      } else {
        message += `<h4>No dentists found</h4>`;
        message += `<p>Please check back later or contact us for assistance.</p>`;
      }

      message += "</div></div>";

      this.$grid.html(message);
    }

    collectActiveFilters() {
      const filters = {};

      // Collect filter buttons
      this.$filteredDiv.find(".btn-filter").each(function () {
        const $btn = $(this);
        const type = $btn.data("filter-type");
        const value = $btn.data("filter-value");

        if (!filters[type]) {
          filters[type] = [];
        }
        filters[type].push(value);
      });

      // Also check the keyword input field directly
      const keywordInput = this.$block.find('input[name="keyword"]');
      if (keywordInput.length && keywordInput.val().trim()) {
        filters.keyword = keywordInput.val().trim();
      }

      return filters;
    }

    loadMore() {
      this.currentPage++;
      this.loadDentists(true);
    }

    loadDentists(append = false) {
      if (this.isLoading) return;

      // Check if AJAX variables are available
      if (typeof dentist_cards_ajax === "undefined") {
        console.error(
          "DentistFilter: dentist_cards_ajax is not defined. Check script localization."
        );
        this.$grid.html(
          '<div class="col-12"><p class="text-center text-danger">Configuration error. Please check script localization.</p></div>'
        );
        return;
      }

      this.isLoading = true;
      this.$spinner.removeClass("d-none");

      if (!append) {
        this.$grid.html("");
      }

      const activeFilters = this.collectActiveFilters();

      const data = {
        action: "filter_dentists",
        paged: this.currentPage,
        per_page: this.perPage,
        image_fit: this.imageFit,
        nonce: dentist_cards_ajax.nonce,
        ...activeFilters,
      };

      $.ajax({
        url: dentist_cards_ajax.ajax_url,
        type: "POST",
        data: data,
        success: (response) => {
          // Handle response.html format (matches testimonials pattern)
          if (response.html) {
            if (append) {
              this.$grid.append(response.html);
            } else {
              this.$grid.html(response.html);
            }
          } else {
            console.error("No HTML content in response:", response);
          }

          const count = response.total_posts || 0;

          // Handle no results case with specific messages
          if (count === 0 && !append) {
            this.displayNoResultsMessage(response.filters_applied);
          }

          // Show/hide load more button based on whether there are more pages
          if (response.has_more) {
            this.$loadMoreBtn.removeClass("d-none");
          } else {
            this.$loadMoreBtn.addClass("d-none");
          }

          // Update results count if element exists
          const $resultsCount = this.$block.find(".results-count");
          if ($resultsCount.length && count !== undefined) {
            $resultsCount.text(`Showing ${count} dentists`);
          }

          this.isLoading = false;
          this.$spinner.addClass("d-none");
        },
        error: (xhr, status, error) => {
          console.error("AJAX Error:", error);
          console.error("Status:", status);
          console.error("Response Text:", xhr.responseText);
          this.isLoading = false;
          this.$spinner.addClass("d-none");
          this.$grid.html(
            '<div class="col-12"><p class="text-center text-danger">Error loading dentists. Please try again.</p></div>'
          );
        },
      });
    }
  }

  $(document).ready(function () {
    const $blocks = $(".dentist-cards-container[data-mode='all']");

    $blocks.each(function () {
      new DentistFilter($(this));
    });
  });
})(jQuery);
