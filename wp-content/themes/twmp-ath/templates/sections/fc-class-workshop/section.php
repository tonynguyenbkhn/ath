<?php

if (! defined('ABSPATH')) {
    exit;
}

$data = wp_parse_args(
    $args,
    [
        'hide_section'     => '',
        'id'               => '',
        'class'            => '',
        'class_container'  => '',
        'title'            => '',
        'description'      => '',
        'button_text'      => '',
        'button_link'      => '',
        'products'         => [],
        'enable_container' => false,
    ]
);

if ($data['hide_section']) {
    return;
}

$_class = 'fc-class';
$_class .= ! empty($data['class']) ? esc_attr(' ' . $data['class']) : '';

$_class_container = 'container';
$_class_container .= ! empty($data['class_container']) ? esc_attr(' ' . $data['class_container']) : '';

$product_ids = is_array($data['products']) ? array_values(array_filter(array_map('absint', $data['products']))) : [];
$slides = [];
$palette = [
    'class-card--theme-red',
    'class-card--theme-orange',
    'class-card--theme-purple',
    'class-card--theme-dark',
];

foreach ($product_ids as $index => $product_id) {
    $product_post = get_post($product_id);

    if (! $product_post instanceof WP_Post || 'publish' !== $product_post->post_status) {
        continue;
    }

    $badges = function_exists('get_field') ? get_field('ath_badges', $product_id) : [];
    $short_info = function_exists('get_field') ? (string) get_field('ath_short_info', $product_id) : '';
    $location_detail = function_exists('twmp_get_taxonomy_term_names') ? twmp_get_taxonomy_term_names($product_id, 'ath_venue') : '';
    $location = function_exists('get_field') ? (string) get_field('ath_location', $product_id) : '';
    $description_source = trim(wp_strip_all_tags((string) $product_post->post_content));
    $description = '';

    if ('' !== $description_source) {
        $description = wp_trim_words($description_source, 18, '...');
    } elseif (! empty($product_post->post_excerpt)) {
        $description = wp_trim_words(wp_strip_all_tags($product_post->post_excerpt), 18, '...');
    }

    $timestamp = get_post_timestamp($product_post);
    $next_range = function_exists('twmp_get_next_event_datetime_range') ? twmp_get_next_event_datetime_range($product_id) : [];
    $timestamp = ! empty($next_range['start_timestamp']) ? absint($next_range['start_timestamp']) : $timestamp;
    $date_day = $timestamp ? wp_date('j', $timestamp) : '';
    $date_weekday = $timestamp ? strtoupper(wp_date('D', $timestamp)) : '';
    $date_month = $timestamp ? strtoupper(wp_date('M', $timestamp)) : '';
    $date_year = $timestamp ? wp_date('y', $timestamp) : '';

    ob_start();
    get_template_part(
        'templates/sections/fc-class-workshop/item',
        null,
        [
            'product_id'     => $product_id,
            'title'          => get_the_title($product_post),
            'badges'         => is_array($badges) ? $badges : [],
            'short_info'     => $short_info,
            'location'       => trim($location_detail) ? $location_detail : $location,
            'description'    => $description,
            'date_day'       => $date_day,
            'date_weekday'   => $date_weekday,
            'date_month'     => $date_month,
            'date_year'      => $date_year,
            'image_id'       => get_post_thumbnail_id($product_id),
            'image_size'     => 'large',
            'lazyload'       => true,
            'permalink'      => get_permalink($product_id),
            'featured'       => 0 === $index,
            'theme_class'    => $palette[$index % count($palette)],
        ]
    );
    $slides[] = [
        'content' => ob_get_clean(),
        'class'   => 'fc-class-section__slide',
    ];
}

$has_intro = ! empty($data['title']) || ! empty($data['description']) || (! empty($data['button_text']) && ! empty($data['button_link']));

$section_id = sanitize_file_name(strtolower((string) $data['id']));

if (! $has_intro && empty($slides)) {
    return;
}
?>

