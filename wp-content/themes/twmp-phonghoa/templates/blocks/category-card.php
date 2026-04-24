<?php

$data = wp_parse_args($args, [
	'id' => '',
	'class' => '',
	'lazyload' => true,
	'term_id' => 'term_id',
	'enable_container' => false
]);

$_class = !empty($data['class']) ? esc_attr(' ' . $data['class']) : '';

$_class_container = 'container';
$_class_container .= !empty($data['class_container']) ? esc_attr(' ' . $data['class_container']) : '';

$term = get_term($data['term_id'], 'product_cat');
if (is_wp_error($term) || !$term) {
	log_error_with_trace('The term does not exist');
	return false;
}
$thumbnail_id = get_term_meta($data['term_id'], 'thumbnail_id', true);

$child_categories = get_terms([
	'taxonomy'   => 'product_cat',
	'parent'     => $data['term_id'],
	'hide_empty' => false,
]);

$post_args = array(
	'post_type'              => 'product',
	'post_status'            => 'publish',
	'update_post_meta_cache' => false,
	'update_post_term_cache' => false,
	'no_found_rows'          => true,
	'posts_per_page'		 => 6,
	'tax_query'              => array(
		array(
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => $data['term_id'],
		),
	),
);

?>

<div class="<?php echo esc_attr( $_class ); ?>" <?php if (!empty($data['id'])) : ?> id="<?php echo esc_attr($data['id']); ?>" <?php endif; ?>>
	<?php if ($data['enable_container']) : ?><div class="<?php echo esc_attr($_class_container); ?>"><?php endif; ?>
		<div class="row">
			<div class="col-lg-4 col-md-3 mb-md-0 mb-sm-2 mb-2">
				<?php
				get_template_part('templates/core-blocks/heading', null, [
					'title_class' => 'category-card__title',
					'description_class' => 'category-card__description',
					'class' => 'category-card__header',
					'title' => $data && !empty($data['title']) ? $data['title'] : '',
					'description' => $data && !empty($data['description']) ? $data['description'] : '',
				]);
				?>
				<div class="category-card__image">
					<?php
					get_template_part('templates/core-blocks/image', null, [
						'image_id' => $thumbnail_id,
						'image_size' => 'full',
						'lazyload' => $data['lazyload'],
						'class' => 'pe-none image--cover image--default',
						'image_class' => 'w-100 h-auto',
					]);
					?>
				</div>
				<?php if (count($child_categories) > 0): ?>
					<ul class="category-card__category-list">
						<?php foreach ($child_categories as $child):
							$category_link = get_term_link($child->term_id, 'product_cat')
						?>
							<li class="category-card__category-item">
								<a href="<?php echo esc_url($category_link) ?>" class="category-card__category-link">
									<?php
									echo twmp_get_svg_icon('arrow-right');
									echo esc_html($child->name)
									?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<div class="category-card__view-more">
					<?php
					get_template_part('templates/core-blocks/button', null, [
						'class'       => 'category-card__view-more-link text-black',
						'button_text' => esc_html__('View all', 'twmp-phonghoa'),
						'button_url' => '#',
						'svg_icon_after' => twmp_get_svg_icon('arrow-right')
					]);
					?>
				</div>
			</div>
			<div class="col-lg-8 col-md-9">
				<div class="woocommerce">
					<?php
					get_template_part('templates/blocks/product-grid', null, [
						'id' => '',
						'class' => 'product-grid',
						'block_layout' => '3-col',
						'enable_container' => false,
						'query' => new WP_Query($post_args)
					]);
					?>
				</div>
			</div>
		</div>
		<?php if ($data['enable_container']) : ?>
		</div><?php endif; ?>
</div>