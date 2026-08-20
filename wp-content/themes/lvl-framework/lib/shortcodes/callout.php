<?php if( ! defined('ABSPATH') ) exit;
//[callout][/callout]
add_shortcode('callout', 'lvl_callout_handler');
function lvl_callout_handler( $atts, $content = null) {

    extract(shortcode_atts([
        'xclass' => ''
    ], $atts));

    ob_start(); ?>
    <div class="callout <?php echo $atts['xclass']; ?>">
        <div class="wrapper">
            <?php echo $content; ?>
        </div>
    </div>
    <?php
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}