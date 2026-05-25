<?php

if (!defined('ABSPATH')) {
	exit;
}

$data = wp_parse_args($args, [
	'id' => '',
	'class' => '',
	'items' => [],
	'enable_container' => false,
	'grid_css_class' => ''
]);

$_class = 'product-cat-grid';
$_class .= ! empty($data['class']) ? esc_attr(' ' . $data['class']) : '';

$_class_container = 'container';
$_class_container .= !empty($data['class_container']) ? esc_attr(' ' . $data['class_container']) : '';

$term_args = [
	'orderby' => 'menu_order',
	'order' => 'asc',
	'hide_empty' => false,
	'pad_counts' => true,
	'child_of' => 0,
];

if (!empty($data['items'])) {
	$term_args['include'] = $data['items'];
	$term_args['orderby'] = 'include';
}

$product_categories = get_terms('product_cat', $term_args);

$grid_css_class = $data['grid_css_class'] ? $data['grid_css_class'] : 'col-12 col-md-4 col-lg-3';

if (!empty($product_categories) && !is_wp_error($product_categories)) : ?>

	<div class="<?php echo esc_attr($_class); ?>" <?php if (! empty($data['id'])) : ?> id="<?php echo esc_attr(sanitize_file_name(strtolower($data['id']))); ?>" <?php endif; ?>>
		<?php
		get_template_part(
			'templates/components/image-light',
			null,
			[
				'side' => 'right',
				'src' => TWMP_IMG_URI . '/what-we-offer.png',
				'alt' => $data && !empty($data['title']) ? $data['title'] : '',
				'width' => 908,
				'height' => 551,
			]
		);
		?>
		<div class="product-cat-grid__logo">
			<img width="953" height="406" src="<?php echo esc_url(TWMP_IMG_URI . '/logo-section.png'); ?>" alt="<?php echo esc_attr__('Our service', 'twmp-ath'); ?>">
		</div>
		<div class="glow"></div>
		<?php if ($data['enable_container']) : ?><div class="<?php echo esc_attr($_class_container); ?>"><?php endif; ?>
			<?php
			get_template_part('templates/components/heading', null, [
				'title_class' => 'product-cat-grid__title',
				'description_class' => 'product-cat-grid__description',
				'class' => 'product-cat-grid__header',
				'title' => $data && !empty($data['title']) ? $data['title'] : '',
				'description' => $data && !empty($data['description']) ? $data['description'] : '',
			]);
			?>
			<div class="product-cat-grid__wrapper">
				<?php
				get_template_part(
					'templates/components/section-shape',
					null,
					[
						'type'  => 'square',
						'class' => 'product-cat-grid__shape product-cat-grid__shape--square',
					]
				);
				get_template_part(
					'templates/components/section-shape',
					null,
					[
						'type'  => 'triangle',
						'class' => 'product-cat-grid__shape product-cat-grid__shape--triangle',
					]
				);
				?>
				<?php foreach ($product_categories as $category) :
					$term_id = $category->term_id ?? 0;

					if (!$term_id || !is_numeric($term_id)) {
						continue;
					}

					$thumbnail_id = get_term_meta($term_id, 'thumbnail_id', true);

					if (!$thumbnail_id) {
						continue;
					}

					get_template_part(
						'templates/sections/product-cat-grid/item',
						null,
						[
							'term_id' => $term_id,
							'term_name' => $category->name ?? '',
						]
					);
				endforeach; ?>
			</div>
			<?php if ($data['enable_container']) : ?>
			</div><?php endif; ?>
	</div>
<?php endif;
