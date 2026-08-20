<?php if (! defined('ABSPATH')) {
	exit;
}

$download_file     = get_field('download_file');
$external_url      = get_field('external_url');
$disable_page_view = get_field('disable_page_view');
// $show_author       = get_field( 'show_author' ) ?? true;
// $show_date         = get_field( 'show_date' ) ?? true;

if ($download_file && $disable_page_view) {
	wp_safe_redirect(wp_get_attachment_url($download_file));
	exit;
}

if ($external_url && $disable_page_view) {
	wp_redirect($external_url);
	exit;
}

$schema_type = match (get_post_type()) {
	'post' => 'BlogPosting',
	'event' => 'Event',
	'vendor' => 'Organization',
	'press-release' => 'NewsArticle',
	'video-podcast' => 'PodcastEpisode',
	default => 'WebPage',
};

$schema = [
	'@context'         => 'https://schema.org',
	'@type'            => $schema_type,
	'headline'         => get_the_title(),
	'datePublished'    => get_the_date('Y-m-d'),
	'dateModified'     => get_the_modified_date('Y-m-d'),
	'author'           => [
		'@type' => 'Person',
	],
	'publisher'        => [
		'@type' => 'Organization',
		'name'  => get_bloginfo('name'),
		'logo'  => [
			'@type'  => 'ImageObject',
			'url'    => get_template_directory_uri() . '/dist/img/logo.svg',
			'width'  => 200,
			'height' => 82,
		],
	],
	'mainEntityOfPage' => [
		'@type' => 'WebPage',
		'@id'   => get_the_permalink(),
	],
];

$display_featured_image = false;
if (has_post_thumbnail()) {
	$schema['image'] = [
		'@type' => 'ImageObject',
	];

	//    $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
	//    if ($image) {
	//        $image_width = $image[1];
	//        $image_height = $image[2];
	//        $schema['image']['url'] = $image[0];
	//        $schema['image']['width'] = $image_width;
	//        $schema['image']['height'] = $image_height;
	//    }
}

add_action('wp_head', function () use ($schema) {
	echo '<script type="application/ld+json">' . json_encode($schema) . '</script>';
});

get_header();

if (have_posts()) :
	while (have_posts()) :
		the_post();

		if (is_singular()) :

			$id = 'resource_' . get_the_ID();
			$cat        = get_the_terms($post, 'category');
