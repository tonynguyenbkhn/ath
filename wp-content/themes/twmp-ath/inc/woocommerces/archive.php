<?php

if (!defined('ABSPATH')) {
    exit;
}

function twmp_is_product_search_page()
{
    if (!is_search()) {
        return false;
    }

    $post_type = get_query_var('post_type');

    if (is_array($post_type)) {
        return in_array('product', $post_type, true);
    }

    return 'product' === $post_type;
}

function twmp_is_shop_catalog_page()
{
    return is_shop() || is_product_category() || twmp_is_product_search_page();
}

function twmp_is_product_search_fallback()
{
    if (!empty($GLOBALS['twmp_product_search_fallback'])) {
        return true;
    }

    return !empty(get_query_var('twmp_product_search_fallback'));
}

function twmp_is_empty_product_search_page()
{
    if (!is_search() || !function_exists('twmp_is_product_search_page') || !twmp_is_product_search_page()) {
        return false;
    }

    $search_term = (string) get_query_var('twmp_product_search_term');

    if ('' === trim($search_term)) {
        $search_term = (string) get_search_query();
    }

    $search_term = trim($search_term);

    if ('' === $search_term || !function_exists('twmp_product_search_has_results')) {
        return false;
    }

    return !twmp_product_search_has_results($search_term);
}

function twmp_product_search_has_results($search_term)
{
    static $cache = array();

    $search_term = trim((string) $search_term);

    if ('' === $search_term) {
        return false;
    }

    if (array_key_exists($search_term, $cache)) {
        return $cache[$search_term];
    }

    $check_query = new WP_Query(
        array(
            'post_type'           => 'product',
            'post_status'         => 'publish',
            's'                   => $search_term,
            'posts_per_page'      => 1,
            'no_found_rows'       => true,
            'ignore_sticky_posts' => true,
            'fields'              => 'ids',
            'suppress_filters'    => true,
        )
    );

    $cache[$search_term] = $check_query->have_posts();

    return $cache[$search_term];
}

function twmp_get_fallback_product_ids()
{
    static $cache = null;

    if (null !== $cache) {
        return $cache;
    }

    $fallback_query = new WP_Query(
        array(
            'post_type'           => 'product',
            'post_status'         => 'publish',
            'ignore_sticky_posts' => true,
            'posts_per_page'      => -1,
            'no_found_rows'       => true,
            'orderby'             => 'date',
            'order'               => 'DESC',
            'fields'              => 'ids',
        )
    );

    $cache = $fallback_query->posts;

    return $cache;
}

function twmp_flag_empty_product_search_query($query)
{
    if (is_admin() || !($query instanceof WP_Query) || !$query->is_main_query() || !$query->is_search()) {
        return;
    }

    $post_type = $query->get('post_type');
    $is_product_search = false;

    if (is_array($post_type)) {
        $is_product_search = in_array('product', $post_type, true);
    } else {
        $is_product_search = 'product' === $post_type;
    }

    if (!$is_product_search) {
        return;
    }

    $search_term = (string) $query->get('s');
    if ('' === trim($search_term)) {
        return;
    }

    if (twmp_product_search_has_results($search_term)) {
        return;
    }

    $query->set('twmp_product_search_fallback', 1);
    $query->set('twmp_product_search_term', $search_term);
    $query->set('s', '');
    $query->set('post_type', 'product');
    $query->set('post_status', 'publish');
    $query->set('ignore_sticky_posts', true);
    // Keep posts_per_page as requested (do not force -1) so pagination works
    $query->set('no_found_rows', true);
    $query->set('orderby', 'date');
    $query->set('order', 'DESC');

    $GLOBALS['twmp_product_search_fallback'] = true;
    $GLOBALS['twmp_product_search_term'] = $search_term;

    // Pre-fill WC loop props so templates reading wc_get_loop_prop() earlier see fallback values
    $fallback_posts = twmp_get_fallback_product_ids();
    $fallback_count = count($fallback_posts);
    if (function_exists('wc_set_loop_prop')) {
        wc_set_loop_prop('total', $fallback_count);
        wc_set_loop_prop('total_pages', 1);
        wc_set_loop_prop('current_page', 1);
        wc_set_loop_prop('per_page', max(1, $fallback_count));
    }
    if (!isset($GLOBALS['woocommerce_loop']) || !is_array($GLOBALS['woocommerce_loop'])) {
        $GLOBALS['woocommerce_loop'] = array();
    }
    $GLOBALS['woocommerce_loop']['total'] = $fallback_count;
    $GLOBALS['woocommerce_loop']['total_pages'] = 1;
    $GLOBALS['woocommerce_loop']['current_page'] = 1;
    $GLOBALS['woocommerce_loop']['per_page'] = max(1, $fallback_count);
}

