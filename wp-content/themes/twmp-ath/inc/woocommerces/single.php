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
    echo '<h2>' . esc_html__('Section', 'twmp-ath') . '</h2>';
    echo '<p>' . esc_html__('Your section content here.', 'twmp-ath') . '</p>';
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
	<section class="relate-product-section relate-product-section--related-products">
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
							'title'             => esc_html__('Related Products', 'twmp-ath'),
							'description'       => '',
						]
					);
					?>
				</div>
			</div>

			<div class="relate-product-section__slider-wrap position-relative">
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
    echo '<div class="container single-product-container">';
}, 15);

add_action('woocommerce_after_single_product', function () {
    echo '</div>';
}, 100);
