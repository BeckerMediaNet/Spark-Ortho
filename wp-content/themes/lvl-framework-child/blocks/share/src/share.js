document.addEventListener('DOMContentLoaded', (e) => {

	const blocks = document.querySelectorAll('.block--share');

	function create_block_share() {

		return {
			block: null,

			init: function (block) {
				this.block = block;

				this.listeners();
				
			},

			listeners: function() {
				const links = this.block.querySelectorAll('.share-button');
				links.forEach( link => {

					if( link.classList.contains('share-link') ) {
						link.addEventListener('click', (e) => {
							e.preventDefault();

							const clipBoard = navigator.clipboard;
							clipBoard.writeText(e.currentTarget.href);
						});
					}
				});
			},

			log: (message) => {
				console.log(message);
			}
		}
	}

	blocks.forEach(block => {
		const block_share = create_block_share();
		block_share.init(block);
	});
});

