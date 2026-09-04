document.addEventListener('DOMContentLoaded', (e) => {

	const blocks = document.querySelectorAll('.block--card');

	function create_block_card() {

		return {
			block: null,

			init: function (block) {
				this.block = block;
				// this.listeners();
			},

			listeners: function() {

				let links = this.block.querySelectorAll('a');

				if(!links.length) return;

				if(links.length > 1) {
					this.block.classList.add('linked');
					this.block.classList.add('linked-multi');
					return;
				}

				let link = links[0];

				if (link) {

					if (link.href === '') return;
					
					this.block.dataset.lvlStretchLink = 'true';

					this.block.addEventListener('click', function (e) {
						// left mouse button clicked
						if (e.button === 0) {

							if (e.ctrlKey || e.metaKey) {
								let tabLink = document.createElement('a');
								tabLink.setAttribute('aria-hidden', 'true');
                				tabLink.href = link.href;
                				tabLink.target = '_blank';
                				tabLink.click();

							} else {
								link.click();
							}
						}
					});
			
					this.block.addEventListener('auxclick', function (e) {
						// middle mouse button clicked
						if (e.button === 1) {

							e.preventDefault();
							let tabLink = document.createElement('a');
							tabLink.setAttribute('aria-hidden', 'true');
                			tabLink.href = link.href;
                			tabLink.target = '_blank';
                			tabLink.click();
						}
					});
				}
			},

			log: (message) => {
				console.log(message);
			}
		}
	}

	blocks.forEach(block => {
		const block_card = create_block_card();
		block_card.init(block);
	});
});