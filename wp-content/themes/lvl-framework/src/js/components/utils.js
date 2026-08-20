/** UTILITY FUNCTIONS
 * util.getCookie();
 * util.setCookie();
 * util.checkCookie();
 * util.getQueryParam();
 */

const util = {

    // Gets a cookie and returns the cookies value, if no cookie exists it returns blank '.
    getCookie: function (c_name) {
        if (document.cookie.length > 0) {
            var c_start = document.cookie.indexOf(c_name + '=');
            if (c_start != -1) {
                c_start = c_start + c_name.length + 1;
                var c_end = document.cookie.indexOf(';', c_start);
                if (c_end == -1) {
                    c_end = document.cookie.length;
                }
                return unescape(document.cookie.substring(c_start, c_end));
            }
        }
        return '';
    },

    // Sets a cookie with your given ('cookie name', 'cookie value', 'good for x days').
    setCookie: function (c_name, value, expiredays) {
        var exdate = new Date();
        exdate.setDate(exdate.getDate() + expiredays);
        document.cookie = c_name + '=' + escape(value) + ((expiredays == null) ? '' : '; expires=' + exdate.toUTCString()) + '; path=/';
    },

    // Checks to see if a cookie exists, then returns boolean (true/false).
    checkCookie: function (c_name) {
        c_name = util.getCookie(c_name);
        return ((c_name != null && c_name != ''));
    },

    // Gets a query string parameter value by name.
    // Defaults to location.href but can accept any URL to parse.
    getQueryParam: function (name, url) {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, '\\$&');
        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    }

};

// Set scrollbar width css custom property
function setScrollbarWidthVariable() {
    const outer = document.createElement('div');
    outer.style.visibility = 'hidden';
    outer.style.overflow = 'scroll'; // forcing scrollbar to appear

    const inner = document.createElement('div');
    outer.appendChild(inner);

    document.body.appendChild(outer);

    const scrollbarWidth = outer.offsetWidth - inner.offsetWidth; // Difference in width is scrollbar

    // Remove the divs
    outer.parentNode.removeChild(outer);

    // Set CSS variable
    document.body.style.setProperty('--scrollbar-width', scrollbarWidth + "px");
}

// const throttle = (fn, delay) => {
//     let time = Date.now();
//     return () => {
//         if ((time + delay - Date.now()) <= 0) {
//             fn();
//             time = Date.now();
//         }
//     }
// }

document.addEventListener('DOMContentLoaded', (e) => {
    setScrollbarWidthVariable();

    let windowWidth = window.innerWidth;
    window.addEventListener('resize', () => {
        windowWidth = window.innerWidth;
    });

    // debugger to find elements extending width of viewport:
    // var docWidth = document.documentElement.offsetWidth;
    // [].forEach.call(
    // 	document.querySelectorAll('*'),
    // 	function(el) {
    // 		if (el.offsetWidth > docWidth) {
    // 			console.log(el);
    // 		}
    // 	}
    // );

});