add_action('pre_get_posts', 'twmp_flag_empty_product_search_query', 20);

// Nếu vào danh mục sản phẩm mà danh mục không có sản phẩm, bật fallback tương tự search
function twmp_flag_empty_product_category_query($query)
{
    if (is_admin() || !($query instanceof WP_Query) || !$query->is_main_query() || !$query->is_product_category()) {
        return;
    }

    $term = get_queried_object();
    if (!($term instanceof WP_Term)) {
        return;
    }

    $term_count = isset($term->count) ? absint($term->count) : 0;
    if ($term_count > 0) {
        return;
    }

    // Đánh dấu fallback (nguồn từ category)
    $query->set('twmp_product_search_fallback', 1);
    $query->set('twmp_product_search_term', isset($term->slug) ? (string) $term->slug : '');
    $GLOBALS['twmp_product_search_fallback'] = true;
    $GLOBALS['twmp_product_search_term'] = isset($term->slug) ? (string) $term->slug : '';
    $GLOBALS['twmp_product_search_fallback_from_category'] = true;
    // Pre-fill WC loop props so templates reading wc_get_loop_prop() earlier see fallback values
    $fallback_posts = twmp_get_fallback_product_ids();
    $fallback_count = count($fallback_posts);
    if (function_exists('wc_set_loop_prop')) {
        wc_set_loop_prop('total', $fallback_count);
        // Use a placeholder per_page here; real values will be set later in the_posts filter
        wc_set_loop_prop('total_pages', 1);
        wc_set_loop_prop('current_page', 1);
        wc_set_loop_prop('per_page', max(1, get_option('posts_per_page')));
    }
    if (!isset($GLOBALS['woocommerce_loop']) || !is_array($GLOBALS['woocommerce_loop'])) {
        $GLOBALS['woocommerce_loop'] = array();
    }
    $GLOBALS['woocommerce_loop']['total'] = $fallback_count;
    $GLOBALS['woocommerce_loop']['total_pages'] = 1;
    $GLOBALS['woocommerce_loop']['current_page'] = 1;
    $GLOBALS['woocommerce_loop']['per_page'] = max(1, get_option('posts_per_page'));
    // Debug: log fallback and loop props for runtime inspection
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[twmp] category fallback: term=' . (isset($term->slug) ? $term->slug : '') . ' fallback_count=' . $fallback_count);
        error_log('[twmp] pre_get_posts $wp_query->found_posts=' . (isset($GLOBALS['wp_query']->found_posts) ? $GLOBALS['wp_query']->found_posts : 'unset'));
        error_log('[twmp] pre_get_posts $woocommerce_loop=' . print_r($GLOBALS['woocommerce_loop'], true));
    }
}

add_action('pre_get_posts', 'twmp_flag_empty_product_category_query', 9);

