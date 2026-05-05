<?php

if (!defined('ABSPATH')) {
  exit;
}

// Xử lý tính năng "Buy Now" - bỏ qua giỏ hàng và chuyển thẳng đến trang thanh toán
add_filter('woocommerce_add_to_cart_validation', function ($passed, $product_id, $quantity) {
  if (!empty($_REQUEST['twmp_buy_now'])) {
    if (function_exists('WC') && WC()->cart) {
      WC()->cart->empty_cart();
    }
  }
  return $passed;
}, 1, 3);

add_filter('woocommerce_add_to_cart_redirect', function ($redirect_url) {
  if (!empty($_REQUEST['twmp_buy_now'])) {
    return wc_get_checkout_url();
  }

  return $redirect_url;
});

// Reset checkout flow nếu có item mới được thêm vào cart
add_action('woocommerce_before_checkout_form', function () {
  if (!function_exists('WC') || !WC()->session) {
    return;
  }

  // Nếu cart có item mới → reset flow
  if (!empty(WC()->cart) && !WC()->cart->is_empty()) {
    WC()->session->__unset('twmp_checkout_payment_order_id');
    WC()->session->__unset('twmp_checkout_payment_order_key');
  }
}, 1);

// Custom html cho trang thanh toán
add_action('woocommerce_before_checkout_form', 'twmp_checkout_page_open', 5);

function twmp_checkout_page_open()
{
  $state = function_exists('twmp_checkout_get_payment_order_context') ? twmp_checkout_get_payment_order_context() : array();
  $step = !empty($state['step']) ? absint($state['step']) : 1;
  $settings = array(
    'step'               => $step,
    'orderId'            => !empty($state['order_id']) ? absint($state['order_id']) : 0,
    'orderKey'           => !empty($state['order_key']) ? sanitize_text_field($state['order_key']) : '',
    'ajaxUrl'            => admin_url('admin-ajax.php'),
    'pollAction'         => 'twmp_checkout_poll_payment_status',
    'uploadAction'       => 'twmp_checkout_upload_payment_proof',
    'adminReviewAction'  => 'twmp_checkout_admin_review_order',
    'nonceActionPrefix'  => 'twmp_checkout_payment_',
  );

  echo '<div class="page-block page-block--checkout woocommerce-checkout-custom--settings" data-settings="' . esc_attr(wp_json_encode($settings)) . '">';
  echo '<div class="twmp-checkout-steps" aria-hidden="true">';
  echo '<div class="twmp-checkout-steps__item ' . esc_attr(1 === $step ? 'is-active' : '') . '"><span class="twmp-checkout-steps__index">1</span><span class="twmp-checkout-steps__label">' . esc_html__('Booking information', 'twmp-ath') . '</span></div>';
  echo '<div class="twmp-checkout-steps__line"></div>';
  echo '<div class="twmp-checkout-steps__item ' . esc_attr(2 === $step ? 'is-active' : '') . '"><span class="twmp-checkout-steps__index">2</span><span class="twmp-checkout-steps__label">' . esc_html__('Payment', 'twmp-ath') . '</span></div>';
  echo '</div>';
}

add_action('woocommerce_after_checkout_form', 'twmp_checkout_page_close', 100);

function twmp_checkout_page_close()
{
  echo '</div>';
}

