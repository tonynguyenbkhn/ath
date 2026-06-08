<?php

if (!defined('ABSPATH')) {
    exit;
}

$data = wp_parse_args($args, [
    'class' => '',
    'post_id' => get_the_ID(),
    'date' => true,
    'author' => true,
    'categories' => true,
]);

$post_id = absint($data['post_id']);
$_class = 'post-meta';
$_class .= !empty($data['class']) ? ' ' . $data['class'] : '';
?>

<div class="<?php echo esc_attr($_class); ?>">
    <?php if ($data['date']) : ?>
        <span class="post-meta__item post-meta__date"><?php echo esc_html(get_the_date('', $post_id)); ?></span>
    <?php endif; ?>
    <?php if ($data['author']) : ?>
        <span class="post-meta__item post-meta__author"><?php echo esc_html(get_the_author_meta('display_name', (int) get_post_field('post_author', $post_id))); ?></span>
    <?php endif; ?>
    <?php if ($data['categories']) : ?>
        <?php $categories = get_the_category_list(', ', '', $post_id); ?>
        <?php if (!empty($categories)) : ?>
            <span class="post-meta__item post-meta__categories"><?php echo wp_kses_post($categories); ?></span>
        <?php endif; ?>
    <?php endif; ?>
</div>
