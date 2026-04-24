<?php
$data = wp_parse_args($args, [
	'class' => '',
	'text' => '',
	'show_form_search' => false,
	'show_title' => true,
	'tag_h1' => true,
	'show_breadcrumbs' => true
]);
$_class  = 'page__title-area';
$_class .= ! empty($data['class']) ? esc_attr(' ' . $data['class']) : '';
?>

<section class="<?php echo esc_attr($_class); ?>">
	<div class="container">
		<div class="row">
			<?php if ($data['show_form_search']) : ?>
				<div class="col-xl-6 col-lg-6 col-md-12 col-12">
				<?php else : ?>
					<div class="col-xl-12 col-lg-12 col-md-12 col-12">
					<?php endif; ?>
					<div class="page__title-content">
						<?php if ($data['show_title']) : ?>
							<?php if ($data['text']): ?>
								<h1 class="page__title"><?php echo esc_html($data['text']) ?></h1>
							<?php elseif (is_archive()): ?>
								<?php $data['tag_h1'] ? the_archive_title('<h1 class="page__title">', '</h1>') : the_archive_title('<div class="page__title h1">', '</div>'); ?>
							<?php elseif (is_singular()): ?>
								<?php the_title('<h1 class="page__title">', '</h1>'); ?>
							<?php elseif (is_tag()): ?>
								<?php $data['tag_h1'] ? the_archive_title('<h1 class="page__title">', '</h1>') : the_archive_title('<div class="page__title h1">', '</div>'); ?>
							<?php endif; ?>
						<?php endif; ?>
						<?php if ($data['show_breadcrumbs']) : ?>
							<div class="breadcrumbs">
								<?php twmp_breadcrumbs(); ?>
							</div>
						<?php endif; ?>
					</div>
					</div>
					<?php if ($data['show_form_search']): ?>
						<div class="col-xl-6 col-lg-6 col-md-12 col-12 d-flex justify-content-lg-end justify-content-md-center justify-content-sm-center align-items-center">
							<div class="hero__search">
								<form method="get" action="<?php echo esc_url(home_url('/tai-web-mien-phi/')); ?>">
									<div class="hero__search-inner d-flex justify-content-lg-start justify-content-md-center justify-content-sm-center">
										<div class="hero__search-input">
											<span>
												<?php echo twmp_get_svg_icon('search') ?>
											</span>
											<input type="search" value="<?php echo esc_attr(get_search_query()); ?>" name="s" class="not-default" placeholder="<?php echo esc_attr__('Search for templates', 'twmp-phonghoa') ?>">
										</div>
										<button type="submit" class="hero__search-submit ml-20"> <span></span> <?php echo esc_html__('Search', 'twmp-phonghoa') ?></button>
									</div>
								</form>
							</div>
						</div>
					<?php endif; ?>
				</div>
		</div>
</section>