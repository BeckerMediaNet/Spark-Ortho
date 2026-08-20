<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//admin ajax
add_action( 'wp_ajax_nopriv_search_quick_links', 'search_quick_links' );
add_action( 'wp_ajax_search_quick_links', 'search_quick_links' );

function search_quick_links() {
	$quick_links = get_field( 'search_quick_links', 'options' );
	if ( $quick_links ) {
		ob_start();
		?>
        <div class="quick-links p-3 border border-primary bg-white" data-bs-theme="light">
            <h3>Quick Links</h3>
            <ul>
				<?php foreach ( $quick_links as $quick_link ) {
                    $link = $quick_link['link'];
                    if($link) {
	                    $link_url = $link['url'];
	                    $link_title = $link['title'];
	                    $link_target = $link['target'] ?: '_self';
                        ?>
                        <li>
                            <a href="<?php echo $link_url; ?>" target="<?php echo $link_target; ?>"><?php echo $link_title; ?></a>
                        </li>
                        <?php
                    }
                    ?>
				<?php } ?>
            </ul>
        </div>
		<?php

		$quick_links = ob_get_clean();

		echo json_encode( array( 'success' => true, 'quick_links' => $quick_links ) );
		die();
	}
	echo json_encode( array( 'success' => false ) );
	die();
}