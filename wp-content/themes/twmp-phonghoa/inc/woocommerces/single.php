<?php

///////////////////////////
// HELPERS
///////////////////////////

function wcs_get_image_id_by_color($color_slug)
{
    global $product;

    if (!$product->is_type('variable')) {
        return;
    }

    $variations = $product->get_available_variations();

    if (empty($variations)) {
        return;
    }

    foreach ($variations as $variation) {
        if (isset($variation['attributes']['attribute_pa_color']) && $variation['attributes']['attribute_pa_color'] === $color_slug) {
            $image_id = $variation['image_id'];
            return $image_id;
        }
    }

    return false;
}

///////////////////////////
// CUSTOMIZE
///////////////////////////

// 1. customize swatches color -> image
// Active plugin Variation Swatches for WooCommerce - By Emran Ahmed
if (class_exists('Woo_Variation_Swatches')) {
    add_filter('woo_variation_swatches_color_attribute_template', 'wcs_change_color_to_image_attribute_template', 10, 2);

    function wcs_change_color_to_image_attribute_template($template, $data)
    {
        $term = $data['item'];
        $color = $term->slug;
        $image_id = wcs_get_image_id_by_color($color);
        if ($term && $color !== '' && count($image_id) > 0) {
            return sprintf('<span class="variable-item-span variable-item-span-color"><img src="%s" /></span>', wp_get_attachment_url($image_id));
        } {
            return $template;
        }
    }
}

// 2. remove additional information

function wcs_remove_additional_information_tab($tabs)
{
    unset($tabs['additional_information']);
    return $tabs;
}
add_filter('woocommerce_product_tabs', 'wcs_remove_additional_information_tab', 98);

// 3. remove sidebar
add_action('wp', function () {
    remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
});

// 3. woocommerce_before_add_to_cart_quantity - woocommerce_after_add_to_cart_quantity
add_action('woocommerce_before_add_to_cart_quantity', function () {
?>
    <div class="quantity-wrapper" data-block="quantity">
    <?php
});

add_action('woocommerce_after_add_to_cart_quantity', function () {
    ?>
    </div>
    <?php
});

// 4. remove product meta default
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);

// 5. Add sku & stock & categories after price
// add_action('woocommerce_single_product_summary', 'twmp_render_product_sku_meta', 11);
// add_action('woocommerce_single_product_summary', 'twmp_render_product_stock_meta', 12);
// add_action('woocommerce_single_product_summary', 'twmp_render_product_categories_meta', 13);


function twmp_render_product_sku_meta()
{
    global $product;

    printf(
        '<p class="product-meta product-meta--sku"><span class="product-meta__label">%s:</span> <span class="product-meta__value sku">%s</span></p>',
        str_replace(':', '', esc_html__('SKU: ', 'twmp-phonghoa')),
        $product->get_sku()
    );
}

function twmp_render_product_stock_meta()
{
    global $product;

    $availability = $product->get_availability();
    printf(
        '<p class="product-meta product-meta--stock"><span class="product-meta__label">%s:</span> <span class="product-meta__value">%s</span></p>',
        esc_html__('Stock', 'twmp-phonghoa'),
        $availability['class'] != 'in-stock' ? $availability['availability'] : esc_html__('In stock', 'twmp-phonghoa')
    );
}


function twmp_render_product_categories_meta()
{
    global $product;

    $product_categories = get_the_terms($product->get_id(), 'product_cat');
    if (!empty($product_categories) && !is_wp_error($product_categories)) {
        $product_category_label = _n('Category', 'Categories', count($product_categories), 'twmp-phonghoa');

        printf(
            '<p class="product-meta product-meta--categories"><span class="product-meta__label">%s:</span> <span class="product-meta__value">%s</span></p>',
            $product_category_label,
            wc_get_product_category_list($product->get_id(), ', ')
        );
    }
}

// 6. Add countdown price

function get_timezone()
{
    $wp_timezone = wp_timezone_string();

    return new DateTimeZone($wp_timezone);
}

function format_date_with_time($timestamp, $type = 'end')
{
    if (empty($timestamp)) {
        return new \WP_Error('not_found', __FUNCTION__ . ': ' . __('Missing timestamp parameter', 'twmp-phonghoa'));
    }

    $timezone = get_timezone();
    $date_format = $type === 'start' ? 'D M d Y 00:00:00 O' : 'D M d Y 23:59:59 O';
    $date = DateTime::createFromFormat('U', $timestamp);
    $date->setTimeZone($timezone);

    return $date->format($date_format);
}

