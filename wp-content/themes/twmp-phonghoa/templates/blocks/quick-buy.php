<?php
global $product;

$condition = $product->is_type('simple') || $product->is_type('variable');

$button_class = 'quick_buy rounded-0';

$button_text = __("Buy Now", "twmp-phonghoa");
$sub_button_text = __("Home delivery (COD) or pick up at store", "twmp-phonghoa");

if ( $condition ) : 
?>
    
    <?php if ( $product->is_type('simple') ) : ?>
        <input type="hidden" name="product_id" value="<?php echo esc_attr( $product->get_id() ); ?>">
        <a data-block="quick-buy" href="#" class="w-100 text-white <?php echo esc_attr( $button_class ); ?>">
            <span><?php echo esc_html( $button_text ); ?></span>
            <span><?php echo esc_html( $sub_button_text ); ?></span>
        </a>

    <?php else : ?>
        <a data-block="quick-buy" href="#" class="text-white <?php echo esc_attr( $button_class ); ?>">
            <span><?php echo esc_html( $button_text ); ?></span>
            <span><?php echo esc_html( $sub_button_text ); ?></span>
        </a>
    <?php endif; ?>

<?php endif; ?>