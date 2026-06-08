<?php

if (!defined('ABSPATH')) {
    exit;
}

$data = wp_parse_args($args, [
    'class' => '',
    'post_data' => null,
    'post_id' => 0,
    'view_more_button' => esc_html__('Read More...', 'twmp-ath'),
    'options' => [],
]);

$post = $data['post_data'] instanceof WP_Post ? $data['post_data'] : get_post(absint($data['post_id']));

if (!$post instanceof WP_Post) {
    return;
}

$post_id = $post->ID;
$_class = !empty($data['class']) ? $data['class'] : '';
?>

<div class="<?php echo esc_attr($_class); ?>">
    <article class="post-card">
        <?php if (has_post_thumbnail($post_id)) : ?>
            <a class="post-card__image" href="<?php echo esc_url(get_permalink($post_id)); ?>">
                <?php echo get_the_post_thumbnail($post_id, 'large', ['class' => 'post-card__img']); ?>
            </a>
        <?php endif; ?>
        <div class="post-card__content">
            <?php get_template_part('templates/components/post-meta', null, [
                'post_id' => $post_id,
                'class' => 'post-card__post-meta',
            ]); ?>
            <h2 class="post-card__title">
                <a href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a>
            </h2>
            <div class="post-card__excerpt">
                <?php echo esc_html(wp_trim_words(get_the_excerpt($post_id), 24)); ?>
            </div>
            <?php if (!empty($data['view_more_button'])) : ?>
                <a class="post-card__button" href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html($data['view_more_button']); ?></a>
            <?php endif; ?>
        </div>
    </article>
</div>
