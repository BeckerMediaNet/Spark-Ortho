import { Navigation, Pagination } from "swiper/modules";

document.addEventListener("DOMContentLoaded", () => {
  initializeTeamSliders();
});

function initializeTeamSliders() {
  const containers = document.querySelectorAll(
    ".team-slider-container:not(.slider-initialized)"
  );

  containers.forEach((container) => {
    const slider = container.querySelector(".team-slider.swiper");
    if (!slider) return;

    const block = container.closest(".block--team-cards");
    const paginationEl = block
      ? block.querySelector(".team-pagination")
      : null;

    try {
      new Swiper(slider, {
        modules: [Navigation, Pagination],
        slidesPerView: 1,
        spaceBetween: 24,
        loop: false,
        navigation: {
          nextEl: container.querySelector(".team-next"),
          prevEl: container.querySelector(".team-prev"),
        },
        pagination: paginationEl
          ? { el: paginationEl, clickable: true }
          : false,
        breakpoints: {
          768: {
            slidesPerView: 2,
            spaceBetween: 24,
          },
          992: {
            slidesPerView: 3,
            spaceBetween: 24,
          },
        },
        keyboard: { enabled: true },
        a11y: { enabled: true },
      });

      container.classList.add("slider-initialized");
    } catch (error) {
      console.error("Failed to initialize team slider:", error);
    }
  });
}

(function ($) {
  "use strict";

  class TeamFilter {
    constructor($block) {
      this.$block = $block;
      this.$grid = $block.find(".team-cards-flex");
      this.$loadMoreBtn = $block.find(".load-more-team");
      this.$resetBtn = $block.find(".filter-reset");
      this.$spinner = $block.find(".team-spinner");
      this.$filteredDiv = $block.find(".filtered");
      this.perPage = parseInt($block.data("per-page")) || 12;
      this.imageFit = $block.data("image-fit") || "cover";
      this.filterByTerms = $block.data("filter-by-terms") || "";
      this.currentPage = 1;
      this.isLoading = false;
      this.currentFilters = {};

      this.init();
    }

    init() {
      this.loadTeam(false);

      // Filter submit button
      this.$block.find(".filter-submit").on("click", (e) => {
        e.preventDefault();

        // Handle keyword input
        const keywordInput = this.$block.find('input[name="keyword"]');
        const keywordValue = keywordInput.val().trim();

        if (keywordValue) {
          keywordInput.val(""); // Clear input after adding filter (matches resources block)
          this.addFilter("keyword", keywordValue, `Keyword: "${keywordValue}"`);
        }

        this.currentPage = 1;
        this.loadTeam(false);
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
          const $input = $(e.target);
          const keyword = $input.val().trim();
          if (keyword) {
            $input.val(""); // Clear input after adding filter (matches resources block)
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
      this.loadTeam(false);
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
      this.loadTeam(false);
    }

    resetFilters() {
      this.$filteredDiv.empty();

      this.$block.find('input[name="keyword"]').val("");

      this.$resetBtn.addClass("d-none");

      this.currentPage = 1;
      this.loadTeam(false);
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
        message += `<h4>No team members found</h4>`;
        message += `<p class="mb-3">No team members match the keyword "<strong>${filtersApplied.keyword}</strong>"`;

        // Add additional filter context if other filters are applied
        if (filtersApplied.team_category && filtersApplied.team_category.length > 0) {
          message += ` with the selected categories`;
        }
        if (filtersApplied.team_location && filtersApplied.team_location.length > 0) {
          message += ` at the selected locations`;
        }
      } else if (filtersApplied && filtersApplied.has_filters) {
        message += `<h4>No team members found</h4>`;
        message += `<p class="mb-3">No team members match your current filter selection.</p>`;
      } else {
        message += `<h4>No team members found</h4>`;
        message += `<p>Please check back later or contact us for assistance.</p>`;
      }

      message += "</div></div>";

      this.$grid.html(message);
    }

    collectActiveFilters() {
      // Collect filter buttons as array of {type, value} objects (matches resources block)
      const filters = [];

      this.$filteredDiv.find(".btn-filter").each(function () {
        const $btn = $(this);
        filters.push({
          type: $btn.data("filter-type"),
          value: $btn.data("filter-value"),
        });
      });

      return filters;
    }

    loadMore() {
      this.currentPage++;
      this.loadTeam(true);
    }

    loadTeam(append = false) {
      if (this.isLoading) return;

      // Check if AJAX variables are available
      if (typeof team_cards_ajax === "undefined") {
        console.error(
          "TeamFilter: team_cards_ajax is not defined. Check script localization."
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

      // Send filters as JSON string (matches resources block pattern)
      const data = {
        action: "filter_team",
        filters: JSON.stringify(activeFilters),
        paged: this.currentPage,
        per_page: this.perPage,
        image_fit: this.imageFit,
        filter_by_terms: this.filterByTerms,
        nonce: team_cards_ajax.nonce,
      };

      $.ajax({
        url: team_cards_ajax.ajax_url,
        type: "POST",
        data: data,
        success: (response) => {
          // Handle response.html format
          if (response.html) {
            if (append) {
              this.$grid.append(response.html);
            } else {
              this.$grid.html(response.html);
            }
          }
          // Empty HTML is expected when no results match filters - handled by displayNoResultsMessage below

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
            $resultsCount.text(`Showing ${count} team members`);
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
            '<div class="col-12"><p class="text-center text-danger">Error loading team members. Please try again.</p></div>'
          );
        },
      });
    }
  }

  $(document).ready(function () {
    const $blocks = $(".team-cards-container[data-mode='all']");

    $blocks.each(function () {
      new TeamFilter($(this));
    });
  });
})(jQuery);
