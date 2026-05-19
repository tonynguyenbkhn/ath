<?php

/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package taiwebmienphi
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();

$contact_page = get_page_by_path('contact-us');
$contact_url = $contact_page ? get_permalink($contact_page) : home_url('/contact-us/');
?>

<div class="not-found-page">
	<div class="container">
		<div class="not-found-content">
			<div class="not-found-eyebrow">
				<?php esc_html_e('Error 404', 'twmp-ath'); ?>
			</div>
			<h1 class="not-found-title"><?php esc_html_e('Page not found', 'twmp-ath'); ?></h1>
			<p class="not-found-message">
				<?php esc_html_e('The page you are looking for may have been removed, renamed, or never existed.', 'twmp-ath'); ?>
			</p>
			<div class="not-found-actions">
				<?php get_template_part('templates/components/button', null, [
					'class' => 'button-default button-style-primary',
					'button_text' => esc_html__('Contact Us', 'twmp-ath'),
					'button_url' => $contact_url,
				]); ?>

				<?php get_template_part('templates/components/button', null, [
					'class' => 'button-default button-style-outline-dark',
					'button_text' => esc_html__('Back Home', 'twmp-ath'),
					'button_url' => home_url('/'),
				]); ?>
			</div>
		</div>
	</div>
</div>

<?php
get_footer();
