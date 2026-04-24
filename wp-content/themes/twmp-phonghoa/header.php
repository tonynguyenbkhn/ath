<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<!-- Font Google -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<header data-block="header-main" id="header-sticky">
		<?php
		if (!is_front_page() && is_active_sidebar('header-top')) {
		?>
			<div class="header__top">
				<div class="container header__container">
					<?php dynamic_sidebar('header-top'); ?>
				</div>
			</div><!-- End Footer Top -->
		<?php
		}
		?>
		<?php
		if (is_front_page() && is_active_sidebar('header-top-home')) {
		?>
			<div class="header__top header__top-home">
				<div class="container header__container">
					<?php dynamic_sidebar('header-top-home'); ?>
				</div>
			</div><!-- End Footer Top -->
		<?php
		}
		?>
		<div class="header__main position-relative">
			<div class="container header__container">
				<div class="row header__row">
					<div class="flex-auto header__col header__logo">
						<?php get_template_part('template-parts/headers/logo', null, []); ?>
					</div>
					<div class="flex-auto header__col header__nav">
						<div class="ywcas-popular-searches-wrapper">
							<?php
							if ( function_exists( 'get_product_search_form' ) ) {
								get_product_search_form();
							} else {
								get_search_form();
							}
							?>

							<div id="rlvlive"></div>
						</div>
					</div>
					<div class="flex-auto header__col header__actions">
						<div class="header__menu-icons">
							<?php echo do_shortcode('[custom_element id="82"]'); ?>
							<?php
							get_template_part('template-parts/headers/icon-cart', null, [
								'class' => 'header__menu-icons__item header__menu-icons__item--cart header__menu-icons__link js-minicart-trigger'
							]);
							?>
							<div class="th-menu-toggle"><?php echo twmp_get_svg_icon('menu') ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="header__bottom">
			<div class="container">
				<?php get_template_part('template-parts/headers/main-nav', null, []); ?>
			</div>

			<?php /* get_template_part('templates/blocks/category-grid', null, [
                'class' => 'category-grid',
                'enable_container' => true,
                'grid_css_class' => 'col',
                'class_container' => 'header__bottom-container'
            ]) */ ?>
		</div>

	</header>

	<?php do_action('twmp_after_header'); ?>
