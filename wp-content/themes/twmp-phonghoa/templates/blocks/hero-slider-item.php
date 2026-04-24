<?php

$data = wp_parse_args($args, [
	'class' => '',
	'image' => '',
	'image_size' => 'full',
	'lazyload' => false,
	'title' => '',
	'description' => '',
	'button_text' => '',
	'button_link' => '',
]);

$_class = 'w-100 position-relative d-flex align-items-center justify-content-center hero-slider-item';
$_class .= !empty( $data['class'] ) ? esc_attr( ' ' . $data['class'] ) : '';

if ( !empty( $data['image'] ) ) :

	?>
	<div class="<?php echo esc_attr( $_class ); ?>">
        <?php if (!empty($data['button_link'])) : ?><a href="<?php echo esc_url($data['button_link']) ?>" ><?php endif; ?>
		<?php
		get_template_part('templates/core-blocks/image', null, [
			'image_id' => $data['image'],
			'image_size' => $data['image_size'],
			'lazyload' => $data['lazyload'],
			'class' => 'pe-none image--cover hero-slider-item__image image--default w-100',
			'image_class' => 'w-100'
		]);
		?>
        <?php if ($data['button_link']) : ?></a><?php endif; ?>
		<div class="hero-slider-item__content-inner w-100">
            <div class="container">
                <div class="row">
                    <div class="col">
                        <?php if ( $data['description'] ) { ?>
                            <p class="hero-slider-item__description"><?php echo wp_kses_post($data['description']) ?></p>
                        <?php } ?>
                        <?php if ( $data['title'] ) { ?>
                            <h2 class="hero-slider-item__title"><?php echo wp_kses_post($data['title']) ?></h2>
                        <?php } ?>
                        <?php if ( $data['button_text'] ) { ?>
                            <?php
                            get_template_part('templates/core-blocks/button', null, [
                                'class'       => 'hero-slider-item__button rounded-0 text-white',
                                'button_text' => $data['button_text'],
                                'button_url' => esc_url_raw($data['button_link']),
                                'svg_icon_after' => twmp_get_svg_icon('arrow-right')
                            ]);
                            ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
	</div>
<?php endif;
