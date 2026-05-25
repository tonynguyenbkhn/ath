<?php

if (! defined('ABSPATH')) {
    exit;
}

$data = wp_parse_args(
    $args,
    [
        'type'  => '',
        'class' => '',
        'style' => '',
    ]
);

$type = sanitize_key((string) $data['type']);

if ('' === $type) {
    return;
}

$classes = [
    'section-shape',
    'section-shape--' . $type,
];

if (! empty($data['class'])) {
    $classes[] = trim((string) $data['class']);
}
?>

<div class="<?php echo esc_attr(implode(' ', array_filter($classes))); ?>"<?php if (! empty($data['style'])) : ?> style="<?php echo esc_attr($data['style']); ?>"<?php endif; ?> aria-hidden="true"></div>
