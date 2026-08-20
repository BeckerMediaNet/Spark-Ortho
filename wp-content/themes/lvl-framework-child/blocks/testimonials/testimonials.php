<?php if (! defined('ABSPATH')) {
	exit;
}

// Include AJAX handlers
require_once __DIR__ . '/ajax.php';

$render = function ($block, $is_preview, $content) {

	$the_block = new Level\Block($block);

	$display_all_testimonials = $the_block->getField('display_all_testimonials') ?: false;
	$full_width = $the_block->getField('full_width') ?: false;

	if ($display_all_testimonials) {
		$testimonials_query = new WP_Query([
			'post_type' => 'testimonial',
			'post_status' => 'publish',
			'posts_per_page' => 9,
			'paged' => 1,
			'orderby' => 'date',
			'order' => 'DESC'
		]);

		$testimonials = $testimonials_query->posts;
		$testimonial_count = count($testimonials);
		$is_all_testimonials = true;
		$total_testimonials = $testimonials_query->found_posts;
	} else {
		// Use selected testimonials
		$testimonials = $the_block->getField('testimonials') ?: [];
		$testimonial_count = count($testimonials);
		$is_all_testimonials = false;
		$total_testimonials = $testimonial_count;
	}
	ob_start(); ?>
	<?php if (!$full_width) : ?>
		<div class="container">
		<?php endif; ?>
		<?php if ($testimonial_count > 3 && !$is_all_testimonials) : ?>
			<!-- Show slider navigation for more than 3 testimonials  -->
			<div class="testimonials--navigation">
				<div class="navigation-buttons-wrapper">
					<button class="testimonials-prev" aria-label="Previous">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
						</svg>
					</button>
					<span class="drag-text">Swipe</span>
					<button class="testimonials-next" aria-label="Next">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
						</svg>
					</button>
				</div>
			</div>
		<?php endif; ?>

		<div class="testimonials mb-5 <?php echo $full_width ? 'full-width' : ''; ?>
		 <?php
			if ($is_all_testimonials) {
				echo 'testimonials-grid';
			} elseif ($testimonial_count <= 3) {
				echo 'testimonials-static-grid';
			}
			?>">
			<?php foreach ($testimonials as $index => $testimonial) :
				$quote_content = get_field('quote_content', $testimonial->ID);
				$quote_author = get_field('quote_author', $testimonial->ID);
				$quote_author_title = get_field('quote_author_title', $testimonial->ID);

				$author = [];
				$author['name'] = $quote_author;
				$author['title'] = $quote_author_title;
				$author = array_filter($author);

				// Calculate alternating colors for "Display All Testimonials"
				if ($is_all_testimonials) {
					$row = floor($index / 3);
					$col = $index % 3;
					// Alternating pattern: row 0: [0,1,2], row 1: [1,2,0], row 2: [2,0,1]
					$color_index = ($col + $row) % 3;
					$color_class = '';
					switch ($color_index) {
						case 0:
							$color_class = 'bg-orange';
							break;
						case 1:
							$color_class = 'bg-primary';
							break;
						case 2:
							$color_class = 'bg-lime-green';
							break;
					}
				} else {
					$color_class = '';
				}
			?>

				<div class="<?php
							if ($is_all_testimonials) {
								echo 'testimonial--static ' . $color_class;
							} elseif ($testimonial_count <= 3) {
								echo 'testimonial--static';
							} else {
								echo 'testimonial';
							}
							?>" data-bs-theme="light">
					<figure class="mb-0 p-3 p-lg-4">

						<div class="source d-flex justify-content-center align-items-center mb-4">
							<svg fill="none" height="20" viewBox="0 0 119 20" width="119" xmlns="http://www.w3.org/2000/svg">
								<g fill="#fff">
									<path d="m15.1967 12.5066 5.8033-4.74809-7.5984-.49897-2.9016-6.75954-2.90164 6.75954-7.59836.49897 5.81148 4.74809-1.80328 6.9934 6.4918-3.7501 6.4918 3.7501z" />
									<path d="m39.4731 12.5066 5.5269-4.74809-7.2365-.49897-2.7635-6.75954-2.7635 6.75954-7.2365.49897 5.5347 4.74809-1.7174 6.9934 6.1827-3.7501 6.1827 3.7501z" />
									<path d="m64.1967 12.5066 5.8033-4.74809-7.5984-.49897-2.9016-6.75954-2.9016 6.75954-7.5984.49897 5.8115 4.74809-1.8033 6.9934 6.4918-3.7501 6.4918 3.7501z" />
									<path d="m88.4731 12.5066 5.5269-4.74809-7.2365-.49897-2.7635-6.75954-2.7635 6.75954-7.2365.49897 5.5347 4.74809-1.7174 6.9934 6.1827-3.7501 6.1827 3.7501z" />
									<path d="m113.197 12.5066 5.803-4.74809-7.598-.49897-2.902-6.75954-2.902 6.75954-7.598.49897 5.811 4.74809-1.803 6.9934 6.492-3.7501 6.492 3.7501z" />
								</g>
							</svg>
						</div>
						<blockquote>
							<p><?php echo $quote_content; ?></p>
						</blockquote>
						<?php if (!empty($author)): ?>
							<figcaption class="mt-auto mt-3">
								<?php if (!empty($author['name'])) :
									echo '<span class="author d-block">' . $author['name'] . '</span>';
								endif;

								if (!empty($author['title'])) :
									echo '<span class="title d-block">' . $author['title'] . '</span>';
								endif; ?>
							</figcaption>
						<?php endif; ?>

					</figure>
				</div>

			<?php endforeach; ?>
		</div>


		<?php if ($is_all_testimonials && $total_testimonials > 9) : ?>
			<!-- Load More Button for all testimonials -->
			<div class="text-center mt-5">
				<button class="btn btn-primary load-more-testimonials"
					data-page="2"
					data-max-pages="<?php echo ceil($total_testimonials / 9); ?>"
					data-ajax-url="<?php echo admin_url('admin-ajax.php'); ?>">
					Load More Testimonials
				</button>
			</div>
		<?php endif; ?> <?php if (!$full_width) : ?>
		</div>
	<?php endif; ?>

<?php

	$output = ob_get_clean();

	echo $the_block->renderSection($output, 'basic');
};

$render($block, $is_preview, $content);
