<?php

if (!defined('ABSPATH')) {
    exit;
}

$data = wp_parse_args($args, [
    'id' => '',
    'class' => '',
    'items' => [],
    'lazyload' => true,
    'enable_container' => false,
]);

if (empty($data['items']) || !is_array($data['items'])) {
    return;
}

$_class = 'section hero-slider';
$_class .= !empty($data['class']) ? ' ' . $data['class'] : '';
?>

<section class="<?php echo esc_attr($_class); ?>" <?php if (!empty($data['id'])) : ?>id="<?php echo esc_attr($data['id']); ?>"<?php endif; ?>>
    <?php if ($data['enable_container']) : ?><div class="container"><?php endif; ?>
        <div class="hero-slider__items">
            <?php foreach ($data['items'] as $item) : ?>
                <?php $image_id = absint($item['image'] ?? 0); ?>
                <?php if ($image_id) : ?>
                    <div class="hero-slider__item">
                        <?php echo wp_get_attachment_image($image_id, 'full', false, [
                            'class' => 'hero-slider__image',
                            'loading' => $data['lazyload'] ? 'lazy' : 'eager',
                        ]); ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php if ($data['enable_container']) : ?></div><?php endif; ?>
</section>
