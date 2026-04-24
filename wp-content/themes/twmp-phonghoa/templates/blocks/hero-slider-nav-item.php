<?php

$data = wp_parse_args($args, [
	'class' => '',
	'image' => '',
	'image_size' => 'full',
	'lazyload' => false
]);

$_class = 'w-100 position-relative d-flex align-items-center justify-content-center hero-slider-item';
$_class .= !empty($data['class']) ? esc_attr(' ' . $data['class']) : '';

if (!empty($data['image'])) :

?>
	<div class="<?php echo esc_attr($_class); ?>">
		<?php
		get_template_part('templates/core-blocks/image', null, [
			'image_id' => $data['image'],
			'image_size' => $data['image_size'],
			'lazyload' => $data['lazyload'],
			'class' => 'pe-none image--cover hero-slider-item__image image--default w-100',
			'image_class' => 'w-100'
		]);
		?>
	</div>
	<div class="hero-slider-item__inner">
		<div class="container hero-slider-item__container">
			<div class="row">
				<div class="col-lg-6">
					<?php if (!empty($data['title'])): ?><h2 class="hero-slider-item__title"><?php echo esc_html($data['title']); ?></h2><?php endif; ?>
					<?php if (!empty($data['sub_title'])): ?><div class="hero-slider-item__subtitle"><?php echo esc_html($data['sub_title']); ?></div><?php endif; ?>
					<?php if (!empty($data['description'])): ?><div class="hero-slider-item__description"><?php echo esc_html($data['description']); ?></div><?php endif; ?>
				</div>
				<div class="col-lg-6 d-flex justify-content-end">
					<div class="hero-slider-item__box">
						<?php if (!empty($data['other_title'])): ?>
							<div class="hero-slider-item__other-title">
								<?php echo esc_html($data['other_title']); ?>
							</div>
						<?php endif; ?>
						<?php if (!empty($data['other_description'])): ?>
							<div class="hero-slider-item__other-description">
								<?php echo esc_html($data['other_description']); ?>
							</div>
						<?php endif; ?>
						<?php if (!empty($data['button_text'])): ?>
							<?php
							get_template_part('templates/core-blocks/button', null, [
								'class'       => 'hero-slider-item__button rounded-0 text-white',
								'button_text' => esc_html($data['button_text']),
								'button_url' => esc_url($data['button_link'])
							]);
							?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php endif;
