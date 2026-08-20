<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_filter_dentists', 'filter_dentists_ajax');
add_action('wp_ajax_nopriv_filter_dentists', 'filter_dentists_ajax');

function filter_dentists_ajax()
{
    // Verify nonce for security
    if (!check_ajax_referer('dentist_cards_nonce', 'nonce', false)) {
        wp_send_json_error('Security check failed');
    }

    // Get pagination and display parameters
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 12;
    $image_fit = isset($_POST['image_fit']) ? sanitize_text_field($_POST['image_fit']) : 'cover';

    // Get filter parameters
    $specialty_filters = isset($_POST['specialty']) ? (array)$_POST['specialty'] : [];
    $location_filters = isset($_POST['location']) ? (array)$_POST['location'] : [];
    $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';

    // Base query args
    $args = array(
        'post_type' => 'dentist',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'paged' => $paged,
        'orderby' => 'title',
        'order' => 'ASC'
    );

    $tax_queries = [];
    $meta_queries = [];

    // Add specialty filters
    if (!empty($specialty_filters)) {
        $tax_queries[] = array(
            'taxonomy' => 'specialty',
            'field'    => 'term_id',
            'terms'    => array_map('intval', $specialty_filters),
            'operator' => 'IN'
        );
    }

    // Add location filters (ACF post_object field)
    if (!empty($location_filters)) {
        $location_meta_queries = [];
        foreach ($location_filters as $location_id) {
            $location_id = intval($location_id);
            // Search for the location ID in the serialized ACF array
            $location_meta_queries[] = array(
                'key'     => 'location',
                'value'   => '"' . $location_id . '"',
                'compare' => 'LIKE'
            );
        }

        if (count($location_meta_queries) > 1) {
            $location_meta_queries['relation'] = 'OR';
            $meta_queries[] = $location_meta_queries;
        } else {
            $meta_queries[] = $location_meta_queries[0];
        }
    }

    // Apply taxonomy queries
    if (!empty($tax_queries)) {
        if (count($tax_queries) > 1) {
            $tax_queries['relation'] = 'AND';
        }
        $args['tax_query'] = $tax_queries;
    }

    // Apply meta queries
    if (!empty($meta_queries)) {
        if (count($meta_queries) > 1) {
            $meta_queries['relation'] = 'AND';
        }
        $args['meta_query'] = $meta_queries;
    }

    // Add keyword search if provided
    if (!empty($keyword)) {
        $args['s'] = $keyword;
    }

    $query = new WP_Query($args);

    // Build HTML output
    $html = '';
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $html .= render_dentist_card(get_the_ID(), $image_fit);
        }
    }

    wp_reset_postdata();

    // Match testimonials format exactly with pagination support
    wp_send_json([
        'html' => $html,
        'has_more' => $query->max_num_pages > $paged,
        'next_page' => $paged + 1,
        'current_page' => $paged,
        'max_pages' => $query->max_num_pages,
        'total_posts' => $query->found_posts,
        'filters_applied' => [
            'keyword' => $keyword,
            'specialty' => $specialty_filters,
            'location' => $location_filters,
            'has_keyword' => !empty($keyword),
            'has_filters' => !empty($specialty_filters) || !empty($location_filters) || !empty($keyword)
        ]
    ]);
}

function render_dentist_card($post_id, $image_fit = 'cover')
{
    $post_card_image = null;
    $featured_image = get_post_thumbnail_id($post_id);

    // Use post_card_image field if available
    $post_card_image_field = get_field('post_card_image', $post_id);
    if ($post_card_image_field && is_array($post_card_image_field) && !empty($post_card_image_field['ID'])) {
        $post_card_image = $post_card_image_field;
        $featured_image = $post_card_image['ID'];
    }

    $excerpt = get_the_excerpt($post_id);

    // Responsive excerpt length
    $excerpt_short = wp_trim_words($excerpt, 30, '...');
    $excerpt_full = wp_trim_words($excerpt, 50, '...');

    $permalink = get_permalink($post_id);

    // Get specialty
    $specialty_terms = get_the_terms($post_id, 'specialty');
    $specialty_name = '';
    if (!is_wp_error($specialty_terms) && !empty($specialty_terms)) {
        $specialty_name = $specialty_terms[0]->name;
    }

    // Get location information - handle multiple locations
    $location_names = [];
    $location_field = get_field('location', $post_id);

    if ($location_field) {
        if (is_array($location_field) && !empty($location_field)) {
            // Multiple locations
            foreach ($location_field as $location) {
                if (is_object($location) && isset($location->ID)) {
                    $location_names[] = get_the_title($location->ID);
                } elseif (is_numeric($location)) {
                    $location_names[] = get_the_title($location);
                }
            }
        } elseif (is_object($location_field) && isset($location_field->ID)) {
            // Single location object
            $location_names[] = get_the_title($location_field->ID);
        } elseif (is_numeric($location_field)) {
            // Single location ID
            $location_names[] = get_the_title($location_field);
        }
    }

    $location_display = !empty($location_names) ? implode(', ', $location_names) : '';

    ob_start();
?>
    <article class="dentist-card h-100">
        <div class="dentist-card-inner">
            <div class="dentist-card-image">
                <?php if ($featured_image) : ?>
                    <?php if ($post_card_image && is_array($post_card_image)) : ?>
                        <?php echo wp_get_attachment_image($post_card_image['ID'], 'medium_large', false, [
                            'class' => 'dentist-card-img',
                            'loading' => 'lazy',
                            'alt' => $post_card_image['alt'] ?: get_the_title($post_id),
                            'style' => 'object-fit: ' . esc_attr($image_fit) . ';'
                        ]); ?>
                    <?php else : ?>
                        <?php echo wp_get_attachment_image($featured_image, 'medium_large', false, [
                            'class' => 'dentist-card-img',
                            'loading' => 'lazy',
                            'style' => 'object-fit: ' . esc_attr($image_fit) . ';'
                        ]); ?>
                    <?php endif; ?>
                <?php else : ?>
                    <div class="dentist-card-placeholder">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z" />
                        </svg>
                    </div>
                <?php endif; ?>
            </div>

            <div class="dentist-card-content">
                <h3 class="dentist-card-title">
                    <?php echo get_the_title($post_id); ?>
                </h3>

                <div class="dentist-card-meta">
                    <?php if ($specialty_name) : ?>
                        <div class="dentist-specialty">
                            <?php echo esc_html($specialty_name); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($location_display) : ?>
                        <div class="dentist-location">
                            <?php echo esc_html($location_display); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dentist-card-hover-content h-100">
                <h3 class="dentist-card-title">
                    <?php echo get_the_title($post_id); ?>
                </h3>

                <div class="dentist-card-meta">
                    <?php if ($specialty_name) : ?>
                        <div class="dentist-specialty">
                            <?php echo esc_html($specialty_name); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($location_display) : ?>
                        <div class="dentist-location">
                            <?php echo esc_html($location_display); ?>
                        </div>
                    <?php endif; ?>
                </div>

	            <?php if ($excerpt) : ?>
					<p class="dentist-card-excerpt d-none d-lg-block"><?php echo $excerpt_full; ?></p>
			        <p class="dentist-card-excerpt d-lg-none"><?php echo $excerpt_short; ?></p>
				<?php endif; ?>

                <a href="<?php echo esc_url($permalink); ?>" class="btn btn-link-secondary">
                    View More
                </a>
            </div>
        </div>
    </article>
<?php
    return ob_get_clean();
}