// Lấy context đơn hàng cho trang thanh toán, bao gồm thông tin đơn hàng, trạng thái xác thực bill, và cấu hình thanh toán
function twmp_checkout_get_payment_order_context()
{
  static $context = null;

  if (null !== $context) {
    return $context;
  }

  $context = array(
    'step'         => 1,
    'order_id'     => 0,
    'order_key'    => '',
    'order'        => null,
    'proof_status' => 'waiting_upload',
    'status_label'  => esc_html__('Awaiting bill upload', 'twmp-ath'),
    'status_text'   => esc_html__('Complete transfer and upload the bill to continue.', 'twmp-ath'),
    'can_upload'    => true,
    'nonce'         => wp_create_nonce('twmp_checkout_payment_guest'),
    'config'        => twmp_checkout_get_payment_config(),
  );

  $request_order_id = 0;
  $request_order_key = '';

  if (isset($_GET['order_id'])) {
    $request_order_id = absint(wp_unslash($_GET['order_id']));
  }

  if (isset($_GET['order_key'])) {
    $request_order_key = sanitize_text_field(wp_unslash($_GET['order_key']));
  } elseif (isset($_GET['key'])) {
    $request_order_key = sanitize_text_field(wp_unslash($_GET['key']));
  }

  if ((!$request_order_id || !$request_order_key) && function_exists('WC') && WC()->session) {
    if (!$request_order_id) {
      $request_order_id = absint(WC()->session->get('twmp_checkout_payment_order_id', 0));
    }

    if (!$request_order_key) {
      $request_order_key = (string) WC()->session->get('twmp_checkout_payment_order_key', '');
    }
  }

  if ($request_order_id > 0 && $request_order_key !== '') {
    $order = function_exists('wc_get_order') ? wc_get_order($request_order_id) : null;
    if ($order instanceof WC_Order && hash_equals($order->get_order_key(), $request_order_key)) {
      if (!headers_sent()) {
        nocache_headers();
      }

      $context['step'] = 2;
      $context['order_id'] = $request_order_id;
      $context['order_key'] = $request_order_key;
      $context['order'] = $order;

      $proof_status = twmp_checkout_get_payment_proof_status($order);
      $context['proof_status'] = $proof_status;
      $context['status_label'] = twmp_checkout_get_payment_status_label($proof_status);
      $context['status_text'] = twmp_checkout_get_payment_status_text($proof_status);
      $context['can_upload'] = in_array($proof_status, array('waiting_upload', 'rejected'), true);
      $context['nonce'] = wp_create_nonce('twmp_checkout_payment_' . $request_order_id);
    }
  }

  return $context;
}

// Lấy cấu hình thanh toán từ options, hỗ trợ nhiều key khác nhau để linh hoạt trong việc đặt tên option
function twmp_checkout_get_option_value(array $keys, $default = '')
{
  foreach ($keys as $key) {
    if (function_exists('get_field')) {
      $value = get_field($key, 'option');
      if (is_array($value) && !empty($value['url'])) {
        return $value;
      }
      if (is_string($value) && trim($value) !== '') {
        return trim($value);
      }
      if (is_numeric($value) && absint($value) > 0) {
        return absint($value);
      }
    }

    $value = get_option($key, null);
    if ($value === null || $value === '' || $value === false) {
      continue;
    }

    return is_string($value) ? trim($value) : $value;
  }

  return $default;
}

// Hỗ trợ lấy URL media từ nhiều dạng input khác nhau (URL trực tiếp, ID attachment, hoặc array chứa URL/ID)
function twmp_checkout_resolve_media_url($value)
{
  if (is_array($value)) {
    if (!empty($value['url'])) {
      return esc_url_raw($value['url']);
    }

    if (!empty($value['ID'])) {
      $value = absint($value['ID']);
    } else {
      return '';
    }
  }

  if (is_numeric($value)) {
    $url = wp_get_attachment_url(absint($value));
    return $url ? esc_url_raw($url) : '';
  }

  if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
    return esc_url_raw($value);
  }

  return '';
}

// Lấy cấu hình thanh toán từ options, hỗ trợ nhiều key khác nhau để linh hoạt trong việc đặt tên option
function twmp_checkout_get_payment_config()
{
  $qr_value = twmp_checkout_get_option_value(array(
    'checkout_payment_qr',
    'payment_qr',
    'twmp_checkout_payment_qr',
    'twmp_payment_qr',
  ));

  return array(
    'qr_url'          => twmp_checkout_resolve_media_url($qr_value),
    'company_name'    => (string) twmp_checkout_get_option_value(array('checkout_company_name', 'company_name', 'twmp_checkout_company_name'), get_bloginfo('name')),
    'company_address' => (string) twmp_checkout_get_option_value(array('checkout_company_address', 'company_address', 'twmp_checkout_company_address')),
    'company_phone'   => (string) twmp_checkout_get_option_value(array('checkout_company_phone', 'company_phone', 'twmp_checkout_company_phone')),
    'company_email'   => (string) twmp_checkout_get_option_value(array('checkout_company_email', 'company_email', 'twmp_checkout_company_email')),
    'bank_name'       => (string) twmp_checkout_get_option_value(array('checkout_bank_name', 'bank_name', 'twmp_checkout_bank_name')),
    'account_name'    => (string) twmp_checkout_get_option_value(array('checkout_bank_account_name', 'bank_account_name', 'twmp_checkout_bank_account_name')),
    'account_number'  => (string) twmp_checkout_get_option_value(array('checkout_bank_account_number', 'bank_account_number', 'twmp_checkout_bank_account_number')),
    'branch'          => (string) twmp_checkout_get_option_value(array('checkout_bank_branch', 'bank_branch', 'twmp_checkout_bank_branch')),
    'transfer_note'   => (string) twmp_checkout_get_option_value(array('checkout_transfer_note', 'transfer_note', 'twmp_checkout_transfer_note')),
    'bill_title'      => (string) twmp_checkout_get_option_value(array('checkout_bill_title', 'payment_bill_title', 'twmp_checkout_bill_title'), esc_html__('Upload bill', 'twmp-ath')),
  );
}

