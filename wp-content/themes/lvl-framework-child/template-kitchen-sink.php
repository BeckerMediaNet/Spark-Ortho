<?php

/*
*
* Template Name: Kitchen Sink
* Template Post Type: page
*
*/

add_filter('wpseo_robots', function ($robots) {
	return 'noindex,nofollow';
});

get_header();

if (have_posts()) :
	while (have_posts()) :
		the_post();
		the_content();
	endwhile;
endif;

// Output color pallette from theme.json
$theme_json = get_stylesheet_directory() . '/theme.json';
$theme_json = file_exists($theme_json) ? json_decode(file_get_contents($theme_json), true) : [];

if (!empty($theme_json)) {

	$colors = $theme_json['settings']['color']['palette']; ?>

	<section class="mt-5">
		<div class="container border-top">
			<div class="row">
				<div class="col-12 py-5">
					<h2 class="h1">Theme colors</h2>
					<p>Click the swatch to copy the color hex value to your clipboard.</p>
				</div>
			</div>

			<div class="row g-3">

				<?php foreach ($colors as $color) : ?>

					<div class="col-6 col-md-3 col-lg-2">
						<div class="card overflow-hidden">
							<div class="color-swatch p-5 border-bottom" data-color-swatch="<?php echo $color['color']; ?>" role="button" style="background-color: <?php echo $color['color']; ?>"></div>
							<div class="p-3">
								<p class="my-0"><strong><?php echo $color['name']; ?></strong></p>
								<p class="my-0 has-body-small-font-size"><?php echo $color['color']; ?></p>
								<p class="my-0 has-body-small-font-size"><?php echo 'rgb(' . lvl_hex2rgb($color['color']) . ')'; ?></p>
							</div>
						</div>
					</div>

				<?php endforeach; ?>

			</div>
		</div>
	</section>

<?php }

$args = array(
	'post_type' => 'wp_block',
	'orderby' => 'title',
	'order' => 'ASC',
	'posts_per_page' => -1,
	'tax_query' => array(
		array(
			'taxonomy' => 'wp_pattern_category',
			'field' => 'slug',
			'terms' => ['templates', 'template'],
			'operator' => 'NOT IN'
		)
	)
);

$blocks_query = new WP_Query($args);

if ($blocks_query->have_posts()) : ?>

	<section class="my-5">
		<div class="container border-top">
			<div class="row">
				<div class="col-12 col-md-8 pt-5">
					<h2 class="h1">Available patterns</h2>
					<p>Patterns are reusable blocks of content that can easily be inserted into the editor. Synced patterns take WordPress patterns a step further by allowing you to update patterns across multiple pages or posts simultaneously. This is especially useful when you have a common element that appears in multiple places on your website and you want to make a global change such as a CTA block that is above the footer on all pages.
				</div>
			</div>

			<div id="toc" class="row mt-5">
				<div class="col-12">
					<h3>Table of contents</h3>
					<ul class="list-unstyled column-count-md-2 column-count-lg-4">
						<?php while ($blocks_query->have_posts()) :
							$blocks_query->the_post(); ?>

							<li><a href="#<?php echo get_the_ID(); ?>"><?php echo get_the_title(); ?></a></li>

						<?php endwhile ?>
					</ul>
				</div>
			</div>
		</div>

	</section>

	<?php while ($blocks_query->have_posts()) :
		$blocks_query->the_post();

		if (!get_field('pattern_hide', get_the_ID())) :

			ob_start(); ?>

			<section id="<?php echo get_the_ID(); ?>" class="py-5 border-top">

				<div class="container">
					<div class="wp-block-columns alignfull">
						<div class="col-12 col-md-8">
							<h2 class="mb-4"><?php echo (get_field('pattern_title', get_the_ID())) ?: the_title(); ?>

								<?php if (is_user_logged_in()) { ?>
									<a href="#" data-copy-target="<?php echo esc_html(get_the_content()); ?>" title="Copy Pattern"><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" class="d-inline-block">
											<path d="M360-240q-33 0-56.5-23.5T280-320v-480q0-33 23.5-56.5T360-880h360q33 0 56.5 23.5T800-800v480q0 33-23.5 56.5T720-240H360Zm0-80h360v-480H360v480ZM200-80q-33 0-56.5-23.5T120-160v-560h80v560h440v80H200Zm160-240v-480 480Z" fill="currentColor" />
										</svg><span class="visually-hidden">Copy Pattern</span></a>

									<a href="<?php echo get_edit_post_link(); ?>" target="_blank" title="Edit Pattern"><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" class="d-inline-block">
											<path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z" fill="currentColor" />
										</svg><span class="visually-hidden">Edit Pattern</span></a>
								<?php } ?>

							</h2>
							<?php if (get_the_excerpt(get_the_ID())) : ?>
								<p class="mb-4"><?php echo get_the_excerpt(get_the_ID()); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<?php the_content(); ?>

			</section>

	<?php

			$pattern = ob_get_clean();
			echo $pattern;

		endif;

	endwhile;

	wp_reset_postdata();
endif;

add_action('wp_footer', function () { ?>

	<script>
		document.addEventListener('DOMContentLoaded', function() {

			document.querySelectorAll('[data-color-swatch]').forEach(function(swatch) {
				swatch.addEventListener('click', function(event) {
					event.preventDefault();
					const color = event.currentTarget.dataset.colorSwatch;
					const clipBoard = navigator.clipboard;
					clipBoard.writeText(color);

				});
			});

			document.querySelectorAll('[data-copy-target]').forEach(function(copyButton) {
				copyButton.addEventListener('click', function(event) {
					event.preventDefault();

					const link = event.target.closest('a');
					const pattern = link.dataset.copyTarget;
					const clipBoard = navigator.clipboard;
					clipBoard.writeText(pattern);

				});
			});
		});
	</script>

<?php });

get_footer();
