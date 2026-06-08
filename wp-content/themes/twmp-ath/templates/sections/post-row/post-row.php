<?php

if (!defined('ABSPATH')) {
    exit;
}

$data = wp_parse_args($args, [
    'class' => '',
    'post_data' => null,
    'post_id' => 0,
    'view_more_button' => esc_html__('Read More...', 'twmp-ath'),
    'post_excerpt_limit' => 24,
    'options' => [],
]);

$post = $data['post_data'] instanceof WP_Post ? $data['post_data'] : get_post(absint($data['post_id']));

if (!$post instanceof WP_Post) {
    return;
}

$post_id = $post->ID;
$options = wp_parse_args($data['options'], [
    'show_excerpt' => true,
    'show_date' => true,
    'show_author' => false,
    'show_categories' => true,
]);
$_class = 'post-row';
$_class .= !empty($data['class']) ? ' ' . $data['class'] : '';
?>

<article class="<?php echo esc_attr($_class); ?>">
    <div class="post-row__wrapper">
        <?php if (has_post_thumbnail($post_id)) : ?>
            <a class="post-row__image" href="<?php echo esc_url(get_permalink($post_id)); ?>">
                <?php echo get_the_post_thumbnail($post_id, 'medium_large', ['class' => 'post-row__img']); ?>
            </a>
        <?php endif; ?>
        <div class="post-row__content">
            <?php get_template_part('templates/components/post-meta', null, [
                'post_id' => $post_id,
                'date' => $options['show_date'],
                'author' => $options['show_author'],
                'categories' => $options['show_categories'],
                'class' => 'post-row__post-meta',
            ]); ?>
            <h2 class="post-row__title">
                <a href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a>
            </h2>
            <?php if ($options['show_excerpt']) : ?>
                <div class="post-row__excerpt">
                    <?php echo esc_html(wp_trim_words(get_the_excerpt($post_id), absint($data['post_excerpt_limit']))); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($data['view_more_button'])) : ?>
                <a class="post-row__button" href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html($data['view_more_button']); ?></a>
            <?php endif; ?>
        </div>
    </div>
</article>
