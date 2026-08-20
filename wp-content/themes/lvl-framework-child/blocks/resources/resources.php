<?php if (!defined('ABSPATH')) exit;

$render = function ($block, $is_preview, $content) {

	global $post;

	$the_block = new Level\Block($block);
	$show_filters = $the_block->getField('show_filters');
	$related = $the_block->getField('related_posts') ? 'true' : 'false';
	$taxonomies = $the_block->getField('taxonomy_filters') ?: [];
	$categories = $the_block->getField('categories') ?: [];
	$tags = $the_block->getField('tags') ?: [];
	$areas = $the_block->getField('business_areas') ?: [];
	$posts = $the_block->getField('posts') ?: [];

	$the_block->addAttribute(['data-limit' => ($the_block->getField('limit')) ?: 0]);

	if ($related && is_single() && get_post_type() === 'post') {
		$the_block->addAttribute(['data-related' => get_the_ID()]);
	}

	$the_block->addAttribute(['data-posts' => implode(',', $posts)]);
	$the_block->addAttribute(['data-tags' => implode(',', $tags)]);
	$the_block->addAttribute(['data-categories' => implode(',', $categories)]);
	$the_block->addAttribute(['data-areas' => implode(',', $areas)]);


	ob_start(); ?>

	<div class="resources">

		<div class="row pb-5<?php echo ($show_filters ? '' : ' d-none') ?>">
			<div class="col-12">
				<div class="filter-bar row align-items-end">

					<?php

					if ($taxonomies) {

						foreach ($taxonomies as $filter) {
							$taxonomy = get_taxonomy($filter);
							if (!$taxonomy) {
								continue;
							}

					?>
							<div class="filter col-12 col-md mt-4 mt-md-2">
								<h5 class="has-body-large-font-size">By <?php echo strtolower($taxonomy->labels->singular_name); ?></h5>
								<div class="dropdown">
									<button class="dropdown-toggle" type="button" id="<?php echo $filter; ?>_select" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false"><?php echo $taxonomy->label; ?></button>
									<ul id="type_filter" class="dropdown-menu" data-bs-offset="0,10" aria-labelledby="<?php echo $filter; ?>_select">
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
						<button class="btn btn-secondary filter-submit mt-3">Filter</button>
						<button class="btn btn-secondary-outline filter-reset mt-3 d-none">Reset</button>
					</div>
				</div>

				<div class="filtered" aria-live="polite">

				</div>
			</div>
		</div>

		<div class="results">

			<div class="resources-target" aria-live="polite"></div>

			<div class="spinner text-center py-5">
				<div class="spinner-border" role="status">
					<span class="visually-hidden">Loading...</span>
				</div>
			</div>

			<nav class="pagination d-none"></nav>

		</div>
	</div>

<?php $output = ob_get_clean();

	echo $the_block->renderSection($output, 'basic');
};

$render($block, $is_preview, $content);
