<?php namespace Level;


/**
 * LvlTaxonomies class.
 * Registers all taxonomy for the theme. Hooks into init.
 */
class Taxonomies
{
    public function __construct()
    {
        // Register taxonomy framework
    }

	/**
	 * Registers a taxonomy.
	 *
	 * @param string       $taxonomy
	 * @param array|string $postTypes
	 * @param string       $singular
	 * @param string       $plural
	 * @param string       $type (public, private, hidden)
	 * @param array        $args Additional arguments for the taxonomy
	 *
	 * @return void
	 */
    public function registerTax(string $taxonomy, array|string $postTypes, string $singular, string $plural, string $type = 'public', array $args = []): void
    {
        $labels = $this->getTaxLabels($singular, $plural);

        // Default options for taxonomy
        $default_args = array(
			'labels' => $labels,
			'description' => '',
		);

		$default_args = array_merge($default_args, $this->getDefaults($type));

        $args = wp_parse_args($args, $default_args);

        register_taxonomy($taxonomy, $postTypes, $args);
    }

	private function getDefaults($type = 'public'): array {
		$defaults = array(
			'public' => true,
			'publicly_queryable' => true,
			'hierarchical' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud' => true,
			'show_in_quick_edit' => true,
			'show_admin_column' => true,
			'meta_box_cb' => null,
			'capabilities' => array(),
			'rewrite' => true,
			'query_var' => true,
			'update_count_callback' => '',
			'_builtin' => false,
			'show_in_rest' => true,
			'rest_base' => false,
			'rest_controller_class' => false,
		);

		switch ($type) {
			case 'public':
				$defaults['public'] = true;
				$defaults['publicly_queryable'] = true;
				$defaults['show_ui'] = true;
				$defaults['show_in_menu'] = true;
				$defaults['show_in_nav_menus'] = true;
				$defaults['show_tagcloud'] = true;
				$defaults['show_in_quick_edit'] = true;
				$defaults['show_admin_column'] = true;
				break;
			case 'private':
				$defaults['public'] = false;
				$defaults['publicly_queryable'] = false;
				$defaults['show_ui'] = true;
				$defaults['show_in_menu'] = true;
				$defaults['show_in_nav_menus'] = false;
				$defaults['show_tagcloud'] = false;
				$defaults['show_in_quick_edit'] = true;
				$defaults['show_admin_column'] = true;
				break;
			case 'hidden':
				$defaults['public'] = false;
				$defaults['publicly_queryable'] = false;
				$defaults['show_ui'] = false;
				$defaults['show_in_menu'] = false;
				$defaults['show_in_nav_menus'] = false;
				$defaults['show_tagcloud'] = false;
				$defaults['show_in_quick_edit'] = false;
				$defaults['show_admin_column'] = false;
				$defaults['show_in_rest'] = false;
				break;
		}

		return $defaults;
	}




	/**
     * Retrieves the labels for a custom taxonomy.
     *
     * @param string $singular
     * @param string $plural
     *
     * @return array
     */
    private function getTaxLabels($singular, $plural)
	{

		return array (
			'name'                       => __($plural),
			'singular_name'              => __($singular),
			'menu_name'                  => __($plural),
			'all_items'                  => __('All ' . $plural),
			'edit_item'                  => __('Edit ' . $singular),
			'view_item'                  => __('View ' . $singular),
			'update_item'                => __('Update ' . $singular),
			'add_new_item'               => __('Add New ' . $singular),
			'new_item_name'              => __('New ' . $singular),
			'parent_item'                => __('Parent ' . $singular),
			'parent_item_colon'          => __('Parent ' . $singular . ':'),
			'search_items'               => __('Search ' . $plural),
			'popular_items'              => __('Popular ' . $plural),
			'separate_items_with_commas' => __('Separate ' . strtolower($plural) . ' with commas'),
			'add_or_remove_items'        => __('Add or remove ' . strtolower($plural)),
			'choose_from_most_used'      => __('Choose from the most used ' . strtolower($plural)),
			'not_found'                  => __('No ' . strtolower($plural) . ' found'),
		);
	}
}

///**
// * Add custom rewrite rules for custom taxonomies
// *
// * @param   string    $taxonomy   The taxonomy slug
// * @param   string    $post_type  The post type slug
// * @param   string    $slug       The path/slug to use in the URL
// *
// * @return void
// */
//function lvl_setup_custom_taxonomy_rewrite ( $taxonomy, $post_type, $slug ) {
//	add_filter( 'register_taxonomy_args', function( $args, $current_taxonomy ) use ( $taxonomy, $slug ) {
//		if ( $current_taxonomy === $taxonomy ) {
//			$args['rewrite'] = [
//				'slug'       => $slug,
//				'with_front' => false,
//			];
//		}
//		return $args;
//	}, 10, 2 );
//
//	add_filter( 'post_type_link', function( $post_link, $post ) use ( $taxonomy, $post_type) {
//		if ( is_object( $post ) && $post->post_type === $post_type ) {
//			$terms = wp_get_object_terms( $post->ID, $taxonomy );
//			if ( $terms ) {
//				return str_replace( "%$taxonomy%", $terms[0]->slug, $post_link );
//			}
//		}
//		return $post_link;
//	}, 1, 2 );
//
//	add_action( 'init', function() use ( $taxonomy, $post_type, $slug ) {
//		add_rewrite_rule( '^' . $slug . '/([^/]*)/?', 'index.php?' . $taxonomy . '=$matches[1]', 'top' );
//	} );
//}
//
//lvl_setup_custom_taxonomy_rewrite( 'solution_category', 'solution', 'solutions/solution-categories' );