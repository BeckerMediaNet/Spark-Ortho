<?php if (!defined('ABSPATH')) exit;

// var_dump($block); die();

$render = function ($block, $is_preview, $content) {

    $the_block = new Level\Block($block);

    $id = $the_block->getId();

    $video_type = $the_block->getField('video_type');
    $video_url = $the_block->getField('video_url');
//    $video_embed = $the_block->getField('video_embed');
    $video_file = $the_block->getField('video_file');
    if ($video_type === 'file' && $video_file) {
        $video_url = $video_file['url'];
    }

    if (!$video_url && !$is_preview)
        return;

    $poster = $the_block->getField('video_poster');

    ob_start();

    if($is_preview && !$video_url) {
        echo $the_block->previewNotice('info', 'Please select a video file or URL to display the video.');
    }
    ?>

    <div class="backdrop"></div>
    <div class="video-wrapper">
        <div tabindex="0" class="video"
             data-src="<?php echo $video_url; ?>"
             data-type="<?php echo $video_type; ?>"
             style="background-image:url(<?php echo($poster ?: get_template_directory_uri() . '/dist/img/video-poster.webp'); ?>); "></div>
    </div>

    <?php

    $output = ob_get_clean();

    echo $the_block->renderSection($output);

};

$render($block, $is_preview, $content);