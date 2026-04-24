<?php

remove_action('woocommerce_checkout_order_review', 'woocommerce_order_review', 10);
remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10);
remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
remove_action('woocommerce_before_checkout_form_cart_notices', 'woocommerce_output_all_notices', 10);

// Layout
add_action('woocommerce_checkout_before_customer_details',  'wcs_checkout_page_block_open', 10);
add_action('woocommerce_checkout_after_customer_details', 'wcs_checkout_page_block_between', 40);
add_action('woocommerce_checkout_after_order_review', 'wcs_checkout_page_block_close', 90);

// add_filter('woocommerce_default_address_fields', 'wcs_checkout_update_fields_order');
// add_filter('woocommerce_checkout_fields', 'wcs_checkout_update_placeholder_fields');

// Move shipping section
// add_filter('woocommerce_cart_ready_to_calc_shipping', '__return_false');

add_action('woocommerce_before_checkout_form', 'wcs_checkout_page_open', 5);
// add_action('woocommerce_before_checkout_form', 'wcs_checkout_render_shop_steps', 6);

function wcs_checkout_page_open()
{
  $block_attributes = array(
    'endpoint' => [
      'get_tinh_tp',
      'get_quan_huyen',
      'get_xa_phuong',
    ],
    'nonce' => wp_create_nonce('twmp_checkout_nonce')
  );
  echo '<div class="page-block page-block--checkout" data-settings="' . esc_attr(json_encode($block_attributes)) . '" data-block="checkout-custom">';
}

add_action('woocommerce_after_checkout_form', 'wcs_checkout_page_close', 100);

function wcs_checkout_page_close()
{
  echo '</div>';
}

function wcs_checkout_page_block_open()
{
  echo '<div class="grid page-block__grid">';
  echo '<div class="grid__col page-block__col page-block__col--main">';
}

function wcs_checkout_page_block_between()
{
  echo '</div>';
  echo '<div class="grid__col page-block__col page-block__col--sidebar">';
}

function wcs_checkout_page_block_close()
{
  echo '</div>';
  echo '</div>';
}

function wcs_checkout_update_fields_order($fields)
{
  unset($fields['company']);
  unset($fields['address_2']);
  unset($fields['postcode']);

  return $fields;
}

function wcs_checkout_update_placeholder_fields($fields)
{
  $fields['billing']['billing_first_name']['placeholder'] = esc_html__('First name', 'twmp-phonghoa');
  $fields['billing']['billing_last_name']['placeholder'] = esc_html__('Last name', 'twmp-phonghoa');
  $fields['billing']['billing_phone']['placeholder'] = esc_html__('Phone', 'twmp-phonghoa');
  $fields['billing']['billing_city']['placeholder'] = esc_html__('City', 'twmp-phonghoa');
  $fields['billing']['billing_email']['placeholder'] = esc_html__('Email', 'twmp-phonghoa');

  $fields['shipping']['shipping_first_name']['placeholder'] = esc_html__('First name', 'twmp-phonghoa');
  $fields['shipping']['shipping_last_name']['placeholder'] = esc_html__('Last name', 'twmp-phonghoa');
  $fields['shipping']['shipping_phone']['placeholder'] = esc_html__('Phone', 'twmp-phonghoa');
  $fields['shipping']['shipping_city']['placeholder'] = esc_html__('City', 'twmp-phonghoa');

  return $fields;
}

function wcs_checkout_render_shop_steps()
{
  get_template_part('templates/blocks/shop-steps', null, []);
}

add_action('devvn_checkout_fields', function ($fields) {
  unset($fields['billing']['billing_state']);
  unset($fields['billing']['billing_city']);
  unset($fields['billing']['billing_address_1']);
  unset($fields['billing']['billing_address_2']);

  return $fields;
}, 10, 1);


