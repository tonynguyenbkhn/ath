<?php

// add_filter('woocommerce_coupons_enabled', 'no');

remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10);
remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);

add_filter('woocommerce_breadcrumb_defaults', function ($args) {
    $args['wrap_before'] = '<nav class="woocommerce-breadcrumb" aria-label="Breadcrumb"><div class="container woocommerce-breadcrumb__container">';
    $args['wrap_after'] = '</div></nav>';

    return $args;
}, 10);

add_action('wp_enqueue_scripts', 'remove_woocommerce_styles', 99);

function remove_woocommerce_styles()
{
    wp_dequeue_style('woocommerce-general');
    wp_dequeue_style('woocommerce-layout');
    wp_dequeue_style('woocommerce-smallscreen');
}