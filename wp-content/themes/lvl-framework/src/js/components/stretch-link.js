document.addEventListener('DOMContentLoaded', (e) => {
    function stretch_links() {

        return {
            init: function () {
                this.stretchers = document.querySelectorAll('[data-lvl-stretch-link="true"]:not(.linked)');
                if (!this.stretchers) return;

                this.listeners();
            },

            stretchers: null,

            listeners: function () {
                this.stretchers.forEach(stretched => {

                    const links = stretched.querySelectorAll('a');

                    if (!links.length) return;

                    if (links.length > 1) {
                        stretched.classList.add('linked');
                        stretched.classList.add('linked-multi');
                        return;
                    }

                    let link = links[0];

                    if (link) {
                        stretched.classList.add('linked');

                        // Remove existing event listeners
                        stretched.removeEventListener('click', this.handleClick);

                        // Add new event listeners
                        stretched.addEventListener('click', this.handleClick.bind(this, link));
                    }

                });
            },

            handleClick: function (link, e) {

                if (e.button === 0 || e.button === 1) {
                    e.preventDefault();
                    let pseudoLink = document.createElement('a');
                    pseudoLink.setAttribute('aria-hidden', 'true');
                    pseudoLink.href = link.href;

                    if (e.ctrlKey || e.metaKey || e.button === 1 || link.target === '_blank') {
                        pseudoLink.target = '_blank';
                    }

                    pseudoLink.click();
                }
            },

            log: (message) => {
                console.log(message);
            }
        }
    }

    const stretchLinks = stretch_links();
    stretchLinks.init();

    document.addEventListener('lvl-stretch-links', function (e) {
        stretchLinks.init();
    });
});