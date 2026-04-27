<?php

add_action('wp', function () {
    // remove sidebar default
    remove_action('woocommerce_sidebar', 'generate_construct_sidebars');
    // remove all notices
    remove_action('woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10);
    // remove sale flash default
    remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
    // remove add to cart default
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
    // remove breadcrumb default
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
    // remove tag a woocommerce-LoopProduct-link woocommerce-loop-product__link
    remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);
});

// 1. custom breadcrumb
add_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 5);
add_action('woocommerce_before_main_content', function () {
    if (is_product() || is_cart() || is_checkout()) {
        return;
    }
    echo '<div class="woocommerce-archive-wrapper">
    <div class="container shop__container">
    <div class="row shop__row">
    <div class="col-lg-12 col-md-12 col-12">';
}, 6);
// 2. wrapper shop container
add_action('woocommerce_before_main_content', function () {
    echo is_product() ? '<div class="woocommerce-single-wrapper"><div class="container single__container">' : '';
}, 10);

add_action('woocommerce_sidebar', function () {
    echo is_product() ? '</div><!-- End ... --></div><!-- End ... -->' : '';
}, 30);


/* custom sidebar */
add_action('woocommerce_sidebar', function () {
    if (is_product() || is_cart() || is_checkout()) {
        return;
    }
    ob_start();
?>
    </div><!-- End col list product -->
    <div class="col-lg-12 col-md-12 col-12">
        <?php
        if (is_shop()) {
            $shop_page_id = wc_get_page_id('shop');

            if ($shop_page_id && $hero_sliders = get_field('hero_slider', $shop_page_id)) {
                $items = [];
                foreach ($hero_sliders as $image_id) {
                    $items[]['image'] = $image_id;
                }
                get_template_part('templates/blocks/hero-slider', null, [
                    'id' => '',
                    'class' => 'section hero-slider',
                    'lazyload' => false,
                    'items' => $items,
                    'enable_container' => false
                ]);
            }
        } else if (is_product_category()) {
            $product_cat_id = get_queried_object()->term_id;
            if ($product_cat_id && $hero_sliders = get_field('hero_slider', 'product_cat_' . $product_cat_id)) {
                $items = [];
                foreach ($hero_sliders as $image_id) {
                    $items[]['image'] = $image_id;
                }
                get_template_part('templates/blocks/hero-slider', null, [
                    'id' => '',
                    'class' => 'section hero-slider',
                    'lazyload' => false,
                    'items' => $items,
                    'enable_container' => false
                ]);
            }
        }
        echo '<div class="d-flex justify-content-between align-items-center shop__header w-100"><div class="d-flex align-items-center shop__header-layout">';
        woocommerce_product_taxonomy_archive_header();
        // woocommerce_result_count();
        echo '</div><!-- End shop__header-layout --></div><!-- End shop__header -->';
        if (is_shop()) { ?>
            <div class="right-sidebar right-sidebar--woocommerce">
                <div class="inside-right-sidebar inside-right-sidebar--woocommerce">
                    <?php dynamic_sidebar('sidebar-archive-woocommerce'); ?>
                </div>
            </div>
        <?php }
        if (is_tax('product_cat')) { ?>
            <?php /*
            $current_term = get_queried_object();
   
            if ($current_term && (int) $current_term->parent !== 0) {
    
            } else { */ ?>
            <div class="right-sidebar right-sidebar--woocommerce">
                <div class="inside-right-sidebar inside-right-sidebar--woocommerce">
                    <?php dynamic_sidebar('sidebar-archive-woocommerce'); ?>
                </div>
            </div>
            <?php // } 
            ?>
        <?php }
        ?>
    </div><!-- End col sidebar -->
    </div><!-- End row shop__row -->
    <?php do_action('twmp-after-shop-page'); ?>
    </div><!-- End container shop__container -->
    <?php
    $html = ob_get_clean();
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- XSS OK. Nội dung đã được escape trong các template con.
    echo wp_kses($html, array(
        'div'  => array('class' => array(), 'id' => array(), 'style' => array()),
        'ul'   => array('class' => array()),
        'li'   => array('class' => array()),
        'a'    => array('href' => array(), 'class' => array(), 'title' => array()),
        'span' => array('class' => array()),
        'img'  => array('src' => array(), 'alt' => array(), 'class' => array(), 'width' => array(), 'height' => array()),
        // Thêm các thẻ khác mà bạn dùng trong archive vào đây
    ));
}, 10);

