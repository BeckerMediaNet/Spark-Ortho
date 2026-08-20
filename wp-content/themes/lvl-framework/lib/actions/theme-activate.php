<?php if (!defined('ABSPATH')) {
	exit;
}

add_action('after_switch_theme', 'lvl_after_switch_theme');
/**
 * @throws Exception
 */
function lvl_after_switch_theme($old_theme_name, $old_theme = false): void
{
	update_option('lvl_theme_initialized', false);
	update_option('lvl_acf_initialized', false);
	// disable image default linking
	update_option('image_default_link_type', 'none');

	lvl_theme_create_kitchen_sink();
	if (get_option('lvl_theme_initialized') === true) {
		return;
	}

	lvl_theme_install_plugins();
	lvl_theme_upload_media();
	lvl_theme_social();
	lvl_theme_settings();
	lvl_theme_create_default_pages();


	update_option('lvl_theme_initialized', true);
}

// when 'advanced-custom-fields-pro/acf.php' is activated
//add_action( 'activated_plugin', 'lvl_theme_plugin_activation', 10, 2 );
function lvl_theme_plugin_activation($plugin, $network_activation): void
{
	if (get_option('lvl_acf_initialized') === true) {
		return;
	}
	if (get_option('lvl_theme_initialized') !== true) {
		return;
	}

	if ('advanced-custom-fields-pro/acf.php' === $plugin) {
		lvl_theme_upload_media();
		lvl_theme_social();

		update_option('lvl_acf_initialized', true);
	}
}

function lvl_theme_install_plugins(): void
{
	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	$plugins = [
			'advanced-custom-fields-pro' => 'acf.php',
			'admin-columns-pro'          => 'admin-columns-pro.php',
			'gravityforms'               => 'gravityforms.php',
	];

	if ($plugins) :
		$plugin_zips = glob(dirname(__FILE__, 3) . '/plugins/*.zip');

		foreach ($plugins as $plugin_name => $plugin_file) :
			if (!is_plugin_active($plugin_name . '/' . $plugin_file)) {
				$plugin = array_filter($plugin_zips, function ($zip) use ($plugin_name) {
					return str_contains($zip, $plugin_name);
				});

				$plugin_path = WP_PLUGIN_DIR . '/' . basename(reset($plugin));
				if (!file_exists($plugin_path)) {
					copy(reset($plugin), $plugin_path);
				}

				$installed = lvl_install_plugin_from_zip_file($plugin_path);

				activate_plugin($plugin_name . '/' . $plugin_file, '', false, true);
			}
		endforeach;
	endif;
}

function lvl_install_plugin_from_zip_file($zip_file_path)
{
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';

	// Check if zip file exists
	if (file_exists($zip_file_path)) {
		// Initialize WordPress Filesystem
		global $wp_filesystem;

		require_once(ABSPATH . '/wp-admin/includes/file.php');
		WP_Filesystem();

		$upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());
		$installed = $upgrader->install($zip_file_path);

		if ($installed) {
			// Plugin installed successfully
			return true;
		} else {
			// There was an error installing the plugin
			return false;
		}
	} else {
		// Zip file does not exist
		return false;
	}
}

function lvl_theme_settings(): void
{
	update_option('admin_email', 'wordpress@lvltools.com');
	update_option('blog_public', 0);
	update_option('permalink_structure', '/news/%postname%/');

	$wpmlc_settings = get_option('wpmlc_settings');
	if ($wpmlc_settings) {
		if (!($wpmlc_settings['wpmediacategory_taxonomy'] ?? false)) {
			$wpmlc_settings['wpmediacategory_taxonomy'] = 'media-category';
			update_option('wpmlc_settings', $wpmlc_settings);
		}
	} else {
		update_option('wpmlc_settings', ['wpmediacategory_taxonomy' => 'media-category']);
	}
}

function lvl_theme_social(): void
{
	//social_media_icon_links [name, url, social_links_icon]
	if (is_plugin_active('advanced-custom-fields-pro/acf.php')) {
		if (!get_field('social_media_icon_links', 'options')) {
			// add social media links
			$social_media_icon_links = [
					[
							'name'              => 'Facebook',
							'url'               => 'https://www.facebook.com/',
							'social_links_icon' => 'facebook',
					],
					[
							'name'              => 'Twitter',
							'url'               => 'https://twitter.com/',
							'social_links_icon' => 'twitter',
					],
					[
							'name'              => 'Instagram',
							'url'               => 'https://www.instagram.com/',
							'social_links_icon' => 'instagram',
					],
					[
							'name'              => 'LinkedIn',
							'url'               => 'https://www.linkedin.com/',
							'social_links_icon' => 'linkedin',
					],
					[
							'name'              => 'YouTube',
							'url'               => 'https://www.youtube.com/',
							'social_links_icon' => 'youtube',
					],

			];

			update_field('social_media_icon_links', $social_media_icon_links, 'options');
		}
	}

}

