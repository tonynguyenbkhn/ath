<?php
$data = wp_parse_args($args, [
	'id' => '',
	'class' => '',
	'items' => [],
	'query' => null,
	'lazyload' => true,
	'enable_container' => false
]);

$_class = !empty($data['class']) ? esc_attr(' ' . $data['class']) : '';
$_class .= $data['lazyload'] ? ' is-not-loaded' : '';

$_class_container = 'container';
$_class_container .= !empty($data['class_container']) ? esc_attr(' ' . $data['class_container']) : '';

$selected_products = $data['items'];

$post_args = array(
	'post_type'              => 'product',
	'post_status'            => 'publish',
	'update_post_meta_cache' => false,
	'update_post_term_cache' => false,
	'no_found_rows'          => true,
);

if (! empty($selected_products)) {
	$post_args['post__in'] = $selected_products;
	$post_args['orderby']  = 'post__in';
} else {
	$post_args['posts_per_page'] = 12;
}

$post_query = new WP_Query($post_args);

$_swiper_items = [];
while ($post_query->have_posts()) : $post_query->the_post();
	ob_start();
	/**
	 * Hook: woocommerce_shop_loop.
	 */
	do_action('woocommerce_shop_loop');

	wc_get_template_part('content', 'product');
	$content_html = ob_get_clean();

	$content_html = str_replace('<li', '<div', $content_html);
	$content_html = str_replace('<ul', '<div', $content_html);
	$content_html = str_replace('</li>', '</div>', $content_html);
	$content_html = str_replace('</ul>', '</div>', $content_html);

	$_swiper_items[] = [
		'class' => 'd-flex flex-column product-slider__slide',
		'content' => $content_html
	];
endwhile;
wp_reset_postdata();
?>

<div class="<?php echo esc_attr($_class); ?>" <?php if (!empty($data['id'])) : ?> id="<?php echo esc_attr($data['id']); ?>" <?php endif; ?> data-block="product-grid-slider">

	<?php if ($data['lazyload']) : ?><noscript><?php endif; ?>
		<?php if ($data['enable_container']) : ?><div class="position-relative <?php echo esc_attr($_class_container); ?>"><?php endif; ?>
			<div class="d-flex align-items-center justify-content-between common-header-wrapper product-grid-header-wrapper">
				<?php
				get_template_part('templates/core-blocks/heading', null, [
					'title_class' => 'product-grid__title',
					'description_class' => 'product-grid__description',
					'class' => 'product-grid__header',
					'title' => $data && !empty($data['title']) ? $data['title'] : '',
					'description' => $data && !empty($data['description']) ? $data['description'] : '',
					'link' => $data['button']['button_url'] ? esc_url($data['button']['button_url']) : '',
				]);
				?>
				<?php if ($data['button'] && $data['button']['button_text']): ?>
					<div class="product-grid-slider__button product-view-more__button">
						<?php
						get_template_part('templates/core-blocks/button', null, [
							'class'       => 'product-view-more text-black text-dark',
							'button_text' => esc_html($data['button']['button_text']),
							'button_url' => $data['button']['button_url'] ? esc_url($data['button']['button_url']) : '',
							'svg_icon_after' => twmp_get_svg_icon('arrow-right'),
							'button_sub_text' => $data && !empty($data['title']) ? $data['title'] : '',
						]);
						?>
					</div>
				<?php endif; ?>
			</div>
			<?php get_template_part('templates/core-blocks/swiper', null, [
				'items' => $_swiper_items,
				'lazyload' => !$data['lazyload'],
				'settings' => [
					'autoplay' => false,
					'pagination' => false,
					'prevNextButtons' => false,
					// 'prevSvgButton' => 'arrow-left',
					// 'nextSvgButton' => 'arrow-right'
				]
			]); ?>
			<div class="swiper-button swiper-button-prev"></div>
			<div class="swiper-button swiper-button-next"></div>
			<?php if ($data['enable_container']) : ?>
			</div><?php endif; ?>
		<?php if ($data['lazyload']) : ?></noscript><?php endif; ?>

</div>