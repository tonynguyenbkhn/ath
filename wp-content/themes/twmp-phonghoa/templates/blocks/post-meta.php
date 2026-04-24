<?php

$data = wp_parse_args($args, [
	'date' => true,
	'author' => true,
	'categories' => true
]);

global $post;

$post_meta_items = twmp_get_post_meta_items($data);

$_class = 'reset d-flex flex-wrap post-meta';
$_class .= !empty( $data['class'] ) ? esc_attr( ' ' . $data['class'] ) : '';

if ( !empty( $post_meta_items ) ) :
?>
	<ul class="<?php echo esc_attr( $_class ); ?>">
		<?php foreach( $post_meta_items as $item ) : ?>
			<li class="d-inline-flex align-items-center post-meta__item">
				<span class="icon pe-none" aria-hidden="true"><?php echo wp_kses_post( $item['icon'] ); ?></span>
				<span class="text"><?php echo esc_html( $item['text'] ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif;
