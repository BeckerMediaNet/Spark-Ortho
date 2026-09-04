import { Navigation, Pagination } from 'swiper/modules';

document.addEventListener('DOMContentLoaded', (e) => {

	const blocks = document.querySelectorAll('.block--cards-wrapper');

	function create_block_cards_wrapper() {

		return {
			block: null,

			init: function (block) {
				this.block = block;

				if (this.block.getAttribute('data-mobile') === 'slider') {
					this.slider()
				}
			},

			slider: function () {
				const target = this.block.querySelector('.cards');

				const wrapper = document.createElement('div');
				wrapper.classList.add('swiper');

				target.parentNode.insertBefore(wrapper, target);
				wrapper.appendChild(target);

				let cards = this.block.querySelectorAll('.block--card');
				cards.forEach( card => {
					card.classList.add('swiper-slide');
				})

				const pagination = document.createElement('div');
				pagination.classList.add('swiper-pagination');

				wrapper.appendChild(pagination);

				target.classList.add('swiper-wrapper');

				this.swiper();
				
			},

			swiper: function() {

					const swiper = new Swiper('#' + this.block.id + ' .swiper', {
						slidesPerView: 1,
						spaceBetween: 48, // this value is equal to --bs-gutter-x
						loop: true,
						pagination: {
							el: '.swiper-pagination',
							clickable: true,
						},
						breakpoints: {
							768: {
								slidesPerView: 99,
								draggable: false,
								loop: false
							}
						},
						modules: [Navigation, Pagination]
					});
			},

			log: function (message) {
				console.log(message);
			}
		}
	}

	blocks.forEach(block => {
		const block_cards_wrapper = create_block_cards_wrapper();
		block_cards_wrapper.init(block);
	});
});