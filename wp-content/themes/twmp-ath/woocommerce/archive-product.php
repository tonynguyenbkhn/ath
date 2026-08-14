<?php

/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined('ABSPATH') || exit;

get_header('shop');

$has_products    = woocommerce_product_loop();
// Treat empty product category like empty search so we show the "Don't have Result" header
$is_empty_search = (function_exists('twmp_is_empty_product_search_page') && twmp_is_empty_product_search_page()) || (
	is_product_category() && function_exists('get_queried_object') &&
	($term = get_queried_object()) instanceof WP_Term && isset($term->count) && 0 === absint($term->count)
);

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action('woocommerce_before_main_content');
if ($is_empty_search) {
?>
	<header class="woocommerce-products-header twmp-shop-search-empty__header">
		<h1 class="woocommerce-products-header__title page-title">
			<?php echo esc_html__("Don't have Result", 'twmp-ath'); ?>
		</h1>
	</header>
<?php
} elseif ($has_products) {
	/**
	 * Hook: woocommerce_shop_loop_header.
	 *
	 * @since 8.6.0
	 *
	 * @hooked woocommerce_product_taxonomy_archive_header - 10
	 */
	do_action('woocommerce_shop_loop_header');
} else {
?>
	<header class="woocommerce-products-header twmp-shop-search-empty__header">
		<h1 class="woocommerce-products-header__title page-title">
			<?php echo esc_html__("Don't have Result", 'twmp-ath'); ?>
		</h1>
	</header>
<?php
}

if ($has_products) {
	if (defined('WP_DEBUG') && WP_DEBUG) {
		error_log('[twmp] archive-product template: $has_products=' . ($has_products ? '1' : '0'));
		error_log('[twmp] archive-product template: wc_get_loop_prop(total)=' . (function_exists('wc_get_loop_prop') ? wc_get_loop_prop('total') : 'unavailable'));
		error_log('[twmp] archive-product template: $GLOBALS["woocommerce_loop"]=' . print_r($GLOBALS['woocommerce_loop'], true));
		error_log('[twmp] archive-product template: $wp_query->found_posts=' . (isset($wp_query->found_posts) ? $wp_query->found_posts : 'unset'));
	}
	/**
	 * Hook: woocommerce_before_shop_loop.
	 *
	 * @hooked woocommerce_output_all_notices - 10
	 * @hooked woocommerce_result_count - 20
	 * @hooked woocommerce_catalog_ordering - 30
	 */
	do_action('woocommerce_before_shop_loop');

	woocommerce_product_loop_start();

	if (defined('WP_DEBUG') && WP_DEBUG) {
		error_log('[twmp] before have_posts: wc_get_loop_prop(total)=' . (function_exists('wc_get_loop_prop') ? wc_get_loop_prop('total') : 'unavailable'));
		error_log('[twmp] before have_posts: $GLOBALS["woocommerce_loop"]=' . print_r($GLOBALS['woocommerce_loop'], true));
		error_log('[twmp] before have_posts: $wp_query->found_posts=' . (isset($wp_query->found_posts) ? $wp_query->found_posts : 'unset'));
	}

	if (wc_get_loop_prop('total')) {
		while (have_posts()) {
			the_post();

			/**
			 * Hook: woocommerce_shop_loop.
			 */
			do_action('woocommerce_shop_loop');

			wc_get_template_part('content', 'product');
		}
	}

	woocommerce_product_loop_end();

	/**
	 * Hook: woocommerce_after_shop_loop.
	 *
	 * @hooked woocommerce_pagination - 10
	 */
	do_action('woocommerce_after_shop_loop');
} else {
	if (!$is_empty_search) {
		/**
		 * Hook: woocommerce_no_products_found.
		 *
		 * @hooked wc_no_products_found - 10
		 */
		do_action('woocommerce_no_products_found');
	}
}

/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action('woocommerce_after_main_content');

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action('woocommerce_sidebar');

get_footer('shop');