function lvl_theme_upload_media(): void
{
	$new_media = [];
	// look in theme media folder and copy file to uploads directory and add to media library
	$media = glob(dirname(__FILE__, 3) . '/src/media/*');
	if ($media) :
		foreach ($media as $file) :
			$file_name = strtolower(basename($file));

			if ($file_name === 'readme.md' || $file_name === '.gitignore') {
				continue;
			}

			$file_path = wp_upload_dir()['path'] . '/' . $file_name;
			if (!file_exists($file_path)) {
				copy($file, $file_path);
				$wp_filetype = wp_check_filetype($file_name, null);
				$attachment = array(
						'post_mime_type' => $wp_filetype['type'],
						'post_title'     => sanitize_file_name($file_name),
						'post_content'   => '',
						'post_status'    => 'inherit',
				);
				$attach_id = wp_insert_attachment($attachment, $file_path);
				require_once(ABSPATH . 'wp-admin/includes/image.php');
				$attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
				wp_update_attachment_metadata($attach_id, $attach_data);

				$new_media[$file_name] = $attach_id;
			} else {
				$attach_id = attachment_url_to_postid(wp_upload_dir()['url'] . '/' . $file_name);
				$new_media[$file_name] = $attach_id;
			}
		endforeach;
	endif;

	foreach ($new_media as $file_name => $attach_id) {
		if (is_plugin_active('advanced-custom-fields-pro/acf.php')) {
			if ($file_name === 'logo.svg' || $file_name === 'logo.png' || $file_name === 'logo.jpg' || $file_name === 'logo.jpeg' || $file_name === 'logo.webp') {
				if (!get_field('sitelogo', 'options')) {
					update_field('sitelogo', $attach_id, 'options');
				}
			}
			if ($file_name === 'logo-white.svg' || $file_name === 'logo-white.png' || $file_name === 'logo-white.jpg' || $file_name === 'logo-white.jpeg' || $file_name === 'logo-white.webp') {
				if (!get_field('footerlogo', 'options')) {
					update_field('footerlogo', $attach_id, 'options');
				}
			}
			if ($file_name === 'pre-footer.svg' || $file_name === 'pre-footer.png' || $file_name === 'pre-footer.jpg' || $file_name === 'pre-footer.jpeg' || $file_name === 'pre-footer.webp') {
				if (!get_field('pre_footer_img', 'options')) {
					update_field('pre_footer_img', $attach_id, 'options');
				}
			}
		}
	}
}


