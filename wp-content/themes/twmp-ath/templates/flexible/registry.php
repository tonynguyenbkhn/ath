<?php

if (!defined('ABSPATH')) {
    exit;
}

return [
    'hero-banner' => [
        'template' => 'templates/sections/hero-banner/section',
        'extra_fields' => [
            'enable_container' => false,
        ],
    ],
    'product-cat-grid' => [
        'template' => 'templates/sections/product-cat-grid/section',
        'extra_fields' => [
            'enable_container' => true,
        ],
    ],
    'about-us' => [
        'template' => 'templates/sections/about-us/section',
        'field_map' => [
            'image_id' => 'image',
            'secondary_image_id' => 'secondary_image',
        ],
        'fields' => [
            'counters',
            'primary_button_text',
            'primary_button_link',
            'secondary_button_text',
            'secondary_button_link',
        ],
        'extra_fields' => [
            'enable_container' => true,
        ],
    ]
];
