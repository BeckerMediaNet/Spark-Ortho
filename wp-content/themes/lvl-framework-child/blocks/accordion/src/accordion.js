document.addEventListener('DOMContentLoaded', (e) => {

	const blocks = document.querySelectorAll('.block--accordion');

	function create_block_accordion() {

		return {
			block: null,
			id: null,
			expanded: null, // set to false to have all panels collapsed by default

			init: function (block) {
				this.block = block;
				this.id = this.block.id;
				this.panels();
			},

			panels: function() {
				let i = 0;
				const panels = this.block.querySelectorAll('.accordion-item');

				this.expanded = this.block.dataset.expanded === 'true' ? true : false;

				panels.forEach( panel => {
					let heading = panel.querySelector('.accordion-header');
					let button = heading.querySelector('.accordion-button');
					let collapse = panel.querySelector('.collapse');

					button.id = this.id + '-heading-' + i;
					button.setAttribute('data-bs-target', '#' + this.id + '-collapse-' + i);
					button.setAttribute('aria-controls', this.id + '-collapse-' + i);
					// heading.setAttribute('tabindex', '0');

					collapse.id = this.id + '-collapse-' + i;
					collapse.setAttribute('aria-labelledby', this.id + '-heading-' + i);
					collapse.setAttribute('data-bs-parent', '#' + this.id);

					if (this.expanded && i === 0) {
						button.setAttribute('aria-expanded', 'true');
						collapse.classList.add('show');
					}

					i++;
				});
			},

			log: (message) => {
				console.log(message);
			}
		}
	}

	blocks.forEach(block => {
		const block_accordion = create_block_accordion();
		block_accordion.init(block);
	});
});