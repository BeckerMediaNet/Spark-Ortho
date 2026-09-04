document.addEventListener('DOMContentLoaded', (e) => {

	const blocks = document.querySelectorAll('.block--nav-tab-panel');

	function create_block_nav_tab_panel() {

		return {
			block: null,

			init: function (block) {
				this.block = block;
			},

			log: (message) => {
				console.log(message);
			}
		}
	}

	blocks.forEach(block => {
		const block_nav_tab_panel = create_block_nav_tab_panel();
		block_nav_tab_panel.init(block);
	});
});