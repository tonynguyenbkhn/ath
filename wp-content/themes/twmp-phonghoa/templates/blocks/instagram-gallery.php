<?php

$data = wp_parse_args($args, [
    'id' => '',
    'class' => '',
    'title' => '',
    'description' => '',
    'gallery' => [],
    'enable_container' => false,
    'lazyload' => false
]);

$_class = !empty($data['class']) ? ' ' . $data['class'] : '';

$_class_container = 'container';
$_class_container .= !empty($data['class_container']) ? esc_attr(' ' . $data['class_container']) : '';

?>

<div class="<?php echo esc_attr($_class) ?> position-relative" <?php if (!empty($data['id'])) : ?> id="<?php echo esc_attr($data['id']); ?>" <?php endif; ?>>
    <?php if ($data['enable_container']) : ?><div class="<?php echo esc_attr($_class_container); ?>"><?php endif; ?>
        <?php
        get_template_part('templates/core-blocks/heading', null, [
            'title' => $data && !empty($data['title']) ? $data['title'] : '',
            'description' => $data && !empty($data['description']) ? $data['description'] : '',
            'title_class' => 'text-center',
            'description_class' => 'text-center'
        ]);
        ?>
        <div class="row instagram-gallery__row">
            <?php if ($data['gallery']): ?><?php foreach ($data['gallery'] as $image): ?>
            <div class="col">
                <a href="<?php echo esc_url(wp_get_attachment_url($image)) ?>" class="instagram-gallery__link position-relative" data-fancybox="gallery">
                    <?php
                                                get_template_part('templates/core-blocks/image', null, [
                                                    'image_id' => $image,
                                                    'image_size' => $data['image_size'],
                                                    'lazyload' => $data['lazyload'],
                                                    'class' => 'image--cover instagram-gallery__figure image--default',
                                                    'image_class' => 'instagram-gallery__image'
                                                ]);
                    ?>
                    <div class="instagram-gallery__overlay overlay-common"></div>
                    <?php echo twmp_get_svg_icon('view'); ?>
                </a>
            </div>
    <?php endforeach;
                                        endif; ?>
        </div>
        <?php if ($data['enable_container']) : ?>
        </div><?php endif; ?>
</div>