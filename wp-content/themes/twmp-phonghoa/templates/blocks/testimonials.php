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

		ob_start();

		get_template_part('templates/blocks/testimonial-card', null, $item);
		$item_html = ob_get_clean();

		$_swiper_items[] = [
			'content' => $item_html
		];
	endforeach;

?>

	<div class="<?php echo esc_attr($_class); ?>" <?php if (!empty($data['id'])) : ?> id="<?php echo esc_attr($data['id']); ?>" <?php endif; ?> data-block="testimonials">

		<?php if ($data['lazyload']) : ?><noscript><?php endif; ?>
			<?php if ($data['enable_container']) : ?><div class="<?php echo esc_attr($_class_container); ?>"><?php endif; ?>
				<?php
				get_template_part('templates/core-blocks/heading', null, [
					'title_class' => 'testimonials__title',
					'description_class' => 'testimonials__description',
					'class' => 'testimonials__header',
					'title' => $data && !empty($data['title']) ? $data['title'] : '',
					'description' => $data && !empty($data['description']) ? $data['description'] : '',
				]);
				?>
				<?php get_template_part('templates/core-blocks/swiper', null, [
					'items' => $_swiper_items,
					'lazyload' => !$data['lazyload'],
					'settings' => [
						'autoplay' => 10000,
						'pagination' => true,
						'prevNextButtons' => true,
						'prevSvgButton' => 'arrow-left',
						'nextSvgButton' => 'arrow-right'
					]
				]); ?>
				<?php if ($data['enable_container']) : ?></div><?php endif; ?>
			<?php if ($data['lazyload']) : ?></noscript><?php endif; ?>

	</div>
<?php endif;
