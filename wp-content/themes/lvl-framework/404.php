<?php get_header();

$hero_message = get_field('hero_message', 'options');

$cta_content = get_field('cta_404', 'options');
$content = $cta_content['content'] ?? '';
$buttons = $cta_content['buttons'] ?? [];
?>

    <section class="banner py-5 bg-primary">
        <div class="container">
            <div class="row">
                <div class="col-12 col-md-6 mx-auto">
                    <div class="text-white text-center">
                        <h1 class="text-white post-type-header"><?php _e('404: Page Not Found'); ?></h1>
                        <p><?php _e($hero_message); ?></p>
                        <p class="mt-5"><a class="btn btn-outline-light" href="/"><?php _e("Return to the homepage.") ?></a></p>
                    </div>
                </div>
            </div>
        </div>

    </section>
    <section class="search">
        <div class="container">
            <div class="row search">
                <div class="col-12 col-lg-6 py-4 col-md-10 m-auto border rounded shadow-sm mt-4">
                    <h2 class="h4 text-center mb-4">Search our website:</h2>
                    <?php get_template_part('searchform'); ?>
                </div>
            </div>
        </div>
    </section>
<?php
if ($content || $buttons) {
    ?>
    <section class="cta py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-10 col-lg-9 mx-auto">
                    <?php echo($content ?: '');
                    if ($buttons) :
                        foreach ($buttons as $button) {
                            $temp = new LVLBlock([]);
                            echo $temp->renderButton($button);
                        }
                    endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php } ?>
    <div class="py-4"></div>
<?php get_footer();