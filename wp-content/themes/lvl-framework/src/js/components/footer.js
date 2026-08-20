document.addEventListener('DOMContentLoaded', function () {
    const setFooterHeight = function () {
        const footer = document.querySelector('#footer');
        if (footer) {
            const height = footer.offsetHeight;
            const body = document.querySelector('body');
            body.style.setProperty('--footer-height', height + 'px');
        }
    }

    setFooterHeight();
});