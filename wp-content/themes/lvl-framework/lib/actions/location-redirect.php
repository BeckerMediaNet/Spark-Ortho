<?php
if (!defined('ABSPATH')) {
    exit;
}

// if plugin is enabled then add the network admin options page
if (is_plugin_active('wpengine-geoip/class-geoip.php') && is_multisite()) {

    add_action('network_admin_menu', 'lvl_location_redirect_network_admin_menu');
    add_action('network_admin_edit_lvl_set_geo_redirects', 'lvl_set_geo_redirects');
    add_action('template_redirect', 'lvl_location_redirect');

    $geoip_country_code = getenv('HTTP_GEOIP_COUNTRY_CODE');
    $geoip_continent = getenv('HTTP_GEOIP_CITY_CONTINENT_CODE');

    add_filter('body_class', function ($classes) use ($geoip_country_code, $geoip_continent) {
        if ($geoip_country_code) {
            $classes[] = 'geoip-country-' . strtolower($geoip_country_code);
        }
        if ($geoip_continent) {
            $classes[] = 'geoip-continent-' . strtolower($geoip_continent);
        }
        return $classes;
    });
}


// add network admin options page for entering geo location url redirect pairs in CSV format into a textarea
function lvl_location_redirect_network_admin_menu(): void
{
    add_menu_page('GEO Redirect', 'GEO Redirect', 'manage_options', 'location-redirect', 'lvl_location_redirect_network_admin_page', 'dashicons-location-alt', 55);
}