function twmp_expand_empty_product_search_results($posts, $query)
{
    if (is_admin() || !($query instanceof WP_Query) || !$query->is_main_query() || (!$query->is_search() && !$query->is_product_category())) {
        return $posts;
    }

    $post_type = $query->get('post_type');
    $is_product_search = false;

    if (is_array($post_type)) {
        $is_product_search = in_array('product', $post_type, true);
    } else {
        $is_product_search = 'product' === $post_type;
    }

    if (!$is_product_search) {
        return $posts;
    }

    if (!empty($posts)) {
        return $posts;
    }

    $fallback_posts = twmp_get_fallback_product_ids();
    $fallback_count = count($fallback_posts);

    $GLOBALS['twmp_product_search_fallback'] = $fallback_count > 0;

    // Determine paging
    $paged = max(1, (int) $query->get('paged') ?: (int) get_query_var('paged') ?: 1);
    $per_page = (int) $query->get('posts_per_page') ?: (int) get_option('posts_per_page') ?: 10;
    $total_pages = $per_page > 0 ? (int) ceil($fallback_count / $per_page) : 1;

    $offset = ($paged - 1) * $per_page;
    $sliced_ids = array_slice((array) $fallback_posts, $offset, $per_page);

    $query->found_posts = $fallback_count;
    $query->post_count = count($sliced_ids);
    $query->max_num_pages = $total_pages;
    $query->set('posts_per_page', $per_page);
    $query->set('no_found_rows', true);
    $query->set('paged', $paged);
    $query->set('twmp_product_search_fallback', 1);

    if (function_exists('wc_set_loop_prop')) {
        wc_set_loop_prop('total', $fallback_count);
        wc_set_loop_prop('total_pages', $total_pages);
        wc_set_loop_prop('current_page', $paged);
        wc_set_loop_prop('per_page', $per_page);
    }

    if (!isset($GLOBALS['woocommerce_loop']) || !is_array($GLOBALS['woocommerce_loop'])) {
        $GLOBALS['woocommerce_loop'] = array();
    }
    $GLOBALS['woocommerce_loop']['total'] = $fallback_count;
    $GLOBALS['woocommerce_loop']['total_pages'] = $total_pages;
    $GLOBALS['woocommerce_loop']['current_page'] = $paged;
    $GLOBALS['woocommerce_loop']['per_page'] = $per_page;

    // Ensure we return paginated WP_Post objects so the main loop's have_posts()/the_post() work.
    $fallback_post_objects = array();
    foreach ((array) $sliced_ids as $post_id) {
        $p = get_post(absint($post_id));
        if ($p instanceof WP_Post) {
            $fallback_post_objects[] = $p;
        }
    }

    // Debug: log expanded posts and loop props
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[twmp] the_posts replacement: fallback_count=' . $fallback_count . ' paged=' . $paged . ' per_page=' . $per_page);
        error_log('[twmp] the_posts $query->found_posts=' . $query->found_posts);
        error_log('[twmp] the_posts $woocommerce_loop=' . print_r($GLOBALS['woocommerce_loop'], true));
    }

    return $fallback_post_objects;
}

add_filter('the_posts', 'twmp_expand_empty_product_search_results', 20, 2);

function twmp_force_facetwp_fallback_ids($post_ids, $renderer)
{
    // If not already flagged, detect empty product category pages directly
    if (!twmp_is_product_search_fallback()) {
        if (is_product_category()) {
            $term = get_queried_object();
            if ($term instanceof WP_Term && isset($term->count) && 0 === absint($term->count)) {
                $GLOBALS['twmp_product_search_fallback'] = true;
                $GLOBALS['twmp_product_search_fallback_from_category'] = true;
            }
        }
    }

    if (!twmp_is_product_search_fallback()) {
        return $post_ids;
    }

    if (!empty($post_ids)) {
        return $post_ids;
    }

    $fallback = twmp_get_fallback_product_ids();
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[twmp] facetwp_pre_filtered_post_ids: returning fallback ids count=' . count($fallback));
    }
    return $fallback;
}

add_filter('facetwp_pre_filtered_post_ids', 'twmp_force_facetwp_fallback_ids', 20, 2);

