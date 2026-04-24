<?php

$data = wp_parse_args($args, [
    'title' => '',
    'description' => '',
    'image_id' => '',
]);
?>
<div class="col-md-4 collection-card">
    <?php
    get_template_part('templates/core-blocks/image', null, [
        'image_id' => $data['image_id'],
        'image_size' => 'full',
        'lazyload' => false,
        'class' => 'pe-none image--cover collection-card__immage image--default',
        'image_class' => ''
    ]);
    ?>
    <div class="collection-card__content">
        <span class="collection-card__content__tag"><?php echo esc_html( $data['description'] ); ?></span>
        <h3 class="collection-card__content__title"><?php echo esc_html( $data['title'] ); ?></h3>
    </div>
</div>