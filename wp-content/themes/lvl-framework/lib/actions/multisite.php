<?php if (!defined('ABSPATH')) {
    exit;
}

add_action('trashed_post', 'lvl_delete_post_relationship', 10, 1);
add_action('deleted_post', 'lvl_delete_post_relationship', 10, 1);

/**
 * Remove all relationships from WP Multisite Content Copier Pro table for a post when it is deleted.
 * @param $post_id
 * @return void
 */
function lvl_delete_post_relationship($post_id)
{
    // REF: /wp-content/plugins/wp-multisite-content-copier-pro/includes/wmcc-functions.php
    // $current_relationship = $wpdb->get_row( "SELECT * FROM ".$wpdb->base_prefix."wmcc_relationships WHERE type='$type' AND type_name='$type_name' AND ((source_item_id='$source_item_id' AND source_blog_id='$source_blog_id') || (destination_item_id='$source_item_id' AND destination_blog_id='$source_blog_id'))" );
    // $relationships = $wpdb->get_results( "SELECT * FROM ".$wpdb->base_prefix."wmcc_relationships WHERE relationship_id='$relationship_id'");

    global $wpdb;
    $relationships = $wpdb->get_results("SELECT * FROM " . $wpdb->base_prefix . "wmcc_relationships WHERE source_item_id='$post_id' OR destination_item_id='$post_id'");
    foreach ($relationships as $relationship) {
        $wpdb->delete($wpdb->base_prefix . 'wmcc_relationships', ['relationship_id' => $relationship->relationship_id]);
    }
}

// same for taxonomy terms
add_action('delete_term', 'lvl_delete_term_relationship', 10, 3);

/**
 * Remove all relationships from WP Multisite Content Copier Pro table for a term when it is deleted.
 * @param $term
 * @param $tt_id
 * @param $taxonomy
 * @return void
 */

function lvl_delete_term_relationship($term, $tt_id, $taxonomy)
{
    global $wpdb;
    $relationships = $wpdb->get_results("SELECT * FROM " . $wpdb->base_prefix . "wmcc_relationships WHERE source_item_id='$tt_id' OR destination_item_id='$tt_id'");
    foreach ($relationships as $relationship) {
        $wpdb->delete($wpdb->base_prefix . 'wmcc_relationships', ['relationship_id' => $relationship->relationship_id]);
    }
}