// 3 . custom sale flash
add_action('woocommerce_before_shop_loop_item_title', function () {
    global $product;
    $final_price = wcs_get_price_discount_percentage($product, 'percentage');
    $classes = ['d-block', 'product__tag', 'product__tag--onsale', 'product__tag--primary'];
    $html = '';

    if (!empty($final_price)) :
        ob_start();
    ?>
        <span class="<?php echo esc_attr(implode(' ', array_filter($classes))); ?>">
            <?php echo esc_html($final_price); ?>
        </span>
    <?php
        $html = ob_get_clean();
    else :
        $html = '';
    endif;

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo wp_kses($html, array(
        'div'  => array('class' => array(), 'id' => array(), 'style' => array()),
        'ul'   => array('class' => array()),
        'li'   => array('class' => array()),
        'a'    => array('href' => array(), 'class' => array(), 'title' => array()),
        'span' => array('class' => array()),
        'img'  => array('src' => array(), 'alt' => array(), 'class' => array(), 'width' => array(), 'height' => array()),
        // Thêm các thẻ khác mà bạn dùng trong archive vào đây
    ));
}, 5);

add_action('woocommerce_before_shop_loop_item_title', function () {
    global $product;
    $label = get_field('label', $product->get_id());
    $classes = ['product__tag', 'product__tag--label'];
    $html = '';

    if (!empty($label) && $label !== 'none') :
        ob_start();
        $src = TWMP_IMG_URI . '/' . $label . '.png'
    ?>
        <span class="<?php echo esc_attr(implode(' ', array_filter($classes))); ?>">
            <img style="width: 48px;height:27px" src="<?php echo esc_url($src) ?>" alt="">
        </span>
    <?php
        $html = ob_get_clean();
    else :
        $html = '';
    endif;

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo wp_kses($html, array(
        'div'  => array('class' => array(), 'id' => array(), 'style' => array()),
        'ul'   => array('class' => array()),
        'li'   => array('class' => array()),
        'a'    => array('href' => array(), 'class' => array(), 'title' => array()),
        'span' => array('class' => array()),
        'img'  => array('src' => array(), 'alt' => array(), 'class' => array(), 'width' => array(), 'height' => array()),
        // Thêm các thẻ khác mà bạn dùng trong archive vào đây
    ));
}, 6);

// 4. custom html shop header
// add_action('woocommerce_shop_loop_header', function () {
//     echo '<div class="d-flex justify-content-between align-items-center shop__header w-100"><div class="d-flex align-items-center">';
// }, 5);

// add_action('woocommerce_before_shop_loop', function () {
//     echo '</div>';
// }, 25);

// 5. Add icon change layout
/*
add_action('woocommerce_before_shop_loop', function () {
    // $list = ['list', 'columns-3', 'columns-4'];
    $list = ['columns-3', 'columns-4'];
    $html = '';
    ob_start();
    $html .= '<div class="d-flex align-items-center">';
    $html .= '<div class="product-style-list d-flex align-items-center" data-block="product-style-list">';
    foreach ($list as $icon):
        $html .= '<div class="product-style-list__item ' . $icon . '">';
        $html .= '<span data-type="' . esc_attr($icon) . '">' . twmp_get_svg_icon($icon) . '</span>';
        $html .= '</div>';
    endforeach;
    $html .= '</div>';
    $html .= ob_get_clean();
    echo $html;
}, 28);
*/
// add_action('woocommerce_before_shop_loop', function () {
//     echo '</div>';
// }, 35);