// Lấy trạng thái xác thực bill của đơn hàng, dựa trên order status và meta '_twmp_checkout_payment_proof_status'
function twmp_checkout_get_payment_proof_status($order)
{
  if (!$order instanceof WC_Order) {
    return 'waiting_upload';
  }

  $order_status = $order->get_status();
  if (in_array($order_status, array('processing', 'completed'), true)) {
    return 'approved';
  }

  if ('failed' === $order_status) {
    return 'rejected';
  }

  $status = sanitize_key((string) $order->get_meta('_twmp_checkout_payment_proof_status', true));
  if (in_array($status, array('waiting_upload', 'pending_review', 'approved', 'rejected'), true)) {
    return $status;
  }

  return 'waiting_upload';
}

// Lấy label hiển thị tương ứng với trạng thái xác thực bill
function twmp_checkout_get_payment_status_label($status)
{
  $labels = array(
    'waiting_upload' => esc_html__('Waiting for bill upload', 'twmp-ath'),
    'pending_review' => esc_html__('Waiting for confirmation', 'twmp-ath'),
    'approved'       => esc_html__('Payment confirmed', 'twmp-ath'),
    'rejected'       => esc_html__('Bill rejected', 'twmp-ath'),
  );

  return !empty($labels[$status]) ? $labels[$status] : esc_html__('Waiting for bill upload', 'twmp-ath');
}

// Lấy text mô tả tương ứng với trạng thái xác thực bill, để hướng dẫn khách hàng biết bước tiếp theo cần làm gì
function twmp_checkout_get_payment_status_text($status)
{
  $texts = array(
    'waiting_upload' => esc_html__('Transfer to the account below and upload the bill.', 'twmp-ath'),
    'pending_review' => esc_html__('We have received your bill. Please wait for admin review.', 'twmp-ath'),
    'approved'       => esc_html__('Your payment was approved.', 'twmp-ath'),
    'rejected'       => esc_html__('Your bill was rejected. Please upload a clearer file.', 'twmp-ath'),
  );

  return !empty($texts[$status]) ? $texts[$status] : esc_html__('Transfer to the account below and upload the bill.', 'twmp-ath');
}

// Lấy label cho nút hành động tương ứng với trạng thái xác thực bill, ví dụ: nếu đã approved thì nút sẽ là "Success", nếu rejected thì là "Failed", còn lại sẽ là "Waiting"
function twmp_checkout_get_payment_action_label($status)
{
  if ('approved' === $status) {
    return esc_html__('Success', 'twmp-ath');
  }

  if ('rejected' === $status) {
    return esc_html__('Failed', 'twmp-ath');
  }

  return esc_html__('Waiting', 'twmp-ath');
}


// Các hàm hỗ trợ cho tính năng vé sự kiện, bao gồm lấy dữ liệu sản phẩm vé, lưu trữ lựa chọn vé vào session, và render phần chi tiết vé trên trang thanh toán
function twmp_checkout_get_ticket_product_data($product_id = 0)
{
  $product_id = absint($product_id);
  if (!$product_id && function_exists('get_the_ID')) {
    $product_id = absint(get_the_ID());
  }

  $data = array(
    'product_id'   => $product_id,
    'performances'  => array(),
    'ticket_prices' => array(),
  );

  if (!$product_id || !function_exists('get_field')) {
    return $data;
  }

  $performance_rows = (array) get_field('ath_performance_schedule', $product_id);
  foreach ($performance_rows as $row) {
    $datetime_raw = isset($row['performance_datetime']) ? trim((string) $row['performance_datetime']) : '';
    if ($datetime_raw === '') {
      continue;
    }

    $timestamp = strtotime($datetime_raw);
    if (!$timestamp) {
      continue;
    }

    $key = 'performance-' . md5($datetime_raw);
    $day = wp_date('l', $timestamp);
    $date = wp_date('d M Y', $timestamp);
    $time = wp_date('H:i', $timestamp);

    $data['performances'][$key] = array(
      'key'          => $key,
      'datetime'     => $datetime_raw,
      'timestamp'    => $timestamp,
      'day'          => $day,
      'date'         => $date,
      'time'         => $time,
      'display'      => sprintf('%s | %s %s', $day, $date, $time),
      'display_short'=> sprintf('%s %s', $date, $time),
    );
  }

  $price_rows = (array) get_field('ath_ticket_price_options', $product_id);
  foreach ($price_rows as $row) {
    $label = isset($row['label']) ? trim((string) $row['label']) : '';
    $price_raw = isset($row['price']) ? $row['price'] : '';
    $price = (float) wc_format_decimal($price_raw);

    if ($label === '' || $price <= 0) {
      continue;
    }

    $key = 'price-' . md5($label . '|' . $price);
    $data['ticket_prices'][$key] = array(
      'key'   => $key,
      'label' => $label,
      'price' => $price,
    );
  }

  return $data;
}

