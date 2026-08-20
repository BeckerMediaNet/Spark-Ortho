<?php

if (get_field('scroll_to_top', 'options')) { ?>

	<div class="scroll-to-top position-fixed z-999" style="bottom: 1rem; right: 1rem;">
		<a href="#top" class="btn btn-secondary btn-scroll p-2" aria-label="Scroll to top">
			<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor" role="img" focusable="false">
				<path d="M480-528 296-344l-56-56 240-240 240 240-56 56-184-184Z" />
			</svg>
			<span class="visually-hidden">Scroll to top</span>
		</a>
	</div>

<?php } ?>

</main>

<footer id="footer">

	<?php if (is_active_sidebar('lvl-footer')) dynamic_sidebar('lvl-footer'); ?>

</footer>

<div id="searchModal" class="modal fade" tabindex="-1" role="dialog" aria-label="<?php _e('Search Form', 'wmx'); ?>" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<button type="button" class="btn-close btn-close-white ms-auto mb-2" data-bs-dismiss="modal" aria-label="<?php _e('Close', 'wmx'); ?>"></button>
			<?php get_search_form(); ?>
		</div>
	</div>
</div>

<?php wp_footer(); ?>

</body>

</html>