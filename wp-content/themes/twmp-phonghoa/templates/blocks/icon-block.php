<?php

$data = wp_parse_args($args, [
    'image_id' => '',
    'title' => '',
    'url' => '',
    'lazyload' => false,
    'image_size' => 'full'
]);

$_class = 'text-decoration-none text-dark d-flex icon-text';
$_class .= !empty($data['class']) ? esc_attr(' ' . $data['class']) : '';

$_title_class = 'icon-block__title';
$_title_class .= !empty($data['title_class']) ? esc_attr(' ' . $data['title_class']) : ' w-100';

$_description_class = 'icon-block__description';
$_description_class .= !empty($data['description_class']) ? esc_attr(' ' . $data['description_class']) : ' w-100';

?>

<a class="<?php echo esc_attr($_class); ?>" title="<?php echo esc_attr($data['title']); ?>" href="<?php echo esc_attr($data['url']); ?>">
    <span class="d-inline-block text-center mb-1 icon-block__image-wrapper" aria-hidden="true">
        <?php
        get_template_part('templates/core-blocks/image', null, [
            'image_id' => $data['image_id'],
            'image_size' => $data['image_size'],
            'lazyload' => $data['lazyload'],
            'class' => 'pe-none image--cover hero-slider-item__image image--default'
        ]);
        ?>
    </span>
    <span class="d-flex flex-column icon-text__content">
        <?php if ($data['title']): ?>
            <span class="<?php echo esc_attr($_title_class); ?>"><?php echo esc_html($data['title']); ?></span>
        <?php endif; ?>
        <?php if ($data['description']): ?>
            <span class="<?php echo esc_attr($_description_class); ?>"><?php echo esc_html($data['description']); ?></span>
        <?php endif; ?>
    </span>
</a>