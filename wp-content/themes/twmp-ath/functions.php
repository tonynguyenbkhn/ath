<?php

if (!defined('ABSPATH')) {
    exit;
}

if (! defined('TWMP_DIR_PATH')) {
    define('TWMP_DIR_PATH', untrailingslashit(get_theme_file_path()));
}

if (! defined('TWMP_DIR_URI')) {
    define('TWMP_DIR_URI', untrailingslashit(get_theme_file_uri()));
}

if (! defined('TWMP_DIST_URI')) {
    define('TWMP_DIST_URI', untrailingslashit(get_theme_file_uri()) . '/assets');
}

if (! defined('TWMP_DIST_PATH')) {
    define('TWMP_DIST_PATH', untrailingslashit(get_theme_file_path()) . '/assets');
}

if (! defined('TWMP_DIST_JS_URI')) {
    define('TWMP_DIST_JS_URI', untrailingslashit(get_theme_file_uri()) . '/assets/js');
}

if (! defined('TWMP_DIST_JS_DIR_PATH')) {
    define('TWMP_DIST_JS_DIR_PATH', untrailingslashit(get_theme_file_path()) . '/assets/js');
}

if (! defined('TWMP_IMG_URI')) {
    define('TWMP_IMG_URI', untrailingslashit(get_theme_file_uri()) . '/assets/images');
}

if (! defined('TWMP_IMAGES_URI')) {
    define('TWMP_IMAGES_URI', untrailingslashit(get_theme_file_uri()) . '/images');
}

if (! defined('TWMP_DIST_CSS_URI')) {
    define('TWMP_DIST_CSS_URI', untrailingslashit(get_theme_file_uri()) . '/assets/css');
}

if (! defined('TWMP_DIST_CSS_DIR_PATH')) {
    define('TWMP_DIST_CSS_DIR_PATH', untrailingslashit(get_theme_file_path()) . '/assets/css');
}

require_once TWMP_DIR_PATH . '/inc/helpers/utility.php';
require_once TWMP_DIR_PATH . '/inc/helpers/comments.php';
require_once TWMP_DIR_PATH . '/inc/helpers/search.php';
require_once TWMP_DIR_PATH . '/inc/helpers/autoloader.php';
require_once TWMP_DIR_PATH . '/inc/helpers/template-functions.php';

function twmp_get_theme_instance()
{
    \TWMP_THEME\Inc\TWMP_THEME::get_instance();
}

twmp_get_theme_instance();

if (is_singular() && comments_open() && get_option('thread_comments')) {
    wp_enqueue_script('comment-reply');
}

add_filter('get_the_archive_title', function ($title) {
    if (is_category() || is_tag() || is_tax()) {
        $title = single_term_title('', false);
    }
    return $title;
});

function twmp_enqueue_styles()
{
    wp_enqueue_style(
        'mytheme-style', // handle
        get_stylesheet_uri(), // tự động lấy style.css
        array(), // dependencies
        wp_get_theme()->get('Version') // version để tránh cache
    );
}
add_action('wp_enqueue_scripts', 'twmp_enqueue_styles');

