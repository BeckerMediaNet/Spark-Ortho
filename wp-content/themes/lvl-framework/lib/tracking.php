<?php

/**
 * Add GTM dataLayer global variable populated with various properties for use in tags & triggers inside GTM.
 *
 * @access public
 * @return void
 */
add_action( 'wp_head', 'lvl_add_tracking_scripts' );
function lvl_add_tracking_scripts(): void {
    // If no ACF, bail
	if ( ! function_exists( 'get_field' ) ) {
		return;
	}

	// DataLayer & GTM; define container ID on Theme Options page in WordPress:
	$container_id = get_field( 'gtm_container_id', 'options' );
	if ( $container_id ) :
		global $post;
		$data_layer = [];
		if ( ! is_null( $post ) ) {
			// Setting Content Type Value
			$post_type_obj                                   = get_post_type_object( $post->post_type );
			$content_type                                    = $post_type_obj->labels->name;
			$data_layer['content_groupings']['content_type'] = $content_type;
		} else {
			if ( is_404() ) {
				$data_layer['content_groupings']['content_type'] = '404 Errors';
			}
		}
		$data_layer_json = json_encode( $data_layer );
		?>
        <script>
            // GTM DataLayer Definition
            dataLayer = [<?php echo $data_layer_json ?>];
        </script>
        <!-- Google Tag Manager -->
        <script>(function (w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({
                    'gtm.start':
                        new Date().getTime(), event: 'gtm.js'
                });
                var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
                j.async = true;
                j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', '<?php echo $container_id; ?>');</script>
        <!-- End Google Tag Manager --><?php
	endif;
}