function get_time_range($product)
{
    $output = [];

    if ($product->is_type('simple')) {

        $start_date_timestamp = get_post_meta($product->get_id(), '_sale_price_dates_from', true);
        $end_time_timestamp = get_post_meta($product->get_id(), '_sale_price_dates_to', true);

        if (!empty($start_date_timestamp) && !empty($end_time_timestamp)) :
            $output = array(
                'from' => format_date_with_time($start_date_timestamp, 'start'),
                'to' => format_date_with_time($end_time_timestamp, 'end')
            );
        endif;
    } elseif ($product->is_type('variable')) {

        $variations = $product->get_available_variations();

        if (empty($variations)) {
            return $output;
        }

        $start_dates = [];
        $end_dates = [];

        // Loop to find all available date time range
        foreach ($variations as $variation) {
            $variation_object = new WC_Product_Variation($variation['variation_id']);
            $variation_data = $variation_object->get_data();

            $sale_price_from = !empty($variation_data['date_on_sale_from']) ? $variation_data['date_on_sale_from'] : null;
            $sale_price_to = !empty($variation_data['date_on_sale_to']) ? $variation_data['date_on_sale_to'] : null;

            if (!empty($sale_price_from)) {
                $start_dates[] = $sale_price_from->date('D M d Y 00:00:00 O');
            }

            if (!empty($sale_price_to)) {
                $end_dates[] = $sale_price_to->date('D M d Y 23:59:59 O');
            }
        }

        if (!empty($start_dates)) {
            $output['from'] = min($start_dates);
        }

        if (!empty($end_dates)) {
            $output['to'] = max($end_dates);
        }
    }

    return $output;
}

function is_scheduled($date)
{
    if (empty($date) || is_wp_error($date)) {
        return false;
    }

    return strtotime($date) > current_time('timestamp');
}

function is_date_running($date)
{
    if (empty($date) || is_wp_error($date)) {
        return false;
    }

    return strtotime($date) >= current_time('timestamp');
}

add_action('woocommerce_single_product_summary', 'render_countdown_block', 10);

function render_countdown_block()
{
    global $product;
    $time_range = get_time_range($product);
    $price = $product->get_price_html();

    $sales_price_from = !empty($time_range['from']) ? $time_range['from'] : null;
    $sales_price_to   = !empty($time_range['to']) ? $time_range['to'] : null;

    $is_scheduled = !empty($sales_price_from) && is_scheduled($sales_price_from);
    $is_running = !empty($sales_price_to) && is_date_running($sales_price_to);
    $_class = 'product-price';

    if (
        ($is_scheduled ||
            $is_running)
    ) {
        $attributes = 'data-block="countdown-price"';

        $_class .= ' product-price--countdown';
        $_class .= ' is-style-' . esc_attr('default');

        if (!empty($sales_price_from)) :
            $attributes .= sprintf(' data-start-date="%s"', $sales_price_from); // Ex: 2021-10-01 00:00:00
        endif;

        if (!empty($sales_price_to)) :
            $attributes .= sprintf(' data-end-date="%s"', $sales_price_to); // Ex: 2021-10-01 23:59:59
        endif;

        $price_output_html = sprintf('<span class="%s" %s>', $_class, $attributes);
        $price_output_html .= apply_filters('woocommerce_get_price', $price);
        $price_output_html .= '</span>'; // Close .single-product__price--has-discount

        echo wp_kses_post( $price_output_html );
    }
}

add_action('wp_enqueue_scripts', 'load_assets');

function load_assets()
{
    ob_start();
    printf('var CODETOT_COUNTDOWN_LABELS = \'%s\'', json_encode(get_labels()));
    $labels_js_content = ob_get_clean();

    wp_register_script('codetot-countdown-labels', false);
    wp_enqueue_script('codetot-countdown-labels', false);
    wp_add_inline_script('codetot-countdown-labels', $labels_js_content);
}