function twmp_force_facetwp_filtered_fallback_ids($post_ids, $renderer)
{
    $normalized_post_ids = array_values((array) $post_ids);
    $is_empty_post_ids = empty($normalized_post_ids) || (1 === count($normalized_post_ids) && 0 === (int) $normalized_post_ids[0]);

    if (!twmp_is_product_search_fallback()) {
        if (is_product_category()) {
            $term = get_queried_object();
            if ($term instanceof WP_Term && isset($term->count) && 0 === absint($term->count)) {
                $GLOBALS['twmp_product_search_fallback'] = true;
                $GLOBALS['twmp_product_search_fallback_from_category'] = true;
            }
        }
    }

    if (!twmp_is_product_search_fallback() || !$is_empty_post_ids || !($renderer instanceof FacetWP_Renderer)) {
        return $post_ids;
    }

    foreach ((array) $renderer->facets as $facet) {
        if (!empty($facet['selected_values'])) {
            return $post_ids;
        }
    }

    $fallback = twmp_get_fallback_product_ids();
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[twmp] facetwp_filtered_post_ids: returning fallback ids count=' . count($fallback));
    }
    return $fallback;
}

add_filter('facetwp_filtered_post_ids', 'twmp_force_facetwp_filtered_fallback_ids', 20, 2);

function twmp_adjust_facetwp_product_search_query_args($query_args, $renderer)
{
    $post_type = $query_args['post_type'] ?? '';
    $is_product_search = false;

    if (is_array($post_type)) {
        $is_product_search = in_array('product', $post_type, true);
    } else {
        $is_product_search = 'product' === $post_type;
    }

    if (!$is_product_search) {
        return $query_args;
    }

    $is_fallback = !empty($query_args['twmp_product_search_fallback']) || twmp_is_product_search_fallback();

    if (!$is_fallback) {
        return $query_args;
    }

    $GLOBALS['twmp_product_search_fallback'] = true;

    unset($query_args['s']);
    unset($query_args['twmp_product_search_fallback']);
    // Nếu fallback bắt nguồn từ category thì giữ product_cat để FacetWP vẫn lọc theo danh mục
    if (empty($GLOBALS['twmp_product_search_fallback_from_category'])) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[twmp] facetwp_query_args: removing product_cat from query_args for fallback');
        }
        unset($query_args['product_cat']);
        unset($query_args['taxonomy']);
        unset($query_args['term']);
    } else {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[twmp] facetwp_query_args: preserving product_cat because fallback_from_category=1');
        }
    }

    $query_args['post_type']           = 'product';
    $query_args['post_status']         = 'publish';
    $query_args['ignore_sticky_posts'] = true;
    $query_args['posts_per_page']      = -1;
    $query_args['no_found_rows']       = true;
    $query_args['orderby']             = 'date';
    $query_args['order']               = 'DESC';

    return $query_args;
}

add_filter('facetwp_query_args', 'twmp_adjust_facetwp_product_search_query_args', 20, 2);

// Debug helper: log WC loop state early in shop loop so we can see what wc_get_loop_prop() returns
function twmp_log_woocommerce_loop_state()
{
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }

    global $wp_query;

    $total_prop = function_exists('wc_get_loop_prop') ? wc_get_loop_prop('total') : 'wc_get_loop_prop_unavailable';
    error_log('[twmp] woocommerce_before_shop_loop hook: wc_get_loop_prop(total)=' . print_r($total_prop, true));
    error_log('[twmp] woocommerce_before_shop_loop hook: $wp_query->found_posts=' . (isset($wp_query->found_posts) ? $wp_query->found_posts : 'unset'));
    error_log('[twmp] woocommerce_before_shop_loop hook: $GLOBALS["woocommerce_loop"]=' . print_r($GLOBALS['woocommerce_loop'], true));
}

add_action('woocommerce_before_shop_loop', 'twmp_log_woocommerce_loop_state', 5);

