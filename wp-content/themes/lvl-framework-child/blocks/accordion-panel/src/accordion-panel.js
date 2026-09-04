document.addEventListener('DOMContentLoaded', (e) => {

	const blocks = document.querySelectorAll('.block--accordion-panel');

	function create_block_accordion_panel() {

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
		const block_accordion_panel = create_block_accordion_panel();
		block_accordion_panel.init(block);
	});
});