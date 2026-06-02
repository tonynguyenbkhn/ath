<?php

if (! defined('ABSPATH')) {
    exit;
}

$data = wp_parse_args(
    $args,
    [
        'hide_section' => '',
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

$_class = 'show-event';
$_class .= ! empty($data['class']) ? esc_attr(' ' . $data['class']) : '';

$_class_container = 'container';
$_class_container .= ! empty($data['class_container']) ? esc_attr(' ' . $data['class_container']) : '';

$product_ids = is_array($data['products']) ? array_values(array_filter(array_map('absint', $data['products']))) : [];
$slides = [];
$palette = [
    'event-card--theme-red',
    'event-card--theme-orange',
    'event-card--theme-purple',
    'event-card--theme-dark',
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
    $date_day = $timestamp ? wp_date('j', $timestamp) : '';
    $date_weekday = $timestamp ? strtoupper(wp_date('D', $timestamp)) : '';
    $date_month = $timestamp ? strtoupper(wp_date('M', $timestamp)) : '';
    $date_year = $timestamp ? wp_date('y', $timestamp) : '';

    ob_start();
    get_template_part(
        'templates/sections/show-event/item',
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
        'class'   => 'event-section__slide',
    ];
}

$has_intro = ! empty($data['title']) || ! empty($data['description']) || (! empty($data['button_text']) && ! empty($data['button_link']));

if (! $has_intro && empty($slides)) {
    return;
}
?>

<section class="<?php echo esc_attr($_class); ?>" <?php if (! empty($data['id'])) : ?> id="<?php echo esc_attr(sanitize_file_name(strtolower($data['id']))); ?>" <?php endif; ?>>

    <div class="show-event__viewport">
        <div class="show-event__track">
            <?php
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
            ?>
            <?php if ($data['enable_container']) : ?>
                <div class="<?php echo esc_attr($_class_container); ?>">
                <?php endif; ?>
                <div class="position-relative event-control-wrap">
                    <div class="event-control">
                        <div class="nav">
                            <div class="swiper-button swiper-button-prev"></div>
                            <div class="swiper-button swiper-button-next"></div>
                        </div>
                        <div class="swiper-pagination event-swiper-pagination"></div>
                    </div>
                    <div class="event-section__shell">
                        <div class="event-section__intro-row">
                            <div class="event-section__intro">
                                <?php
                                get_template_part(
                                    'templates/components/heading',
                                    null,
                                    [
                                        'title_class'       => 'event-section__title',
                                        'description_class' => 'event-section__description',
                                        'class'             => 'event-section__heading flex-column',
                                        'title'             => $data['title'],
                                        'description'       => '',
                                    ]
                                );
                                ?>
                            </div>
                            <div class="event-section__actions">
                                <div class="heading__description event-section__description">
                                    <?php echo wp_kses_post(wpautop($data['description'])); ?>
                                </div>

                                <?php if (! empty($data['button_text']) && ! empty($data['button_link'])) : ?>
                                    <?php
                                    get_template_part(
                                        'templates/components/button',
                                        null,
                                        [
                                            'class'              => 'event-section__button button-medium typo-system-button',
                                            'button_text'        => $data['button_text'],
                                            'button_url'         => $data['button_link'],
                                            'button_link_target' => '_self',
                                            'svg_icon_after'     => twmp_get_svg_icon('arrow-right'),
                                        ]
                                    );
                                    ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($data['enable_container']) : ?>
                </div>
            <?php endif; ?>
            <?php if ($data['enable_container']) : ?>
                <div class="<?php echo esc_attr($_class_container); ?> full-right">
                <?php endif; ?>
                <div class="event-section__shell">
                    <?php if (! empty($slides)) : ?>
                        <div class="event-section__slider-wrap position-relative">
                            <?php
                            get_template_part(
                                'templates/components/section-shape',
                                null,
                                [
                                    'type'  => 'triangle',
                                    'class' => 'show-event__shape show-event__shape--triangle',
                                ]
                            );
                            get_template_part(
                                'templates/components/section-shape',
                                null,
                                [
                                    'type'  => 'circle',
                                    'class' => 'show-event__shape show-event__shape--circle',
                                ]
                            );
                            ?>
                            <?php
                            get_template_part(
                                'templates/components/swiper',
                                null,
                                [
                                    'class'            => 'event-section__swiper',
                                    'data_block'       => 'show-event',
                                    'enable_container' => false,
                                    'settings'         => [
                                        'autoPlay'        => false,
                                        'prevNextButtons' => false,
                                        'pagination'      => false,
                                        'slidesPerView'   => 1.3,
                                        'spaceBetween'    => 24,
                                        'centeredSlides'  => true,
                                        'breakpoints'     => [
                                            640  => [
                                                'slidesPerView'  => 1.3,
                                                'spaceBetween'   => 24,
                                                'centeredSlides' => true,
                                            ],
                                            992  => [
                                                'slidesPerView'  => 2.3,
                                                'spaceBetween'   => 24,
                                                'centeredSlides' => true,
                                            ],
                                            1200 => [
                                                'slidesPerView'  => 3.3,
                                                'spaceBetween'   => 0,
                                                'centeredSlides' => false,
                                            ],
                                        ],
                                    ],
                                    'items'            => $slides,
                                ]
                            );
                            ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($data['enable_container']) : ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>