<?php

$data = wp_parse_args($args, [
    'id' => '',
    'class' => '',
    'items' => [],
    'enable_container' => false,
    'block_layout' => '4-col',
    'view_more_button' => '',
    'query' => null
]);

$_class = !empty($data['class']) ? ' ' . $data['class'] : '';

$_class_container = 'container';
$_class_container .= !empty($data['class_container']) ? esc_attr(' ' . $data['class_container']) : '';

$selected_posts = $data['items'];

$block_layout        = $data['block_layout'];
$post_args           = array(
    'post_type'              => 'post',
    'post_status'            => 'publish',
    'update_post_meta_cache' => false,
    'update_post_term_cache' => false,
    'no_found_rows'          => true,
);

if (! empty($selected_posts)) {
    $post_args['post__in'] = $selected_posts;
    $post_args['orderby']  = 'post__in';
} else {
    $post_args['posts_per_page'] = 12;
}

$post_query = $data['query'] ? $data['query'] : new WP_Query($post_args);

$grid_css_class = '';
if ($block_layout === '3-col') {
    $grid_css_class .= 'col-lg-4 col-md-4 col-sm-6 col-12';
} elseif ($block_layout === '4-col') {
    $grid_css_class .= 'col-lg-3 col-md-4 col-sm-6 col-12';
} elseif ($block_layout === '1-col') {
    $grid_css_class .= 'col-lg-12 col-md-12 col-sm-12 col-12';
} elseif ($block_layout === '2-col') {
    $grid_css_class .= 'col-lg-6 col-md-6 col-sm-6 col-12';
}

if ($post_query->have_posts()) :
?>

    <div class="<?php echo esc_attr($_class) ?>" <?php if (!empty($data['id'])) : ?> id="<?php echo esc_attr($data['id']); ?>" <?php endif; ?>>
        <?php if ($data['enable_container']) : ?><div class="<?php echo esc_attr($_class_container); ?>"><?php endif; ?>
            <?php
            get_template_part('templates/core-blocks/heading', null, [
                'title' => $data && !empty($data['title']) ? $data['title'] : '',
                'description' => $data && !empty($data['description']) ? $data['description'] : '',
                'title_class' => 'text-center',
                'description_class' => 'text-center'
            ]);
            ?>
            <div class="row">
                <?php
                while ($post_query->have_posts()) :
                    $post_query->the_post();
                ?>
                    <div class="<?php echo esc_attr($grid_css_class); ?>">
                        <?php
                        get_template_part(
                            'templates/blocks/post-card',
                            null,
                            array(
                                'post_data' => get_post(get_the_ID()),
                                'post_id' => get_the_ID(),
                                'view_more_button' => $data['view_more_button'],
                                'post_title_limit' => 20,
                                'options' => [
                                    'show_excerpt' => false,
                                    'show_date' => true,
                                    'show_author' => true,
                                    'show_categories' => true
                                ]
                            )
                        );
                        ?>
                    </div>
                <?php
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
            <?php if ($data['enable_container']) : ?>
            </div><?php endif; ?>
    </div>
<?php
endif;