?>

			<section class="single-article">

				<article <?php post_class('type---post-single'); ?>>

					<div class="container">
						<div class="row mb-5">
							<?php if ($display_featured_image && has_post_thumbnail()) { ?>
								<?php
								$image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
								if ($image) {
								?>
									<div class="col-12 col-lg-10 col-xl-8 m-auto">
										<div class="featured-image my-3">
											<?php the_post_thumbnail('large', ['class' => 'img-fluid w-100']); ?>
										</div>
									</div>
								<?php } ?>
							<?php } ?>
						</div>
						<div class="row mb-4">
							<div class="col-12 col-lg-10 col-xl-8 m-auto">
								<?php
								echo ($cat ? '<span class="d-inline-block lh-1 py-1 px-2 rounded text-secondary border border-secondary">' . $cat[0]->name . '</span>' : '');
								?>
								<h1 class="post-title"><?php the_title(); ?></h1>
							</div>
						</div>
						<div class="row mb-5">
							<div class="col-12 col-lg-10 col-xl-8 m-auto">
								<div class="meta">
								</div>
							</div>
						</div>

						<?php
						if ($download_file) {
						?>
							<div class="row mb-5">
								<div class="col-12 col-lg-10 col-xl-8 m-auto">
									<div class="py-3 px-5 bg-primary-subtle rounded border border-primary text-center" data-lvl-stretch-link="true">
										<h2 id="download-label" class="h5 text-body d-inline-block me-3">
											"<?php the_title(); ?>"
											<?php _e('is available for download.', 'theme'); ?>
										</h2>
										<a href="<?php echo wp_get_attachment_url($download_file); ?>" class="btn btn-primary" target="_blank" rel="nofollow" aria-labelledby="download-label">
											<?php _e('Download', 'theme'); ?>
										</a>
									</div>
								</div>
							</div>
						<?php
						} elseif ($external_url) {
							$is_local = strpos($external_url, get_site_url()) !== false;
							$message = $is_local ? __('is available to view.', 'theme') : __('is available on an external site.', 'theme');
							$cta = $is_local ? __('View', 'theme') : __('View', 'theme');
						?>
							<div class="row mb-5">
								<div class="col-12 col-lg-10 col-xl-8 m-auto">
									<div class="py-3 px-5 bg-primary-subtle rounded border border-primary text-center" data-lvl-stretch-link="true">
										<h2 id="external-label" class="h5 text-body d-inline-block me-3">
											"<?php the_title(); ?>"
											<?php echo $message; ?>
										</h2>
										<a href="<?php echo $external_url; ?>" class="btn btn-primary" target="_blank" rel="nofollow" aria-labelledby="external-label">
											<?php echo $cta; ?>
										</a>
									</div>
								</div>
							</div>
						<?php
						}
						?>
					</div>
					<?php
					//                    if (has_blocks()) {
					//                        the_content();
					//                    } else {
					?>
					<div class="post-content container<?php echo (has_blocks() ? '' : ' no-blocks'); ?>">
						<div class="row">
							<div class="col-12 col-lg-10 col-xl-8 m-auto">
								<?php
								$content = get_the_content();
								// remove img with alt="Image ID: ###"
								$content = preg_replace('/<img[^>]*alt="Image ID: \d+"[^>]*>/', '', $content);

								echo apply_filters('the_content', $content);

								// the_content();

								if (strpos($content, '<!-- wp:') === false && $post_type === 'technical-paper') {
									echo lvl_attachments_to_gallery_slideshow(get_the_ID());
								}

								?>
							</div>
						</div>
					</div>
					<?php
					//                    }
					?>
					<?php // echo get_template_part( 'includes/share' );
					?>

				</article>

			</section>

			<section class="share-post">

				<div class="container mb-5">
					<div class="row align-items-end">
						<div class="col-12 col-lg-10 col-xl-8 m-auto">
							<h5 class="mb-0"><?php _e('Share'); ?>:</h5>
							<?php $content = strip_tags(strip_shortcodes(get_the_content()));
							//                            $excerpt = lvl_base_get_excerpt_by_word_count(trim($content), 45);
							$excerpt = get_the_excerpt();
							?>
							<a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo rawurlencode(get_the_permalink()); ?>&title=<?php echo rawurlencode(get_the_title()); ?>" target="_blank" rel="noopener noreferrer">
								<svg>
									<use xlink:href="#linkedin"></use>
								</svg>
								<span class="visually-hidden">Linkedin</span>
							</a>
							<a href="https://twitter.com/intent/tweet?text=<?php echo rawurlencode($excerpt); ?>&amp;url=<?php echo rawurlencode(get_the_permalink()); ?>" target="_blank" rel="noopener noreferrer">
								<svg>
									<use xlink:href="#twitter"></use>
								</svg>
								<span class="visually-hidden">Twitter</span>
							</a>
							<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode(get_the_permalink()); ?>" target="_blank" rel="noopener noreferrer">
								<svg>
									<use xlink:href="#facebook"></use>
								</svg>
								<span class="visually-hidden">Facebook</span>
							</a>
							<a href="mailto:?subject=Check%20this%20out%20&amp;body=<?php echo rawurlencode('Here\'s the link: ' . get_the_permalink()); ?>" target="_blank" rel="noopener noreferrer">
								<svg>
									<use xlink:href="#mail"></use>
								</svg>
								<span class="visually-hidden">E-mail</span>
							</a>
						</div>
					</div>
				</div>

			</section>

			<div class="container py-4">
				<div class="row">
					<div class="col-12">
						<div class="divider">
							<hr>
						</div>
					</div>
				</div>
			</div>


<?php else :

			the_content();

		endif;
	endwhile;
endif;

get_footer();
