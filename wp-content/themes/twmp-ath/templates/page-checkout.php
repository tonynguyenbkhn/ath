<?php

/**
 * Template Name: Checkout
 * Template Post Type: page
 */

get_header();

$data = $_GET;

$is_step_2 = false;

if (!empty($data['twmp_checkout_step']) && $data['twmp_checkout_step'] === '2') {
    $is_step_2 = true;
}

$is_class_workshop = function_exists('twmp_checkout_is_class_workshop_context') && twmp_checkout_is_class_workshop_context();
$is_order_received = function_exists('twmp_checkout_is_order_received_page') && twmp_checkout_is_order_received_page();

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

$title = '';

if ($is_class_workshop) {
  $title = __('Register', 'twmp-ath');
}

get_template_part('templates/sections/page-title/section-checkout', null, ['class' => 'page-checkout-title', 'title'=> $title]);

?>

<div data-block="checkout-custom">
    <div class="woocommerce-checkout-custom <?php echo $is_step_2 ? esc_attr('twmp_checkout_step_2') : esc_attr('twmp_checkout_step_1'); ?><?php echo $is_order_received ? esc_attr(' twmp_checkout_order_received') : ''; ?>">
        <div class="container">
            <div class="woocommerce_checkout-columns">
                <?php if ($is_step_2) : ?>
                    <div class="woocommerce_checkout--full">
                        <?php echo do_shortcode('[woocommerce_checkout]'); ?>
                    </div>
                <?php else : ?>
                    <div class="<?php echo ($is_class_workshop || $is_order_received) ? esc_attr('woocommerce_checkout--full') : esc_attr('woocommerce_checkout--left'); ?>">
                        <?php echo do_shortcode('[woocommerce_checkout]'); ?>
                    </div>
                    <?php if (!$is_class_workshop) : ?>
                        <div class="woocommerce_checkout--right">
                            <?php echo do_shortcode('[woocommerce_cart]'); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
