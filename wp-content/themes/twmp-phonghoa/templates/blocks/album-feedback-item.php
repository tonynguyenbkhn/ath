<?php

$data = wp_parse_args($args, [
	'class' => '',
	'image' => '',
	'lazyload' => ''
]);

$_class = 'w-100 position-relative d-flex align-items-center justify-content-center album-feedback-item';
$_class .= !empty( $data['class'] ) ? esc_attr( ' ' . $data['class'] ) : '';

if ( !empty( $data['image'] ) ) :
	$image_src_data = wp_get_attachment_image_src( $data['image'], 'full', false );
	$srcset = wp_get_attachment_image_srcset($data['image'], 'large');
	$sizes  = wp_get_attachment_image_sizes($data['image'], 'large');
	?>
	<div class="<?php echo esc_attr( $_class ); ?>">
		<a data-fancybox="gallery" data-src="<?php echo esc_url( $image_src_data[0] ); ?>">
			<figure class="image pe-none image--cover album-feedback-item__image image--default">
        		<img class="image__img" width="<?php echo esc_attr( $image_src_data[1] ); ?>" height="<?php echo esc_attr( $image_src_data[2] ); ?>" src="<?php echo esc_url( $image_src_data[0] ); ?>" srcset="<?php echo esc_attr($srcset); ?>" sizes="<?php echo esc_attr($sizes); ?>"  alt="">
			</figure>
    	</a>
	</div>
<?php endif;
