<?php 

$phone = get_field('phone_number')?? false;

add_action( 'wp_footer', function() use($phone) { 
	
	if($phone) : ?>

		<script>
			document.addEventListener('DOMContentLoaded', function() {
				let phone = '<?php echo $phone; ?>';
				let cleaned = phone.replace(/\D/g, '');
				let match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
				let pattern = /\d{3}-\d{3}-\d{4}/;

				let links = document.querySelectorAll('a[href^="tel:"]');
				links.forEach(link => {
					link.href = 'tel:' + cleaned;
					link.innerHTML = link.innerHTML.replace(/\d{3}-\d{3}-\d{4}/, '(' + match[1] + ')' + '-' + match[2] + '-' + match[3]);
				});
			});
		</script>

<?php endif;

}, 1);

add_filter('wpseo_robots', function ($robots) {
	return 'noindex, nofollow';
});

get_header();

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();

        the_content();
	}
}



get_footer();