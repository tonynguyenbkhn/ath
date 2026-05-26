<?php

if (!defined('ABSPATH')) {
	exit;
}

$language_items = function_exists('pll_the_languages')
	? pll_the_languages([
		'raw'           => 1,
		'hide_current'  => 0,
		'hide_if_empty' => 0,
	])
	: [];

$language_icon_map = [
	'en' => 'english',
	'vi' => 'vietnam',
	'fr' => 'french',
];

$current_language_item = null;
if (!empty($language_items) && is_array($language_items)) {
	foreach ($language_items as $language_item) {
		if (!empty($language_item['current_lang'])) {
			$current_language_item = $language_item;
			break;
		}
	}
}

if (empty($current_language_item) && !empty($language_items[0])) {
	$current_language_item = $language_items[0];
}

$current_language_slug = !empty($current_language_item['slug']) ? sanitize_key($current_language_item['slug']) : '';
$current_language_icon = isset($language_icon_map[$current_language_slug]) ? $language_icon_map[$current_language_slug] : $current_language_slug;
$current_language_icon_uri = $current_language_icon ? get_theme_file_uri('/assets/images/icons/' . $current_language_icon . '.svg') : '';

?>

<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<!-- Font Google -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Archivo:ital,wght@0,100..900;1,100..900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'">
	<style>
		@font-face {
			font-family: 'Beautique Display';
			src: url(<?php echo get_theme_file_uri('/assets/fonts/BeautiqueDisplay-Regular.otf'); ?>) format('opentype');
			font-weight: 400;
			font-style: normal;
			font-display: swap;
		}

		@font-face {
			font-family: 'Beautique Display';
			src: url(<?php echo get_theme_file_uri('/assets/fonts/BeautiqueDisplay-Italic.otf'); ?>) format('opentype');
			font-weight: 400;
			font-style: italic;
			font-display: swap;
		}

		@font-face {
			font-family: 'Beautique Display';
			src: url(<?php echo get_theme_file_uri('/assets/fonts/BeautiqueDisplay-Medium.otf'); ?>) format('opentype');
			font-weight: 500;
			font-style: normal;
			font-display: swap;
		}

		@font-face {
			font-family: 'Beautique Display';
			src: url(<?php echo get_theme_file_uri('/assets/fonts/BeautiqueDisplay-MediumItalic.otf'); ?>) format('opentype');
			font-weight: 500;
			font-style: italic;
			font-display: swap;
		}

		@font-face {
			font-family: 'Beautique Display';
			src: url(<?php echo get_theme_file_uri('/assets/fonts/BeautiqueDisplay-Bold.otf'); ?>) format('opentype');
			font-weight: 700;
			font-style: normal;
			font-display: swap;
		}

		@font-face {
			font-family: 'Beautique Display';
			src: url(<?php echo get_theme_file_uri('/assets/fonts/BeautiqueDisplay-BoldItalic.otf'); ?>) format('opentype');
			font-weight: 700;
			font-style: italic;
			font-display: swap;
		}

		@font-face {
			font-family: 'Beautique Display';
			src: url(<?php echo get_theme_file_uri('/assets/fonts/BeautiqueDisplay-Black.otf'); ?>) format('opentype');
			font-weight: 900;
			font-style: normal;
			font-display: swap;
		}

		@font-face {
			font-family: 'Beautique Display';
			src: url(<?php echo get_theme_file_uri('/assets/fonts/BeautiqueDisplay-BlackItalic.otf'); ?>) format('opentype');
			font-weight: 900;
			font-style: italic;
			font-display: swap;
		}
	</style>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<header id="header-sticky">
		<div class="header__main position-relative">
			<div class="container header__container">
				<div class="header__row">
					<div class="flex-auto header__col header__logo">
						<?php get_template_part('template-parts/headers/logo', null, []); ?>
					</div>
					<div class="flex-auto header__col header__nav fw-normal">
						<?php get_template_part('template-parts/headers/main-nav', null, []); ?>
					</div>
					<div class="flex-auto header__col header__actions">
						<div class="header__menu-icons">
							<div class="">
								<?php get_template_part('template-parts/headers/icon-search', null, []); ?>
							</div>
							<?php if (!empty($language_items) && is_array($language_items) && !empty($current_language_icon_uri)) : ?>
								<div class="header__menu-icons__language">
									<button
										type="button"
										class="header__language-switcher-toggle button-reset"
										aria-label="<?php echo esc_attr__('Select language', 'twmp-ath'); ?>"
										aria-expanded="false"
										onclick="var menu=this.nextElementSibling; var expanded=menu.hidden; menu.hidden=!expanded; this.setAttribute('aria-expanded', expanded ? 'true' : 'false');">
										<img
											src="<?php echo esc_url($current_language_icon_uri); ?>"
											alt="<?php echo esc_attr($current_language_item['name'] ?? __('Language', 'twmp-ath')); ?>"
											width="24"
											height="24"
											loading="eager"
											decoding="async" />
										<span class="screen-reader-text"><?php echo esc_html($current_language_item['name'] ?? __('Language', 'twmp-ath')); ?></span>
									</button>
									<div class="header__language-switcher-menu" hidden>
										<?php foreach ($language_items as $language_item) :
											$language_slug = !empty($language_item['slug']) ? sanitize_key($language_item['slug']) : '';
											$language_icon = isset($language_icon_map[$language_slug]) ? $language_icon_map[$language_slug] : $language_slug;
											$language_icon_uri = $language_icon ? get_theme_file_uri('/assets/images/icons/' . $language_icon . '.svg') : '';

											if (empty($language_icon_uri)) {
												continue;
											}

											$is_current_language = !empty($language_item['current_lang']);
										?>
											<a
												class="header__language-switcher-item<?php echo $is_current_language ? ' is-current' : ''; ?>"
												href="<?php echo esc_url($language_item['url']); ?>"
												<?php echo $is_current_language ? 'aria-current="true"' : ''; ?>>
												<img
													src="<?php echo esc_url($language_icon_uri); ?>"
													alt="<?php echo esc_attr($language_item['name']); ?>"
													width="24"
													height="24"
													loading="lazy"
													decoding="async" />
												<span class="screen-reader-text"><?php echo esc_html($language_item['name']); ?></span>
											</a>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endif; ?>
							<div class="th-menu-toggle"><?php echo twmp_get_svg_icon('menu') ?></div>
							<div class="header__menu-icons__button">
								<?php get_template_part('templates/components/button', null, [
									'class' => 'bg-primary-500 text-white typo-system-button button-default',
									'button_text' => esc_html__('Contact Us', 'twmp-ath'),
									'button_url' => '#',
									'button_link_target' => '_blank',
								]); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</header>

	<?php do_action('twmp_after_header'); ?>