function get_labels()
{
    return array(
        'days' => array(
            'singular' => esc_html__('Day', 'twmp-phonghoa'),
            'plural'   => esc_html__('Day', 'twmp-phonghoa')
        ),
        'hours' => array(
            'singular' => esc_html__('Hrs', 'twmp-phonghoa'),
            'plural'   => esc_html__('Hrs', 'twmp-phonghoa')
        ),
        'minutes' => array(
            'singular' => esc_html__('Min', 'twmp-phonghoa'),
            'plural'   => esc_html__('Min', 'twmp-phonghoa')
        ),
        'seconds' => array(
            'singular' => esc_html__('Sec', 'twmp-phonghoa'),
            'plural'   => esc_html__('Sec', 'twmp-phonghoa')
        ),
        'message' => array(
            'not_start' => esc_html__('Sale begins after', 'twmp-phonghoa'),
            'ongoing'   => esc_html__('Sale ended after', 'twmp-phonghoa'),
            'expired'   => esc_html__('The sale has ended.', 'twmp-phonghoa'),
            'less_day'  => esc_html__('The sale will end after less than a day.', 'twmp-phonghoa'),
            'less_hour' => esc_html__('Hurry up! The sale will end after less than a hour.', 'twmp-phonghoa')
        )
    );
}

// 7. Quick buy

add_action('woocommerce_after_add_to_cart_button', 'wcs_quick_buy');
function wcs_quick_buy()
{
    get_template_part('templates/blocks/quick-buy', null, []);
    get_template_part('templates/blocks/quick-buy-kredivo', null, []);
}

// 8. Remove woocommerce notices

remove_action('woocommerce_before_single_product', 'woocommerce_output_all_notices', 10);

// 9. Add brand

add_action('woocommerce_single_product_summary', function () {
    global $product;

    if (!$product) {
        return;
    }

    $brand_terms = get_the_terms($product->get_id(), 'product_brand');

    if (!empty($brand_terms) && !is_wp_error($brand_terms)) {
        $brand_names = wp_list_pluck($brand_terms, 'name');
        echo '<p class="product-brand">Brand: ' . implode(', ', $brand_names) . '</p>';
    }
}, 40);

// 10 . Change text to price on gallery
remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
// add_action('woocommerce_before_single_product_summary', 'twmp_change_sale_flash_in_gallery', 5);

function twmp_change_sale_flash_in_gallery()
{
    global $product;

    $final_price = wcs_get_price_discount_percentage($product, 'percentage');
    $classes = ['product__tag', 'single-product-top__sale-tag', 'product__tag--primary'];

    if (!empty($final_price)) :
    ?>
        <span class="<?php echo esc_attr(implode(' ', array_filter($classes))); ?>">
            <?php echo esc_html($final_price); ?>
        </span>
    <?php
    endif;
}

// 11. Custom class single product

add_filter('woocommerce_post_class', function ($classes, $product) {
    if (is_product()) {
        $classes[] = 'product__detail';
    }

    return $classes;
}, 10, 2);

// 12. Wrap li

add_action('woocommerce_review_before', function () {
    echo '<div class="comment-avatar">';
}, 5);
add_action('woocommerce_review_before', function () {
    echo '</div>';
}, 15);

// remove heding in tab
add_filter('woocommerce_product_description_heading', '__return_empty_string');

// remove title
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);

add_action('woocommerce_before_single_product_summary', function () {
    global $product;
    ?>
    <div class="single__header">
        <div class="row align-items-center">
            <div class="col-xl-9 col-lg-12 col-md-12 col-sm-12 col-12">
                <?php wc_get_template('single-product/title.php'); ?>
            </div>
            <div class="col-xl-3 col-lg-12 col-md-12 col-sm-12 col-12">
                <div class="d-flex justify-content-xl-end justify-content-start">
                    <?php
                    woocommerce_template_single_rating();
                    $sold = (int) get_field('sold', $product->get_id()) > 0 ? get_field('sold', $product->get_id()) : '741';
                    ?>
                    <span class="total-sales"><?php echo esc_html__('Sold', 'twmp-phonghoa') . ' ' . $sold; ?></span>
                </div>
            </div>
        </div>
    </div>
<?php
}, 6);

// remove rating
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);

// remove except
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);

// remove add to cart
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);