function lvl_location_redirect_network_admin_page(): void
{
    $location_redirect_rules = get_site_option('lvl_geo_redirects');
    $location_redirect_rules = json_decode($location_redirect_rules) ?: '';

    $cookie_expire = get_site_option('lvl_geo_redirects_cookie_expire', 11);
    $enabled = get_site_option('lvl_geo_redirects_enabled');
    $enabled = $enabled ? 'checked' : '';

    // is updated
    if (isset($_GET['updated'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Location Redirect Rules Updated</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>GEO Redirect</h1>
        <h2>Redirect users based on their location.</h2>
        <p>Users will be redirected if they are coming in from an external URL and have not yet been redirected yet.</p>
        <form method="post" action="edit.php?action=lvl_set_geo_redirects">
            <?php settings_fields('lvl_geo_redirects'); ?>
            <?php do_settings_sections('lvl_geo_redirects'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        Location Redirect Rules
                        <br/><br/>
                        <small>Country and Continent codes use <a href="http://www.geonames.org/countries/" target="_blank">http://www.geonames.org/countries/</a></small>
                    </th>
                    <td>
                        <p>Enter location code and url pairs in CSV format. <strong>One pair per line.</strong><br/>Example: UK,https://example.com/uk</p>
                        <textarea name="lvl_geo_redirects" placeholder="UK,https://example.com/uk" rows="10" cols="50"><?php echo($location_redirect_rules ?: ''); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th>Cookie Expiration</th>
                    <td>
                        <input type="number" min="0" name="lvl_geo_redirects_cookie_expire" value="<?php echo($cookie_expire !== false ? $cookie_expire : 10); ?>"> seconds (set to 0 to expire at end of session)
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Enable Location Redirect</th>
                    <td>
                        <input type="checkbox" name="lvl_geo_redirects_enabled" <?php echo $enabled; ?>>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function lvl_set_geo_redirects(): void
{
    if (isset($_POST['lvl_geo_redirects'])) {
        $location_redirect_rules = ($_POST['lvl_geo_redirects']);
        $enabled = isset($_POST['lvl_geo_redirects_enabled']) ? true : false;

        if (!get_site_option('lvl_geo_redirects')) {
            add_site_option('lvl_geo_redirects', '');
        }

        if(!get_site_option('lvl_geo_redirects_cookie_expire')) {
            add_site_option('lvl_geo_redirects_cookie_expire', 11);
        }

        update_site_option('lvl_geo_redirects', json_encode($location_redirect_rules));
        update_site_option('lvl_geo_redirects_cookie_expire', $_POST['lvl_geo_redirects_cookie_expire']?? 11);
        update_site_option('lvl_geo_redirects_enabled', $enabled);
    }
    wp_redirect(add_query_arg(['page' => 'location-redirect', 'updated' => 'true'], network_admin_url('admin.php')));
    exit;
}


/**
 * Redirect users based on their location.
 * @return void
 * @see http://www.geonames.org/countries/
 */
function lvl_location_redirect(): void
{
//    if (!is_user_logged_in() || is_admin())
//        return;
    if(is_user_logged_in() || is_admin())
        return;

    $enabled = get_site_option('lvl_geo_redirects_enabled');
    if (!$enabled)
        return;

    // if visitor is human then continue
    if (isset($_SERVER['HTTP_USER_AGENT']) && str_contains($_SERVER['HTTP_USER_AGENT'], 'bot'))
        return;


    // if referrer is same domain then return
    if (isset($_SERVER['HTTP_REFERER']) && str_contains($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) {
        return;
    }

    // if user has redirected cookie then return
    if (isset($_COOKIE['wordpress_geo_redirected']) && $_COOKIE['wordpress_geo_redirected'] == 2) {
        return;
    }

    $location_redirect_rules = get_site_option('lvl_geo_redirects');
    if (!$location_redirect_rules) {
        return;
    }

    $location_redirect_rules = json_decode($location_redirect_rules, true);

    // convert location_redirect_rules to array
    $location_redirect_rules = explode("\n", $location_redirect_rules);
    if (empty($location_redirect_rules)) {
        return;
    }

    $location_redirect_rules = array_map(function ($rule) {
        $rule = explode(',', $rule);
        return [
            'location' => trim($rule[0]) ?? false,
            'url'      => rtrim(trim($rule[1]), "/") ?? false,
        ];
    }, $location_redirect_rules);


    $geoip_country_code = getenv('HTTP_GEOIP_CITY_CONTINENT_CODE');
    $geoip_continent = getenv('HTTP_GEOIP_COUNTRY_CODE');
    $cookie_expire_seconds = get_site_option('lvl_geo_redirects_cookie_expire', 11) ?? 10;
    $cookie_expire = time() + $cookie_expire_seconds;//((60 * 60 * 24) * $cookie_expire_seconds); // (seconds * minutes * hours) * days

    if (!$geoip_country_code && !$geoip_continent) {
        return;
    }

    foreach ($location_redirect_rules as $rule) {
        if (!$rule['location'] || !$rule['url'])
            continue;

        if (strtoupper($rule['location']) == $geoip_country_code || strtoupper($rule['location']) == $geoip_continent) {
            if (get_site_url() == $rule['url'])
                continue;

            setcookie('wordpress_geo_redirected', 2, $cookie_expire, '/', $_SERVER['HTTP_HOST'], false, true);

            $current_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $redirect_url = str_replace(get_site_url(), $rule['url'], $current_url);

            $headers = get_headers($redirect_url); // won't work on basic auth sites
            if (!str_contains($headers[0], '200')) {
                $redirect_url = $rule['url'];
            }

            // insert cookie setting script into page before redirect
            add_action('wp_head', function () use ($cookie_expire_seconds, $redirect_url) {
                echo '<script>
                        (function () {
                            const isCookieEnabled = navigator.cookieEnabled;
                            if (!isCookieEnabled) {
                                console.warn("Cookies are disabled. Geo Redirect disabled.");
                                return;
                            }
                            
                            // GEO Redirect Cookie
                            if (document.cookie.indexOf("wordpress_geo_redirected") >= 0) {
                                console.log("Already redirected");
                                return;
                            }
                            
                            if(' . $cookie_expire_seconds . ' === 0){
                                document.cookie = "wordpress_geo_redirected=2; path=/; domain=' . $_SERVER['HTTP_HOST'] . '; secure; samesite=strict;";
                            } else {
                                document.cookie = "wordpress_geo_redirected=2; max-age=' . $cookie_expire_seconds . '; path=/; domain=' . $_SERVER['HTTP_HOST'] . '; secure; samesite=strict;";
                            }

                            if(window.location.href === "' . $redirect_url . '") {
                                console.log("Already on redirect url");
                                return;
                            }
                            
                            console.log("Redirecting to: ' . $redirect_url . '");
                            
                            window.location.href = "' . $redirect_url . '";
                        })();
                      </script>';
            }, -99);

//            wp_safe_redirect($redirect_url, 302);
//            return;
        }
    }
}