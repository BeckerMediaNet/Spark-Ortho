document.addEventListener("DOMContentLoaded", (e) => {
  const blocks = document.querySelectorAll(".block--testimonials");

  function create_block_testimonials() {
    return {
      block: null,

      init: function (block) {
        this.block = block;

        const testimonialCount =
          this.block.querySelectorAll(".testimonial").length;
        const isAllTestimonials = this.block.querySelector(
          ".testimonials.testimonials-grid"
        );

        // Only initialize slider if more than 3 testimonials AND not showing all testimonials
        if (testimonialCount > 3 && !isAllTestimonials) {
          this.slider();
        }
      },

      slider: function () {
        const target = this.block.querySelector(".testimonials");
        target.classList.add("swiper-wrapper");

        const wrapper = document.createElement("div");
        wrapper.classList.add("swiper");

        target.parentNode.insertBefore(wrapper, target);
        wrapper.appendChild(target);

        const sliderNavigation = this.block.querySelector(
          ".testimonials--navigation"
        );

        this.swiper();
      },

      swiper: function () {
        let testimonials = this.block.querySelectorAll(".testimonial");
        let testimonial = this.block.querySelector(".testimonial");

        if (testimonials.length > 3) {
          testimonials.forEach((testimonial) => {
            testimonial.classList.add("swiper-slide");
          });

          var swiper = new Swiper(this.block.querySelector(".swiper"), {
            slidesPerView: 1,
            spaceBetween: 36,
            slidesOffsetBefore: 3,
            slidesOffsetAfter: -3,
            loop: false,
            pagination: {
              el: this.block.querySelector(".swiper-pagination"),
              clickable: true,
            },
            keyboard: {
              enabled: false,
            },
            a11y: {
              enabled: true,
            },
            navigation: {
              nextEl: this.block.querySelector(".swiper-button-next"),
              prevEl: this.block.querySelector(".swiper-button-prev"),
            },
            modules: [Navigation, Pagination, A11y],
            breakpoints: {
              576: {
                slidesPerView: 2.5,
                slidesOffsetBefore: 12,
                slidesOffsetAfter: testimonial.offsetWidth / 5 - 7,
              },

              1200: {
                slidesPerView: 3.5,
                slidesOffsetBefore: 12,
                slidesOffsetAfter: testimonial.offsetWidth / 7 - 7,
              },
            },
          });

          // Wire up custom navigation buttons
          const prevButton = this.block.querySelector(".testimonials-prev");
          const nextButton = this.block.querySelector(".testimonials-next");

          if (prevButton && nextButton) {
            prevButton.addEventListener("click", () => {
              swiper.slidePrev();
            });

            nextButton.addEventListener("click", () => {
              swiper.slideNext();
            });
          }
        }
      },

      log: function (message) {
        console.log(message);
      },
    };
  }

  blocks.forEach((block) => {
    const block_testimonials = create_block_testimonials();
    block_testimonials.init(block);
  });

  // Load More Testimonials functionality
  function initLoadMoreTestimonials() {
    const loadMoreButtons = document.querySelectorAll(
      ".load-more-testimonials"
    );

    loadMoreButtons.forEach((button) => {
      if (button.dataset.initialized) return; // Avoid double initialization
      button.dataset.initialized = "true";

      button.addEventListener("click", function () {
        const currentPage = parseInt(this.dataset.page);
        const maxPages = parseInt(this.dataset.maxPages);
        const ajaxUrl = this.dataset.ajaxUrl;
        const testimonialsContainer = this.closest(
          ".block--testimonials"
        ).querySelector(".testimonials.testimonials-grid");

        // Show loading state
        this.textContent = "Loading...";
        this.disabled = true;

        // Make AJAX request
        fetch(ajaxUrl, {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: new URLSearchParams({
            action: "load_more_testimonials",
            page: currentPage,
          }),
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.html) {
              // Append new testimonials
              testimonialsContainer.insertAdjacentHTML("beforeend", data.html);

              // Update button state
              if (data.has_more) {
                this.dataset.page = currentPage + 1;
                this.textContent = "Load More Testimonials";
                this.disabled = false;
              } else {
                // No more testimonials, hide button
                this.style.display = "none";
              }
            }
          })
          .catch((error) => {
            this.textContent = "Load More Testimonials";
            this.disabled = false;
          });
      });
    });
  }

  // Initialize load more on page load
  initLoadMoreTestimonials();

  // Editor
  const processedBlocks = new Set();

  if (typeof wp !== "undefined" && wp?.data?.subscribe) {
    wp.data.subscribe(() => {
      const blocks = document.querySelectorAll(".block--testimonials");
      blocks.forEach((block) => {
        if (!processedBlocks.has(block)) {
          const block_testimonials = create_block_testimonials();
          block_testimonials.init(block);
          processedBlocks.add(block);
        }
      });

      // Re-initialize load more for new blocks
      initLoadMoreTestimonials();
    });
  }
});
