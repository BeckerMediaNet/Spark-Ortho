document.addEventListener('DOMContentLoaded', (e) => {

	const blocks = document.querySelectorAll('.block--meta');

	function create_block_meta() {

		return {
			block: null,

			init: function (block) {
				this.block = block;

				// this.listeners();
				
			},

			listeners: function() {
				
			},

			log: (message) => {
				console.log(message);
			}
		}
	}

	blocks.forEach(block => {
		const block_meta = create_block_meta();
		block_meta.init(block);
	});
});

