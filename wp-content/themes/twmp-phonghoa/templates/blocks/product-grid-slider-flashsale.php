<?php
$data = wp_parse_args($args, [
	'id' => '',
	'class' => '',
	'items' => [],
	'query' => null,
	'lazyload' => false,
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
	'posts_per_page'		 => 12,
	'meta_query'     => array(
		'relation' => 'OR',
		array(
			'key'     => '_sale_price_dates_from',
			'value'   => '',
			'compare' => '!='
		),
		array(
			'key'     => '_sale_price_dates_to',
			'value'   => '',
			'compare' => '!='
		),
	),
);

if (! empty($selected_products)) {
	$post_args['post__in'] = $selected_products;
	$post_args['orderby']  = 'post__in';
}

$post_query = new WP_Query($post_args);

$_swiper_items = [];

$min_start_date = null;
$max_end_date   = null;

while ($post_query->have_posts()) : $post_query->the_post();

	$product_id = get_the_ID();
	$start = get_post_meta($product_id, '_sale_price_dates_from', true);
	$end   = get_post_meta($product_id, '_sale_price_dates_to', true);

	$start = $start ? (int)$start : null;
	$end   = $end ? (int)$end : null;

	if (!is_null($start)) {
		if (is_null($min_start_date) || $start < $min_start_date) {
			$min_start_date = $start;
		}
	}

	if (!is_null($end)) {
		if (is_null($max_end_date) || $end > $max_end_date) {
			$max_end_date = $end;
		}
	}

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

$display_start = $min_start_date ? date('d/m/Y', $min_start_date) : '';
$display_end   = $max_end_date   ? date('d/m/Y', $max_end_date)   : '';
if ( count($_swiper_items) > 0 ):
?>
<div class="<?php echo esc_attr($_class); ?>" <?php if (!empty($data['id'])) : ?> id="<?php echo esc_attr($data['id']); ?>" <?php endif; ?> data-block="product-grid-slider-flashsale">

	<?php if ($data['lazyload']) : ?><noscript><?php endif; ?>
		<?php if ($data['enable_container']) : ?><div class="<?php echo esc_attr($_class_container); ?>"><?php endif; ?>
			<div class="product-grid-slider-flashsale__wrapper position-relative">
				<div class="d-flex align-items-center justify-content-between">
					<div class="product-grid-slider-flashsale__header">
						<img src="<?php echo TWMP_IMG_URI . '/flashsale.png'; ?>" alt="">
						<?php if ($max_end_date): ?>
							<div class="countdown-wrapper" data-end-date="<?php echo esc_attr(date('Y-m-d H:i:s', $max_end_date)); ?>">
								<div class="countdown">
									<div class="countdown-item">
										<span class="days"><span class="day_1"><?php echo esc_html__('0', 'twmp-phonghoa'); ?></span><span class="day_2"><?php echo esc_html__('0', 'twmp-phonghoa'); ?></span></span>
										<small><?php echo esc_html__('Day', 'twmp-phonghoa'); ?></small>
									</div>
									<span class="text-white">:</span>
									<div class="countdown-item">
										<span class="hours"><span class="hour_1"><?php echo esc_html__('0', 'twmp-phonghoa'); ?></span><span class="hour_2"><?php echo esc_html__('0', 'twmp-phonghoa'); ?></span></span>
										<small><?php echo esc_html__('Hour', 'twmp-phonghoa'); ?></small>
									</div>
									<span class="text-white">:</span>
									<div class="countdown-item">
										<span class="minutes"><span class="minute_1"><?php echo esc_html__('0', 'twmp-phonghoa'); ?></span><span class="minute_2"><?php echo esc_html__('0', 'twmp-phonghoa'); ?></span></span>
										<small><?php echo esc_html__('Minute', 'twmp-phonghoa'); ?></small>
									</div>
									<span class="text-white">:</span>
									<div class="countdown-item">
										<span class="seconds"><span class="second_1"><?php echo esc_html__('0', 'twmp-phonghoa'); ?></span><span class="second_2"><?php echo esc_html__('0', 'twmp-phonghoa'); ?></span></span>
										<small><?php echo esc_html__('Second', 'twmp-phonghoa'); ?></small>
									</div>
								</div>
							</div>
						<?php endif; ?>
					</div>
					<?php if ($data['button'] && $data['button']['button_text']): ?>
						<div class="product-grid-slider-flashsale__button product-view-more__button">
							<?php
							get_template_part('templates/core-blocks/button', null, [
								'class'       => 'product-view-more text-white',
								'button_text' => esc_html($data['button']['button_text']),
								'button_url' => $data['button']['button_url'] ? esc_url($data['button']['button_url']) : '',
								'svg_icon_after' => twmp_get_svg_icon('arrow-right'),
								'button_sub_text' => 'Khuyến mãi'
							]);
							?>
						</div>
					<?php endif; ?>
				</div>
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
			</div>
			<?php if ($data['enable_container']) : ?>
			</div><?php endif; ?>
		<?php if ($data['lazyload']) : ?>
		</noscript><?php endif; ?>

</div>

<?php endif; ?>