function twmp_checkout_get_ticket_product_id()
{
  if (!function_exists('WC') || !WC()->cart) {
    return 0;
  }

  foreach (WC()->cart->get_cart() as $cart_item) {
    $product_id = !empty($cart_item['product_id']) ? absint($cart_item['product_id']) : 0;
    if (!$product_id) {
      continue;
    }

    $ticket_data = twmp_checkout_get_ticket_product_data($product_id);
    if (!empty($ticket_data['performances']) || !empty($ticket_data['ticket_prices'])) {
      return $product_id;
    }
  }

  return 0;
}

function twmp_checkout_get_ticket_selection_defaults($product_id = 0)
{
  $ticket_data = twmp_checkout_get_ticket_product_data($product_id);
  $performance_key = '';
  $price_key = '';

  if (!empty($ticket_data['performances'])) {
    $performance_key = array_key_first($ticket_data['performances']);
  }

  if (!empty($ticket_data['ticket_prices'])) {
    $price_key = array_key_first($ticket_data['ticket_prices']);
  }

  return array(
    'product_id'      => absint($ticket_data['product_id']),
    'performance_key' => $performance_key,
    'price_key'       => $price_key,
  );
}

function twmp_checkout_resolve_ticket_selection($product_id, $selection)
{
  $ticket_data = twmp_checkout_get_ticket_product_data($product_id);
  $defaults = twmp_checkout_get_ticket_selection_defaults($product_id);

  $performance_key = isset($selection['performance_key']) ? sanitize_key($selection['performance_key']) : '';
  $price_key = isset($selection['price_key']) ? sanitize_key($selection['price_key']) : '';

  if (empty($ticket_data['performances']) || empty($ticket_data['performances'][$performance_key])) {
    $performance_key = $defaults['performance_key'];
  }

  if (empty($ticket_data['ticket_prices']) || empty($ticket_data['ticket_prices'][$price_key])) {
    $price_key = $defaults['price_key'];
  }

  return array(
    'product_id'      => absint($product_id),
    'performance_key' => $performance_key,
    'price_key'       => $price_key,
  );
}

function twmp_checkout_get_ticket_selection_state($product_id = 0)
{
  $defaults = twmp_checkout_get_ticket_selection_defaults($product_id);
  $state = $defaults;

  if (function_exists('WC') && WC()->session) {
    $stored = (array) WC()->session->get('twmp_ticket_selection', array());
    $state = array_merge($state, array_filter($stored, 'strlen'));

    if (empty($state['product_id'])) {
      $state['product_id'] = $defaults['product_id'];
    }

    if (empty($state['performance_key']) && !empty($defaults['performance_key'])) {
      $state['performance_key'] = $defaults['performance_key'];
    }

    if (empty($state['price_key']) && !empty($defaults['price_key'])) {
      $state['price_key'] = $defaults['price_key'];
    }

    WC()->session->set('twmp_ticket_selection', $state);
  }

  return $state;
}

