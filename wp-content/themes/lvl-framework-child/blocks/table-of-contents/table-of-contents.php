<?php if (! defined('ABSPATH')) exit;

$render = function ($block, $is_preview, $content) {

	$the_block = new Level\Block($block);

	ob_start(); ?>

	<div class="table-of-contents">
		<h5>Table of Contents</h5>
		<?php if ($is_preview): ?>
			<div class="editor-preview-message">
				<p>H2s in the parent columns block will be populated as links below on the front-end.</p>
			</div>
		<?php endif; ?>
		<ul></ul>
	</div>

<?php

	$output = ob_get_clean();

	echo $the_block->renderSection($output, 'basic');
};

$render($block, $is_preview, $content);
