<?php

$data = wp_parse_args($args, [
	'id' => '',
	'class' => '',
	'lazyload' => false,
	'items' => [],
	'enable_container' => false
]);
if ( 0 === 1 ) {

	$_class = !empty($data['class']) ? esc_attr(' ' . $data['class']) : '';
	$_class .= $data['lazyload'] ? ' is-not-loaded' : '';

	$_class_container = 'container';
	$_class_container .= !empty($data['class_container']) ? esc_attr(' ' . $data['class_container']) : '';

	$_swiper_items = [];

	if (!empty($data['items'])) :

		foreach ($data['items'] as $index => $item) :
			ob_start();

			$item['image_lazyload'] = $data['lazyload'];

			get_template_part('templates/blocks/hero-slider-nav-item', null, $item);
			$item_html = ob_get_clean();

			$_swiper_items[] = [
				'content' => $item_html,
				'lazyload' => $index > 0,
			];
		endforeach;

	?>

		<div class="<?php echo esc_attr($_class); ?>" <?php if (!empty($data['id'])) : ?> id="<?php echo esc_attr($data['id']); ?>" <?php endif; ?> data-block="hero-slider-nav">

			<?php if ($data['lazyload']) : ?><noscript><?php endif; ?>
				<?php if ($data['enable_container']) : ?><div class="position-relative <?php echo esc_attr($_class_container); ?>"><?php endif; ?>
					<?php get_template_part('templates/core-blocks/swiper', null, [
						'items' => $_swiper_items,
						'lazyload' => !$data['lazyload'],
						'settings' => [
							'autoplay' => 10000,
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
			<div class="thumbs-slider__wrapper">
				<div class="container thumbs-slider__container position-relative">
					<div class="swiper js-thumbs-swiper">
						<div class="swiper-wrapper">
							<?php foreach ($data['items'] as $item): ?>
								<div class="swiper-slide">
									<?php if (!empty($item['nav_title'])): ?>
										<div class="thumbs-slider__title">
											<?php echo esc_html($item['nav_title']); ?>
										</div>
									<?php endif; ?>
									<?php if (!empty($item['nav_description'])): ?>
										<div class="thumbs-slider__description-wrapper d-flex justify-content-between align-items-center">
											<p class="thumbs-slider__description"><?php echo esc_html($item['nav_description']); ?></p>
											<?php echo twmp_get_svg_icon('angle-right'); ?>
										</div>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
					<!-- Thêm navigation riêng cho thumbs -->
					<!-- <div class="thumbs-button-prev swiper-button-prev"></div>
					<div class="thumbs-button-next swiper-button-next"></div> -->
				</div>
			</div>
		</div>

	<?php endif;
}