function twmp_checkout_render_ticket_detail_section()
{
  $product_id = twmp_checkout_get_ticket_product_id();
  if (!$product_id) {
    return;
  }

  $ticket_data = twmp_checkout_get_ticket_product_data($product_id);
  if (empty($ticket_data['performances']) && empty($ticket_data['ticket_prices'])) {
    return;
  }

  $state = twmp_checkout_get_ticket_selection_state($product_id);
  $quantity = 1;
  $cart_item_key = twmp_checkout_get_cart_item_key_by_product_id($product_id);

  if ($cart_item_key && function_exists('WC') && WC()->cart) {
    foreach (WC()->cart->get_cart() as $current_cart_item_key => $cart_item) {
      if ($current_cart_item_key !== $cart_item_key) {
        continue;
      }

      $quantity = !empty($cart_item['quantity']) ? max(1, absint($cart_item['quantity'])) : 1;
      break;
    }
  }
  ?>
  <section class="twmp-checkout-card twmp-checkout-card--ticket-detail">
    <input type="hidden" name="twmp_ticket_product_id" value="<?php echo esc_attr($product_id); ?>">

    <header class="twmp-checkout-card__header">
      <span class="twmp-checkout-card__step">2</span>
      <h3 class="twmp-checkout-card__title"><?php echo esc_html__('Ticket detail', 'twmp-ath'); ?></h3>
    </header>

    <div class="twmp-checkout-card__content">
      <?php if (!empty($ticket_data['performances'])) : ?>
        <div class="twmp-checkout-ticket-detail__group twmp-checkout-ticket-detail__group--performance">
          <p class="twmp-checkout-ticket-detail__label"><?php echo esc_html__('Select Performance date *', 'twmp-ath'); ?></p>
          <div class="twmp-checkout-ticket-detail__options twmp-checkout-ticket-detail__options--performance">
          <?php foreach ($ticket_data['performances'] as $option) : ?>
            <label class="twmp-ticket-option <?php echo esc_attr($state['performance_key'] === $option['key'] ? 'is-selected' : ''); ?>">
              <input
                type="radio"
                name="twmp_ticket_performance"
                value="<?php echo esc_attr($option['key']); ?>"
                <?php checked($state['performance_key'], $option['key']); ?>
                required
              >
              <span class="twmp-ticket-option__main">
                <span class="twmp-ticket-option__day"><?php echo esc_html($option['day']); ?></span>
                <span class="twmp-ticket-option__date"><?php echo esc_html($option['date']); ?></span>
              </span>
              <span class="twmp-ticket-option__time"><?php echo esc_html($option['time']); ?></span>
            </label>
          <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($ticket_data['ticket_prices'])) : ?>
        <div class="twmp-checkout-ticket-detail__group twmp-checkout-ticket-detail__group--price">
          <p class="twmp-checkout-ticket-detail__label"><?php echo esc_html__('Select type of ticket', 'twmp-ath'); ?></p>
          <div class="twmp-checkout-ticket-detail__options twmp-checkout-ticket-detail__options--price">
          <?php foreach ($ticket_data['ticket_prices'] as $option) : ?>
            <label class="twmp-ticket-price-option <?php echo esc_attr($state['price_key'] === $option['key'] ? 'is-selected' : ''); ?>">
              <input
                type="radio"
                name="twmp_ticket_price_option"
                value="<?php echo esc_attr($option['key']); ?>"
                <?php checked($state['price_key'], $option['key']); ?>
                required
              >
              <span class="twmp-ticket-price-option__label"><?php echo esc_html($option['label']); ?></span>
              <span class="twmp-ticket-price-option__price"><?php echo wp_kses_post(wc_price($option['price'])); ?></span>
              <span class="twmp-ticket-price-option__unit"><?php echo esc_html__('/ Ticket', 'twmp-ath'); ?></span>
            </label>
          <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="twmp-checkout-ticket-detail__group twmp-checkout-ticket-detail__group--quantity">
        <p class="twmp-checkout-ticket-detail__label"><?php echo esc_html__('Quantity', 'twmp-ath'); ?> *</p>
        <div class="twmp-ticket-quantity" data-ticket-quantity-control>
          <button type="button" class="twmp-ticket-quantity__button" data-ticket-quantity-step="minus" aria-label="<?php echo esc_attr__('Decrease quantity', 'twmp-ath'); ?>">-</button>
          <input
            type="number"
            name="twmp_ticket_quantity"
            class="twmp-ticket-quantity__input"
            value="<?php echo esc_attr($quantity); ?>"
            min="1"
            step="1"
            inputmode="numeric"
            required
          >
          <button type="button" class="twmp-ticket-quantity__button" data-ticket-quantity-step="plus" aria-label="<?php echo esc_attr__('Increase quantity', 'twmp-ath'); ?>">+</button>
        </div>
      </div>
    </div>
  </section>
  <?php
}

add_action('woocommerce_checkout_after_customer_details', 'twmp_checkout_render_ticket_detail_section', 20);

function twmp_checkout_get_cart_item_key_by_product_id($product_id = 0)
{
  if (!function_exists('WC') || !WC()->cart) {
    return '';
  }

  $product_id = absint($product_id);
  if (!$product_id) {
    $product_id = twmp_checkout_get_ticket_product_id();
  }

  if (!$product_id) {
    return '';
  }

  foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
    if (!empty($cart_item['product_id']) && absint($cart_item['product_id']) === $product_id) {
      return $cart_item_key;
    }
  }

  return '';
}