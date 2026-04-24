<?php

$data = wp_parse_args($args, [
	'class' => '',
	'image' => '',
	'lazyload' => ''
]);

$_class = 'w-100 position-relative d-flex align-items-center justify-content-center logo-slider-item';
$_class .= !empty( $data['class'] ) ? esc_attr( ' ' . $data['class'] ) : '';

if ( !empty( $data['image'] ) ) :
	?>
	<div class="<?php echo esc_attr( $_class ); ?>">
		<?php
		get_template_part('templates/core-blocks/image', null, [
			'image_id' => $data['image'],
			'image_size' => 'full',
			'lazyload' => $data['lazyload'],
			'class' => 'pe-none image--cover logo-slider-item__image image--default',
		]);
		?>
	</div>
<?php endif;
