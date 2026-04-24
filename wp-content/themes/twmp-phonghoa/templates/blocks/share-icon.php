<?php 

$data = wp_parse_args($args, [
    'class' => '',
]);

$_class = 'share-icon';
$_class .= !empty($data['class']) ? ' ' . $data['class'] : '';

global $post;
$share_url = urlencode(get_permalink($post));
$share_title = urlencode(get_the_title($post));
?>
<div class="<?php echo esc_attr($_class) ?>" data-block="share-icon">
    <div class="d-flex align-items-center share-icon__wrapper">
        <a class="share-icon__facebook" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_url($share_url); ?>"
           title="<?php echo esc_attr__( 'Share on Facebook', 'twmp-phonghoa' ) ?>">
            <?php echo twmp_get_svg_icon('share-facebook'); ?>
        </a>

        <a class="share-icon__twitter" href="https://twitter.com/intent/tweet?url=<?php echo esc_url($share_url); ?>&text=<?php echo esc_attr($share_title); ?>"
           title="<?php echo esc_attr__( 'Share on Twitter', 'twmp-phonghoa' ) ?>">
            <?php echo twmp_get_svg_icon('share-twitter'); ?>
        </a>

        <a class="share-icon__linkedin" href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo esc_url($share_url); ?>"
           title="<?php echo esc_attr__( 'Share on LinkedIn', 'twmp-phonghoa' ) ?>">
            <?php echo twmp_get_svg_icon('share-linkedin'); ?>
        </a>
    </div>
</div>