add_filter('woocommerce_checkout_fields', function ($fields) {

  $fields['billing']['billing_sexy'] = array(
    'type'     => 'billing_sexy_custom',
    'required' => true,
    'priority' => 5,
  );

  $fields['billing']['billing_shipping_label'] = array(
    'type'     => 'billing_shipping_label_custom',
    'priority' => 200, // sau email
  );

  $fields['billing']['billing_delivery_address'] = array(
    'type'        => 'text',
    'label'       => '',
    'placeholder' => 'Nhập địa chỉ nhận hàng',
    'required'    => true,
    'class'       => array('form-row-wide'),
    'priority'    => 300,
  );

  $fields['billing']['billing_district_district'] = array(
    'type'        => 'text',
    'label'       => '',
    'placeholder' => 'Nhập quận / huyện',
    'required'    => true,
    'class'       => array('form-row-first'),
    'priority'    => 301,
  );

  $fields['billing']['billing_wards_and_communes'] = array(
    'type'        => 'text',
    'label'       => '',
    'placeholder' => 'Nhập phường / xã',
    'required'    => true,
    'class'       => array('form-row-last'),
    'priority'    => 302,
  );

  // $fields['billing']['billing_city_province_shop'] = array(
  //   'type'              => 'select',
  //   'label'             => esc_html__('Province/City', 'twmp-phonghoa'),
  //   'required'          => true,
  //   'class'             => array('form-row-wide'),
  //   'input_class'       => array('regular-select'),
  //   'options'           => array(
  //     ''                => esc_html__('Province/City', 'twmp-phonghoa'),
  //     'Hồ Chí Minh'     => 'Hồ Chí Minh',
  //   )
  // );
  return $fields;
});

add_filter('woocommerce_form_field_billing_sexy_custom', function ($field, $key, $args, $value) {

  $value = empty($value) ? 'male' : $value;

  ob_start();
?>
  <p class="form-row billing-radio form-row-wide" id="billing_sexy_field" data-priority="10">
    <span class="woocommerce-input-wrapper">
      <input type="radio" class="input-radio " value="male" name="<?php echo esc_attr($key); ?>" id="billing_sexy_male" <?php checked($value, 'male'); ?>>
      <label for="billing_sexy_male" class="radio ">Anh
      </label>
      <input type="radio" class="input-radio " value="female" name="<?php echo esc_attr($key); ?>" id="billing_sexy_female" <?php checked($value, 'female'); ?>>
      <label for="billing_sexy_female" class="radio ">Chị
      </label>
    </span>
  </p>
<?php

  return ob_get_clean();
}, 10, 4);

add_filter('woocommerce_form_field_billing_shipping_label_custom', function ($field, $key, $args, $value) {
  $value = empty($value) ? 'nhan-hang-tai-nha' : $value;
  ob_start();
?>
  <p class="form-row billing-radio form-row-wide" id="billing_delivery_form_field" data-priority="90">
    <label for="billing_delivery_form_nhan-hang-tai-nha" class="">Hình thức nhận hàng
      <span class="optional">(tuỳ chọn)</span>
    </label>
    <span class="woocommerce-input-wrapper">
      <input type="radio" class="input-radio " value="nhan-hang-tai-nha" name="billing_delivery_form" id="billing_delivery_form_nhan-hang-tai-nha" <?php checked($value, 'nhan-hang-tai-nha'); ?>>
      <label for="billing_delivery_form_nhan-hang-tai-nha" class="radio ">Nhận hàng tại nhà
        <span class="optional">(tuỳ chọn)</span>
      </label>
      <input type="radio" class="input-radio " value="nhan-tai-cua-hang" name="billing_delivery_form" id="billing_delivery_form_nhan-tai-cua-hang" <?php checked($value, 'nhan-tai-cua-hang'); ?>>
      <label for="billing_delivery_form_nhan-tai-cua-hang" class="radio ">Nhận tại cửa hàng
        <span class="optional">(tuỳ chọn)</span>
      </label>
    </span>
  </p>
<?php
  return ob_get_clean();
}, 10, 4);

// Lưu custom fields vào order meta
add_action('woocommerce_checkout_update_order_meta', 'twmp_save_custom_checkout_fields');
function twmp_save_custom_checkout_fields($order_id) {
  // Lưu billing_sexy
  if (!empty($_POST['billing_sexy'])) {
    update_post_meta($order_id, '_billing_sexy', sanitize_text_field($_POST['billing_sexy']));
  }

  // Lưu billing_delivery_form (từ billing_shipping_label)
  if (!empty($_POST['billing_delivery_form'])) {
    update_post_meta($order_id, '_billing_delivery_form', sanitize_text_field($_POST['billing_delivery_form']));
  }

  // Lưu các field text bổ sung (mặc dù WooCommerce tự động lưu, nhưng đảm bảo)
  if (!empty($_POST['billing_delivery_address'])) {
    update_post_meta($order_id, '_billing_delivery_address', sanitize_text_field($_POST['billing_delivery_address']));
  }

  if (!empty($_POST['billing_district_district'])) {
    update_post_meta($order_id, '_billing_district_district', sanitize_text_field($_POST['billing_district_district']));
  }

  if (!empty($_POST['billing_wards_and_communes'])) {
    update_post_meta($order_id, '_billing_wards_and_communes', sanitize_text_field($_POST['billing_wards_and_communes']));
  }
}

