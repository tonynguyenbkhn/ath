<?php

if (! defined('ABSPATH')) {
    exit;
}

$data = wp_parse_args(
    $args,
    [
        'type'  => '',
        'class' => '',
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

<div class="<?php echo esc_attr(implode(' ', array_filter($classes))); ?>" aria-hidden="true"></div>