// add_action('woocommerce_before_shop_loop', function () {
//     echo '</div>';
// }, 40);

add_action('woocommerce_shop_loop_item_title', function () {
    echo '<div class="d-flex product__content">';
}, 5);

add_action('woocommerce_after_shop_loop_item_title', function () {
    echo '</div><!-- End ... --></div><!-- End ... -->';
}, 15);

// 6. show star rating when rating = 0
add_filter('woocommerce_product_get_rating_html', function ($html, $rating, $count) {
    if (0 == $rating) {
        $label = sprintf(__('Rated %s out of 5', 'twmp-ath'), $rating);
        $html  = '<div class="star-rating" role="img" aria-label="' . esc_attr($label) . '">' . wc_get_star_rating_html($rating, $count) . '</div>';
    }
    return $html;
}, 10, 3);

// 7. show description
add_action('woocommerce_shop_loop_item_title', function () {
    global $post;

    $short_description = $post->post_excerpt;

    if (! $short_description) {
        return;
    }
    $html = '';
    ob_start();
    $html .= '<div class="woocommerce-product-details__short-description">';
    $html .= wp_trim_words($short_description, 30, '...');
    $html .= '</div><!-- End ... -->';
    $html .= ob_get_clean();
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo wp_kses($html, array(
        'div'  => array('class' => array(), 'id' => array(), 'style' => array()),
        'ul'   => array('class' => array()),
        'li'   => array('class' => array()),
        'a'    => array('href' => array(), 'class' => array(), 'title' => array()),
        'span' => array('class' => array()),
        'img'  => array('src' => array(), 'alt' => array(), 'class' => array(), 'width' => array(), 'height' => array()),
        // Thêm các thẻ khác mà bạn dùng trong archive vào đây
    ));
}, 15);

// 8. custom add to cart
// add_action('woocommerce_after_shop_loop_item_title', function () {
//     global $product;
//     get_template_part('templates/blocks/add-to-cart-button', null, [
//         'product_id' => $product->get_id(),
//         'class' => 'product-row__cart-button',
//         'enable_quick_buy' => false
//     ]);
// }, 14);

// Uu dai
add_action('woocommerce_after_shop_loop_item_title', function () {
    global $product;
    $promotion_type = get_field('promotion', $product->get_id());
    $promotion_content = get_field('promotion_content', $product->get_id());

    $html = '';

    if (!empty($promotion_type) && $promotion_type !== 'none' && !empty($promotion_content)) :
        ob_start();
    ?>
        <div class="promotion-content">
            <span class="promotion-content__icon">
                <?php echo twmp_get_svg_icon($promotion_type); ?>
            </span>
            <span class="promotion-content__text">
                <?php echo esc_html($promotion_content); ?>
            </span>
        </div>
    <?php
        $html = ob_get_clean();
    else :
        $html = '';
    endif;

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo wp_kses($html, array(
        'div'  => array('class' => array(), 'id' => array(), 'style' => array()),
        'ul'   => array('class' => array()),
        'li'   => array('class' => array()),
        'a'    => array('href' => array(), 'class' => array(), 'title' => array()),
        'span' => array('class' => array()),
        'img'  => array('src' => array(), 'alt' => array(), 'class' => array(), 'width' => array(), 'height' => array()),
        // Thêm các thẻ khác mà bạn dùng trong archive vào đây
    ));
}, 14);

// sold
add_action('woocommerce_after_shop_loop_item_title', function () {
    global $product;
    $html = '';
    ob_start();
    ?>
    <span class="total-sales"><?php echo esc_html__('Sold', 'twmp-ath') . ' ' . $product->get_total_sales(); ?></span>
    <?php
    $html = ob_get_clean();
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo wp_kses($html, array(
        'div'  => array('class' => array(), 'id' => array(), 'style' => array()),
        'ul'   => array('class' => array()),
        'li'   => array('class' => array()),
        'a'    => array('href' => array(), 'class' => array(), 'title' => array()),
        'span' => array('class' => array()),
        'img'  => array('src' => array(), 'alt' => array(), 'class' => array(), 'width' => array(), 'height' => array()),
        // Thêm các thẻ khác mà bạn dùng trong archive vào đây
    ));
}, 14);

