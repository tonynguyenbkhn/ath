<?php

if (! defined('ABSPATH')) {
	exit;
}

$data = wp_parse_args(
	$args,
	[
		'title'   => '',
		'artists' => [],
		'class'   => '',
	]
);

$artist_ids = is_array($data['artists']) ? array_filter(array_map('absint', $data['artists'])) : [];
$slides     = [];

foreach ($artist_ids as $artist_id) {
	$artist_post = get_post($artist_id);

	if (! $artist_post instanceof WP_Post || 'publish' !== $artist_post->post_status) {
		continue;
	}

	$image_id    = get_post_thumbnail_id($artist_id);
	$position    = get_the_excerpt($artist_post);
	$description = wp_trim_words(wp_strip_all_tags($artist_post->post_content), 22, '...');

	ob_start();
	?>
	<article class="artist-single-card">
		<a class="artist-single-card__link" href="<?php echo esc_url(get_permalink($artist_id)); ?>">
			<?php if ($image_id) : ?>
				<div class="artist-single-card__media">
					<?php
					get_template_part(
						'templates/components/image',
						null,
						[
							'image_id'    => $image_id,
							'image_size'  => 'large',
							'lazyload'    => false,
							'class'       => 'artist-single-card__image-wrap image--cover image--default',
							'image_class' => 'artist-single-card__image',
							'alt'         => get_the_title($artist_post),
						]
					);
					?>
				</div>
			<?php endif; ?>

			<div class="artist-single-card__body">
				<h3 class="artist-single-card__name"><?php echo esc_html(get_the_title($artist_post)); ?></h3>

				<?php if ($position) : ?>
					<p class="artist-single-card__position"><?php echo esc_html($position); ?></p>
				<?php endif; ?>

				<?php if ($description) : ?>
					<p class="artist-single-card__description"><?php echo esc_html($description); ?></p>
				<?php endif; ?>
			</div>
		</a>
	</article>
	<?php
	$slides[] = [
		'content' => ob_get_clean(),
		'class'   => 'artist-single-slider__slide',
	];
}

if (empty($slides)) {
	return;
}

$_class = 'artist-single-slider';
$_class .= ! empty($data['class']) ? esc_attr(' ' . $data['class']) : '';
?>

<section class="<?php echo esc_attr($_class); ?>">
	<div class="artist-single-slider__header">
		<?php if (! empty($data['title'])) : ?>
			<?php
			get_template_part(
				'templates/components/heading',
				null,
				[
					'title_class' => 'artist-single-slider__title',
					'class'       => 'artist-single-slider__heading',
					'title'       => $data['title'],
				]
			);
			?>
		<?php endif; ?>

		<div class="artist-single-slider__controls">
			<div class="swiper-pagination artist-single-slider__pagination"></div>
			<div class="artist-single-slider__nav">
				<button class="swiper-button swiper-button-prev artist-single-slider__button" type="button" aria-label="<?php echo esc_attr__('Previous slide', 'twmp-ath'); ?>">
					<?php echo twmp_get_svg_icon('arrow-left'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
				<button class="swiper-button swiper-button-next artist-single-slider__button" type="button" aria-label="<?php echo esc_attr__('Next slide', 'twmp-ath'); ?>">
					<?php echo twmp_get_svg_icon('arrow-right'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
			</div>
		</div>
	</div>

	<?php
	get_template_part(
		'templates/components/swiper',
		null,
		[
			'class'            => 'artist-single-slider__swiper',
			'data_block'       => 'artist-single-slider',
			'enable_container' => false,
			'settings'         => [
				'autoPlay'        => false,
				'pagination'      => false,
				'prevNextButtons' => false,
				'slidesPerView'   => 1.15,
				'spaceBetween'    => 24,
				'breakpoints'     => [
					640  => [
						'slidesPerView' => 2,
						'spaceBetween'  => 24,
					],
					992  => [
						'slidesPerView' => 3,
						'spaceBetween'  => 24,
					],
					1200 => [
						'slidesPerView' => 4,
						'spaceBetween'  => 24,
					],
				],
			],
			'items'            => $slides,
		]
	);
	?>
</section>