// add thong tin bao hanh
add_action('woocommerce_single_product_summary', function () {
    global $product;
    $dataStickyContact = get_field('sticky_links', 'option') ? get_field('sticky_links', 'option') : [];
    $zalo = '';
    foreach ($dataStickyContact as $item) {
        if ($item['type'] === 'zalo') {
            $zalo = $item;
        }
    }
    $zalo_woo = get_field('zalo_woo', 'option') ? get_field('zalo_woo', 'option') : $zalo['url'];
?>
    <div class="box-sale-mifan">
        <p style="font-weight: 600;font-size: 16px;">
            <a href="<?php echo esc_url($zalo_woo) ?>" style="display: flex;align-items: center;">
                <img src="https://mivietnam.vn/wp-content/uploads/2024/10/zalo-icon.png" style="height: 24px;margin-right: 5px;" />
                <?php echo esc_html__('Message Discount this product', 'twmp-phonghoa') ?>
            </a>
        </p>
    </div>
    <?php
    if ($product->is_type('simple')) {

        $promotions = get_posts([
            'post_type'      => 'manage-promotions',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => [
                [
                    'key'     => 'products',
                    'value'   => '"' . $product->get_id() . '"',
                    'compare' => 'LIKE',
                ]
            ]
        ]);

        $guarantee = get_field('guarantee', $product->get_id());
        // $gift = get_field('gift', $product->get_id()); 
    ?>
        <div class="woocommerce_single_product_summary__guarantee">
            <?php echo wp_kses_post($guarantee); ?>
        </div>
        <?php

        if ($promotions) {
            foreach ($promotions as $promotion) {
                echo '<div class="woocommerce_single_product_summary__gift">';
                echo wp_kses_post(get_field('content', $promotion->ID));
                echo '</div>';
            }
        }
        ?>
    <?php
    }

    echo do_shortcode('[contact-form-7 id="dd97541" title="Nhận thêm ưu đãi"]');
}, 70);

add_action('woocommerce_after_variations_table', function () {
    global $product;
    if ($product->is_type('variable')) {
        $guarantee = get_field('guarantee', $product->get_id());
        $gift = get_field('gift', $product->get_id()); ?>
        <div class="woocommerce_single_product_summary__guarantee">
            <?php echo wp_kses_post($guarantee); ?>
        </div>
        <div class="woocommerce_single_product_summary__gift">
            <?php echo wp_kses_post($gift); ?>
        </div>
    <?php
    }
}, 10);

// add_action('woocommerce_single_product_summary', function () {
// 	$product_id = get_the_ID();
// 	$product = wc_get_product($product_id);

// 	if (!$product) {
// 		return;
// 	}

// 	get_template_part('templates/blocks/add-to-cart-button', null, [
// 		'product_id' => $product_id,
//         'enable_quick_buy' => true
// 	]);
// }, 80);

add_action('woocommerce_single_product_summary', function () {
    global $product;
    $contact_before = get_field('contact', $product->get_id());
    if ($contact_before) {
        get_template_part('templates/blocks/quick-contact', null, []);
    } else {
        woocommerce_template_single_add_to_cart();
    }
}, 80);

add_filter('woocommerce_product_single_add_to_cart_text', 'custom_add_to_cart_button_text_with_icon');
function custom_add_to_cart_button_text_with_icon($text)
{
    $icon = '';
    return $icon . ' ' . esc_html__('Add to cart', 'twmp-phonghoa');
}

// add div wrapper of entry summary

add_action('woocommerce_single_product_summary', function () {
    echo '<div class="entry-summary-wrapper">';
}, 1);

add_action('woocommerce_single_product_summary', function () {
    echo '</div>';
}, 1000);

// remove relate product
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);

add_filter('woocommerce_related_products', 'custom_related_products_ids', 10, 3);

function custom_related_products_ids($related_products, $product_id, $args)
{
    $custom_ids = get_field('related_product', $product_id);

    if (!empty($custom_ids)) {
        return $custom_ids;
    }

    $terms = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'ids']);
    if (!empty($terms)) {
        $query_args = [
            'posts_per_page' => 5,
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
            'tax_query'      => [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $terms,
                ]
            ],
            'post__not_in'   => [$product_id],
        ];
        $q = get_posts($query_args);
        return wp_list_pluck($q, 'ID');
    }

    return $related_products;
}

add_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 1);

add_action('woocommerce_after_single_product_summary', function () {
    echo '<div class="woocommerce_after_single_product_summary"><div class="row"><div class="col-lg-8 col-md-12 col-sm-12 col-12">';
}, 5);

