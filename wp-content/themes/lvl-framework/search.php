<?php if( ! defined( 'ABSPATH' ) ) exit;

global $wp_query;
$search_query = get_search_query();
$total = $wp_query->found_posts ?: 0;
$paged = get_query_var( 'paged' ) ?: 1;

get_header(); ?>
	<section class="banner py-4 py-md-5">
		<div class="container">
			<?php if( $search_query ) {
				if( have_posts() ) { ?>
					<h1><?php _e( 'Search results', 'theme' ); ?></h1>
				<?php } else { ?>
					<h1><?php _e( 'No results found', 'theme' ); ?></h1>
				<?php }
			} else { ?>
				<h1><?php _e( 'Search our website:', 'theme' ); ?></h1>
			<?php } ?>
		</div>
	</section>
	<section class="search-inline py-4 py-md-5">
		<div class="container">
			<div class="row">
				<div class="col-lg-8 col-xl-6">
                    <h2><?php _e( 'Search more', 'theme' ); ?></h2>
					<?php get_search_form(); ?>
				</div>
			</div>
		</div>
	</section>
	<?php if( have_posts() ) : ?>
		<hr class="mb-5">
		<section class="search-results">
			<div class="container">
				<div class="row">
					<div class="col-lg-10 col-xl-8">
						<h2 class="h4 mb-3 text-muted"><?php echo $total . ( ( $total === 1 ) ? __( ' result found', 'theme' ) : __( ' results found', 'theme' ) ) . ( $paged > 1 ? ' &bull; page ' . $paged . ' of ' . $wp_query->max_num_pages : '' ); ?></h2>
						<ul class="search-results-list list-unstyled">
							<?php while( have_posts() ) :
								the_post(); ?>
								<li <?php post_class( 'search-result mb-3 pb-3 border-bottom'); ?>>
                                    <small class="text-muted d-block mb-2"><?php echo str_replace( '_', ' ', strtoupper( get_post_type() ) ); ?></small>
									<h3 class="mb-2"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
									<p class="mb-3"><?php echo get_the_excerpt(); ?></p>
									<a href="<?php the_permalink(); ?>" class="d-block mw-100 text-truncate"><?php the_permalink(); ?></a>
								</li>
							<?php endwhile; ?>
						</ul>
					</div>
				</div>
			</div>
		</section>
		<hr>
		<section class="container mb-5 pb-5">
			<nav class="pagination serp-pagination mt-4 mb-5 pb-5">
				<?php the_posts_pagination([
					'prev_text' => '&laquo;',
					'next_text' => '&raquo;'
				]); ?>
			</nav>
		</section>
	<?php endif;
get_footer();