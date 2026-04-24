<?php

$data = wp_parse_args($args, [
    'id' => '',
    'class' => '',
    'items' => [],
    'lazyload' => true,
    'enable_container' => false
]);

$_class = !empty($data['class']) ? esc_attr(' ' . $data['class']) : '';
$_class .= $data['lazyload'] ? ' is-not-loaded' : '';

$_class_container = 'container';
$_class_container .= !empty($data['class_container']) ? esc_attr(' ' . $data['class_container']) : '';

$args = array(
    'orderby' => 'menu_order',
    'order' => 'asc',
    'hide_empty' => false,
    'pad_counts' => true,
    'child_of' => 0,
);

if (!empty($select_categories)) {
    $args['include'] = $select_categories;
    $args['orderby'] = 'include';
}

$product_categories = get_terms('product_cat', $args);

$_swiper_items = [];
foreach ($product_categories as $index => $category):
    ob_start();

    get_template_part('templates/blocks/category-grid-item', null, array('term_id' => $category->term_id, 'term_name' => $category->name));

    $content_html = ob_get_clean();

    $_swiper_items[] = [
        'class' => 'd-flex flex-column product-slider__slide',
        'content' => $content_html
    ];
endforeach;

?>

<div class="<?php echo esc_attr($_class); ?>" <?php if (!empty($data['id'])) : ?> id="<?php echo esc_attr($data['id']); ?>" <?php endif; ?> data-block="category-grid-slider">

    <?php if ($data['lazyload']) : ?><noscript><?php endif; ?>
        <?php if ($data['enable_container']) : ?><div class="<?php echo esc_attr($_class_container); ?>"><?php endif; ?>
            <?php
            get_template_part('templates/core-blocks/heading', null, [
                'title_class' => 'category-grid-slider__title',
                'description_class' => 'category-grid-slider__description',
                'class' => 'category-grid-slider__header',
                'title' => $data && !empty($data['title']) ? $data['title'] : '',
                'description' => $data && !empty($data['description']) ? $data['description'] : '',
            ]);
            ?>
            <?php get_template_part('templates/core-blocks/swiper', null, [
                'items' => $_swiper_items,
                'lazyload' => !$data['lazyload'],
                'settings' => [
                    'autoplay' => 4000,
                    'pagination' => true,
                    'prevNextButtons' => true,
                    'prevSvgButton' => 'arrow-left',
                    'nextSvgButton' => 'arrow-right'
                ]
            ]); ?>
            <?php if ($data['enable_container']) : ?></div><?php endif; ?>
        <?php if ($data['lazyload']) : ?></noscript><?php endif; ?>

</div>