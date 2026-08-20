<?php if (!defined('ABSPATH')) {
    exit;
}

/**
 * Team Cards Block Functions
 */

add_action('init', 'lvl_register_team_cards_block_styles');
function lvl_register_team_cards_block_styles()
{
    register_block_style('lvl/team-cards', array(
        'name'         => 'alternating-bg-colors',
        'label'        => __('Alternating Background Colors', 'theme'),
    ));
}

// Enqueue team cards AJAX variables
add_action('wp_enqueue_scripts', 'lvl_team_cards_localization');
function lvl_team_cards_localization()
{
    // Localize to the main child theme script
    wp_localize_script('lvl-child-scripts', 'team_cards_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('team_cards_nonce')
    ]);
}
require_once __DIR__ . '/ajax.php';
