<?php

$data = wp_parse_args($args, [
	'id' => '',
	'class' => '',
	'items' => [],
	'block_layout' => '3-col',
	'enable_container' => false,
	'query' => null
]);

$_class = !empty($data['class']) ? ' ' . $data['class'] : '';

$_class_container = 'container';
$_class_container .= !empty($data['class_container']) ? esc_attr(' ' . $data['class_container']) : '';

$selected_products = $data['items'];
$block_layout = $data['block_layout'];

$post_args = [
	'post_type' => 'product',
	'post_status' => 'publish',
	'update_post_meta_cache' => false,
	'update_post_term_cache' => false,
	'no_found_rows' => true,
];

if (!empty($selected_products)) {
	$post_args['post__in'] = $selected_products;
	$post_args['orderby'] = 'post__in';
} else {
	$post_args['posts_per_page'] = 12;
}

$post_query = $data['query'] instanceof WP_Query ? $data['query'] : new WP_Query($post_args);

$grid_css_class = 'products';

switch ($block_layout) :
	case '1-col':
		$grid_css_class .= ' columns-1';
		break;
	case '2-col':
		$grid_css_class .= ' columns-2';
		break;
	case '3-col':
		$grid_css_class .= ' columns-3';
		break;
	case '4-col':
		$grid_css_class .= ' columns-4';
		break;
	case '5-col':
		$grid_css_class .= ' columns-5';
		break;
	case '6-col':
		$grid_css_class .= ' columns-6';
		break;
endswitch;

if ($post_query->have_posts()) :
?>

	<div class="<?php echo esc_attr($_class); ?> woocommerce" <?php if (!empty($data['id'])) : ?> id="<?php echo esc_attr($data['id']); ?>" <?php endif; ?>>
		<?php if ($data['enable_container']) : ?><div class="<?php echo esc_attr($_class_container); ?>"><?php endif; ?>
			<div class="d-flex align-items-center justify-content-between common-header-wrapper product-grid-header-wrapper">
				<?php
				get_template_part('templates/core-blocks/heading', null, [
					'title_class' => 'product-grid__title',
					'description_class' => 'product-grid__description',
					'class' => 'product-grid__header',
					'title' => $data && !empty($data['title']) ? $data['title'] : '',
					'description' => $data && !empty($data['description']) ? $data['description'] : '',
					'link' => !empty($data['button']) && $data['button']['button_url'] ? esc_url($data['button']['button_url']) : ''
				]);
				?>
				<?php if (!empty($data['button']) && $data['button']['button_text']) : ?>
					<div class="product-grid-slider__button product-view-more__button">
						<?php
						get_template_part('templates/core-blocks/button', null, [
							'class' => 'product-view-more text-black text-dark',
							'button_text' => esc_html($data['button']['button_text']),
							'button_url' => $data['button']['button_url'] ? esc_url($data['button']['button_url']) : '',
							'svg_icon_after' => twmp_get_svg_icon('arrow-right'),
							'button_sub_text' => $data && !empty($data['title']) ? $data['title'] : '',
						]);
						?>
					</div>
				<?php endif; ?>
			</div>
			<div class="product-grid__main product-scroll-wrapper mobile-scroll">
				<ul class="<?php echo esc_attr($grid_css_class); ?>">
					<?php
					while ($post_query->have_posts()) {
						$post_query->the_post();

						ob_start();
						do_action('woocommerce_shop_loop');
						wc_get_template_part('content', 'product');
						$product_html = ob_get_clean();
						$product_html = str_replace(['<h2 class="woocommerce-loop-product__title">', '</h2>'], ['<h3 class="woocommerce-loop-product__title">', '</h3>'], $product_html);
						echo wp_kses_post($product_html);
					}

					wp_reset_postdata();
					?>
				</ul>
			</div>
			<?php if ($data['enable_container']) : ?>
			</div><?php endif; ?>
	</div>
<?php
endif;
