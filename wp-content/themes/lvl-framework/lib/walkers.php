<?php if (!defined('ABSPATH')) exit;

locate_template('lib/walkers/dropdown-walker.php', true, true);
//locate_template('lib/walkers/nav-walker.php', true, true);
//locate_template('lib/walkers/class-wp-bootstrap-navwalker.php', true, true);
//locate_template('lib/walkers/nav-walker-v2.php', true, true);
locate_template('lib/walkers/LVL_Nav_Walker.php', true, true);


add_action('after_setup_theme', 'lvl_register_walkers');
function lvl_register_walkers()
{
//    new lvl_extend_nav_menu();
//    new WP_Bootstrap_Navwalker();
//    new Dropdown_Walker();
}