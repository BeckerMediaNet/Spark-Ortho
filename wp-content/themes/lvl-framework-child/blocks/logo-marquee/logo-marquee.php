<?php if (! defined('ABSPATH')) exit;

$render = function ($block, $is_preview, $content) {

	$the_block = new Level\Block($block);

	$logos = $the_block->getField('logos');
	$speed = $the_block->getField('speed') ?? 30;

	if (empty($logos)) {
		return;
	}

	$the_block->addStyle('--marquee-speed:' . intval($speed) . 's');

	ob_start(); ?>

	<div class="logo-marquee">
		<div class="logo-marquee-track">

			<?php foreach ($logos as $logo) :
				$image = $logo['image'];
				$link = $logo['link'];
			?>

				<?php if (!empty($link['url'])) : ?>
					<a href="<?php echo esc_url($link['url']); ?>" class="logo-marquee-item"
						<?php echo !empty($link['target']) ? 'target="' . esc_attr($link['target']) . '"' : ''; ?>
						<?php echo !empty($link['title']) ? 'aria-label="' . esc_attr($link['title']) . '"' : ''; ?>>
						<?php echo wp_get_attachment_image($image, 'medium', '', ['class' => 'img-fluid']); ?>
					</a>
				<?php else : ?>
					<div class="logo-marquee-item">
						<?php echo wp_get_attachment_image($image, 'medium', '', ['class' => 'img-fluid']); ?>
					</div>
				<?php endif; ?>

			<?php endforeach; ?>

			<?php // Duplicate set, immediately after the first, so the -50% loop point lines up seamlessly ?>
			<?php foreach ($logos as $logo) :
				$image = $logo['image'];
				$link = $logo['link'];
			?>

				<?php if (!empty($link['url'])) : ?>
					<a href="<?php echo esc_url($link['url']); ?>" class="logo-marquee-item" aria-hidden="true" tabindex="-1"
						<?php echo !empty($link['target']) ? 'target="' . esc_attr($link['target']) . '"' : ''; ?>>
						<?php echo wp_get_attachment_image($image, 'medium', '', ['class' => 'img-fluid']); ?>
					</a>
				<?php else : ?>
					<div class="logo-marquee-item" aria-hidden="true">
						<?php echo wp_get_attachment_image($image, 'medium', '', ['class' => 'img-fluid']); ?>
					</div>
				<?php endif; ?>

			<?php endforeach; ?>

		</div>
	</div>

<?php

	$output = ob_get_clean();

	echo $the_block->renderSection($output, 'basic');
};

$render($block, $is_preview, $content);
