<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! defined( 'LVL_THEME_URI' ) ) {
	define( "LVL_THEME_URI", get_template_directory_uri() );
}
if ( ! defined( 'LVL_FRAMEWORK_CONFIG' ) ) {
	define( "LVL_FRAMEWORK_CONFIG", locate_template( 'framework.config.json', false ) );
}

// ENABLE CONDITIONAL LOADING BLOCK ASSETS
add_filter( 'should_load_separate_core_block_assets', '__return_true' );

/**
 * Sets up the main theme functionality (menus, html5, featured-images etc...).
 * @return void
 */
add_action( 'after_setup_theme', function () {
	// TODO: reevaluate usefulness of framework.config.json
//	if ( file_exists( LVL_FRAMEWORK_CONFIG ) ) {
//		$framework_config = json_decode( file_get_contents( LVL_FRAMEWORK_CONFIG ), true );
//		if ( isset( $framework_config['theme_support'] ) ) {
//			foreach ( $framework_config['theme_support'] as $feature => $args ) {
//				add_theme_support( $feature, $args );
//			}
//		}
//
//		if ( isset( $framework_config['menus'] ) ) {
//			foreach ( $framework_config['menus'] as $key => $menu ) {
//				register_nav_menu( $menu['slug'] ?? 'lvl' . $key, __( $menu['label'], 'theme' ) ?? __( 'Label Missing: ', 'theme' ) . $key );
//			}
//		}
//
//		if ( isset( $framework_config['post_thumbnails'] ) ) {
////			foreach ( $framework_config['post_thumbnails'] as $post_type => $args ) {
//			add_theme_support( 'post-thumbnails', $framework_config['post_thumbnails'] );
////			}
//		}
//
//		if ( isset( $framework_config['html5'] ) ) {
//			add_theme_support( 'html5', $framework_config['html5'] );
//		}
//
//		if ( isset( $framework_config['editor_styles'] ) ) {
//			add_editor_style( $framework_config['editor_styles'] );
//		}
//	} else {
	// Adding Menu Support.
	add_theme_support( 'menus' );
	register_nav_menus( [
		'main-menu'   => __( 'Main Menu' ),
		'utility'     => __( 'Utility Nav' ),
		'mobile-menu' => __( 'Mobile CTA Nav' ),
		//		'footer'      => __( 'Footer Menu' ),
		// 'footer-1'  => __( 'Footer Column 1 Menu' ),
		// 'footer-2'  => __( 'Footer Column 2 Menu' ),
		//		'colophon'    => __( 'Colophon Menu' ),
	] );

	// Support for HTML5 Markup.
	add_theme_support( 'html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	] );

	// Adding Featured Image Support.
	add_theme_support( 'post-thumbnails' );
//	add_theme_support( 'post-thumbnails', [
//		'post',
//		'landing-page',
//		'location',
//		'author',
//		'event',
//		'resource',
//		'testimonial',
//		'team-member',
//	] );
//	}

	// remove core block patterns
	remove_theme_support( 'core-block-patterns' );

	// Remove admin bar for non-administrators.
	// if( ! current_user_can( 'manage_options' ) && ! is_admin() ) show_admin_bar( false );

} );

add_filter( 'should_load_remote_block_patterns', '__return_false' );

/**
 * Removes customize option from admin bar.
 * @return void
 */
add_action( 'wp_before_admin_bar_render', 'lvl_remove_admin_bar_customize' );
function lvl_remove_admin_bar_customize(): void {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu( 'customize' );
}


/**
 * Adds "Kitchen Sink" to admin bar.
 * Don't forget to make a Kitchen Sink page!
 * And to noindex it!
 * @return void
 */
add_action( 'admin_bar_menu', 'lvl_add_toolbar_items', 100 );
function lvl_add_toolbar_items( $admin_bar ): void {
	$admin_bar->add_menu( [
		'id'    => 'kitchen-sink',
		'title' => 'Kitchen Sink',
		'href'  => home_url( '/kitchen-sink/' ),
		'meta'  => [ 'title' => __( 'Kitchen Sink' ) ],
	] );

	// if page exists with slug "wordpress-guide" add to admin bar
	$guidePage = get_page_by_path( 'wordpress-guide' );
	if ( $guidePage ) {
		$admin_bar->add_menu( [
			'id'    => 'wordpress-guide',
			'title' => 'WordPress Guide',
			'href'  => home_url( '/wordpress-guide/' ),
			'meta'  => [ 'title' => __( 'WordPress Guide' ) ],
		] );
	}
}


add_action( 'admin_menu', 'lvl_reusable_blocks_admin_menu' );
function lvl_reusable_blocks_admin_menu(): void {
	add_menu_page( 'Patterns', 'Patterns', 'edit_posts', 'edit.php?post_type=wp_block', '', 'dashicons-editor-table', 22 );
}


add_action( 'template_redirect', 'lvl_redirect_tags' );
function lvl_redirect_tags() {
	global $wp;
	global $post;
	$current_url = home_url( add_query_arg( array(), $wp->request ) );
	if ( strpos( $current_url, '/tag/' ) != false ) {
		$tagid = get_queried_object()->term_id;
		if ( $tagid ) {
			wp_redirect( home_url( '/resources/?post_tag=' . $tagid ) );
		} else {
			wp_redirect( home_url( '/resources/' ) );
		}
		die();
	} else {
		return false;
	}
}

/**
 * Automatically set "Resource Type" taxonomy as "Blogs" for blog posts
 */
function set_blog_resource_type( $post_id, $post, $update ) {
	if ( 'post' != $post->post_type ) {
		return;
	} // Only run for blog posts (not pages, etc.)

	// If this is a revision, don't send the email.
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	// make sure the resource_type taxonomy Blog is set on post, if not, add it to the list of terms
	$blog = get_term_by( 'slug', 'blog', 'resource_types' );
	if ( $blog ) {
		$terms    = wp_get_post_terms( $post_id, 'resource_types' );
		$term_ids = wp_list_pluck( $terms, 'term_id' );
		if ( ! in_array( $blog->term_id, $term_ids ) ) {
			wp_set_post_terms( $post_id, $blog->slug, 'resource_types', true );
		}
	}
}

//add_action( 'wp_insert_post', 'set_blog_resource_type', 10, 3 );


function lvl_theme_message( $message ) {
	return <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Missing Assets</title>
        <style>
            body {
                background-color: #2c3e50;
                font-family: 'Arial', sans-serif;
                color: #ecf0f1;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .container {
                background-color: #34495e;
                padding: 2rem;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                text-align: center;
            }
            .container p {
                margin: 1rem 0;
            }
            .container code {
                background-color: #2c3e50;
                padding: 0.2rem 0.4rem;
                border-radius: 4px;
                color: #e74c3c;
            }
            .container a {
                color: #1abc9c;
                text-decoration: none;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
    <div class="container">
        $message
    </div>
    </body>
    </html>
    HTML;
}

add_action( 'init', function () {
	if ( ! is_user_logged_in() ) {
		return;
	}
	if ( ! is_admin() ) {
		if ( ! function_exists( 'get_field' ) ) {

			$message = <<<HTML
            <h1>Advanced Custom Fields Pro Missing</h1>
            <p>Advanced Custom Fields Pro is an integral part of the WordPress Theme activated.</p>
            <p>Please install and/or activate the plugin for proper functionality.</p>
            <p><a href="/wp-admin/plugins.php?plugin_status=all&paged=1&s" style="color: inherit;">&rarr; Activate Plugins &larr;</a></p>
            HTML;

			echo lvl_theme_message( $message );

			die();
		}
	}
} );

// if "dist" folder doesn't exist in theme root, display error message and notes to compile
add_action( 'init', function () {
	if ( ! is_user_logged_in() ) {
		return;
	}
	if ( ! is_admin() ) {
		if ( ! file_exists( get_template_directory() . '/dist' ) ) {

			$message = <<<HTML
            <h1>Compiled Assets Missing</h1>
            <p>Compiled assets are missing from the Parent and/or Child theme.</p>
            <p>Please compile the assets in both themes using the following commands:</p>
            <p><code>npm install</code></p>
            <p><code>npm run build</code></p>    
            HTML;

			echo lvl_theme_message( $message );

			die();
		}
	}
} );

if ( ! function_exists( 'var_dumped' ) ) {
	/**
	 * Var dump and die
	 *
	 * @param      $var
	 * @param bool $die
	 *
	 * @return void
	 */
	function var_dumped( $var, bool $die = false ): void {
		echo '<pre>';
		ob_start();
		var_dump( $var );
		$vd = ob_get_clean();
		echo esc_html( $vd );
		echo '</pre>';
		if ( $die ) {
			die();
		}
	}
}

// Classes
locate_template( 'lib/classes/Block.php', true, true );
locate_template( 'lib/classes/PostTypes.php', true, true );
locate_template( 'lib/classes/Taxonomies.php', true, true );
locate_template( 'lib/classes/NavWalker.php', true, true );
locate_template( 'lib/classes/Helper.php', true, true );


/**
 * Including other theme functionality in separate files.
 */
locate_template( 'lib/acf.php', true, true );
//locate_template( 'lib/admin/theme-options.php', true, true ); // TODO: Future
locate_template( 'lib/helpers.php', true, true );
locate_template( 'lib/blocks/hooks_blocks.php', true, true );
locate_template( 'lib/filters.php', true, true );
locate_template( 'lib/actions.php', true, true );
locate_template( 'lib/endpoints.php', true, true );
locate_template( 'lib/ajax.php', true, true );
locate_template( 'lib/post-types.php', true, true );
locate_template( 'lib/taxonomies.php', true, true );
locate_template( 'lib/widgets.php', true, true );
locate_template( 'lib/shortcodes.php', true, true );
locate_template( 'lib/scripts-styles.php', true, true );
locate_template( 'lib/forms.php', true, true );
locate_template( 'lib/tracking.php', true, true );
locate_template( 'lib/migration.php', true, true );

locate_template( 'lib/_experimental/ai.php', true, true );


if ( is_admin() ) {
	locate_template( 'lib/admin/functions.php', true, true );
}