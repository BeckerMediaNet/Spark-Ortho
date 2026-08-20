<?php
if (!defined('ABSPATH')) {
    exit;
}

// on menu save
add_action('wp_update_nav_menu', 'lvl_save_menu', 10, 1);
function lvl_save_menu($menu_id): void
{
    delete_transient('lvl_rest_header');
}