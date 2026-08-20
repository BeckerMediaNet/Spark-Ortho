document.addEventListener('DOMContentLoaded', (e) => {
    /** THROTTLE FUNCTION */
    const throttle = (fn, delay) => {
        let time = Date.now();
        return () => {
            if ((time + delay - Date.now()) <= 0) {
                fn();
                time = Date.now();
            }
        }
    }

    /** SET SCROLLED CLASS ON BODY WHEN SCROLLED */
    const runOnScroll = function (init = false) {
        const body = document.querySelector('body');
        if (body.classList.contains('default')) {
            setHeaderHeight(null);
            return;
        }

        const header = document.querySelector('#header');
        if (!header) return;
        const lastScrollPos = parseInt(body.dataset.lastScroll);
        const scrollPos = window.scrollY;
        const hasScrolled = body.classList.contains('scrolled');

        if (scrollPos <= (header.offsetHeight < 100 ? 100 : header.offsetHeight))
            body.classList.remove('scrolled');
        else if (scrollPos > 0 && !hasScrolled)
            body.classList.add('scrolled');

        if (scrollPos < lastScrollPos) {
            body.classList.add('scrolled--up');
            body.classList.remove('scrolled--down');

            setHeaderHeight(header.offsetHeight);
        } else {
            body.classList.remove('scrolled--up');
            body.classList.add('scrolled--down');

            if (body.classList.contains('peekaboo-nav'))
                setHeaderHeight(null);
        }

        if (init || body.classList.contains('sticky-nav'))
            setHeaderHeight(header.offsetHeight);

        body.dataset.lastScroll = scrollPos.toString(); // set data attribute to current scroll position
    }

    /** ENABLE HOVER TO CLICK DROPDOWN ON MOBILE */
    const mobileDropdownHoverToClick = function () {
        if (window.innerWidth > 991) {
            // console.log('hover')
            // const dropdowns = document.querySelectorAll('#header [data-bs-toggle="dropdown"]');
            // if (!dropdowns) return;
            //
            // dropdowns.forEach(dropdown => {
            //     dropdown.setAttribute('data-bs-toggle', 'dropdown-hover');
            //     dropdown.dispose();
            //     // dropdown.removeEventListener('click', function (event) {
            //     //     // event.preventDefault();
            //     // });
            // });

            return;
        }

        const dropdowns = document.querySelectorAll('[data-bs-toggle="dropdown-hover"]');
        if (!dropdowns) return;

        // console.log('dropdown');
        dropdowns.forEach(dropdown => {
            // update to dropdown
            dropdown.setAttribute('data-bs-toggle', 'dropdown');

            // if dropdown is expanded then follow link if linked
            dropdown.addEventListener('click', function (event) {

                if (this.classList.contains('dropdown-hover-clicked')) {
                    if (this?.href) {
                        window.location = this.href;
                    }
                }
                dropdowns.forEach(dropdown => {
                    dropdown.classList.remove('dropdown-hover-clicked');
                });

                this.classList.add('dropdown-hover-clicked');
            });
        });


        // const dropdownElementList = document.querySelectorAll('.dropdown-toggle')
        const dropdownList = [...dropdowns].map(dropdownToggleEl => new Dropdown(dropdownToggleEl))
    }

	// Handle all search modal links
    document.querySelectorAll('a[href="#search"]').forEach(searchAnchor => {

		searchAnchor.classList.add('search-link');

		searchAnchor.addEventListener('click', function (e) {
			e.preventDefault();
			let searchModal = new window.Modal(document.getElementById('searchModal'));
			searchModal.show();
		});
	});


    /** FOCUS ON SEARCHMODAL FORM INPUT AND SELECT ALL TEXT */
    const searchModal = document.getElementById('searchModal');
    if (searchModal) {
        searchModal.addEventListener('shown.bs.modal', function (event) {
            const input = this.querySelector('input');
            if (!input) return;
            input.focus();
            input.select();
        });
    }

    /** ALLOW POPPER.JS TO DYNAMICALLY POSITION THE DROPDOWN IN THE NAVBAR (prevent page overflow) */
    Dropdown.prototype._detectNavbar = function () {
        return false;
    };

    /** SET ACTIVE CLASS ON NAVBAR TOGGLE */
    const flipPatty = function () {
        // TODO: UPDATE to work with toggler

        const navbarOffCanvas = document.getElementById('navbarOffcanvas');
        const hamburger = document.querySelector('#hamburger');

        if (!navbarOffCanvas || !hamburger) return;


        navbarOffCanvas.addEventListener('show.bs.offcanvas', event => {
            hamburger.classList.add('is-active');
        })

        navbarOffCanvas.addEventListener('hide.bs.offcanvas', event => {
            hamburger.classList.remove('is-active');
        })
    };


    /** SET LOCATION DROPDOWN */
    const locationDropdownSet = function () {
        let navLocation = document.querySelector('.nav-location');
        if (!navLocation) return;

        // loop over dropdown links to find the href that most closely matches the current page
        let navLocationDropdownItems = document.querySelectorAll('.nav-location .dropdown-menu .menu-item a');
        let closestMatch = null;
        if (navLocationDropdownItems) {
            let match = 0;
            navLocationDropdownItems.forEach(link => {
                // compare the href to the current page and the length of the match
                if (window.location.href.includes(link.href) && link.href.length > match) {
                    match = link.href.length;
                    closestMatch = link;
                }
            });

            if (closestMatch) {
                closestMatch.parentElement.classList.add('active');

                // set the top link to the closest match
                const topA = navLocation.querySelector('a');
                topA.innerHTML = closestMatch.innerHTML;
            }
        }


        // let navLocationDropdownItemsActive = document.querySelector('.nav-location .dropdown-menu .menu-item.active a');
        // if (navLocationDropdownItemsActive) {
        //     const topA = navLocation.querySelector('a');
        //     topA.innerHTML = navLocationDropdownItemsActive.innerHTML;
        // }
    };


    /** SET HEADER HEIGHT ON BODY */
    const setHeaderHeight = function (headerHeightOffset, init = false) {
        const header = document.querySelector('#header');
        if (!header) return;

        if (init)
            document.body.style.setProperty('--header-height', header.offsetHeight + 'px');

        if (headerHeightOffset === null || document.body.classList.contains('default-nav')) {
            document.body.style.removeProperty('--header-height-offset');
            return;
        }

        if (!headerHeightOffset)
            headerHeightOffset = header.offsetHeight;

        document.body.style.setProperty('--header-height-offset', headerHeightOffset + 'px');
    };

    /** TRIGGER BOOTSTRAP DROPDOWN ON HOVER */
    const setDropdownHover = function () {
        const toggler = document.querySelector('#navbar .navbar-toggler');
        if (toggler?.dataset.bsToggle === 'offcanvas') {
            return;
        }

        // const dropdowns = document.querySelectorAll('.dropdown-hover.dropdown');
        const dropdowns = document.querySelectorAll('.dropdown-hover .dropdown-toggle');

        if (!dropdowns) return;

        const navWrapper = document.querySelector('#navbar');
        dropdowns.forEach(dropdown => {

            let args = {
                boundary: navWrapper,
                offset: [0, 0]
            };

            if (dropdown.closest('.dropend')) {
                args = {
                    // offset: [0,0],
                };
            }

            let bsDropdown = new window.Dropdown(dropdown, args);
            if (window.innerWidth < 992) {
                dropdown.setAttribute('data-bs-toggle', 'none');
            } else {
                dropdown.setAttribute('data-bs-toggle', 'dropdown');


                dropdown.addEventListener('pointerup', function (event) {
                    if (event.pointerType === 'touch') {
                        if (dropdown.classList.contains('show')) {
                            if (this?.href) {
                                window.location.href = this.href;
                            }
                        } else {
                            // console.log('touch');
                        }
                    } else {
                        if (event.button === 0) {
                            if (this?.href) {
                                window.location.href = this.href;
                            }
                        }
                    }
                });

                dropdown.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        if (this?.href) {
                            window.location.href = this.href;
                        }
                    }
                });

                let leaveTimeout;

                dropdown.closest('.menu-item').addEventListener('pointerenter', function (event) {
                    if (event.pointerType !== 'mouse') {
                        return;
                    }

                    // console.log('hover');
                    const dropdownMenu = this.querySelector('.dropdown-menu');
                    if (dropdownMenu && !event.currentTarget.contains(event.relatedTarget) && event.type === 'pointerenter') {
                        bsDropdown.show();
                    }

                    clearTimeout(leaveTimeout);
                });


                dropdown.closest('.menu-item').addEventListener('pointerleave', function (event) {
                    if (event.pointerType !== 'mouse') {
                        return;
                    }

                    leaveTimeout = setTimeout(() => {
                        const dropdownMenu = this.querySelector('.dropdown-menu');
                        // if (dropdownMenu && !event.currentTarget.contains(event.relatedTarget) && event.type === 'pointerleave') {
                        bsDropdown.hide();
                        dropdown.blur();
                        // }
                    }, 50);
                });

                dropdown.addEventListener('focus', function (event) {
                    // console.log('focus');
                    let dropdownMenu = this.querySelector('.dropdown-menu');
                    if (!dropdownMenu) {
                        dropdownMenu = this.parentNode.querySelector('.dropdown-menu');
                    }

                    if (dropdownMenu) {
                        bsDropdown.show();
                        // console.log(bsDropdown);
                    }
                });

                dropdown.addEventListener('blur', function (event) {
                    // console.log('blur');
                    const dropdownMenu = this.querySelector('.dropdown-menu');
                    if (dropdownMenu) {
                        bsDropdown.hide();
                    }
                });
            }
        });
    };


    setDropdownHover();
    window.addEventListener('resize', throttle(setDropdownHover, 500));

    setHeaderHeight(false, true);
    window.addEventListener('resize', throttle(setHeaderHeight, 100));

    runOnScroll();
    window.addEventListener('scroll', throttle(runOnScroll, 10));

    flipPatty();

    // locationDropdownSet();

    mobileDropdownHoverToClick();
    // window.addEventListener('resize', throttle(mobileDropdownHoverToClick, 500));
});