<?php
//use WebPExpress\Option;

if (!defined('ABSPATH')) exit;

// TODO: Extend WebpExpress plugin to allow for custom filters
//add_action('plugins_loaded', 'lvl_webp_init');
//function lvl_webp_init()
//{
//    if (!is_plugin_active('webp-express/webp-express.php')) {
//        return;
//    }
//
//    if (Option::getOption('webp-express-alter-html', false)) {
////        require_once __DIR__ . '/lib/classes/AlterHtmlInit.php';
////        \WebPExpress\AlterHtmlInit::setHooks();
//        add_filter( 'our_filter', '\\WebPExpress\\AlterHtmlInit::alterHtml', 99999 ); // priority big, so it will be executed last
//
//    }
//}

defined('MAX_FILE_SIZE') || define('MAX_FILE_SIZE', 1500000);

add_filter('lvl_webp', 'lvl_webp_src');
function lvl_webp_src($content)
{
    // is WebpExpress plugin active?
    if (!is_plugin_active('webp-express/webp-express.php')) {
        return $content;
    }

    $content = lvl_webp_filter_content($content);
    return $content;
}

// WEBPEXPRESS CHECK - check if webp version of image file exists with modified path
function lvl_get_webp_url($src)
{
    $file_path = explode('/uploads/', $src);
    if (str_contains($file_path[1], 'sites')) {
        $file_sub_dirs = explode('/', $file_path[1]);
        unset($file_sub_dirs[0], $file_sub_dirs[1]);
        $file_path[1] = implode('/', $file_sub_dirs);
    }

    $uploadDir = wp_upload_dir();
    $file_check = str_replace('/uploads', '/webp-express/webp-images/uploads', $uploadDir['basedir']) . '/' . $file_path[1] . '.webp';

    if (file_exists($file_check)) {
        return str_replace('/uploads', '/webp-express/webp-images/uploads', $src) . '.webp';
    }

    return $src;
}

function lvl_webp_filter_content($content)
{
    $matches = [];
    preg_match_all('/(src|srcset|poster|(data-[^=]*(lazy|small|slide|img|large|src|thumb|source|set|bg-url)[^=]*))=("|\')(.*?)("|\')/i', $content, $matches);

    $content = preg_replace_callback(
        '/(src|srcset|poster|(data-[^=]*(lazy|small|slide|img|large|src|thumb|source|set|bg-url)[^=]*))=("|\')(.*?)("|\')/i',
        function ($matches) {
            // if match is srcset loop through and replace each src with webp
            if ($matches[1] === 'srcset') {
                $srcset = explode(',', $matches[5]);
                $webp_srcset = [];
                foreach ($srcset as $src) {
                    $src = trim($src);
                    // remove size for processing and then reapply size
                    $src_parts = explode(' ', $src);
                    $src = array_shift($src_parts);
                    $src = lvl_get_webp_url($src);
                    $webp_srcset[] = $src . ' ' . implode(' ', $src_parts);
                }
                return $matches[1] . '="' . implode(', ', $webp_srcset) . '"';
            }

            return $matches[1] . '="' . lvl_get_webp_url($matches[5]) . '"';
        },
        $content
    );

    return $content;
}