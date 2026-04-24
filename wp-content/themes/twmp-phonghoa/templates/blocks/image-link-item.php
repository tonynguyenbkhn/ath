<?php

$data = wp_parse_args($args, [
	'class' => '',
	'image_id' => '',
	'lazyload' => '',
	'link' => ''
]);

$data['link'] = $data['image']['image_link'];

$_class = 'w-100 position-relative d-flex align-items-center justify-content-center logo-slider-item';
$_class .= !empty( $data['class'] ) ? esc_attr( ' ' . $data['class'] ) : '';

if ( !empty( $data['image'] ) ) :
	?>
	<div class="<?php echo esc_attr( $_class ); ?>">
		 <?php if (!empty($data['link'])) : ?><a href="<?php echo esc_url($data['link']) ?>" ><?php endif; ?>
		<?php
		get_template_part('templates/core-blocks/image', null, [
			'image_id' => $data['image']['image_id'],
			'image_size' => 'full',
			'lazyload' => $data['lazyload'],
			'class' => 'pe-none image--cover image-link-item__image image--default',
            'image_class' => 'w-100'
		]);
		?>
		<?php if ($data['link']) : ?></a><?php endif; ?>
	</div>
<?php endif;
