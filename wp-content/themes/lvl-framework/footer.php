<?php
$pre_footer_img = get_field('pre_footer_img', 'options');
if ($pre_footer_img) {
    ?>
    <div id="pre-footer" class="pre-footer">
        <?php echo wp_get_attachment_image($pre_footer_img, 'full', false, ['class' => 'img-fluid w-100']); ?>
    </div>
    <?php
}
?>    <?php
if(get_field('scroll_to_top', 'options')) {
    ?>
    <div class="scroll-to-top position-fixed z-999" style="bottom: 1rem; right: 1rem;">
        <a href="#top" class="btn btn-outline-dark rounded text-dark bg-light">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-chevron-up" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M7.646 4.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1-.708.708L8 5.707l-5.646 5.647a.5.5 0 0 1-.708-.708z"/>
            </svg>
            <span class="visually-hidden">Scroll to top</span>
        </a>
    </div>
    <?php
}
?>
</main>
<footer id="footer" class="py-5 px-3 fw-light">

    <div class="container">
        <div class="row justify-content-center gap-5">
            <div class="nav-footer-wrapper col-12 col-md-6 col-lg-auto">
                <?php $logo = get_field('footerlogo', 'options');
                if ($logo) {
                    ?>
                    <a href="<?php echo home_url(); ?>" class="navbar-brand" aria-label="Navigate to home.">
                        <?php
//                        if (!empty($logo) && str_contains($logo['url'] ?? '', '.svg')) {
//                            $logopathed = str_replace(home_url(), '', $logo['url']);
//                            echo file_get_contents(ABSPATH . $logopathed);
//                        } elseif (!empty($logo)) {
                            echo wp_get_attachment_image($logo['id'] ?? -1, ['179','60'], false, ['class' => 'img-fluid']);
//                        } ?>
                    </a>
                    <?php
                } ?>

                <?php
                if (is_active_sidebar('lvl-footer-sub-logo')) : ?>
                    <div id="footer-sub-logo" class="footer-sub-logo mt-4 widget-area" role="complementary">
                        <?php dynamic_sidebar('lvl-footer-sub-logo'); ?>
                    </div>
                <?php endif;
                ?>
            </div>

            <?php
            if (is_active_sidebar('lvl-footer-col1')) : ?>
                <div class="nav-footer-wrapper col-12 col-md-6 col-lg-auto">
                    <div id="lvl-footer-col1" class="lvl-footer-col1 widget-area" role="complementary">
                        <?php dynamic_sidebar('lvl-footer-col1'); ?>
                    </div>
                </div>
            <?php endif;
            ?>

            <div class="nav-footer-wrapper col-12 col-md-6 col-lg-auto">
                <?php
                $theme_locations = get_nav_menu_locations();
                if (isset($theme_locations['footer'])) {
                    $menu_obj = get_term($theme_locations['footer'], 'nav_menu');

                    if ($menu_obj) {
                        $cols = 1;
                        if ($menu_obj->count > 2) {
                            $cols = 2;
                        }
                        if($menu_obj->count >= 5) {
                            $cols = 3;
                        }

                        wp_nav_menu([
                            'theme_location' => 'footer',
                            'container'      => '',
                            'menu_class'     => 'widget list-unstyled column-count-' . $cols,
                            'menu_id'        => '',
                            'fallback_cb'    => false,
                        ]);
                    }
                }
                ?>
            </div>

            <?php
            //if (is_active_sidebar('lvl-footer-col2')) : ?>
                <div class="nav-footer-wrapper col-12 col-md-6 col-lg-auto">
                    <div id="lvl-footer-col2" class="widget-area" role="complementary">
                        <?php dynamic_sidebar('lvl-footer-col2'); ?>
                    </div>

                    <?php if (have_rows('social_media_icon_links', 'options')) : ?>
                        <!-- social media icons -->
                        <ul class="list-unstyled social-links pt-3 justify-content-start">
                            <?php while (have_rows('social_media_icon_links', 'options')) :
                                the_row();
                                $url = get_sub_field('url');
                                $title = get_sub_field('name');
                                $icon = get_sub_field('social_links_icon'); ?>
                                <li class="social-link">
                                    <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer">
                                        <span class="visually-hidden"><?php echo $title; ?></span>
                                        <svg role="img" focusable="false">
                                            <use xlink:href="#<?php echo $icon; ?>" aria-hidden="true"></use>
                                        </svg>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php //endif; ?>


        </div>
        <div class="colophon-footer row flex-column pt-md-5 pt-4">
            <div class="nav-footer-wrapper col text-center">
                <?php

                wp_nav_menu([
                    'theme_location' => 'colophon',
                    'container'      => '',
                    'menu_class'     => 'list-unstyled d-flex justify-content-center justify-content-lg-start gap-4',
                    'menu_id'        => '',
                    'fallback_cb'    => false,
                ]);

                ?>
            </div>
            <div class="col text-center">
                <div class="colophon-wrap">
                    <div>Copyright &copy; <?php echo date('Y'); ?><br/><?php //echo get_bloginfo('name') . '.'; ?> <span class="colophon"><?php echo get_field('colophon', 'options'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
<div id="searchModal" class="modal fade" tabindex="-1" role="dialog" aria-label="Search Form" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered--" role="document">
        <div class="modal-content bg-transparent-- border-0--">

            <div class="modal-header">
                <h1 class="modal-title h5">Site Search</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!--                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>-->
                <?php get_search_form(); ?>
            </div>
            <div class="modal-footer visually-hidden">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>


        </div>
    </div>
</div>
<?php wp_footer(); ?>
</body>
</html>
