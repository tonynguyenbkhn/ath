<?php

$data = wp_parse_args($args, [
	'id' => '',
	'class' => '',
	'lazyload' => true,
	'items' => [],
	'enable_container' => false
]);

$_class = !empty($data['class']) ? esc_attr(' ' . $data['class']) : '';

$_class_container = 'container';
$_class_container .= !empty($data['class_container']) ? esc_attr(' ' . $data['class_container']) : '';

if (!empty($data['items'])) :
?>

	<div class="<?php echo esc_attr($_class) ?>" <?php if (!empty($data['id'])) : ?> id="<?php echo esc_attr($data['id']); ?>" <?php endif; ?>>
		<?php if ($data['enable_container']) : ?><div class="<?php echo esc_attr($_class_container); ?>"><?php endif; ?>
			<?php
			get_template_part('templates/core-blocks/heading', null, [
				'title_class' => 'collection-grid__title',
				'description_class' => 'collection-grid__description',
				'class' => 'collection-grid__header',
				'title' => $data && !empty($data['title']) ? $data['title'] : '',
				'description' => $data && !empty($data['description']) ? $data['description'] : '',
			]);
			?>
			<div class="container-fluid">
				<div class="row">
					<?php foreach ($data['items'] as $index => $item) : ?>
						<?php get_template_part('templates/blocks/collection-card', null, [
							'title' => $item['title'],
							'description' => $item['description'],
							'image_id' => $item['image_id'],
						]); ?>
					<?php endforeach; ?>
				</div>
			</div>
			<?php if ($data['enable_container']) : ?>
			</div><?php endif; ?>
	</div>
<?php
endif;
