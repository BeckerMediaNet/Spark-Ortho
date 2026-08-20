<?php

/**
 * Archive template for the Team (Doctors) post type.
 *
 * The parent theme's generic archive.php falls back to a plain post list unless
 * a "{post_type}_archive_page" option is set under Settings -> Reading, and that
 * option was unset/pointing nowhere for "team". Rather than depend on that, this
 * dedicated template (same approach as archive-location.php) hardcodes the hero
 * copy (editable via Team > Archive Settings) and renders the team-cards block
 * directly, matching how it already worked before.
 */

get_header();

$heading     = get_field('team_archive_heading', 'option');
$description = get_field('team_archive_description', 'option');

if (empty($heading)) {
	$heading = 'Meet Our Orthodontists';
}

if (empty($description)) {
	$description = 'Our doctors complete additional years of training specifically to work with orthodontic patients. Meet our Spark Orthodontists across our <a href="/locations/">nine convenient locations</a> before you come in for your appointment. We can&#8217;t wait to see you!';
}

$team_cards_block = '<!-- wp:lvl/team-cards {"name":"lvl/team-cards","data":{"display_mode":"all","_display_mode":"field_team_display_mode","show_filters":"1","_show_filters":"field_team_show_filters","taxonomy_filters":["specialty"],"_taxonomy_filters":"field_team_taxonomy_filters","per_page":"12","_per_page":"field_team_per_page","image_fit":"cover","_image_fit":"field_image_fit","enable_featured_member":"1","_enable_featured_member":"field_team_enable_featured_member","featured_team_member":"5489","_featured_team_member":"field_team_featured_member","id":"archiveteamcards01"},"mode":"preview","align":"full"} /-->';

$team_cards_block = parse_blocks($team_cards_block);

?>

<section class="pt-5 pb-2">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-12">
				<h1 class="text-center archive-title"><?php echo $heading; ?></h1>
				<p class="text-center"><?php echo $description; ?></p>
			</div>
		</div>
	</div>
</section>

<div class="container pt-0 pt-md-2 pt-lg-3 pb-6">
	<?php
	foreach ($team_cards_block as $block) {
		echo render_block($block);
	}
	?>
</div>

<?php get_footer(); ?>
