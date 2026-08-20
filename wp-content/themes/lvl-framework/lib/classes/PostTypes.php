<?php namespace Level;

/**
 * CustomPostTypes class.
 * Registers all custom post types for the theme. Hooks into init.
 */
class PostTypes {
	private string $singular;
	private string $plural;

	public function __construct() {
		// Register custom post type framework
	}

	/**
	 * Registers a custom post type.
	 *
	 * @param string $postType
	 * @param string $singular
	 * @param string $plural
	 * @param array  $features
	 * @param string $type (public, private, hidden)
	 * @param array  $args
	 *
	 * @return void
	 */
	public function registerCPT( string $postType, string $singular, string $plural, array $features = [], string $type = 'public', array $args = [] ): void {
		// Set the singular and plural labels
		$this->singular = $singular;
		$this->plural   = $plural;

		$default_args = $this->getDefaults( $type );
		$default_args['labels'] = $this->getCPTLabels();

		$args = wp_parse_args( $args, $default_args );

		register_post_type( $postType, $args );

		foreach ( $features as $feat ) {
			$support = get_theme_support( $feat );
			if ( ! is_array( $support ) ) {
				$support = array( array() );
			} elseif ( ! isset( $support[0] ) || ! is_array( $support[0] ) ) {
				$support[0] = array();
			}
			$support[0][] = $postType;

			add_theme_support( $feat, $support[0] );
		}
	}

	/**
	 * Retrieves the labels for a custom post type.
	 *
	 * @param string $singular
	 * @param string $plural
	 *
	 * @return array
	 */
	private function getCPTLabels(): array {
		return array(
			'name'                  => $this->plural,
			'singular_name'         => $this->singular,
			'add_new'               => 'Add New',
			'add_new_item'          => 'Add New ' . $this->singular,
			'edit_item'             => 'Edit ' . $this->singular,
			'new_item'              => 'New ' . $this->singular,
			'view_item'             => 'View ' . $this->singular,
			'search_items'          => 'Search ' . $this->plural,
			'not_found'             => 'No ' . $this->plural . ' found',
			'not_found_in_trash'    => 'No ' . $this->plural . ' found in trash',
			'parent_item_colon'     => 'Parent ' . $this->singular . ':',
			'all_items'             => 'All ' . $this->plural,
			'archives'              => $this->plural . ' Archives',
			'insert_into_item'      => 'Insert into ' . $this->singular,
			'uploaded_to_this_item' => 'Uploaded to this ' . $this->singular,
			'featured_image'        => 'Featured Image',
			'set_featured_image'    => 'Set Featured Image',
			'remove_featured_image' => 'Remove Featured Image',
			'use_featured_image'    => 'Use as Featured Image',
			'menu_name'             => $this->plural,
			'name_admin_bar'        => $this->singular,
		);
	}

	private function getDefaults( $type = 'public' ): array {

		$defaults = array(
			'public'              => true,
			'show_ui'             => true,
			'has_archive'         => true,
			'show_in_rest'        => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'exclude_from_search' => false,
			'capability_type'     => 'page',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'rewrite'             => [ 'slug' => _wp_to_kebab_case( $this->singular ), 'with_front' => false ],
			'query_var'           => true,
			'menu_icon'           => 'dashicons-superhero',
			'supports'            => [ 'title', 'thumbnail' ],
		);

		if ( 'private' === $type ) {
			$defaults['public']              = false;
			$defaults['show_ui']             = true;
			$defaults['has_archive']         = false;
			$defaults['show_in_rest']        = true;
			$defaults['show_in_menu']        = true;
			$defaults['show_in_nav_menus']   = false;
			$defaults['exclude_from_search'] = true;
			$defaults['capability_type']     = 'page';
			$defaults['map_meta_cap']        = true;
			$defaults['hierarchical']        = false;
			$defaults['rewrite']             = [ 'slug' => _wp_to_kebab_case( $this->singular ), 'with_front' => false ];
			$defaults['query_var']           = true;
			$defaults['menu_icon']           = 'dashicons-superhero';
			$defaults['supports']            = [ 'title', 'thumbnail' ];

		} elseif ( 'hidden' === $type ) {
			$defaults['public']              = false;
			$defaults['show_ui']             = false;
			$defaults['has_archive']         = false;
			$defaults['show_in_rest']        = false;
			$defaults['show_in_menu']        = false;
			$defaults['show_in_nav_menus']   = false;
			$defaults['exclude_from_search'] = true;
			$defaults['capability_type']     = 'page';
			$defaults['map_meta_cap']        = true;
			$defaults['hierarchical']        = false;
			$defaults['rewrite']             = [ 'slug' => _wp_to_kebab_case( $this->singular ), 'with_front' => false ];
			$defaults['query_var']           = true;
			$defaults['menu_icon']           = 'dashicons-superhero';
			$defaults['supports']            = [ 'title', 'thumbnail' ];
		}

		return $defaults;

	}
}