// Hiển thị custom fields trong admin order details
add_action('woocommerce_admin_order_data_after_billing_address', 'twmp_display_custom_fields_in_admin');
function twmp_display_custom_fields_in_admin($order) {
  $sexy = get_post_meta($order->get_id(), '_billing_sexy', true);
  $delivery_form = get_post_meta($order->get_id(), '_billing_delivery_form', true);
  $delivery_address = get_post_meta($order->get_id(), '_billing_delivery_address', true);
  $district = get_post_meta($order->get_id(), '_billing_district_district', true);
  $wards = get_post_meta($order->get_id(), '_billing_wards_and_communes', true);

  if ($sexy || $delivery_form || $delivery_address || $district || $wards) {
    echo '<div class="address">';
    echo '<h3>' . esc_html__('Thông tin bổ sung', 'twmp-phonghoa') . '</h3>';
    if ($sexy) {
      echo '<p><strong>' . esc_html__('Giới tính:', 'twmp-phonghoa') . '</strong> ' . esc_html($sexy === 'male' ? 'Anh' : 'Chị') . '</p>';
    }
    if ($delivery_form) {
      $delivery_label = $delivery_form === 'nhan-hang-tai-nha' ? 'Nhận hàng tại nhà' : 'Nhận tại cửa hàng';
      echo '<p><strong>' . esc_html__('Hình thức nhận hàng:', 'twmp-phonghoa') . '</strong> ' . esc_html($delivery_label) . '</p>';
    }
    if ($delivery_address) {
      echo '<p><strong>' . esc_html__('Địa chỉ nhận hàng:', 'twmp-phonghoa') . '</strong> ' . esc_html($delivery_address) . '</p>';
    }
    if ($district) {
      echo '<p><strong>' . esc_html__('Quận/Huyện:', 'twmp-phonghoa') . '</strong> ' . esc_html($district) . '</p>';
    }
    if ($wards) {
      echo '<p><strong>' . esc_html__('Phường/Xã:', 'twmp-phonghoa') . '</strong> ' . esc_html($wards) . '</p>';
    }
    echo '</div>';
  }
}

// add_filter('woocommerce_checkout_fields', function ($fields) {
//   $fields['billing']['billing_delivery_address'] = array(
//     'type'        => 'select',
//     'label'       => esc_html__('Province/City', 'twmp-phonghoa'),
//     'required'    => true,
//     'class'       => array('form-row-wide'),
//     'input_class' => 'regular-select',
//     'options'     => get_tinh_thanh_pho()
//   );
//   return $fields;
// });

// add_filter('woocommerce_checkout_fields', function ($fields) {
//   $fields['billing']['billing_district_shop'] = array(
//     'type'        => 'select',
//     'label'       => esc_html__('District', 'twmp-phonghoa'),
//     'required'    => true,
//     'class'       => array('form-row-wide'),
//     'input_class' => array('regular-select'),
//     'options'     => array(
//       ''          => esc_html__('District', 'twmp-phonghoa'),
//       'Quận 1'    => 'Quận 1',
//     )
//   );
//   return $fields;
// });


add_action('wp_enqueue_scripts', function () {
  if (is_checkout()) {
    wp_dequeue_style('select2');
    wp_dequeue_script('select2');

    wp_dequeue_style('selectWoo');
    wp_dequeue_script('selectWoo');

    wp_dequeue_script('wc-enhanced-select');
  }
}, 100);

add_action('woocommerce_after_checkout_form', function () {
?>
  <script>
    jQuery(function($) {
      $('.select2-hidden-accessible').each(function() {
        if ($(this).hasClass('select2-hidden-accessible')) {
          $(this).select2('destroy');
        }
      });
    });
  </script>
<?php
});

add_action('wp_ajax_get_tinh_tp_by_matp', 'load_tinh_tp_ajax');
add_action('wp_ajax_nopriv_get_tinh_tp_by_matp', 'load_tinh_tp_ajax');
function load_tinh_tp_ajax()
{
  // Verify nonce for security
  $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
  if (!wp_verify_nonce($nonce, 'twmp_checkout_nonce')) {
    wp_send_json_error(['message' => 'Security check failed'], 403);
  }

  $data = get_tinh_thanh_pho();
  wp_send_json_success($data);
}

