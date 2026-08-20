<?php if (! defined('ABSPATH')) exit;

// Get Resources
add_action('wp_ajax_resources_get', 'resources_get');
add_action('wp_ajax_nopriv_resources_get', 'resources_get');
function resources_get()
{

	$page = ($_POST['page']) ?: 0;
	$related = ($_POST['related']);
	$filters = $_POST['filters'];

	// Debug: Log what we're receiving
	error_log('Resources AJAX - Related value: ' . var_export($related, true));
	error_log('Resources AJAX - All POST data: ' . print_r($_POST, true));

	// Log query args after they're built (we'll add this after the query is built)
	$filters = json_decode(stripslashes($filters));
	$limit = ($_POST['limit']);
	$posts = ($_POST['posts']);
	$categories = ($_POST['categories']);
	$tags = ($_POST['tags']);
	$areas = ($_POST['areas']);

	$args = [
		'post_type' => 'post',
		'post_status' => 'publish',
		'posts_per_page' => ($limit) ?: 12,
		'offset' => ($page - 1) * 12,
		'tax_query' => [
			'relation' => 'AND'
		]
	];

	$post_taxonomies = get_object_taxonomies('post', 'objects');

	$filtered_taxes = array_filter($filters, function ($object) use ($post_taxonomies) {
		return in_array($object->type, array_keys($post_taxonomies));
	});

	if ($filtered_taxes) {
		$args['tax_query'] = [
			'relation' => 'AND',
		];

		foreach ($filtered_taxes as $filter_tax) {
			$args['tax_query'][] = [
				'taxonomy' => $filter_tax->type,
				'terms'    => $filter_tax->value,
			];
		}
	}

	if ($related !== 'false') {

		$args['post__not_in'] = [$related];

		$rel_cats = wp_list_pluck(get_the_terms($related, 'category'), 'term_id');
		$rel_tags = wp_list_pluck(get_the_terms($related, 'post_tag'), 'term_id');

		if ($rel_cats || $rel_tags) {
			$args['tax_query']['relation'] = 'OR';

			if ($rel_cats) {
				$args['tax_query'][] = [
					'taxonomy' => 'category',
					'terms'    => $rel_cats,
					'operator' => 'IN',
				];
			}

			if ($rel_tags) {
				$args['tax_query'][] = [
					'taxonomy' => 'post_tag',
					'terms'    => $rel_tags,
					'operator' => 'IN',
				];
			}
		}
	}

	$keyword = array_filter($filters, function ($object) {
		return  $object->type === 'keyword';
	});

	$keyword = array_map(function ($object) {
		return $object->value;
	}, $keyword);

	if ($categories) {
		$args['tax_query'][] = [
			'taxonomy'  => 'category',
			'terms'		=> $categories
		];
	}

	// Pre Filters
	if ($posts) {
		$args['post__in'] = explode(',', $posts);
	}

	if ($tags) {
		$args['tax_query'][] = [
			'taxonomy'  => 'post_tag',
			'terms'		=> $tags
		];
	}

	if ($areas) {
		$args['tax_query'][] = [
			'taxonomy'  => 'business_area',
			'terms'		=> $areas
		];
	}

	if ($keyword) {
		$args['s'] = implode(',', $keyword);
	}

	$resources = new WP_Query($args);

	ob_start(); ?>

	<?php if ($resources->have_posts()) : ?>

		<?php foreach ($resources->posts as $post) :
			$cat = get_the_terms($post, 'category');
			if (!empty($post->post_excerpt)) {
				$excerpt = $post->post_excerpt;
			} else {
				$excerpt = '';
				if (preg_match('/<\/h1>\s*<p[^>]*>(.*?)<\/p>/si', $post->post_content, $matches)) {
					$excerpt = wp_strip_all_tags($matches[1], true);
				} else {
					$excerpt = wp_strip_all_tags($post->post_content, true);
				}
				$excerpt = wp_trim_words($excerpt, 25, '...');
			}
		?>

			<div id="resource-<?php echo $post->ID; ?>" class="resource" data-bs-theme="light">
				<div class="card">

					<figure class="wp-block-image mb-0">
						<?php if (get_field('feat_override', $post)) {
							echo wp_get_attachment_image(get_field('feat_override', $post), 'large', false, ['class' => 'img-fluid']);
						} else if (get_the_post_thumbnail($post)) {
							echo get_the_post_thumbnail($post, 'large', ['class' => 'img-fluid']);
						} else {
							echo wp_get_attachment_image(get_field('default_card_img', 'options'), 'full', false, ['class' => 'default-post-image p-3']);
						} ?>
					</figure>

					<div class="d-flex flex-column is-layout-flow h-100 p-4">
						<h5 id="<?php echo 'title-' . $post->ID; ?>" class="mt-2"><?php echo $post->post_title; ?></h5>
						<div class="excerpt mb-4">
							<?php echo apply_filters('the_content', $excerpt); ?>
						</div>


						<div class="location-link-wrapper d-flex justify-content-end pt-4 mt-auto w-100">
							<a href="<?php echo esc_url(get_permalink($post->ID)); ?>" class="btn btn-link-secondary">Read More</a>
						</div>
					</div>
				</div>
			</div>

		<?php endforeach; ?>



	<?php else : ?>

		<div class="no-results text-center py-5">
			<p>No resources were found, please adjust filters and try again.</p>
			<button class="btn btn-link filter-reset">clear filters</button>
		</div>

	<?php endif; ?>

<?php

	$response = ob_get_clean();

	echo $response;

	header('pagination:{"totalPages":' . $resources->max_num_pages . ', "currentPage":' . $page . "}");
	header('loadmore:' . ($resources->found_posts > $page * 12));

	die();
}

add_filter('acf/load_field/name=taxonomy_filters', function ($field) {

	$post_taxonomies = get_object_taxonomies('post', 'objects');

	foreach ($post_taxonomies as $tax) {
		$field['choices'][$tax->name] = $tax->labels->singular_name;
	}

	return $field;
});
