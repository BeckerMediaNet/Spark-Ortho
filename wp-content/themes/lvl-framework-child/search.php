<?php if (!defined('ABSPATH')) exit;

global $wp_query;
$search_query = get_search_query();
$total = $wp_query->found_posts ?: 0;
$paged = get_query_var('paged') ?: 1;

get_header(); ?>
<section class="banner mt-6 py-4">
	<div class="container">
		<?php if ($search_query) {
			if (have_posts()) { ?>
				<h1><?php _e('Search results'); ?></h1>
			<?php } else { ?>
				<h1><?php _e('No results found'); ?></h1>
			<?php }
		} else { ?>
			<h1><?php _e('Search our website:'); ?></h1>
		<?php } ?>
	</div>
</section>

<section class="search-inline py-4">
	<div class="container">
		<div class="row">
			<div class="col-lg-8 col-xl-6">
				<?php get_search_form(); ?>
			</div>
		</div>
	</div>
</section>

<?php if (have_posts()) : ?>

	<section class="search-results">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<h2 class="h6 my-5 text-muted"><?php echo $total . (($total === 1) ? __(' result found') : __(' results found')) . ($paged > 1 ? ' &bull; page ' . $paged . ' of ' . $wp_query->max_num_pages : ''); ?></h2>
					<ul class="search-results-list list-unstyled">
						<?php while (have_posts()) :
							the_post(); ?>
							<li <?php post_class('search-result mb-3 pb-3 border-bottom'); ?>>
								<small class="text-muted"><?php echo str_replace('_', ' ', strtoupper(get_post_type())); ?></small>
								<h3 class="h5 mt-0"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<p class="mb-0"><?php echo get_the_excerpt(); ?></p>
								<a href="<?php the_permalink(); ?>" class="d-block mw-100 text-truncate mt-3"><?php the_permalink(); ?></a>
							</li>
						<?php endwhile; ?>
					</ul>
				</div>
			</div>
		</div>
	</section>
	
	<section class="container mb-5 pb-5">
		<nav class="pagination serp-pagination mt-4 mb-5 pb-5">
			<?php the_posts_pagination([
				'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M10 13L4 7L10 1" stroke="currentColor" stroke-width="2"/></svg>',
				'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M4 1L10 7L4 13" stroke="currentColor" stroke-width="2"/></svg>'
			]); ?>
		</nav>
	</section>
<?php endif;
get_footer();
