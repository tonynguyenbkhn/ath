<?php

add_action('rest_api_init', 'custom_rest_routes');

function custom_rest_routes()
{
    register_rest_route('twmp/v1', 'get_product_tabs_html', array(
        'methods' => 'GET',
        'callback' => 'get_product_tabs_html_callback',
        'permission_callback' => '__return_true'
    ));
}

function get_product_tabs_html_callback($request)
{
    $category_id = isset($request['category_id']) && is_numeric($request['category_id']) ? esc_attr($request['category_id']) : null;
    $posts_per_page = isset($request['posts_per_page']) && is_numeric($request['posts_per_page']) ? esc_attr($request['posts_per_page']) : null;
    $product_query_type = isset($request['query_type']) ? esc_attr($request['query_type']) : null;

    if (empty($category_id) || empty($posts_per_page) || empty($product_query_type)) {
        return new \WP_Rest_Response(array(
            'errorCode' => 400,
            'errorMessage' => __('Bad request. Missing parameters.', 'twmp-phonghoa')
        ), 200);
    }

    $product_args = wcs_get_product_query_by_type($product_query_type);
    $product_args = wp_parse_args(array(
        'posts_per_page' => $posts_per_page,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'id',
                'terms' => $category_id
            )
        ),
        'meta_query' => array(
            array(
                'key' => '_stock_status',
                'value' => 'instock'
            )
        )
    ), $product_args);

    $product_query = new WP_Query($product_args);

    if ($product_query->have_posts()) :

        ob_start();
        while ($product_query->have_posts()) :
            $product_query->the_post();
            wc_get_template_part('content', 'product');
        endwhile;
        wp_reset_postdata();
        $html = ob_get_clean();

        $response = new \WP_Rest_Response();
        $data_args = array(
            'html' => $html
        );

        if (empty($request->get_param('cache'))) :
            $response->set_headers(array(
                'Cache-Control' => 'max-age=3600' // 1 hour
            ));
            $data_args['cache'] = true;
        else :
            $data_args['cache'] = false;
        endif;

        $response->set_data($data_args);
        $response->set_status(200);

        return $response;

    else :

        return new \WP_Rest_Response(array(
            'errorCode' => 404,
            'errorMessage' => __('There is no product to display.', 'twmp-phonghoa'),
            'html' => __('There is no product to display.', 'twmp-phonghoa')
        ), 200);

    endif;
}

add_action('rest_api_init', 'custom_rest_routes_product_tabs_slider');

function custom_rest_routes_product_tabs_slider()
{
    register_rest_route('wcs/v1', 'get_product_tabs_slider_html', array(
        'methods' => 'GET',
        'callback' => 'get_product_tabs_slider_html_callback',
        'permission_callback' => '__return_true'
    ));
}

function get_product_tabs_slider_html_callback($request)
{
    $category_id = isset($request['category_id']) && is_numeric($request['category_id']) ? esc_attr($request['category_id']) : null;
    $posts_per_page = isset($request['posts_per_page']) && is_numeric($request['posts_per_page']) ? esc_attr($request['posts_per_page']) : null;
    $product_query_type = isset($request['query_type']) ? esc_attr($request['query_type']) : null;

    if (empty($category_id) || empty($posts_per_page) || empty($product_query_type)) {
        return new \WP_Rest_Response(array(
            'errorCode' => 400,
            'errorMessage' => __('Bad request. Missing parameters.', 'twmp-phonghoa')
        ), 200);
    }

    $product_args = wcs_get_product_query_by_type($product_query_type);
    $product_args = wp_parse_args(array(
        'posts_per_page' => $posts_per_page,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'id',
                'terms' => $category_id
            )
        ),
        'meta_query' => array(
            array(
                'key' => '_stock_status',
                'value' => 'instock'
            )
        )
    ), $product_args);

    $product_query = new WP_Query($product_args);
    $total_product_count = $product_query->found_posts;

    if ($product_query->have_posts()) :
        $_swiper_items = [];

        while ($product_query->have_posts()) :
            $product_query->the_post();
            ob_start();
            $content_html = wc_get_template_part('content', 'product');
            $content_html = ob_get_clean();
            $content_html = str_replace('<li', '<span', $content_html);
            $content_html = str_replace('<ul', '<span', $content_html);
            $content_html = str_replace('</li>', '</span>', $content_html);
            $content_html = str_replace('</ul>', '</ul>', $content_html);
            $_swiper_items[] = [
                'class' => 'd-flex flex-column product-slider__slide',
                'content' => $content_html
            ];
        endwhile;
        wp_reset_postdata();
        // $html = ob_get_clean();

        $response = new \WP_Rest_Response();
        ob_start();
        get_template_part('templates/core-blocks/swiper', null, [
            'class' => 'cat_' . $category_id,
            'items' => $_swiper_items,
            'lazyload' => false,
            'settings' => [
                'autoplay' => $total_product_count > 4 ? 4000 : false,
                'pagination' => $total_product_count > 4 ? true : false,
                'prevNextButtons' => $total_product_count > 4 ? true : false,
                'prevSvgButton' => 'arrow-left',
                'nextSvgButton' => 'arrow-right'
            ]
        ]);
        $html_output = ob_get_clean();

        $data_args = array(
            'html' => $html_output
        );

        if (empty($request->get_param('cache'))) :
            $response->set_headers(array(
                'Cache-Control' => 'max-age=3600' // 1 hour
            ));
            $data_args['cache'] = true;
        else :
            $data_args['cache'] = false;
        endif;

        $response->set_data($data_args);
        $response->set_status(200);

        return $response;

    else :

        return new \WP_Rest_Response(array(
            'errorCode' => 404,
            'errorMessage' => __('There is no product to display.', 'twmp-phonghoa'),
            'html' => __('There is no product to display.', 'twmp-phonghoa')
        ), 200);

    endif;
}

add_action('wp_ajax_update_quantity_in_mini_cart', 'ajax_update_quantity_in_mini_cart');
add_action('wp_ajax_nopriv_update_quantity_in_mini_cart', 'ajax_update_quantity_in_mini_cart');

function ajax_update_quantity_in_mini_cart()
{
    check_ajax_referer('wcs-config-nonce', 'nonce', false);
    if (!isset($_POST['key']) || !isset($_POST['qty'])) {
        wp_send_json_error();
    }

    $response = array();

    $cart_item_key = sanitize_text_field(wp_unslash($_POST['key']));
    $product_qty = absint($_POST['qty']);

    WC()->cart->set_quantity($cart_item_key, $product_qty);

    $count = WC()->cart->get_cart_contents_count();

    ob_start();
    $response['item'] = $count;
    $response['total_price'] = WC()->cart->get_cart_total();
    $response['content'] = ob_get_clean();

    wp_send_json_success($response);
}
