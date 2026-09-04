import { Autoplay } from "swiper/modules";

document.addEventListener("DOMContentLoaded", (e) => {
  const blocks = document.querySelectorAll(".block--logos");

  function create_block_logos() {
    return {
      block: null,
      slider: null,

      init: function (block) {
        this.block = block;

        if (this.block.getAttribute("data-layout") === "slider") {
          this.builder();
          // this.listners();
        }
      },

      builder: function () {
        this.slider = this.block.querySelector(".logos");
        this.slider.classList.add("swiper-wrapper");

        const wrapper = document.createElement("div");
        wrapper.classList.add("swiper");

        this.slider.parentNode.insertBefore(wrapper, this.slider);
        wrapper.appendChild(this.slider);

        this.swiper();
      },

      swiper: function () {
        // Get settings from data attributes
        let desktopSlidesPerView =
          parseInt(this.block.getAttribute("data-desktop-slides")) || 6;
        let autoplayEnabled =
          this.block.getAttribute("data-autoplay") === "true";
        let autoplayDelay =
          parseInt(this.block.getAttribute("data-autoplay-delay")) || 0;
        let showNavigation =
          this.block.getAttribute("data-show-navigation") === "true";
        let logos = this.block.querySelectorAll(".logo");

        logos.forEach((logo, index) => {
          logo.classList.add("swiper-slide");

          // Add permanent color classes based on original index
          if (this.block.classList.contains("is-style-alternating-bg-colors")) {
            logo.classList.remove(
              "bg-primary",
              "bg-secondary",
              "bg-red"
            );

            // Add color class based on original position (0-based index)
            const colorIndex = index % 3;
            if (colorIndex === 0) {
              logo.classList.add("bg-primary");
            } else if (colorIndex === 1) {
              logo.classList.add("bg-secondary");
            } else {
              logo.classList.add("bg-red");
            }
          }
        });

        if (logos.length >= 2) {
          // Configure autoplay settings
          let autoplayConfig = autoplayEnabled
            ? {
                delay: autoplayDelay,
              }
            : false;

          const swiper = new Swiper("#" + this.block.id + " .swiper", {
            slidesPerView: 1,
            spaceBetween: 32,
            autoHeight: false,
            centeredSlides: true,
            updateOnWindowResize: true,
            loop: true,
            autoplay: autoplayConfig,
            speed: 2500,
            modules: [Autoplay],
            breakpoints: {
              576: {
                slidesPerView: 2,
              },
              768: {
                slidesPerView: 2,
              },
              992: {
                slidesPerView: 3,
              },
              1200: {
                slidesPerView: 4,
              },
              1440: {
                slidesPerView: desktopSlidesPerView,
              },
            },
          });

          // Wire up navigation buttons if enabled
          if (showNavigation) {
            const prevButton = this.block.querySelector(".logos-prev");
            const nextButton = this.block.querySelector(".logos-next");

            if (prevButton && nextButton) {
              prevButton.addEventListener("click", () => {
                swiper.slidePrev();
              });

              nextButton.addEventListener("click", () => {
                swiper.slideNext();
              });
            }
          }
        }
      },

      log: function (message) {
        console.log(message);
      },
    };
  }

  blocks.forEach((block) => {
    const block_logos = create_block_logos();
    block_logos.init(block);
  });

  // Editor
  const processedBlocks = new Set();

  if (typeof wp !== "undefined" && wp?.data?.subscribe) {
    wp.data.subscribe(() => {
      const blocks = document.querySelectorAll(".block--logos");
      blocks.forEach((block) => {
        if (!processedBlocks.has(block)) {
          const block_logos = create_block_logos();
          block_logos.init(block);
          processedBlocks.add(block);
        }
      });
    });
  }
});
