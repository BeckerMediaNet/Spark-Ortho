<?php

//include_once 'helpers/resources.php';

/**
 * @param string $file_path
 *
 * @return string
 */
function lvl_cache_bust(string $file_path, $timestamp = false): string
{
    // TODO: may need further options to be able to force cache overriding in dev environments
    if ($timestamp) {
        return $file_path . '?v=' . $timestamp;
    }

    if (!file_exists(realpath(__DIR__ . '/..') . $file_path)) {
        return $file_path . '?v=' . date('Ymd');
    }

    return $file_path . '?v=' . filemtime(realpath(__DIR__ . '/..') . $file_path);
}


// Place WP Menu items in a loopable array:
/**
 * @param array $elements
 * @param int   $parentId
 *
 * @return array
 */
function lvl_build_menu(array &$elements, int $parentId = 0): array
{
    $branch = [];
    foreach ($elements as &$element) {
        if ($element->menu_item_parent == $parentId) {
            $children = lvl_build_menu($elements, $element->ID);
            if ($children) {
                $element->lvl_children = $children;
            }
            // $branch[$element->ID] = $element;
            $branch[] = $element;
            unset($element);
        }
    }

    return $branch;
}


// Place WP Menu items in a loopable array:
/**
 * @param $menu_id
 *
 * @return array|null
 */
function lvl_nav_menu_tree($menu_id): ?array
{
    $items = wp_get_nav_menu_items($menu_id);

    return $items ? lvl_build_menu($items, 0) : null;
}


// Catch first blog post image or Featured Image if it exists:
/**
 * @param int $postId
 *
 * @return string
 */
function lvl_catch_that_image(int $postId): string
{
    if (!$postId) {
        $postId = get_the_ID();
    }
    if ($thisPost = get_post($postId)) {
        if (has_post_thumbnail($postId)) {
            return get_the_post_thumbnail_url($postId, 'post-thumbnail');
        }

        preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/', $thisPost->post_content, $matches);
        if (!empty($matches)) {
            $first_img = $matches[1][0];
            if (!empty($first_img)) {
                return $first_img;
            }
        }
    }

    return '';
}

// Custom Excerpt - return first sentence only
/**
 * @param $id
 *
 * @return string
 */
function lvl_custom_excerpt($id): string
{
    $excerpt = get_the_excerpt($id);
	$excerpt = preg_replace('/\[.*?\]/', '', $excerpt);

    return preg_replace('/(.*?[?!.](?=\s|$)).*/', '\\1', strip_shortcodes($excerpt));
}

// Calc luminosity difference between two RGB colors
/**
 * @param $hex
 *
 * @return string
 */
function lumdiff($hex): string
{

    if (!$hex) {
        return 'section-light';
    }

    // Convert $hex to RGB values
    list($R1, $G1, $B1) = sscanf($hex, "#%02x%02x%02x");
    list($R2, $G2, $B2) = sscanf('#000000', "#%02x%02x%02x"); // #495057 is the body font color


    $L1 = 0.2126 * pow($R1 / 255, 2.2) +
        0.7152 * pow($G1 / 255, 2.2) +
        0.0722 * pow($B1 / 255, 2.2);

    $L2 = 0.2126 * pow($R2 / 255, 2.2) +
        0.7152 * pow($G2 / 255, 2.2) +
        0.0722 * pow($B2 / 255, 2.2);

    if ($L1 > $L2) {
        $val = ($L1 + 0.05) / ($L2 + 0.05);
    } else {
        $val = ($L2 + 0.05) / ($L1 + 0.05);
    }

    // echo $val;

    if ($val >= 5) {
        return 'text-dark';
    } else {
        return 'text-light';
    }
}


/**
 * @param $string
 *
 * @return string
 */
function lvl_rgba2hex($string): string
{
    $rgba = array();
    $hex = '';
    $regex = '#\((([^()]+|(?R))*)\)#';
    if (preg_match_all($regex, $string, $matches)) {
        $rgba = explode(',', implode(' ', $matches[1]));
    } else {
        $rgba = explode(',', $string);
    }

    $rr = dechex($rgba['0']);
    $gg = dechex($rgba['1']);
    $bb = dechex($rgba['2']);
    $aa = '';

    if ($rr == '0') {
        $rr = '00';
    }
    if ($gg == '0') {
        $gg = '00';
    }
    if ($bb == '0') {
        $bb = '00';
    }

    if (array_key_exists('3', $rgba)) {
        $aa = dechex($rgba['3'] * 255);
    }

    // return strtoupper("#$aa$rr$gg$bb");
    return strtoupper("#$rr$gg$bb");
}

