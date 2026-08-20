<?php if (!defined('ABSPATH')) {
    exit;
}

add_filter('rest_dispatch_request', 'block_users_endpoint', 10, 4);
function block_users_endpoint($dispatch_result, $request, $route, $handler)
{
    if ($route === '/wp/v2/users') {
        if (!is_admin() && !is_user_logged_in()) {
            return new WP_Error('rest_forbidden', __('Access denied.'), array('status' => 403));
        }
    }

    return $dispatch_result;
}