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

?>

<div data-block="checkout-custom">
    <div class="wp-block-group woocommerce-checkout-custom">
        <div class="container">
            <div class="wp-block-columns">
                <?php if ($is_step_2) : ?>
                    <div class="wp-block-column" style="flex-basis:100%">
                        <?php echo do_shortcode('[woocommerce_checkout]'); ?>
                    </div>
                <?php else : ?>
                    <div class="wp-block-column" style="flex-basis:45%">
                        <?php echo do_shortcode('[woocommerce_checkout]'); ?>
                    </div>
                    <div class="wp-block-column" style="flex-basis:55%">
                        <?php echo do_shortcode('[woocommerce_cart]'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
