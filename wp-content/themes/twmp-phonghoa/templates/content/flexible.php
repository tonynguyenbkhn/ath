<?php
if (have_rows('sections')) {
    $layouts = [
        'hero-slider' => ['extra_fields' => ['enable_container' => false]],
        'hero-slider-nav' => ['extra_fields' => ['enable_container' => true]],
        'collection-grid' => ['extra_fields' => ['enable_container' => false]],
        'logo-slider' => ['extra_fields' => ['enable_container' => true]],
        'post-grid' => ['extra_fields' => ['enable_container' => true, 'view_more_button' => '']],
        'post-grid-slider' => ['fields' => ['button'], 'extra_fields' => ['enable_container' => true, 'block_layout' => '4-col']],
        'icon-grid' => ['extra_fields' => ['enable_container' => true]],
        'product-grid' => ['fields' => ['button'], 'extra_fields' => ['enable_container' => true, 'block_layout' => '5-col']],
        'product-grid-slider' => ['fields' => ['button'], 'extra_fields' => ['enable_container' => true, 'block_layout' => '4-col']],
        'product-grid-slider-flashsale' => ['fields' => ['button'], 'extra_fields' => ['enable_container' => true, 'block_layout' => '4-col']],
        'product-tabs' => ['extra_fields' => ['enable_container' => true, 'block_layout' => '4-col']],
        'product-tabs-category' => ['fields' => ['button'], 'extra_fields' => ['enable_container' => true, 'block_layout' => '4-col']],
        'product-tabs-slider' => ['extra_fields' => ['enable_container' => true, 'block_layout' => '4-col']],
        'testimonials' => ['extra_fields' => ['enable_container' => true]],
        'category-card' => ['fields' => ['term_id'], 'extra_fields' => ['enable_container' => true]],
        'category-grid' => ['extra_fields' => ['enable_container' => true, 'block_layout' => '4-col']],
        'category-grid-slider' => ['extra_fields' => ['enable_container' => true, 'block_layout' => '4-col']],
        'two-up-intro' => ['fields' => ['image_id', 'content'], 'extra_fields' => ['enable_container' => true]],
        'newsletter' =>  ['fields' => ['gallery'], 'extra_fields' => ['enable_container' => false]],
        'instagram-gallery' =>  ['fields' => ['gallery'], 'extra_fields' => ['enable_container' => false]],
        'image-link' =>  ['extra_fields' => ['enable_container' => true]],
    ];

    while (have_rows('sections')) : the_row();
        $layout = get_row_layout();

        if (!isset($layouts[$layout])) continue;

        $base_fields = ['id' => 'section_id', 'title' => 'title', 'description' => 'description', 'items' => 'items'];
        if (!empty($layouts[$layout]['fields'])) {
            foreach ($layouts[$layout]['fields'] as $field) {
                $base_fields[$field] = $field;
            }
        }

        $data = twmp_get_flexible_content_data($base_fields);

        $data['class'] = "section $layout mb-3";

        if (!empty($layouts[$layout]['extra_fields'])) {
            foreach ($layouts[$layout]['extra_fields'] as $key => $value) {
                if (is_int($key)) {
                    $data[$value] = get_sub_field($value);
                } else {
                    $data[$key] = $value;
                }
            }
        }

        if (!empty($data['enable_container'])) {
            $data['class_container'] = "{$layout}__main-container";
        }

        get_template_part("templates/blocks/$layout", null, $data);
    endwhile;
}
