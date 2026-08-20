<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_filter_team', 'filter_team_ajax');
add_action('wp_ajax_nopriv_filter_team', 'filter_team_ajax');

function filter_team_ajax()
{
    // Verify nonce for security
    if (!check_ajax_referer('team_cards_nonce', 'nonce', false)) {
        wp_send_json_error('Security check failed');
    }

    // Get pagination and display parameters
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 12;
    $image_fit = isset($_POST['image_fit']) ? sanitize_text_field($_POST['image_fit']) : 'cover';
    $featured_id = isset($_POST['featured_id']) ? intval($_POST['featured_id']) : 0;

    // Parse filters from JSON string sent by JavaScript
    $specialty_filters = [];
    $location_filters = [];
    $keyword = '';

    if (isset($_POST['filters'])) {
        $filters = json_decode(stripslashes($_POST['filters']), true);
        if (is_array($filters)) {
            foreach ($filters as $filter) {
                $type = isset($filter['type']) ? sanitize_text_field($filter['type']) : '';
                $value = isset($filter['value']) ? $filter['value'] : '';

                switch ($type) {
                    case 'specialty':
                        $specialty_filters[] = intval($value);
                        break;
                    case 'location':
                        $location_filters[] = intval($value);
                        break;
                    case 'keyword':
                        $keyword = sanitize_text_field($value);
                        break;
                }
            }
        }
    }

    // Also support direct POST parameters for backwards compatibility
    if (empty($specialty_filters) && isset($_POST['specialty'])) {
        $specialty_filters = array_map('intval', (array)$_POST['specialty']);
    }
    if (empty($location_filters) && isset($_POST['location'])) {
        $location_filters = array_map('intval', (array)$_POST['location']);
    }
    if (empty($keyword) && isset($_POST['keyword'])) {
        $keyword = sanitize_text_field($_POST['keyword']);
    }

    $has_filters = !empty($specialty_filters) || !empty($location_filters) || !empty($keyword);

    // Base query args
    $args = array(
        'post_type' => 'team',
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

    // Prepend featured member on page 1 when no filters are active
    $featured_html = '';
    if ($featured_id && !$has_filters && $paged === 1) {
        $featured_post = get_post($featured_id);
        if ($featured_post && $featured_post->post_status === 'publish') {
            $featured_html = render_team_card($featured_id, $image_fit);
            $args['post__not_in'] = [$featured_id];
        }
    }

    $query = new WP_Query($args);

    // Build HTML output
    $html = $featured_html;
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $html .= render_team_card(get_the_ID(), $image_fit);
        }
    }

    wp_reset_postdata();

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

function render_team_card($post_id, $image_fit = 'cover')
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
            foreach ($location_field as $location) {
                if (is_object($location) && isset($location->ID)) {
                    if (get_post_type($location->ID) === 'location') {
                        $title = get_the_title($location->ID);
                        if ($title) $location_names[] = $title;
                    }
                } elseif (is_numeric($location)) {
                    if (get_post_type((int) $location) === 'location') {
                        $title = get_the_title((int) $location);
                        if ($title) $location_names[] = $title;
                    }
                }
            }
        } elseif (is_object($location_field) && isset($location_field->ID)) {
            if (get_post_type($location_field->ID) === 'location') {
                $title = get_the_title($location_field->ID);
                if ($title) $location_names[] = $title;
            }
        } elseif (is_numeric($location_field)) {
            if (get_post_type((int) $location_field) === 'location') {
                $title = get_the_title((int) $location_field);
                if ($title) $location_names[] = $title;
            }
        }
    }

    $location_display = !empty($location_names) ? implode(', ', $location_names) : '';

    ob_start();
?>
    <article class="team-card h-100">
        <div class="team-card-inner">
            <div class="team-card-image">
                <?php if ($featured_image) : ?>
                    <?php if ($post_card_image && is_array($post_card_image)) : ?>
                        <?php echo wp_get_attachment_image($post_card_image['ID'], 'medium_large', false, [
                            'class' => 'team-card-img',
                            'loading' => 'lazy',
                            'alt' => $post_card_image['alt'] ?: get_the_title($post_id),
                            'style' => 'object-fit: ' . esc_attr($image_fit) . ';'
                        ]); ?>
                    <?php else : ?>
                        <?php echo wp_get_attachment_image($featured_image, 'medium_large', false, [
                            'class' => 'team-card-img',
                            'loading' => 'lazy',
                            'style' => 'object-fit: ' . esc_attr($image_fit) . ';'
                        ]); ?>
                    <?php endif; ?>
                <?php else :
                    echo wp_get_attachment_image(get_field('default_card_img', 'options'), 'full', false, ['class' => 'p-5 object-fit-contain']);
                endif; ?>
            </div>

            <div class="team-card-content">
                <h3 class="team-card-title">
                    <?php echo get_the_title($post_id); ?>
                </h3>

                <div class="team-card-meta">
                    <?php if ($specialty_name) : ?>
                        <div class="team-category">
                            <?php echo esc_html($specialty_name); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($location_display) : ?>
                        <div class="team-location">
                            <?php echo esc_html($location_display); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="team-card-hover-content h-100">
                <h3 class="team-card-title">
                    <?php echo get_the_title($post_id); ?>
                </h3>

                <div class="team-card-meta">
                    <?php if ($specialty_name) : ?>
                        <div class="team-category">
                            <?php echo esc_html($specialty_name); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($location_display) : ?>
                        <div class="team-location">
                            <?php echo esc_html($location_display); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($excerpt) : ?>
                    <p class="team-card-excerpt d-none d-lg-block"><?php echo $excerpt_full; ?></p>
                    <p class="team-card-excerpt d-lg-none"><?php echo $excerpt_short; ?></p>
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