<section class="position-relative <?php echo esc_attr($_class); ?>" <?php if (! empty($data['id'])) : ?> id="<?php echo esc_attr(sanitize_file_name(strtolower($data['id']))); ?>" <?php endif; ?>>
    <?php
    if ('coaching-team-development' === $section_id):
        get_template_part(
            'templates/components/image-light',
            null,
            [
                'side' => 'right',
                'src' => TWMP_IMG_URI . '/show-event-light.png',
                'alt' => $data && !empty($data['title']) ? $data['title'] : '',
                'width' => 898,
                'height' => 965,
            ]
        );
    endif;
    if ('awareness-performances' === $section_id):
        get_template_part(
            'templates/components/image-light',
            null,
            [
                'side' => 'left',
                'src' => TWMP_IMG_URI . '/our-team-light.png',
                'alt' => $data && !empty($data['title']) ? $data['title'] : '',
                'width' => 1095,
                'height' => 941,
            ]
        );
    endif;
    ?>
    <div class="container">
        <?php
        get_template_part(
            'templates/components/heading',
            null,
            [
                'title_class'       => 'fc-class-section__title',
                'description_class' => 'fc-class-section__description',
                'class'             => 'fc-class-section__heading flex-column',
                'title'             => $data['title'],
                'description'       => ''
            ]
        );
        ?>
    </div>
    <?php if ($data['enable_container']) : ?>
        <div class="<?php echo esc_attr($_class_container); ?>">
        <?php endif; ?>
        <div class="fc-class-section__shell">

            <?php if (! empty($slides)) : ?>
                <div class="fc-class-section__slider-wrap position-relative">
                    <div class="fc-class-section__slider">
                        <div class="fc-class-section-control">
                            <div class="nav">
                                <div class="swiper-button swiper-button-prev"></div>
                                <div class="swiper-button swiper-button-next"></div>
                            </div>
                            <div class="swiper-pagination class-section-swiper-pagination"></div>
                        </div>
                        <?php
                        get_template_part(
                            'templates/components/swiper',
                            null,
                            [
                                'class'            => 'fc-class-section__swiper',
                                'data_block'       => 'fc-class-workshop',
                                'enable_container' => false,
                                'settings'         => [
                                    'autoPlay'        => false,
                                    'pagination'      => false,
                                    'prevNextButtons' => false,
                                    'slidesPerView'   => 1.3,
                                    'spaceBetween'    => 24,
                                    'centeredSlides'    => true,
                                    'breakpoints'     => [
                                        640  => [
                                            'slidesPerView' => 2,
                                            'spaceBetween'  => 32,
                                            'centeredSlides'    => true,
                                        ],
                                        992  => [
                                            'slidesPerView' => 2.3,
                                            'spaceBetween'  => 32,
                                            'centeredSlides'    => false,
                                        ],
                                        1200 => [
                                            'slidesPerView' => 1.8,
                                            'spaceBetween'  => 0,
                                            'centeredSlides'    => false,
                                        ],
                                    ],
                                ],
                                'items'            => $slides,
                            ]
                        );
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="fc-class-section__intro-row">
                <div class="fc-class-section__intro-row-wrapper">
                    <div class="fc-class-section__intro">
                        <?php
                        get_template_part(
                            'templates/components/heading',
                            null,
                            [
                                'title_class'       => 'fc-class-section__title',
                                'description_class' => 'fc-class-section__description',
                                'class'             => 'fc-class-section__heading flex-column',
                                'title'             => '',
                                'description'       => $data['description']
                            ]
                        );
                        ?>
                    </div>
                    <div class="fc-class-section__actions">
                        <?php if (! empty($data['button_text']) && ! empty($data['button_link'])) : ?>

                            <?php
                            get_template_part(
                                'templates/components/button',
                                null,
                                [
                                    'class'              => 'fc-class-section__button button-medium typo-system-button',
                                    'button_text'        => $data['button_text'],
                                    'button_url'         => $data['button_link'],
                                    'button_link_target' => '_self',
                                    'svg_icon_after'     => twmp_get_svg_icon('arrow-right'),
                                ]
                            );
                            ?>
                    </div>
                </div>
            <?php endif; ?>
            </div>
        </div>

        <?php if ($data['enable_container']) : ?>
        </div>
    <?php endif; ?>
</section>
