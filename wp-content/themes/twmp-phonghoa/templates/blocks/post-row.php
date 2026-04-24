<?php

$data = wp_parse_args(
	$args,
	array(
		'class'              => '',
		'post_id '           => '',
		'post_data'          => '',
		'post_title_limit'   => 25,
		'post_excerpt_limit' => 20,
		'view_more_button'   => __('View more', 'twmp-phonghoa'),
		'options' => [
			'show_excerpt' => true,
			'show_date' => true,
			'show_author' => true,
			'show_categories' => true
		]
	)
);

$_class  = 'post-row col-lg-12 col-md-12 col-sm-12 col-12';
$_class .= ! empty($data['class']) ? esc_attr(' ' . $data['class']) : '';

$post_data = $data['post_data'] ?? get_post($data['post_id']);

$post_title       = ! empty($data['post_title_limit']) ? wp_trim_words($post_data->post_title, $data['post_title_limit'], '...') : $post_data->post_title;
$post_description = $post_data->post_excerpt ? wp_trim_words($post_data->post_excerpt, $data['post_excerpt_limit'], '...') : wp_trim_words($post_data->post_content, $data['post_excerpt_limit'], '...');
$post_permalink   = get_permalink($post_data);

$options = $data['options'];

?>
<article class="<?php echo esc_attr($_class); ?>">
	<div class="post-row__wrapper row">
		<div class="col-lg-4 col-md-4 col-sm-12 col-12">
			<a class="image__overlay-zoom post-row__overlay-link" href="<?php echo esc_url($post_permalink); ?>" title="<?php echo esc_attr($post_data->post_title); ?>">
				<?php
				get_template_part('templates/core-blocks/image', null, [
					'image_id' => get_post_thumbnail_id($post_data),
					'image_size' => 'full',
					'lazyload' => false,
					'class' => 'pe-none image--cover post-row__image image--default',
				]);
				?>
			</a>
		</div>
		<div class="col-lg-8 col-md-8 col-sm-12 col-12">
			<div class="post-row__content">
				<a class="post-row__title-link" href="<?php echo esc_url($post_permalink); ?>" title="<?php echo esc_attr($post_data->post_title); ?>">
					<h3 class="post-row__title h5"><?php echo esc_html($post_title); ?></h3>
				</a>
				<?php
				get_template_part('templates/blocks/post-meta', null, [
					'date' => $options['show_date'],
					'author' => $options['show_author'],
					'categories' => $options['show_categories'],
					'class' => 'post-row__post-meta'
				]);
				?>
				<?php if ($options['show_excerpt']): ?>
					<p class="post-row__description"><?php echo esc_html($post_description); ?> </p>
				<?php endif; ?>
				<?php if ($data['view_more_button'] !== '') : ?>
					<div class="post-row__footer">
						<?php
						get_template_part('templates/core-blocks/button', null, [
							'class'       => 'post-row__button rounded-0 text-white',
							'button_text' => $data['view_more_button'],
							'button_url' => esc_url($post_permalink),
							'type' => 'dark'
						]);
						?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</article>
