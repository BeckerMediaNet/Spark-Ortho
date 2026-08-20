<?php if (!defined('ABSPATH')) {
	exit;
}

$render = function ($block, $is_preview, $content) {

	$the_block = new Level\Block($block, ['is_preview' => $is_preview]);

	$post_type      = 'team';
	$cards_per_row  = 3;
	$display_mode   = $the_block->getField('display_mode') ?: 'related';
	$show_filters   = $the_block->getField('show_filters') ?: false;
	$taxonomy_filters = $the_block->getField('taxonomy_filters') ?: ['specialty'];
	$per_page       = $the_block->getField('per_page') ?: 12;
	$image_fit      = $the_block->getField('image_fit') ?: 'cover';
	$related_limit  = $the_block->getField('related_limit') ?: 'limit_3';
	$sort_order     = $the_block->getField('sort_order') ?: 'newest';

	$enable_featured = $the_block->getField('enable_featured_member') ?: false;
	$featured_member = $enable_featured ? $the_block->getField('featured_team_member') : null;
	$featured_id = 0;
	if ($featured_member) {
		$featured_id = is_object($featured_member) ? $featured_member->ID : intval($featured_member);
	}

	// Add data attributes for AJAX filtering
	if ($display_mode === 'all') {
		$the_block->addAttribute(['data-mode' => 'all']);
		$the_block->addAttribute(['data-per-page' => $per_page]);
		$the_block->addAttribute(['data-image-fit' => $image_fit]);
		if ($featured_id) {
			$the_block->addAttribute(['data-featured-id' => $featured_id]);
		}
	}

	// Get current page location (if on a location page)
	$current_location = null;
	if (is_singular('location')) {
		$current_location = get_queried_object_id();
	}

	$the_block->addStyle('--card-count:' . $cards_per_row);
	$the_block->addClass('team-cards-container');

	// If "all" mode, render filter interface and return early
	if ($display_mode === 'all') {
		require_once get_stylesheet_directory() . '/blocks/team-cards/ajax.php';

		ob_start(); ?>

		<div class="team-cards-wrapper">
			<?php if ($show_filters) : ?>
				<div class="row pb-5">
					<div class="col-12">
						<div class="filter-bar row align-items-end">

							<?php
							if (!empty($taxonomy_filters)) {
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
							?>

							<div class="filter col-12 col-md mt-4 mt-md-2">
								<h5 class="has-body-large-font-size">By keyword</h5>
								<label for="keyword" class="sr-only">Keyword Search</label>
								<input class="form-control" name="keyword" id="keyword" placeholder="Keyword search" data-filter-type="keyword" data-filter-value="" />
							</div>

							<div class="filter col-12 col-md-4 col-lg-3 mt-4 mt-lg-0 d-flex gap-3">
								<button class="btn btn-secondary filter-submit mt-0">Filter</button>
								<button class="btn btn-secondary-outline filter-reset mt-0 d-none">Reset</button>
							</div>
						</div>

						<div class="filtered" aria-live="polite">

						</div>
					</div>
				</div>
			<?php endif; ?>

			<div class="team-cards-flex d-flex flex-wrap justify-content-center gap-4"></div>

			<div class="team-spinner text-center py-5 d-none">
				<div class="spinner-border" role="status">
					<span class="visually-hidden">Loading...</span>
				</div>
			</div>

			<div class="text-center mt-4">
				<button class="btn btn-secondary load-more-team d-none">Load More</button>
			</div>
		</div>

	<?php
		$output = ob_get_clean();
		echo $the_block->renderSection($output, 'basic');
		return;
	}

	// Related mode — build the posts array
	$meta_query = [];
	if ($current_location) {
		$location_id_str = (string)$current_location;
		$search_pattern  = 's:' . strlen($location_id_str) . ':"' . $location_id_str . '"';
		$meta_query[]    = [
			'key'     => 'location',
			'value'   => $search_pattern,
			'compare' => 'LIKE'
		];
	}

	$posts = [];

	if ($related_limit === 'all') {
		// Simple query — all members at this location, no specialty mixing
		$orderby_map = [
			'newest'       => ['orderby' => 'date',  'order' => 'DESC'],
			'oldest'       => ['orderby' => 'date',  'order' => 'ASC'],
			'alphabetical' => ['orderby' => 'title', 'order' => 'ASC'],
		];
		$sort_params = $orderby_map[$sort_order] ?? $orderby_map['newest'];

		$location_query = new WP_Query([
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => $sort_params['orderby'],
			'order'          => $sort_params['order'],
			'meta_query'     => $meta_query,
		]);
		$posts = $location_query->posts;

		if ($featured_id) {
			$posts = array_values(array_filter($posts, fn($p) => $p->ID !== $featured_id));
			$featured_post = get_post($featured_id);
			if ($featured_post && $featured_post->post_status === 'publish') {
				array_unshift($posts, $featured_post);
			}
		}
	} else {
		// Limit to 3 with specialty mixing
		$max_cards = 3;

		$excluded_specialties = [];
		if (taxonomy_exists('specialty')) {
			if (term_exists('orthodontist', 'specialty')) {
				$excluded_specialties[] = 'orthodontist';
			}
			if (term_exists('office-manager', 'specialty')) {
				$excluded_specialties[] = 'office-manager';
			}
		}

		$tax_query = [];
		if (!empty($excluded_specialties)) {
			$tax_query = [[
				'taxonomy' => 'specialty',
				'field'    => 'slug',
				'terms'    => $excluded_specialties,
				'operator' => 'NOT IN'
			]];
		}

		$regular_query = new WP_Query([
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => 2,
			'orderby'        => 'rand',
			'meta_query'     => $meta_query,
			'tax_query'      => $tax_query
		]);
		$posts = $regular_query->posts;

		if (count($posts) < 2 && taxonomy_exists('specialty') && term_exists('orthodontist', 'specialty')) {
			$fill_ortho_query = new WP_Query([
				'post_type'      => $post_type,
				'post_status'    => 'publish',
				'posts_per_page' => 2 - count($posts),
				'orderby'        => 'rand',
				'meta_query'     => $meta_query,
				'tax_query'      => [[
					'taxonomy' => 'specialty',
					'field'    => 'slug',
					'terms'    => 'orthodontist',
					'operator' => 'IN'
				]],
				'post__not_in'   => array_map(fn($p) => $p->ID, $posts)
			]);
			foreach ($fill_ortho_query->posts as $ortho_post) {
				$posts[] = $ortho_post;
			}
		}

		$ortho_tax_query = [];
		if (taxonomy_exists('specialty') && term_exists('orthodontist', 'specialty')) {
			$ortho_tax_query = [[
				'taxonomy' => 'specialty',
				'field'    => 'slug',
				'terms'    => 'orthodontist',
				'operator' => 'IN'
			]];
		}

		$ortho_query = new WP_Query([
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'rand',
			'meta_query'     => $meta_query,
			'tax_query'      => $ortho_tax_query,
			'post__not_in'   => array_map(fn($p) => $p->ID, $posts)
		]);

		if ($ortho_query->have_posts()) {
			$posts[] = $ortho_query->posts[0];
		} else {
			$office_manager_found = false;
			if (taxonomy_exists('specialty') && term_exists('office-manager', 'specialty')) {
				$om_query = new WP_Query([
					'post_type'      => $post_type,
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'orderby'        => 'rand',
					'meta_query'     => $meta_query,
					'tax_query'      => [[
						'taxonomy' => 'specialty',
						'field'    => 'slug',
						'terms'    => 'office-manager',
						'operator' => 'IN'
					]],
					'post__not_in'   => array_map(fn($p) => $p->ID, $posts)
				]);
				if ($om_query->have_posts()) {
					$posts[] = $om_query->posts[0];
					$office_manager_found = true;
				}
			}

			if (!$office_manager_found && count($posts) < $max_cards) {
				$additional_query = new WP_Query([
					'post_type'      => $post_type,
					'post_status'    => 'publish',
					'posts_per_page' => $max_cards - count($posts),
					'orderby'        => 'rand',
					'meta_query'     => $meta_query,
					'tax_query'      => $tax_query,
					'post__not_in'   => array_map(fn($p) => $p->ID, $posts)
				]);
				foreach ($additional_query->posts as $additional_post) {
					$posts[] = $additional_post;
				}
			}
		}

		// Sort the merged array by the chosen order
		if ($sort_order === 'newest') {
			usort($posts, fn($a, $b) => strtotime($b->post_date) - strtotime($a->post_date));
		} elseif ($sort_order === 'oldest') {
			usort($posts, fn($a, $b) => strtotime($a->post_date) - strtotime($b->post_date));
		} elseif ($sort_order === 'alphabetical') {
			usort($posts, fn($a, $b) => strcmp($a->post_title, $b->post_title));
		}

		if ($featured_id) {
			$posts = array_values(array_filter($posts, fn($p) => $p->ID !== $featured_id));
			$featured_post = get_post($featured_id);
			if ($featured_post && $featured_post->post_status === 'publish') {
				array_unshift($posts, $featured_post);
			}
			$posts = array_slice($posts, 0, $max_cards);
		}
	}

	$total_posts = count($posts);
	$use_slider  = ($related_limit === 'all' && $total_posts >= 4);

	// Renders a single team card; expects setup_postdata() to have been called
	$render_card = function ($post) use ($current_location, $post_type, $image_fit) {
		setup_postdata($post);
		$post_id = $post->ID;

		$post_card_image = null;
		$featured_image  = get_post_thumbnail_id($post_id);

		if ($post_type === 'team') {
			$post_card_image_field = get_field('post_card_image', $post_id);
			if ($post_card_image_field && is_array($post_card_image_field) && !empty($post_card_image_field['ID'])) {
				$post_card_image = $post_card_image_field;
				$featured_image  = $post_card_image['ID'];
			}
		}

		$excerpt       = get_the_excerpt($post_id);
		$permalink     = get_permalink($post_id);
		$excerpt_short = wp_trim_words($excerpt, 30, '...');
		$excerpt_full  = wp_trim_words($excerpt, 50, '...');

		$specialty_terms = get_the_terms($post_id, 'specialty');
		$specialty_name  = '';
		if (!is_wp_error($specialty_terms) && !empty($specialty_terms)) {
			$specialty_name = $specialty_terms[0]->name;
		}

		$location_display = '';
		$location_field   = get_field('location', $post_id);
		if ($location_field) {
			if (is_array($location_field) && !empty($location_field)) {
				if ($current_location) {
					foreach ($location_field as $location) {
						$loc_id = is_object($location) ? $location->ID : $location;
						if ($loc_id == $current_location) {
							$location_display = get_the_title($loc_id);
							break;
						}
					}
				} else {
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
				$location_display = get_the_title($location_field->ID);
			} elseif (is_numeric($location_field)) {
				$location_display = get_the_title($location_field);
			}
		} ?>
		<article class="team-card">
			<div class="team-card-inner">
				<div class="team-card-image">
					<?php if ($featured_image) : ?>
						<?php if ($post_card_image && is_array($post_card_image)) : ?>
							<?php echo wp_get_attachment_image($post_card_image['ID'], 'medium_large', false, [
								'class'   => 'team-card-img',
								'loading' => 'lazy',
								'alt'     => $post_card_image['alt'] ?: get_the_title($post_id),
								'style'   => 'object-fit: ' . esc_attr($image_fit) . ';'
							]); ?>
						<?php else : ?>
							<?php echo wp_get_attachment_image($featured_image, 'medium_large', false, [
								'class'   => 'team-card-img',
								'loading' => 'lazy',
								'style'   => 'object-fit: ' . esc_attr($image_fit) . ';'
							]); ?>
						<?php endif; ?>
					<?php else : ?>
					<?php echo wp_get_attachment_image(get_field('default_card_img', 'options'), 'full', false, ['class' => 'p-5 object-fit-contain']);
					endif; ?>
				</div>

				<!-- Content Section -->
				<div class="team-card-content">
					<h3 class="team-card-title">
						<?php echo get_the_title($post_id); ?>
					</h3>
					<div class="team-card-meta">
						<?php if ($specialty_name) : ?>
							<div class="team-category"><?php echo esc_html($specialty_name); ?></div>
						<?php endif; ?>
						<?php if ($location_display) : ?>
							<div class="team-location"><?php echo esc_html($location_display); ?></div>
						<?php endif; ?>
					</div>
				</div>

				<!-- Hover Content -->
				<div class="team-card-hover-content">
					<h3 class="team-card-title">
						<?php echo get_the_title($post_id); ?>
					</h3>
					<div class="team-card-meta">
						<?php if ($specialty_name) : ?>
							<div class="team-category"><?php echo esc_html($specialty_name); ?></div>
						<?php endif; ?>
						<?php if ($location_display) : ?>
							<div class="team-location"><?php echo esc_html($location_display); ?></div>
						<?php endif; ?>
					</div>
					<?php if ($excerpt) : ?>
						<p class="team-card-excerpt d-none d-md-block"><?php echo $excerpt_full; ?></p>
						<p class="team-card-excerpt d-md-none"><?php echo $excerpt_short; ?></p>
					<?php endif; ?>
					<a href="<?php echo esc_url($permalink); ?>" class="btn btn-link-secondary">
						View More
					</a>
				</div>
			</div>
		</article>
		<?php
	};

	ob_start(); ?>

	<div class="team-cards-wrapper">
		<?php if (!empty($posts)) : ?>
			<?php if ($use_slider) : ?>

				<div class="team-slider-container">
					<div class="team-slider swiper">
						<div class="swiper-wrapper">
							<?php foreach ($posts as $post) : ?>
								<div class="swiper-slide">
									<?php $render_card($post); ?>
								</div>
							<?php endforeach; wp_reset_postdata(); ?>
						</div>
					</div>
					<div class="team-navigation-wrapper">
						<button class="team-prev" aria-label="Previous slide">
							<svg xmlns="http://www.w3.org/2000/svg" width="12" height="11" viewBox="0 0 12 11" fill="none">
								<path d="M11.0005 5.12473L1.00049 5.12473M1.00049 5.12473L5.2862 9.12473M1.00049 5.12473L5.2862 1.12473" stroke="#1A9ED9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
							</svg>
						</button>
						<button class="team-next" aria-label="Next slide">
							<svg xmlns="http://www.w3.org/2000/svg" width="12" height="11" viewBox="0 0 12 11" fill="none">
								<path d="M1.00049 5.12473H11.0005M11.0005 5.12473L6.71477 1.12473M11.0005 5.12473L6.71477 9.12473" stroke="#1A9ED9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
							</svg>
						</button>
					</div>
				</div>
				<div class="swiper-pagination team-pagination"></div>

			<?php else : ?>

				<div class="team-cards-flex d-flex flex-wrap justify-content-center gap-4" data-current-page="1" data-total-posts="<?php echo $total_posts; ?>">
					<?php foreach ($posts as $post) :
						$render_card($post);
					endforeach; wp_reset_postdata(); ?>
				</div>

			<?php endif; ?>
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
