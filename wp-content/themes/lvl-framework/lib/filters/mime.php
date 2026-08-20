<?php if (!defined('ABSPATH')) exit;



// Allow SVG
add_filter( 'wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {

    global $wp_version;
    if ( $wp_version !== '4.7.1' ) {
        return $data;
    }

    $filetype = wp_check_filetype( $filename, $mimes );

    return [
        'ext'             => $filetype['ext'],
        'type'            => $filetype['type'],
        'proper_filename' => $data['proper_filename']
    ];

}, 10, 4 );

function cc_mime_types( $mimes ){
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter( 'upload_mimes', 'cc_mime_types' );

function fix_svg() {
    echo '<style type="text/css">
        .attachment-266x266, .thumbnail img {
             width: 100% !important;
             height: auto !important;
        }
        </style>';
}
//add_action( 'admin_head', 'fix_svg' );

/**
 * Removes the width and height attributes of <img> tags for SVG
 *
 * Without this filter, the width and height are set to "1" since
 * WordPress core can't seem to figure out an SVG file's dimensions.
 *
 * For SVG:s, returns an array with file url, width and height set
 * to null, and false for 'is_intermediate'.
 *
 * @wp-hook image_downsize
 * @param mixed $out Value to be filtered
 * @param int $id Attachment ID for image.
 * @return bool|array False if not in admin or not SVG. Array otherwise.
 */
function lvl_fix_svg_size_attributes( $out, $id ) {
    $image_url  = wp_get_attachment_url( $id );
    $file_ext   = pathinfo( $image_url, PATHINFO_EXTENSION );

    if ( is_admin() || 'svg' !== $file_ext ) {
        return false;
    }

    return array( $image_url, null, null, false );
}
add_filter( 'image_downsize', 'lvl_fix_svg_size_attributes', 10, 2 );



// Add AVIF image support to WordPress

add_filter( 'upload_mimes', 'filter_allowed_mimes_avif', 1000, 1 );
function filter_allowed_mimes_avif( $mime_types ) {
    $mime_types['avif'] = 'image/avif';
    return $mime_types;
}
// Get correct dimensions for AVIF images
add_filter( 'wp_generate_attachment_metadata', 'avif_image_metadata', 1, 3 );
function avif_image_metadata( $metadata, $attachment_id, $context ) {
    if ( empty( $metadata ) ) {
        return $metadata;
    }

    $attachment_post = get_post( $attachment_id );
    if ( ! $attachment_post || is_wp_error( $attachment_post ) ) {
        return $metadata;
    }

    if ( 'image/avif' !== $attachment_post->post_mime_type ) {
        return $metadata;
    }

    if ( ( ! empty( $metadata['width'] ) && ( 0 !== $metadata['width'] ) ) && ( ! empty( $metadata['height'] ) && 0 !== $metadata['height'] ) ) {
        return $metadata;
    }

    $file = get_attached_file( $attachment_id );
    if ( ! $file ) {
        return $metadata;
    }

    if ( empty( $metadata['width'] ) ) {
        $metadata['width'] = 0;
    }

    if ( empty( $metadata['height'] ) ) {
        $metadata['height'] = 0;
    }

    if ( empty( $metadata['file'] ) ) {
        $metadata['file'] = _wp_relative_upload_path( $file );
    }

    if ( empty( $metadata['sizes'] ) ) {
        $metadata['sizes'] = array();
    }

    // Create an image resource from the AVIF file
    $image = imagecreatefromavif( $file );

    // Get the width and height of the image
    $metadata['width']  = imagesx( $image );
    $metadata['height'] = imagesy( $image );

    // Destroy the image resource
    imagedestroy( $image );

    return $metadata;
}


add_filter('wp_get_attachment_image_src', 'lvl_fix_wp_get_attachment_image_svg', 10, 4);
function lvl_fix_wp_get_attachment_image_svg($image, $attachment_id, $size, $icon)
{
    if (is_array($image) && preg_match('/\.svg$/i', $image[0]) && $image[1] <= 1) {
        try {
            $svg_file_path = get_attached_file($attachment_id);
            if (file_exists($svg_file_path)) {
                $svg = simplexml_load_file($svg_file_path);

                if ($svg !== false) {
                    $attr = $svg->attributes();
                    $width = (string)$attr->width;
                    $height = (string)$attr->height;
                    if (!empty($width) && !empty($height)) {
                        $image[1] = (int)$width;
                        $image[2] = (int)$height;
                    } elseif ($attr->viewBox) {
                        $viewbox = (string)$attr->viewBox;
                        if (!empty($viewbox)) {
                            $viewbox = explode(' ', $viewbox);
                            if (count($viewbox) === 4) {
                                $image[1] = (int)$viewbox[2];
                                $image[2] = (int)$viewbox[3];
                            }
                        }
                    }

                    $image_size = image_constrain_size_for_editor($image[1], $image[2], $size);
                    $image[1] = $image_size[0];
                    $image[2] = $image_size[1];
                }
            }

        } catch (Exception $e) {
            return $image;
        }
    }

    return $image;
}