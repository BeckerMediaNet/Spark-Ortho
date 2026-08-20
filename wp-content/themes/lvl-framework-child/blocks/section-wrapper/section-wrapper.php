<?php if (!defined('ABSPATH')) exit;

$render = function ( $block, $is_preview, $content ) {

	$the_block = new Level\Block( $block );

	$inner = '<InnerBlocks templateLock="false" />';

	ob_start(); ?>

    <div class="inner-wrapper is-layout-flow">

		<?php echo $inner; ?>

    </div>

	<?php

	$output = ob_get_clean();

	echo $the_block->renderSection( $output );

};
$render( $block, $is_preview, $content );