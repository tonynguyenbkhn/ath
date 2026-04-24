<?php

$data = wp_parse_args($args, [
	'id' => '',
	'class' => '',
	'lazyload' => false,
	'items' => [],
	'enable_container' => false
]);

$_class = !empty($data['class']) ? esc_attr(' ' . $data['class']) : '';
$_class .= $data['lazyload'] ? ' is-not-loaded' : '';

$_class_container = 'container';
$_class_container .= !empty($data['class_container']) ? esc_attr(' ' . $data['class_container']) : '';

$_swiper_items = [];

if (!empty($data['items'])) :

	foreach ($data['items'] as $index => $item) :
		ob_start();

		$item['image_lazyload'] = $data['lazyload'];

		get_template_part('templates/blocks/hero-slider-item', null, $item);
		$item_html = ob_get_clean();

		$_swiper_items[] = [
			'content' => $item_html,
			'lazyload' => $index > 0,
		];
	endforeach;

?>

	<div class="position-relative <?php echo esc_attr($_class); ?>" <?php if (!empty($data['id'])) : ?> id="<?php echo esc_attr($data['id']); ?>" <?php endif; ?> data-block="hero-slider">

		<?php if ($data['lazyload']) : ?><noscript><?php endif; ?>
			<?php if ($data['enable_container']) : ?><div class="position-relative <?php echo esc_attr($_class_container); ?>"><?php endif; ?>
				<?php get_template_part('templates/core-blocks/swiper', null, [
					'items' => $_swiper_items,
					'lazyload' => !$data['lazyload'],
					'settings' => [
						'autoplay' => 5000,
						'pagination' => false,
						'prevNextButtons' => false,
						//			'prevSvgButton' => 'arrow-left',
						//			'nextSvgButton' => 'arrow-right'
					]
				]); ?>
				<div class="swiper-button swiper-button-prev"></div>
				<div class="swiper-button swiper-button-next"></div>
				<?php if ($data['enable_container']) : ?>
				</div><?php endif; ?>
			<?php if ($data['lazyload']) : ?></noscript><?php endif; ?>

	</div>

<?php endif;
