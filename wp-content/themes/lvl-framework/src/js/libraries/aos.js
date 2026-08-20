import AOS from 'aos';
import 'aos/dist/aos.css';

// TODO: TBD if practical
// add data-aos="animation_name" to all sections
document.querySelectorAll('.wp-block-column, .wp-block-heading, .block:not(.block--section-wrapper)').forEach((section, index) => {
    section.setAttribute('data-aos', 'fade-up');
    // section.setAttribute('data-aos-delay', index * 100);
});

AOS.init({
    offset: 200,
    duration: 600,
    easing: 'ease-in-sine',
    delay: 100,
});