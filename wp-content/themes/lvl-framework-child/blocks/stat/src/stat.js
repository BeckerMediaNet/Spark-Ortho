document.addEventListener('DOMContentLoaded', (e) => {

	const blocks = document.querySelectorAll('.block--stat');

	function create_block_stat() {

		return {
			block: null,
			config: null,
			value: null,
			prefix: null,
			suffix: null,
			observer: null,

			init: function (block) {

				this.block = block;
				this.value = this.block.dataset.value;
				this.prefix = this.block.dataset.prefix;
				this.suffix = this.block.dataset.suffix;

				this.config = { rootMargin: '0px 0px', threshold: 0.25 };
				this.observer = new IntersectionObserver(this.stat.bind(this), this.config);

				this.observer.observe(this.block);
			},

			stat: function (entries) {

				entries.forEach(entry => {
					if (entry.intersectionRatio > 0) {
						this.observer.unobserve(entry.target);
						this.count(entry.target);
					}
				});
			},

			count: function () {

				const num = this.value;
				let places = this.places(num);

				const options = {
					prefix: this.prefix ? '<span class="prefix">' + this.prefix + '</span>' : '',
					suffix: this.suffix ? '<span class="suffix">' + this.suffix + '</span>' : '',
					duration: 3,
					decimalPlaces: places
				}
				
				const countUp = new CountUp(this.block, num, options);
				countUp.start();
			},

			places: (num) => {

				let text = num.toString();
				let index = text.indexOf(".");
				return index == -1 ? 0 : (text.length - index - 1);
			},

			log: (message) => {
				console.log(message);
			}
		}
	}

	blocks.forEach(block => {
		const block_stat = create_block_stat();
		block_stat.init(block);
	});

	// Editor
	const processedBlocks = new Set();

	if (typeof wp !== 'undefined' && wp?.data?.subscribe) {
		wp.data.subscribe(() => {
			const blocks = document.querySelectorAll('.block--stat');
			blocks.forEach(block => {
				if (!processedBlocks.has(block)) {
					const block_stat = create_block_stat();
					block_stat.init(block);
					processedBlocks.add(block);
				}
			});
		});
	}
});