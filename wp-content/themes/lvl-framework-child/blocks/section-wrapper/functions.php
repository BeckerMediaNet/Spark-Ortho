<?php if (!defined('ABSPATH')) exit;


/**
 * Add gradient and background classes to section-wrapper block on frontend
 */
add_filter('render_block', 'lvl_add_gradient_classes_to_section_wrapper', 10, 2);
function lvl_add_gradient_classes_to_section_wrapper($block_content, $block)
{
	// Only process section-wrapper blocks
	if ($block['blockName'] !== 'lvl/section-wrapper') {
		return $block_content;
	}

	// Skip if we're in the admin/editor
	if (is_admin()) {
		return $block_content;
	}

	$additional_classes = [];

	// Check for gradient background
	if (isset($block['attrs']['gradient'])) {
		$additional_classes[] = 'has-' . $block['attrs']['gradient'] . '-gradient-background';
		$additional_classes[] = 'has-background';
	}

	// Check for solid background color
	if (isset($block['attrs']['backgroundColor'])) {
		$additional_classes[] = 'has-' . $block['attrs']['backgroundColor'] . '-background-color';
		$additional_classes[] = 'has-background';
	}

	// Check for custom background color
	$custom_bg_color = null;
	if (isset($block['attrs']['style']['color']['background'])) {
		$additional_classes[] = 'has-background';
		$custom_bg_color = $block['attrs']['style']['color']['background'];
	}

	// If we have classes to add, parse the HTML and add them
	if (!empty($additional_classes)) {
		// Use a simpler regex approach to add classes to the section element
		$classes_to_add = implode(' ', $additional_classes);

		// Look for the section tag with existing classes and add our classes
		$pattern = '/(<section[^>]*class=")([^"]*)(")([^>]*>)/';

		if (preg_match($pattern, $block_content)) {
			$block_content = preg_replace($pattern, '$1$2 ' . $classes_to_add . '$3$4', $block_content);
		} else {
			// If no class attribute exists, add one
			$pattern = '/(<section)([^>]*>)/';
			$block_content = preg_replace($pattern, '$1 class="' . $classes_to_add . '"$2', $block_content);
		}
	}

	// Add inline style for custom background color
	if ($custom_bg_color) {
		$safe_color = esc_attr($custom_bg_color);
		// Add or append to existing style attribute
		if (preg_match('/(<section[^>]*style=")([^"]*)(")/i', $block_content)) {
			$block_content = preg_replace('/(<section[^>]*style=")([^"]*)(")/i', '$1$2background-color:' . $safe_color . ';$3', $block_content);
		} else {
			$block_content = preg_replace('/(<section)(\s)/i', '$1 style="background-color:' . $safe_color . ';"$2', $block_content);
		}
	}

	return $block_content;
}
