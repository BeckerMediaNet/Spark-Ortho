document.addEventListener('DOMContentLoaded', (e) => {

	const blocks = document.querySelectorAll('.block--hero-slide');

	function create_block_hero_slide() {

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
		const block_hero_slide = create_block_hero_slide();
		block_hero_slide.init(block);
	});
});