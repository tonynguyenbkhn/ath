<?php

$data = wp_parse_args($args, [
    'id' => '',
    'class' => '',
    'items' => [],
    'enable_container' => false
]);

$_class = !empty($data['class']) ? ' ' . $data['class'] : '';

$_class_container = 'container';
$_class_container .= !empty($data['class_container']) ? esc_attr(' ' . $data['class_container']) : '';

$_columns = 5;
$_enable_lazyload = isset($enable_lazyload) && $enable_lazyload;

$block_attributes = array(
    'endpoint' => 'get_product_tabs_html',
    'postsPerPage' => !empty($numbers) ? (int) $numbers : 5,
    'queryType' => !empty($attribute) ? esc_attr($attribute) : 'on_sale'
);

$categories = get_terms([
    'taxonomy'   => 'product_cat',
    'hide_empty' => false,
]);

$items = [];
foreach ($categories as $index => $category) :
    $items[] = array(
        'id' => esc_attr($category->slug),
        'name' => esc_html($category->name),
        'category_id' => esc_attr($category->term_id),
        'is_active' => $index === 0,
        'is_lazyload' => $index !== 0,
    );
endforeach; ?>
<div class="<?php echo esc_attr($_class) ?>" data-settings='<?php echo json_encode($block_attributes) ?>' <?php if (!empty($data['id'])) : ?> id="<?php echo esc_attr($data['id']); ?>" <?php endif; ?> data-block="product-tabs">
    <?php if ($data['enable_container']) : ?><div class="<?php echo esc_attr($_class_container); ?>"><?php endif; ?>
        <?php
        get_template_part('templates/core-blocks/heading', null, [
            'title_class' => 'product-tabs__title',
            'description_class' => 'product-tabs__description',
            'class' => 'product-tabs__header',
            'title' => $data && !empty($data['title']) ? $data['title'] : '',
            'description' => $data && !empty($data['description']) ? $data['description'] : '',
        ]);
        ?>
        <?php if (!empty($categories)) : ?>
            <div class="d-inline-flex product-tabs__nav-wrapper">
                <ul class="d-flex product-tabs__nav" role="tablist">
                    <?php foreach ($items as $item) : ?>
                        <?php printf(
                            '<li class="rel product-tabs__item" role="tab" aria-controls="%1$s" aria-selected="%2$s">%3$s</li>',
                            $item['id'],
                            var_export($item['is_active'], true),
                            $item['name']
                        ); ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php

        foreach ($items as $item) : ?>
            <div class="product-tabs__tab-content js-tab-content" data-category-id="<?php echo esc_attr($item['category_id']); ?>" id="<?php echo esc_attr($item['id']); ?>" role="tabpanel" aria-expanded="<?php echo var_export($item['is_active'], true); ?>">
                <div class="rel product-tabs__inner">
                    <div class="product-grid__main product-scroll-wrapper mobile-scroll">
                        <ul class="products columns-<?php echo esc_attr($_columns); ?> js-grid<?php if (!$item['is_active'] || ($item['is_active'] && $item['is_lazyload'])) : ?> is-not-loaded<?php endif; ?>">
                            <?php
                            if ($item['is_active'] && !$item['is_lazyload']) :
                                $product_args = wcs_get_product_query_by_type($block_attributes['queryType']);
                                $product_args = wp_parse_args(array(
                                    'posts_per_page' => $block_attributes['postsPerPage'],
                                    'tax_query' => array(
                                        array(
                                            'taxonomy' => 'product_cat',
                                            'field' => 'id',
                                            'terms' => $item['category_id']
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
                                    while ($product_query->have_posts()) :
                                        $product_query->the_post();
                                        wc_get_template_part('content', 'product');
                                    endwhile;
                                    wp_reset_postdata();
                                else :
                                    printf('<li class="product no-product">%s</li>', esc_html__('There is no products available for this category.', 'twmp-phonghoa'));
                                endif;
                            endif;
                            ?>
                        </ul>
                    </div>
                    <?php
                    get_template_part('templates/blocks/loader', null, ['class' => 'loader--dark']);
                    ?>
                    <div class="mt-2 product-tabs__footer d-none">
                        <?php /*
                        get_template_part('templates/core-blocks/button', null, [
                            'class'       => 'product-tabs__button',
                            'button_text' => esc_html__('View all products', 'twmp-phonghoa'),
                            'button_attrs' => ' title="' . sprintf(__('View all products in %s', 'twmp-phonghoa'), $item['name']) . '"',
                            'type' => !empty($button_style) ? $button_style : 'primary',
                            'button_url'  => get_term_link((int) $item['category_id'], 'product_cat'),
                            'button_link_target' => !empty($button_target) ? esc_attr($button_target) : '_self'
                        ]); */
                        ?>
                    </div>
                </div>
            </div>
        <?php
        endforeach; ?>
        <?php if ($data['enable_container']) : ?>
        </div><?php endif; ?>
</div>