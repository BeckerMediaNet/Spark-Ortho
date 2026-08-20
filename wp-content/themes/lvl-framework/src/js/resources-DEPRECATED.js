document.addEventListener('DOMContentLoaded', (e) => {

    const blocks = document.querySelectorAll('main .resources');

    const resources = {

        loaded: true,
        page: 1,

        init: (block) => {

            // check for url params on page load and set filters
            let url = new URL(window.location);
            let params = new URLSearchParams(url.search);
            let filterCount = 0;

            params.forEach((value, key) => {

                if (key === 'resource_types' || key === 'category' || key === 'keyword') {
                    filterCount++;
                    let values = value.split(',');
                    values.forEach(value => {

                        if (value) {
                            let text = '';
                            if (key === 'resource_types' || key === 'category') {
                                let filter = block.querySelector('[data-filter-type="' + key + '"][data-filter-value="' + value + '"]');
                                if (filter)
                                    text = (filter.innerText === '' ? value : filter.innerText);
                            }

                            if (text !== '') {
                                resources.filtered(block, key, value, text)
                            }
                        }
                    })
                }
            });

            if (filterCount > 0) {
                // scroll to block
                const rect = block.getBoundingClientRect();
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const offset = rect.top + scrollTop - 100;
                window.scrollTo({
                    top: offset,
                    behavior: 'smooth'
                });
            }


            // add event listener for the enter key on the keyword input
            block.querySelector('.filter #keyword').addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    resources.submit(block);
                }
            });

            // TODO review
            // add event listener for the filter button
            block.querySelector('.filter .filter-submit').addEventListener('click', function (e) {
                e.preventDefault();
                resources.submit(block);
            });

            // add event listeners for all dropdown items
            let dropdowns = block.querySelectorAll('.filter .dropdown');
            dropdowns.forEach(filter => {
                resources.dropdowns(block, filter);
            });

            // additional listeners and load the posts
            resources.submit(block);
            resources.reset(block);
            resources.more(block);
            resources.load(block);
        },

        params: (type, value) => {

            let url = new URL(window.location);
            let param = (url.searchParams.get(type)) ? url.searchParams.get(type).split(',') : [];

            if (!param.includes(value)) {
                param.push(value);
                url.searchParams.set(type, param.join(','));
                url.search = decodeURIComponent(url.search);
                window.history.replaceState({}, '', url);
            }
        },

        dropdowns: (block, filter) => {

            let options = filter.querySelectorAll('.dropdown-item');

            options.forEach(option => {

                option.addEventListener('click', function (e) {
                    let type = e.target.dataset.filterType;
                    let value = e.target.dataset.filterValue;
                    let text = e.target.innerText;
                    resources.filtered(block, type, value, text)
                })
            });

        },

        filtered: (block, type, value, text) => {

            let filtered = block.querySelector('.filtered');
            let filter = filtered.querySelector('[data-filter-type="' + type + '"][data-filter-value="' + value + '"]');

            if (!filter) {

                const button = document.createElement('button');
                button.classList.add('btn', 'btn-filter');
                button.setAttribute('data-filter-type', type);
                button.setAttribute('data-filter-value', value);
                button.innerText = text;

                button.addEventListener('click', (e) => {

                    let url = new URL(window.location);
                    let param = url.searchParams.get(e.target.dataset.filterType);

                    if (param) {
                        let values = param.split(',');
                        values = values.filter(value => value !== e.target.dataset.filterValue);
                        values = values.join(',');

                        if (values != '') {
                            url.searchParams.set(e.target.dataset.filterType, values);
                        } else {
                            url.searchParams.delete(e.target.dataset.filterType);
                        }

                        window.history.replaceState(null, '', url);
                    }

                    e.target.remove();
                    resources.loaded = false;
                    resources.page = 1;
                    resources.load(block);
                })

                filtered.appendChild(button);

                resources.loaded = false;
                resources.page = 1;
                resources.params(type, value);
                resources.load(block);
            }

        },

        submit: (block) => {

            let submit = block.querySelector('.filter-submit');
            let keyword = block.querySelector('#keyword');

            submit?.addEventListener('click', function (e) {
                e.preventDefault();
            let type = 'keyword';
            let value = keyword.value;
            let text = keyword.value;

            if (keyword.value !== '') {
                keyword.value = '';
                resources.loaded = false;
                resources.page = 1;
                resources.filtered(block, type, value, text);
            }
            })
        },

        reset: (block) => {

            block.addEventListener('click', function (e) {
                if (e.target.classList.contains('filter-reset')) {
                    let url = new URL(window.location);
                    url.search = '';

                    window.history.replaceState({}, '', url);

                    let filtered = block.querySelector('.filtered');
                    filtered.innerHTML = '';

                    resources.loaded = false;
                    resources.page = 1;
                    resources.load(block);
                }
            })
        },

        more: (block) => {

            block.addEventListener('click', function (e) {

                if (e.target.classList.contains('load-more')) {
                    resources.loaded = true;
                    resources.page = resources.page + 1;
                    resources.load(block);
                }
            })

        },

        load: (block) => {

            let spinner = block.querySelector('.spinner');
            let target = block.querySelector('.resources-target');
            let more = block.querySelector('.load-more');

            more.disabled = true;
            more.classList.remove('d-block');

            if (!resources.loaded) {
                target.innerHTML = '';
            }

            let filtereds = []
            let filters = block.querySelectorAll('.btn-filter');

            filters.forEach(item => {

                const obj = {
                    type: item.dataset.filterType,
                    value: item.dataset.filterValue
                }

                filtereds.push(obj);
            });

            spinner.style.display = 'block';

            const data = new FormData();

            data.append('filters', JSON.stringify(filtereds));
            data.append('action', 'resources_get');
            data.append('nonce', resources_ajax.nonce);
            data.append('page', resources.page);

            fetch(resources_ajax.ajax_url, {
                method: 'POST',
                credentials: 'same-origin',
                body: data
            })

                .then((response) => {

                    if (!response?.ok) {
                        resources.log(response)
                        resources.log('Error getting resources');
                        return;
                    }

                    return response;
                })

                .then((response) => {
                    if (response?.headers?.get('loadmore')) {

                        more.classList.add('d-block');
                        more.disabled = false;
                    } else {
                        more.classList.remove('d-block');
                        more.disabled = true;
                    }

                    return response?.text();
                })

                .then((data) => {

                    if (resources.loaded) {
                        target.innerHTML += data;
                    } else {
                        target.innerHTML = data;
                    }

                    const cardLoading = {
                        items: target.querySelectorAll('.card--loading'),
                        counter: 0,
                        duration: 100,
                    }

                    cardLoading.items.forEach((item) => {
                        cardLoading.counter++;

                        setTimeout(() => {
                            item.classList.remove("card--loading")
                            item.classList.add("card--loaded")
                            setTimeout(() => {
                                item.classList.remove("card--loaded")
                            }, cardLoading.counter * cardLoading.duration + 50)
                        }, cardLoading.counter * cardLoading.duration)
                    })

                    spinner.style.display = 'none';
                })

                .then(() => {
                    // add a click event to follow link in card
                    let resourceCards = block.querySelectorAll('.resource');
                    resourceCards.forEach(resourceCard => {
                        resourceCard.addEventListener('click', function (e) {
                            let link = e.currentTarget.querySelector('a');
                            link.click();
                        })
                    })
                })

        },

        log: (message) => {
            console.log(message);
        }
    }

    blocks.forEach(block => {
        resources.init(block);
    });
});