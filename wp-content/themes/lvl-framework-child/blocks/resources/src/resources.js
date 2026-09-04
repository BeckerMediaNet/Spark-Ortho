document.addEventListener("DOMContentLoaded", (e) => {
  const blocks = document.querySelectorAll(".block--resources");

  function create_block_resources() {
    return {
      block: null,
      loaded: true,
      initial: true,
      related: false,
      page: 1,
      limit: 0,
      posts: "",
      categories: "",
      tags: "",
      areas: "",

      init: function (block) {
        this.block = block;
        this.limit = this.block.dataset.limit;
        this.posts = this.block.dataset.posts;
        this.categories = this.block.dataset.categories;
        this.tags = this.block.dataset.tags;
        this.areas = this.block.dataset.areas;

        // check for url params on page load and set filters
        let url = new URL(window.location);
        let params = new URLSearchParams(url.search);

        params.forEach((value, key) => {
          if (key == "category" || key == "post_tag" || key == "keyword") {
            let values = value.split(",");
            values.forEach((value) => {
              if (value) {
                let text = "";
                if (key == "category" || key == "post_tag") {
                  text = this.block.querySelector(
                    '[data-filter-type="' +
                      key +
                      '"][data-filter-value="' +
                      value +
                      '"]'
                  )?.innerText;
                } else {
                  text = value;
                }

                if (text && text != "") {
                  this.filtered(key, value, text);
                }
              }
            });
          }

          if (key == "cp") {
            this.page = value;
          }
        });

        // add event listeners for all dropdown items
        let dropdowns = this.block.querySelectorAll(".filter .dropdown");
        dropdowns.forEach((filter) => {
          this.dropdowns(filter);
        });

        let keyword = this.block.querySelector("#keyword");
        this.keyword(keyword);

        // additional listeners and load the posts
        this.submit();
        this.reset();
        this.more();
        this.load();
      },

      params: function (type, value) {
        let url = new URL(window.location);
        let param = url.searchParams.get(type)
          ? url.searchParams.get(type).split(",")
          : [];

        if (!param.includes(value)) {
          param.push(value);
          url.searchParams.set(type, param.join(","));
          url.search = decodeURIComponent(url.search);
          window.history.replaceState({}, "", url);
        }
      },

      dropdowns: function (filter) {
        let self = this;

        let toggle = filter.querySelector(".dropdown-toggle");
        let options = filter.querySelectorAll(".dropdown-item");

        options.forEach((option) => {
          option.addEventListener("click", function (e) {
            let type = e.target.dataset.filterType;
            let value = e.target.dataset.filterValue;
            let text = e.target.innerText;
            self.filtered(type, value, text);
          });

          option.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
              let type = e.target.dataset.filterType;
              let value = e.target.dataset.filterValue;
              let text = e.target.innerText;
              self.filtered(type, value, text);
              toggle.focus();
              toggle.setAttribute("aria-expanded", "false");
            }
          });
        });
      },

      keyword: function (keyword) {
        let self = this;

        keyword?.addEventListener("keydown", function (event) {
          if (event.key === "Enter") {
            let type = "keyword";
            let value = keyword.value;
            let text = keyword.value;

            if (keyword.value !== "") {
              keyword.value = "";
              self.loaded = false;
              self.page = 1;
              self.filtered(type, value, text);
            }
          }
        });
      },

      filtered: function (type, value, text) {
        let self = this;

        let filtered = this.block.querySelector(".filtered");
        let filter = filtered.querySelector(
          '[data-filter-type="' + type + '"][data-filter-value="' + value + '"]'
        );

        if (!filter) {
          const button = document.createElement("button");
          button.classList.add("btn", "btn-secondary", "btn-filter");
          button.setAttribute("data-filter-type", type);
          button.setAttribute("data-filter-value", value);
          button.innerText = text;

          button.addEventListener("click", (e) => {
            let url = new URL(window.location);
            let param = url.searchParams.get(e.target.dataset.filterType);

            if (param) {
              let values = param.split(",");
              values = values.filter(
                (value) => value !== e.target.dataset.filterValue
              );
              values = values.join(",");

              if (values != "") {
                url.searchParams.set(e.target.dataset.filterType, values);
              } else {
                url.searchParams.delete(e.target.dataset.filterType);
              }

              window.history.replaceState(null, "", url);
            }

            e.target.remove();
            self.loaded = false;
            self.page = 1;
            self.load();
          });

          filtered.prepend(button);

          this.loaded = false;
          this.page = 1;
          this.params(type, value);
          this.load();
        }
      },

      submit: function () {
        let self = this;

        let submit = this.block.querySelector(".filter-submit");
        let keyword = this.block.querySelector("#keyword");

        submit?.addEventListener("click", function (e) {
          e.preventDefault();
          let type = "keyword";
          let value = keyword.value;
          let text = keyword.value;

          if (keyword.value !== "") {
            keyword.value = "";
            self.loaded = false;
            self.page = 1;
            self.filtered(type, value, text);
          }
        });
      },

      reset: function () {
        let self = this;

        this.block.addEventListener("click", function (e) {
          if (e.target.classList.contains("filter-reset")) {
            let url = new URL(window.location);
            url.search = "";

            window.history.replaceState({}, "", url);

            let filtered = self.block.querySelector(".filtered");
            filtered.innerHTML = "";

            self.loaded = false;
            self.page = 1;
            self.load();
          }
        });
      },

      more: function () {
        let self = this;

        this.block.addEventListener("click", function (e) {
          if (e.target.classList.contains("load-more")) {
            self.loaded = true;
            self.page = this.page + 1;
            self.load();
          }
        });
      },

      pagination: function (pagination) {
        let self = this;

        pagination = JSON.parse(pagination);

        let totalPages = pagination.totalPages;
        let currentPage = pagination.currentPage;

        const paginationContainer = this.block.querySelector(".pagination");
        paginationContainer.innerHTML = "";
        paginationContainer.classList.remove("d-none");

        if (totalPages > 1) {
          // add previous arrow
          if (currentPage > 1) {
            const prevLink = document.createElement("a");
            prevLink.classList.add("page-link");
            prevLink.setAttribute(
              "href",
              window.location.href.split("?")[0] + "page/" + (currentPage - 1)
            );
            prevLink.setAttribute("data-page", currentPage - 1);
            prevLink.setAttribute("aria-label", "Previous page");
            prevLink.innerHTML =
              '<span class="visually-hidden">Previous page</span><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M10 13L4 7L10 1" stroke="currentColor" stroke-width="2"/></svg>';

            paginationContainer.appendChild(prevLink);
          }

          const range = 2; // The range of pages around the current page
          let start = currentPage - range;
          let end = currentPage + range;

          if (start < 1) {
            start = 1;
            end = start + range * 2;
          }

          if (end > totalPages) {
            end = totalPages;
            start = end - range * 2;
            if (start < 1) start = 1;
          }

          if (totalPages <= range * 2) {
            for (let i = 1; i <= totalPages; i++) {
              appendPageItem(i, currentPage);
            }
          } else {
            if (start > 2) {
              appendPageItem(1, currentPage);
              appendPageItem("...", currentPage);
            } else if (start === 2) {
              appendPageItem(1, currentPage);
            }

            for (let i = start; i <= end; i++) {
              appendPageItem(i, currentPage);
            }

            if (end < totalPages - 1) {
              appendPageItem("...", currentPage);
              appendPageItem(totalPages, currentPage);
            } else if (end === totalPages - 1) {
              appendPageItem(totalPages, currentPage);
            }
          }

          function appendPageItem(page, currentPage) {
            const paginationItem = document.createElement("a");
            paginationItem.classList.add("page-link");
            paginationItem.setAttribute(
              "href",
              window.location.href.split("?")[0] + "page/" + page
            );
            paginationItem.setAttribute("data-page", page);
            paginationItem.innerText = page;

            if (page === currentPage) {
              paginationItem.classList.add("active");
            }

            paginationContainer.appendChild(paginationItem);
          }

          // add next arrow
          if (currentPage != totalPages) {
            const nextLink = document.createElement("a");
            nextLink.classList.add("page-link");
            nextLink.setAttribute(
              "href",
              window.location.href.split("?")[0] + "page/" + (currentPage + 1)
            );
            nextLink.setAttribute("data-page", currentPage + 1);
            nextLink.setAttribute("aria-label", "Next page");
            nextLink.innerHTML =
              '<span class="visually-hidden">Next page</span><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M4 1L10 7L4 13" stroke="currentColor" stroke-width="2"/></svg>';

            paginationContainer.appendChild(nextLink);
          }

          const paginationItems = this.block.querySelectorAll(".page-link");
          paginationItems.forEach((item) => {
            if (item.dataset.page != "...") {
              item.addEventListener("click", function (e) {
                e.preventDefault();

                const target = self.block.querySelector(".resources-target");
                target.innerHTML = "";

                self.loaded = true;
                self.page = e.currentTarget.dataset.page;
                self.load();
                // self.block.scrollIntoView(true);

                let url = new URL(window.location);

                url.searchParams.set("cp", self.page);
                url.search = decodeURIComponent(url.search);
                window.history.replaceState({}, "", url);
              });
            } else {
              item.classList.add("disabled");
            }
          });
        }
      },

      pageItem: function (page, currentPage) {
        const item = document.createElement("a");
        item.classList.add("page-link");
        item.setAttribute("href", "#");
        item.setAttribute("data-page", page);
        item.innerText = page;

        if (page === currentPage) {
          item.classList.add("active");
        }

        return item;
      },

      load: function () {
        const spinner = this.block.querySelector(".spinner");
        const target = this.block.querySelector(".resources-target");
        const pagination = this.block.querySelector(".pagination");

        pagination.innerHTML = "";

        // let more = block.querySelector('.load-more');

        // more.disabled = true;
        // more.classList.remove('d-block');

        if (!this.loaded) {
          target.innerHTML = "";
        }

        let filtereds = [];
        let filters = this.block.querySelectorAll(".btn-filter");

        if (filters.length != 0) {
          this.block.querySelector(".filter-reset")?.classList.remove("d-none");
        } else {
          this.block.querySelector(".filter-reset")?.classList.add("d-none");
        }

        filters.forEach((item) => {
          const obj = {
            type: item.dataset.filterType,
            value: item.dataset.filterValue,
          };

          filtereds.push(obj);
        });

        spinner.style.display = "block";

        const data = new FormData();

        data.append("filters", JSON.stringify(filtereds));
        data.append("action", "resources_get");
        data.append("nonce", lvl_block_resources_ajax.nonce);
        data.append("page", this.page);
        data.append("url", window.location.href.split("?")[0]);
        data.append(
          "related",
          this.block.dataset.related ? this.block.dataset.related : this.related
        );
        data.append("limit", this.limit);
        data.append("posts", this.posts);
        data.append("categories", this.categories);
        data.append("tags", this.tags);
        data.append("areas", this.areas);

        fetch(lvl_block_resources_ajax.ajax_url, {
          method: "POST",
          credentials: "same-origin",
          body: data,
        })
          .then((response) => {
            if (!response.ok) {
              this.log("Error getting resources");
              return;
            }

            return response;
          })

          .then((response) => {
            // if ( response.headers.get('loadmore') ) {
            // 	more.classList.add('d-block');
            // 	more.disabled = false;
            // } else {
            // 	more.classList.remove('d-block');
            // 	more.disabled = true;
            // }

            if (this.limit == 0) {
              this.pagination(response.headers.get("pagination"));
            }

            return response.text();
          })

          .then((data) => {
            // if ( resources.loaded ) {
            // target.innerHTML += data;
            // } else {
            target.innerHTML = data;
            // }

            this.listeners();

            spinner.style.display = "none";

            if (!this.initial) {
              this.block.scrollIntoView(true);
            }

            this.initial = false;
          });
      },

      listeners: function () {
        let cards = this.block.querySelectorAll(".card");

        cards.forEach((card) => {
          let link = card.querySelector("a");

          if (link) {
            card.classList.add("linked");
            card.addEventListener("click", function (e) {
              // left mouse button clicked
              if (e.button === 0) {
                if (e.ctrlKey || e.metaKey) {
                  let tabLink = document.createElement("a");
                  tabLink.setAttribute("aria-hidden", "true");
                  tabLink.href = link.href;
                  tabLink.target = "_blank";
                  tabLink.click();
                } else {
                  link.click();
                }
              }
            });

            card.addEventListener("auxclick", function (e) {
              // middle mouse button clicked
              if (e.button === 1) {
                e.preventDefault();
                let tabLink = document.createElement("a");
                tabLink.setAttribute("aria-hidden", "true");
                tabLink.href = link.href;
                tabLink.target = "_blank";
                tabLink.click();
              }
            });
          }
        });
      },

      log: function (message) {
        console.log(message);
      },
    };
  }

  blocks.forEach((block) => {
    const block_resources = create_block_resources();
    block_resources.init(block);
  });

  // Editor
  const processedBlocks = new Set();

  if (typeof wp !== "undefined" && wp?.data?.subscribe) {
    wp.data.subscribe(() => {
      const blocks = document.querySelectorAll(".block--resources");
      blocks.forEach((block) => {
        if (!processedBlocks.has(block)) {
          const block_resources = create_block_resources();
          block_resources.init(block);
          processedBlocks.add(block);
        }
      });
    });
  }
});
