document.addEventListener('DOMContentLoaded', (e) => {

	const blocks = document.querySelectorAll('.block--media-text');

	function create_block_media_text() {

		return {
			block: null,
			src: null,
			type: null,
			player: null,

			init: function (block) {
				this.block = block;
				this.src = this.block.querySelector('.media').dataset.mediaSrc;
				
				if (this.src !== '') {
					this.video();
				}
			},

			video: function() {
				let self = this;
				let media = this.block.querySelector('.media');

				media.addEventListener('click', function () {
                    self.open();
                });

				this.player = document.createElement('div');
				this.player.id = 'video-player-' + this.block.id;
				this.player.classList.add('video-player');
				document.body.appendChild(this.player);
			},

			open: function() {
				const isExpanded = this.player.classList.contains('expanded');

				if (!isExpanded) {
					document.body.style.overflow = 'hidden';
					this.player.classList.add('expanded');
					this.load();
				
				} else {
					this.player.classList.remove('expanded');
					this.unload();
				} 
			},

			load: function() {
				let self = this;
                let src = this.src;

                const types = [
                    'youtube',
                    'vimeo',
                    'wistia',
                    'vidyard',
                    'hubspot',
                    '.mp4'
                ];
                let type = types.find(t => src.includes(t));
                const closeButton = `<button class="close btn btn-outline-light">CLOSE &times;</button>`;
                const iframeClasses = 'iframe-embed rounded';

                switch (type) {

                    case 'youtube':
                        let youtubeId = src.split('v=').pop();
                        this.player.innerHTML = `${closeButton}<iframe class="${iframeClasses}" src="https://www.youtube-nocookie.com/embed/${youtubeId}?autoplay=1" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>`;
                        break;

                    case 'vimeo':
                        // let vimeoId = src.split('/').pop();
                        let vimeoId = src.replace('https://vimeo.com/', ''); // catches multi ID urls
                        this.player.innerHTML = `${closeButton}<iframe src="https://player.vimeo.com/video/${vimeoId}?autoplay=1" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>`;
                        break;

                    case 'wistia':
                        let wistiaId = src.split('/').pop();
                        this.player.innerHTML = `${closeButton}<iframe src="https://fast.wistia.net/embed/iframe/${wistiaId}?autoplay=1" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>`;
                        break;

                    case 'vidyard':
                        let vidyardId = src.split('/').pop();
                        this.player.innerHTML = `${closeButton}<iframe src="https://play.vidyard.com/${vidyardId}.html?v=3.1.1&type=inline" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>`;
                        break;

                    case 'hubspot':
                        let hubspotId = src.split('/').pop();
                        this.player.innerHTML = `${closeButton}<iframe src="https://app.hubspot.com/video/${hubspotId}" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>`;
                        break;

                    case '.mp4':
                        this.player.innerHTML = `${closeButton}<video aria-label="Media Player" preload="auto" controls autoplay><source type="video/mp4" src="${src}"/></video>`;
                        break;

                    default:
                        console.error('Invalid video type');
                }

                let close = this.player.querySelector('.close');

                close?.addEventListener('click', function () {
                    self.unload();
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        self.unload();
                    }
                });

                this.player.addEventListener('click', function (e) {
                    if (e.target === this) {
                        self.unload();
                    }
                });

			},

			unload: function() {
                this.player.innerHTML = '';
                this.player.classList.remove('expanded');
                document.body.style.overflow = 'initial';
			},

			log: (message) => {
				console.log(message);
			}
		}
	}

	blocks.forEach(block => {
		const block_media_text = create_block_media_text();
		block_media_text.init(block);
	});
});