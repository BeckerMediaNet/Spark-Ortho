<?php if (!defined('ABSPATH')) {
    exit;
}

// on saving a vendor post check and create a vendor_name taxonomy term if it doesn't exist
add_action('save_post_vendor', 'lvl_save_vendor', 10, 3);
function lvl_save_vendor($post_id, $post, $update): void
{
//    if ($update) {
//        return;
//    }
    if ($post->post_status === 'auto-draft') {
        return;
    }

    $vendor_name = get_the_title($post_id);
    $vendor_term = get_term_by('name', $vendor_name, 'vendor_name');

    if (!$vendor_term) {
        wp_insert_term($vendor_name, 'vendor_name');
    }
}