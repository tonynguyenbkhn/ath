<?php

$data = wp_parse_args($args, [
    'id' => '',
    'class' => '',
    'lazyload' => false,
    'image_id' => '',
    'image_size' => 'full',
    'content' => '',
    'title' => '',
    'description' => '',
    'enable_container' => false
]);

$_class = !empty($data['class']) ? esc_attr(' ' . $data['class']) : '';

$_class_container = 'container';
$_class_container .= !empty($data['class_container']) ? esc_attr(' ' . $data['class_container']) : '';

?>
<div class="<?php echo esc_attr($_class); ?>" <?php if (!empty($data['id'])) : ?> id="<?php echo esc_attr($data['id']); ?>" <?php endif; ?>>
<?php if ($data['enable_container']) : ?><div class="<?php echo esc_attr($_class_container); ?>"><?php endif; ?>
    <?php
    get_template_part('templates/core-blocks/heading', null, [
        'title_class' => 'two-up-intro__title',
        'description_class' => 'two-up-intro__description',
        'class' => 'two-up-intro__header',
        'title' => $data['title'],
        'description' => $data['description'],
    ]);
    ?>
    <div class="row two-up-intro__row">
        <div class="col two-up-intro__col">
            <?php
            get_template_part('templates/core-blocks/image', null, [
                'image_id' => $data['image_id'],
                'image_size' => $data['image_size'],
                'lazyload' => $data['lazyload'],
                'class' => 'pe-none image--cover two-up-intro-item__image image--default'
            ]);
            ?>
        </div>
        <div class="col two-up-intro__col">
            <div class="two-up-intro__inner">
                <?php echo wp_kses_post($data['content']); ?>
            </div>
        </div>
    </div>
    <?php if ($data['enable_container']) : ?></div><?php endif; ?>
</div>