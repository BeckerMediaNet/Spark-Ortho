document.addEventListener('DOMContentLoaded', (e) => {

	const blocks = document.querySelectorAll('.block--table-of-contents');

	function create_block_table_of_contents() {

		return {
			block: null,

			init: function (block) {
				this.block = block;

				let toc = this.block.querySelector('.table-of-contents');
				let list = toc.querySelector('ul');
				let parent = this.block.closest('.wp-block-columns');
				let headings = parent.querySelectorAll('h2.wp-block-heading, h3.wp-block-heading');

				if (headings.length < 1) {
					this.block.style.display = 'none';
				}

				headings.forEach((el) => {
					let li = document.createElement('li');
					let a = document.createElement('a');
					el.id = el.textContent.toLowerCase().replace(/ /g, '-');
					a.href = '#' + el.id;
					a.textContent = el.textContent;
					li.appendChild(a);
					list.appendChild(li);
				});
			},

			log: (message) => {
				console.log(message);
			}
		}
	}

	blocks.forEach(block => {
		const block_table_of_contents = create_block_table_of_contents();
		block_table_of_contents.init(block);
	});
});

