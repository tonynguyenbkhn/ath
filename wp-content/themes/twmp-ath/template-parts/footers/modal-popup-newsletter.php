<?php

if (!defined('ABSPATH')) {
    exit;
}

$data = wp_parse_args($args, [
    'class' => '',
    'id' => '',
    'attributes' => '',
    'close_button_class' => ''
]);

$_class = 'modal modal--popup-newsletter';
$_class .= !empty( $data['class'] ) ? ' ' . $data['class'] : '';

$_id = !empty($data['id']) ? $data['id'] : 'modal-popup-newsletter';
$_custom_attributes = !empty($data['attributes']) ? $data['attributes'] : '';

$_close_button_class = !empty($data['close_button_class']) ? $data['close_button_class'] : 'js-close-button';

if ( !function_exists('get_field') ) {
    return;
}

$title = get_field('ath_newsletter_title', 'option');
$posts = get_field('ath_newsletter_post', 'option'); // relationship returns array of IDs when return_format = 'id'

// normalize posts to array of ints
$post_ids = [];
if ( !empty($posts) ) {
    if ( is_array($posts) ) {
        foreach ( $posts as $p ) {
            if ( $p instanceof WP_Post ) {
                $post_ids[] = absint( $p->ID );
            } else {
                $post_ids[] = absint( $p );
            }
        }
    } else {
        if ( $posts instanceof WP_Post ) {
            $post_ids[] = absint( $posts->ID );
        } else {
            $post_ids[] = absint( $posts );
        }
    }
}

// build slides using product card renderer for consistency
$slides = [];
if ( ! empty( $post_ids ) && function_exists( 'twmp_render_product_card' ) ) {
    global $product, $post;
    $previous_product = $product;
    $previous_post = $post;

    foreach ( $post_ids as $pid ) {
        if ( ! function_exists( 'wc_get_product' ) ) continue;
        $related_product = wc_get_product( $pid );
        if ( ! $related_product instanceof WC_Product ) continue;

        $product = $related_product;
        $post = get_post( $pid );

        ob_start();
        twmp_render_product_card();
        $slide_html = ob_get_clean();

        if ( '' === trim( (string) $slide_html ) ) continue;

        $slides[] = [
            'content' => $slide_html,
            'class'   => 'newsletter-section__slide',
        ];
    }

    $product = $previous_product;
    $post = $previous_post;
}

?>

<div class="<?php echo esc_attr( $_class ); ?>" id="<?php echo esc_attr( $_id ); ?>" role="dialog" <?php echo esc_attr( $_custom_attributes ); ?> data-block="popup-newsletter">
    <div class="modal__wrapper" style="max-width:1042px;">
        <div class="modal__header">
            <?php if ( $title ) : ?>
                <span class="modal__title typo-display-sm-medium"><?php echo esc_html( $title ); ?></span>
            <?php else : ?>
                <span class="modal__title typo-display-sm-medium"><?php esc_html_e('Newsletter', 'twmp-ath'); ?></span>
            <?php endif; ?>
        </div>

        <div class="modal__content js-content">
            <?php
            if ( ! empty( $slides ) ) :
                get_template_part(
                    'templates/components/swiper',
                    null,
                    [
                        'class'            => 'modal-newsletter__swiper',
                        'data_block'       => 'popup-newsletter',
                        'enable_container' => false,
                        'settings'         => [
                            'autoPlay'        => false,
                            'pagination'      => false,
                            'prevNextButtons' => true,
                            'slidesPerView'   => 3,
                            'spaceBetween'    => 24,
                            'breakpoints'     => [
                                640  => [ 'slidesPerView' => 1 ],
                                768  => [ 'slidesPerView' => 2 ],
                                1200 => [ 'slidesPerView' => 3 ],
                            ],
                        ],
                        'items'            => $slides,
                    ]
                );
            else :
                ?>
                <div class="newsletter-empty">
                    <p><?php esc_html_e('No items configured', 'twmp-ath'); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <button class="modal__close-button d-none <?php echo esc_attr( $_close_button_class ); ?>" data-close-modal="<?php echo esc_attr( $_id ); ?>" aria-label="<?php esc_attr_e('Close a modal', 'twmp-ath'); ?>">
            <?php echo twmp_get_svg_icon('close'); ?>
        </button>
    </div>
</div>
