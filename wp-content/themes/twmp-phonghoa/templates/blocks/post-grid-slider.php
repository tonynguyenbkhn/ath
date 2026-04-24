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

$selected_posts = $data['items'];

$post_args = array(
	'post_type'              => 'post',
	'post_status'            => 'publish',
	'update_post_meta_cache' => false,
	'update_post_term_cache' => false,
	'no_found_rows'          => true,
);

if (! empty($selected_posts)) {
	$post_args['post__in'] = $selected_posts;
	$post_args['orderby']  = 'post__in';
} else {
	$post_args['posts_per_page'] = 12;
}

$post_query = new WP_Query($post_args);

$_swiper_items = [];
while ($post_query->have_posts()) : $post_query->the_post();
	ob_start();

	get_template_part(
		'templates/blocks/post-card',
		null,
		array(
			'post_data' => get_post(get_the_ID()),
			'post_id' => get_the_ID(),
			'view_more_button' => $data && isset($data['view_more_button']) ? $data['view_more_button'] : '',
			'post_title_limit' => 10,
			'post_excerpt_limit' => 15,
			'options' => [
				'show_excerpt' => false,
				'show_date' => true,
				'show_author' => false,
				'show_categories' => true
			]
		)
	);

	$content_html = ob_get_clean();

	$_swiper_items[] = [
		'class' => 'd-flex flex-column post-slider__slide',
		'content' => $content_html
	];
endwhile;
wp_reset_postdata();

?>

<div class="<?php echo esc_attr($_class); ?>" <?php if (!empty($data['id'])) : ?> id="<?php echo esc_attr($data['id']); ?>" <?php endif; ?> data-block="post-grid-slider">

	<?php if ($data['lazyload']) : ?><noscript><?php endif; ?>
		<?php if ($data['enable_container']) : ?><div class="<?php echo esc_attr($_class_container); ?>"><?php endif; ?>
			<div class="d-flex align-items-center justify-content-between common-header-wrapper product-grid-header-wrapper">
				<?php
				get_template_part('templates/core-blocks/heading', null, [
					'title_class' => 'product-grid__title',
					'description_class' => 'product-grid__description',
					'class' => 'product-grid__header',
					'title' => $data && !empty($data['title']) ? $data['title'] : '',
					'description' => $data && !empty($data['description']) ? $data['description'] : '',
				]);
				?>
				<div class="post-grid-slider__button product-view-more__button">
					<?php
					get_template_part('templates/core-blocks/button', null, [
						'class'       => 'product-view-more text-black text-dark',
						'button_text' => esc_html__('Xem thêm', 'twmp-phonghoa'),
						'button_url' => esc_url(home_url('blog')),
						'svg_icon_after' => twmp_get_svg_icon('arrow-right')
					]);
					?>
				</div>
			</div>
			<?php get_template_part('templates/core-blocks/swiper', null, [
				'items' => $_swiper_items,
				'lazyload' => !$data['lazyload'],
				'settings' => [
					'autoplay' => 10000,
					'pagination' => false,
					'prevNextButtons' => false,
					'prevSvgButton' => 'arrow-left',
					'nextSvgButton' => 'arrow-right'
				]
			]); ?>
			<?php if ($data['enable_container']) : ?>
			</div><?php endif; ?>
		<?php if ($data['lazyload']) : ?></noscript><?php endif; ?>

</div>