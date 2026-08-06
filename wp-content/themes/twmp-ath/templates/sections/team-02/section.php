<?php

if (! defined('ABSPATH')) {
	exit;
}

$data = wp_parse_args(
	$args,
	[
		'hide_section'     => '',
		'id'               => '',
		'class'            => '',
		'class_container'  => '',
		'title'            => '',
		'description'      => '',
		'button_text'      => '',
		'button_link'      => '',
		'artists'          => [],
		'enable_container' => false,
	]
);

if ($data['hide_section']) {
    return;
}

$_class = 'team-02-section';
$_class .= ! empty($data['class']) ? esc_attr(' ' . $data['class']) : '';

$_class_container = 'container';
$_class_container .= ! empty($data['class_container']) ? esc_attr(' ' . $data['class_container']) : '';

$artist_ids = is_array($data['artists']) ? array_filter(array_map('absint', $data['artists'])) : [];
$slides = [];

foreach ($artist_ids as $artist_id) {
	$artist_post = get_post($artist_id);

	if (! $artist_post instanceof WP_Post || 'publish' !== $artist_post->post_status) {
		continue;
	}

	$item_args = [
		'name'        => get_the_title($artist_post),
		'position'    => get_the_excerpt($artist_post),
		'description' => wp_trim_words(wp_strip_all_tags($artist_post->post_content), 18, '...'),
		'image_id'    => get_post_thumbnail_id($artist_id),
		'image_size'  => 'large',
		'lazyload'    => false,
		'url'         => get_permalink($artist_id),
	];

	ob_start();
	get_template_part('templates/sections/team/item', null, $item_args);
	$slides[] = [
		'content' => ob_get_clean(),
		'class'   => 'team-02-section__slide',
	];
}

$has_intro = ! empty($data['title']) || ! empty($data['description']) || (! empty($data['button_text']) && ! empty($data['button_link']));

if (! $has_intro && empty($slides)) {
	return;
}
?>

<section class="position-relative <?php echo esc_attr($_class); ?>" <?php if (! empty($data['id'])) : ?> id="<?php echo esc_attr(sanitize_file_name(strtolower($data['id']))); ?>" <?php endif; ?>>
	<?php
	get_template_part(
		'templates/components/image-light',
		null,
		[
			'side' => 'left',
			'src' => TWMP_IMG_URI . '/our-team-light.png',
			'alt' => $data && !empty($data['title']) ? $data['title'] : '',
			'width' => 1095,
			'height' => 941,
		]
	);
	?>
	<?php if ($data['enable_container']) : ?>
		<div class="<?php echo esc_attr($_class_container); ?>">
		<?php endif; ?>

		<div class="team-02-section__shell">
			<div class="team-02-section__header">
				<div class="team-02-section__intro">
					<?php
					get_template_part(
						'templates/components/heading',
						null,
						[
							'title_class'       => 'team-02-section__title',
							'description_class' => 'team-02-section__description',
							'class'             => 'team-02-section__heading',
							'title'             => $data['title'],
							'description'       => $data['description'],
						]
					);
					?>

					<?php if (! empty($data['button_text']) && ! empty($data['button_link'])) : ?>
						<div class="team-02-section__cta">
							<?php
							get_template_part(
								'templates/components/button',
								null,
								[
									'class'              => 'team-02-section__button button-normal typo-system-button',
									'button_text'        => $data['button_text'],
									'button_url'         => $data['button_link'],
									'button_link_target' => '_self',
									'svg_icon_after'     => twmp_get_svg_icon('arrow-right'),
								]
							);
							?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<?php if (! empty($slides)) : ?>
				<div class="team-02-section__slider-wrap position-relative">
					<div class="event-control">
						<div class="nav">
							<div class="swiper-button swiper-button-prev"></div>
							<div class="swiper-button swiper-button-next"></div>
						</div>
						<div class="swiper-pagination event-swiper-pagination"></div>
					</div>
					<?php
					get_template_part(
						'templates/components/swiper',
						null,
						[
							'class'            => 'team-02-section__swiper',
							'data_block'       => 'team-02',
							'enable_container' => false,
							'settings'         => [
								'autoPlay'        => false,
								'pagination'      => false,
								'prevNextButtons' => false,
								'slidesPerView'   => 1.15,
								'spaceBetween'    => 32,
								'breakpoints'     => [
									640  => [
										'slidesPerView' => 1.4,
										'spaceBetween'  => 36,
									],
									992  => [
										'slidesPerView' => 2.3,
										'spaceBetween'  => 40,
									],
									1200 => [
										'slidesPerView' => 4,
										'spaceBetween'  => 48,
									],
								],
							],
							'items'            => $slides,
						]
					);
					?>
				</div>
			<?php endif; ?>
			<?php if (!empty($data['sub_description'])) : ?>
				<div class="sub_description">
					<?php echo wp_kses_post($data['sub_description']); ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ($data['enable_container']) : ?>
		</div>
	<?php endif; ?>
</section>