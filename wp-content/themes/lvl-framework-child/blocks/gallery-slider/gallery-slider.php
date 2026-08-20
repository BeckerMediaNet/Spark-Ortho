<?php if (! defined('ABSPATH')) exit;

$render = function ($block, $is_preview, $content) {

	$the_block = new Level\Block($block);

	$logos = $the_block->getField('images');
	$autoplay = $the_block->getField('autoplay') ?? true;
	$autoplay_delay = $the_block->getField('autoplay_delay') ?? 0;
	$show_navigation = $the_block->getField('show_navigation') ?? false;
	$show_pagination = $the_block->getField('show_pagination') ?? true;

	$the_block->addAttribute(['data-autoplay' => $autoplay ? 'true' : 'false'], 0);
	$the_block->addAttribute(['data-autoplay-delay' => $autoplay_delay], 0);
	$the_block->addAttribute(['data-show-navigation' => $show_navigation ? 'true' : 'false'], 0);
	$the_block->addAttribute(['data-show-pagination' => $show_pagination ? 'true' : 'false'], 0);


	ob_start(); ?>

	<div class="gallery-slider-container">
		<div class="gallery-slider swiper">
			<div class="swiper-wrapper">
				<?php foreach ($logos as $logo) : ?>
					<div class="swiper-slide">
						<div class="image">
							<?php echo wp_get_attachment_image($logo, 'large', '', ['class' => 'img-fluid']); ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<?php if ($show_navigation) : ?>
			<div class="gallery-navigation-wrapper">
				<button class="gallery-prev" aria-label="Previous slide">
					<svg xmlns="http://www.w3.org/2000/svg" width="12" height="11" viewBox="0 0 12 11" fill="none">
						<path d="M11.0005 5.12473L1.00049 5.12473M1.00049 5.12473L5.2862 9.12473M1.00049 5.12473L5.2862 1.12473" stroke="#1A9ED9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
				<button class="gallery-next" aria-label="Next slide">
					<svg xmlns="http://www.w3.org/2000/svg" width="12" height="11" viewBox="0 0 12 11" fill="none">
						<path d="M1.00049 5.12473H11.0005M11.0005 5.12473L6.71477 1.12473M11.0005 5.12473L6.71477 9.12473" stroke="#1A9ED9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
			</div>
		<?php endif; ?>


	</div>

	<?php if ($show_pagination) : ?>
		<div class="swiper-pagination"></div>
	<?php endif; ?>

<?php

	$output = ob_get_clean();

	echo $the_block->renderSection($output, 'basic');
};

$render($block, $is_preview, $content);
