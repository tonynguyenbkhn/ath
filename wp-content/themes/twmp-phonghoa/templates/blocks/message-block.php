<?php 

$data = wp_parse_args($args, [
	'content' => '',
]);
?>
<?php if (!empty($data['content'])) : ?>
  <div class="message-block">
    <div class="container message-block__container">
      <?php echo wp_kses_post( $data['content'] ); ?>
    </div>
  </div>
<?php endif; ?>