<?php

return [
    'hero-slider' => [
        'template' => 'templates/sections/hero-slider/section',
        'extra_fields' => [
            'enable_container' => false,
        ],
    ],
    'hero-slider-nav' => [
        'template' => 'templates/sections/hero-slider-nav',
        'extra_fields' => [
            'enable_container' => true,
        ],
    ],
    'collection-grid' => [
        'template' => 'templates/sections/collection-grid',
        'extra_fields' => [
            'enable_container' => false,
        ],
    ],
    'logo-slider' => [
        'template' => 'templates/sections/logo-slider/section',
        'extra_fields' => [
            'enable_container' => true,
        ],
    ],
    'post-grid' => [
        'template' => 'templates/sections/post-grid/section',
        'extra_fields' => [
            'enable_container' => true,
            'view_more_button' => '',
        ],
    ],
    'post-grid-slider' => [
        'template' => 'templates/sections/post-grid-slider',
        'fields' => ['button'],
        'extra_fields' => [
            'enable_container' => true,
            'block_layout' => '4-col',
        ],
    ],
    'icon-grid' => [
        'template' => 'templates/sections/icon-grid',
        'extra_fields' => [
            'enable_container' => true,
        ],
    ],
    'product-grid' => [
        'template' => 'templates/sections/product-grid/section',
        'fields' => ['button'],
        'extra_fields' => [
            'enable_container' => true,
            'block_layout' => '5-col',
        ],
    ],
    'product-grid-slider' => [
        'template' => 'templates/sections/product-grid-slider',
        'fields' => ['button'],
        'extra_fields' => [
            'enable_container' => true,
            'block_layout' => '4-col',
        ],
    ],
    'product-grid-slider-flashsale' => [
        'template' => 'templates/sections/product-grid-slider-flashsale',
        'fields' => ['button'],
        'extra_fields' => [
            'enable_container' => true,
            'block_layout' => '4-col',
        ],
    ],
    'product-tabs' => [
        'template' => 'templates/sections/product-tabs',
        'field_map' => [
            'posts_per_page' => 'numbers',
            'query_type' => 'attribute',
        ],
        'extra_fields' => [
            'enable_container' => true,
            'block_layout' => '4-col',
        ],
    ],
    'product-tabs-category' => [
        'template' => 'templates/sections/product-tabs-category',
        'field_map' => [
            'posts_per_page' => 'numbers',
            'query_type' => 'attribute',
        ],
        'extra_fields' => [
            'enable_container' => true,
            'block_layout' => '4-col',
        ],
    ],
    'product-tabs-slider' => [
        'template' => 'templates/sections/product-tabs-slider',
        'field_map' => [
            'posts_per_page' => 'numbers',
            'query_type' => 'attribute',
        ],
        'extra_fields' => [
            'enable_container' => true,
            'block_layout' => '4-col',
        ],
    ],
    'testimonials' => [
        'template' => 'templates/sections/testimonials/section',
        'extra_fields' => [
            'enable_container' => true,
        ],
    ],
    'category-card' => [
        'template' => 'templates/sections/category-card',
        'fields' => ['term_id'],
        'extra_fields' => [
            'enable_container' => true,
        ],
    ],
    'category-grid' => [
        'template' => 'templates/sections/category-grid/section',
        'extra_fields' => [
            'enable_container' => true,
            'block_layout' => '4-col',
        ],
    ],
    'category-grid-slider' => [
        'template' => 'templates/sections/category-grid-slider',
        'extra_fields' => [
            'enable_container' => true,
            'block_layout' => '4-col',
        ],
    ],
    'two-up-intro' => [
        'template' => 'templates/sections/two-up-intro',
        'fields' => ['image_id', 'content'],
        'extra_fields' => [
            'enable_container' => true,
        ],
    ],
    'newsletter' => [
        'template' => 'templates/sections/newsletter',
        'fields' => ['gallery'],
        'extra_fields' => [
            'enable_container' => false,
        ],
    ],
    'instagram-gallery' => [
        'template' => 'templates/sections/instagram-gallery',
        'fields' => ['gallery'],
        'extra_fields' => [
            'enable_container' => false,
        ],
    ],
    'image-link' => [
        'template' => 'templates/sections/image-link',
        'extra_fields' => [
            'enable_container' => true,
        ],
    ],
];
