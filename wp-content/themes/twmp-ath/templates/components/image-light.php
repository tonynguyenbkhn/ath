<?php

if (!defined('ABSPATH')) {
	exit;
}

$data = wp_parse_args($args, [
	'class' => '',
	'side' => '',
	'src' => '',
	'alt' => '',
	'width' => 0,
	'height' => 0,
]);

$src = !empty($data['src']) ? esc_url($data['src']) : '';

if (empty($src)) {
	return;
}

$classes = ['image__light'];
$side = sanitize_key((string) $data['side']);

if (in_array($side, ['left', 'right'], true)) {
	$classes[] = 'image__light--' . $side;
}

if (!empty($data['class'])) {
	$classes[] = trim((string) $data['class']);
}
?>

<div class="<?php echo esc_attr(implode(' ', array_filter($classes))); ?>">
	<img
		src="<?php echo esc_url($src); ?>"
		alt="<?php echo esc_attr($data['alt']); ?>"
		<?php if (!empty($data['width'])) : ?>width="<?php echo esc_attr(absint($data['width'])); ?>"<?php endif; ?>
		<?php if (!empty($data['height'])) : ?>height="<?php echo esc_attr(absint($data['height'])); ?>"<?php endif; ?>
	>
</div>