// After WC sets up its loop (usually priority 10), re-apply our fallback loop props
function twmp_apply_fallback_loop_props_after_setup()
{
    $should_apply = false;
    if (function_exists('twmp_is_product_search_fallback') && twmp_is_product_search_fallback()) {
        $should_apply = true;
        $apply_reason = 'flag';
    } else {
        // Fallback: if we're on a product category page and the term has zero products, apply fallback
        if (is_product_category()) {
            $term = get_queried_object();
            if ($term instanceof WP_Term && isset($term->count) && 0 === absint($term->count)) {
                $should_apply = true;
                $apply_reason = 'empty_category_direct_detect';
            }
        }
    }

    if (!$should_apply) {
        return;
    }

    $fallback_posts = twmp_get_fallback_product_ids();
    $fallback_count = count($fallback_posts);

    global $wp_query;
    $paged = 1;
    $per_page = (int) get_option('posts_per_page') ?: 10;
    if ($wp_query instanceof WP_Query) {
        $paged = max(1, (int) $wp_query->get('paged') ?: (int) get_query_var('paged') ?: 1);
        $per_page = (int) $wp_query->get('posts_per_page') ?: (int) get_option('posts_per_page') ?: $per_page;
    }

    $total_pages = $per_page > 0 ? (int) ceil($fallback_count / $per_page) : 1;
    $offset = ($paged - 1) * $per_page;
    $sliced_ids = array_slice((array) $fallback_posts, $offset, $per_page);

    if (function_exists('wc_set_loop_prop')) {
        wc_set_loop_prop('total', $fallback_count);
        wc_set_loop_prop('total_pages', $total_pages);
        wc_set_loop_prop('current_page', $paged);
        wc_set_loop_prop('per_page', $per_page);
    }

    if (!isset($GLOBALS['woocommerce_loop']) || !is_array($GLOBALS['woocommerce_loop'])) {
        $GLOBALS['woocommerce_loop'] = array();
    }
    $GLOBALS['woocommerce_loop']['total'] = $fallback_count;
    $GLOBALS['woocommerce_loop']['total_pages'] = $total_pages;
    $GLOBALS['woocommerce_loop']['current_page'] = $paged;
    $GLOBALS['woocommerce_loop']['per_page'] = $per_page;

    if ($wp_query instanceof WP_Query) {
        $wp_query->found_posts = $fallback_count;
        $wp_query->post_count = count($sliced_ids);
        $wp_query->max_num_pages = $total_pages;
        $wp_query->query_vars['posts_per_page'] = $per_page;

        // Populate posts with paginated slice so have_posts() works consistently
        $fallback_post_objects = array();
        foreach ((array) $sliced_ids as $post_id) {
            $p = get_post(absint($post_id));
            if ($p instanceof WP_Post) {
                $fallback_post_objects[] = $p;
            }
        }

        if (!empty($fallback_post_objects)) {
            $wp_query->posts = $fallback_post_objects;
            $wp_query->post_count = count($fallback_post_objects);
            $wp_query->found_posts = $fallback_count;
        }
    }

    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[twmp] after wc_setup_loop: reapplied fallback_count=' . $fallback_count . ' reason=' . ($apply_reason ?? 'unknown'));
        error_log('[twmp] after wc_setup_loop: wc_get_loop_prop(total)=' . (function_exists('wc_get_loop_prop') ? wc_get_loop_prop('total') : 'unavailable'));
        error_log('[twmp] after wc_setup_loop: $wp_query->found_posts=' . (isset($wp_query->found_posts) ? $wp_query->found_posts : 'unset'));
        error_log('[twmp] after wc_setup_loop: $GLOBALS["woocommerce_loop"]=' . print_r($GLOBALS['woocommerce_loop'], true));
    }
}

add_action('woocommerce_before_shop_loop', 'twmp_apply_fallback_loop_props_after_setup', 11);

// Tiếng việt: Để tùy chỉnh cách hiển thị sản phẩm trong vòng lặp sản phẩm WooCommerce, bạn có thể sử dụng hook 'woocommerce_before_shop_loop_item' để thay thế các phần tử mặc định bằng cách của riêng bạn. Dưới đây là một ví dụ về cách làm điều này:
add_action('wp', function () {
    remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
    remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
    remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
    remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
    remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
    remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);

    add_action('woocommerce_before_shop_loop_item', 'twmp_render_product_card', 10);
});

