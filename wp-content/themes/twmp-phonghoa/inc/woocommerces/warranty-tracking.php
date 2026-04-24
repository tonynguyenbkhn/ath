<?php
add_action('wp_ajax_search_warranty_lookup', 'search_warranty_lookup');
add_action('wp_ajax_nopriv_search_warranty_lookup', 'search_warranty_lookup');

function search_warranty_lookup()
{
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    if (empty($phone)) {
        wp_send_json_error(['message' => esc_html__('Please enter phone number.', 'twmp-phonghoa')]);
    }

    $args = [
        'billing_phone' => $phone
    ];

    $orders = wc_get_orders($args);

    if (!$orders) {
        wp_send_json_error(['message' => esc_html__('Order not found.', 'twmp-phonghoa')]);
    }

    ob_start();
    foreach ($orders as $order) {
?>
        <div class="overflows">
            <table>
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Customer Name', 'twmp-phonghoa'); ?></th>
                        <th><?php echo esc_html__('Warranty Code', 'twmp-phonghoa'); ?></th>
                        <th><?php echo esc_html__('Product', 'twmp-phonghoa'); ?></th>
                        <th><?php echo esc_html__('Received Date', 'twmp-phonghoa'); ?></th>
                        <th><?php echo esc_html__('Status', 'twmp-phonghoa'); ?></th>
                        <th><?php echo esc_html__('Description', 'twmp-phonghoa'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    $results = warranty_search($order->get_id());
                    $mabaohanh = '';
                    $status_name = '';
                    if ($results) {
                        foreach ($results as $result) {
                            $result         = warranty_load($result->ID, false);
                            $mabaohanh     .= $result['code'];
                            $status_term    = wp_get_post_terms($result['ID'], 'shop_warranty_status');
                            $status_name      = (isset($status_term[0]) && $status_term[0] instanceof WP_Term) ? $status_term[0]->name : get_term_by('slug', 'new', 'shop_warranty_status')->name;
                        }
                    }
                    foreach ($order->get_items() as $item_idx => $item):
                        $item_warranty = new Warranty_Item($item_idx);
                        $warranty      = warranty_get_order_item_warranty($item);
                        $item_has_rma  = false;

                        if (! empty($item['item_meta']['_bundled_by'])) {
                            continue;
                        }

                        $warranty_string = esc_html(warranty_get_warranty_duration_string($warranty, $order));
                        $item_has_rma    = $item_warranty->has_warranty();

                        if ($item_has_rma) {
                            $order_has_rma = true;
                        }

                        if ($item_warranty->is_expired()) {
                            $warranty_string .= '<br/><strong>' . esc_attr__('Expired Warranty', 'twmp-phonghoa') . '</strong>';
                        }
                    ?>
                        <tr>
                            <td class="fullname" data-label="<?php echo esc_attr__('Customer Name:', 'twmp-phonghoa'); ?>"><?php echo esc_html( $order->get_billing_last_name() ); ?></td>
                            <td class="code-warranty" data-label="<?php echo esc_attr__('Warranty Code:', 'twmp-phonghoa'); ?>"><?php echo esc_html( $mabaohanh ); ?></td>
                            <td class="product-name" data-label=<?php echo esc_attr__("Product:", 'twmp-phonghoa'); ?>>
                                <?php
                                // translators: %1$s: Item name, %2$d: Item quantity.
                                printf(esc_html__('%1$s x %2$d', 'twmp-phonghoa'), esc_html($item->get_name()), esc_html($item->get_quantity()));

                                wc_display_item_meta($item);
                                ?>
                            </td>
                            <td class="date" data-label=<?php echo esc_attr__('Received Date:', 'twmp-phonghoa'); ?>><?php echo esc_html( $warranty_string ); ?></td>
                            <td class="status" data-label=<?php echo esc_attr__('Status:', 'twmp-phonghoa'); ?>><?php echo esc_html( $status_name ); ?></td>
                            <td class="description" data-label="<?php echo esc_attr__("Description:", 'twmp-phonghoa'); ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php
    }

    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}

add_action('rest_api_init', function () {
    register_rest_route('twmp/v1', '/search_warranty_lookup', [
        'methods'             => 'POST',
        'callback'            => 'twmp_search_warranty_lookup_rest',
        'permission_callback' => '__return_true', // Cho phép cả khách dùng
    ]);
});

function twmp_search_warranty_lookup_rest(WP_REST_Request $request)
{
    $phone = sanitize_text_field($request->get_param('phone'));

    if (empty($phone)) {
        return new WP_REST_Response([
            'success' => false,
            'message' => __('Please enter phone number.', 'twmp-phonghoa')
        ], 400);
    }

    $args = [
        'billing_phone' => $phone
    ];

    $orders = wc_get_orders($args);

    if (!$orders) {
        return new WP_REST_Response([
            'success' => false,
            'message' => __('Order not found.', 'twmp-phonghoa')
        ], 404);
    }

    ob_start(); ?>
    <div class="overflows">
        <table>
            <thead>
                <tr>
                    <th style="width: 200px"><?php esc_html_e('Customer Name', 'twmp-phonghoa'); ?></th>
                    <th style="width: 150px"><?php esc_html_e('Warranty Code', 'twmp-phonghoa'); ?></th>
                    <th><?php esc_html_e('Product', 'twmp-phonghoa'); ?></th>
                    <th style="width: 150px"><?php esc_html_e('Received Date', 'twmp-phonghoa'); ?></th>
                    <th style="width: 150px"><?php esc_html_e('Status', 'twmp-phonghoa'); ?></th>
                    <th style="width: 100px"><?php esc_html_e('Description', 'twmp-phonghoa'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order) {
                    // Bắt đầu HTML output
                ?>
                    <?php
                    $results = warranty_search($order->get_id());
                    $mabaohanh = '';
                    $status_name = '';

                    if ($results) {
                        foreach ($results as $result) {
                            $result = warranty_load($result->ID, false);
                            $mabaohanh .= $result['code'];

                            $status_term = wp_get_post_terms($result['ID'], 'shop_warranty_status');
                            $status_name = (isset($status_term[0]) && $status_term[0] instanceof WP_Term)
                                ? $status_term[0]->name
                                : get_term_by('slug', 'new', 'shop_warranty_status')->name;
                        }
                    }

                    foreach ($order->get_items() as $item_idx => $item) {
                        if (!empty($item['item_meta']['_bundled_by'])) {
                            continue;
                        }

                        $item_warranty = new Warranty_Item($item_idx);
                        $warranty = warranty_get_order_item_warranty($item);
                        $warranty_string = esc_html(warranty_get_warranty_duration_string($warranty, $order));

                        if ($item_warranty->is_expired()) {
                            $warranty_string .= '<br/><strong>' . esc_html__('Expired Warranty', 'twmp-phonghoa') . '</strong>';
                        }
                    ?>
                        <tr>
                            <td class="fullname"><?php echo esc_html($order->get_billing_last_name()); ?></td>
                            <td class="code-warranty"><?php echo esc_html($mabaohanh); ?></td>
                            <td class="product-name">
                                <?php
                                printf(esc_html__('%1$s x %2$d', 'twmp-phonghoa'), esc_html($item->get_name()), esc_html($item->get_quantity()));
                                wc_display_item_meta($item);
                                ?>
                            </td>
                            <td class="date"><?php echo esc_html( $warranty_string ); ?></td>
                            <td class="status"><?php echo esc_html($status_name); ?></td>
                            <td class="description"></td>
                        </tr>
                    <?php
                    }
                    ?>

                <?php
                } ?>
            </tbody>
        </table>
    </div>
<?php $html = ob_get_clean();

    return new WP_REST_Response([
        'success' => true,
        'html'    => $html
    ]);
}
