<?php

/**
 * ==========================================
 * WooCommerce Single Product Customizations
 * Theme: twmp-ath
 * ==========================================
 */

if (!defined('ABSPATH')) {
	exit;
}

//////////////////////////////
// HELPERS
//////////////////////////////

/**
 * Render contact us button
 */
function twmp_render_contact_us_button()
{
	$button_text = esc_html__('Contact Us', 'twmp-ath');
	$button_link = get_permalink(get_page_by_path('contact'));
	get_template_part('templates/components/button', null, [
		'class' => 'bg-system-white text-system-black typo-system-button button-default contact-us-btn',
		'button_text' => $button_text,
		'button_url' => $button_link,
		'button_link_target' => '_self',
	]);
}

/**
 * Convert ACF field value safely
 */
function twmp_field_to_string($value)
{
	if (is_array($value)) {
		return implode(', ', array_map('sanitize_text_field', $value));
	}

	return is_scalar($value) ? (string) $value : '';
}

/**
 * Render pagination if available.
 */
function twmp_render_related_event_pagination($total, $current, $fragment = '')
{
	$total = absint($total);
	$current = absint($current);
	$fragment = is_string($fragment) ? $fragment : '';

	if ($total < 2) {
		return;
	}

	echo paginate_links([
		'base'      => esc_url_raw(add_query_arg('paged', '%#%', remove_query_arg('paged'))),
		'format'    => '',
		'current'   => max(1, $current),
		'total'     => $total,
		'prev_text' => '&laquo;',
		'next_text' => '&raquo;',
		'type'      => 'list',
		'add_fragment' => $fragment,
	]);
}

/**
 * Get related product years for the current product.
 */
function twmp_get_related_event_years(WC_Product $product)
{
	global $wpdb;

	$product_id = $product->get_id();
	$term_ids = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'ids']);
	$term_ids = array_values(array_filter(array_map('absint', is_array($term_ids) ? $term_ids : [])));

	if (empty($term_ids)) {
		return [];
	}

	$placeholders = implode(',', array_fill(0, count($term_ids), '%d'));
	$year_sql = $wpdb->prepare(
		"
        SELECT DISTINCT YEAR(p.post_date) AS product_year
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        WHERE p.post_type = 'product'
          AND p.post_status = 'publish'
          AND tt.taxonomy = 'product_cat'
          AND tt.term_id IN ({$placeholders})
          AND p.ID <> %d
        ORDER BY product_year DESC
        ",
		array_merge($term_ids, [$product_id])
	);

	$years = $wpdb->get_col($year_sql);

	return array_values(array_filter(array_map('absint', is_array($years) ? $years : [])));
}

//////////////////////////////
// REMOVE DEFAULT WOOCOMMERCE
//////////////////////////////

add_filter('woocommerce_product_tabs', function ($tabs) {
	// Rename Description tab
	if (isset($tabs['description'])) {
		$tabs['description']['title'] = __('About', 'twmp-ath');
	}

	// Add custom Section tab
	$tabs['section'] = [
		'title'    => __('Section', 'twmp-ath'),
		'priority' => 25,
		'callback' => 'render_product_section_tab',
	];

	return $tabs;
}, 98);

function render_product_section_tab()
{
	global $product;

	if (! $product instanceof WC_Product) {
		return;
	}

	$product_id = $product->get_id();
	$term_ids = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'ids']);
	$term_ids = array_values(array_filter(array_map('absint', is_array($term_ids) ? $term_ids : [])));

	if (empty($term_ids)) {
		return;
	}

	$selected_year = isset($_GET['event_year']) ? absint(wp_unslash($_GET['event_year'])) : 0;
	$paged = isset($_GET['paged']) ? max(1, absint(wp_unslash($_GET['paged']))) : max(1, absint(get_query_var('paged')));

	$years = twmp_get_related_event_years($product);

	$query_args = [
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => 6,
		'paged'               => $paged,
		'post__not_in'        => [$product_id],
		'ignore_sticky_posts' => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'tax_query'           => [
			[
				'taxonomy'         => 'product_cat',
				'field'            => 'term_id',
				'terms'            => $term_ids,
				'operator'         => 'IN',
				'include_children' => false,
			],
		],
	];

	if ($selected_year > 0) {
		$query_args['date_query'] = [
			[
				'year' => $selected_year,
			],
		];
	}

	$related_query = new WP_Query($query_args);
	$base_url = remove_query_arg(['event_year', 'paged']);
