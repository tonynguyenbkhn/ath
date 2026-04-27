<?php

$data = wp_parse_args(
	$args,
	[
		'class' => '',
		'term_id' => '',
		'term_name' => '',
		'image_size' => 'full',
		'lazyload' => false
	]
);

$thumbnail_id = get_term_meta($data['term_id'], 'thumbnail_id', true);
?>

<div class="category-grid__item">
	<a class="category-grid__link" href="<?php echo esc_url(get_term_link($data['term_id'])); ?>" title="<?php printf('View %s', $data['term_name']); ?>">
		<?php
		get_template_part('templates/core-blocks/image', null, [
			'image_id' => $thumbnail_id,
			'image_size' => $data['image_size'],
			'lazyload' => $data['lazyload'],
			'class' => 'pe-none image--cover image--default'
		]);
		?>
		<span class="d-block category-grid__label"><span class="category-grid__label__text"><?php echo esc_html($data['term_name']); ?></span></span>
	</a>
</div>
