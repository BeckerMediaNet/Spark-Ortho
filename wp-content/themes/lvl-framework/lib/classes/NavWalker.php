<?php
/**
 * WP Bootstrap Mega Navwalker
 *
 * @package WP-Bootstrap-Mega-Navwalker
 */

/*
 * Class Name: LvlNavWalker
 * Plugin Name: WP Bootstrap Mega Navwalker
 * Plugin URI:  https://github.com/wp-bootstrap/WP-Bootstrap-MegaMenu-Navwalker
 * Description: A custom WordPress nav walker class to implement the Bootstrap 4 navigation style in a custom theme using the WordPress built in menu manager.
 * Author: WP Bootstrap Team, @jaycbrf4
 * Version: 1.0.0
 * Author URI: https://github.com/wp-bootstrap
 * GitHub Plugin URI: https://github.com/wp-bootstrap/WP-Bootstrap-MegaMenu-Navwalker
 * GitHub Branch: master
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
*/

namespace Level;
use Walker_Nav_Menu;

if ( ! class_exists( 'NavWalker' ) ) {
	/**
	 * WP_Bootstrap_Mega_Navwalker class.
	 *
	 * @extends Walker_Nav_Menu
	 */
	class NavWalker extends Walker_Nav_Menu {

		private $uid;

		/**
		 * Start Level.
		 *
		 * @access public
		 *
		 * @param mixed &$output Output.
		 * @param int    $depth (default: 0) Depth.
		 * @param array  $args (default: array()) Arguments.
		 *
		 * @return void
		 */
		public function start_lvl( &$output, $depth = 0, $args = array() ) {

			$indent = str_repeat( "\t", $depth );
			$output .= "\n$indent<div id=\"$this->uid\" class=\"dropdown-menu collapse\">\n";
			$output .= "$indent<ul class=\"dropdown-content\">\n"; // Add the wrapping div
		}


		/**
		 * End Level.
		 *
		 * @access public
		 *
		 * @param mixed &$output Output.
		 * @param int    $depth (default: 0) Depth.
		 * @param array  $args (default: array()) Arguments.
		 *
		 * @return void
		 */
		public function end_lvl( &$output, $depth = 0, $args = array() ) {

			$indent = str_repeat( "\t", $depth );
			$output .= "$indent</ul>\n";
			$output .= "$indent</div>\n"; // Close the wrapping div
		}


		/**
		 * Start El.
		 *
		 * @access public
		 *
		 * @param mixed &$output Output.
		 * @param mixed  $item Item.
		 * @param int    $depth (default: 0) Depth.
		 * @param array  $args (default: array()) Arguments.
		 * @param int    $id (default: 0) ID.
		 *
		 * @return void
		 */
		public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
			$this->uid         = uniqid( 'menu-' );
			$indent            = ( $depth ) ? str_repeat( "\t", $depth ) : '';
			$has_mega_menu     = get_post_meta( $item->ID, '_menu_item_mega_menu', true );
			$is_dropdown_hover = get_field( 'is_dropdown_hover', 'options' );


			$classes   = empty( $item->classes ) ? array() : (array) $item->classes;
			$classes[] = 'nav-item';

			if ( $has_mega_menu ) {
				$classes[] = 'mm-dropdown'; // TODO: Review Integration
			}

			if ( in_array( 'menu-item-has-children', $classes ) || $has_mega_menu ) {
//				$classes[] = 'dropdown dropdown-center';
				$classes[] = 'dropdown';

				// if 2+ levels then dropend
				if ( $depth > 0 ) {
					$classes[] = 'dropend';
				} else {
					$classes[] = 'dropdown-center';
				}

				// Check if the ACF field "is_dropdown_hover" is true
				if ( $is_dropdown_hover ) {
					$classes[] = 'dropdown-hover';
				}
			}

			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
			$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

			$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
			$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

			$output .= $indent . '<li' . $id . $class_names . '>';

			$atts           = array();
			$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
			$atts['target'] = ! empty( $item->target ) ? $item->target : '';
			$atts['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';
			$atts['href']   = ! empty( $item->url ) ? $item->url : '';

			if ( in_array( 'menu-item-has-children', $classes ) || $has_mega_menu ) {
				// $atts['class'] = 'nav-link dropdown-toggle';
				$atts['class'] = 'nav-link';
				$atts['id'] = 'navbarDropdown' . $item->ID;
				// $atts['role']               = 'button';
				// $atts['data-bs-toggle']     = 'dropdown';
				// $atts['data-bs-auto-close'] = 'outside';
				// $atts['aria-expanded']      = 'false';

				if ( $depth > 0 ) {
//                $atts['data-bs-offset'] = '0,10';
					$atts['data-bs-reference'] = 'toggle';
				}
			} else {
				$atts['class'] = 'nav-link';
			}

			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

			$attributes = '';
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$value      = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
					$attributes .= ' ' . $attr . '="' . $value . '"';
				}
			}

			$title = apply_filters( 'the_title', $item->title, $item->ID );
			$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

			$item_output = $args->before;
			$item_output .= '<a' . $attributes . '>';
			$item_output .= $args->link_before . $title . $args->link_after;
			$item_output .= '</a>';

			// Add a button for toggling the dropdown on mobile devices
			if ( in_array( 'menu-item-has-children', $classes ) || $has_mega_menu ) {
				// $item_output .= '<button class="dropdown-toggle-btn d-lg-none" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"><i class="fas fa-chevron-down"></i></button>';
                // $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" aria-hidden="true" class="dropdown-icon bi bi-chevron-down" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/></svg>';
				$item_output .= "<button title=\"Dropdown Toggle\" class=\"dropdown-toggle-btn btn btn-link d-lg-none collapsed\" data-bs-toggle=\"collapse\" data-bs-target=\"#$this->uid\" aria-expanded=\"false\" aria-controls=\"$this->uid\"><span class=\"visually-hidden\">Toggle more. </span></button>";
			}

			$item_output .= $args->after;

			$args->has_mega_menu = $has_mega_menu;

			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );

			if ( $has_mega_menu ) {

				$output .= "<div id=\"$this->uid\" class=\"mega-menu-wrapper dropdown-menu collapse depth_$depth\"><ul data-bs-theme=\"light\" class=\"dropdown-content\">";

				ob_start();
				dynamic_sidebar( 'mega-menu-item-' . $item->ID );
				$dynamic_sidebar = ob_get_clean();
				$output          .= $dynamic_sidebar;
				$output          .= '</ul></div>';
			}
		}



		static function addMegaMenuContext( $item_id, $item ): void {

			if ( $item->menu_item_parent == 0 ) {
				$menu_mega_menu = get_post_meta( $item_id, '_menu_item_mega_menu', true ); ?>

                <p class="field-mega-menu description description-wide">
                    <label for="edit-menu-item-mega-menu-<?php echo $item_id; ?>">
                        <input type="checkbox" id="edit-menu-item-mega-menu-<?php echo $item_id; ?>" class="edit-menu-item-mega-menu" name="menu-item-mega-menu[<?php echo $item_id; ?>]" value="true" <?php echo ( $menu_mega_menu == true ) ? 'checked' : ''; ?> > Mega Menu?
                    </label>
                    <small class="description-wide">To edit the mega menu go to the widgets editor <a href="/wp-admin/widgets.php">here</a></small>
                </p>

			<?php }
		}

		static function updateNavMenuMeta( $menu_id, $menu_item_db_id ): void {
			// Save mega menu
			if ( ! empty( $_POST['menu-item-mega-menu'][ $menu_item_db_id ] ) ) {
				update_post_meta( $menu_item_db_id, '_menu_item_mega_menu', sanitize_text_field( $_POST['menu-item-mega-menu'][ $menu_item_db_id ] ) );
			} else {
				update_post_meta( $menu_item_db_id, '_menu_item_mega_menu', '' );
			}
		}

        static function addItemClass( $classes, $item, $args ) {

	        $mega_menu = get_post_meta( $item->ID, '_menu_item_mega_menu', true );

	        if ( $mega_menu ) {
		        $classes[] = 'mega-menu';
	        }

	        return $classes;
        }

        static function addMenuAttributes( $nav_menu, $args ) {
	        if ( str_contains( $nav_menu, 'theme-' ) ) {
		        // if light then add data-bs-theme="light", if dark then add data-bs-theme="dark"
		        $nav_menu = str_replace( '<ul id="', '<ul data-bs-theme="' . ( str_contains( $nav_menu, 'theme-light' ) ? 'light' : 'dark' ) . '" id="', $nav_menu );
	        }

	        return $nav_menu;
        }

        static function registerMegaMenuWidgets() {
            $menu_locations = get_nav_menu_locations();
            $menu_location  = 'main-menu'; // replace this with your menu location

            if ( isset( $menu_locations[ $menu_location ] ) ) {
                $menu_object = wp_get_nav_menu_object( $menu_locations[ $menu_location ] );

                if ( $menu_object ) {
                    $menu_items = wp_get_nav_menu_items( $menu_object->term_id );

                    if ( $menu_items ) {

                        foreach ( $menu_items as $key => $item ) {

                            if ( get_post_meta( $item->ID, '_menu_item_mega_menu', true ) != '' ) {
                                register_sidebar( array(
                                    'id'            => 'mega-menu-item-' . $item->ID,
                                    'description'   => 'Mega Menu items',
                                    'name'          => $item->title . ' - Mega Menu',
                                    'before_widget' => '<li id="%1$s" class="mega-menu-item">',
                                    'after_widget'  => '</li>',
                                ) );
                            }
                        }
                    }
                }
            }
        }
	}
}

// Add Mega Menu option to menu items
add_action( 'wp_nav_menu_item_custom_fields', 'Level\NavWalker::addMegaMenuContext', 10, 2 );
add_action( 'wp_update_nav_menu_item', 'Level\NavWalker::updateNavMenuMeta', 10, 2 );

// Add mega-menu class to li
add_filter( 'nav_menu_css_class', 'Level\NavWalker::addItemClass', 1, 3 );

// filter wp_nav_menu to add attribute based on menu_class
add_filter( 'wp_nav_menu', 'Level\NavWalker::addMenuAttributes', 10, 2 );


// Register Main Nav mega menu widgets
add_action( 'init', 'Level\NavWalker::registerMegaMenuWidgets' );

//NavWalker::register_main_nav_mega_menu_widgets();