?>
	<section class="single-related-event">
		<div class="single-related-event__filters" aria-label="<?php echo esc_attr__('Filter events by year', 'twmp-ath'); ?>">
			<?php
			$all_url = remove_query_arg(['paged']);
			$all_url = add_query_arg('event_year', 0, $all_url);
			?>
			<a class="single-related-event__filter<?php echo 0 === $selected_year ? ' is-active' : ''; ?>" href="<?php echo esc_url($all_url); ?>">
				<?php echo esc_html__('All', 'twmp-ath'); ?>
			</a>
			<?php foreach ($years as $year) : ?>
				<?php $year_url = add_query_arg('event_year', $year, $base_url); ?>
				<a class="single-related-event__filter<?php echo $year === $selected_year ? ' is-active' : ''; ?>" href="<?php echo esc_url($year_url); ?>">
					<?php echo esc_html($year); ?>
				</a>
			<?php endforeach; ?>
		</div>

		<?php if ($related_query->have_posts()) : ?>
			<div class="single-related-event__grid">
				<?php
				while ($related_query->have_posts()) :
					$related_query->the_post();
					$related_product = function_exists('wc_get_product') ? wc_get_product(get_the_ID()) : null;

					if (! $related_product instanceof WC_Product) {
						continue;
					}

					$previous_product = $product;
					$product = $related_product;
				?>
					<div class="single-related-event__item">
						<?php twmp_render_product_card(); ?>
					</div>
				<?php
					$product = $previous_product;
				endwhile;
				?>
			</div>

			<div class="single-related-event__pagination">
				<?php twmp_render_related_event_pagination((int) $related_query->max_num_pages, $paged); ?>
			</div>
		<?php else : ?>
			<div class="single-related-event__empty">
				<?php echo esc_html__('No related events found.', 'twmp-ath'); ?>
			</div>
		<?php endif; ?>
	</section>
<?php

	wp_reset_postdata();
}

add_action('wp', function () {
	remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
});

remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
remove_action('woocommerce_before_single_product', 'woocommerce_output_all_notices', 10);
remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);

add_filter('woocommerce_product_description_heading', '__return_empty_string');

//////////////////////////////
// PRODUCT CLASSES
//////////////////////////////

add_filter('woocommerce_post_class', function ($classes) {
	if (is_product()) {
		$classes[] = 'product__detail';
	}

	return $classes;
}, 10);

//////////////////////////////
// REVIEW STRUCTURE
//////////////////////////////

add_action('woocommerce_review_before', function () {
	echo '<div class="comment-avatar">';
}, 5);

add_action('woocommerce_review_before', function () {
	echo '</div>';
}, 15);

//////////////////////////////
// ENTRY SUMMARY WRAPPER
//////////////////////////////

add_action('woocommerce_single_product_summary', function () {
	echo '<div class="entry-summary-wrapper">';
}, 1);

add_action('woocommerce_single_product_summary', function () {
	echo '</div>';
}, 1000);

//////////////////////////////
// PRODUCT HEADER
//////////////////////////////

