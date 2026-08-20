document.addEventListener('DOMContentLoaded', (e) => {

	const blocks = document.querySelectorAll('main .events');

	const events = {

		loaded: true,
		page: 1,

		init: (block) => {

			// check for url params on page load and set filters
			let url = new URL(window.location);
			let params = new URLSearchParams(url.search);

			params.forEach( (value, key) => {

				if (key == 'event-type' ) {
					let values = value.split(',');
					values.forEach( value => {

						if (value) {
							let text = '';
							if ( key == 'type' ) {
								text = block.querySelector('[data-filter-type="'+key+'"][data-filter-value="'+value+'"]').innerText;
							} else {
								text = value
							}

							if ( text != '') {
								events.filtered(block, key, value, text)
							}
						}
					})
				}
			});

			// add event listeners for all dropdown items
			let dropdowns = block.querySelectorAll('.filter .dropdown');
			dropdowns.forEach( filter => {
				events.dropdowns(block, filter);
			});

			// additional listeners and load the posts
			events.submit(block);
			events.reset(block);
			events.more(block);
			events.load(block);
		},

		params: (type, value) => {

			let url = new URL(window.location);
			let param = (url.searchParams.get(type))? url.searchParams.get(type).split(',') : [];

			if ( !param.includes(value) ) {
				param.push(value);
				url.searchParams.set(type, param.join(','));
				url.search = decodeURIComponent(url.search);
				window.history.replaceState({}, '', url);
			}
		},

		dropdowns: (block, filter) => {

			let options = filter.querySelectorAll('.dropdown-item');

			options.forEach( option => {
				
				option.addEventListener('click', function(e) {
					let type = e.target.dataset.filterType;
					let value = e.target.dataset.filterValue;
					let text = e.target.innerText;
					events.filtered(block, type, value, text)
				})
			});

		},

		filtered: (block, type, value, text) => {

			let filtered = block.querySelector('.filtered');
			let filter = filtered.querySelector('[data-filter-type="'+type+'"][data-filter-value="'+value+'"]');

			if( !filter ) {

				const button = document.createElement('button');
				button.classList.add('btn', 'btn-filter');
				button.setAttribute('data-filter-type', type);
				button.setAttribute('data-filter-value', value);
				button.innerText = text;

				button.addEventListener('click', (e) => {

					let url = new URL(window.location);
					let param = url.searchParams.get(e.target.dataset.filterType);

					if ( param ) {
						let values = param.split(',');
						values = values.filter(value => value !== e.target.dataset.filterValue);
						values = values.join(',');

						if ( values != '' ) {
							url.searchParams.set(e.target.dataset.filterType, values);
						} else {
							url.searchParams.delete(e.target.dataset.filterType);
						}
		
						window.history.replaceState(null, '', url);
					}

					e.target.remove();
					events.loaded = false;
					events.page = 1;
					events.load(block);
				})

				filtered.appendChild(button);

				events.loaded = false;
				events.page = 1;
				events.params(type, value);
				events.load(block);
			}

		},

		submit: (block) => {

			let submit = block.querySelector('.filter-submit');
			let keyword = block.querySelector('#keyword');

			submit?.addEventListener('click', function(e) {
				e.preventDefault();
				let type = 'keyword';
				let value = keyword.value;
				let text = keyword.value;
				
				if (keyword.value !== '') {
					keyword.value = '';
					events.loaded = false;
					events.page = 1;
					events.filtered(block, type, value, text);
				}
			})
		},

		reset: (block) => {

			block.addEventListener('click', function(e){
				if ( e.target.classList.contains('filter-reset') ) {
					let url = new URL(window.location);
					url.search = '';
					
					window.history.replaceState({}, '', url);

					let filtered = block.querySelector('.filtered');
					filtered.innerHTML = '';

					events.loaded = false;
					events.page = 1;
					events.load(block);
				}
			})
		},

		more: (block) => {

			block.addEventListener('click', function(e){

				if ( e.target.classList.contains('load-more') ) {
					events.loaded = true;
					events.page = events.page + 1;
					events.load(block);
				}
			})

		},

		load: (block) => {

			let spinner = block.querySelector('.spinner');
			let target = block.querySelector('.events-target');
			let more = block.querySelector('.load-more');

			more.disabled = true;
			more.classList.remove('d-block');

			if ( !events.loaded ) {
				target.innerHTML = '';
			}

			let filtereds = []
			let filters = block.querySelectorAll('.btn-filter');

			filters.forEach( item => {

				const obj = {
					type: item.dataset.filterType,
					value: item.dataset.filterValue
				}
				
				filtereds.push(obj);
			});

			spinner.style.display = 'block';

			const data = new FormData();

			data.append('filters', JSON.stringify(filtereds));
			data.append('action', 'events_get');
			data.append('nonce', events_ajax.nonce);
			data.append('page', events.page);
			
			fetch ( events_ajax.ajax_url, {
				method: 'POST',
				credentials: 'same-origin',
				body: data
			})

			.then( (response) => {

				if (!response.ok) {
					events.log('Error getting events');
					return;
				}

				return response;
			})
			
			.then( (response) => {

				if ( response.headers.get('loadmore') ) {
					more.classList.add('d-block');
					more.disabled = false;
				} else {
					more.classList.remove('d-block');
					more.disabled = true;
				}
				
				return response.text();
			})
			
			.then( (data) => {
				
				if ( events.loaded ) {
					target.innerHTML += data;
				} else {
					target.innerHTML = data;
				}

				spinner.style.display = 'none';
			})

		},
		
		log: (message) => {
			console.log(message);
		}
	}

	blocks.forEach( block => {
		events.init(block);
	});
});