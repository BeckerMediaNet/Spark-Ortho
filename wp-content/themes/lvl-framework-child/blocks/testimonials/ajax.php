<?php if (! defined('ABSPATH')) exit;

// Load More Testimonials
add_action('wp_ajax_load_more_testimonials', 'load_more_testimonials');
add_action('wp_ajax_nopriv_load_more_testimonials', 'load_more_testimonials');
function load_more_testimonials()
{
    $page = intval($_POST['page']) ?: 1;
    $posts_per_page = 9;

    $args = [
        'post_type' => 'testimonial',
        'post_status' => 'publish',
        'posts_per_page' => $posts_per_page,
        'paged' => $page,
        'orderby' => 'date',
        'order' => 'DESC'
    ];

    $testimonials_query = new WP_Query($args);
    $testimonials = $testimonials_query->posts;

    ob_start();

    if ($testimonials) {
        foreach ($testimonials as $index => $testimonial) {
            $quote_content = get_field('quote_content', $testimonial->ID);
            $quote_author = get_field('quote_author', $testimonial->ID);
            $quote_author_title = get_field('quote_author_title', $testimonial->ID);

            $author = [];
            $author['name'] = $quote_author;
            $author['title'] = $quote_author_title;
            $author = array_filter($author);

            // Calculate position for alternating colors
            $global_index = (($page - 1) * $posts_per_page) + $index;
            $row = floor($global_index / 3);
            $col = $global_index % 3;

            // Alternating pattern for BG color
            $color_index = ($col + $row) % 3;
            $color_class = '';
            switch ($color_index) {
                case 0:
                    $color_class = 'bg-orange';
                    break;
                case 1:
                    $color_class = 'bg-primary';
                    break;
                case 2:
                    $color_class = 'bg-lime-green';
                    break;
            }
?>

            <div class="testimonial--static <?php echo $color_class; ?>" data-bs-theme="light">
                <figure class="mb-0 p-3 p-lg-4">
                    <div class="stars d-flex justify-content-center align-items-center mb-4">
                        <svg fill="none" height="20" viewBox="0 0 119 20" width="119" xmlns="http://www.w3.org/2000/svg">
                            <g fill="#fff">
                                <path d="m15.1967 12.5066 5.8033-4.74809-7.5984-.49897-2.9016-6.75954-2.90164 6.75954-7.59836.49897 5.81148 4.74809-1.80328 6.9934 6.4918-3.7501 6.4918 3.7501z" />
                                <path d="m39.4731 12.5066 5.5269-4.74809-7.2365-.49897-2.7635-6.75954-2.7635 6.75954-7.2365.49897 5.5347 4.74809-1.7174 6.9934 6.1827-3.7501 6.1827 3.7501z" />
                                <path d="m64.1967 12.5066 5.8033-4.74809-7.5984-.49897-2.9016-6.75954-2.9016 6.75954-7.5984.49897 5.8115 4.74809-1.8033 6.9934 6.4918-3.7501 6.4918 3.7501z" />
                                <path d="m88.4731 12.5066 5.5269-4.74809-7.2365-.49897-2.7635-6.75954-2.7635 6.75954-7.2365.49897 5.5347 4.74809-1.7174 6.9934 6.1827-3.7501 6.1827 3.7501z" />
                                <path d="m113.197 12.5066 5.803-4.74809-7.598-.49897-2.902-6.75954-2.902 6.75954-7.598.49897 5.811 4.74809-1.803 6.9934 6.492-3.7501 6.492 3.7501z" />
                            </g>
                        </svg>
                    </div>
                    <blockquote>
                        <p><?php echo $quote_content; ?></p>
                    </blockquote>
                    <?php if (!empty($author)): ?>
                        <figcaption class="mt-auto mt-3">
                            <?php if (!empty($author['name'])) :
                                echo '<span class="author d-block">' . $author['name'] . '</span>';
                            endif;

                            if (!empty($author['title'])) :
                                echo '<span class="title d-block">' . $author['title'] . '</span>';
                            endif; ?>
                        </figcaption>
                    <?php endif; ?>
                </figure>
            </div>

<?php
        }
    }

    $html = ob_get_clean();

    wp_send_json([
        'html' => $html,
        'has_more' => $page < $testimonials_query->max_num_pages,
        'current_page' => $page,
        'max_pages' => $testimonials_query->max_num_pages
    ]);
}
