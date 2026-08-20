import Swiper from 'swiper';
import 'swiper/css';
// BETTER TO CUSTOM CODE YOU OWN CSS FOR NAVIGATION AND PAGINATION:
// import 'swiper/css/navigation';
// import 'swiper/css/pagination';

// IMPORT GLOBAL INDIVIDUAL MODULES AS NEEDED (RECOMMENDED):
import 'swiper/css/effect-fade';
import {
    Navigation,
    Pagination,
    Autoplay,
    A11y,
    EffectFade,
    // Keyboard
} from 'swiper/modules';


window.Swiper = Swiper;
window.Navigation = Navigation;
window.Pagination = Pagination;
window.Autoplay = Autoplay;
window.A11y = A11y;
window.EffectFade = EffectFade;
// window.Keyboard = Keyboard;

// https://swiperjs.com/swiper-api
// BEGIN GLOBAL CUSTOMIZATIONS HERE:
document.addEventListener('DOMContentLoaded', (e) => {
    /**
     * Since Swiper is usually used as our customized option for a slider you will
     * likely want to set up the Swiper instance in the block's js file.
     *
     * If all swiper instances are the same, you can set and initial the global Swiper instance here.
     *
     * EXAMPLE:
     */
    // const swiper = new Swiper('.swiper-container', {
    //     // configure Swiper to use modules.
    //     modules: [Navigation, Pagination],
    //     // Optional parameters
    //     direction: 'horizontal',
    //     loop: true,
    //     autoplay: {
    //         delay: 5000,
    //     },
    //     keyboard: {
    //         enabled: true,
    //     },
    //     speed: 1000,
    //     effect: 'fade',
    //     fadeEffect: {
    //         crossFade: true
    //     },
    //     // If we need pagination
    //     pagination: {
    //         el: '.swiper-pagination',
    //         clickable: true,
    //     },
    // });
});