function twmp_render_product_card()
{
    global $product, $post;

    if (!$product instanceof WC_Product) {
        return;
    }

    $product_id   = $product->get_id();
    $product_post = get_post($product_id);

    if (!$product_post instanceof WP_Post) {
        return;
    }

    if (!function_exists('get_field')) {
        return;
    }

    $only_show_image = get_field('ath_only_show_image', $product_id);

    $title    = $product->get_name();
    $url      = get_permalink($product_id);
    $image_id = $product->get_image_id();

    $badges = function_exists('get_field') ? get_field('ath_badges', $product_id) : [];

    if (!is_array($badges)) {
        $badges = [];
    }

    $short_info      = function_exists('get_field') ? (string) get_field('ath_short_info', $product_id) : '';
    $location_detail = function_exists('twmp_get_taxonomy_term_names') ? twmp_get_taxonomy_term_names($product_id, 'ath_venue') : '';

    $description_source = trim(wp_strip_all_tags((string) $product_post->post_content));
    $description        = '';

    if ('' !== $description_source) {
        $description = wp_trim_words($description_source, 18, '...');
    } elseif (!empty($product_post->post_excerpt)) {
        $description = wp_trim_words(wp_strip_all_tags($product_post->post_excerpt), 18, '...');
    }

    $timestamp    = get_post_timestamp($product_post);
    $start_datetime = function_exists('get_field') ? (string) get_field('ath_start_datetime', $product_id) : '';
    $timestamp    = $start_datetime ? strtotime($start_datetime) : $timestamp;
    $date_day     = $timestamp ? wp_date('j', $timestamp) : '';
    $date_weekday = $timestamp ? strtoupper(wp_date('D', $timestamp)) : '';
    $date_month   = $timestamp ? strtoupper(wp_date('M', $timestamp)) : '';
    $date_year    = $timestamp ? wp_date('y', $timestamp) : '';

?>

    <div class="product-card product-card--theme-red">
        <a class="product-card__link" href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr($title); ?>">
            <div class="product-card__media">
                <figure class="image product-card__image-wrap image--cover image--default">
                    <?php
                    if ($image_id) {
                        echo wp_get_attachment_image($image_id, 'woocommerce_thumbnail', false, [
                            'class' => 'image__img product-card__image',
                            'alt'   => esc_attr($title),
                        ]);
                    } else {
                        echo wc_placeholder_img('woocommerce_thumbnail', [
                            'class' => 'image__img product-card__image',
                        ]);
                    }
                    ?>
                </figure>

                <div class="product-card__overlay" aria-hidden="true"></div>
            </div>
            <?php if (!$only_show_image): ?>
                <div class="product-card__top">
                    <?php if (!empty($badges)) : ?>
                        <div class="product-card__badges">
                            <?php

                            foreach ($badges as $badge) : ?>
                                <?php
                                $badge_label = '';
                                $badge_theme = 'orange';

                                if (is_array($badge)) {
                                    $badge_label = $badge['text'] ?? '';
                                    $badge_theme = $badge['style'] ?? $badge_theme;
                                } elseif (is_string($badge)) {
                                    $badge_label = $badge;
                                }

                                $badge_label = trim((string) $badge_label);
                                $badge_theme = sanitize_html_class((string) $badge_theme);

                                if ('' === $badge_label) {
                                    continue;
                                }

                                ?>
                                <span class="ath-badge ath-badge--<?php echo esc_attr($badge_theme); ?>">
                                    <?php echo esc_html($badge_label); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($date_day || $date_weekday || $date_month || $date_year) : ?>
                        <div class="product-card__date">
                            <?php if ($date_day) : ?>
                                <span class="product-card__date-day"><?php echo esc_html($date_day); ?></span>
                            <?php endif; ?>

                            <?php if ($date_weekday) : ?>
                                <span class="product-card__date-weekday"><?php echo esc_html($date_weekday); ?></span>
                            <?php endif; ?>

                            <?php if ($date_month || $date_year) : ?>
                                <span class="product-card__date-month">
                                    <?php echo esc_html(trim($date_month . ', ' . $date_year, ', ')); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </a>

        <div class="product-card__body">
            <?php if ($short_info) : ?>
                <p class="product-card__short-info"><?php echo esc_html($short_info); ?></p>
            <?php endif; ?>

            <?php if ($location_detail) : ?>
                <div class="product-card__location">
                    <?php echo esc_html($location_detail); ?>
                </div>
            <?php endif; ?>

            <?php if ($description) : ?>
                <div class="product-card__more">

                    <div class="product-card__actions">
                        <div class="product-card__action product-card__action--book">
                            <?php
                            if (function_exists('twmp_render_cart_button')) {
                                twmp_render_cart_button(
                                    $product_id,
                                    __('Book Ticket', 'twmp'),
                                    'bg-primary-500 text-system-white typo-system-button button-default cart-redirect-btn'
                                );
                            }
                            ?>
                        </div>

                        <div class="product-card__action product-card__action--view">
                            <a
                                title="<?php echo esc_attr(sprintf(__('View Detail %s', 'twmp'), $title)); ?>"
                                class="product-card__view-button button-normal typo-system-button button-default has-icon has-after-icon"
                                href="<?php echo esc_url($url); ?>">
                                <span class="text pe-none"><?php echo esc_html__('View Detail', 'twmp'); ?></span>
                                <span class="icon pe-none" aria-hidden="true"></span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
}

