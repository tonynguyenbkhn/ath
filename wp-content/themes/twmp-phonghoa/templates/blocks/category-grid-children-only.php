<?php

$data = wp_parse_args($args, [
    'id' => '',
    'class' => '',
    'enable_container' => false,
    'grid_css_class' => ''
]);

$_class = !empty($data['class']) ? esc_attr(' ' . $data['class']) : '';

$_class_container = 'container';
$_class_container .= !empty($data['class_container']) ? esc_attr(' ' . $data['class_container']) : '';

$current_cat = null;
$cat_ancestors = [];


if (is_tax('product_cat')) {
    $current_cat = get_queried_object(); // hoặc $wp_query->queried_object
    $cat_ancestors = get_ancestors($current_cat->term_id, 'product_cat');
}

$args = [
    'hide_empty' => false,
    'parent'     => $current_cat ? $current_cat->term_id : 0, // nếu không có danh mục hiện tại thì lấy cấp gốc
    'orderby'    => 'name',
    'order'      => 'ASC',
];

$product_categories = get_terms('product_cat', $args);

$grid_css_class = $data['grid_css_class'] ? $data['grid_css_class'] : 'col-12 col-md-4 col-lg-3';

if (!empty($product_categories) && !is_wp_error($product_categories)) : ?>

    <div class="<?php echo esc_attr($_class) ?>" <?php if (!empty($data['id'])) : ?> id="<?php echo esc_attr($data['id']); ?>" <?php endif; ?>>
        <?php
        get_template_part('templates/core-blocks/heading', null, [
            'title_class' => 'category-grid__title',
            'description_class' => 'category-grid__description',
            'class' => 'category-grid__header',
            'title' => $data && !empty($data['title']) ? $data['title'] : '',
            'description' => $data && !empty($data['description']) ? $data['description'] : '',
        ]);
        ?>
        <div class="category-grid__wrapper">
            <?php foreach ($product_categories as $category) :
                $term_id = $category->term_id ?? 0;

                if (! $term_id || ! is_numeric($term_id) || strtolower($category->slug) === 'all') {
                    continue;
                }

                get_template_part(
                    'templates/blocks/category-grid-item',
                    null,
                    [
                        'term_id'   => $term_id,
                        'term_name' => $category->name ?? '',
                    ]
                );
            endforeach; ?>
        </div>

    </div>
<?php endif;
