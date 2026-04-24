<?php

$data = wp_parse_args(
	$args,
	array(
		'class' => '',
	)
);

$_class  = 'toggle-show-content';
$_class .= ! empty($data['class']) ? esc_attr(' ' . $data['class']) : '';

?>

<div class="<?php echo esc_attr($_class); ?>">
    <a class="js-btn-toggle-content text-center d-block" href="">        
        <span class="show-more"><?php echo esc_html__('Show more', 'twmp-phonghoa'); echo twmp_get_svg_icon('down-arrow') ?></span>
        <span class="show-less"><?php echo esc_html__('Show less', 'twmp-phonghoa'); echo twmp_get_svg_icon('up-arrow') ?></span>
    </a>
</div>