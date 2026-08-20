<?php if (!defined('ABSPATH')) {
	exit;
}

$render = function ($block, $is_preview, $content) {

	$the_block = new Level\Block($block, ['is_preview' => $is_preview]);

	$post_type = 'dentist';
	$cards_per_row = 3;
	$display_mode = $the_block->getField('display_mode') ?: 'related';
	$show_filters = $the_block->getField('show_filters') ?: false;
	$taxonomy_filters = $the_block->getField('taxonomy_filters') ?: ['specialty'];
	$per_page = $the_block->getField('per_page') ?: 12;
	$image_fit = $the_block->getField('image_fit') ?: 'cover';

	// Add data attributes for AJAX filtering
	if ($display_mode === 'all') {
		$the_block->addAttribute(['data-mode' => 'all']);
		$the_block->addAttribute(['data-per-page' => $per_page]);
		$the_block->addAttribute(['data-image-fit' => $image_fit]);
	}

	// Get current page location (if on a location page)
	$current_location = null;
	if (is_singular('location')) {
		$current_location = get_queried_object_id();
	}

	$the_block->addStyle('--card-count:' . $cards_per_row);
	$the_block->addClass('dentist-cards-container');

	// If "all" mode, render filter interface and return early
	if ($display_mode === 'all') {
		require_once get_stylesheet_directory() . '/blocks/dentist-cards/ajax.php';

		ob_start(); ?>

		<div class="dentist-cards-wrapper">
			<?php if ($show_filters) : ?>
				<div class="row pb-5">
					<div class="col-12">
						<div class="filter-bar row align-items-end">

							<?php
							if ($taxonomy_filters) {
								foreach ($taxonomy_filters as $filter) {
									$taxonomy = get_taxonomy($filter);
									if (!$taxonomy) {
										continue;
									}
							?>
									<div class="filter col-12 col-md mt-4 mt-md-2">
										<h5 class="has-body-large-font-size">By <?php echo strtolower($taxonomy->labels->singular_name); ?></h5>
										<div class="dropdown">
											<button class="dropdown-toggle" type="button" id="<?php echo $filter; ?>_select" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false"><?php echo $taxonomy->label; ?></button>
											<ul class="dropdown-menu" data-bs-offset="0,10" aria-labelledby="<?php echo $filter; ?>_select">
												<?php
												$terms = get_terms([
													'taxonomy'   => $filter,
													'hide_empty' => true,
													'exclude'    => 1,
												]);

												foreach ($terms as $term) {
												?>
													<li class="dropdown-item" data-filter-type="<?php echo $term->taxonomy; ?>" data-filter-value="<?php echo $term->term_id; ?>" tabindex="0"><?php echo $term->name; ?></li>
												<?php } ?>
											</ul>
										</div>
									</div>
								<?php
								}
							}

							// Always show location filter as a fallback
							if (!in_array('location_service', $taxonomy_filters)) {
								?>
								<div class="filter col-12 col-md mt-4 mt-md-2">
									<h5 class="has-body-large-font-size">By location</h5>
									<div class="dropdown">
										<button class="dropdown-toggle" type="button" id="location_select" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">Location</button>
										<ul class="dropdown-menu" data-bs-offset="0,10" aria-labelledby="location_select">
											<?php
											$locations = get_posts([
												'post_type' => 'location',
												'post_status' => 'publish',
												'posts_per_page' => -1,
												'orderby' => 'title',
												'order' => 'ASC'
											]);
											foreach ($locations as $location) : ?>
												<li class="dropdown-item" data-filter-type="location" data-filter-value="<?php echo esc_attr($location->ID); ?>" tabindex="0"><?php echo esc_html(get_the_title($location->ID)); ?></li>
											<?php endforeach; ?>
										</ul>
									</div>
								</div>
							<?php } ?>

							<div class="filter col-12 col-md mt-4 mt-md-2">
								<h5 class="has-body-large-font-size">By keyword</h5>
								<label for="keyword" class="sr-only">Keyword Search</label>
								<input class="form-control" name="keyword" id="keyword" placeholder="Keyword search" data-filter-type="keyword" data-filter-value="" />
							</div>

							<div class="filter col-12 col-md-4 col-lg-3 mt-4 mt-lg-0 d-flex gap-3">
								<button class="btn btn-primary filter-submit mt-0">Filter</button>
								<button class="btn btn-secondary-outline filter-reset mt-0 d-none">Reset</button>
							</div>
						</div>

						<div class="filtered" aria-live="polite">

						</div>
					</div>
				</div>
			<?php endif; ?>

			<div class="dentist-cards-flex d-flex gap-4 justify-content-center flex-wrap"></div>

			<div class="dentist-spinner text-center py-5 d-none">
				<div class="spinner-border" role="status">
					<span class="visually-hidden">Loading...</span>
				</div>
			</div>

			<div class="text-center mt-4">
				<button class="btn btn-primary load-more-dentists d-none">Load More</button>
			</div>
		</div>

	<?php
		$output = ob_get_clean();
		echo $the_block->renderSection($output, 'basic');
		return;
	}

	// Related mode (existing functionality)
	$posts = [];
	$total_posts = 0;
	$meta_query = [];

	if ($current_location) {
		$location_id_str = (string)$current_location;
		$search_pattern = 's:' . strlen($location_id_str) . ':"' . $location_id_str . '"';

		$meta_query[] = [
			'key' => 'location',
			'value' => $search_pattern,
			'compare' => 'LIKE'
		];
	}	// Always try to show 3 cards maximum
	$max_cards = 3;

	// First, get regular dentists (non-orthodontists) - up to 2 cards
	$tax_query = [];
	if (taxonomy_exists('specialty') && term_exists('orthodontist', 'specialty')) {
		$tax_query = [
			[
				'taxonomy' => 'specialty',
				'field' => 'slug',
				'terms' => 'orthodontist',
				'operator' => 'NOT IN'
			]
		];
	}

	$regular_query_args = [
		'post_type' => $post_type,
		'post_status' => 'publish',
		'posts_per_page' => 2,
		'orderby' => 'rand',
		'meta_query' => $meta_query,
		'tax_query' => $tax_query
	];

	$regular_query = new WP_Query($regular_query_args);
	$posts = $regular_query->posts;

	// Try to get an orthodontist for the 3rd card
	$ortho_tax_query = [];
	if (taxonomy_exists('specialty') && term_exists('orthodontist', 'specialty')) {
		$ortho_tax_query = [
			[
				'taxonomy' => 'specialty',
				'field' => 'slug',
				'terms' => 'orthodontist',
				'operator' => 'IN'
			]
		];
	}

	$ortho_query_args = [
		'post_type' => $post_type,
		'post_status' => 'publish',
		'posts_per_page' => 1,
		'orderby' => 'rand',
		'meta_query' => $meta_query,
		'tax_query' => $ortho_tax_query,
		'post__not_in' => array_map(function ($post) {
			return $post->ID;
		}, $posts)
	];

	$ortho_query = new WP_Query($ortho_query_args);
	if ($ortho_query->have_posts()) {
		$posts[] = $ortho_query->posts[0];
	} else {
		// No orthodontist found, try to get one more regular dentist if we have less than 3
		if (count($posts) < $max_cards) {
			$additional_dentist_args = [
				'post_type' => $post_type,
				'post_status' => 'publish',
				'posts_per_page' => $max_cards - count($posts),
				'orderby' => 'rand',
				'meta_query' => $meta_query,
				'tax_query' => $tax_query,
				'post__not_in' => array_map(function ($post) {
					return $post->ID;
				}, $posts)
			];

			$additional_query = new WP_Query($additional_dentist_args);
			if ($additional_query->have_posts()) {
				foreach ($additional_query->posts as $additional_post) {
					$posts[] = $additional_post;
				}
			}
		}
	}

	$total_posts = count($posts);

	// Simplified taxonomy handling - always show dentist specialty and location

	ob_start(); ?>

	<div class="dentist-cards-wrapper">
		<?php if (!empty($posts)) : ?>
			<div class="dentist-cards-flex d-flex gap-4 justify-content-center flex-wrap" data-current-page="1" data-total-posts="<?php echo $total_posts; ?>">
				<?php foreach ($posts as $index => $post) :
					setup_postdata($post);
					$post_id = $post->ID;

					$post_card_image = null;
					$featured_image = get_post_thumbnail_id($post_id);

					if ($post_type === 'dentist') {
						$post_card_image_field = get_field('post_card_image', $post_id);
						if ($post_card_image_field && is_array($post_card_image_field) && !empty($post_card_image_field['ID'])) {
							$post_card_image = $post_card_image_field;
							$featured_image = $post_card_image['ID'];
						}
					}

					$excerpt = get_the_excerpt($post_id);
					$permalink = get_permalink($post_id);

					// Responsive excerpt length
					$excerpt_short = wp_trim_words($excerpt, 30, '...');
					$excerpt_full = wp_trim_words($excerpt, 50, '...');

					// Get specialty and location
					$specialty_terms = get_the_terms($post_id, 'specialty');
					$specialty_name = '';
					if (!is_wp_error($specialty_terms) && !empty($specialty_terms)) {
						$specialty_name = $specialty_terms[0]->name;
					}

					// Get location information
					$location_display = '';
					$location_field = get_field('location', $post_id);

					if ($location_field) {
						if (is_array($location_field) && !empty($location_field)) {
							// If on a location page, show only that location
							if ($current_location) {
								foreach ($location_field as $location) {
									$loc_id = is_object($location) ? $location->ID : $location;
									if ($loc_id == $current_location) {
										$location_display = get_the_title($loc_id);
										break;
									}
								}
							} else {
								// Not on location page, show all locations
								$location_names = [];
								foreach ($location_field as $location) {
									if (is_object($location) && isset($location->ID)) {
										$location_names[] = get_the_title($location->ID);
									} elseif (is_numeric($location)) {
										$location_names[] = get_the_title($location);
									}
								}
								$location_display = implode(', ', $location_names);
							}
						} elseif (is_object($location_field) && isset($location_field->ID)) {
							// Single location object
							$location_display = get_the_title($location_field->ID);
						} elseif (is_numeric($location_field)) {
							// Single location ID
							$location_display = get_the_title($location_field);
						}
					}
				?>
					<article class="dentist-card">
						<div class="dentist-card-inner">
							<div class="dentist-card-image">
								<?php if ($featured_image) : ?>
									<?php if ($post_card_image && is_array($post_card_image)) : ?>
										<?php echo wp_get_attachment_image($post_card_image['ID'], 'medium_large', false, [
											'class' => 'dentist-card-img',
											'loading' => 'lazy',
											'alt' => $post_card_image['alt'] ?: get_the_title($post_id),
											'style' => 'object-fit: ' . esc_attr($image_fit) . ';'
										]); ?>
									<?php else : ?>
										<?php echo wp_get_attachment_image($featured_image, 'medium_large', false, [
											'class' => 'dentist-card-img',
											'loading' => 'lazy',
											'style' => 'object-fit: ' . esc_attr($image_fit) . ';'
										]); ?>
									<?php endif; ?>
								<?php else : ?>
									<div class="dentist-card-placeholder">
										<svg width="60" height="60" viewBox="0 0 24 24" fill="currentColor">
											<path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z" />
										</svg>
									</div>
								<?php endif; ?>
							</div>

							<!-- Content Section -->
							<div class="dentist-card-content">

								<h3 class="dentist-card-title">
									<?php echo get_the_title($post_id); ?>
								</h3>

								<div class="dentist-card-meta">
									<?php if ($specialty_name) : ?>
										<div class="dentist-specialty">
											<?php echo esc_html($specialty_name); ?>
										</div>
									<?php endif; ?>
									<?php if ($location_display) : ?>
										<div class="dentist-location">
											<?php echo esc_html($location_display); ?>
										</div>
									<?php endif; ?>
								</div>
							</div>

							<!-- Hover Content -->
							<div class="dentist-card-hover-content">

								<h3 class="dentist-card-title">
									<?php echo get_the_title($post_id); ?>
								</h3>

								<div class="dentist-card-meta">
									<?php if ($specialty_name) : ?>
										<div class="dentist-specialty">
											<?php echo esc_html($specialty_name); ?>
										</div>
									<?php endif; ?>
									<?php if ($location_display) : ?>
										<div class="dentist-location">
											<?php echo esc_html($location_display); ?>
										</div>
									<?php endif; ?>
								</div>

								<?php if ($excerpt) : ?>
									<p class="dentist-card-excerpt d-none d-md-block"><?php echo $excerpt_full; ?></p>
									<p class="dentist-card-excerpt d-md-none"><?php echo $excerpt_short; ?></p>
								<?php endif; ?>

								<a href="<?php echo esc_url($permalink); ?>" class="btn btn-link-secondary">
									View More
								</a>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
				<?php wp_reset_postdata(); ?>
			</div>

		<?php else : ?>
			<div class="dentist-cards-empty">
				<p>No dentists found for the selected criteria.</p>
			</div>
		<?php endif; ?>
	</div>

<?php

	$output = ob_get_clean();

	echo $the_block->renderSection($output, 'basic');
};

$render($block, $is_preview, $content);
