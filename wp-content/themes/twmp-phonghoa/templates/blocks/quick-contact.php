<?php

global $product;

$condition = $product->is_type('simple') || $product->is_type('variable');

$button_class = 'quick_buy rounded-0';

$button_text = __("Đặt hàng trước", "twmp-phonghoa");
$sub_button_text = __("", "twmp-phonghoa");


$dataStickyContact = get_field('sticky_links', 'option') ? get_field('sticky_links', 'option') : [];
$zalo = '';
foreach ($dataStickyContact as $item) {
    if ($item['type'] === 'zalo') {
        $zalo = $item;
    }
}
$zalo_woo = get_field('zalo_woo', 'option') ? get_field('zalo_woo', 'option') : $zalo['url'];

if ($condition) {
    echo '<form class="cart"><a style="width: 100% !important; height: 46px" href="'.esc_url($zalo_woo).'" target="_blank" class=" w-100 text-white ' . $button_class . '"><span>' . $button_text . '</span><span>'.$sub_button_text.'</span></a></form>';
}