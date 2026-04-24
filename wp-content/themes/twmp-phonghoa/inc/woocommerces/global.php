<?php

// add_filter('woocommerce_coupons_enabled', 'no');

remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10);
remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);

add_filter('woocommerce_breadcrumb_defaults', function ($args) {
    $args['wrap_before'] = '<nav class="woocommerce-breadcrumb" aria-label="Breadcrumb"><div class="container woocommerce-breadcrumb__container">';
    $args['wrap_after'] = '</div></nav>';

    return $args;
}, 10);

// add_filter('woocommerce_widget_cart_item_quantity', 'wcs_update_quantity_mini_cart', 10, 3);

function wcs_update_quantity_mini_cart($output, $cart_item, $cart_item_key)
{
    $product        = $cart_item['data'];
    $stock_quantity = $product->get_stock_quantity();
    $product_price  = WC()->cart->get_product_price($product);

    ob_start(); ?>
    <span class="mini-cart__info">
        <span class="mini-cart-quantity quantity">
            <div class="quantity-wrapper" data-block="quantity"><span class="product-qty" data-qty="minus"><?php echo twmp_get_svg_icon('minus'); ?></span>
                <input type="number" data-cart_item_key="<?php echo esc_attr($cart_item_key); ?>" class="input-text qty text" step="1" min="1" max="<?php echo esc_attr($stock_quantity ? $stock_quantity : ''); ?>" value="<?php echo esc_attr($cart_item['quantity']); ?>" inputmode="numeric" />
                <span class="product-qty" data-qty="plus"><?php echo twmp_get_svg_icon('plus'); ?></span>
            </div>
        </span>

        <span class="mini-cart-product-price"><?php echo wp_kses_post($product_price); ?></span>
    </span>
    <?php
    return ob_get_clean();
}

add_filter('woocommerce_add_to_cart_fragments', 'woocommerce_header_add_to_cart_fragment');
function woocommerce_header_add_to_cart_fragment($fragments)
{
    global $woocommerce;
    ob_start();
    get_template_part('template-parts/footers/wcs-mini-cart', null, []);
    $mini_cart = ob_get_clean();
    // $fragments['.mini-cart__count'] = '<span class="mini-cart__count">' . $woocommerce->cart->cart_contents_count . '</span>';
    $fragments['.cart-icon__count'] = '<span class="cart-icon__count">' . $woocommerce->cart->cart_contents_count . '</span>';
    $fragments['div.widget_shopping_cart_content'] = $mini_cart;
    return $fragments;
}

function remove_woocommerce_styles()
{
    wp_dequeue_style('woocommerce-general');
    wp_dequeue_style('woocommerce-layout');
    wp_dequeue_style('woocommerce-smallscreen');
}
add_action('wp_enqueue_scripts', 'remove_woocommerce_styles', 99);

add_action('admin_footer', 'twmp_toggle_description_script');
function twmp_toggle_description_script()
{
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'product') :
        echo '<style>
                .woocommerce-product-description .postbox-header {
                    position: relative;
                    cursor: pointer;
                }

                .woocommerce-product-description .postbox-header::after {
                    content: "▲";
                    position: absolute;
                    right: 10px;
                    top: 50%;
                    transform: translateY(-50%);
                    transition: transform 0.3s ease;
                    font-size: 14px;
                }

                .woocommerce-product-description.open .postbox-header::after {
                    content: "▼";
                }
            </style>';
    ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const box = document.querySelector('#postdivrich');
                const content = document.querySelector('#wp-content-wrap');

                if (box && content) {
                    content.style.display = 'none';

                    box.addEventListener('click', function(e) {
                        if (e.target.closest('#wp-content-wrap')) return;

                        const isOpen = box.classList.contains('open');
                        content.style.display = isOpen ? 'none' : 'block';
                        box.classList.toggle('open', !isOpen);
                    });
                }
            });
        </script>
    <?php
    endif;
}

// add_action( 'wp_enqueue_scripts', 'remove_ywcas_search_block_script', 100 );

// function remove_ywcas_search_block_script() {
//     wp_dequeue_script( 'ywcas-search-block-block-frontend' );
//     wp_deregister_script( 'ywcas-search-block-block-frontend' );
// }