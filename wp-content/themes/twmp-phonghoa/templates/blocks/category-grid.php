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

$grid_css_class = $data['grid_css_class'] ? $data['grid_css_class'] : 'col-12 col-md-4 col-lg-3';

if (!empty($product_categories) && !is_wp_error($product_categories)) : ?>

    <div class="<?php echo esc_attr($_class) ?>" <?php if (!empty($data['id'])) : ?> id="<?php echo esc_attr($data['id']); ?>" <?php endif; ?>>
        <?php if ($data['enable_container']) : ?><div class="<?php echo esc_attr($_class_container); ?>"><?php endif; ?>
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

                    if (! $term_id || ! is_numeric($term_id)) {
                        continue;
                    }

                    $thumbnail_id = get_term_meta($term_id, 'thumbnail_id', true);

                    if (! $thumbnail_id) {
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
            <?php if ($data['enable_container']) : ?>
            </div><?php endif; ?>
    </div>
<?php endif;
