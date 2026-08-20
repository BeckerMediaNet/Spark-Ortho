<?php if (!defined('ABSPATH')) exit;

$render = function ($block, $is_preview, $content) {

	$the_block = new Level\Block($block, ['is_preview' => $is_preview]);

	$selection_method = $the_block->getField('selection_method') ?: 'automatic';
	$limit_faqs = $the_block->getField('limit_faqs');
	$number_of_faqs = $the_block->getField('number_of_faqs') ?: 5;
	$topics_filter = $the_block->getField('topics_filter') ?: [];
	$sort_order = $the_block->getField('sort_order') ?: 'date_desc';
	$manual_faqs = $the_block->getField('manual_faqs') ?: [];

	$the_block->addClass('faq-panel-container');

	$faqs = [];

	if ($selection_method === 'manual' && !empty($manual_faqs)) {
		// Manual selection - use selected FAQs
		$faqs = $manual_faqs;
	} else {
		// Automatic selection - determine orderby and order based on sort_order
		$orderby = 'date';
		$order = 'DESC';

		switch ($sort_order) {
			case 'date_asc':
				$orderby = 'date';
				$order = 'ASC';
				break;
			case 'title_asc':
				$orderby = 'title';
				$order = 'ASC';
				break;
			case 'title_desc':
				$orderby = 'title';
				$order = 'DESC';
				break;
			case 'date_desc':
			default:
				$orderby = 'date';
				$order = 'DESC';
				break;
		}

		$query_args = [
			'post_type' => 'faq',
			'post_status' => 'publish',
			'posts_per_page' => $limit_faqs ? $number_of_faqs : -1,
			'orderby' => $orderby,
			'order' => $order,
		];

		// Add taxonomy filter if topics are selected
		if (!empty($topics_filter)) {
			$query_args['tax_query'] = [
				[
					'taxonomy' => 'topics',
					'field'    => 'term_id',
					'terms'    => $topics_filter,
					'operator' => 'IN',
				],
			];
		}

		$faq_query = new WP_Query($query_args);
		$faqs = $faq_query->posts;
		wp_reset_postdata();
	}

	// Generate unique ID for this accordion group
	$accordion_id = 'faq-accordion-' . uniqid();

	ob_start(); ?>

	<?php if (!empty($faqs)) : ?>
		<div class="accordion faq-accordion" id="<?php echo esc_attr($accordion_id); ?>">
			<?php foreach ($faqs as $index => $faq) :
				setup_postdata($faq);
				$faq_id = $faq->ID;
				$question = get_the_title($faq_id);
				$answer = apply_filters('the_content', get_the_content(null, false, $faq_id));

				$collapse_id = 'collapse-' . $faq_id . '-' . $index;
				$heading_id = 'heading-' . $faq_id . '-' . $index;
			?>
				<div class="accordion-item faq-item">
					<h3 class="accordion-header" id="<?php echo esc_attr($heading_id); ?>">
						<button class="accordion-button collapsed" type="button"
							data-bs-toggle="collapse"
							data-bs-target="#<?php echo esc_attr($collapse_id); ?>"
							aria-expanded="false"
							aria-controls="<?php echo esc_attr($collapse_id); ?>">
							<?php echo esc_html($question); ?>
						</button>
					</h3>
					<div id="<?php echo esc_attr($collapse_id); ?>"
						class="accordion-collapse collapse"
						aria-labelledby="<?php echo esc_attr($heading_id); ?>"
						data-bs-parent="#<?php echo esc_attr($accordion_id); ?>">
						<div class="accordion-body">
							<?php echo $answer; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
			<?php wp_reset_postdata(); ?>
		</div>
	<?php else : ?>
		<div class="faq-empty">
			<p>No FAQs found. Please add FAQ posts or select FAQs manually.</p>
		</div>
	<?php endif; ?>

<?php

	$output = ob_get_clean();

	echo $the_block->renderSection($output, 'basic');
};

$render($block, $is_preview, $content);
