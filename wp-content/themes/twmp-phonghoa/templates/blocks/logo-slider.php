<?php

$data = wp_parse_args($args, [
	'id' => '',
	'class' => '',
	'items' => [],
	'item_data' => [],
	'lazyload' => true,
	'enable_container' => false
]);

$_class = !empty($data['class']) ? esc_attr(' ' . $data['class']) : '';
$_class .= $data['lazyload'] ? ' is-not-loaded' : '';

$_class_container = 'container';
$_class_container .= !empty($data['class_container']) ? esc_attr(' ' . $data['class_container']) : '';

$_swiper_items = [];

if (!empty($data['items'])) :

	foreach ($data['items'] as $index => $item) :

		$data['item_data']['image'] = $item;
		$data['item_data']['lazyload'] = $data['lazyload'];

		ob_start();

		get_template_part('templates/blocks/logo-slider-item', null, $data['item_data']);
		$item_html = ob_get_clean();

		$_swiper_items[] = [
			'content' => $item_html
		];
	endforeach;

?>

	<div class="<?php echo esc_attr($_class); ?>" <?php if (!empty($data['id'])) : ?> id="<?php echo esc_attr($data['id']); ?>" <?php endif; ?> data-block="logo-slider">

		<?php if ($data['lazyload']) : ?><noscript><?php endif; ?>
			<?php if ($data['enable_container']) : ?><div class="<?php echo esc_attr($_class_container); ?>"><?php endif; ?>
				<?php
				get_template_part('templates/core-blocks/heading', null, [
					'title_class' => 'logo-slider__title',
					'description_class' => 'logo-slider__description',
					'class' => 'logo-slider__header',
					'title' => $data && !empty($data['title']) ? $data['title'] : '',
					'description' => $data && !empty($data['description']) ? $data['description'] : '',
				]);
				?>
				<?php get_template_part('templates/core-blocks/swiper', null, [
					'items' => $_swiper_items,
					'lazyload' => !$data['lazyload'],
					'settings' => [
						'autoplay' => 4000,
						'pagination' => false,
						'prevNextButtons' => false,
						'prevSvgButton' => 'arrow-left',
						'nextSvgButton' => 'arrow-right'
					]
				]); ?>
				<?php if ($data['enable_container']) : ?></div><?php endif; ?>
			<?php if ($data['lazyload']) : ?></noscript><?php endif; ?>

	</div>
<?php endif;