// Layout

add_action('woocommerce_before_main_content', 'twmp_render_shop_layout', 40);
function twmp_render_shop_layout()
{
    if (twmp_is_shop_catalog_page()):
        echo '<div class="twmp-shop-layout"><div class="twmp-shop-layout__main"><div class="twmp-shop-layout__container container">';
    endif;
}

add_action('woocommerce_after_main_content', 'twmp_render_shop_layout_end', 9);
function twmp_render_shop_layout_end()
{
    if (twmp_is_shop_catalog_page()):
        echo '</div></div></div>';
    endif;
}

add_action('woocommerce_shop_loop_header', 'twmp_render_shop_header', 20);
function twmp_render_shop_header()
{
    echo '<div class="twmp-shop-layout-wrapper" data-block="shop-page"><div class="twmp-shop-layout__left"><div class="twmp-shop-layout__left-innner"><div class="twmp-shop-layout__left-wrap">' . do_shortcode('[facetwp facet="categories"]') . '<div class="twmp-shop-search-filter">' . get_product_search_form(false) . '<button class="filter-mobile-event">' . twmp_get_svg_icon('filter') . '</button></div></div>';
}
add_action('woocommerce_after_main_content', 'twmp_render_shop_header_end', 1);
function twmp_render_shop_header_end()
{
    echo '</div></div>';
}

add_action('woocommerce_after_main_content', 'twmp_render_shop_sidebar_start', 2);
function twmp_render_shop_sidebar_start()
{
    echo '<div class="twmp-shop-layout__right"><div class="twmp-shop-layout__right-innner">';
}