// 9. custom link
add_action('woocommerce_before_shop_loop_item', function () {
    echo '<div class="woocommerce-LoopProduct-link woocommerce-loop-product__link">';
}, 10);

add_action('woocommerce_after_shop_loop_item', function () {
    echo '</div><!-- End ... -->';
}, 25);

// 10. wrapper image by link
add_action('woocommerce_before_shop_loop_item_title', function () {
    global $product;

    if (! ($product instanceof WC_Product)) {
        return;
    }

    echo '<a href="' . get_the_permalink() . '" class="product-link product-link__image image__overlay-zoom">';
}, 9);
add_action('woocommerce_before_shop_loop_item_title', function () {
    echo '</a><!-- End ... -->';
}, 11);

// // 11. wrapper title by link
add_action('woocommerce_shop_loop_item_title', function () {
    global $product;

    if (! ($product instanceof WC_Product)) {
        return;
    }

    echo '<a href="' . get_the_permalink() . '" class="product-link product-link__title">';
}, 9);
add_action('woocommerce_shop_loop_item_title', function () {
    echo '</a><!-- End ... --><div class="product__content-inner">';
}, 11);


// remove order default of woocommerce
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
// remove result count
// remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
// remove header
remove_action('woocommerce_shop_loop_header', 'woocommerce_product_taxonomy_archive_header', 10);

// custom plugin filter everything

// add_filter('wpc_filters_label_term_html', 'custom_filter_brand_with_image', 10, 4);

// function custom_filter_brand_with_image($html, $link_attributes, $term_object, $filter)
// {
//     // Lấy thumbnail ID từ term meta
//     $thumbnail_id = get_term_meta($term_object->term_id, 'thumbnail_id', true);

//     // Nếu có ảnh
//     if ($thumbnail_id) {
//         $image_url = wp_get_attachment_image_url($thumbnail_id, 'thumbnail');
//         if ($image_url) {
//             // Tạo HTML hiển thị ảnh + tên thương hiệu
//             $html = sprintf(
//                 '<a %s><img src="%s" alt="%s">%s</a>',
//                 $link_attributes,
//                 esc_url($image_url),
//                 esc_attr($term_object->name),
//                 ''
//             );
//         }
//     }

//     return $html;
// }

add_filter('use_widgets_block_editor', '__return_false');

add_action('woocommerce_product_query', 'twmp_custom_orderby_query');
function twmp_custom_orderby_query($query)
{
    if (is_admin() || !$query->is_main_query() || !is_shop() && !is_product_taxonomy()) {
        return;
    }

    // Xem nhiều → sắp xếp theo "popularity" (dựa trên tổng số lượng đã bán)
    if (isset($_GET['orderby_view']) && $_GET['orderby_view'] === 'product_views') {
        $query->set('meta_key', 'total_sales');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', 'DESC');
    }

    // Khuyến mãi → chỉ lấy sản phẩm đang sale
    if (isset($_GET['orderby_promotion']) && $_GET['orderby_promotion'] === 'promotion') {
        $meta_query = $query->get('meta_query');

        $meta_query[] = array(
            'key'     => '_sale_price',
            'value'   => 0,
            'compare' => '>',
            'type'    => 'NUMERIC'
        );

        $query->set('meta_query', $meta_query);
    }
}

