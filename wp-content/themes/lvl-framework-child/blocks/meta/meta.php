<?php if (! defined('ABSPATH')) exit;

$render = function ($block, $is_preview, $content) {

	$the_block = new Level\Block($block);

	$show_author = get_field('show_author_meta');

	if ($show_author) {
		$author = get_the_author();
	} else {
		$author = false;
	}

	$avatar = false;

	$date = get_the_date('F j, Y');
	$tags = get_the_tags();
	$cats = get_the_category();
	$reading_time = str_word_count(strip_tags(get_the_content())) / 200;

	$avatar_url = get_avatar_url(get_the_author_meta('ID'), ['size' => 128]);

	if (!strpos($avatar_url, 'd=mm') === true) {
		$avatar = get_avatar(get_the_author_meta('ID'), 128, '', $author, ['class' => 'img-fluid']);
	}

	ob_start(); ?>

	<div class="meta-row small">

		<?php if ($author && $avatar) : ?>
			<div class="avatar-wrapper">
				<?php echo $avatar; ?>
			</div>
		<?php endif; ?>

		<div>

			<?php if ($author) : ?>
				<div>
					<strong>Author:</strong> <?php echo $author; ?>
				</div>
			<?php endif; ?>

			<?php if ($date) : ?>
				<div>
					<strong>Date:</strong> <?php echo $date; ?>
				</div>
			<?php endif; ?>

			<?php if (!empty($tags)) : ?>
				<div>
					<strong>Tags:</strong> <?php echo strToTitleCase(implode(', ', wp_list_pluck($tags, 'name'))); ?>
				</div>
			<?php endif; ?>

			<?php if (!empty($cats)) :
				$catLinks = [];
				foreach ($cats as $cat) {
					$catLinks[] = '<a href="/blog/?category=' . $cat->term_id . '">' . strToTitleCase($cat->name) . '</a>';
				} ?>

				<div>
					<strong>Categories:</strong> <?php echo implode(', ', $catLinks); ?>
				</div>
			<?php endif; ?>

			<div class="reading-time">
				<strong>Reading Time:</strong> <?php echo ceil($reading_time) . ' ' . _n('min', 'min', $reading_time) ?>
			</div>

		</div>
	</div>

<?php

	$output = ob_get_clean();

	echo $the_block->renderSection($output, 'basic');
};

$render($block, $is_preview, $content);
