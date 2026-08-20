import {CountUp} from "countup.js";

window.CountUp = CountUp;

// BEGIN GLOBAL CUSTOMIZATIONS HERE:
// class CountUpManager {
//     constructor() {
//         document.addEventListener('DOMContentLoaded', this.init.bind(this));
//     }

//     init() {
//         const countUps = document.querySelectorAll('[data-countup]');
//         countUps.forEach(this.setupCountUp.bind(this));
//     }

//     setupCountUp(countUp) {
//         const count = new CountUp(
//             countUp,
//             parseInt(countUp.dataset.value),
//             {
//                 enableScrollSpy: countUp.dataset?.enableScrollSpy ?? true,
//                 scrollSpyOnce: countUp.dataset?.scrollSpyOnce ?? true,
//                 scrollSpyDelay: (countUp.dataset?.scrollSpyDelay) ? parseInt(countUp.dataset.scrollSpyDelay) : 100,
//                 duration: (countUp.dataset?.duration) ? parseInt(countUp.dataset.duration) : 5,
//                 separator: ',',
//                 decimal: '.',
//                 decimalPlaces: (countUp.dataset?.decimalPlaces) ? parseInt(countUp.dataset.decimalPlaces) : 0,
//                 suffix: countUp.dataset?.suffix ? '<span class="suffix">' + countUp.dataset.suffix + '</span>' : '',
//                 prefix: countUp.dataset?.prefix ? '<span class="prefix">' + countUp.dataset.prefix + '</span>' : '',
//             }
//         );

//         if (!count.error) {
//             count.start();
//         } else {
//             console.error(count.error);
//         }
//     }
// }

// new CountUpManager();