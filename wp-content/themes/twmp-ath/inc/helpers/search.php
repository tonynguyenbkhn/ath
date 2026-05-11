<?php

if (!defined('ABSPATH')) {
    exit;
}

function twmp_get_shop_url()
{
    if (function_exists('wc_get_page_permalink')) {
        $shop_url = wc_get_page_permalink('shop');

        if (is_string($shop_url) && '' !== trim($shop_url)) {
            return esc_url_raw($shop_url);
        }
    }

    return esc_url_raw(home_url('/'));
}