function lvl_theme_create_kitchen_sink(): int|WP_Error
{
	$sinkPageID = 0;
	$sinkPage = get_page_by_path('kitchen-sink', OBJECT);
	if (!isset($sinkPage)) {
		$sinkGuid = site_url() . '/kitchen-sink';

		$wphtml = <<<HTML
<!-- wp:lvl/section-wrapper {"name":"lvl/section-wrapper","data":{},"mode":"preview","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"bs":{"theme":""}} -->
<!-- wp:paragraph {"className":"lead"} -->
<p class="lead">Welcome! This "Kitchen Sink" page is for demonstrating what's possible using the custom content blocks in WordPress. This site is built using the <a href="https://getbootstrap.com/docs/5.1/utilities/api/" target="_blank" rel="noopener noreferrer">Bootstrap</a> front-end framework, so if you're comfortable with a little code, feel free to make use of their CSS utility classes. For example, this paragraph (<code>&lt;p&gt;&lt;/p&gt;</code>) includes the "lead" class.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">This is an example "Heading 1" (H1)</h1>
<!-- /wp:heading -->

<!-- wp:heading -->
<h2 class="wp-block-heading">This is an example "Heading 2" (H2)</h2>
<!-- /wp:heading -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">This is an example "Heading 3" (H3)</h3>
<!-- /wp:heading -->

<!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading">This is an example "Heading 4" (H4)</h4>
<!-- /wp:heading -->

<!-- wp:heading {"level":5} -->
<h5 class="wp-block-heading">This is an example "Heading 5" (H5)</h5>
<!-- /wp:heading -->

<!-- wp:heading {"level":6,"className":"mb-2"} -->
<h6 class="wp-block-heading mb-2">This is an example "Heading 6" (H6)</h6>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>TIP: retain semantic HTML by selecting heading levels based on <em>content structure</em> rather than <em>style</em>. If it structurally makes more sense to use an <code>&lt;h2&gt;&lt;/h2&gt;</code>, but you want it to look like an <code>&lt;h3&gt;&lt;/h3&gt;</code>, use a class like this: <code>&lt;h2 class="h3"&gt;&lt;/h2&gt;</code>.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"bs":{"theme":""}} -->
<p>This is an example paragraph with some formatted elements. <strong>This is bold text</strong> elit, sed do eiusmod tempor incididunt ut <a href="#">inline link</a> labore et dolore magna aliqua. Ut enim ad minim veniam, <del>strikethrough text</del> laboris nisi ut aliquip ex ea commodo consequat. <u>Underlined text</u> reprehenderit in <mark>marked text</mark> velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat <code>&lt;inline code&gt;</code> non proident, sunt in culpa qui officia <abbr title="abbreviation">this is abbr text</abbr> mollit anim id est laborum. <kbd>ctrl + , (keyboard instructional text)</kbd>.</p>
<!-- /wp:paragraph -->
<!-- /wp:lvl/section-wrapper -->
HTML;

		$rendered = apply_filters('the_content', $wphtml);

		$sinkObj = [
				'post_title'     => 'Kitchen Sink',
				'post_type'      => 'page',
				'post_name'      => 'kitchen-sink',
//				'post_content'   => '<p class="lead">Welcome! This "Kitchen Sink" page is for demonstrating what\'s possible using the custom content blocks in WordPress. This site is built using the <a href="https://getbootstrap.com/docs/5.1/utilities/api/" target="_blank" rel="noopener noreferrer">Bootstrap</a> front-end framework, so if you\'re comfortable with a little code, feel free to make use of their CSS utility classes. For example, this paragraph (<code>&lt;p&gt;&lt;/p&gt;</code>) includes the "lead" class.</p>'
//						. '<h1>This is an example "Heading 1" (H1)</h1><h2>This is an example "Heading 2" (H2)</h2><h3>This is an example "Heading 3" (H3)</h3><h4>This is an example "Heading 4" (H4)</h4><h5>This is an example "Heading 5" (H5)</h5><h6 class="mb-2">This is an example "Heading 6" (H6)</h6>'
//						. '<p>TIP: retain semantic HTML by selecting heading levels based on <em>content structure</em> rather than <em>style</em>. If it structurally makes more sense to use an <code>&lt;h2&gt;&lt;/h2&gt;</code>, but you want it to look like an <code>&lt;h3&gt;&lt;/h3&gt;</code>, use a class like this: <code>&lt;h2 class="h3"&gt;&lt;/h2&gt;</code>.</p>'
//						. '<p>This is an example paragraph with some formatted elements. <strong>This is bold text</strong> elit, sed do eiusmod tempor incididunt ut <a href="#">inline link</a> labore et dolore magna aliqua. Ut enim ad minim veniam, <del>strikethrough text</del> laboris nisi ut aliquip ex ea commodo consequat. <u>Underlined text</u> reprehenderit in <mark>marked text</mark> velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat <code>&lt;inline code&gt;</code> non proident, sunt in culpa qui officia <abbr title="abbreviation">this is abbr text</abbr> mollit anim id est laborum. <kbd>ctrl + , (keyboard instructional text)</kbd>.</p>',
				'post_content'   => $rendered,
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_author'    => 1,
				'menu_order'     => 0,
				'guid'           => $sinkGuid,
		];
		$sinkPageID = wp_insert_post($sinkObj, false);
	} else {
		$sinkPageID = $sinkPage->ID;
	}

	return $sinkPageID;
}

function lvl_theme_create_default_pages(): void
{
	$pages = [
			'home'     => [
					'post_title' => 'Home',
			],
			'resource' => [
					'post_title' => 'Resources',
			],
			'contact'  => [
					'post_title' => 'Contact',
			],
			'about'    => [
					'post_title' => 'About',
			],
	];

	foreach ($pages as $slug => $page) {
		if (get_page_by_path($slug)) {
			continue;
		}

		$page['post_name'] = $slug;
		$page['post_type'] = 'page';
		$page['post_status'] = 'publish';
		$page['comment_status'] = 'closed';
		$page['ping_status'] = 'closed';
		$page['post_author'] = 1;
		$page['menu_order'] = 0;
		$page['guid'] = site_url() . '/' . $slug;

		$pageID = wp_insert_post($page, false);

		if ($slug === 'home') {
			update_option('page_on_front', $pageID);
			update_option('show_on_front', 'page');
		}

		if (in_array($slug, ['blog', 'resource'])) {
			update_option('page_for_posts', $pageID);
		}
	}
}