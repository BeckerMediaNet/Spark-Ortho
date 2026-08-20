<?php if (!defined('ABSPATH')) exit;

$render = function ($block, $is_preview, $content) {

	$the_block = new Level\Block($block);
	$links = $the_block->getField('additional_links');

	$innerBlocks = [
		[
			'lvl/nav-tab-panel',
		]
	];

	$allowedBlocks = ['lvl/nav-tab-panel'];

	$inner = '<InnerBlocks template="' . esc_attr(wp_json_encode($innerBlocks)) . '" templateLock="false" allowedBlocks="' . esc_attr(wp_json_encode($allowedBlocks)) . '" />';

	ob_start(); ?>

	<div class="row">
		<div class="nav-tabs-wrapper col-12 col-md-3">
			<ul class="nav nav-tabs"></ul>

			<?php if (!empty($links)) : ?>
				<?php foreach ($links as $link) : ?>
					<?php
					// Ensure $link is an array and has the expected structure
					if (is_array($link) && isset($link['link'])) :
						$link_data = $link['link'];
						$url = is_array($link_data) ? ($link_data['url'] ?? '#') : $link_data;
						$title = is_array($link_data) ? ($link_data['title'] ?? 'Link') : $link_data;
						$is_button = isset($link['button']) ? $link['button'] : false;
					?>
						<a href="<?php echo esc_url($url); ?>" class="<?php echo $is_button ? 'btn btn-secondary' : ''; ?>"><?php echo esc_html($title); ?></a>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<div class="tab-content-wrapper col-12 col-md-9">
			<div class="tab-content">
				<?php echo $inner; ?>
			</div>
		</div>
	</div>




<?php

	$output = ob_get_clean();

	echo $the_block->renderSection($output, 'basic');
};

$render($block, $is_preview, $content);
