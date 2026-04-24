<?php
add_action('wp_ajax_search_order_by_phone', 'search_order_by_phone');
add_action('wp_ajax_nopriv_search_order_by_phone', 'search_order_by_phone');

function search_order_by_phone()
{
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    if (empty($phone)) {
        wp_send_json_error(['message' => esc_html__( 'Please enter phone number.', 'twmp-phonghoa' )]);
    }

    $args = [
        'billing_phone' => $phone
    ];

    $orders = wc_get_orders($args);

    if (!$orders) {
        wp_send_json_error(['message' => esc_html__( 'Order not found.', 'twmp-phonghoa' )]);
    }

    ob_start();
    foreach ($orders as $order) {
    ?>
        <div class="overflows">
            <table>
                <tbody>
                    <tr>
                        <th colspan="4"><?php echo esc_html__( 'Order code', 'twmp-phonghoa' ); ?> <?php echo esc_html( $order->get_id() ); ?>
                            <span><?php echo wc_get_order_status_name($order->get_status()); ?></span>
                        </th>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo esc_html__( 'Product', 'twmp-phonghoa' ); ?></td>
                        <td class="quantity"><?php echo esc_html__( 'Quantity', 'twmp-phonghoa' ); ?></td>
                        <td class="total"><?php echo esc_html__( 'Total', 'twmp-phonghoa' ); ?></td>
                    </tr>
                    <?php foreach ($order->get_items() as $item): ?>
                        <tr>
                            <td class="thumbnail">
                                <?php echo wp_kses_post( $item->get_product()->get_image( [50, 50] ) ); ?>
                            </td>
                            <td class="name"><?php echo esc_html( $item->get_name() ); ?></td>
                            <td class="quantity"><?php echo esc_html( $item->get_quantity() ); ?></td>
                            <td class="total"><?php echo wc_price( esc_html( $item->get_total() ) ); ?></td>
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
	register_rest_route('twmp/v1', '/search_order_by_phone', [
		'methods'  => 'POST',
		'callback' => 'twmp_search_order_by_phone_rest',
		'permission_callback' => '__return_true',
		'args' => [
			'phone' => [
				'required' => true,
			]
			// '_wpnonce' => [
			// 	'required' => true,
			// ],
		]
	]);
});

function twmp_search_order_by_phone_rest(WP_REST_Request $request) {
	// $nonce = $request->get_param('_wpnonce');

	// if (!wp_verify_nonce($nonce, 'twmp_search_order')) {
	// 	return new WP_REST_Response([
	// 		'success' => false,
	// 		'message' => __('Invalid nonce.', 'twmp-phonghoa')
	// 	], 403);
	// }

	$phone = sanitize_text_field($request->get_param('phone'));

	if (empty($phone)) {
		return new WP_REST_Response([
			'success' => false,
			'message' => __('Please enter phone number.', 'twmp-phonghoa')
		], 400);
	}

	$args = ['billing_phone' => $phone];
	$orders = wc_get_orders($args);

	if (empty($orders)) {
		return new WP_REST_Response([
			'success' => false,
			'message' => __('Order not found.', 'twmp-phonghoa')
		], 404);
	}

	ob_start();
	foreach ($orders as $order) {
		?>
		<div class="overflows">
			<table>
				<tbody>
					<tr>
						<th colspan="4"><?php echo esc_html__('Order code', 'twmp-phonghoa'); ?> <?php echo esc_html( $order->get_id() ); ?>
							<span><?php echo wc_get_order_status_name($order->get_status()); ?></span>
						</th>
					</tr>
					<tr>
						<td colspan="2"><?php echo esc_html__('Product', 'twmp-phonghoa'); ?></td>
						<td class="quantity"><?php echo esc_html__('Quantity', 'twmp-phonghoa'); ?></td>
						<td class="total"><?php echo esc_html__('Total', 'twmp-phonghoa'); ?></td>
					</tr>
					<?php foreach ($order->get_items() as $item): ?>
						<tr>
							<td class="thumbnail"><?php echo wp_kses_post( $item->get_product()->get_image( [50, 50] ) ); ?></td>
							<td class="name"><?php echo esc_html( $item->get_name() ); ?></td>
							<td class="quantity"><?php echo esc_html( $item->get_quantity() ); ?></td>
							<td class="total"><?php echo esc_html( wc_price( $item->get_total() ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
	$html = ob_get_clean();

	return new WP_REST_Response([
		'success' => true,
		'html'    => $html
	]);
}