<?php if (! defined('ABSPATH')) {
    exit;
}

// if you want to include the parent theme's block styles
include_once get_template_directory() . '/lib/blocks/renders.php';


/**
 * Custom render for collapsible paragraphs
 * 
 * @param $block_content
 * @param $block
 *
 * @return false|mixed|string
 */
add_filter('render_block', 'lvl_render_block_filter__paragraphs', 10, 2);
function lvl_render_block_filter__paragraphs($block_content, $block): mixed
{

    if ('core/paragraph' === $block['blockName']) {

        if (isset($block['attrs']['className']) && str_contains($block['attrs']['className'], 'collapse-mobile')) {

            $uid = uniqid('collapse-mobile-');

            $block_content = str_replace('<p class="collapse-mobile">', '<p id="' . $uid . '" class="collapse-mobile collapse">', $block_content);
            $block_content = str_replace('</p>', '</p><a href="#' . $uid . '" class="collapse-mobile-btn" data-bs-toggle="collapse" role="button" aria-expanded="false"></a>', $block_content);
        }
    }

    return $block_content;
}

/**
 * Add custom class to blocks with custom block gap
 * 
 * @param $block_content
 * @param $block
 *
 * @return false|mixed|string
 */
add_filter('render_block', 'lvl_add_custom_gap_class', 10, 2);
function lvl_add_custom_gap_class($block_content, $block): mixed
{

    if (!is_admin() && isset($block['attrs']['style']['spacing']['blockGap'])) {
        $dom = new DOMDocument();
        $dom->loadHTML(mb_convert_encoding($block_content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $wrapper = $dom->getElementsByTagName('div')->item(0);

        if ($wrapper) {
            $classes = $wrapper->getAttribute('class');
            $wrapper->setAttribute('class', $classes . ' has-custom-block-gap');

            $block_content = $dom->saveHTML();
        }
    }

    return $block_content;
}
