<?php

if (!defined('ABSPATH')) {
	exit;
}

$data = wp_parse_args(
	$args,
	[
		'class' => '',
		'term_id' => '',
		'term_name' => '',
		'term_slug' => '',
		'image_size' => 'full',
		'lazyload' => false
	]
);

$thumbnail_id = get_term_meta($data['term_id'], 'thumbnail_id', true);

$product_cat_link = '';

if ( $data['term_slug'] === 'for-companies' ) {
	$product_cat_link = home_url('/for-companies/');
} else if ( $data['term_slug'] === 'for-schools' ) {
	$product_cat_link = home_url('/for-schools/');
} else {
	$product_cat_link = get_term_link($data['term_id']);
}

?>

<div class="product-cat-grid__item">
	<a class="product-cat-grid__link" href="<?php echo esc_url($product_cat_link); ?>" title="<?php printf('View %s', $data['term_name']); ?>">
		<?php
		get_template_part('templates/components/image', null, [
			'image_id' => $thumbnail_id,
			'image_size' => $data['image_size'],
			'lazyload' => $data['lazyload'],
			'class' => 'pe-none image--cover image--default'
		]);
		?>
		<span class="d-block product-cat-grid__label"><span class="product-cat-grid__label__text"><?php echo esc_html($data['term_name']); ?></span></span>
	</a>
</div>