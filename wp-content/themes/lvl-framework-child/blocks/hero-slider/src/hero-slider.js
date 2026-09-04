import { Navigation, Pagination, EffectFade, A11y, Autoplay } from 'swiper/modules';

document.addEventListener('DOMContentLoaded', (e) => {

	const blocks = document.querySelectorAll('.block--hero-slider');

	function create_block_hero_slider() {

		return {
			block: null,
			swiper: null,

			init: function (block) {
				this.block = block;

				if (this.block.querySelectorAll('.block--hero-slide').length > 1) {
					this.builder()
				}
			},

			builder: function () {

				let self = this;

				const target = this.block.querySelector('.slides');

				const wrapper = document.createElement('div');
				wrapper.classList.add('swiper');

				target.parentNode.insertBefore(wrapper, target);
				wrapper.appendChild(target);

				let slides = this.block.querySelectorAll('.block--hero-slide');
				slides.forEach( slide => {
					slide.classList.add('swiper-slide');
				})

				if ( slides.length > 1 ) {

					const nav = document.createElement('div');
					nav.classList.add('swiper-nav');

					wrapper.appendChild(nav);

					const controls = document.createElement('div');
					controls.classList.add('swiper-controls');
					controls.setAttribute('role', 'button');
					controls.setAttribute('aria-label', 'Play / Pause');
					controls.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="play" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M360-272.31v-415.38L686.15-480 360-272.31Z"/></svg><svg class="pause" fill="currentColor" height="13" viewBox="0 0 10 13" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m0 0h3v13h-3z"/><path d="m7 0h3v13h-3z"/></svg>';

					nav.appendChild(controls);
					
					const prev = document.createElement('div');
					prev.classList.add('swiper-arrow', 'swiper-prev');
					prev.innerHTML = '<svg fill="none" height="17" viewBox="0 0 9 17" width="9" xmlns="http://www.w3.org/2000/svg"><path d="m8 14-6-6 6-6" stroke="currentColor" stroke-width="2"/></svg>';
					
					nav.appendChild(prev);

					const dots = document.createElement('div');
					dots.classList.add('swiper-pagination');

					nav.appendChild(dots);

					const next = document.createElement('div');
					next.classList.add('swiper-arrow', 'swiper-next');
					next.innerHTML = '<svg fill="none" height="17" viewBox="0 0 10 17" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m2 3 6 6-6 6" stroke="currentColor" stroke-width="2"/></svg>';
					
					nav.appendChild(next);

					controls.addEventListener('click', function() {

						if (self.swiper.autoplay.running) {
							self.swiper.autoplay.stop();
							this.querySelector('.play').style.display = 'inline';
							this.querySelector('.pause').style.display = 'none';
						} else {
							self.swiper.autoplay.start();
							this.querySelector('.play').style.display = 'none';
							this.querySelector('.pause').style.display = 'inline';
						}
					});
				}

				target.classList.add('swiper-wrapper');

				this.slider();
				
			},

			slider: function() {

				this.swiper = new Swiper('#' + this.block.id + ' .swiper', {
					slidesPerView: 1,
					spaceBetween: 0,
					autoplay: {
						delay: 5000,
						pauseOnMouseEnter: true,
					},
					loop: true,
					effect: 'fade',
					fadeEffect: {
						crossFade: true
					},
					speed: 800,
					pagination: {
						el: this.block.querySelector('.swiper-pagination'),
						clickable: true,
					},
					navigation: {
						nextEl: this.block.querySelector('.swiper-next'),
						prevEl: this.block.querySelector('.swiper-prev'),
					},
					modules: [Navigation, Pagination, EffectFade, A11y, Autoplay]
				});
			},

			log: function (message) {
				console.log(message);
			}
		}
	}

	blocks.forEach(block => {
		const block_hero_slider = create_block_hero_slider();
		block_hero_slider.init(block);
	});
});