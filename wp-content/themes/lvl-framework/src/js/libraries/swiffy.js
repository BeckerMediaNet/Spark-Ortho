import {swiffyslider} from 'swiffy-slider';
import "swiffy-slider/css";
window.swiffyslider = swiffyslider;

// https://swiffyslider.com/docs/
// BEGIN GLOBAL CUSTOMIZATIONS HERE:
document.addEventListener('DOMContentLoaded', (e) => {
    swiffyslider.init();

    document.querySelectorAll('.swiffy-slider').forEach((swiffy) => {
        swiffy.classList.add('swiffy-initialized');
    });
});