<?php

/**
 * Template Name: Flexible
 * Template Post Type: page
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();

?>
<div class="flexible-content">
	<?php
	if ( !is_front_page() and is_page() ):
	get_template_part(
		'templates/components/image-light',
		null,
		[
			'class' => 'event__light',
			'side' => 'right',
			'src' => TWMP_IMG_URI . '/event-light.png',
			'alt' => esc_html(get_the_title()),
			'width' => 1052,
			'height' => 816,
		]
	);
	endif;
	?>
	<?php
	get_template_part('templates/content/flexible');
	?>
</div>
<?php
get_footer();
