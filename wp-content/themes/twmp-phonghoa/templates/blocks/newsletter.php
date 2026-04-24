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
        <div class="newsletter__inner">
            <?php
            get_template_part('templates/core-blocks/heading', null, [
                'title' => $data && !empty($data['title']) ? $data['title'] : '',
                'description' => $data && !empty($data['description']) ? $data['description'] : '',
                'title_class' => 'text-center',
                'description_class' => 'text-center'
            ]);
            ?>
            <div class="newsletter__form">
                <?php echo do_shortcode('[contact-form-7 id="c48cf19" title="Sign Up For Our Newsletter"]'); ?>
            </div>
        </div>
        <div class="row newsletter__row">
            <?php if ($data['gallery']): ?><?php foreach ($data['gallery'] as $image): ?>
            <div class="col-md-6 col-sm-12 col-12">
                <?php
                                                get_template_part('templates/core-blocks/image', null, [
                                                    'image_id' => $image,
                                                    'image_size' => $data['image_size'],
                                                    'lazyload' => $data['lazyload'],
                                                    'class' => 'image--cover newsletter__figure image--default',
                                                    'image_class' => 'newsletter__image'
                                                ]);
                ?>
            </div>
    <?php endforeach;
                                        endif; ?>
        </div>
        <?php if ($data['enable_container']) : ?>
        </div><?php endif; ?>
</div>