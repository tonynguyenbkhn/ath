<?php

///////////////////////////
// HELPERS
///////////////////////////

function twmp_get_image_id_by_color($color_slug)
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

// 2. remove additional information

function twmp_remove_additional_information_tab($tabs)
{
    unset($tabs['additional_information']);
    return $tabs;
}
add_filter('woocommerce_product_tabs', 'twmp_remove_additional_information_tab', 98);

// 3. remove sidebar
add_action('wp', function () {
    remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
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
        str_replace(':', '', esc_html__('SKU: ', 'twmp-ath')),
        $product->get_sku()
    );
}

function twmp_render_product_stock_meta()
{
    global $product;

    $availability = $product->get_availability();
    printf(
        '<p class="product-meta product-meta--stock"><span class="product-meta__label">%s:</span> <span class="product-meta__value">%s</span></p>',
        esc_html__('Stock', 'twmp-ath'),
        $availability['class'] != 'in-stock' ? $availability['availability'] : esc_html__('In stock', 'twmp-ath')
    );
}


function twmp_render_product_categories_meta()
{
    global $product;

    $product_categories = get_the_terms($product->get_id(), 'product_cat');
    if (!empty($product_categories) && !is_wp_error($product_categories)) {
        $product_category_label = _n('Category', 'Categories', count($product_categories), 'twmp-ath');

        printf(
            '<p class="product-meta product-meta--categories"><span class="product-meta__label">%s:</span> <span class="product-meta__value">%s</span></p>',
            $product_category_label,
            wc_get_product_category_list($product->get_id(), ', ')
        );
    }
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

// 10 . Change text to price on gallery
remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
// add_action('woocommerce_before_single_product_summary', 'twmp_change_sale_flash_in_gallery', 5);

function twmp_change_sale_flash_in_gallery()
{
    global $product;

    $final_price = twmp_get_price_discount_percentage($product, 'percentage');
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

add_action('woocommerce_single_product_summary', function () {
    ?>
    <div class="single__header">
        <div class="row align-items-center">
            <div class="col-12">
                <?php
                global $product;
                if ( isset($product) && $product ) {
                    $product_id = $product->get_id();
                    $badges = function_exists('get_field') ? get_field('ath_badges', $product_id) : false;
                    if (!empty($badges) && is_array($badges)) {
                        echo '<div class="product-badges">';
                        foreach ($badges as $badge) {
                            $text = isset($badge['text']) ? $badge['text'] : '';
                            $style = isset($badge['style']) ? $badge['style'] : 'orange';
                            $class = 'ath-badge ath-badge--' . esc_attr($style);
                            if ($text) {
                                printf('<span class="%s">%s</span>', esc_attr($class), esc_html($text));
                            }
                        }
                        echo '</div>';
                    }
                }
                ?>
                <?php wc_get_template('single-product/title.php'); ?>
                <?php
                if ( isset( $product ) && $product ) {
                    $product_id = $product->get_id();
                    $subtitle = function_exists('get_field') ? get_field('ath_subtitle', $product_id) : false;
                    if ( $subtitle ) {
                        printf('<p class="product-subtitle">%s</p>', esc_html($subtitle));
                    }

                    $description = get_the_excerpt($product_id);
                    if ( $description ) {
                        echo '<div class="product-description">' . wp_kses_post( wpautop( $description ) ) . '</div>';
                    }
                }
                ?>
            </div>
        </div>
    </div>
<?php
}, 1);

add_action('woocommerce_single_product_summary', function () {
    global $product;
    
}, 15);

// remove rating
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);

// remove except
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);

// remove add to cart
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);

// add thong tin bao hanh

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

// add_action('woocommerce_single_product_summary', function () {
//     global $product;
//     $contact_before = get_field('contact', $product->get_id());
//     if ($contact_before) {
//         get_template_part('templates/blocks/quick-contact', null, []);
//     } else {
//         woocommerce_template_single_add_to_cart();
//     }
// }, 80);

add_filter('woocommerce_product_single_add_to_cart_text', 'custom_add_to_cart_button_text_with_icon');
function custom_add_to_cart_button_text_with_icon($text)
{
    $icon = '';
    return $icon . ' ' . esc_html__('Add to cart', 'twmp-ath');
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

add_filter('woocommerce_related_products', 'twmp_related_products_ids', 10, 3);

function twmp_related_products_ids($related_products, $product_id, $args)
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
        <h3><?php echo esc_html__('Specifications', 'twmp-ath') ?></h3>
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
        <h3><?php echo esc_html__('Suggestions for you', 'twmp-ath') ?></h3>
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

// remove default variation

// remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );

// display price in pa_color