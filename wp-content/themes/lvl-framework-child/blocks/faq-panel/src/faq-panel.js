document.addEventListener("DOMContentLoaded", (e) => {
  const blocks = document.querySelectorAll(".block--faq-panel");

  function create_block_faq_panel() {
    return {
      block: null,

      init: function (block) {
        this.block = block;
      },

      log: (message) => {
        console.log(message);
      },
    };
  }

  blocks.forEach((block) => {
    const block_faq_panel = create_block_faq_panel();
    block_faq_panel.init(block);
  });
});
