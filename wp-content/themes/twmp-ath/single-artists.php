<?php

if (! defined('ABSPATH')) {
	exit;
}

get_header();

while (have_posts()) :
	the_post();

	$artist_id     = get_the_ID();
	$image_id      = get_post_thumbnail_id($artist_id);
	$highlights    = function_exists('get_field') ? get_field('ath_artist_summary_highlights', $artist_id) : [];
	$other_members = function_exists('get_field') ? get_field('ath_artist_other_members', $artist_id) : [];
	$partners      = function_exists('get_field') ? get_field('ath_artist_partner_artists', $artist_id) : [];

	$other_members_title = function_exists('get_field') ? get_field('ath_artist_other_members_title', $artist_id) : '';
	$partners_title      = function_exists('get_field') ? get_field('ath_artist_partner_artists_title', $artist_id) : '';

	$other_members_title = $other_members_title ? $other_members_title : esc_html__('Other Member', 'twmp-ath');
	$partners_title      = $partners_title ? $partners_title : esc_html__('Other Partner Artists & Practitioners', 'twmp-ath');
	?>

	<main id="primary" class="site-main single-artist">
		<?php
		if (function_exists('woocommerce_breadcrumb')) {
			woocommerce_breadcrumb();
		} else {
			?>
			<div class="single-artist__breadcrumbs breadcrumbs container">
				<?php twmp_breadcrumbs(); ?>
			</div>
			<?php
		}
		?>

		<div class="single-artist__container container">
			<section class="single-artist__summary">
				<div class="single-artist__media">
					<?php
					if ($image_id) :
						get_template_part(
							'templates/components/image',
							null,
							[
								'image_id'    => $image_id,
								'image_size'  => 'large',
								'lazyload'    => false,
								'class'       => 'single-artist__image-wrap image--cover image--default',
								'image_class' => 'single-artist__image',
								'alt'         => get_the_title(),
							]
						);
					endif;
					?>
				</div>

				<div class="single-artist__content">
					<h1 class="single-artist__title"><?php the_title(); ?></h1>

					<?php if (! empty($highlights) && is_array($highlights)) : ?>
						<ul class="single-artist__highlights">
							<?php foreach ($highlights as $highlight) :
								$text = isset($highlight['text']) ? $highlight['text'] : '';

								if (! $text) {
									continue;
								}
								?>
								<li><?php echo esc_html($text); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if (get_the_content()) : ?>
						<div class="single-artist__description">
							<?php the_content(); ?>
						</div>
					<?php elseif (has_excerpt()) : ?>
						<div class="single-artist__description">
							<?php the_excerpt(); ?>
						</div>
					<?php endif; ?>
				</div>
			</section>
        </div>
        <div class="single-artist__wrap position-relative">
            <?php

            get_template_part(
                'templates/components/image-light',
                null,
                [
                    'class'  => 'artist-single-slider__light',
                    'side'   => 'right',
                    'src'    => TWMP_IMG_URI . '/page-bio-light.png',
                    'alt'    => get_the_title(),
                    'width'  => 932,
                    'height' => 960,
                ]
            );

            ?>
            <div class="single-artist__container container">
                <?php
                get_template_part(
                    'templates/sections/artist-single-slider/section',
                    null,
                    [
                        'title'   => $other_members_title,
                        'artists' => $other_members,
                        'class'   => 'single-artist__slider-section single-artist__slider-section--members',
                    ]
                );

                get_template_part(
                    'templates/sections/artist-single-slider/section',
                    null,
                    [
                        'title'   => $partners_title,
                        'artists' => $partners,
                        'class'   => 'single-artist__slider-section single-artist__slider-section--partners',
                    ]
                );
                ?>
            </div>
        </div>
	</main>

	<?php
endwhile;

get_footer();
