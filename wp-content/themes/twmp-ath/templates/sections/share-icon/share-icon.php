<?php

if (!defined('ABSPATH')) {
    exit;
}

$data = wp_parse_args($args, [
    'class' => '',
    'post_id' => get_the_ID(),
]);

$post_id = absint($data['post_id']);
$url = get_permalink($post_id);
$title = get_the_title($post_id);
$_class = 'share-icon';
$_class .= !empty($data['class']) ? ' ' . $data['class'] : '';
?>

<div class="<?php echo esc_attr($_class); ?>">
    <div class="share-icon__wrapper">
        <a class="share-icon__link" href="<?php echo esc_url('https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($url)); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('Share on Facebook', 'twmp-ath'); ?>">
            <?php echo twmp_get_svg_icon('facebook'); ?>
        </a>
        <a class="share-icon__link" href="<?php echo esc_url('https://twitter.com/intent/tweet?url=' . rawurlencode($url) . '&text=' . rawurlencode($title)); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('Share on X', 'twmp-ath'); ?>">
            <?php echo twmp_get_svg_icon('twitter'); ?>
        </a>
        <a class="share-icon__link" href="<?php echo esc_url('https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode($url)); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('Share on LinkedIn', 'twmp-ath'); ?>">
            <?php echo twmp_get_svg_icon('linkedin'); ?>
        </a>
    </div>
</div>
