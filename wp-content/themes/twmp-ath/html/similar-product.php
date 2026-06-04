<?php

function twmp_woocommerce_output_related_products()
{
	global $product;

	if (! ($product instanceof WC_Product)) {
		return;
	}

	$product_id = $product->get_id();
	$custom_ids = function_exists('get_field') ? get_field('ath_similar_service', $product_id) : [];

	$custom_ids = is_array($custom_ids) ? $custom_ids : [$custom_ids];
	$related_ids = [];

	foreach ($custom_ids as $custom_id) {
		if ($custom_id instanceof WP_Post) {
			$custom_id = $custom_id->ID;
		} elseif (is_array($custom_id)) {
			$custom_id = $custom_id['ID'] ?? $custom_id['id'] ?? 0;
		} elseif (is_object($custom_id) && isset($custom_id->ID)) {
			$custom_id = $custom_id->ID;
		}

		$custom_id = absint($custom_id);

		if (! $custom_id || $custom_id === $product_id || in_array($custom_id, $related_ids, true)) {
			continue;
		}

		$related_post = get_post($custom_id);

		if (! $related_post instanceof WP_Post || 'product' !== $related_post->post_type || 'publish' !== $related_post->post_status) {
			continue;
		}

		if (! function_exists('wc_get_product')) {
			continue;
		}

		$related_product = wc_get_product($custom_id);

		if (! $related_product instanceof WC_Product) {
			continue;
		}

		$related_ids[] = $custom_id;
	}

	if (empty($related_ids) || ! function_exists('twmp_render_product_card')) {
		return;
	}

	$slides = [];
	$previous_product = $product;

	foreach ($related_ids as $related_id) {
		$related_product = wc_get_product($related_id);

		if (! $related_product instanceof WC_Product) {
			continue;
		}

		$product = $related_product;

		ob_start();
		twmp_render_product_card();
		$slide_html = ob_get_clean();

		if ('' === trim((string) $slide_html)) {
			continue;
		}

		$slides[] = [
			'content' => $slide_html,
			'class'   => 'relate-product-section__slide',
		];
	}

	$product = $previous_product;

	if (empty($slides)) {
		return;
	}
?>
	<section class="relate-product-section section relate-product-section--related-products">
		<div class="relate-product-section__shell">
			<div class="relate-product-section__header">
				<div class="relate-product-section__intro">
					<?php
					get_template_part(
						'templates/components/heading',
						null,
						[
							'title_class'       => 'relate-product-section__title',
							'description_class' => 'relate-product-section__description',
							'class'             => 'relate-product-section__heading',
							'title'             => esc_html__('Similar Show/ Event', 'twmp-ath'),
							'description'       => '',
						]
					);
					?>
				</div>
			</div>

			<div class="relate-product-section__slider-wrap position-relative">
				<div class="relate-product-control">
					<div class="nav">
						<div class="swiper-button swiper-button-prev"></div>
						<div class="swiper-button swiper-button-next"></div>
					</div>
				</div>
				<?php
				get_template_part(
					'templates/components/swiper',
					null,
					[
						'class'           => 'relate-product-section__swiper',
						'data_block'      => 'relate-product',
						'enable_container' => false,
						'settings'        => [
							'autoPlay'        => false,
							'pagination'      => false,
							'prevNextButtons' => false,
							'grid'           => [
								'rows' => 2,
							],
							'slidesPerView'   => 1,
							'slidesPerGroup'  => 1,
							'spaceBetween'    => 24,
							'breakpoints'     => [
								640  => [
									'slidesPerView'  => 2,
									'slidesPerGroup' => 2,
								],
								992  => [
									'slidesPerView'  => 4,
									'slidesPerGroup' => 4,
								],
								1200 => [
									'slidesPerView'  => 4,
									'slidesPerGroup' => 4,
								],
							],
						],
						'items'           => $slides,
					]
				);
				?>
			</div>
		</div>
	</section>
<?php
}