<?php if( ! defined('ABSPATH') ) exit;

// Get Resources
add_action('wp_ajax_resources_get', 'resources_get');
add_action('wp_ajax_nopriv_resources_get', 'resources_get');
function resources_get() {

	$page = ($_POST['page'])? : 1;

	$filters = $_POST['filters'];
	$filters = json_decode( stripslashes($filters) );

	// Categories
	$categories = array_filter($filters, function ($object) {
		return  $object->type === 'category';
	});

	$categories = array_map(function ($object) {
		return $object->value;
	}, $categories);

	// Tags
	$types = array_filter($filters, function ($object) {
		return  $object->type === 'resource_type';
	});

	$types = array_map(function ($object) {
		return $object->value;
	}, $types);

	// Keywords
	$keyword = array_filter($filters, function ($object) {
		return  $object->type === 'keyword';
	});

	$keyword = array_map(function ($object) {
		return $object->value;
	}, $keyword);

	$args = [
		'post_type' => ['post','press-release','event'],
		'post_status' => 'publish',
		'posts_per_page' => 9,
		'offset' => ($page - 1) * 9,
		'tax_query' => [
			'relation' => 'AND'
		]
	];

	if ( $categories ) {
		$args['tax_query'][] = [
			'taxonomy'  => 'category',
			'terms'		=> $categories
		];
	}

	if ( $types ) {
		$args['tax_query'][] = [
			'taxonomy'  => 'resource_type',
			'terms'		=> $types
		];
	}

	if ( $keyword ) {
		$args['s'] = implode(',',$keyword);
	}

	$resources = new WP_Query($args);

	
	ob_start(); ?>
		<?php if ( $resources->have_posts()) : ?>

			<?php foreach ( $resources->posts as $post ) : 
					$cat = get_the_terms($post, 'category');
					$type = get_the_terms($post, 'resource_type');
					$excerpt = wp_strip_all_tags($post->post_content, true);
					$excerpt = wp_trim_words($excerpt, 25, '...');
				?>
				<article id="resource-<?php echo $post->ID; ?>" class="resource g-col-12">

                    <?php
                    $default_img = get_field('default_card_img', 'options');

                    if (wp_get_attachment_image(get_post_thumbnail_id($post))) {
                        ?>
                        <figure class="card-image-banner mb-0" style="overflow:hidden;width: 100%; height:150px;">
                            <?php echo wp_get_attachment_image(get_post_thumbnail_id($post), 'medium_large', false, ["class" => "object-fit--cover"]); ?>
                        </figure>
                        <?php
                    } elseif ($default_img) {
                        ?>
                        <figure class="card-image-banner mb-0" style="overflow:hidden;width: 100%; height:150px;">
                            <?php echo wp_get_attachment_image($default_img, 'medium_large', false, ["class" => "object-fit--cover"]); ?>
                        </figure>
                        <?php
                    }
                    ?>

					<div class="wrapper">
						<p class="leader small d-flex align-items-center mb-3"><?php echo $cat[0]->name; ?></p>
						<h3 class="resource-title h4">
							<?php echo $post->post_title; ?>
						</h3>
						<?php // echo apply_filters('the_content', (get_post_meta($post->ID, '_yoast_wpseo_metadesc', true))? : $excerpt); ?>
						<span class="resource_type"><?php echo $type[0]->name; ?></span>
					</div>

					<a href="<?php echo get_permalink($post); ?>" class="btn btn-link stretched-link mx-3">Read</a>
          
				</article>
			<?php endforeach; ?>

		<?php else : ?>

			<div class="text-center py-5">
				<p>no resources were found, please adjust filters and try again</p>
				<button class="btn btn-link filter-reset">clear filters</button>
			</div>

		<?php endif; ?>

	<?php 
	
	$response = ob_get_clean();

	echo $response;

	header('loadmore:'. ($resources->found_posts > $page * 9));

	die();

}