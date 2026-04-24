<?php

$data = wp_parse_args($args, [
    'id' => '',
    'class' => '',
    'items' => [],
    'enable_container' => false
]);

$_class = !empty($data['class']) ? ' ' . $data['class'] : '';

$_class_container = 'container';
$_class_container .= !empty($data['class_container']) ? esc_attr(' ' . $data['class_container']) : '';

if (!empty($data['items'])) : ?>
    <div class="<?php echo esc_attr($_class) ?>" <?php if (!empty($data['id'])) : ?> id="<?php echo esc_attr($data['id']); ?>" <?php endif; ?>>
        <?php if ($data['enable_container']) : ?><div class="<?php echo esc_attr($_class_container); ?>"><?php endif; ?>
            <?php
            get_template_part('templates/core-blocks/heading', null, [
                'title_class' => 'icon-grid__title',
                'description_class' => 'icon-grid__description',
                'class' => 'icon-grid__header',
                'title' => $data && !empty($data['title']) ? $data['title'] : '',
                'description' => $data && !empty($data['description']) ? $data['description'] : '',
            ]);
            ?>
            <div class="row">
                <?php foreach ($data['items'] as $item) :
                ?>
                    <div class="mb-1 position-relative col icon-grid__col">
                        <?php get_template_part('templates/blocks/icon-block', null, $item); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($data['enable_container']) : ?>
            </div><?php endif; ?>
    </div>
<?php endif;