add_action('woocommerce_single_product_summary', function () {
	global $product;

	if (!$product) {
		return;
	}

	$product_id = $product->get_id();

	echo '<div class="row align-items-center"><div class="col-12">';

	/**
	 * Product badges
	 */
	$badges = function_exists('get_field') ? get_field('ath_badges', $product_id) : false;

	if (!empty($badges) && is_array($badges)) {
		echo '<div class="product-badges">';

		foreach ($badges as $badge) {
			$text  = $badge['text'] ?? '';
			$style = $badge['style'] ?? 'orange';

			if ($text) {
				printf(
					'<span class="ath-badge ath-badge--%s">%s</span>',
					esc_attr($style),
					esc_html($text)
				);
			}
		}

		echo '</div>';
	}

	/**
	 * Title
	 */
	wc_get_template('single-product/title.php');

	/**
	 * Subtitle
	 */
	$subtitle = function_exists('get_field') ? get_field('ath_subtitle', $product_id) : false;

	if ($subtitle) {
		printf('<p class="product-subtitle">%s</p>', esc_html($subtitle));
	}

	/**
	 * Description
	 */
	$description = get_the_excerpt($product_id);

	if ($description) {
		echo '<div class="product-description">' . wp_kses_post(wpautop($description)) . '</div>';
	}

	echo '</div></div>';
}, 1);

//////////////////////////////
// PRODUCT META DETAILS
//////////////////////////////

add_action('woocommerce_single_product_summary', function () {
	global $product;

	if (!$product) {
		return;
	}

	$product_id = $product->get_id();

	$fields = [
		'ath_start_datetime' => ['time', 'Time'],
		'ath_venue' => ['pin', 'Location'],
		'ath_language' => ['globe', 'Language'],
		'ath_format' => ['selection', 'Format'],
		'ath_age_group' => ['stack', 'Age'],
		'ath_demonstration' => ['users', 'Demonstration'],
	];

	echo '<div class="product-details-meta">';

	foreach ($fields as $field_key => $icon) {
		$value = get_field($field_key, $product_id) ?? '';

		if ('ath_start_datetime' === $field_key) {
			$end_value = function_exists('get_field') ? get_field('ath_end_datetime', $product_id) : '';
			$value = twmp_format_event_datetime_range($value, $end_value);
		} elseif ('ath_venue' === $field_key) {
			$value = twmp_get_taxonomy_term_names($product_id, 'ath_venue');
		} elseif ('ath_age_group' === $field_key) {
			$value = twmp_get_taxonomy_term_names($product_id, 'ath_age_group');
		}

		echo '<div class="product-details-meta__item">';
		echo twmp_get_svg_icon($icon[0]);
		echo '<div><span class="product-details-meta__item-label">' . esc_html($icon[1]) . '</span>: <span class="product-details-meta__item-text">' . esc_html(twmp_field_to_string($value)) . '</span></div>';
		echo '</div>';
	}

	echo '</div>';
}, 15);

//////////////////////////////
// CART BUTTON
//////////////////////////////

add_action('woocommerce_single_product_summary', function () {
	echo '<div class="product-action-buttons d-flex items-center gap-16">';
	twmp_render_cart_button();
	twmp_render_contact_us_button();
	echo '</div>';
}, 16);

//////////////////////////////
// ADD TO CART TEXT
//////////////////////////////

add_filter('woocommerce_product_single_add_to_cart_text', function () {
	return esc_html__('Add to cart', 'twmp-ath');
});

//////////////////////////////
// RELATED PRODUCTS
//////////////////////////////
add_action('woocommerce_after_single_product_summary', 'twmp_woocommerce_output_related_products', 20);

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
						'items'           => $slides,
					]
				);
				?>
			</div>
		</div>
	</section>
<?php
}

//////////////////////////////
// AFTER SUMMARY LAYOUT
//////////////////////////////

add_action('woocommerce_after_single_product_summary', function () {
	echo '<div class="woocommerce_after_single_product_summary">';
	// echo '<div class="row">';
	// echo '<div class="col-lg-8 col-md-12 col-sm-12 col-12">';
}, 5);

add_action('woocommerce_after_single_product_summary', function () {
	// echo '</div>';
	// echo '<div class="col-lg-4 col-md-12 col-sm-12 col-12">';
	// echo '<div class="single__content-widgets">';
}, 50);

add_action('woocommerce_after_single_product_summary', function () {
	// echo '</div></div></div></div>';
	echo '</div>';
}, 1000);

add_action('woocommerce_before_single_product', function () {
	if (is_product()) {
		echo '<div class="container single-product-container">';
	}
}, 15);

add_action('woocommerce_after_single_product', function () {
	if (is_product()) {
		echo '</div>';
	}
}, 100);
