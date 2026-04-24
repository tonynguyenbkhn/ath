<?php

$data = wp_parse_args($args, [
    'class' => '',
]);

$_class = 'loader';
$_class .= !empty($data['class']) ? ' ' . $data['class'] : '';

?>
<div class="<?php echo esc_attr($_class) ?>">
	<div class="loader__inner">
    <div class="spinner">
      <div class="double-bounce1"></div>
      <div class="double-bounce2"></div>
    </div>
	</div>
</div>