add_action('twmp-after-shop-page', function () {
    if (is_search()) {
        $keyword  = get_search_query();
        $posts = new WP_Query([
            'post_type' => 'post',
            's' => $keyword,
            'posts_per_page' => 4
        ]);
        if ($posts->have_posts()) : ?>
            <div class="archive-shop-page-related-post">
                <div class="archive-shop-page-related-post__row row">
                    <div class="col-12 d-flex mb-1 justify-content-between align-items-center">
                        <?php
                        get_template_part('templates/core-blocks/heading', null, [
                            'title_class' => 'archive-shop-page-related-post__title mb-0',
                            'description_class' => '',
                            'class' => 'archive-shop-page-related-post__header',
                            'title' => esc_html__('News contains', 'twmp-ath') . ' ' . '"' . $keyword . '"',
                            'description' => '',
                        ]);

                        $url = add_query_arg(
                            [
                                's' => $keyword,
                                'post_type' => 'post'
                            ],
                            home_url('/')
                        );
                        ?>
                        <a class="archive-shop-page-related-post__view-more" href="<?php echo esc_url($url); ?>">
                            <span><?php echo esc_html__('View more', 'twmp-ath'); ?></span>
                            <?php echo twmp_get_svg_icon('arrow-right'); ?>
                        </a>
                    </div>
                    <?php while ($posts->have_posts()) :
                        $posts->the_post();
                        get_template_part('templates/blocks/post-card', null, [
                            'class' => 'col-lg-3 post-card--col',
                            'post_data' => get_post(get_the_ID()),
                            'post_id' => get_the_ID(),
                            'view_more_button' => esc_html__('', 'twmp-ath'),
                            'options' => [
                                'show_excerpt' => false,
                                'show_date' => false,
                                'show_author' => false,
                                'show_categories' => false
                            ]
                        ]);
                    endwhile;
                    wp_reset_postdata(); ?>
                </div>
            </div>
        <?php
        else :
            get_template_part('template-parts/content', 'none');
        endif;
        ?>
    <?php } ?>
    <?php
}, 10);

add_action('twmp-after-shop-page', function () {
    if (is_shop() || is_search() || is_product_category()) {
        if (is_shop()) {
            $shop_page_id = wc_get_page_id('shop');
        } else if (is_product_category()) {
            $shop_page_id = get_queried_object()->term_id;
        } else {
            $shop_page_id = null;
        }
        if (is_shop()) {
            $images         = get_field('images', $shop_page_id);
            $related_post   = get_field('related_post', $shop_page_id);
            $content        = get_field('content', $shop_page_id);
        } else if (is_product_category()) {
            $images         = get_field('images', 'product_cat_' . $shop_page_id);
            $related_post   = get_field('related_post', 'product_cat_' . $shop_page_id);
            $content        = get_field('content', 'product_cat_' . $shop_page_id);
        } else {
            $images         = [];
            $related_post   = [];
            $content        = '';
        }
    ?>
        <?php /*
        if (is_search()) {
            $query = get_search_query();
            $posts = new WP_Query([
                'post_type' => 'post',
                's' => $query,
                'posts_per_page' => 4
            ]);

            if ($posts->have_posts()) : ?>
                <div class="archive-shop-page-related-post">
                    <div class="archive-shop-page-related-post__row row">
                        <?php while ($posts->have_posts()) :
                            $posts->the_post();
                            get_template_part('templates/blocks/post-card', null, [
                                'class' => 'col-lg-3 post-card--col',
                                'post_data' => get_post(get_the_ID()),
                                'post_id' => get_the_ID(),
                                'view_more_button' => esc_html__('', 'twmp-ath'),
                            ]);
                        endwhile; ?>
                    </div>
                </div>
            <?php
            else :
                get_template_part('template-parts/content', 'none');
            endif;
            ?>
        <?php } */
        if ($shop_page_id) {
        ?>
            <div class="archive-shop-page-seo">
                <div class="row archive-shop-page-seo__row">
                    <?php if (!empty($images) || !empty($related_post)): ?>
                        <div class="col-lg-4 col-md-12 col-sm-12 col-12">
                            <div class="archive-shop-page-seo__widgets">
                                <?php if (!empty($images)) : ?>
                                    <div class="archive-shop-page-seo__widget archive-shop-page-seo__widget-image">
                                        <?php
                                        foreach ($images as $item) {
                                            echo '<a href="' . esc_url($item['url']) . '">';
                                            get_template_part('templates/core-blocks/image', null, [
                                                'image_id' => $item['image_id'],
                                                'image_size' => 'full',
                                                'lazyload' => true,
                                                'class' => 'pe-none image--cover image--default',
                                                'image_class' => 'w-100 h-auto',
                                            ]);
                                            echo '</a>';
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($related_post)) : ?>
                                    <div class="archive-shop-page-seo__widget archive-shop-page-seo__widget-relate-post mt-lg-3 mt-md-2 mt-1 mobile-scroll">
                                        <?php
                                        get_template_part('templates/core-blocks/heading', null, [
                                            'title_class' => 'category-card__title',
                                            'description_class' => 'category-card__description',
                                            'class' => 'common-header-wrapper category-card__header',
                                            'title' => esc_html__('Related News', 'twmp-ath'),
                                            'description' => '',
                                        ]);
                                        ?>
                                        <?php
                                        get_template_part('templates/blocks/post-grid', null, [
                                            'id' => '',
                                            'class' => 'post-grid mobile-same-height',
                                            'items' => $related_post,
                                            'enable_container' => false,
                                            'block_layout' => '1-col',
                                            'view_more_button' => '',
                                            'query' => null
                                        ]);
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($images) || !empty($related_post)): ?>
                        <div class="col-lg-8 col-md-12 col-sm-12 col-12">
                        <?php else: ?>
                            <div class="col-12"></div>
                        <?php endif; ?>
                        <?php if (!empty($content)): ?>
                            <div class="archive-shop-page-seo__content">
                                <h3 class="archive-shop-page-seo__title"><?php echo esc_html__('Introduce', 'twmp-ath'); ?></h3>
                                <div data-block="show-less">
                                    <div class="js-content-toggle has-toggle">
                                        <div class="single__content">
                                            <?php echo wp_kses_post($content); ?>
                                        </div>
                                        <?php get_template_part('templates/blocks/show-less', null, []); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        </div>
                </div>
            </div>
<?php
        }
    }
}, 20);