add_action('wp_ajax_get_quan_huyen_by_matp', 'load_quan_huyen_ajax');
add_action('wp_ajax_nopriv_get_quan_huyen_by_matp', 'load_quan_huyen_ajax');
function load_quan_huyen_ajax()
{
  // Verify nonce for security
  $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
  if (!wp_verify_nonce($nonce, 'twmp_checkout_nonce')) {
    wp_send_json_error(['message' => 'Security check failed'], 403);
  }

  // Validate and sanitize matp parameter
  if (empty($_POST['matp'])) {
    wp_send_json_error(['message' => 'Missing required parameter: matp'], 400);
  }

  $matp = sanitize_text_field($_POST['matp']);
  $data = get_quan_huyen();
  $result = [];

  foreach ($data as $item) {
    if ($item['matp'] === $matp) {
      $result[] = $item;
    }
  }

  wp_send_json_success($result);
}

add_action('wp_ajax_get_xa_phuong_by_maqh', 'load_xa_phuong_ajax');
add_action('wp_ajax_nopriv_get_xa_phuong_by_maqh', 'load_xa_phuong_ajax');
function load_xa_phuong_ajax()
{
  // Verify nonce for security
  $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
  if (!wp_verify_nonce($nonce, 'twmp_checkout_nonce')) {
    wp_send_json_error(['message' => 'Security check failed'], 403);
  }

  // Validate and sanitize maqh parameter
  if (empty($_POST['maqh'])) {
    wp_send_json_error(['message' => 'Missing required parameter: maqh'], 400);
  }

  $maqh = sanitize_text_field($_POST['maqh']);
  $data = get_xa_phuong_thi_tran();
  $result = [];

  foreach ($data as $item) {
    if ($item['maqh'] === $maqh) {
      $result[] = $item;
    }
  }

  wp_send_json_success($result);
}


add_action('rest_api_init', function () {
  register_rest_route('twmp/v1', '/get_tinh_tp', [
    'methods'             => 'GET',
    'callback'            => 'twmp_rest_get_tinh_tp',
    'permission_callback' => '__return_true',
  ]);

  register_rest_route('twmp/v1', '/get_quan_huyen', [
    'methods'             => 'GET',
    'callback'            => 'twmp_rest_get_quan_huyen',
    'permission_callback' => '__return_true',
  ]);

  register_rest_route('twmp/v1', '/get_xa_phuong', [
    'methods'             => 'GET',
    'callback'            => 'twmp_rest_get_xa_phuong',
    'permission_callback' => '__return_true',
  ]);
});

function twmp_rest_get_tinh_tp(WP_REST_Request $request)
{
  $data = get_tinh_thanh_pho();
  return new WP_REST_Response($data, 200);
}

function twmp_rest_get_quan_huyen(WP_REST_Request $request)
{
  $matp = $request->get_param('matp');
  if (empty($matp)) {
    return new WP_REST_Response([], 200);
  }

  $data = get_quan_huyen();
  $result = array_filter($data, function ($item) use ($matp) {
    return isset($item['matp']) && $item['matp'] === $matp;
  });

  return new WP_REST_Response(array_values($result), 200);
}

function twmp_rest_get_xa_phuong(WP_REST_Request $request)
{
  $maqh = $request->get_param('maqh');
  if (empty($maqh)) {
    return new WP_REST_Response([], 200);
  }

  $data = get_xa_phuong_thi_tran();
  $result = array_filter($data, function ($item) use ($maqh) {
    return isset($item['maqh']) && $item['maqh'] === $maqh;
  });

  return new WP_REST_Response(array_values($result), 200);
}

add_filter('woocommerce_add_to_cart_redirect', function ($url) {
  // Nếu đang add to cart từ quick-buy (không phải từ page giỏ hàng)
  if (!is_cart()) {
    return wc_get_checkout_url(); // Chuyển luôn đến trang checkout
  }
  return $url;
});

add_filter('woocommerce_checkout_fields', function ($fields) {
  $fields['billing']['billing_first_name']['type'] = 'hidden';
  $fields['billing']['billing_first_name']['required'] = false;
  $fields['billing']['billing_country']['type'] = 'hidden';
  $fields['billing']['billing_country']['required'] = false;
  $fields['billing']['billing_address_1']['type'] = 'hidden';
  $fields['billing']['billing_address_1']['required'] = false;
  $fields['billing']['billing_address_2']['type'] = 'hidden';
  $fields['billing']['billing_postcode']['type'] = 'hidden';
  $fields['billing']['billing_city']['type'] = 'hidden';
  $fields['billing']['billing_city']['required'] = false;
  $fields['billing']['billing_country']['default'] = 'VN'; // đổi thành mã quốc gia mong muốn

  $fields['billing']['billing_last_name']['placeholder'] = 'Nhập họ và tên';
  $fields['billing']['billing_phone']['placeholder']     = 'Nhập số điện thoại';
  $fields['billing']['billing_email']['placeholder']     = 'Nhập địa chỉ email';
  return $fields;
});
