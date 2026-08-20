<?php if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dentist Cards Block Functions
 */

add_action('init', 'lvl_register_dentist_cards_block_styles');
function lvl_register_dentist_cards_block_styles()
{
    register_block_style('lvl/dentist-cards', array(
        'name'         => 'alternating-bg-colors',
        'label'        => __('Alternating Background Colors', 'theme'),
    ));
}

// Enqueue dentist cards AJAX variables (matches locations pattern)
add_action('wp_enqueue_scripts', 'lvl_dentist_cards_localization');
function lvl_dentist_cards_localization()
{
    // Localize to the main child theme script (same as locations)
    wp_localize_script('lvl-child-scripts', 'dentist_cards_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('dentist_cards_nonce')
    ]);
}
require_once __DIR__ . '/ajax.php';