add_action('woocommerce_after_shop_loop_item_title', function () {
    echo '<div class="d-flex align-items-center justify-content-between">';
}, 4);

add_action('woocommerce_after_shop_loop_item_title', function () {
    global $product;
    $sold = (int) get_field('sold', $product->get_id()) > 0 ? get_field('sold', $product->get_id()) : '741';
    echo esc_html__('Sold ', 'twmp-ath') . esc_html($sold);
    echo '</div>';
}, 6);

// remove filter recently viewed yith_wrvp_filter_by_cat_args
// add_filter('yith_wrvp_filter_by_cat_args', '__return_false');
//
//add_filter('yith_ywar_rating_widget', 'custom_ywar_rating_widget', 10, 7);
//
//function custom_ywar_rating_widget($output, $context, $review_stats, $elem, $link, $label, $additional_label) {
//    global $product;
//    // Lấy giá trị từ custom field ACF
//    $sold = (int) get_field('sold', $product->get_id()) > 0 ? get_field('sold', $product->get_id()) : '741';
//
//    // Trả về nội dung thay thế
//    return esc_html__('Sold ', 'twmp-ath') . esc_html($sold);
//}

function shortcode_category_grid()
{
    ob_start();

    get_template_part('templates/blocks/category-grid-children-only', null, [
        'class' => 'category-grid',
        'enable_container' => true,
        'grid_css_class' => 'col',
        'class_container' => 'header__bottom-container'
    ]);

    return ob_get_clean();
}
add_shortcode('category_grid', 'shortcode_category_grid');

// Trong functions.php của theme (hoặc plugin của bạn)
remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);

add_action('woocommerce_shop_loop_item_title', 'custom_woocommerce_template_loop_product_title', 10);
function custom_woocommerce_template_loop_product_title()
{
    echo '<h3 class="' . esc_attr(apply_filters('woocommerce_product_loop_title_classes', 'woocommerce-loop-product__title')) . '">' . get_the_title() . '</h3>';
}
