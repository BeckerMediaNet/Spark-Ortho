document.addEventListener('DOMContentLoaded', () => {
    const searchDropdowns = document.querySelectorAll('.wp-block-search-dropdown.dropdown');
    if (!searchDropdowns) {
        return;
    }

    searchDropdowns.forEach(searchDropdown => {
        const input = searchDropdown.querySelector('input');
        const dropdown = searchDropdown.querySelector('.dropdown-menu');

        input.addEventListener('focus', () => {
            dropdown.classList.add('show');
            searchDropdown.setAttribute('aria-expanded', 'true');
        });

        // if click outsite of serach dropdown, close dropdown
        document.addEventListener('click', (e) => {
            if (!searchDropdown.contains(e.target)) {
                dropdown.classList.remove('show');
                searchDropdown.setAttribute('aria-expanded', 'false');
            }
        });
    });

});