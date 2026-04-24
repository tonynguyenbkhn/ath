<?php

$data = wp_parse_args($args, [
	'class' => '',
	'product_id' => '',
	'product_qty' => 1,
	'enable_quick_buy' => false
]);

$product = wc_get_product( $data['product_id'] );

if (!$product) {
	return; // hoặc echo thông báo lỗi
}

$product_type = $product->get_type();
$product_name = $product->get_name();
$_class = 'js-add-to-cart single_add_to_cart_button button';
$_class .= !empty( $data['class'] ) ? esc_attr(' ' . $data['class'] ) : '';

$link_title = __('Add to cart', 'twmp-phonghoa');

if ($product_type == 'simple') :
?>
	<span class="add-to-cart-button-wrapper">
		<button class="<?php echo esc_attr( $_class ); ?>"
		data-product-id="<?php echo esc_attr( $data['product_id'] ); ?>"
		data-product-title="<?php echo esc_attr( $product_name ); ?>"
		data-product-qty="<?php echo esc_attr( $data['product_qty'] ); ?>"
		title="<?php echo esc_attr( $link_title ); ?>">
            <span class="pe-none"><?php echo esc_html__('Add to cart', 'twmp-phonghoa') ?></span>
			<span class="icon pe-none" aria-hidden="true"><?php echo twmp_get_svg_icon('cart'); ?></span>
		</button>
		
	</span>
<?php endif;