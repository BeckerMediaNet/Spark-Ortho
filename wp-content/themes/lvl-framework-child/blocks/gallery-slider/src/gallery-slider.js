import { Autoplay, Navigation, Pagination } from "swiper/modules";

document.addEventListener("DOMContentLoaded", () => {
  initializeGallerySliders();
});

// Also initialize when new blocks are added in the editor
if (typeof wp !== "undefined" && wp?.data?.subscribe) {
  wp.data.subscribe(() => {
    setTimeout(() => {
      initializeGallerySliders();
    }, 100);
  });
}

function initializeGallerySliders() {
  const blocks = document.querySelectorAll(
    ".block--gallery-slider:not(.slider-initialized)"
  );

  blocks.forEach((block) => {
    const slider = block.querySelector(".gallery-slider.swiper");

    if (!slider) {
      console.warn("Gallery slider container not found in block:", block);
      return;
    }

    // Get settings from block data attributes
    const autoplayEnabled = block.getAttribute("data-autoplay") === "true";
    const autoplayDelay =
      parseInt(block.getAttribute("data-autoplay-delay")) || 3000;
    const showNavigation =
      block.getAttribute("data-show-navigation") === "true";
    const showPagination =
      block.getAttribute("data-show-pagination") === "true";

    // Configure autoplay
    let autoplayConfig = false;
    if (autoplayEnabled) {
      autoplayConfig = {
        delay: autoplayDelay > 0 ? autoplayDelay : 3000,
        disableOnInteraction: false,
      };
    }

    // Configure navigation
    let navigationConfig = false;
    if (showNavigation) {
      // Add navigation arrows HTML if buttons exist but are empty
      const prevBtn = block.querySelector(".gallery-prev");
      const nextBtn = block.querySelector(".gallery-next");

      if (prevBtn && !prevBtn.innerHTML.trim()) {
        prevBtn.innerHTML = `
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6 1.41-1.41z"/>
          </svg>
        `;
        prevBtn.setAttribute("aria-label", "Previous slide");
      }

      if (nextBtn && !nextBtn.innerHTML.trim()) {
        nextBtn.innerHTML = `
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
          </svg>
        `;
        nextBtn.setAttribute("aria-label", "Next slide");
      }

      navigationConfig = {
        nextEl: block.querySelector(".gallery-next"),
        prevEl: block.querySelector(".gallery-prev"),
      };
    }

    // Configure pagination
    let paginationConfig = false;
    if (showPagination) {
      paginationConfig = {
        el: block.querySelector(".swiper-pagination"),
        clickable: true,
        bulletClass: "swiper-pagination-bullet",
        bulletActiveClass: "swiper-pagination-bullet-active",
      };
    }

    // Initialize Swiper
    try {
      const swiper = new Swiper(slider, {
        modules: [Autoplay, Navigation, Pagination],
        slidesPerView: 1,
        spaceBetween: 0,
        centeredSlides: true,
        loop: true,
        autoplay: autoplayConfig,
        navigation: navigationConfig,
        pagination: paginationConfig,
        speed: 800,
        grabCursor: true,
        keyboard: {
          enabled: true,
        },
        a11y: {
          enabled: true,
        },
        on: {
          init: function () {
            console.log("Gallery slider initialized successfully");
          },
          slideChange: function () {
            // Optional: Add custom slide change logic here
          },
        },
      });

      // Mark as initialized to prevent duplicate initialization
      block.classList.add("slider-initialized");

      // Store swiper instance for potential later access
      block.swiperInstance = swiper;
    } catch (error) {
      console.error("Failed to initialize gallery slider:", error);
    }
  });
}
