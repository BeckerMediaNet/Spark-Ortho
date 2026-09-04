<?php if (! defined('ABSPATH')) exit;

$render = function ($block, $is_preview, $content) {

	$the_block = new Level\Block($block);

	$before_image    = $the_block->getField('before_image');
	$after_image     = $the_block->getField('after_image');
	$before_label    = $the_block->getField('before_label') ?: 'Before';
	$after_label     = $the_block->getField('after_label') ?: 'After';
	$start_position  = $the_block->getField('start_position') ?: 50;

	if (! $before_image || ! $after_image) {
		if ($is_preview) {
			echo $the_block->previewNotice('info', 'Please set a <strong>Before</strong> and <strong>After</strong> image to display the transition slider.');
		}
		return;
	}

	ob_start(); ?>

	<div class="transition-slider-container"
		role="group"
		aria-label="<?php echo esc_attr(sprintf('%s and %s image comparison', $before_label, $after_label)); ?>">
		<div class="beer-slider"
			data-beer-label="<?php echo esc_attr($after_label); ?>"
			data-start="<?php echo esc_attr($start_position); ?>">
			<?php echo wp_get_attachment_image($after_image, 'large', '', [
				'class' => 'img-fluid transition-slider__after',
				'alt'   => esc_attr(sprintf('%s image', $after_label)),
			]); ?>
			<div class="beer-reveal" data-beer-label="<?php echo esc_attr($before_label); ?>" aria-hidden="true">
				<?php echo wp_get_attachment_image($before_image, 'large', '', [
					'class' => 'img-fluid transition-slider__before',
					'alt'   => esc_attr(sprintf('%s image', $before_label)),
				]); ?>
			</div>
		</div>
	</div>

	<?php

	$output = ob_get_clean();

	echo $the_block->renderSection($output, 'basic');
};

$render($block, $is_preview, $content);
