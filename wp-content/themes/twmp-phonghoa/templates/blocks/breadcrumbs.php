<?php
$container = 'container';
$_class = !empty($class) ? ' ' . esc_attr($class) : '';

?>

<div class="breadcrumbs <?php echo esc_attr($_class); ?>">
	<div class="<?php echo esc_attr($container); ?> breadcrumbs__container">
		<?php twmp_breadcrumbs(); ?>
	</div>
</div>