/**
 * @param $string
 *
 * @return string
 */
function lvl_hex2rgb($hex) : string
{
	list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
	return "$r, $g, $b";
}

/**
 * @param int $limit
 * @param int $days
 *
 * @return array|object|null
 */
function get_popular_recent_tags(int $limit = 10, int $days = 30): array|object|null
{
    global $wpdb;

    $query = "
        SELECT t.*, COUNT(p.ID) as tag_count
        FROM {$wpdb->term_relationships} tr
        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
        INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
        WHERE tt.taxonomy = 'post_tag'
        AND p.post_type = 'post'
        AND p.post_status = 'publish'
        AND p.post_date > DATE_SUB(NOW(), INTERVAL %d DAY)
        GROUP BY t.term_id
        ORDER BY tag_count DESC, t.name ASC
        LIMIT %d
    ";

    return $wpdb->get_results($wpdb->prepare($query, $days, $limit));
}

/**
 * @param string $form_id
 *
 * @return void
 */
function the_hubspot_embed(string $form_id): void
{

    // TODO: add to theme options
    $access_token = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
    $url = 'https://api.hubapi.com/forms/v2/forms/' . $form_id;

    $headers = array(
        'Authorization: Bearer ' . $access_token,
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $form_data = json_decode($response, true);
    } else {
        echo 'Error: ' . $http_code;
        return;
    }


    $portal_id = ($form_data['portalId'] ?? '');

    ob_start(); ?>
    <!-- Start of HubSpot Embed Code -->
    <script type="text/javascript" id="hs-script-loader" src="//js.hsforms.net/forms/v2.js"></script>
    <script type="text/javascript">
        hbspt.forms.create({
            portalId: "<?php echo $portal_id; ?>",
            formId: "<?php echo $form_id; ?>"
        });
    </script>
    <!-- End of HubSpot Embed Code -->

    <?php

    $embed_code = ob_get_clean();

    echo $embed_code;
}

//Capability link
function get_capability_link($capability)
{
    switch ($capability) {
        case 'Application Development':
            $link = '/what-we-do/application-development/';
            break;
        case 'Cloud Services':
            $link = '/what-we-do/cloud-services/';
            break;
        case 'CRM & Automation':
        case 'CRM &amp; Automation':
            $link = '/what-we-do/crm-automation/';
            break;
        case 'Data & Analytics':
        case 'Data &amp; Analytics':
            $link = '/what-we-do/data-analytics/';
            break;
        case 'DevOps':
            $link = '/what-we-do/devops/';
            break;
        case 'Emerging Technologies':
            $link = '/what-we-do/emerging-technologies/';
            break;
        case 'Managed Services':
            $link = '/what-we-do/managed-services/';
            break;
        case 'Productivity & Collaboration':
        case 'Productivity &amp; Collaboration':
            $link = '/what-we-do/productivity-collaboration/';
            break;
        case 'User Experience':
            $link = '/what-we-do/user-experience/';
            break;
        default:
            $link = '/what-we-do/';
            break;
    }
    return $link;
}

//Pagination for arcghive pages
function lvl_bs_pagination($query = null, $paged = null)
{
    if (!$query) {
        global $wp_query;
        $query = $wp_query;
    }
    if (!$paged) {
        $paged = get_query_var('page') ? get_query_var('page') : (get_query_var('paged') ? get_query_var('paged') : 1);
    }
    $pagination = paginate_links(array(
        'base'    => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
        'format'  => '?paged=%#%',
        'current' => max(1, $paged),
        'total'   => $query->max_num_pages,
        'type'    => 'list',
    ));
    $pagination = str_replace('<ul class=\'page-numbers\'', '<ul class="pagination justify-content-end"', $pagination);
    $pagination = preg_replace('/<li><span aria-current=\'page\' class=\'page-numbers current\'>(.+)</', '<li class="page-item active"><span class="page-link">$1 <span class="sr-only">(current)</span><', $pagination);
    $pagination = preg_replace('/<li><span class="page-numbers dots">(.+)</', '<li class="page-item disabled"><span class="page-link">$1<', $pagination);
    $pagination = str_replace('<li>', '<li class="page-item">', $pagination);
    return '<label class="d-block float-left pt-1 text-muted">' . __('Page ' . $paged . ' of ' . $query->max_num_pages) . '</label>' . str_replace('page-numbers', 'page-link', $pagination);
}


