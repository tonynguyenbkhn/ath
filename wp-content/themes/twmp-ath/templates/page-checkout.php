<?php

/**
 * Template Name: Checkout
 * Template Post Type: page
 */

get_header();

$data = $_GET;

$is_step_2 = false;

if ($data && $data['twmp_checkout_step'] === '2') {
    $is_step_2 = true;
}

get_template_part(
    'templates/components/image-light',
    null,
    [
        'class' => 'event__light',
        'side' => 'right',
        'src' => TWMP_IMG_URI . '/event-light.png',
        'alt' => esc_html(get_the_title()),
        'width' => 1052,
        'height' => 816,
    ]
);

get_template_part('templates/sections/page-title/section', null, ['class' => 'page-checkout-title']);

?>

<div data-block="checkout-custom">
    <div class="woocommerce-checkout-custom <?php echo $is_step_2 ? 'twmp_checkout_step_2' : 'twmp_checkout_step_1' ?>">
        <div class="container">
            <div class="woocommerce_checkout-columns">
                <?php if ($is_step_2) : ?>
                    <div class="woocommerce_checkout--full">
                        <?php echo do_shortcode('[woocommerce_checkout]'); ?>
                    </div>
                <?php else : ?>
                    <div class="woocommerce_checkout--left">
                        <?php echo do_shortcode('[woocommerce_checkout]'); ?>
                    </div>
                    <div class="woocommerce_checkout--right">
                        <?php echo do_shortcode('[woocommerce_cart]'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
