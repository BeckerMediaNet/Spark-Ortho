<?php if( ! defined('ABSPATH') ) exit;

// Get events
add_action('wp_ajax_events_get', 'events_get');
add_action('wp_ajax_nopriv_events_get', 'events_get');
function events_get() {

	$page = ($_POST['page'])? : 1;

	$filters = $_POST['filters'];
	$filters = json_decode( stripslashes($filters) );

	// Types
	$types = array_filter($filters, function ($object) {
		return  $object->type === 'event-type';
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

	$current_datetime = new DateTime('now', new DateTimeZone('America/New_York'));
	$current_datetime = $current_datetime->format('Y-m-d H:i:s');

	$args = [
		'post_type' => 'events',
		'post_status' => 'publish',
		'posts_per_page' => 9,
		'offset' => ($page - 1) * 9,
		'orderby' => 'meta_value',
		'meta_key' => 'event_date',
		'order' => 'ASC',
		'meta_query' => [
			[
				'key' => 'event_date',
				'value'   => $current_datetime,
            	'compare' => '>=',
            	'type'    => 'DATETIME',
			]
		],
		'tax_query' => [
			'relation' => 'AND'
		]
	];

	if ( $types ) {
		$args['tax_query'][] = [
			'taxonomy'  => 'event-type',
			'terms'		=> $types
		];
	}

	if ( $keyword ) {
		$args['s'] = implode(',',$keyword);
	}

	$events = new WP_Query($args);

	
	ob_start(); ?>
		<?php if ( $events->have_posts()) : ?>

			<?php foreach ( $events->posts as $post ) : 
					$type = get_the_terms($post, 'event-type');
				?>
				<div id="event-<?php echo $post->ID; ?>" class="resource col-12 col-md-6 col-lg-4">
					<p class="leader d-flex align-items-center mb-3"><?php echo ($type[0]->name)? : 'Event'; ?><svg class="ms-2" width="82" height="2" viewBox="0 0 82 2" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1H56" stroke="#23004B" stroke-width="2" stroke-linecap="round"/><path d="M63 1H70" stroke="#23004B" stroke-width="2" stroke-linecap="round"/><path d="M75 1H81" stroke="#23004B" stroke-width="2" stroke-linecap="round"/></svg></p>

					<div class="wrapper">
						<?php echo (get_the_post_thumbnail($post))? get_the_post_thumbnail($post, 'medium', ['class' => 'mb-3']) : '<img src="'. get_template_directory_uri().'/dist/img/event-placeholder.webp" class="mb-3" />' ?>
						<div class="d-flex align-items-center mb-3">
							<svg class="mt-n1 me-2" width="18" height="18" viewBox="0 0 83 91" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M20.75 0C22.3804 0 23.7143 1.27969 23.7143 2.84375V11.375H59.2857V2.84375C59.2857 1.27969 60.6196 0 62.25 0C63.8804 0 65.2143 1.27969 65.2143 2.84375V11.375H71.1429C77.6828 11.375 83 16.476 83 22.75V28.4375V34.125V79.625C83 85.899 77.6828 91 71.1429 91H11.8571C5.31719 91 0 85.899 0 79.625V34.125V28.4375V22.75C0 16.476 5.31719 11.375 11.8571 11.375H17.7857V2.84375C17.7857 1.27969 19.1196 0 20.75 0ZM77.0714 34.125H5.92857V79.625C5.92857 82.7709 8.5779 85.3125 11.8571 85.3125H71.1429C74.4221 85.3125 77.0714 82.7709 77.0714 79.625V34.125ZM71.1429 17.0625H11.8571C8.5779 17.0625 5.92857 19.6041 5.92857 22.75V28.4375H77.0714V22.75C77.0714 19.6041 74.4221 17.0625 71.1429 17.0625Z" fill="#23004B"/>
							</svg>
							<span><?php echo date_format(date_create(get_field('event_date', $post->ID)), "M j, y @ g:i A"); ?></span>
						</div>
						<h3 id="event-<?php echo $post->ID; ?>-title"><?php echo $post->post_title; ?></h3>
						<?php echo (get_field('event_description', $post->ID))? apply_filters('the_content', get_field('event_description', $post->ID)) : '';?>
						<?php echo (get_field('event_link', $post->ID))? '<a href="'.get_field('event_link', $post->ID).'" class="mt-auto" aria-labelledby=event-'. $post->ID .'-title">Learn more</a>' : '';?>
					</div>
				</div>
			<?php endforeach; ?>

		<?php else : ?>

			<div class="text-center py-5">
				<p>no events were found, please adjust filters and try again</p>
				<button class="btn btn-link filter-reset">clear filters</button>
			</div>

		<?php endif; ?>

	<?php 
	
	$response = ob_get_clean();

	echo $response;

	header('loadmore:'. ($events->found_posts > $page * 9));

	die();

}