add_action('woocommerce_after_main_content', 'twmp_render_shop_sidebar_main', 5);
function twmp_render_shop_sidebar_main()
{
    if (class_exists('WooCommerce') && twmp_is_shop_catalog_page()) {
    ?>
        <div class="filter-shop">
            <div class="filter-item__head">
                <h3 class="filter-item__title"><?php echo esc_html__('Filter', 'twmp-ath'); ?></h3>
                <button class="filter-item__reset button-text d-flex items-center gap-8" onclick="FWP.reset()"><?php echo esc_html__('Clear all', 'twmp-ath'); ?> <?php echo twmp_get_svg_icon('clear-all'); ?></button>
            </div>
            <div class="filter-item__body">
                <div class="filter-item">
                    <span class="filter-item__label"><?php echo esc_html__('Date Time', 'twmp-ath'); ?></span>
                    <?php
                    echo do_shortcode('[facetwp facet="date_time"]');
                    ?>
                </div>
                <div class="filter-item">
                    <span class="filter-item__label"><?php echo esc_html__('Date of week', 'twmp-ath'); ?></span>
                    <?php
                    echo do_shortcode('[facetwp facet="date_of_week"]');
                    ?>
                </div>
                <div class="filter-item">
                    <span class="filter-item__label"><?php echo esc_html__('Age group', 'twmp-ath'); ?></span>
                    <?php
                    echo do_shortcode('[facetwp facet="age_group"]');
                    ?>
                </div>
                <div class="filter-item">
                    <span class="filter-item__label"><?php echo esc_html__('Event status', 'twmp-ath'); ?></span>
                    <?php
                    echo do_shortcode('[facetwp facet="event_status"]');
                    ?>
                </div>
                <div class="filter-item">
                    <span class="filter-item__label"><?php echo esc_html__('Event type', 'twmp-ath'); ?></span>
                    <?php
                    echo do_shortcode('[facetwp facet="event_type"]');
                    ?>
                </div>
                <div class="filter-item">
                    <span class="filter-item__label"><?php echo esc_html__('Location', 'twmp-ath'); ?></span>
                    <?php
                    echo do_shortcode('[facetwp facet="location"]');
                    ?>
                </div>
            </div>
            <div class="close-filter">
                <button class="w-100 btn-close-filter"><?php echo esc_html__('Close', 'twmp-ath'); ?></button>
            </div>
        </div>
<?php
    }
}

add_action('woocommerce_after_main_content', 'twmp_render_shop_sidebar_end', 8);
function twmp_render_shop_sidebar_end()
{
    echo '</div></div></div>';
}


// Custom Banner Shop Page

add_action('woocommerce_before_main_content', function () {
    if (!class_exists('WooCommerce')) {
        return;
    }

    if (is_shop() || is_product_category()) {

        $banner_id = 0;

        if (is_product_category()) {
            $term = get_queried_object();

            if ($term instanceof WP_Term) {
                $banner_id = absint(function_exists('get_field') ? get_field('ath_product_cat_image', $term) : 0);
            }
        }

        if (!$banner_id) {
            $banner_id = absint(function_exists('get_field') ? get_field('ath_banner_shop_page', 'option') : 0);
        }

        if (!$banner_id) {
            return;
        }
        echo '<div class="twmp-shop-banner">';
        get_template_part('templates/components/image', null, [
            'image_id'    => $banner_id,
            'image_size'  => 'full',
            'lazyload'    => false,
            'class'       => 'twmp-shop-banner__image image--cover image--default',
            'image_class' => 'twmp-shop-banner__image',
        ]);

        echo '</div>';
    } else {
        return;
    }
}, 5);

add_action('woocommerce_before_main_content', function () {
    if (!class_exists('WooCommerce')) {
        return;
    }

    if (is_shop() || is_product_category() || is_product()) {

        get_template_part(
            'templates/components/image-light',
            null,
            [
                'class' => 'event__light',
                'side' => 'right',
                'src' => TWMP_IMG_URI . '/event-light.png',
                'alt' => esc_html(get_the_title()),
                'width' => 1052,
                'height' => 816,
            ]
        );
    } else {
        return;
    }
}, 25);

add_filter('woocommerce_default_catalog_orderby', function () {
    return 'date';
});

add_action('woocommerce_after_main_content', function () {
    if (!function_exists('get_field')) {
        return;
    }

    $feedback = get_field('feedback', 'option');

    if (empty($feedback)) {
        return;
    }

    $args = array(
        'title' => esc_html__('What People Say About Us', 'twmp-ath'),
        'items' => $feedback,
        'enable_container' => true,
        'class' => 'testimonials-section'
    );

    get_template_part('templates/sections/testimonials/section', null, $args);
}, 30);
