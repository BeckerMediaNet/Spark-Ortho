<?php if (! defined('ABSPATH')) exit;

$render = function ($block, $is_preview, $content) {

	$the_block = new Level\Block($block);

	$logos = $the_block->getField('logos');
	$layout = $the_block->getField('layout');
	$cols = $the_block->getField('columns') ?? 6;
	$desktop_slides = $the_block->getField('desktop_slides_per_view') ?? 6;
	$autoplay = $the_block->getField('autoplay') ?? true;
	$autoplay_delay = $the_block->getField('autoplay_delay') ?? 0;
	$show_navigation = $the_block->getField('show_navigation') ?? false;

	$the_block->addAttribute(['data-layout' => $layout], 0);

	if ($layout == 'slider') {
		$the_block->addAttribute(['data-desktop-slides' => $desktop_slides], 0);
		$the_block->addAttribute(['data-autoplay' => $autoplay ? 'true' : 'false'], 0);
		$the_block->addAttribute(['data-autoplay-delay' => $autoplay_delay], 0);
		$the_block->addAttribute(['data-show-navigation' => $show_navigation ? 'true' : 'false'], 0);
	}

	if ($layout == 'grid') {
		$the_block->addStyle('--grid-cols:' . $cols);
	}

	ob_start(); ?>

	<div class="logos">

		<?php foreach ($logos as $logo) :
			$image = $logo['image'];
			$link = $logo['link'];
		?>

			<?php if (!empty($link['url'])) : ?>

				<a href="<?php echo esc_url($link['url']); ?>" class="logo"
					<?php echo !empty($link['target']) ? 'target="' . esc_attr($link['target']) . '"' : ''; ?>
					<?php echo !empty($link['title']) ? 'aria-label="' . esc_attr($link['title']) . '"' : ''; ?>>
					<?php echo wp_get_attachment_image($image, 'medium', '', ['class' => 'img-fluid']); ?>
				</a>
			<?php else : ?>
				<div class="logo">
					<?php echo wp_get_attachment_image($image, 'medium', '', ['class' => 'img-fluid']); ?>
				</div>
			<?php endif; ?>

		<?php endforeach; ?>

	</div>

	<?php if ($layout == 'slider' && $show_navigation) : ?>
		<div class="logos-navigation mt-4 d-flex justify-content-center align-items-center gap-3">
			<div class="navigation-buttons-wrapper">
				<button class="logos-prev" aria-label="Previous">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
				<span class="drag-text">Swipe</span>
				<button class="logos-next" aria-label="Next">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
			</div>
		</div>
	<?php endif; ?>

<?php

	$output = ob_get_clean();

	echo $the_block->renderSection($output, 'basic');
};

$render($block, $is_preview, $content);