add_action('woocommerce_after_single_product_summary', function () {
    echo '</div><div class="col-lg-4 col-md-12 col-sm-12 col-12"><div class="single__content-widgets">';
}, 50);

add_action('woocommerce_after_single_product_summary', function () {
    echo '</div></div></div></div>';
}, 1000);

add_action('woocommerce_after_single_product_summary', function () {
    global $product;
    $product_id = $product->get_id();
    $information = get_field('information', $product_id); ?>
    <div class="single__content-widget">
        <h3><?php echo esc_html__('Specifications', 'twmp-phonghoa') ?></h3>
        <div class="single__content-widget-information">
            <?php echo wp_kses_post($information); ?>
        </div>
    </div>
<?php
}, 60);

add_action('woocommerce_after_single_product_summary', function () {
    global $product;
    $product_id = $product->get_id();
    $information = get_field('information', $product_id); ?>
    <div class="single__content-widget">
        <h3><?php echo esc_html__('Suggestions for you', 'twmp-phonghoa') ?></h3>
        <div class="single__content-widget-suggest">
            <?php get_template_part('templates/blocks/product-grid', null, array(
                'class' => 'product-grid',
                'items' => array(
                    // 300,
                    // 299,
                    // 297,
                    // 295,
                    // 293,
                    // 303,
                    // 302,
                    // 296,
                    // 294,
                    // 291,
                ),
                'block_layout' => '2-col',
                'enable_container' => false
            )); ?>
        </div>
    </div>
    <?php
}, 60);

// custom link variable

add_filter('wpclv_term_button', function ($html, $term, $product_id) {
    if (empty($product_id)) {
        return $html;
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        return $html;
    }

    $price_html = $product->get_price_html();

    // Thêm giá trước thẻ đóng </a> hoặc </span>
    // Ưu tiên chèn trước thẻ đóng (theo logic hiện tại của plugin)

    // Nếu có <a>, chèn trước </a>
    if (strpos($html, '</a>') !== false) {
        $html = str_replace('</a>', ' <span class="wpclv-price">' . $price_html . '</span></a>', $html);
    }

    // Nếu có <span>, chèn trước </span> (chỉ nếu không có <a>)
    else if (strpos($html, '</span>') !== false) {
        $html = str_replace('</span>', ' <span class="wpclv-price">' . $price_html . '</span></span>', $html);
    }

    return $html;
}, 10, 3);

// remove default variation

// remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );

// display price in pa_color

add_filter('woo_variation_swatches_variable_item_custom_attributes', function ($html_attributes, $data, $attribute_type, $variation_data) {
    // Đảm bảo là thuộc tính màu (pa_color) mới xử lý

    if (empty($data['attribute_name']) || strpos($data['attribute_name'], 'pa_color') === false) {
        return $html_attributes;
    }

    // Lấy product
    $product = $data['args']['product'];
    if (!$product || !$product->is_type('variable')) {
        return $html_attributes;
    }
    $option_slug = $data['option_slug'];
    $variations  = $product->get_available_variations();

    // Duyệt từng biến thể để tìm giá tương ứng màu
    foreach ($variations as $variation) {
        $attributes = $variation['attributes'];
        $variation_color = $attributes['attribute_pa_color'] ?? '';

        if ($variation_color === $option_slug) {
            $price = $variation['display_price'];
            $price_html = wc_price($price);
            $html_attributes['data-price'] = esc_attr($price_html);
            break;
        }
    }

    return $html_attributes;
}, 10, 4);

add_action('wp_footer', function () {
    if (!is_product()) return;
    ?>
    <script>
        jQuery(function($) {
            $('.variable-item[data-price]').each(function() {
                const price = $(this).data('price');

                if (price && $(this).find('.variable-item-contents .swatch-price').length === 0) {
                    // Thêm <div class="swatch-price"> bên trong .variable-item-contents
                    $(this).find('.variable-item-contents').append('<div class="swatch-price">' + price + '</div>');
                }
            });
        });
    </script>
    <style>
        .variable-item-contents .swatch-price {
            font-size: 12px;
            color: #666;
            margin-top: 3px;
            text-align: center;
        }
    </style>
<?php
});

add_action('woocommerce_add_to_cart', function () {
    if (isset($_REQUEST['buy_now']) && $_REQUEST['buy_now'] === '1') {
        if (WC()->session) {
            WC()->session->set('chosen_payment_method', 'kredivo');
        }
    }
});