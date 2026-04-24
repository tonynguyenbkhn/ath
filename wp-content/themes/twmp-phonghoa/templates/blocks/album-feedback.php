<?php

$data = wp_parse_args($args, [
	'id' => '',
	'class' => 'section album-feedback',
	'items' => [],
	'item_data' => [],
	'lazyload' => true,
	'enable_container' => true
]);

$data['items'] = get_field('album_feedback', 'option') ? get_field('album_feedback', 'option') : [];
$data['title'] = get_field('album_feedback_title', 'option') ? get_field('album_feedback_title', 'option') : [];
$_class = !empty($data['class']) ? esc_attr(' ' . $data['class']) : '';
$_class .= $data['lazyload'] ? ' is-not-loaded' : '';

$_class_container = 'container album-feedback__container';
$_class_container .= !empty($data['class_container']) ? esc_attr(' ' . $data['class_container']) : '';

$_swiper_items = [];

if (!empty($data['items'])) :

	foreach ($data['items'] as $index => $item) :

		$data['item_data']['image'] = $item;
		$data['item_data']['lazyload'] = $data['lazyload'];

		ob_start();

		get_template_part('templates/blocks/album-feedback-item', null, $data['item_data']);
		$item_html = ob_get_clean();

		$_swiper_items[] = [
			'content' => $item_html
		];
	endforeach;

?>

	<div class="position-relative <?php echo esc_attr($_class); ?>" <?php if (!empty($data['id'])) : ?> id="<?php echo esc_attr($data['id']); ?>" <?php endif; ?> data-block="album-feedback">

		<?php if ($data['lazyload']) : ?><noscript><?php endif; ?>
			<div class="container">
				<div class="d-flex align-items-center justify-content-between common-header-wrapper product-grid-header-wrapper">
					<?php
					get_template_part('templates/core-blocks/heading', null, [
						'title_class' => 'album-feedback__title',
						'description_class' => 'album-feedback__description',
						'class' => 'album-feedback__header',
						'title' => $data && !empty($data['title']) ? $data['title'] : '',
						'description' => $data && !empty($data['description']) ? $data['description'] : '',
					]);
					?>
				</div>
			</div>
			<?php if ($data['enable_container']) : ?><div class="position-relative <?php echo esc_attr($_class_container); ?>"><?php endif; ?>

				<?php
				get_template_part('templates/core-blocks/swiper', null, [
					'items' => $_swiper_items,
					'lazyload' => !$data['lazyload'],
					'settings' => [
						'autoplay' => 10000,
						'pagination' => false,
						'prevNextButtons' => true,
						// 'prevSvgButton' => 'arrow-left',
						// 'nextSvgButton' => 'arrow-right'
					]
				]);
				?>
				<?php if ($data['enable_container']) : ?>
				</div><?php endif; ?>
			<?php if ($data['lazyload']) : ?>
			</noscript><?php endif; ?>

	</div>
<?php endif;
